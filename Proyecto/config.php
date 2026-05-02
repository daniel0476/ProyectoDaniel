<?php
/**
 * config.php
 * Configuración de conexión a base de datos y variables globales
 * IMPORTANTE: Cambiar credenciales según tu entorno
 */

// ============================================
// CONFIGURACIÓN DE BASE DE DATOS
// ============================================
define('DB_HOST', 'localhost');      // Host del servidor MySQL
define('DB_USER', 'root');           // Usuario MySQL (cambia si es diferente)
define('DB_PASS', '');               // Contraseña MySQL
define('DB_NAME', 'barberia');       // Nombre de la base de datos

// ============================================
// CONFIGURACIÓN DE LA APLICACIÓN
// ============================================
define('APP_NAME', 'Barbería Barbers');
define('APP_URL', 'http://localhost/barberia');  // URL base del proyecto
define('SESSION_TIMEOUT', 3600);                 // Timeout de sesión (1 hora)

// ============================================
// CONFIGURACIÓN DE SEGURIDAD
// ============================================
define('USE_HTTPS', false);  // Cambiar a true en producción
define('HASH_ALGORITHM', 'sha256');

// ============================================
// CONECTAR A LA BASE DE DATOS
// ============================================
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar conexión
if ($mysqli->connect_error) {
    die('Error de conexión: ' . $mysqli->connect_error);
}

// Establecer charset UTF-8
$mysqli->set_charset("utf8mb4");

// ============================================
// INICIAR SESIÓN
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar timeout de sesión
if (isset($_SESSION['ultimo_acceso'])) {
    if (time() - $_SESSION['ultimo_acceso'] > SESSION_TIMEOUT) {
        $es_api = defined('IS_API_REQUEST') && IS_API_REQUEST === true;
        $login_url = APP_URL . '/login.php?sesion_expirada=1';
        session_destroy();
        $_SESSION = array();

        if ($es_api) {
            http_response_code(401);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success' => false,
                'message' => 'La sesión ha expirado. Inicia sesión de nuevo.'
            ]);
        } else {
            header('Location: ' . $login_url);
        }
        exit;
    }
}
$_SESSION['ultimo_acceso'] = time();

// ============================================
// VARIABLES GLOBALES
// ============================================
$usuario_logueado = isset($_SESSION['usuario_id']);
$usuario_id = $_SESSION['usuario_id'] ?? null;
$usuario_rol = $_SESSION['usuario_rol'] ?? null;
$es_admin = ($usuario_rol === 'admin');
?>
