<?php
/**
 * admin/barberos.php
 * Maneja barberos desde el panel administrativo.
 */



require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../funciones.php';

verificar_autenticacion();
verificar_admin();

$titulo_pagina = 'Gestión de Barberos - ' . APP_NAME;

$error = '';
$exito = '';

// Procesar creación, actualización o eliminación de barbero
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requerir_csrf();

    if (isset($_POST['eliminar'])) {
        $dni = trim($_POST['dni'] ?? '');
        $stmt = $mysqli->prepare("DELETE FROM barberos WHERE DNI_barbero = ?");
        $stmt->bind_param('s', $dni);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $exito = ' Barbero eliminado correctamente.';
        } else {
            $error = 'Error: No se puede eliminar el barbero porque tiene citas asociadas. Elimínalas primero.';
        }
        $stmt->close();
    } else {
        $dni = trim($_POST['dni'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $especialidad = trim($_POST['especialidad'] ?? '');
        $experiencia_anos = intval($_POST['experiencia_anos'] ?? 1);
        $horario_inicio = trim($_POST['horario_inicio'] ?? '09:00');
        $horario_fin = trim($_POST['horario_fin'] ?? '18:00');
        $dias_atiende = trim($_POST['dias_atiende'] ?? 'Lun-Vie');

        // Procesar foto
        $foto_nombre = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $foto_nombre = 'barbero_' . preg_replace('/[^a-zA-Z0-9]/', '_', $dni) . '.' . $ext;
                $ruta_destino = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/equipo/' . $foto_nombre;
                if (!move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino)) {
                    if (!copy($_FILES['foto']['tmp_name'], $ruta_destino)) {
                        $foto_nombre = null;
                    }
                }
            } else {
                $error = 'Formato de foto no válido. Usa JPG, PNG o WEBP.';
            }
        }

        if (empty($dni) || empty($nombre) || empty($apellidos)) {
            $error = 'Por favor completa los campos obligatorios.';
        } elseif (empty($error)) {
            $check = $mysqli->prepare("SELECT DNI_barbero FROM barberos WHERE DNI_barbero = ? LIMIT 1");
            $check->bind_param('s', $dni);
            $check->execute();
            $resultado = $check->get_result();
            $existe = $resultado->num_rows > 0;
            $check->close();

            if ($existe) {
                if ($foto_nombre) {
                    $stmt = $mysqli->prepare("UPDATE barberos SET nombre = ?, apellidos = ?, telefono = ?, especialidad = ?, experiencia_anos = ?, horario_inicio = ?, horario_fin = ?, dias_atiende = ?, foto = ?, activo = 1 WHERE DNI_barbero = ?");
                    $stmt->bind_param('sssssissss', $nombre, $apellidos, $telefono, $especialidad, $experiencia_anos, $horario_inicio, $horario_fin, $dias_atiende, $foto_nombre, $dni);
                } else {
                    $stmt = $mysqli->prepare("UPDATE barberos SET nombre = ?, apellidos = ?, telefono = ?, especialidad = ?, experiencia_anos = ?, horario_inicio = ?, horario_fin = ?, dias_atiende = ?, activo = 1 WHERE DNI_barbero = ?");
                    $stmt->bind_param('sssssisss', $nombre, $apellidos, $telefono, $especialidad, $experiencia_anos, $horario_inicio, $horario_fin, $dias_atiende, $dni);
                }
                $exito = ' Barbero actualizado correctamente.';
            } else {
                $stmt = $mysqli->prepare("INSERT INTO barberos (DNI_barbero, nombre, apellidos, telefono, especialidad, experiencia_anos, horario_inicio, horario_fin, dias_atiende, foto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('sssssissss', $dni, $nombre, $apellidos, $telefono, $especialidad, $experiencia_anos, $horario_inicio, $horario_fin, $dias_atiende, $foto_nombre);
                $exito = ' Barbero creado correctamente.';
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

// Traer los barberos activos para mostrar en la tabla
$barberos = $mysqli->query("SELECT * FROM barberos WHERE activo = 1 ORDER BY nombre")->fetch_all(MYSQLI_ASSOC);

$estilos_adicionales = '<style>
    .admin-barbero-card {
        height: 100%;
    }

    .admin-barbero-meta small {
        display: block;
        margin-bottom: 4px;
    }

    .admin-barbero-actions {
        flex-wrap: wrap;
    }

    .admin-barberos-header h1 {
        color: #000000 !important;
    }

    .admin-barberos-header {
        margin-bottom: 24px;
    }
</style>';

ob_start();
?>

<div class="admin-barberos-header row mb-30">
    <div class="col-md-6">
        <h1 class="mb-0">
            <i class="bi bi-person-badge"></i> Gestión de Barberos
        </h1>
    </div>
    <div class="col-md-6 text-end">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalBarbero" onclick="resetBarberoForm()">
            <i class="bi bi-plus-circle"></i> Nuevo Barbero
        </button>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if (!empty($exito)): ?>
    <div class="alert alert-success"><?php echo $exito; ?></div>
<?php endif; ?>

<!-- LISTA DE BARBEROS -->
<div class="row">
    <?php foreach ($barberos as $barbero): ?>
    <div class="col-md-6 mb-3">
        <div class="card admin-barbero-card">
            <div class="card-body">
                <div class="d-flex align-items-start mb-2">
                    <div class="me-3">
                        <?php if (!empty($barbero['foto'])): ?>
                            <img src="../assets/img/servir_imagen.php?tipo=barbero&archivo=<?php echo urlencode($barbero['foto']); ?>" alt="Foto" class="rounded" style="width:64px;height:64px;object-fit:cover;">
                        <?php else: ?>
                            <div class="rounded bg-secondary d-flex align-items-center justify-content-center text-white" style="width:64px;height:64px;font-size:24px;">
                                <i class="bi bi-person"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-0">
                            <?php echo e($barbero['nombre'] . ' ' . $barbero['apellidos']); ?>
                        </h5>
                        <span class="badge bg-secondary mt-1"><?php echo e($barbero['DNI_barbero']); ?></span>
                    </div>
                </div>
                
                <p class="card-text text-muted mb-2">
                    <i class="bi bi-briefcase"></i> <?php echo e($barbero['especialidad']); ?>
                </p>
                
                <div class="mb-2 admin-barbero-meta">
                    <small><strong>Teléfono:</strong> <?php echo e($barbero['telefono'] ?? '-'); ?></small><br>
                    <small><strong>Experiencia:</strong> <?php echo $barbero['experiencia_anos']; ?> años</small><br>
                    <small><strong>Horario:</strong> <?php echo formatear_hora($barbero['horario_inicio']); ?> - <?php echo formatear_hora($barbero['horario_fin']); ?></small><br>
                    <small><strong>Atiende:</strong> <?php echo e($barbero['dias_atiende']); ?></small>
                </div>
                
                <div class="mt-3 d-flex gap-2 admin-barbero-actions">
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalBarbero" onclick="cargarBarbero('<?php echo htmlspecialchars(json_encode($barbero), ENT_QUOTES, 'UTF-8'); ?>')">
                        <i class="bi bi-pencil"></i> Editar
                    </button>
                    <form method="POST" action="barberos.php" class="d-inline" onsubmit="return confirm('¿Seguro?');">
                        <?php echo csrf_input(); ?>
                        <input type="hidden" name="eliminar" value="1">
                        <input type="hidden" name="dni" value="<?php echo e($barbero['DNI_barbero']); ?>">
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

<!-- MODAL FORMULARIO -->
<div class="modal fade" id="modalBarbero" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tituloModal">Nuevo Barbero</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <?php echo csrf_input(); ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="dni" class="form-label">DNI *</label>
                        <input type="text" class="form-control" id="dni" name="dni" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="foto" class="form-label">Foto</label>
                        <input type="file" class="form-control" id="foto" name="foto" accept="image/jpeg,image/png,image/webp">
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="col-md-6">
                            <label for="apellidos" class="form-label">Apellidos *</label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="telefono" name="telefono">
                    </div>
                    
                    <div class="mb-3">
                        <label for="especialidad" class="form-label">Especialidad</label>
                        <input type="text" class="form-control" id="especialidad" name="especialidad" placeholder="Ej: Cortes degradados">
                    </div>
                    
                    <div class="mb-3">
                        <label for="experiencia_anos" class="form-label">Años de Experiencia</label>
                        <input type="number" class="form-control" id="experiencia_anos" name="experiencia_anos" value="1" min="0">
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="horario_inicio" class="form-label">Horario Inicio</label>
                            <input type="time" class="form-control" id="horario_inicio" name="horario_inicio" value="09:00">
                        </div>
                        <div class="col-md-6">
                            <label for="horario_fin" class="form-label">Horario Fin</label>
                            <input type="time" class="form-control" id="horario_fin" name="horario_fin" value="18:00">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="dias_atiende" class="form-label">Días que Atiende</label>
                        <input type="text" class="form-control" id="dias_atiende" name="dias_atiende" placeholder="Ej: Lun-Vie">
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
function cargarBarbero(datos) {
    const barbero = JSON.parse(datos);
    document.getElementById('dni').value = barbero.DNI_barbero;
    document.getElementById('nombre').value = barbero.nombre;
    document.getElementById('apellidos').value = barbero.apellidos;
    document.getElementById('telefono').value = barbero.telefono || '';
    document.getElementById('especialidad').value = barbero.especialidad || '';
    document.getElementById('experiencia_anos').value = barbero.experiencia_anos;
    document.getElementById('horario_inicio').value = (barbero.horario_inicio || '').substring(0, 5);
    document.getElementById('horario_fin').value = (barbero.horario_fin || '').substring(0, 5);
    document.getElementById('dias_atiende').value = barbero.dias_atiende;
    document.getElementById('tituloModal').textContent = 'Editar Barbero';
}

function resetBarberoForm() {
    document.getElementById('dni').value = '';
    document.getElementById('nombre').value = '';
    document.getElementById('apellidos').value = '';
    document.getElementById('telefono').value = '';
    document.getElementById('especialidad').value = '';
    document.getElementById('experiencia_anos').value = '1';
    document.getElementById('horario_inicio').value = '09:00';
    document.getElementById('horario_fin').value = '18:00';
    document.getElementById('dias_atiende').value = '';
    document.getElementById('foto').value = '';
    document.getElementById('tituloModal').textContent = 'Nuevo Barbero';
}
</script>

<?php
$contenido = ob_get_clean();
include __DIR__ . '/../plantilla.php';
?>
