<?php
/*
 * reservas.php
 * ============
 * Página donde el cliente puede ver todas sus citas (activas y pasadas),
 * consultar detalles como barbero, servicio, fecha, hora, precio,
 * y cancelar aquellas que aún estén dentro del plazo permitido.
 */

require_once 'config.php';
require_once 'funciones.php';

// Solo usuarios logueados pueden ver sus reservas
verificar_autenticacion();

$titulo_pagina = 'Mis Reservas - ' . APP_NAME;

// ---------------------------------------------------------------
// CARGA DE DATOS
// ---------------------------------------------------------------
// Obtenemos todas las citas del usuario (excluye canceladas)
$citas = obtener_citas_usuario($usuario_id, $mysqli);
// Cuántas horas de antelación mínima se necesitan para cancelar
$horas_cancelacion = obtener_horas_cancelacion($mysqli);

// ---------------------------------------------------------------
// ESTILOS ADICIONALES (CSS embebido)
// ---------------------------------------------------------------
$estilos_adicionales = '<style>
    .reservas-page-title,
    .reservas-page-title .bi,
    .reservas-empty-title,
    .reservas-empty-subtitle {
        color: #111111 !important;
    }

    .reservas-empty-icon {
        font-size: 64px;
        color: #111111 !important;
    }

    .no-data {
        color: #111111 !important;
    }

    .reserva-card {
        border-left-width: 4px;
        border-left-style: solid;
    }

    .reserva-card-pendiente {
        border-left-color: #ffc107;
    }

    .reserva-card-confirmada {
        border-left-color: #28a745;
    }

    .reserva-card-cancelada {
        border-left-color: #dc3545;
    }

    .reserva-card-completada {
        border-left-color: #17a2b8;
    }

    .reserva-card-default {
        border-left-color: #999;
    }

    .reserva-nota {
        font-size: 13px;
    }

    .reserva-stat-total,
    .reserva-stat-proximas,
    .reserva-stat-completadas,
    .reserva-stat-gasto {
        font-weight: 700;
    }

    .reserva-stat-total {
        color: #e39a56;
    }

    .reserva-stat-proximas {
        color: #28a745;
    }

    .reserva-stat-completadas {
        color: #17a2b8;
    }

    .reserva-stat-gasto {
        color: #ffc107;
    }
</style>';

// ---------------------------------------------------------------
// PROCESAR CANCELACIÓN
// ---------------------------------------------------------------
// Si el usuario ha pulsado el botón "Cancelar" en alguna cita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar'], $_POST['id_cita'])) {
    requerir_csrf();

    $id_cita = intval($_POST['id_cita']);
    
    // Verificar que la cita pertenece al usuario actual (seguridad)
    $cita = obtener_cita($id_cita, $mysqli);
    if ($cita && $cita['ID_usuario'] == $usuario_id && $cita['estado'] !== 'cancelada') {
        // Calcular cuánto tiempo falta para la cita
        $fecha_cita = strtotime($cita['fecha'] . ' ' . $cita['hora']);
        $tiempo_restante = $fecha_cita - time();
        $horas = $tiempo_restante / 3600;
        
        // Solo permitir cancelar si quedan suficientes horas de antelación
        if ($horas >= $horas_cancelacion) {
            $query = "UPDATE citas SET estado = 'cancelada' WHERE ID_cita = $id_cita";
            if ($mysqli->query($query)) {
                redirigir_con_mensaje('reservas.php', ' Cita cancelada correctamente.', 'success');
            }
        } else {
            // Si no cumple el plazo, mostrar mensaje de advertencia
            redirigir_con_mensaje('reservas.php', ' No puedes cancelar una cita con menos de ' . $horas_cancelacion . ' horas de anticipación.', 'warning');
        }
    }
}

// ---------------------------------------------------------------
// CAPTURAR CONTENIDO
// ---------------------------------------------------------------
ob_start();
?>

<div class="row mb-30">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-20">
            <h1 class="mb-0 reservas-page-title">
                <i class="bi bi-calendar-check"></i> Mis Citas
            </h1>
            <!-- Botón para crear una nueva reserva -->
            <a href="nueva_reserva.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nueva Reserva
            </a>
        </div>
    </div>
</div>

<!-- Mensaje cuando no hay citas registradas -->
<?php if (empty($citas)): ?>
    <div class="no-data">
        <i class="bi bi-calendar-x reservas-empty-icon"></i>
        <h4 class="mt-3 reservas-empty-title">No tienes citas aún</h4>
        <p class="reservas-empty-subtitle">Por qué no reservas una ahora?</p>
        <a href="nueva_reserva.php" class="btn btn-primary mt-3">
            <i class="bi bi-calendar-plus"></i> Reservar Cita
        </a>
    </div>
