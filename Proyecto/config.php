<?php
/*
 * config.php
 * ==========
 * Archivo de configuración principal del proyecto.
 * Se encarga de:
 *   - Conectar a la base de datos remota (InfinityFree).
 *   - Definir constantes generales (nombre, URL, timeout de sesión).
 *   - Iniciar la sesión de PHP y controlar su expiración.
 *   - Poner a disposición variables globales de sesión (usuario_logueado, etc.).
 */

// Activamos la visualización de errores para facilitar el desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// ---------------------------------------------------------------
// DATOS DE CONEXIÓN A LA BASE DE DATOS (InfinityFree)
// ---------------------------------------------------------------
define('DB_HOST', 'sql101.infinityfree.com');
define('DB_USER', 'if0_42014852');
define('DB_PASS', 'AN4jfXvdh43gpmr');
define('DB_NAME', 'if0_42014852_barberia');

// ---------------------------------------------------------------
// PARÁMETROS DE LA APLICACIÓN
// ---------------------------------------------------------------
// Nombre comercial del sitio
define('APP_NAME', 'Barbería Barbers');
// Indica si se debe forzar HTTPS (a false porque InfinityFree ya lo maneja)
define('USE_HTTPS', false);
// Tiempo máximo de inactividad antes de cerrar la sesión (1 hora = 3600 segundos)
define('SESSION_TIMEOUT', 3600);
// Algoritmo usado para hash de tokens internos (NO para contraseñas, esas usan bcrypt)
define('HASH_ALGORITHM', 'sha256');
// URL pública del sitio (usada para redirecciones y assets)
define('APP_URL', 'https://cjbarberia.infinityfreeapp.com');

// ---------------------------------------------------------------
// CONEXIÓN A MYSQL
// ---------------------------------------------------------------
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Si hay error de conexión, detenemos todo y mostramos el mensaje
if ($mysqli->connect_error) {
    die('Error de conexión: ' . $mysqli->connect_error);
}

// Forzamos el charset a UTF-8 para evitar problemas con acentos y ñ
$mysqli->set_charset("utf8mb4");

// ---------------------------------------------------------------
// GESTIÓN DE SESIÓN
// ---------------------------------------------------------------
// Iniciamos sesión si todavía no se ha hecho (evita duplicados)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Comprobamos si la sesión ha expirado por inactividad
if (isset($_SESSION['ultimo_acceso'])) {
    // Si ha pasado más tiempo del permitido, cerramos la sesión
    if (time() - $_SESSION['ultimo_acceso'] > SESSION_TIMEOUT) {
        // Detectamos si quien llama es una API (devuelve JSON en lugar de redirigir)
        $es_api = defined('IS_API_REQUEST') && IS_API_REQUEST === true;
        $login_url = APP_URL . '/login.php?sesion_expirada=1';
        // Destruimos la sesión completamente
        session_destroy();
        $_SESSION = array();

        if ($es_api) {
            // Respuesta para peticiones AJAX/API
            http_response_code(401);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success' => false,
                'message' => 'La sesión ha expirado. Inicia sesión de nuevo.'
            ]);
        } else {
            // Redirección normal para páginas web
            header('Location: ' . $login_url);
        }
        exit;
    }
}
// Actualizamos la marca de tiempo del último acceso
$_SESSION['ultimo_acceso'] = time();

// ---------------------------------------------------------------
// VARIABLES GLOBALES DE SESIÓN
// ---------------------------------------------------------------
// Se usan en todas las páginas para saber si hay alguien logueado
$usuario_logueado = isset($_SESSION['usuario_id']);
$usuario_id = $_SESSION['usuario_id'] ?? null;
$usuario_rol = $_SESSION['usuario_rol'] ?? null;
$es_admin = ($usuario_rol === 'admin');
?>
