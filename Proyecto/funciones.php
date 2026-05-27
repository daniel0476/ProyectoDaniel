<?php
/**
 * funciones.php
 * Funciones compartidas por varias páginas del proyecto.
 */

// Seguridad y sesión

/**
 * Comprueba si hay sesión activa y redirige al login si no.
 */
function verificar_autenticacion() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

/**
 * Comprueba si el usuario tiene rol de administrador.
 */
function verificar_admin() {
    if (($_SESSION['usuario_rol'] ?? null) !== 'admin') {
        die('Acceso denegado. Requiere permisos de administrador.');
    }
}

/**
 * Escapa texto para mostrarlo seguro en HTML.
 */
function e($valor) {
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Crea un hash seguro para la contraseña.
 */
function hashear_contrasena($contrasena) {
    return password_hash($contrasena, PASSWORD_BCRYPT);
}

/**
 * Comprueba la contraseña contra su hash.
 */
function verificar_contrasena($contrasena, $hash) {
    return password_verify($contrasena, $hash);
}

/**
 * Guarda la nueva contraseña hasheada en la base de datos.
 */
function actualizar_contrasena_usuario($id_usuario, $hash, $conexion) {
    $query = "UPDATE usuarios SET contrasena = '" . escapar($hash, $conexion) . "' WHERE ID_usuario = " . intval($id_usuario);
    return $conexion->query($query);
}

/**
 * Escapa texto antes de usarlo en una consulta SQL.
 */
function escapar($texto, $conexion) {
    return $conexion->real_escape_string($texto);
}

/**
 * Comprueba que un email tiene un formato válido.
 */
function validar_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Recupera o genera el token CSRF de la sesión.
 */
function obtener_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Devuelve un input oculto con el token CSRF.
 */
function csrf_input() {
    return '<input type="hidden" name="csrf_token" value="' . e(obtener_csrf_token()) . '">';
}

/**
 * Comprueba que el token CSRF enviado es válido.
 */
function verificar_csrf() {
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

/**
 * Detiene la petición si el token CSRF no coincide.
 */
function requerir_csrf() {
    if (!verificar_csrf()) {
        http_response_code(403);
        die('Petición no válida. Recarga la página e inténtalo de nuevo.');
    }
}

// Consultas y datos compartidos

/**
 * Busca al usuario activo por su ID.
 */
function obtener_usuario($id, $conexion) {
    $query = "SELECT * FROM usuarios WHERE ID_usuario = " . intval($id) . " AND activo = 1";
    $resultado = $conexion->query($query);
    return $resultado->fetch_assoc();
}

/**
 * Busca al usuario activo usando su email.
 */
function obtener_usuario_por_email($email, $conexion) {
    $email = escapar($email, $conexion);
    $query = "SELECT * FROM usuarios WHERE email = '$email' AND activo = 1";
    $resultado = $conexion->query($query);
    return $resultado->fetch_assoc();
}

/**
 * Busca al barbero activo según su DNI.
 */
function obtener_barbero($dni, $conexion) {
    $dni = escapar($dni, $conexion);
    $query = "SELECT * FROM barberos WHERE DNI_barbero = '$dni' AND activo = 1";
    $resultado = $conexion->query($query);
    return $resultado->fetch_assoc();
}

/**
 * Devuelve los barberos activos ordenados por nombre.
 */
function obtener_barberos($conexion) {
    $query = "SELECT * FROM barberos WHERE activo = 1 ORDER BY nombre";
    $resultado = $conexion->query($query);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}

/**
 * Busca el servicio activo por su ID.
 */
function obtener_servicio($id, $conexion) {
    $query = "SELECT * FROM servicios WHERE ID_servicio = " . intval($id) . " AND activo = 1";
    $resultado = $conexion->query($query);
    return $resultado->fetch_assoc();
}

/**
 * Devuelve los servicios activos ordenados por nombre.
 */
function obtener_servicios($conexion) {
    $query = "SELECT * FROM servicios WHERE activo = 1 ORDER BY nombre";
    $resultado = $conexion->query($query);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}

/**
 * Recupera la información completa de una cita.
 */
function obtener_cita($id, $conexion) {
    $query = "SELECT * FROM citas WHERE ID_cita = " . intval($id);
    $resultado = $conexion->query($query);
    $cita = $resultado->fetch_assoc();
    
    if (!$cita) {
        return null;
    }
    
    // Obtener datos del usuario
    $usuario = obtener_usuario($cita['ID_usuario'], $conexion);
    $cita['nombre_cliente'] = $usuario['nombre'] ?? '';
    $cita['email'] = $usuario['email'] ?? '';
    $cita['telefono'] = $usuario['telefono'] ?? '';
    
    // Obtener datos del barbero
    $barbero = obtener_barbero($cita['DNI_barbero'], $conexion);
    $cita['barbero_nombre'] = $barbero['nombre'] ?? '';
    $cita['barbero_apellidos'] = $barbero['apellidos'] ?? '';
    
    // Obtener datos del servicio
    $servicio = obtener_servicio($cita['ID_servicio'], $conexion);
    $cita['servicio_nombre'] = $servicio['nombre'] ?? '';
    $cita['duracion_minutos'] = $servicio['duracion_minutos'] ?? 0;
    
    return $cita;
}

/**
 * Trae todas las citas asociadas a un usuario.
 */
function obtener_citas_usuario($id_usuario, $conexion) {
    $query = "SELECT * FROM citas WHERE ID_usuario = " . intval($id_usuario) . " AND estado != 'cancelada' ORDER BY fecha DESC, hora DESC";
    $resultado = $conexion->query($query);
    $citas = $resultado->fetch_all(MYSQLI_ASSOC);
    
    // Enriquecer cada cita con datos de barbero y servicio
    foreach ($citas as &$cita) {
        $barbero = obtener_barbero($cita['DNI_barbero'], $conexion);
        $cita['barbero_nombre'] = $barbero['nombre'] ?? '';
        $cita['barbero_apellidos'] = $barbero['apellidos'] ?? '';
        
        $servicio = obtener_servicio($cita['ID_servicio'], $conexion);
        $cita['servicio_nombre'] = $servicio['nombre'] ?? '';
        $cita['duracion_minutos'] = $servicio['duracion_minutos'] ?? 0;
    }
    
    return $citas;
}

/**
 * Trae las citas de un barbero en un día concreto.
 */
function obtener_citas_barbero_fecha($dni_barbero, $fecha, $conexion) {
    $dni_barbero = escapar($dni_barbero, $conexion);
    $fecha = escapar($fecha, $conexion);
    $query = "SELECT * FROM citas WHERE DNI_barbero = '$dni_barbero' AND fecha = '$fecha' AND estado != 'cancelada' ORDER BY hora";
    $resultado = $conexion->query($query);
    $citas = $resultado->fetch_all(MYSQLI_ASSOC);
    
    // Agregar duración del servicio a cada cita
    foreach ($citas as &$cita) {
        $servicio = obtener_servicio($cita['ID_servicio'], $conexion);
        $cita['duracion_minutos'] = $servicio['duracion_minutos'] ?? 0;
    }
    
    return $citas;
}

/**
 * Lee la configuración general de la barbería.
 */
function obtener_config_sistema($conexion) {
    $query = "SELECT * FROM configuracion_sistema WHERE ID_config = 1 LIMIT 1";
    $resultado = $conexion->query($query);
    if (!$resultado) {
        return [];
    }
    return $resultado->fetch_assoc() ?: [];
}

/**
 * Convierte una hora en minutos desde medianoche.
 */
function hora_a_minutos($hora) {
    $partes = explode(':', $hora);
    $h = intval($partes[0] ?? 0);
    $m = intval($partes[1] ?? 0);
    return ($h * 60) + $m;
}

/**
 * Da formato HH:MM a una cantidad de minutos.
 */
function minutos_a_hora($minutos) {
    $h = floor($minutos / 60);
    $m = $minutos % 60;
    return sprintf('%02d:%02d', $h, $m);
}

/**
 * Devuelve los bloques de disponibilidad base para un barbero.
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
 * Calcula las horas libres disponibles para reservar.
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



/**
 * Formatea una fecha con estilo español.
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
 * Formatea una hora al estilo HH:MM.
 */
function formatear_hora($hora) {
    return date('g:i A', strtotime($hora));
}

/**
 * Genera los slots libres entre dos horas.
 */
function generar_slots_disponibles($hora_inicio, $hora_fin, $duracion_minutos, $citas_ocupadas = []) {
    $slots = [];
    $inicio = strtotime($hora_inicio);
    $fin = strtotime($hora_fin);
    
    while ($inicio < $fin) {
        $hora_actual = date('H:i', $inicio);
        $hora_siguiente = date('H:i', strtotime('+' . $duracion_minutos . ' minutes', $inicio));
        
        // Ignorar si el slot ya está reservado
        $ocupado = false;
        foreach ($citas_ocupadas as $cita) {
            $cita_inicio = strtotime($cita['hora']);
            $cita_fin = strtotime('+' . $cita['duracion_minutos'] . ' minutes', $cita_inicio);
            $slot_inicio = $inicio;
            $slot_fin = strtotime('+' . $duracion_minutos . ' minutes', $inicio);
            
            // Detectar si el slot choca con otra cita
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
 * Registra el acceso del usuario en el historial.
 */
function registrar_acceso($id_usuario, $conexion) {
    $ip = escapar($_SERVER['REMOTE_ADDR'], $conexion);
    $navegador = escapar($_SERVER['HTTP_USER_AGENT'], $conexion);
    
    $query = "INSERT INTO historial_acceso (ID_usuario, ip_address, navegador) 
              VALUES ($id_usuario, '$ip', '$navegador')";
    $conexion->query($query);
}

/**
 * Devuelve el HTML de una alerta.
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
 * Guarda un mensaje flash y redirige a otra página.
 */
function redirigir_con_mensaje($url, $mensaje, $tipo = 'info') {
    $_SESSION['mensaje'] = $mensaje;
    $_SESSION['tipo_mensaje'] = $tipo;
    header('Location: ' . $url);
    exit;
}

/**
 * Muestra un mensaje flash y lo elimina de la sesión.
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
 * Lee el tiempo mínimo para cancelar una cita.
 */
function obtener_horas_cancelacion($conexion) {
    $config = obtener_config_sistema($conexion);

    return max(1, (int)($config['aviso_cancelacion_horas'] ?? 24));
}

?>
