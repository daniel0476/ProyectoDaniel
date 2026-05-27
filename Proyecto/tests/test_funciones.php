<?php
/**
 * test_funciones.php
 * Pruebas básicas para las funciones comunes del proyecto.
 */

// Sesión necesaria para algunas funciones.
$_SESSION = [];

// 1) Validación de texto seguro.
assert(e('<script>hola</script>') === '&lt;script&gt;hola&lt;/script&gt;');
assert(e(null) === '');

// 2) Validación de email.
assert(validar_email('user@example.com') === 'user@example.com');
assert(validar_email('mal@correo') === false);

// 3) Hash y verificación de contraseñas.
$hash = hashear_contrasena('secret');
assert(password_verify('secret', $hash));
assert(verificar_contrasena('secret', $hash) === true);
assert(verificar_contrasena('wrong', $hash) === false);

// 4) CSRF.
$token = obtener_csrf_token();
assert(is_string($token) && strlen($token) === 64);
assert(strpos(csrf_input(), 'csrf_token') !== false);
$_POST['csrf_token'] = $token;
assert(verificar_csrf() === true);

// 5) Conversión de horas y minutos.
assert(hora_a_minutos('08:30') === 510);
assert(minutos_a_hora(510) === '08:30');
assert(minutos_a_hora(5) === '00:05');

// 6) Formateo de fecha y hora.
assert(formatear_fecha('2026-05-26') === 'Martes, 26 de Mayo de 2026');
assert(formatear_hora('2026-05-26 09:15:00') === '9:15 AM');

// 7) Bloques de slots con citas ocupadas.
$slots = generar_slots_disponibles(
    '09:00',
    '10:00',
    30,
    [
        ['hora' => '09:30', 'duracion_minutos' => 30]
    ]
);
assert(is_array($slots));
assert(count($slots) === 1);
assert($slots[0]['inicio'] === '09:00');
assert($slots[0]['fin'] === '09:30');

// 8) Alerta HTML.
$html_alert = alerta('Prueba', 'success');
assert(strpos($html_alert, 'alert alert-success') !== false);
assert(strpos($html_alert, 'Prueba') !== false);

// 9) Mensajes flash.
$_SESSION['mensaje'] = 'Mensaje de prueba';
$_SESSION['tipo_mensaje'] = 'warning';
assert(strpos(mostrar_mensaje(), 'alert alert-warning') !== false);
assert(!isset($_SESSION['mensaje']));
assert(!isset($_SESSION['tipo_mensaje']));

// 10) Fecha de cancelación mínima.
// Sin DB, se prueba que devuelve el valor por defecto del sistema.
class DummyConnection { public function query($q) { return false; } }
$conexion = new DummyConnection();
assert(obtener_horas_cancelacion($conexion) === 24);
