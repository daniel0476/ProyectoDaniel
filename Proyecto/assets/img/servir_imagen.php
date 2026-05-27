<?php
$tipo = $_GET['tipo'] ?? '';
$archivo = $_GET['archivo'] ?? '';

$base = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/';
$directorios = [
    'barbero' => $base . 'equipo/',
    'servicio' => $base . 'servicios/'
];

$defaults = [
    'barbero' => $base . 'equipo/juanPerez.jpg',
    'servicio' => $base . 'servicios/cortePelo.png'
];

$ruta = '';
if (isset($directorios[$tipo]) && !empty($archivo)) {
    $archivo_limpio = basename($archivo);
    $ruta_candidata = $directorios[$tipo] . $archivo_limpio;
    if (file_exists($ruta_candidata)) {
        $ruta = $ruta_candidata;
    }
}

if (empty($ruta)) {
    $ruta = $defaults[$tipo] ?? '';
}

if (empty($ruta) || !file_exists($ruta)) {
    http_response_code(404);
    exit;
}

$ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
$mimes = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'webp' => 'image/webp'
];
$mime = $mimes[$ext] ?? 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($ruta));
header('Cache-Control: max-age=86400');
readfile($ruta);