<?php else: ?>
    <!-- Listado de citas en tarjetas -->
    <div class="row">
        <?php foreach ($citas as $cita): ?>
        <!-- Asignamos una clase CSS según el estado (define el color del borde izquierdo) -->
        <?php $clase_estado = match($cita['estado']) { 'pendiente' => 'reserva-card-pendiente', 'confirmada' => 'reserva-card-confirmada', 'cancelada' => 'reserva-card-cancelada', 'completada' => 'reserva-card-completada', default => 'reserva-card-default' }; ?>
        <div class="col-md-6 mb-3">
            <div class="card reserva-card <?php echo $clase_estado; ?>">
                <div class="card-body">
                    <!-- Cabecera: nombre del servicio + badge del estado -->
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title mb-0">
                            <?php echo e($cita['servicio_nombre']); ?>
                        </h5>
                        <span class="badge badge-estado-<?php echo $cita['estado']; ?>">
                            <i class="bi bi-circle-fill"></i> 
                            <?php echo ucfirst($cita['estado']); ?>
                        </span>
                    </div>
                    
                    <!-- Detalles de la cita -->
                    <div class="card-text mt-3">
                        <div class="mb-2">
                            <strong>Barbero:</strong> 
                            <?php echo e($cita['barbero_nombre'] . ' ' . $cita['barbero_apellidos']); ?>
                        </div>
                        
                        <div class="mb-2">
                            <strong>Fecha:</strong> 
                            <?php echo formatear_fecha($cita['fecha']); ?>
                        </div>
                        
                        <div class="mb-2">
                            <strong>Hora:</strong> 
                            <?php echo formatear_hora($cita['hora']); ?>
                        </div>
                        
                        <div class="mb-2">
                            <strong>Duracin:</strong> 
                            <?php echo $cita['duracion_minutos']; ?> minutos
                        </div>
                        
                        <div class="mb-2">
                            <strong>Precio:</strong> 
                            €<?php echo number_format($cita['precio_final'], 2, ',', '.'); ?>
                        </div>
                        
                        <!-- Notas que el cliente dejó al hacer la reserva -->
                        <?php if ($cita['notas_cliente']): ?>
                        <div class="mb-2 p-2 bg-light rounded reserva-nota">
                            <strong>Notas:</strong> <?php echo nl2br(e($cita['notas_cliente'])); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Botón de cancelar + info de tiempo restante -->
                    <div class="mt-3 d-flex gap-2">
                        <?php 
                        $fecha_cita = strtotime($cita['fecha'] . ' ' . $cita['hora']);
                        $tiempo_restante = $fecha_cita - time();
                        $horas = $tiempo_restante / 3600;
                        $es_proxima = $tiempo_restante > 0;
                        $puede_cancelar = $horas >= $horas_cancelacion && ($cita['estado'] === 'pendiente' || $cita['estado'] === 'confirmada');
                        ?>
                        
                        <!-- Mostramos botón de cancelar solo si la cita está activa -->
                        <?php if ($cita['estado'] === 'pendiente' || $cita['estado'] === 'confirmada'): ?>
                            <form method="POST" action="reservas.php" onsubmit="return <?php echo $puede_cancelar ? "confirm('Estás seguro de que quieres cancelar esta cita?')" : 'false'; ?>;">
                                <?php echo csrf_input(); ?>
                                <input type="hidden" name="cancelar" value="1">
                                <input type="hidden" name="id_cita" value="<?php echo (int)$cita['ID_cita']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" <?php echo $puede_cancelar ? '' : 'disabled'; ?> title="<?php echo $puede_cancelar ? 'Cancelar cita' : 'No se puede cancelar con menos de ' . $horas_cancelacion . 'h de antelación'; ?>">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </button>
                            </form>
                        <?php endif; ?>

                        <!-- Si no se puede cancelar, mostramos el motivo -->
                        <?php if (!$puede_cancelar && ($cita['estado'] === 'pendiente' || $cita['estado'] === 'confirmada')): ?>
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i>
                                Se puede cancelar hasta <?php echo $horas_cancelacion; ?>h antes de la cita
                            </small>
                        <?php endif; ?>
                        
                        <!-- Indicador de tiempo restante para citas próximas -->
                        <?php if ($es_proxima && ($cita['estado'] === 'pendiente' || $cita['estado'] === 'confirmada')): ?>
                            <span class="badge bg-success ms-auto">
                                <i class="bi bi-hourglass-split"></i> 
                                Próxima en <?php echo floor($horas); ?>h
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Estadísticas resumen de todas las citas -->
    <div class="row mt-40">
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Total de Citas</h6>
                    <h3 class="mb-0 reserva-stat-total">
                        <?php echo count($citas); ?>
                    </h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Próximas</h6>
                    <h3 class="mb-0 reserva-stat-proximas">
                        <?php echo count(array_filter($citas, fn($c) => strtotime($c['fecha']) >= time() && ($c['estado'] === 'pendiente' || $c['estado'] === 'confirmada'))); ?>
                    </h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Completadas</h6>
                    <h3 class="mb-0 reserva-stat-completadas">
                        <?php echo count(array_filter($citas, fn($c) => $c['estado'] === 'completada')); ?>
                    </h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="text-muted">Gasto Total</h6>
                    <h3 class="mb-0 reserva-stat-gasto">
                        €<?php echo number_format(array_sum(array_column($citas, 'precio_final')), 2, ',', '.'); ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
$contenido = ob_get_clean();
include 'plantilla.php';
?>
