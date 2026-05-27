<?php
/**
 * admin/usuarios.php
 * Administración de usuarios en el panel.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../funciones.php';

verificar_autenticacion();
verificar_admin();

$titulo_pagina = 'Gestión de Usuarios - ' . APP_NAME;

$error = '';
$exito = '';

// Procesar las acciones de usuario enviadas desde el formulario
$filtro_rol = trim($_GET['rol'] ?? '');
$filtro_estado = trim($_GET['estado'] ?? '');
$filtro_busqueda = trim($_GET['busqueda'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requerir_csrf();

    if (isset($_POST['eliminar'])) {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0 && $id !== $usuario_id) {
            $query = "UPDATE usuarios SET activo = 0 WHERE ID_usuario = $id";
            if ($mysqli->query($query)) {
                $exito = '✅ Usuario eliminado correctamente.';
            }
        }
    }

    if (isset($_POST['cambiar_rol'])) {
        $id = intval($_POST['id'] ?? 0);
        $rol = trim($_POST['rol'] ?? '');

        if ($id > 0 && in_array($rol, ['cliente', 'admin'], true) && $id !== $usuario_id) {
            $query = "UPDATE usuarios SET rol = '$rol' WHERE ID_usuario = $id";
            if ($mysqli->query($query)) {
                $exito = '✅ Rol actualizado correctamente.';
            }
        }
    }
}

// Preparar la consulta según los filtros aplicados
$query = "SELECT * FROM usuarios WHERE 1=1";

if (!empty($filtro_rol)) {
    $filtro_rol = escapar($filtro_rol, $mysqli);
    $query .= " AND rol = '$filtro_rol'";
}

if (!empty($filtro_estado)) {
    if ($filtro_estado === 'activo') {
        $query .= " AND activo = 1";
    } elseif ($filtro_estado === 'inactivo') {
        $query .= " AND activo = 0";
    }
}

if (!empty($filtro_busqueda)) {
    $filtro_busqueda = escapar($filtro_busqueda, $mysqli);
    $query .= " AND (nombre LIKE '%$filtro_busqueda%' OR apellidos LIKE '%$filtro_busqueda%' OR email LIKE '%$filtro_busqueda%')";
}

$query .= " ORDER BY nombre";

$usuarios = $mysqli->query($query)->fetch_all(MYSQLI_ASSOC);

$estilos_adicionales = '<style>
    .admin-users-summary {
        margin-bottom: 20px;
    }

    .admin-users-summary h1,
    .admin-users-summary p {
        color: #000000 !important;
    }

    .admin-users-summary p strong {
        color: #000000 !important;
    }

    .admin-filters .form-label {
        margin-bottom: 6px;
        color: #ffffff !important;
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

    .admin-filters input[type="text"] {
        background-color: #ffffff !important;
        color: #000000 !important;
        border-color: #d6cec5 !important;
    }

    .admin-users-table td,
    .admin-users-table th {
        vertical-align: middle;
    }

    .admin-users-role {
        min-width: 140px;
    }

    .admin-users-actions {
        min-width: 150px;
        white-space: nowrap;
    }

    .admin-empty-icon {
        font-size: 48px;
        color: #8a8f97;
    }

    .admin-users-role-select {
        max-width: 120px;
        color: #000000 !important;
        background-color: #ffffff !important;
    }

    .admin-users-role-select option {
        color: #000000 !important;
    }

    .admin-users-role-select option[selected] {
        color: #000000 !important;
    }

    .admin-users-table {
        background-color: #ffffff !important;
    }

    .admin-users-table td {
        color: #000000 !important;
    }

    .admin-users-table .text-muted {
        color: #555555 !important;
    }

    .admin-users-table th {
        color: #ffffff !important;
        background-color: rgba(0, 0, 0, 0.5) !important;
    }
</style>';

ob_start();
?>

<div class="mb-30 admin-users-summary">
    <h1 class="mb-20">
        <i class="bi bi-people"></i> Gestión de Usuarios
    </h1>
    <p class="text-muted">Total de usuarios registrados: <strong><?php echo count($usuarios); ?></strong></p>
</div>

<!-- FILTROS -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="usuarios.php" class="row g-3 admin-filters">
            <div class="col-md-3">
                <label for="rol" class="form-label text-white" style="color:#ffffff !important;">Rol</label>
                <select class="form-select" id="rol" name="rol">
                    <option value="">Todos</option>
                    <option value="cliente" <?php echo $filtro_rol === 'cliente' ? 'selected' : ''; ?>>Cliente</option>
                    <option value="admin" <?php echo $filtro_rol === 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="estado" class="form-label text-white" style="color:#ffffff !important;">Estado</label>
                <select class="form-select" id="estado" name="estado">
                    <option value="">Todos</option>
                    <option value="activo" <?php echo $filtro_estado === 'activo' ? 'selected' : ''; ?>>Activo</option>
                    <option value="inactivo" <?php echo $filtro_estado === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                </select>
            </div>
            
            <div class="col-md-4">
                <label for="busqueda" class="form-label text-white" style="color:#ffffff !important;">Buscar</label>
                <input type="text" class="form-control" id="busqueda" name="busqueda" placeholder="Nombre, apellidos o email..." value="<?php echo e($filtro_busqueda); ?>">
            </div>
            
            <div class="col-md-2">
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

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if (!empty($exito)): ?>
    <div class="alert alert-success"><?php echo $exito; ?></div>
<?php endif; ?>

<!-- TABLA DE USUARIOS -->
<div class="card">
    <div class="card-body">
        <?php if (empty($usuarios)): ?>
            <div class="no-data">
                <i class="bi bi-person-x admin-empty-icon"></i>
                <h4 class="mt-3">No hay usuarios registrados</h4>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table admin-users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Citas</th>
                            <th>Registro</th>
                            <th>Estado</th>
                            <th>Rol</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $usuario_citas = []; ?>
                        <?php foreach ($usuarios as $u): ?>
                            <?php 
                            // Contar las citas asociadas al usuario
                            $query_citas = "SELECT COUNT(*) as total FROM citas WHERE ID_usuario = " . $u['ID_usuario'];
                            $citas_count = $mysqli->query($query_citas)->fetch_assoc()['total'];
                            $usuario_citas[$u['ID_usuario']] = $citas_count;
                            ?>
                        <tr>
                            <td><small class="text-muted">#<?php echo $u['ID_usuario']; ?></small></td>
                            <td>
                                <strong><?php echo e($u['nombre'] . ' ' . $u['apellidos']); ?></strong>
                            </td>
                            <td><?php echo e($u['email']); ?></td>
                            <td><?php echo e($u['telefono'] ?? '-'); ?></td>
                            <td>
                                <span class="badge bg-secondary"><?php echo $citas_count; ?></span>
                            </td>
                            <td>
                                <small class="text-muted"><?php echo date('d/m/Y', strtotime($u['fecha_registro'])); ?></small>
                            </td>
                            <td>
                                <?php if ($u['activo']): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="admin-users-role">
                                <form method="POST" action="usuarios.php" onsubmit="return confirm('¿Cambiar rol de este usuario?');">
                                    <?php echo csrf_input(); ?>
                                    <input type="hidden" name="cambiar_rol" value="1">
                                    <input type="hidden" name="id" value="<?php echo (int)$u['ID_usuario']; ?>">
                                    <select class="form-select form-select-sm admin-users-role-select" name="rol" onchange="this.form.submit()">
                                        <option value="cliente" <?php echo $u['rol'] === 'cliente' ? 'selected' : ''; ?>>Cliente</option>
                                        <option value="admin" <?php echo $u['rol'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                </form>
                            </td>
                            <td class="admin-users-actions">
                                <a href="#" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modalDetalles_<?php echo $u['ID_usuario']; ?>">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                                <form method="POST" action="usuarios.php" class="d-inline" onsubmit="return confirm('¿Estás seguro?');">
                                    <?php echo csrf_input(); ?>
                                    <input type="hidden" name="eliminar" value="1">
                                    <input type="hidden" name="id" value="<?php echo (int)$u['ID_usuario']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i> Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php foreach ($usuarios as $u): ?>
    <?php $citas_count = $usuario_citas[$u['ID_usuario']] ?? 0; ?>
    <div class="modal fade" id="modalDetalles_<?php echo $u['ID_usuario']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Información del usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>ID:</strong> <?php echo $u['ID_usuario']; ?></p>
                    <p><strong>Nombre:</strong> <?php echo e($u['nombre'] . ' ' . $u['apellidos']); ?></p>
                    <p><strong>Email:</strong> <?php echo e($u['email']); ?></p>
                    <p><strong>Teléfono:</strong> <?php echo e($u['telefono'] ?? '-'); ?></p>
                    <p><strong>Total de Citas:</strong> <?php echo $citas_count; ?></p>
                    <p><strong>Registered:</strong> <?php echo e(formatear_fecha($u['fecha_registro'])); ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
    </div>
</div>

<div class="mt-3">
    <a href="dashboard.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver al Dashboard
    </a>
</div>

<?php
$contenido = ob_get_clean();
include __DIR__ . '/../plantilla.php';
?>
