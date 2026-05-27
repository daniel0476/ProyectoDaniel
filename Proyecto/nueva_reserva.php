<?php
/*
 * nueva_reserva.php
 * =================
 * Página donde el cliente puede crear una nueva reserva.
 * Muestra un formulario con selects para servicio, barbero,
 * fecha y hora (cargadas dinámicamente vía AJAX).
 * También incluye un resumen en vivo de la reserva.
 */

require_once 'config.php';
require_once 'funciones.php';

// Solo usuarios logueados pueden hacer reservas
verificar_autenticacion();

$titulo_pagina = 'Nueva Reserva - ' . APP_NAME;

$error = '';
$exito = '';

// ---------------------------------------------------------------
// CARGA DE DATOS INICIALES
// ---------------------------------------------------------------
$servicios = obtener_servicios($mysqli);
$barberos = obtener_barberos($mysqli);
$config = obtener_config_sistema($mysqli);

// ---------------------------------------------------------------
// ESTILOS ADICIONALES (CSS embebido)
// ---------------------------------------------------------------
$estilos_adicionales = '<style>
    .form-select {
        color: #f3f3f3;
    }

    .form-select option {
        background-color: #ffffff;
        color: #111111;
    }

    .reserva-resumen {
        display: none;
    }

    .reserva-resumen-lineas {
        margin-top: 10px;
    }

    .reserva-resumen-lineas p {
        margin-bottom: 6px;
    }
</style>';

// ---------------------------------------------------------------
// PROCESAR EL FORMULARIO DE NUEVA RESERVA
// ---------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requerir_csrf();

    // Recogemos y sanitizamos los datos del formulario
    $fecha = trim($_POST['fecha'] ?? '');
    $hora = trim($_POST['hora'] ?? '');
    $id_servicio = intval($_POST['id_servicio'] ?? 0);
    $dni_barbero = trim($_POST['dni_barbero'] ?? '');
    $notas = trim($_POST['notas'] ?? '');
    
    // Validación de campos obligatorios
    if (empty($fecha) || empty($hora) || $id_servicio <= 0 || empty($dni_barbero)) {
        $error = 'Por favor, completa todos los campos requeridos.';
    } else {
        // Verificar que el servicio existe
        $servicio = obtener_servicio($id_servicio, $mysqli);
        if (!$servicio) {
            $error = 'Servicio no encontrado.';
        } else {
            // Verificar que la fecha no sea pasada
            if (strtotime($fecha) < strtotime(date('Y-m-d'))) {
                $error = 'La fecha debe ser hoy o posterior.';
            } else {
                // Verificar que el barbero existe
                $barbero = obtener_barbero($dni_barbero, $mysqli);
                if (!$barbero) {
                    $error = 'Barbero no encontrado.';
                } else {
                    // Validar formato de hora
                    if (strtotime($hora) === false) {
                        $error = 'La hora seleccionada no es válida.';
                    } else {
                        // Comprobar que la hora está disponible (no ocupada)
                        $slots_disponibles = obtener_slots_disponibles_barbero($dni_barbero, $fecha, $id_servicio, $mysqli);
                        if (!in_array($hora, $slots_disponibles, true)) {
                            $error = 'El horario seleccionado no está disponible. Por favor, elige otro.';
                        } else {
                            // TODO OK: Insertar la reserva en la base de datos
                            $fecha_prep = escapar($fecha, $mysqli);
                            $hora_prep = escapar($hora, $mysqli);
                            $dni_prep = escapar($dni_barbero, $mysqli);
                            $notas_prep = escapar($notas, $mysqli);

                            $query = "INSERT INTO citas (fecha, hora, DNI_barbero, ID_usuario, ID_servicio, precio_final, estado, notas_cliente) 
                                      VALUES ('$fecha_prep', '$hora_prep', '$dni_prep', $usuario_id, $id_servicio, " . $servicio['precio'] . ", 'pendiente', '$notas_prep')";

                            if ($mysqli->query($query)) {
                                redirigir_con_mensaje('reservas.php', ' Cita reservada con éxito! El barbero la confirmará pronto.', 'success');
                            } else {
                                $error = 'Error al crear la reserva: ' . $mysqli->error;
                            }
                        }
                    }
                }
            }
        }
    }
}

