<?php
/**
 * admin/citas.php
 * Lista de citas y edición de estados desde el panel.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../funciones.php';

verificar_autenticacion();
verificar_admin();

$titulo_pagina = 'Gestión de Citas - ' . APP_NAME;

// Filtramos citas y preparamos los cambios de estado
$filtro_estado = trim($_GET['estado'] ?? '');
$filtro_fecha = trim($_GET['fecha'] ?? '');

// Procesar eliminación de cita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_cita'])) {
    requerir_csrf();

    $id = intval($_POST['id'] ?? 0);
    if ($id > 0) {
        // Crear carpeta de logs si no existe y registrar la acción
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $admin_id = $usuario_id ?? ($_SESSION['usuario_id'] ?? 0);
        $detalle = 'Eliminada cita ID ' . $id . ' por usuario ' . $admin_id . ' en ' . date('Y-m-d H:i:s') . PHP_EOL;
        @file_put_contents($logDir . '/eliminaciones_citas.log', $detalle, FILE_APPEND | LOCK_EX);

        $query = "DELETE FROM citas WHERE ID_cita = $id";
        $mysqli->query($query);
    }
}

// Procesar cambio de estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado'])) {
    requerir_csrf();

    $id = intval($_POST['id'] ?? 0);
    $estado = trim($_POST['nuevo_estado'] ?? '');

    if ($id > 0 && in_array($estado, ['pendiente', 'confirmada', 'completada', 'cancelada'], true)) {
        $query = "UPDATE citas SET estado = '$estado' WHERE ID_cita = $id";
        $mysqli->query($query);
    }
}

// Preparamos la consulta con los filtros elegidos
$query = "SELECT * FROM citas WHERE 1=1";

if (!empty($filtro_estado)) {
    $filtro_estado = escapar($filtro_estado, $mysqli);
    $query .= " AND estado = '$filtro_estado'";
}

if (!empty($filtro_fecha)) {
    $filtro_fecha = escapar($filtro_fecha, $mysqli);
    $query .= " AND fecha = '$filtro_fecha'";
}

$query .= " ORDER BY fecha DESC, hora DESC";

$citas = $mysqli->query($query)->fetch_all(MYSQLI_ASSOC);

// Enriquecer cada cita con datos del usuario, barbero y servicio
foreach ($citas as &$cita) {
    // Obtener usuario
    $usuario = obtener_usuario($cita['ID_usuario'], $mysqli);
    $cita['cliente_nombre'] = $usuario['nombre'] ?? 'N/A';
    $cita['cliente_email'] = $usuario['email'] ?? '';
    $cita['cliente_telefono'] = $usuario['telefono'] ?? '';
    
    // Obtener barbero
    $barbero = obtener_barbero($cita['DNI_barbero'], $mysqli);
    $cita['barbero_nombre'] = $barbero['nombre'] ?? 'N/A';
    $cita['barbero_apellidos'] = $barbero['apellidos'] ?? '';
    
    // Obtener servicio
    $servicio = obtener_servicio($cita['ID_servicio'], $mysqli);
    $cita['servicio_nombre'] = $servicio['nombre'] ?? 'N/A';
    $cita['duracion_minutos'] = $servicio['duracion_minutos'] ?? 0;
}

$estilos_adicionales = '<style>
    .admin-filters .form-label {
        margin-bottom: 6px;
    }

    .admin-filters label[for="estado"],
    .admin-filters label[for="fecha"] {
        color: #ffffff !important;
    }

    .admin-citas-header h1 {
        color: #000000 !important;
    }

    .admin-citas-header {
        margin-bottom: 24px;
    }

    .admin-citas-table td,
    .admin-citas-table th {
        vertical-align: middle;
        color: #000000 !important;
    }

    .admin-citas-table th {
        background-color: #f8f9fa !important;
    }

    .admin-citas-table td {
        font-size: 14px;
        background-color: #ffffff !important;
    }

    .admin-citas-table small,
    .admin-citas-table .text-muted {
        color: #000000 !important;
    }

    .admin-citas-table strong {
        color: #000000 !important;
    }

    .admin-citas-state {
        min-width: 130px;
    }

    .admin-citas-state-select {
        max-width: 120px;
        color: #000000 !important;
        background-color: #ffffff !important;
    }

    .admin-filters .form-select,
    .admin-filters .form-select option {
        color: #000000 !important;
        background-color: #ffffff !important;
    }

    .admin-filters .form-select option[selected],
    .admin-filters .form-select option:checked {
        color: #000000 !important;
        background-color: #ffffff !important;
    }

    .admin-filters input[type="date"] {
        background-color: #ffffff !important;
        color: #000000 !important;
        border-color: #d6cec5 !important;
    }

    .admin-filters input[type="date"]::-webkit-datetime-edit,
    .admin-filters input[type="date"]::-webkit-input-placeholder {
        color: #000000 !important;
    }

    .admin-citas-actions {
        min-width: 80px;
        text-align: center;
    }
</style>';

ob_start();
?>

<div class="admin-citas-header row mb-30">
    <div class="col-md-8">
        <h1 class="mb-0"><i class="bi bi-calendar-check"></i> Gestión de Citas</h1>
    </div>
</div>

<!-- FILTROS -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="citas.php" class="row g-3 admin-filters">
            <div class="col-md-4">
                <label for="estado" class="form-label">Estado</label>
                <select class="form-select" id="estado" name="estado">
                    <option value="">Todos</option>
                    <option value="pendiente" <?php echo $filtro_estado === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                    <option value="confirmada" <?php echo $filtro_estado === 'confirmada' ? 'selected' : ''; ?>>Confirmada</option>
                    <option value="completada" <?php echo $filtro_estado === 'completada' ? 'selected' : ''; ?>>Completada</option>
                    <option value="cancelada" <?php echo $filtro_estado === 'cancelada' ? 'selected' : ''; ?>>Cancelada</option>
                </select>
            </div>
            
            <div class="col-md-4">
                <label for="fecha" class="form-label">Fecha</label>
                <input type="date" class="form-control" id="fecha" name="fecha" value="<?php echo $filtro_fecha; ?>">
            </div>
            
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- TABLA -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm admin-citas-table">
                <thead>
                    <tr>
                        <th>Fecha/Hora</th>
                        <th>Cliente</th>
                        <th>Barbero</th>
                        <th>Servicio</th>
                        <th>Precio</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($citas as $cita): ?>
                    <tr>
                        <td>
                            <strong><?php echo date('d/m/Y', strtotime($cita['fecha'])); ?></strong><br>
                            <small><?php echo formatear_hora($cita['hora']); ?></small>
                        </td>
                        <td>
                            <strong><?php echo e($cita['cliente_nombre'] ?? 'N/A'); ?></strong><br>
                            <small><?php echo e($cita['cliente_email'] ?? ''); ?></small>
                        </td>
                        <td><?php echo e($cita['barbero_nombre'] . ' ' . ($cita['barbero_apellidos'] ?? '')); ?></td>
                        <td><?php echo e($cita['servicio_nombre']); ?></td>
                        <td>€<?php echo number_format($cita['precio_final'], 2); ?></td>
                        <td class="admin-citas-state">
                            <form method="POST" action="citas.php<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . e($_SERVER['QUERY_STRING']) : ''; ?>" onsubmit="return confirm('¿Cambiar estado de la cita?');">
                                <?php echo csrf_input(); ?>
                                <input type="hidden" name="cambiar_estado" value="1">
                                <input type="hidden" name="id" value="<?php echo (int)$cita['ID_cita']; ?>">
                                <select class="form-select form-select-sm admin-citas-state-select" name="nuevo_estado" onchange="this.form.submit()">
                                    <option value="pendiente" <?php echo $cita['estado'] === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                    <option value="confirmada" <?php echo $cita['estado'] === 'confirmada' ? 'selected' : ''; ?>>Confirmada</option>
                                    <option value="completada" <?php echo $cita['estado'] === 'completada' ? 'selected' : ''; ?>>Completada</option>
                                    <option value="cancelada" <?php echo $cita['estado'] === 'cancelada' ? 'selected' : ''; ?>>Cancelada</option>
                                </select>
                            </form>
                        </td>
                        <td class="admin-citas-actions">
                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modalDetalles_<?php echo $cita['ID_cita']; ?>">
                                <i class="bi bi-eye"></i>
                            </button>
                            <form method="POST" action="citas.php<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . e($_SERVER['QUERY_STRING']) : ''; ?>" style="display:inline;" onsubmit="return confirm('⚠️ ¿Eliminar esta cita definitivamente? Esta acción no se puede deshacer.');">
                                <?php echo csrf_input(); ?>
                                <input type="hidden" name="eliminar_cita" value="1">
                                <input type="hidden" name="id" value="<?php echo (int)$cita['ID_cita']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php foreach ($citas as $cita): ?>
    <div class="modal fade" id="modalDetalles_<?php echo $cita['ID_cita']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalle de la cita</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Cliente:</strong> <?php echo e($cita['cliente_nombre']); ?></p>
                    <p><strong>Email:</strong> <?php echo e($cita['cliente_email']); ?></p>
                    <p><strong>Teléfono:</strong> <?php echo e($cita['cliente_telefono'] ?? '-'); ?></p>
                    <hr>
                    <p><strong>Barbero:</strong> <?php echo e($cita['barbero_nombre'] . ' ' . $cita['barbero_apellidos']); ?></p>
                    <p><strong>Servicio:</strong> <?php echo e($cita['servicio_nombre']); ?></p>
                    <p><strong>Fecha:</strong> <?php echo e(formatear_fecha($cita['fecha'])); ?></p>
                    <p><strong>Hora:</strong> <?php echo e(formatear_hora($cita['hora'])); ?></p>
                    <p><strong>Duración:</strong> <?php echo $cita['duracion_minutos']; ?> minutos</p>
                    <p><strong>Precio:</strong> €<?php echo number_format($cita['precio_final'], 2); ?></p>
                    <?php if ($cita['notas_cliente']): ?>
                        <p><strong>Notas del cliente:</strong><br><?php echo nl2br(e($cita['notas_cliente'])); ?></p>
                    <?php endif; ?>
                    <?php if ($cita['notas_admin']): ?>
                        <p><strong>Notas del admin:</strong><br><?php echo nl2br(e($cita['notas_admin'])); ?></p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<div class="mt-3">
    <a href="dashboard.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<!-- Modal de confirmación para eliminar cita -->
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="citas.php">
                <?php echo csrf_input(); ?>
                <input type="hidden" name="eliminar_cita" value="1">
                <input type="hidden" name="id" id="modalEliminar_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Eliminar esta cita definitivamente? Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.btn-eliminar').forEach(function(btn){
        btn.addEventListener('click', function(){
            var id = this.getAttribute('data-id');
            var input = document.getElementById('modalEliminar_id');
            if (input) input.value = id;
        });
    });
});
</script>

<?php
$contenido = ob_get_clean();
include __DIR__ . '/../plantilla.php';
?>
