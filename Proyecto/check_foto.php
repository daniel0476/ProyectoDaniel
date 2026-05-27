<?php
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Ruta equipo: " . $_SERVER['DOCUMENT_ROOT'] . '/assets/img/equipo/' . "<br>";
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
    echo "DIRECTORIO NO ENCONTRADO: $dir<br>";
}
echo "Ruta servir_imagen.php: " . __FILE__ . "<br>";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "<br>";
