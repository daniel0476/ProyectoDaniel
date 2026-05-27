<?php
/*
 * admin/servicios.php
 * ===================
 * Panel de gestión de servicios de la barbería.
 * Permite crear, editar y eliminar (soft-delete) servicios,
 * incluyendo nombre, descripción, precio, duración y foto.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../funciones.php';

// Solo administradores pueden acceder
verificar_autenticacion();
verificar_admin();

$titulo_pagina = 'Gestión de Servicios - ' . APP_NAME;

$error = '';
$exito = '';

// ---------------------------------------------------------------
// PROCESAR CREACIÓN, ACTUALIZACIÓN O ELIMINACIÓN DE SERVICIO
// ---------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requerir_csrf();

    // Eliminación lógica (soft-delete): activo = 0
    if (isset($_POST['eliminar'])) {
        $id = intval($_POST['id'] ?? 0);
        $query = "UPDATE servicios SET activo = 0 WHERE ID_servicio = $id";
        if ($mysqli->query($query)) {
            $exito = ' Servicio eliminado.';
        }
    } else {
        // Crear o actualizar servicio
        $id = intval($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = floatval(str_replace(',', '.', trim($_POST['precio'] ?? '0')));
        $duracion_minutos = intval($_POST['duracion_minutos'] ?? 30);

        // Procesar la foto subida
        $foto_nombre = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $foto_nombre = 'servicio_' . $id . '_' . time() . '.' . $ext;
                $ruta_destino = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/servicios/' . $foto_nombre;
                if (!move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino)) {
                    if (!copy($_FILES['foto']['tmp_name'], $ruta_destino)) {
                        $foto_nombre = null;
                    }
                }
            } else {
                $error = 'Formato de foto no válido. Usa JPG, PNG o WEBP.';
            }
        }

        // Validar campos obligatorios
        if (empty($nombre)) {
            $error = 'El nombre del servicio es obligatorio.';
        } elseif ($precio <= 0) {
            $error = 'El precio debe ser mayor que 0.';
        } elseif ($duracion_minutos <= 0) {
            $error = 'La duración debe ser mayor que 0.';
        } elseif (empty($error)) {
            if ($id > 0) {
                // ACTUALIZAR servicio existente
                if ($foto_nombre) {
                    $query = "UPDATE servicios SET nombre = ?, descripcion = ?, precio = ?, duracion_minutos = ?, foto = ? WHERE ID_servicio = ?";
                    $stmt = $mysqli->prepare($query);
                    $stmt->bind_param('ssdisi', $nombre, $descripcion, $precio, $duracion_minutos, $foto_nombre, $id);
                } else {
                    $query = "UPDATE servicios SET nombre = ?, descripcion = ?, precio = ?, duracion_minutos = ? WHERE ID_servicio = ?";
                    $stmt = $mysqli->prepare($query);
                    $stmt->bind_param('ssdii', $nombre, $descripcion, $precio, $duracion_minutos, $id);
                }
                $exito = ' Servicio actualizado correctamente.';
            } else {
                // CREAR nuevo servicio
                $query = "INSERT INTO servicios (nombre, descripcion, precio, duracion_minutos, foto) VALUES (?, ?, ?, ?, ?)";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param('ssdis', $nombre, $descripcion, $precio, $duracion_minutos, $foto_nombre);
                $exito = ' Servicio creado correctamente.';
            }

            if ($stmt->execute()) {
                $_POST = [];
            } else {
                $error = 'Error: ' . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// ---------------------------------------------------------------
// OBTENER LISTA DE SERVICIOS ACTIVOS
// ---------------------------------------------------------------
$servicios = $mysqli->query("SELECT * FROM servicios WHERE activo = 1 ORDER BY nombre")->fetch_all(MYSQLI_ASSOC);

$estilos_adicionales = '<style>
    .admin-servicios-header h1 {
        color: #000000 !important;
    }

    .admin-servicios-header {
        margin-bottom: 24px;
    }

    .admin-servicio-card {
        height: 100%;
    }

    .admin-servicio-precio {
        font-size: 22px;
        font-weight: 700;
        color: #e39a56;
    }
</style>';

ob_start();
?>

<div class="admin-servicios-header row mb-30">
    <div class="col-md-6">
        <h1 class="mb-0">
            <i class="bi bi-scissors"></i> Gestión de Servicios
        </h1>
    </div>
    <div class="col-md-6 text-end">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalServicio" onclick="resetServicioForm()">
            <i class="bi bi-plus-circle"></i> Nuevo Servicio
        </button>
    </div>
</div>

<!-- Mensajes de error/éxito -->
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if (!empty($exito)): ?>
    <div class="alert alert-success"><?php echo $exito; ?></div>
<?php endif; ?>

<!-- LISTA DE SERVICIOS (tarjetas) -->
<div class="row">
    <?php foreach ($servicios as $servicio): ?>
    <div class="col-md-4 mb-3">
        <div class="card admin-servicio-card">
            <div class="card-body">
                <!-- Foto del servicio (o icono si no tiene) -->
                <div class="text-center mb-3">
                    <?php if (!empty($servicio['foto'])): ?>
                        <img src="../assets/img/servir_imagen.php?tipo=servicio&archivo=<?php echo urlencode($servicio['foto']); ?>" alt="Foto" class="rounded" style="width:80px;height:80px;object-fit:cover;">
                    <?php else: ?>
                        <div class="rounded bg-secondary d-inline-flex align-items-center justify-content-center text-white" style="width:80px;height:80px;font-size:32px;">
                            <i class="bi bi-scissors"></i>
                        </div>
                    <?php endif; ?>
                </div>
                
                <h5 class="card-title text-center">
                    <?php echo e($servicio['nombre']); ?>
                </h5>
                <p class="card-text text-center text-muted small">
                    <?php echo e($servicio['descripcion']); ?>
                </p>
                
                <div class="text-center mb-3">
                    <span class="admin-servicio-precio">€<?php echo number_format($servicio['precio'], 2, ',', '.'); ?></span>
                    <span class="badge bg-secondary ms-2"><?php echo $servicio['duracion_minutos']; ?> min</span>
                </div>
                
                <!-- Botones de acción -->
                <div class="text-center">
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalServicio" onclick="cargarServicio('<?php echo htmlspecialchars(json_encode($servicio), ENT_QUOTES, 'UTF-8'); ?>')">
                        <i class="bi bi-pencil"></i> Editar
                    </button>
                    <form method="POST" action="servicios.php" class="d-inline" onsubmit="return confirm('Seguro?');">
                        <?php echo csrf_input(); ?>
                        <input type="hidden" name="eliminar" value="1">
                        <input type="hidden" name="id" value="<?php echo (int)$servicio['ID_servicio']; ?>">
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="bi bi-trash"></i> Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- MODAL FORMULARIO (Crear/Editar Servicio) -->
<div class="modal fade" id="modalServicio" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModalServicio">Nuevo Servicio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <?php echo csrf_input(); ?>
                <input type="hidden" name="id" id="servicio_id" value="0">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre *</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="foto" class="form-label">Foto</label>
                        <input type="file" class="form-control" id="foto" name="foto" accept="image/jpeg,image/png,image/webp">
                    </div>
                    
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="2"></textarea>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="precio" class="form-label">Precio (€) *</label>
                            <input type="number" class="form-control" id="precio" name="precio" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label for="duracion_minutos" class="form-label">Duración (min) *</label>
                            <input type="number" class="form-control" id="duracion_minutos" name="duracion_minutos" min="5" required>
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

<!-- JavaScript para cargar datos en el modal al editar -->
<script>
function cargarServicio(datos) {
    const servicio = JSON.parse(datos);
    document.getElementById('servicio_id').value = servicio.ID_servicio;
    document.getElementById('nombre').value = servicio.nombre;
    document.getElementById('descripcion').value = servicio.descripcion || '';
    document.getElementById('precio').value = servicio.precio;
    document.getElementById('duracion_minutos').value = servicio.duracion_minutos;
    document.getElementById('tituloModalServicio').textContent = 'Editar Servicio';
}

function resetServicioForm() {
    document.getElementById('servicio_id').value = '0';
    document.getElementById('nombre').value = '';
    document.getElementById('descripcion').value = '';
    document.getElementById('precio').value = '';
    document.getElementById('duracion_minutos').value = '30';
    document.getElementById('foto').value = '';
    document.getElementById('tituloModalServicio').textContent = 'Nuevo Servicio';
}
</script>

<?php
$contenido = ob_get_clean();
include __DIR__ . '/../plantilla.php';
?>