// ---------------------------------------------------------------
// CAPTURAR CONTENIDO
// ---------------------------------------------------------------
ob_start();
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">
                    <i class="bi bi-calendar-plus"></i> Nueva Reserva
                </h3>
            </div>
            <div class="card-body">
                <!-- Mensaje de error si lo hay -->
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo e($error); ?></div>
                <?php endif; ?>
                
                <!-- Formulario de reserva -->
                <form method="POST" id="formulario_reserva">
                    <?php echo csrf_input(); ?>
                    
                    <!-- Fila 1: Servicio y Barbero -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_servicio" class="form-label">
                                <i class="bi bi-scissors"></i> Servicio *
                            </label>
                            <select 
                                class="form-select" 
                                id="id_servicio" 
                                name="id_servicio" 
                                required
                                onchange="actualizarDuracion()"
                            >
                                <option value="">Selecciona un servicio...</option>
                                <?php foreach ($servicios as $svc): ?>
                                    <option value="<?php echo (int)$svc['ID_servicio']; ?>" data-duracion="<?php echo (int)$svc['duracion_minutos']; ?>">
                                        <?php echo e($svc['nombre']); ?> - €<?php echo number_format($svc['precio'], 2, ',', '.'); ?> 
                                        (<?php echo $svc['duracion_minutos']; ?> min)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="dni_barbero" class="form-label">
                                <i class="bi bi-person-check"></i> Barbero *
                            </label>
                            <select 
                                class="form-select" 
                                id="dni_barbero" 
                                name="dni_barbero" 
                                required
                                onchange="actualizarHorarios()"
                            >
                                <option value="">Selecciona un barbero...</option>
                                <?php foreach ($barberos as $bar): ?>
                                    <option value="<?php echo e($bar['DNI_barbero']); ?>">
                                        <?php echo e($bar['nombre'] . ' ' . $bar['apellidos']); ?> 
                                        (<?php echo e($bar['especialidad']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Fila 2: Fecha y Hora -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha" class="form-label">
                                <i class="bi bi-calendar"></i> Fecha *
                            </label>
                            <input 
                                type="date" 
                                class="form-control" 
                                id="fecha" 
                                name="fecha" 
                                required
                                value="<?php echo e($_POST['fecha'] ?? date('Y-m-d')); ?>"
                                min="<?php echo date('Y-m-d'); ?>"
                                onchange="actualizarHorarios()"
                            >
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="hora" class="form-label">
                                <i class="bi bi-clock"></i> Hora *
                            </label>
                            <!-- Select de horas: se habilita vía JS después de cargar slots -->
                            <select class="form-select" id="hora" name="hora" required disabled>
                                <option value="">Selecciona hora...</option>
                            </select>
                            <small class="text-muted mt-2" id="horas_disponibles"></small>
                        </div>
                    </div>
                    
                    <!-- Notas opcionales -->
                    <div class="mb-3">
                        <label for="notas" class="form-label">
                            <i class="bi bi-chat"></i> Notas (Opcional)
                        </label>
                        <textarea 
                            class="form-control" 
                            id="notas" 
                            name="notas" 
                            rows="3" 
                            placeholder="Indicaciones especiales, alergia a productos, etc."
                        ></textarea>
                    </div>
                    
                    <!-- Resumen de la reserva (se actualiza dinámicamente) -->
                    <div class="alert alert-info reserva-resumen" id="resumen">
                        <strong>Resumen de tu reserva:</strong>
                        <div id="resumen_contenido"></div>
                    </div>
                    
                    <!-- Aviso de política de cancelación -->
                    <div class="alert alert-warning py-2 mb-3" role="alert">
                        <small><i class="bi bi-info-circle"></i> Las citas solo se pueden cancelar hasta <strong><?php echo (int)($config['aviso_cancelacion_horas'] ?? 24); ?>h</strong> antes de la hora reservada.</small>
                    </div>

                    <!-- Botones de acción -->
                    <div class="d-flex gap-2 justify-content-between">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                        <button 
                            type="submit" 
                            class="btn btn-primary" 
                            id="btn_reservar"
                            disabled
                        >
                            <i class="bi bi-check-circle"></i> Confirmar Reserva
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     JAVASCRIPT: Carga dinámica de horarios y resumen
     ============================================================ -->
<script>
// Datos de configuración pasados desde PHP
const config = {
    horario_apertura: '<?php echo e($config['horario_apertura']); ?>',
    horario_cierre: '<?php echo e($config['horario_cierre']); ?>',
    duracion_slot: <?php echo $config['duracion_slot_minutos']; ?>,
    tiempo_preparacion: <?php echo $config['tiempo_preparacion_minutos']; ?>
};

// Arrays completos de servicios y barberos para usar en JS
const servicios = <?php echo json_encode($servicios, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
const barberos = <?php echo json_encode($barberos, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

/**
 * actualizarDuracion()
 * Se ejecuta al cambiar el servicio. Dispara la recarga de horarios.
 */
function actualizarDuracion() {
    const select = document.getElementById('id_servicio');
    const option = select.options[select.selectedIndex];
    if (option.value) {
        actualizarHorarios();
    } else {
        actualizarResumen();
    }
}

/**
 * actualizarHorarios()
 * Llama a la API de horarios disponibles y rellena el select de horas.
 * También formatea las horas a formato 12h AM/PM para mostrar al usuario.
 */
async function actualizarHorarios() {
    const fecha = document.getElementById('fecha').value;
    const dni_barbero = document.getElementById('dni_barbero').value;
    const id_servicio = document.getElementById('id_servicio').value;
    const horaSelect = document.getElementById('hora');
    const infoHoras = document.getElementById('horas_disponibles');
    
    // Resetear el select de horas
    horaSelect.innerHTML = '<option value="">Selecciona hora...</option>';
    horaSelect.disabled = true;
    document.getElementById('btn_reservar').disabled = true;
    document.getElementById('resumen').style.display = 'none';
    infoHoras.textContent = '';
    
    // Si falta algún campo, no hacemos la petición
    if (!fecha || !dni_barbero || !id_servicio) {
        infoHoras.textContent = 'Selecciona servicio, barbero y fecha para ver horas.';
        return;
    }

    infoHoras.textContent = 'Cargando horarios disponibles...';

    try {
        const params = new URLSearchParams({
            fecha,
            dni_barbero,
            id_servicio
        });

        const response = await fetch(`api/horarios_disponibles.php?${params.toString()}`, {
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('No se pudo obtener la disponibilidad.');
        }

        const data = await response.json();
        if (!data.success) {
            throw new Error(data.message || 'Error obteniendo horarios.');
        }

        if (!Array.isArray(data.slots) || data.slots.length === 0) {
            infoHoras.textContent = 'No hay horarios disponibles para esta selección.';
            return;
        }

        // Función para convertir hora 24h a formato 12h AM/PM
        function formato12h(hora) {
            const [h, m] = hora.split(':');
            const ih = parseInt(h, 10);
            const ampm = ih >= 12 ? 'PM' : 'AM';
            const h12 = ih % 12 || 12;
            return h12 + ':' + m + ' ' + ampm;
        }

        // Rellenar el select con las horas disponibles
        data.slots.forEach((hora) => {
            const option = document.createElement('option');
            option.value = hora;
            option.textContent = formato12h(hora);
            horaSelect.appendChild(option);
        });

        horaSelect.disabled = false;
        infoHoras.textContent = `${data.slots.length} horarios disponibles.`;
    } catch (error) {
        infoHoras.textContent = error.message;
    }

    actualizarResumen();
}

/**
 * actualizarResumen()
 * Muestra un resumen en vivo con los datos seleccionados.
 */
function actualizarResumen() {
    const idServicio = document.getElementById('id_servicio').value;
    const dniBarbero = document.getElementById('dni_barbero').value;
    const fecha = document.getElementById('fecha').value;
    const hora = document.getElementById('hora').value;
    
    const btnReservar = document.getElementById('btn_reservar');
    
    // Solo mostrar resumen si todos los campos están completos
    if (idServicio && dniBarbero && fecha && hora) {
        const servicio = servicios.find(s => s.ID_servicio == idServicio);
        const barbero = barberos.find(b => b.DNI_barbero == dniBarbero);
        
        const resumenContent = `
            <div class="reserva-resumen-lineas">
                <p><strong>Servicio:</strong> ${servicio.nombre} - €${parseFloat(servicio.precio).toFixed(2)}</p>
                <p><strong>Barbero:</strong> ${barbero.nombre} ${barbero.apellidos}</p>
                <p><strong>Fecha:</strong> ${new Date(fecha).toLocaleDateString('es-ES', {weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'})}</p>
                <p><strong>Hora:</strong> ${hora}</p>
                <p><strong>Duracin:</strong> ${servicio.duracion_minutos} minutos</p>
            </div>
        `;
        
        document.getElementById('resumen_contenido').innerHTML = resumenContent;
        document.getElementById('resumen').style.display = 'block';
        btnReservar.disabled = false;
    } else {
        document.getElementById('resumen').style.display = 'none';
        btnReservar.disabled = true;
    }
}

// Event listeners para actualizar dinámicamente
document.getElementById('fecha').addEventListener('change', actualizarHorarios);
document.getElementById('dni_barbero').addEventListener('change', actualizarHorarios);
document.getElementById('hora').addEventListener('change', actualizarResumen);
document.getElementById('id_servicio').addEventListener('change', actualizarResumen);

// Mensaje inicial al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    const infoHoras = document.getElementById('horas_disponibles');
    if (infoHoras) {
        infoHoras.textContent = 'Selecciona servicio, barbero y fecha para ver horas.';
    }
});
</script>

<?php
$contenido = ob_get_clean();
include 'plantilla.php';
?>
