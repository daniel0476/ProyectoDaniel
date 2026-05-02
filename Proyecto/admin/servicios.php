<?php
/**
 * admin/servicios.php
 * CRUD de servicios
 */

require_once '../config.php';
require_once '../funciones.php';

verificar_autenticacion();
verificar_admin();

$titulo_pagina = 'Gestión de Servicios - ' . APP_NAME;

$error = '';
$exito = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requerir_csrf();

    if (isset($_POST['eliminar'])) {
        $id = intval($_POST['id'] ?? 0);
        $query = "UPDATE servicios SET activo = 0 WHERE ID_servicio = $id";
        if ($mysqli->query($query)) {
            $exito = '✅ Servicio eliminado.';
        }
    } else {
        $id = intval($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = floatval($_POST['precio'] ?? 0);
        $duracion = intval($_POST['duracion_minutos'] ?? 30);

        if (empty($nombre) || $precio <= 0) {
            $error = 'Nombre y precio son obligatorios.';
        } else {
            $nombre_prep = escapar($nombre, $mysqli);
            $descripcion_prep = escapar($descripcion, $mysqli);

            if ($id > 0) {
                $query = "UPDATE servicios SET nombre = '$nombre_prep', descripcion = '$descripcion_prep', precio = $precio, duracion_minutos = $duracion WHERE ID_servicio = $id";
                $exito = '✅ Servicio actualizado.';
            } else {
                $query = "INSERT INTO servicios (nombre, descripcion, precio, duracion_minutos) VALUES ('$nombre_prep', '$descripcion_prep', $precio, $duracion)";
                $exito = '✅ Servicio creado.';
            }

            if ($mysqli->query($query)) {
                $_POST = [];
            } else {
                $error = 'Error: ' . $mysqli->error;
            }
        }
    }
}

// Obtener servicios
$servicios = $mysqli->query("SELECT * FROM servicios WHERE activo = 1 ORDER BY nombre")->fetch_all(MYSQLI_ASSOC);

$estilos_adicionales = '<style>
    .admin-servicios-table td,
    .admin-servicios-table th {
        vertical-align: middle;
        color: #000000 !important;
    }

    .admin-servicios-table th {
        background-color: #f8f9fa !important;
    }

    .admin-servicios-table td {
        background-color: #ffffff !important;
    }

    .admin-servicios-desc {
        min-width: 260px;
    }

    .admin-servicios-actions {
        min-width: 110px;
        white-space: nowrap;
        text-align: center;
    }

    .admin-servicios-header h1 {
        color: #000000 !important;
    }

    .admin-servicios-header {
        margin-bottom: 24px;
    }
</style>';

ob_start();
?>

<div class="admin-servicios-header row mb-30">
    <div class="col-md-6">
        <h1 class="mb-0"><i class="bi bi-scissors"></i> Gestión de Servicios</h1>
    </div>
    <div class="col-md-6 text-end">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalServicio">
            <i class="bi bi-plus-circle"></i> Nuevo Servicio
        </button>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if (!empty($exito)): ?>
    <div class="alert alert-success"><?php echo $exito; ?></div>
<?php endif; ?>

<!-- TABLA -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table admin-servicios-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Precio</th>
                        <th>Duración</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($servicios as $svc): ?>
                    <tr>
                        <td><strong><?php echo e($svc['nombre']); ?></strong></td>
                        <td class="admin-servicios-desc"><?php echo e(substr($svc['descripcion'] ?? '-', 0, 50)); ?></td>
                        <td>€<?php echo number_format($svc['precio'], 2); ?></td>
                        <td><?php echo $svc['duracion_minutos']; ?> min</td>
                        <td class="admin-servicios-actions">
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalServicio" onclick="cargarServicio('<?php echo htmlspecialchars(json_encode($svc), ENT_QUOTES, 'UTF-8'); ?>')">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="servicios.php" class="d-inline" onsubmit="return confirm('¿Seguro?');">
                                <?php echo csrf_input(); ?>
                                <input type="hidden" name="eliminar" value="1">
                                <input type="hidden" name="id" value="<?php echo (int)$svc['ID_servicio']; ?>">
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

<!-- MODAL -->
<div class="modal fade" id="modalServicio" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nuevo Servicio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <?php echo csrf_input(); ?>
                <input type="hidden" id="id" name="id" value="0">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="2"></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="precio" class="form-label">Precio (€) *</label>
                            <input type="number" class="form-control" id="precio" name="precio" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label for="duracion_minutos" class="form-label">Duración (min)</label>
                            <input type="number" class="form-control" id="duracion_minutos" name="duracion_minutos" value="30">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="dashboard.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<script>
function cargarServicio(datos) {
    const servicio = JSON.parse(datos);
    document.getElementById('id').value = servicio.ID_servicio;
    document.getElementById('nombre').value = servicio.nombre;
    document.getElementById('descripcion').value = servicio.descripcion || '';
    document.getElementById('precio').value = servicio.precio;
    document.getElementById('duracion_minutos').value = servicio.duracion_minutos;
}
</script>

<?php
$contenido = ob_get_clean();
include '../plantilla.php';
?>
