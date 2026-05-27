<?php
/*
 * logout.php
 * ==========
 * Cierra la sesión del usuario de forma segura.
 * Destruye los datos de sesión en el servidor,
 * elimina la cookie de sesión del navegador
 * y redirige al login con un parámetro de confirmación.
 */

require_once 'config.php';

// Destruir todos los datos de sesión almacenados en el servidor
session_destroy();

// Vaciar el array de sesión en memoria por si acaso
$_SESSION = array();

// Eliminar la cookie de sesión del navegador del usuario
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    // Establecer la cookie con una fecha en el pasado para que expire
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Redirigir a la página de login con indicador de logout exitoso
header('Location: login.php?logout=1');
exit;
?>
