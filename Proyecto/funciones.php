<?php
/**
 * funciones.php
 * Funciones auxiliares globales
 */

// ============================================
// FUNCIONES DE SEGURIDAD
// ============================================

/**
 * Verificar si el usuario está autenticado
 * Si no, redirige a login
 */
function verificar_autenticacion() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

/**
 * Verificar si el usuario es administrador
 */
function verificar_admin() {
    if (($_SESSION['usuario_rol'] ?? null) !== 'admin') {
        die('Acceso denegado. Requiere permisos de administrador.');
    }
}

/**
 * Escapar salida HTML
 */
function e($valor) {
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Hashear contraseña
 */
function hashear_contrasena($contrasena) {
    return password_hash($contrasena, PASSWORD_BCRYPT);
}

/**
 * Verificar contraseña
 */
function verificar_contrasena($contrasena, $hash) {
    return password_verify($contrasena, $hash);
}

/**
 * Escapar entrada para SQL
 */
function escapar($texto, $conexion) {
    return $conexion->real_escape_string($texto);
}

/**
 * Validar email
 */
function validar_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Obtener o crear token CSRF de sesión
 */
function obtener_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Campo oculto CSRF para formularios
 */
function csrf_input() {
    return '<input type="hidden" name="csrf_token" value="' . e(obtener_csrf_token()) . '">';
}

/**
 * Validar token CSRF para peticiones POST
 */
function verificar_csrf() {
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

/**
 * Exigir token CSRF válido
 */
function requerir_csrf() {
    if (!verificar_csrf()) {
        http_response_code(403);
        die('Petición no válida. Recarga la página e inténtalo de nuevo.');
    }
}

// ============================================
// FUNCIONES DE BASE DE DATOS
// ============================================

/**
 * Obtener usuario por ID
 */
function obtener_usuario($id, $conexion) {
    $query = "SELECT * FROM usuarios WHERE ID_usuario = " . intval($id) . " AND activo = 1";
    $resultado = $conexion->query($query);
    return $resultado->fetch_assoc();
}

/**
 * Obtener usuario por email
 */
function obtener_usuario_por_email($email, $conexion) {
    $email = escapar($email, $conexion);
    $query = "SELECT * FROM usuarios WHERE email = '$email' AND activo = 1";
    $resultado = $conexion->query($query);
    return $resultado->fetch_assoc();
}

/**
 * Obtener barbero por DNI
 */
function obtener_barbero($dni, $conexion) {
    $dni = escapar($dni, $conexion);
    $query = "SELECT * FROM barberos WHERE DNI_barbero = '$dni' AND activo = 1";
    $resultado = $conexion->query($query);
    return $resultado->fetch_assoc();
}

/**
 * Obtener todos los barberos activos
 */
function obtener_barberos($conexion) {
    $query = "SELECT * FROM barberos WHERE activo = 1 ORDER BY nombre";
    $resultado = $conexion->query($query);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}

/**
 * Obtener servicio por ID
 */
function obtener_servicio($id, $conexion) {
    $query = "SELECT * FROM servicios WHERE ID_servicio = " . intval($id) . " AND activo = 1";
    $resultado = $conexion->query($query);
    return $resultado->fetch_assoc();
}

/**
 * Obtener todos los servicios activos
 */
function obtener_servicios($conexion) {
    $query = "SELECT * FROM servicios WHERE activo = 1 ORDER BY nombre";
    $resultado = $conexion->query($query);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}

/**
 * Obtener cita por ID
 */
function obtener_cita($id, $conexion) {
    $query = "SELECT c.*, 
                     u.nombre as nombre_cliente, u.email, u.telefono,
                     b.nombre as barbero_nombre, b.apellidos as barbero_apellidos,
                     s.nombre as servicio_nombre, s.duracion_minutos
              FROM citas c
              LEFT JOIN usuarios u ON c.ID_usuario = u.ID_usuario
              LEFT JOIN barberos b ON c.DNI_barbero = b.DNI_barbero
              LEFT JOIN servicios s ON c.ID_servicio = s.ID_servicio
              WHERE c.ID_cita = " . intval($id);
    $resultado = $conexion->query($query);
    return $resultado->fetch_assoc();
}

/**
 * Obtener citas de un usuario
 */
function obtener_citas_usuario($id_usuario, $conexion) {
    $query = "SELECT c.*, 
                     b.nombre as barbero_nombre, b.apellidos as barbero_apellidos,
                     s.nombre as servicio_nombre, s.duracion_minutos
              FROM citas c
              LEFT JOIN barberos b ON c.DNI_barbero = b.DNI_barbero
              LEFT JOIN servicios s ON c.ID_servicio = s.ID_servicio
              WHERE c.ID_usuario = " . intval($id_usuario) . "
              ORDER BY c.fecha DESC, c.hora DESC";
    $resultado = $conexion->query($query);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}

/**
 * Obtener citas del barbero en una fecha
 */
function obtener_citas_barbero_fecha($dni_barbero, $fecha, $conexion) {
    $dni_barbero = escapar($dni_barbero, $conexion);
    $fecha = escapar($fecha, $conexion);
    $query = "SELECT c.hora, s.duracion_minutos
              FROM citas c
              LEFT JOIN servicios s ON c.ID_servicio = s.ID_servicio
              WHERE c.DNI_barbero = '$dni_barbero' 
              AND c.fecha = '$fecha'
              AND c.estado != 'cancelada'
              ORDER BY c.hora";
    $resultado = $conexion->query($query);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}

/**
 * Obtener configuración del sistema
 */
function obtener_config_sistema($conexion) {
    $query = "SELECT * FROM configuracion_sistema WHERE ID_config = 1 LIMIT 1";
    $resultado = $conexion->query($query);
    return $resultado->fetch_assoc();
}

/**
 * Convertir hora HH:MM(:SS) a minutos del día
 */
function hora_a_minutos($hora) {
    $partes = explode(':', $hora);
    $h = intval($partes[0] ?? 0);
    $m = intval($partes[1] ?? 0);
    return ($h * 60) + $m;
}

/**
 * Convertir minutos del día a HH:MM
 */
function minutos_a_hora($minutos) {
    $h = floor($minutos / 60);
    $m = $minutos % 60;
    return sprintf('%02d:%02d', $h, $m);
}

/**
 * Obtener bloques base de disponibilidad para un barbero en una fecha
 */
function obtener_bloques_disponibles_barbero($dni_barbero, $fecha, $conexion) {
    $dni_barbero = escapar($dni_barbero, $conexion);
    $fecha = escapar($fecha, $conexion);

    $query = "SELECT inicio_hora, fin_hora
              FROM horarios_disponibles
              WHERE DNI_barbero = '$dni_barbero'
              AND fecha = '$fecha'
              AND disponible = 1
              AND tipo = 'normal'
              ORDER BY inicio_hora";

    $resultado = $conexion->query($query);
    $bloques = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];

    if (!empty($bloques)) {
        return $bloques;
    }

    $barbero = obtener_barbero($dni_barbero, $conexion);
    if (!$barbero) {
        return [];
    }

    return [[
        'inicio_hora' => $barbero['horario_inicio'],
        'fin_hora' => $barbero['horario_fin']
    ]];
}

/**
 * Calcular horas de inicio disponibles para una reserva
 */
function obtener_slots_disponibles_barbero($dni_barbero, $fecha, $id_servicio, $conexion) {
    $servicio = obtener_servicio($id_servicio, $conexion);
    $barbero = obtener_barbero($dni_barbero, $conexion);
    $config = obtener_config_sistema($conexion);

    if (!$servicio || !$barbero || !$config) {
        return [];
    }

    if (strtotime($fecha) < strtotime(date('Y-m-d'))) {
        return [];
    }

    $duracion_servicio = max(1, intval($servicio['duracion_minutos']));
    $duracion_slot = max(5, intval($config['duracion_slot_minutos'] ?? 30));
    $tiempo_preparacion = max(0, intval($config['tiempo_preparacion_minutos'] ?? 0));

    $bloques = obtener_bloques_disponibles_barbero($dni_barbero, $fecha, $conexion);
    if (empty($bloques)) {
        return [];
    }

    $citas_ocupadas = obtener_citas_barbero_fecha($dni_barbero, $fecha, $conexion);
    $intervalos_ocupados = [];

    foreach ($citas_ocupadas as $cita) {
        $inicio = hora_a_minutos($cita['hora']);
        $fin = $inicio + intval($cita['duracion_minutos']) + $tiempo_preparacion;
        $intervalos_ocupados[] = [$inicio, $fin];
    }

    $slots = [];
    $es_hoy = ($fecha === date('Y-m-d'));
    $ahora_minutos = hora_a_minutos(date('H:i'));

    foreach ($bloques as $bloque) {
        $inicio_bloque = hora_a_minutos($bloque['inicio_hora']);
        $fin_bloque = hora_a_minutos($bloque['fin_hora']);

        for ($inicio = $inicio_bloque; $inicio + $duracion_servicio <= $fin_bloque; $inicio += $duracion_slot) {
            if ($es_hoy && $inicio <= $ahora_minutos) {
                continue;
            }

            $fin_reserva = $inicio + $duracion_servicio;
            $fin_con_preparacion = $fin_reserva + $tiempo_preparacion;
            $ocupado = false;

            foreach ($intervalos_ocupados as [$inicio_ocupado, $fin_ocupado]) {
                if ($inicio < $fin_ocupado && $fin_con_preparacion > $inicio_ocupado) {
                    $ocupado = true;
                    break;
                }
            }

            if (!$ocupado) {
                $slots[] = minutos_a_hora($inicio);
            }
        }
    }

    $slots = array_values(array_unique($slots));
    sort($slots);

    return $slots;
}

// ============================================
// FUNCIONES DE UTILIDAD
// ============================================

/**
 * Formatear fecha a formato español
 */
function formatear_fecha($fecha) {
    $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
              'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    
    $timestamp = strtotime($fecha);
    $dia_semana = $dias[date('w', $timestamp)];
    $dia = date('d', $timestamp);
    $mes = $meses[date('n', $timestamp)];
    $año = date('Y', $timestamp);
    
    return $dia_semana . ', ' . $dia . ' de ' . $mes . ' de ' . $año;
}

/**
 * Convertir hora a formato 12h
 */
function formatear_hora($hora) {
    return date('H:i', strtotime($hora));
}

/**
 * Generar array de horas disponibles (slots)
 */
function generar_slots_disponibles($hora_inicio, $hora_fin, $duracion_minutos, $citas_ocupadas = []) {
    $slots = [];
    $inicio = strtotime($hora_inicio);
    $fin = strtotime($hora_fin);
    
    while ($inicio < $fin) {
        $hora_actual = date('H:i', $inicio);
        $hora_siguiente = date('H:i', strtotime('+' . $duracion_minutos . ' minutes', $inicio));
        
        // Verificar si no está ocupado
        $ocupado = false;
        foreach ($citas_ocupadas as $cita) {
            $cita_inicio = strtotime($cita['hora']);
            $cita_fin = strtotime('+' . $cita['duracion_minutos'] . ' minutes', $cita_inicio);
            $slot_inicio = $inicio;
            $slot_fin = strtotime('+' . $duracion_minutos . ' minutes', $inicio);
            
            // Verificar si hay solapamiento
            if ($slot_inicio < $cita_fin && $slot_fin > $cita_inicio) {
                $ocupado = true;
                break;
            }
        }
        
        if (!$ocupado) {
            $slots[] = [
                'inicio' => $hora_actual,
                'fin' => $hora_siguiente
            ];
        }
        
        $inicio = strtotime('+' . $duracion_minutos . ' minutes', $inicio);
    }
    
    return $slots;
}

/**
 * Registrar acceso en historial
 */
function registrar_acceso($id_usuario, $conexion) {
    $ip = escapar($_SERVER['REMOTE_ADDR'], $conexion);
    $navegador = escapar($_SERVER['HTTP_USER_AGENT'], $conexion);
    
    $query = "INSERT INTO historial_acceso (ID_usuario, ip_address, navegador) 
              VALUES ($id_usuario, '$ip', '$navegador')";
    $conexion->query($query);
}

/**
 * Mensaje de alerta (HTML)
 */
function alerta($mensaje, $tipo = 'info') {
    $clases = [
        'success' => 'alert alert-success',
        'error' => 'alert alert-danger',
        'warning' => 'alert alert-warning',
        'info' => 'alert alert-info'
    ];
    
    $clase = $clases[$tipo] ?? 'alert alert-info';
    return "<div class='$clase' role='alert'>$mensaje</div>";
}

/**
 * Redireccionar con mensaje
 */
function redirigir_con_mensaje($url, $mensaje, $tipo = 'info') {
    $_SESSION['mensaje'] = $mensaje;
    $_SESSION['tipo_mensaje'] = $tipo;
    header('Location: ' . $url);
    exit;
}

/**
 * Mostrar y limpiar mensaje de sesión
 */
function mostrar_mensaje() {
    if (isset($_SESSION['mensaje'])) {
        $mensaje = $_SESSION['mensaje'];
        $tipo = $_SESSION['tipo_mensaje'] ?? 'info';
        unset($_SESSION['mensaje']);
        unset($_SESSION['tipo_mensaje']);
        return alerta($mensaje, $tipo);
    }
    return '';
}

/**
 * Obtener horas mínimas para cancelar una cita
 */
function obtener_horas_cancelacion($conexion) {
    $config = obtener_config_sistema($conexion);

    return max(1, (int)($config['aviso_cancelacion_horas'] ?? 24));
}

?>
