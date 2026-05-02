<?php
/**
 * registro.php
 * Registro de nuevos usuarios
 */

require_once 'config.php';
require_once 'funciones.php';

$titulo_pagina = 'Registro - ' . APP_NAME;

// Si ya está logueado, redirigir
if ($usuario_logueado) {
    header('Location: index.php');
    exit;
}

$error = '';
$exito = '';

// Procesar registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requerir_csrf();

    $nombre = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');
    $contrasena_confirmar = trim($_POST['contrasena_confirmar'] ?? '');
    
    // Validar datos
    if (empty($nombre) || empty($apellidos) || empty($email) || empty($contrasena)) {
        $error = 'Por favor, completa todos los campos requeridos.';
    } elseif (!validar_email($email)) {
        $error = 'El email no es válido.';
    } elseif (strlen($contrasena) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($contrasena !== $contrasena_confirmar) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        // Verificar que el email no existe
        $email_prep = escapar($email, $mysqli);
        $query_check = "SELECT ID_usuario FROM usuarios WHERE email = '$email_prep'";
        $resultado = $mysqli->query($query_check);
        
        if ($resultado->num_rows > 0) {
            $error = 'El email ya está registrado. <a href="login.php">¿Inicia sesión aquí?</a>';
        } else {
            // Registrar nuevo usuario
            $nombre_prep = escapar($nombre, $mysqli);
            $apellidos_prep = escapar($apellidos, $mysqli);
            $telefono_prep = escapar($telefono, $mysqli);
            $hash_contrasena = hashear_contrasena($contrasena);
            
            $query = "INSERT INTO usuarios (nombre, apellidos, email, telefono, contrasena, rol) 
                      VALUES ('$nombre_prep', '$apellidos_prep', '$email_prep', '$telefono_prep', '$hash_contrasena', 'cliente')";
            
            if ($mysqli->query($query)) {
                // Automáticamente loguearse
                $usuario_nuevo = obtener_usuario_por_email($email, $mysqli);

                session_regenerate_id(true);
                $_SESSION['usuario_id'] = $usuario_nuevo['ID_usuario'];
                $_SESSION['usuario_nombre'] = $usuario_nuevo['nombre'];
                $_SESSION['usuario_email'] = $usuario_nuevo['email'];
                $_SESSION['usuario_rol'] = $usuario_nuevo['rol'];
                
                registrar_acceso($usuario_nuevo['ID_usuario'], $mysqli);
                
                redirigir_con_mensaje('index.php', '✅ ¡Bienvenido! Tu cuenta ha sido creada exitosamente.', 'success');
            } else {
                $error = 'Error al registrar: ' . $mysqli->error;
            }
        }
    }
}

ob_start();
?>

