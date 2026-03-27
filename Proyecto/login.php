<?php
/**
 * ╔══════════════════════════════════════════════════════════════════════════╗
 * ║                      🟦 BLOQUE 1: BACKEND BÁSICO                        ║
 * ║                       login.php - Autenticación                         ║
 * ╚══════════════════════════════════════════════════════════════════════════╝
 * 
 * Propósito:
 *   - Mostrar formulario de login
 *   - Procesar credenciales
 *   - Crear sesión segura
 *   - Redirigir al destino
 * 
 * Acceso: http://localhost/barberia/login.php
 */

require_once 'config.php';
require_once 'funciones.php';

// Si ya está logueado, redirigir a index.php (Bloque 2)
if ($usuario_logueado) {
    header('Location: index.php');
    exit;
}

// ═══════════════════════════════════════════════════════════════════════════
// PROCESAMIENTO DE FORMULARIO (POST)
// ═══════════════════════════════════════════════════════════════════════════

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';
    
    // Validaciones
    if (empty($email) || empty($contrasena)) {
        $error = '❌ Por favor, completa todos los campos.';
    } elseif (!validar_email($email)) {
        $error = '❌ El email no es válido.';
    } else {
        // Buscar usuario en BD
        $usuario = obtener_usuario_por_email($email);
        
        if (!$usuario) {
            $error = '❌ Email o contraseña incorrectos.';
        } else {
            // Verificar contraseña
            if (verificar_contrasena($contrasena, $usuario['contrasena'])) {
                // ✅ Credenciales correctas: crear sesión
                $_SESSION['usuario_id'] = $usuario['ID_usuario'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_apellidos'] = $usuario['apellidos'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['usuario_rol'] = $usuario['rol'];
                $_SESSION['usuario_activo'] = $usuario['activo'];
                
                // Registrar acceso en BD
                registrar_acceso($usuario['ID_usuario']);
                
                // Redirigir según rol
                if ($usuario['rol'] === 'admin') {
                    header('Location: admin/dashboard.php');
                } else {
                    header('Location: index.php');
                }
                exit;
            } else {
                $error = '❌ Email o contraseña incorrectos.';
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Barbería</title>
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            max-width: 420px;
            width: 100%;
            margin: 0 20px;
        }
        
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        
        .login-header h1 {
            font-size: 28px;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .login-header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .login-body {
            padding: 40px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            display: block;
        }
        
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: none;
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            color: white;
            cursor: pointer;
            width: 100%;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        
        .register-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }
        
        .register-link a:hover {
            color: var(--secondary);
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .demo-credentials {
            background: #f5f5f5;
            border-left: 4px solid var(--primary);
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 13px;
        }
        
        .demo-credentials strong {
            color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- HEADER -->
            <div class="login-header">
                <h1>✂️ BARBERÍA</h1>
                <p>Sistema de Reservas</p>
            </div>
            
            <!-- BODY -->
            <div class="login-body">
                <!-- MENSAJE DE ERROR -->
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <!-- FORMULARIO -->
                <form method="POST">
                    <div class="form-group">
                        <label for="email" class="form-label">📧 Correo Electrónico</label>
                        <input 
                            type="email" 
                            class="form-control" 
                            id="email" 
                            name="email" 
                            placeholder="tu@email.com"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="contrasena" class="form-label">🔐 Contraseña</label>
                        <input 
                            type="password" 
                            class="form-control" 
                            id="contrasena" 
                            name="contrasena" 
                            placeholder="Tu contraseña"
                            required
                        >
                    </div>
                    
                    <button type="submit" class="btn btn-login">Iniciar Sesión</button>
                </form>
                
                <!-- ENLACE REGISTRO -->
                <div class="register-link">
                    ¿No tienes cuenta? <a href="registro.php">Regístrate</a>
                </div>
                
                <!-- CREDENCIALES DE PRUEBA -->
                <div class="demo-credentials">
                    <strong>📝 Cuentas de Prueba:</strong>
                    <br><br>
                    <strong>Cliente:</strong>
                    <br>Email: <code>juan@email.com</code>
                    <br>Pass: <code>1234</code>
                    <br><br>
                    <strong>Admin:</strong>
                    <br>Email: <code>admin@email.com</code>
                    <br>Pass: <code>1234</code>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
