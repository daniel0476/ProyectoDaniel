<?php
/**
 * login.php
 * Sistema de login de usuarios
 */

require_once 'config.php';

// Si ya está logueado, redirigir al inicio
if ($usuario_logueado) {
    header('Location: index.php');
    exit;
}

require_once 'funciones.php';

$error = '';
$exito = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requerir_csrf();

    $email = trim($_POST['email'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');
    
    // Validar datos
    if (empty($email) || empty($contrasena)) {
        $error = 'Por favor, completa todos los campos.';
    } elseif (!validar_email($email)) {
        $error = 'El email no es válido.';
    } else {
        // Buscar usuario
        $usuario = obtener_usuario_por_email($email, $mysqli);
        
        if ($usuario && verificar_contrasena($contrasena, $usuario['contrasena'])) {
            // Login exitoso
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = $usuario['ID_usuario'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['usuario_rol'] = $usuario['rol'];
            
            // Registrar acceso
            registrar_acceso($usuario['ID_usuario'], $mysqli);
            
            // Redirigir según rol
            if ($usuario['rol'] === 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: index.php');
            }
            exit;
        } else {
            $error = 'Email o contraseña incorrectos.';
        }
    }
}

// Verificar si la sesión expiró
$sesion_expirada = isset($_GET['sesion_expirada']);

$titulo_pagina = 'Login - ' . APP_NAME;

ob_start();

?>
<div class="row justify-content-center login-page-wrap">
    <div class="col-lg-5 col-md-7">
        <div class="login-container">
        <div class="login-header">
            <img src="<?php echo APP_URL; ?>/assets/img/logoBarberia.png" alt="Logo" class="login-logo">
            <h1><?php echo APP_NAME; ?></h1>
            <p>Sistema de Reservas</p>
        </div>
        
        <?php if ($sesion_expirada): ?>
            <div class="alert alert-warning" role="alert">
                Tu sesión ha expirado. Por favor, inicia sesión de nuevo.
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo e($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
            <?php echo csrf_input(); ?>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input 
                    type="email" 
                    class="form-control" 
                    id="email" 
                    name="email" 
                    placeholder="tu@email.com"
                    value="<?php echo e($_POST['email'] ?? ''); ?>"
                    required
                >
            </div>
            
            <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña</label>
                <input 
                    type="password" 
                    class="form-control" 
                    id="contrasena" 
                    name="contrasena" 
                    placeholder="••••••••"
                    required
                >
            </div>
            
            <button type="submit" class="btn btn-login">Iniciar Sesión</button>
        </form>
        
        <div class="demo-creds">
            <strong>🧪 Credenciales de prueba:</strong>
            <p><strong>Cliente:</strong> juan@email.com / 1234</p>
            <p><strong>Admin:</strong> admin@email.com / 1234</p>
        </div>
        
        <div class="register-link">
            ¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a>
        </div>
        </div>
    </div>
</div>

<?php
$contenido = ob_get_clean();
$estilos_adicionales = '<style>
    .login-page-wrap {
        margin-top: 14px;
    }

    .login-container {
        background: rgba(22, 22, 22, 0.92);
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 16px;
        box-shadow: 0 14px 30px rgba(0, 0, 0, 0.45);
        padding: 40px;
        width: 100%;
        color: #f2f2f2;
    }

    .login-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .login-logo {
        width: 72px;
        height: 72px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        margin-bottom: 14px;
    }

    .login-header h1 {
        color: #f1f1f1;
        font-size: 34px;
        font-weight: 700;
        letter-spacing: 1px;
        margin-bottom: 10px;
    }

    .login-header p {
        color: #d3d3d3;
        font-size: 16px;
    }

    .form-label {
        color: #f1f1f1;
        font-weight: 600;
        letter-spacing: 0.4px;
    }

    .login-container .form-control {
        border: 1px solid rgba(255, 255, 255, 0.2);
        background: rgba(255, 255, 255, 0.07);
        color: #ffffff;
        border-radius: 10px;
        padding: 10px;
        margin-bottom: 15px;
        font-size: 14px;
    }

    .login-container .form-control::placeholder {
        color: rgba(255, 255, 255, 0.6);
    }

    .login-container .form-control:focus {
        border-color: #f1f1f1;
        box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.14);
        background: rgba(255, 255, 255, 0.1);
        color: #ffffff;
    }

    .btn-login {
        background: linear-gradient(135deg, #f4f4f4 0%, #b7b7b7 100%);
        border: none;
        color: #171717;
        padding: 10px;
        border-radius: 999px;
        font-size: 18px;
        font-weight: 600;
        cursor: pointer;
        width: 100%;
        transition: transform 0.2s;
    }

    .btn-login:hover {
        transform: translateY(-2px);
        color: #171717;
        box-shadow: 0 8px 16px rgba(255, 255, 255, 0.12);
    }

    .register-link {
        text-align: center;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.12);
    }

    .register-link a {
        color: #f1f1f1;
        text-decoration: none;
        font-weight: 600;
        letter-spacing: 0.4px;
    }

    .register-link a:hover {
        text-decoration: underline;
    }

    .demo-creds {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.12);
        padding: 15px;
        border-radius: 10px;
        margin-top: 20px;
        font-size: 13px;
    }

    .demo-creds p {
        margin: 5px 0;
    }
</style>';

include 'plantilla.php';