<div class="row justify-content-center login-page-wrap">
    <div class="col-lg-6 col-md-8">
        <div class="login-container registro-container">
            <div class="login-header">
                <img src="<?php echo APP_URL; ?>/assets/img/logoBarberia.png" alt="Logo" class="login-logo">
                <h1><?php echo APP_NAME; ?></h1>
                <p>Crear cuenta de cliente</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="registro.php" id="registroForm" novalidate>
                <?php echo csrf_input(); ?>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nombre" class="form-label">Nombre *</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="nombre" 
                            name="nombre" 
                            required
                            value="<?php echo e($_POST['nombre'] ?? ''); ?>"
                        >
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="apellidos" class="form-label">Apellidos *</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="apellidos" 
                            name="apellidos" 
                            required
                            value="<?php echo e($_POST['apellidos'] ?? ''); ?>"
                        >
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email *</label>
                    <input 
                        type="email" 
                        class="form-control" 
                        id="email" 
                        name="email" 
                        placeholder="tu@email.com"
                        required
                        value="<?php echo e($_POST['email'] ?? ''); ?>"
                    >
                </div>
                
                <div class="mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input 
                        type="tel" 
                        class="form-control" 
                        id="telefono" 
                        name="telefono"
                        value="<?php echo e($_POST['telefono'] ?? ''); ?>"
                    >
                </div>
                
                <div class="mb-3">
                    <label for="contrasena" class="form-label">Contraseña *</label>
                    <input 
                        type="password" 
                        class="form-control" 
                        id="contrasena" 
                        name="contrasena" 
                        placeholder="Mínimo 6 caracteres"
                        minlength="6"
                        required
                    >
                    <small class="registro-help">Usa mayúsculas, minúsculas y números para mayor seguridad</small>
                </div>
                
                <div class="mb-3">
                    <label for="contrasena_confirmar" class="form-label">Confirmar Contraseña *</label>
                    <input 
                        type="password" 
                        class="form-control" 
                        id="contrasena_confirmar" 
                        name="contrasena_confirmar" 
                        placeholder="Escribe la contraseña de nuevo"
                        required
                    >
                </div>
                
                <div class="mb-3 form-check registro-terms-wrap">
                    <input 
                        class="form-check-input" 
                        type="checkbox" 
                        id="terminos" 
                        required
                    >
                    <label class="form-check-label" for="terminos">
                        Acepto los <a href="#" target="_blank">términos y condiciones</a> *
                    </label>
                </div>
                
                <button type="submit" class="btn btn-login w-100 mb-2">
                    <i class="bi bi-person-check"></i> Crear Cuenta
                </button>
            </form>

            <div class="register-link">
                ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
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

    .registro-container {
        max-width: 680px;
        margin: 0 auto;
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

    .registro-container .form-control {
        border: 1px solid rgba(255, 255, 255, 0.2);
        background: rgba(255, 255, 255, 0.07);
        color: #ffffff;
        border-radius: 10px;
        padding: 10px;
        margin-bottom: 0;
        font-size: 14px;
    }

    .registro-container .form-control::placeholder {
        color: rgba(255, 255, 255, 0.6);
    }

    .registro-container .form-control:focus {
        border-color: #f1f1f1;
        box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.14);
        background: rgba(255, 255, 255, 0.1);
        color: #ffffff;
    }

    .registro-help {
        display: inline-block;
        margin-top: 8px;
        color: rgba(235, 235, 235, 0.82) !important;
    }

    .registro-terms-wrap .form-check-label,
    .registro-terms-wrap .form-check-label a {
        color: #f1f1f1;
    }

    .registro-terms-wrap .form-check-input {
        background-color: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.35);
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

    @media (max-width: 768px) {
        .login-container {
            padding: 28px 22px;
        }
    }
</style>';
$scripts_adicionales = '<script>
    (function () {
        const form = document.getElementById("registroForm");
        if (!form) {
            return;
        }

        const nombre = document.getElementById("nombre");
        const apellidos = document.getElementById("apellidos");
        const email = document.getElementById("email");
        const contrasena = document.getElementById("contrasena");
        const contrasenaConfirmar = document.getElementById("contrasena_confirmar");
        const terminos = document.getElementById("terminos");

        function limpiarMensaje() {
            this.setCustomValidity("");
        }

        function mensajeInvalido(input) {
            if (input.validity.valueMissing) {
                if (input === nombre) return "Por favor, escribe tu nombre.";
                if (input === apellidos) return "Por favor, escribe tus apellidos.";
                if (input === email) return "Por favor, escribe tu correo electrónico.";
                if (input === contrasena) return "Por favor, escribe una contraseña.";
                if (input === contrasenaConfirmar) return "Por favor, confirma la contraseña.";
                if (input === terminos) return "Debes aceptar los términos y condiciones.";
                return "Este campo es obligatorio.";
            }

            if (input === email && input.validity.typeMismatch) {
                return "Introduce un email válido (por ejemplo: nombre@correo.com).";
            }

            if (input === contrasena && input.validity.tooShort) {
                return "La contraseña debe tener al menos 6 caracteres.";
            }

            if (input === contrasenaConfirmar && contrasena.value !== contrasenaConfirmar.value) {
                return "Las contraseñas no coinciden.";
            }

            return "Revisa este campo.";
        }

        [nombre, apellidos, email, contrasena, contrasenaConfirmar, terminos].forEach(function (input) {
            if (!input) return;
            input.addEventListener("input", limpiarMensaje);
            input.addEventListener("change", limpiarMensaje);
            input.addEventListener("invalid", function () {
                input.setCustomValidity(mensajeInvalido(input));
            });
        });

        form.addEventListener("submit", function (event) {
            contrasenaConfirmar.setCustomValidity("");
            if (contrasena.value !== contrasenaConfirmar.value) {
                contrasenaConfirmar.setCustomValidity("Las contraseñas no coinciden.");
            }

            if (!form.checkValidity()) {
                event.preventDefault();
                form.reportValidity();
            }
        });
    })();
</script>';
include 'plantilla.php';
?>
