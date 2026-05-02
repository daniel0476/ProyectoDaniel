<?php
/**
 * mi_perfil.php
 * Editar perfil del usuario
 */

require_once 'config.php';
require_once 'funciones.php';

verificar_autenticacion();

$titulo_pagina = 'Mi Perfil - ' . APP_NAME;

$usuario = obtener_usuario($usuario_id, $mysqli);
$error = '';
$exito = '';

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requerir_csrf();

    $nombre = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $contrasena_actual = trim($_POST['contrasena_actual'] ?? '');
    $contrasena_nueva = trim($_POST['contrasena_nueva'] ?? '');
    $contrasena_confirmar = trim($_POST['contrasena_confirmar'] ?? '');
    
    // Validar datos
    if (empty($nombre) || empty($apellidos)) {
        $error = 'El nombre y apellidos son obligatorios.';
    } elseif (!empty($contrasena_nueva) && empty($contrasena_actual)) {
        $error = 'Debes proporcionar tu contraseña actual para cambiarla.';
    } elseif (!empty($contrasena_nueva) && !verificar_contrasena($contrasena_actual, $usuario['contrasena'])) {
        $error = 'La contraseña actual es incorrecta.';
    } elseif (!empty($contrasena_nueva) && strlen($contrasena_nueva) < 6) {
        $error = 'La nueva contraseña debe tener al menos 6 caracteres.';
    } elseif (!empty($contrasena_nueva) && $contrasena_nueva !== $contrasena_confirmar) {
        $error = 'Las contraseñas nuevas no coinciden.';
    } else {
        // Actualizar perfil
        $nombre_prep = escapar($nombre, $mysqli);
        $apellidos_prep = escapar($apellidos, $mysqli);
        $telefono_prep = escapar($telefono, $mysqli);
        
        $query = "UPDATE usuarios SET nombre = '$nombre_prep', apellidos = '$apellidos_prep', telefono = '$telefono_prep' WHERE ID_usuario = $usuario_id";
        
        if ($mysqli->query($query)) {
            // Actualizar sesión
            $_SESSION['usuario_nombre'] = $nombre;
            
            // Si va a cambiar contraseña
            if (!empty($contrasena_nueva)) {
                // Actualizar contraseña
                $hash_nueva = hashear_contrasena($contrasena_nueva);
                $query_pass = "UPDATE usuarios SET contrasena = '$hash_nueva' WHERE ID_usuario = $usuario_id";
                if ($mysqli->query($query_pass)) {
                    $exito = '✅ Perfil y contraseña actualizados correctamente.';
                } else {
                    $error = 'Error al actualizar la contraseña: ' . $mysqli->error;
                }
            } else {
                $exito = '✅ Perfil actualizado correctamente.';
            }
            
            // Actualizar datos mostrados
            $usuario = obtener_usuario($usuario_id, $mysqli);
        } else {
            $error = 'Error al actualizar el perfil: ' . $mysqli->error;
        }
    }
}

ob_start();
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">
                    <i class="bi bi-person-circle"></i> Mi Perfil
                </h3>
            </div>
            <div class="card-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo e($error); ?></div>
                <?php endif; ?>
                
                <?php if (!empty($exito)): ?>
                    <div class="alert alert-success"><?php echo e($exito); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="mi_perfil.php" class="perfil-form">
                    <?php echo csrf_input(); ?>
                    <!-- INFORMACIÓN PERSONAL -->
                    <h5 class="mb-3 mt-4">
                        <i class="bi bi-person-vcard"></i> Información Personal
                    </h5>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Nombre *</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="nombre" 
                                name="nombre" 
                                value="<?php echo e($usuario['nombre']); ?>"
                                required
                            >
                        </div>
                        
                        <div class="col-md-6">
                            <label for="apellidos" class="form-label">Apellidos *</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="apellidos" 
                                name="apellidos" 
                                value="<?php echo e($usuario['apellidos']); ?>"
                                required
                            >
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input 
                                type="email" 
                                class="form-control perfil-readonly" 
                                id="email" 
                                value="<?php echo e($usuario['email']); ?>"
                                disabled
                            >
                            <small class="text-muted">No se puede cambiar</small>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input 
                                type="tel" 
                                class="form-control" 
                                id="telefono" 
                                name="telefono" 
                                value="<?php echo e($usuario['telefono'] ?? ''); ?>"
                            >
                        </div>
                    </div>
                    
                    <!-- CAMBIAR CONTRASEÑA -->
                    <h5 class="mb-3 mt-5 border-top pt-4">
                        <i class="bi bi-lock"></i> Cambiar Contraseña (Opcional)
                    </h5>
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="contrasena_actual" class="form-label">Contraseña Actual</label>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="contrasena_actual" 
                                name="contrasena_actual"
                                placeholder="Déjalo en blanco si no quieres cambiar la contraseña"
                            >
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="contrasena_nueva" class="form-label">Nueva Contraseña</label>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="contrasena_nueva" 
                                name="contrasena_nueva"
                            >
                        </div>
                        
                        <div class="col-md-6">
                            <label for="contrasena_confirmar" class="form-label">Confirmar Nueva Contraseña</label>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="contrasena_confirmar" 
                                name="contrasena_confirmar"
                            >
                        </div>
                    </div>
                    
                    <!-- INFORMACIÓN DE CUENTA -->
                    <h5 class="mb-3 mt-5 border-top pt-4">
                        <i class="bi bi-info-circle"></i> Información de Cuenta
                    </h5>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Rol</label>
                            <input 
                                type="text" 
                                class="form-control perfil-readonly" 
                                value="<?php echo e(ucfirst($usuario['rol'])); ?>"
                                disabled
                            >
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Miembro Desde</label>
                            <input 
                                type="text" 
                                class="form-control perfil-readonly" 
                                value="<?php echo e(formatear_fecha($usuario['fecha_registro'])); ?>"
                                disabled
                            >
                        </div>
                    </div>
                    
                    <!-- BOTONES -->
                    <div class="d-flex gap-2 justify-content-between mt-5">
                        <a href="index.php" class="btn btn-secondary">
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
</div>

<?php
$contenido = ob_get_clean();
$estilos_adicionales = '<style>
    .perfil-readonly.form-control,
    .perfil-form .form-control:disabled {
        background: rgba(255, 255, 255, 0.16) !important;
        border-color: rgba(255, 255, 255, 0.3) !important;
        color: #f5f5f5 !important;
        -webkit-text-fill-color: #f5f5f5;
        opacity: 1;
        cursor: not-allowed;
    }

    .perfil-form .text-muted {
        color: #d7d7d7 !important;
    }

    .perfil-form .btn-secondary {
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.28);
        color: #f3f3f3;
    }

    .perfil-form .btn-secondary:hover {
        background: rgba(255, 255, 255, 0.2);
        color: #ffffff;
    }
</style>';
include 'plantilla.php';
?>
