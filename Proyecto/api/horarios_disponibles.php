<?php
/**
 * api/horarios_disponibles.php
 * Devuelve slots disponibles para una fecha/barbero/servicio
 */

define('IS_API_REQUEST', true);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../funciones.php';

header('Content-Type: application/json; charset=UTF-8');

if (!$usuario_logueado) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Debes iniciar sesión para consultar disponibilidad.'
    ]);
    exit;
}

$fecha = trim($_GET['fecha'] ?? '');
$dni_barbero = trim($_GET['dni_barbero'] ?? '');
$id_servicio = intval($_GET['id_servicio'] ?? 0);

if (empty($fecha) || empty($dni_barbero) || $id_servicio <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Parámetros incompletos.'
    ]);
    exit;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Fecha no válida.'
    ]);
    exit;
}

$slots = obtener_slots_disponibles_barbero($dni_barbero, $fecha, $id_servicio, $mysqli);

echo json_encode([
    'success' => true,
    'slots' => $slots,
    'message' => empty($slots) ? 'Sin disponibilidad para la selección actual.' : ''
]);
