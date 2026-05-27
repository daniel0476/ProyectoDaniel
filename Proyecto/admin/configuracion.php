<?php
/**
 * admin/configuracion.php
 * Ajustes generales de la barbería.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../funciones.php';

verificar_autenticacion();
verificar_admin();

$titulo_pagina = 'Configuración - ' . APP_NAME;

$error = '';
$exito = '';

$config = obtener_config_sistema($mysqli);

// Actualizar la configuración cuando envían el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requerir_csrf();

    $nombre_barberia = escapar(trim($_POST['nombre_barberia'] ?? ''), $mysqli);
    $email_barberia = escapar(trim($_POST['email_barberia'] ?? ''), $mysqli);
    $telefono_barberia = escapar(trim($_POST['telefono_barberia'] ?? ''), $mysqli);
    $direccion = escapar(trim($_POST['direccion'] ?? ''), $mysqli);
    $ciudad = escapar(trim($_POST['ciudad'] ?? ''), $mysqli);
    $horario_apertura = trim($_POST['horario_apertura'] ?? '09:00');
    $horario_cierre = trim($_POST['horario_cierre'] ?? '18:00');
    $duracion_slot = intval($_POST['duracion_slot_minutos'] ?? 30);
    $tiempo_preparacion = intval($_POST['tiempo_preparacion_minutos'] ?? 5);
    $aviso_cancelacion = intval($_POST['aviso_cancelacion_horas'] ?? 24);
    
    $query = "UPDATE configuracion_sistema SET 
             nombre_barberia = '$nombre_barberia',
             email_barberia = '$email_barberia',
             telefono_barberia = '$telefono_barberia',
             direccion = '$direccion',
             ciudad = '$ciudad',
             horario_apertura = '$horario_apertura',
             horario_cierre = '$horario_cierre',
             duracion_slot_minutos = $duracion_slot,
             tiempo_preparacion_minutos = $tiempo_preparacion,
             aviso_cancelacion_horas = $aviso_cancelacion
             WHERE ID_config = 1";
    
    if ($mysqli->query($query)) {
        $exito = ' Configuración actualizada correctamente.';
        $config = obtener_config_sistema($mysqli);
    } else {
        $error = 'Error: ' . $mysqli->error;
    }
}

$estilos_adicionales = '<style>
    .admin-config-page-title {
        color: #000000 !important;
    }
</style>';

ob_start();
?>

<h1 class="mb-30 admin-config-page-title">
    <i class="bi bi-gear"></i> Configuración del Sistema
</h1>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if (!empty($exito)): ?>
    <div class="alert alert-success"><?php echo $exito; ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Configuración General</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <?php echo csrf_input(); ?>
                    <!-- INFORMACIÓN DE LA BARBERÍA -->
                    <h6 class="mb-3 admin-config-section-title">
                        <i class="bi bi-shop"></i> Información de la Barbería
                    </h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nombre_barberia" class="form-label">Nombre de la Barbería</label>
                            <input type="text" class="form-control" id="nombre_barberia" name="nombre_barberia" value="<?php echo e($config['nombre_barberia']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="ciudad" class="form-label">Ciudad</label>
                            <input type="text" class="form-control" id="ciudad" name="ciudad" value="<?php echo e($config['ciudad']); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="direccion" class="form-label">Dirección</label>
                        <input type="text" class="form-control" id="direccion" name="direccion" value="<?php echo e($config['direccion']); ?>">
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email_barberia" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email_barberia" name="email_barberia" value="<?php echo e($config['email_barberia']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="telefono_barberia" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="telefono_barberia" name="telefono_barberia" value="<?php echo e($config['telefono_barberia']); ?>">
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <!-- HORARIOS -->
                    <h6 class="mb-3 admin-config-section-title">
                        <i class="bi bi-clock"></i> Horarios
                    </h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="horario_apertura" class="form-label">Horario de Apertura</label>
                            <input type="time" class="form-control" id="horario_apertura" name="horario_apertura" value="<?php echo $config['horario_apertura']; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="horario_cierre" class="form-label">Horario de Cierre</label>
                            <input type="time" class="form-control" id="horario_cierre" name="horario_cierre" value="<?php echo $config['horario_cierre']; ?>">
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <!-- CONFIGURACIÓN DE CITAS -->
                    <h6 class="mb-3 admin-config-section-title">
                        <i class="bi bi-calendar-event"></i> Configuración de Citas
                    </h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="duracion_slot_minutos" class="form-label">Duración de Slots (minutos)</label>
                            <input type="number" class="form-control" id="duracion_slot_minutos" name="duracion_slot_minutos" value="<?php echo $config['duracion_slot_minutos']; ?>" min="5">
                            <small class="text-muted">Intervalos para agendar citas</small>
                        </div>
                        <div class="col-md-4">
                            <label for="tiempo_preparacion_minutos" class="form-label">Tiempo de Preparación (min)</label>
                            <input type="number" class="form-control" id="tiempo_preparacion_minutos" name="tiempo_preparacion_minutos" value="<?php echo $config['tiempo_preparacion_minutos']; ?>" min="0">
                            <small class="text-muted">Entre una cita y otra</small>
                        </div>
                        <div class="col-md-4">
                            <label for="aviso_cancelacion_horas" class="form-label">Aviso Cancelación (horas)</label>
                            <input type="number" class="form-control" id="aviso_cancelacion_horas" name="aviso_cancelacion_horas" value="<?php echo $config['aviso_cancelacion_horas']; ?>" min="1">
                            <small class="text-muted">Tiempo mínimo para cancelar</small>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <!-- BOTONES -->
                    <div class="d-flex gap-2 justify-content-between">
                        <a href="dashboard.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- INFORMACIÓN ADICIONAL -->
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle"></i> Información del Sistema
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Nombre de la Aplicación</small>
                    <p class="mb-0"><strong><?php echo APP_NAME; ?></strong></p>
                </div>
                
                <div class="mb-3">
                    <small class="text-muted">URL Base</small>
                    <p class="mb-0"><strong><?php echo e(APP_URL); ?></strong></p>
                </div>
                
                <div class="mb-3">
                    <small class="text-muted">PHP Version</small>
                    <p class="mb-0"><strong><?php echo phpversion(); ?></strong></p>
                </div>
                
                <div>
                    <small class="text-muted">Servidor</small>
                    <p class="mb-0"><strong><?php echo e($_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido'); ?></strong></p>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-lightbulb"></i> Tips
                </h6>
            </div>
            <div class="card-body admin-config-help">
                <p class="mb-2">
                    💡 Recuerda cambiar los horarios de tu barbería según tus disponibilidades.
                </p>
                <p class="mb-2">
                    💡 El tiempo de preparación se suma entre citas para evitar solapamientos.
                </p>
                <p>
                    💡 El aviso de cancelación es el tiempo mínimo que clientes deben dejar para cancelar.
                </p>
            </div>
        </div>
    </div>
</div>

<?php
$contenido = ob_get_clean();
include __DIR__ . '/../plantilla.php';
?>
