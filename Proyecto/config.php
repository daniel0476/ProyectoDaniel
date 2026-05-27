<?php
/**
 * config.php
 * Carga la configuración general y conecta con la base de datos.
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Datos de la base de datos (valores integrados para la copia del proyecto)
define('DB_HOST', 'sql101.infinityfree.com');
define('DB_USER', 'if0_42014852');
define('DB_PASS', 'AN4jfXvdh43gpmr');
define('DB_NAME', 'if0_42014852_barberia');

// Parámetros de la aplicación
define('APP_NAME', 'Barbería Barbers');

define('USE_HTTPS', false);

define('SESSION_TIMEOUT', 3600);                 // duración de sesión en segundos

define('HASH_ALGORITHM', 'sha256');

// URL base de la aplicación
define('APP_URL', 'https://cjbarberia.infinityfreeapp.com');

// Conexión a la base de datos
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar conexión
if ($mysqli->connect_error) {
    die('Error de conexión: ' . $mysqli->connect_error);
}

// Establecer charset UTF-8
$mysqli->set_charset("utf8mb4");

// Iniciar la sesión si aún no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Controlar el tiempo de vida de la sesión
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

// Variables de sesión usadas en todo el proyecto
$usuario_logueado = isset($_SESSION['usuario_id']);
$usuario_id = $_SESSION['usuario_id'] ?? null;
$usuario_rol = $_SESSION['usuario_rol'] ?? null;
$es_admin = ($usuario_rol === 'admin');
?>
