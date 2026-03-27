<?php
/**
 * ╔══════════════════════════════════════════════════════════════════════════╗
 * ║                      🟦 BLOQUE 1: BACKEND BÁSICO                        ║
 * ║                  config.php - Configuración Central                     ║
 * ╚══════════════════════════════════════════════════════════════════════════╝
 * 
 * Propósito:
 *   - Conexión a la base de datos MySQL
 *   - Gestión de sesiones
 *   - Variables globales
 *   - Configuración de timezone
 * 
 * Uso: require_once 'config.php' al inicio de cada página
 */

// ═══════════════════════════════════════════════════════════════════════════
// 1. CONFIGURACIÓN DE BASE DE DATOS
// ═══════════════════════════════════════════════════════════════════════════

define('DB_HOST', 'localhost');      // Servidor MySQL
define('DB_USER', 'root');           // Usuario MySQL (cambiar si es diferente)
define('DB_PASS', '');               // Contraseña MySQL (sin contraseña en XAMPP por defecto)
define('DB_NAME', 'barberia');       // Nombre de la base de datos

// ═══════════════════════════════════════════════════════════════════════════
// 2. CONFIGURACIÓN DE SESIÓN
// ═══════════════════════════════════════════════════════════════════════════

define('SESSION_TIMEOUT', 3600);     // 1 hora en segundos (3600 seg)
define('SESSION_NAME', 'barberia_session');

// ═══════════════════════════════════════════════════════════════════════════
// 3. CONFIGURACIÓN DE TIMEZONE
// ═══════════════════════════════════════════════════════════════════════════

date_default_timezone_set('Europe/Madrid');  // Zona horaria España

// ═══════════════════════════════════════════════════════════════════════════
// 4. INICIAR SESIÓN
// ═══════════════════════════════════════════════════════════════════════════

session_name(SESSION_NAME);
session_start();

// Verificar timeout de sesión (si no accede en 1 hora = logout automático)
if (isset($_SESSION['ultimo_acceso'])) {
    if (time() - $_SESSION['ultimo_acceso'] > SESSION_TIMEOUT) {
        session_destroy();
        $_SESSION = array();
    }
}

// Actualizar último acceso
$_SESSION['ultimo_acceso'] = time();

// ═══════════════════════════════════════════════════════════════════════════
// 5. CONECTAR A BASE DE DATOS
// ═══════════════════════════════════════════════════════════════════════════

// Usar mysqli POO (Programación Orientada a Objetos)
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar si hay error de conexión
if ($mysqli->connect_error) {
    die('Error de conexión a BD: ' . $mysqli->connect_error);
}

// Establecer charset UTF-8 para caracteres especiales (ñ, á, etc.)
$mysqli->set_charset('utf8mb4');

// ═══════════════════════════════════════════════════════════════════════════
// 6. VARIABLES GLOBALES DE USUARIO
// ═══════════════════════════════════════════════════════════════════════════

// Verificar si el usuario está logueado
$usuario_logueado = isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);

// Si está logueado, obtener datos de sesión
if ($usuario_logueado) {
    $usuario_id = $_SESSION['usuario_id'];
    $usuario_nombre = $_SESSION['usuario_nombre'];
    $usuario_email = $_SESSION['usuario_email'];
    $usuario_rol = $_SESSION['usuario_rol'];
    $es_admin = ($usuario_rol === 'admin');
} else {
    $usuario_id = null;
    $usuario_nombre = null;
    $usuario_email = null;
    $usuario_rol = null;
    $es_admin = false;
}

// ═══════════════════════════════════════════════════════════════════════════
// 7. CONFIGURACIÓN DE ERRORES (Producción vs Desarrollo)
// ═══════════════════════════════════════════════════════════════════════════

// En desarrollo: mostrar errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// En producción: ocultar errores (comentar las líneas anteriores)
// error_reporting(0);
// ini_set('display_errors', 0);

?>
