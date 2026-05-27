<?php
/**
 * check_foto.php
 * Script de diagnóstico para verificar rutas de imágenes.
 * Ayuda a detectar si DOCUMENT_ROOT apunta al lugar correcto
 * y si los archivos realmente existen en el servidor.
 */

// Muestra la raíz del servidor (punto de entrada de la web)
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";

// Muestra la ruta completa a la carpeta de imágenes de barberos
echo "Ruta equipo: " . $_SERVER['DOCUMENT_ROOT'] . '/assets/img/equipo/' . "<br>";

// Escanea el directorio y lista todos los archivos encontrados
echo "Archivos en equipo:<br>";
$dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/equipo/';
if (is_dir($dir)) {
    $files = scandir($dir);
    foreach ($files as $f) {
        if ($f !== '.' && $f !== '..') {
            echo "- $f (" . filetype($dir . $f) . ")<br>";
        }
    }
} else {
    // Si el directorio no existe, lo indica claramente
    echo "DIRECTORIO NO ENCONTRADO: $dir<br>";
}

// Muestra la ruta física de este mismo archivo
echo "Ruta check_foto.php: " . __FILE__ . "<br>";

// Muestra la URL relativa que se usó para acceder a esta página
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "<br>";
