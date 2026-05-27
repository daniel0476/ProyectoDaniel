<?php
/**
 * servir_imagen.php
 * Proxy que sirve imágenes desde carpetas protegidas o muestra un fallback.
 * Si la imagen solicitada no existe, devuelve una imagen por defecto.
 * Si no hay ninguna disponible, responde con 404.
 */

// Tipo de imagen: 'barbero' o 'servicio'
$tipo = $_GET['tipo'] ?? '';
// Nombre del archivo de imagen (ej: barbero_12345678A.png)
$archivo = $_GET['archivo'] ?? '';

// Carpeta raíz donde están las imágenes del proyecto
$base = $_SERVER['DOCUMENT_ROOT'] . '/assets/img/';
// Mapa de tipos a subcarpetas
$directorios = [
    'barbero' => $base . 'equipo/',
    'servicio' => $base . 'servicios/'
];

// Imágenes por defecto si no se ha subido ninguna personalizada
$defaults = [
    'barbero' => $base . 'equipo/juanPerez.jpg',
    'servicio' => $base . 'servicios/cortePelo.png'
];

// 1. Intentar con la imagen solicitada por el usuario
$ruta = '';
if (isset($directorios[$tipo]) && !empty($archivo)) {
    $archivo_limpio = basename($archivo); // elimina posibles rutas maliciosas
    $ruta_candidata = $directorios[$tipo] . $archivo_limpio;
    if (file_exists($ruta_candidata)) {
        $ruta = $ruta_candidata;
    }
}

// 2. Si no se encontró, usar la imagen por defecto
if (empty($ruta)) {
    $ruta = $defaults[$tipo] ?? '';
}

// 3. Si aún no hay ruta válida, devolver 404
if (empty($ruta) || !file_exists($ruta)) {
    http_response_code(404);
    exit;
}

// 4. Determinar el MIME type según la extensión del archivo
$ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
$mimes = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'webp' => 'image/webp'
];
$mime = $mimes[$ext] ?? 'application/octet-stream';

// 5. Enviar cabeceras y volcar el contenido de la imagen
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($ruta));
header('Cache-Control: max-age=86400'); // caché de 24 horas
readfile($ruta);
