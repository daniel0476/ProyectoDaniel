<?php
/*
 * funciones.php
 * =============
 * Archivo que contiene todas las funciones compartidas del proyecto.
 * Aquí se agrupan:
 *   - Seguridad (autenticación, CSRF, escape de strings).
 *   - Consultas a la base de datos (usuarios, barberos, servicios, citas).
 *   - Lógica de horarios y disponibilidad (slots).
 *   - Utilidades de formato (fechas, horas, alertas HTML).
 *   - Mensajes flash entre páginas.
 */

// ================================================================
// SEGURIDAD Y SESIÓN
// ================================================================

/**
 * verificar_autenticacion()
 * -------------------------
 * Mira si hay un usuario_id en la sesión. Si no lo hay, redirige al login.
 * Se usa al inicio de páginas que requieren estar logueado.
 */
function verificar_autenticacion() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

/**
 * verificar_admin()
 * -----------------
 * Comprueba que el rol del usuario sea 'admin'. Si no, muere con error.
 * Se usa en todas las páginas del panel de administración.
 */
function verificar_admin() {
    if (($_SESSION['usuario_rol'] ?? null) !== 'admin') {
        die('Acceso denegado. Requiere permisos de administrador.');
    }
}

/**
 * e($valor)
 * ---------
 * Escapa un valor para mostrarlo de forma segura dentro de HTML.
 * Convierte caracteres especiales como < > & " ' en entidades HTML.
 * Si el valor es null, devuelve cadena vacía.
 */
function e($valor) {
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * hashear_contrasena($contrasena)
 * -------------------------------
 * Genera un hash bcrypt de la contraseña recibida.
 * bcrypt incluye un salt aleatorio, por lo que cada llamada produce un hash distinto.
 */
function hashear_contrasena($contrasena) {
    return password_hash($contrasena, PASSWORD_BCRYPT);
}

/**
 * verificar_contrasena($contrasena, $hash)
 * -----------------------------------------
 * Compara una contraseña en texto plano contra un hash bcrypt.
 * Devuelve true si coincide, false si no.
 */
function verificar_contrasena($contrasena, $hash) {
    return password_verify($contrasena, $hash);
}

/**
 * actualizar_contrasena_usuario($id_usuario, $hash, $conexion)
 * ------------------------------------------------------------
 * Actualiza el campo contrasena de un usuario en la base de datos.
 * Se usa cuando el usuario cambia su contraseña o cuando se migra
 * desde texto plano a bcrypt.
 */
function actualizar_contrasena_usuario($id_usuario, $hash, $conexion) {
    $query = "UPDATE usuarios SET contrasena = '" . escapar($hash, $conexion) . "' WHERE ID_usuario = " . intval($id_usuario);
    return $conexion->query($query);
}

/**
 * escapar($texto, $conexion)
 * --------------------------
 * Escapa caracteres especiales para evitar inyección SQL.
 * Usa el método real_escape_string de la conexión MySQLi.
 */
function escapar($texto, $conexion) {
    return $conexion->real_escape_string($texto);
}

/**
 * validar_email($email)
 * ---------------------
 * Verifica que el email tenga un formato válido usando filter_var.
 * Devuelve el email si es válido, o false si no.
 */
function validar_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * obtener_csrf_token()
 * --------------------
 * Devuelve (o genera si no existe) el token CSRF almacenado en la sesión.
 * El token es un string hexadecimal de 64 caracteres (32 bytes aleatorios).
 */
function obtener_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * csrf_input()
 * ------------
 * Genera un campo HTML <input type="hidden"> con el token CSRF actual.
 * Este campo debe incluirse en todo formulario POST para protección CSRF.
 */
function csrf_input() {
    return '<input type="hidden" name="csrf_token" value="' . e(obtener_csrf_token()) . '">';
}

/**
 * verificar_csrf()
 * ----------------
 * Compara el token CSRF enviado en el POST con el de la sesión.
 * Usa hash_equals para evitar ataques de timing.
 */
function verificar_csrf() {
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

/**
 * requerir_csrf()
 * ---------------
 * Verifica el CSRF y si falla, responde con 403 y termina la ejecución.
 * Se llama al inicio del procesamiento de formularios POST.
 */
function requerir_csrf() {
    if (!verificar_csrf()) {
        http_response_code(403);
        die('Petición no válida. Recarga la página e inténtalo de nuevo.');
    }
}

// ================================================================
// CONSULTAS Y DATOS COMPARTIDOS
// ================================================================

/**
 * obtener_usuario($id, $conexion)
 * -------------------------------
 * Busca un usuario activo por su ID.
 * Devuelve un array asociativo con todos los campos del usuario, o null si no existe.
 */
function obtener_usuario($id, $conexion) {
    $query = "SELECT * FROM usuarios WHERE ID_usuario = " . intval($id) . " AND activo = 1";
    $resultado = $conexion->query($query);
    return $resultado->fetch_assoc();
}

/**
 * obtener_usuario_por_email($email, $conexion)
 * --------------------------------------------
 * Busca un usuario activo usando su dirección de email.
 * Se usa en login y registro para verificar si el email ya existe.
 */
function obtener_usuario_por_email($email, $conexion) {
    $email = escapar($email, $conexion);
    $query = "SELECT * FROM usuarios WHERE email = '$email' AND activo = 1";
    $resultado = $conexion->query($query);
    return $resultado->fetch_assoc();
}

/**
 * obtener_barbero($dni, $conexion)
 * --------------------------------
 * Busca un barbero activo por su DNI.
 * Devuelve array asociativo o null.
 */
function obtener_barbero($dni, $conexion) {
    $dni = escapar($dni, $conexion);
    $query = "SELECT * FROM barberos WHERE DNI_barbero = '$dni' AND activo = 1";
    $resultado = $conexion->query($query);
    return $resultado->fetch_assoc();
}

/**
 * obtener_barberos($conexion)
 * ---------------------------
 * Devuelve todos los barberos activos, ordenados alfabéticamente por nombre.
 */
function obtener_barberos($conexion) {
    $query = "SELECT * FROM barberos WHERE activo = 1 ORDER BY nombre";
    $resultado = $conexion->query($query);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}

/**
 * obtener_servicio($id, $conexion)
 * --------------------------------
 * Busca un servicio activo por su ID.
 * Devuelve array asociativo o null.
 */
function obtener_servicio($id, $conexion) {
    $query = "SELECT * FROM servicios WHERE ID_servicio = " . intval($id) . " AND activo = 1";
    $resultado = $conexion->query($query);
    return $resultado->fetch_assoc();
}

/**
 * obtener_servicios($conexion)
 * ----------------------------
 * Devuelve todos los servicios activos, ordenados alfabéticamente.
 */
function obtener_servicios($conexion) {
    $query = "SELECT * FROM servicios WHERE activo = 1 ORDER BY nombre";
    $resultado = $conexion->query($query);
    return $resultado->fetch_all(MYSQLI_ASSOC);
}

/**
 * obtener_cita($id, $conexion)
 * ----------------------------
 * Obtiene una cita por su ID y la enriquece con datos del cliente, barbero y servicio.
 * Devuelve un array completo con toda la info, o null si no existe la cita.
 */
function obtener_cita($id, $conexion) {
    $query = "SELECT * FROM citas WHERE ID_cita = " . intval($id);
    $resultado = $conexion->query($query);
    $cita = $resultado->fetch_assoc();
    
    if (!$cita) {
        return null;
    }
    
    // Datos del cliente que hizo la reserva
    $usuario = obtener_usuario($cita['ID_usuario'], $conexion);
    $cita['nombre_cliente'] = $usuario['nombre'] ?? '';
    $cita['email'] = $usuario['email'] ?? '';
    $cita['telefono'] = $usuario['telefono'] ?? '';
    
    // Datos del barbero asignado
    $barbero = obtener_barbero($cita['DNI_barbero'], $conexion);
    $cita['barbero_nombre'] = $barbero['nombre'] ?? '';
    $cita['barbero_apellidos'] = $barbero['apellidos'] ?? '';
    
    // Datos del servicio contratado
    $servicio = obtener_servicio($cita['ID_servicio'], $conexion);
    $cita['servicio_nombre'] = $servicio['nombre'] ?? '';
    $cita['duracion_minutos'] = $servicio['duracion_minutos'] ?? 0;
    
    return $cita;
}

/**
 * obtener_citas_usuario($id_usuario, $conexion)
 * ---------------------------------------------
 * Trae todas las citas de un usuario, excluyendo las canceladas.
 * Las ordena de más reciente a más antigua por fecha y hora.
 * Cada cita se enriquece con el nombre del barbero, nombre del servicio y duración.
 */
function obtener_citas_usuario($id_usuario, $conexion) {
    $query = "SELECT * FROM citas WHERE ID_usuario = " . intval($id_usuario) . " AND estado != 'cancelada' ORDER BY fecha DESC, hora DESC";
    $resultado = $conexion->query($query);
    $citas = $resultado->fetch_all(MYSQLI_ASSOC);
    
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
 * obtener_citas_barbero_fecha($dni_barbero, $fecha, $conexion)
 * ------------------------------------------------------------
 * Obtiene todas las citas no canceladas de un barbero en una fecha concreta.
 * Se usa principalmente para calcular la disponibilidad de horarios.
 * Cada cita incluye la duración del servicio asociado.
 */
function obtener_citas_barbero_fecha($dni_barbero, $fecha, $conexion) {
    $dni_barbero = escapar($dni_barbero, $conexion);
    $fecha = escapar($fecha, $conexion);
    $query = "SELECT * FROM citas WHERE DNI_barbero = '$dni_barbero' AND fecha = '$fecha' AND estado != 'cancelada' ORDER BY hora";
    $resultado = $conexion->query($query);
    $citas = $resultado->fetch_all(MYSQLI_ASSOC);
    
    foreach ($citas as &$cita) {
        $servicio = obtener_servicio($cita['ID_servicio'], $conexion);
        $cita['duracion_minutos'] = $servicio['duracion_minutos'] ?? 0;
    }
    
    return $citas;
}

/**
 * obtener_config_sistema($conexion)
 * ---------------------------------
 * Lee la fila de configuración general de la barbería desde la tabla configuracion_sistema.
 * Solo existe un registro (ID_config = 1) que contiene horarios, política de cancelación, etc.
 */
function obtener_config_sistema($conexion) {
    $query = "SELECT * FROM configuracion_sistema WHERE ID_config = 1 LIMIT 1";
    $resultado = $conexion->query($query);
    if (!$resultado) {
        return [];
    }
    return $resultado->fetch_assoc() ?: [];
}

// ================================================================
// CONVERSIÓN Y CÁLCULO DE HORAS
// ================================================================

/**
 * hora_a_minutos($hora)
 * ---------------------
 * Convierte una hora en formato "HH:MM" a minutos desde la medianoche.
 * Ejemplo: "08:30" → 510 minutos.
 */
function hora_a_minutos($hora) {
    $partes = explode(':', $hora);
    $h = intval($partes[0] ?? 0);
    $m = intval($partes[1] ?? 0);
    return ($h * 60) + $m;
}

/**
 * minutos_a_hora($minutos)
 * ------------------------
 * Convierte una cantidad de minutos al formato "HH:MM".
 * Ejemplo: 510 → "08:30".
 */
function minutos_a_hora($minutos) {
    $h = floor($minutos / 60);
    $m = $minutos % 60;
    return sprintf('%02d:%02d', $h, $m);
}

// ================================================================
// LÓGICA DE HORARIOS Y DISPONIBILIDAD
// ================================================================

/**
 * obtener_bloques_disponibles_barbero($dni_barbero, $fecha, $conexion)
 * --------------------------------------------------------------------
 * Devuelve los bloques horarios en los que un barbero está disponible en una fecha.
 * Primero busca en la tabla horarios_disponibles (días especiales).
 * Si no hay registros, usa el horario por defecto del barbero.
 */
function obtener_bloques_disponibles_barbero($dni_barbero, $fecha, $conexion) {
    $dni_barbero = escapar($dni_barbero, $conexion);
    $fecha = escapar($fecha, $conexion);

    // Buscar si hay horarios especiales configurados para ese día
    $query = "SELECT inicio_hora, fin_hora
              FROM horarios_disponibles
              WHERE DNI_barbero = '$dni_barbero'
              AND fecha = '$fecha'
              AND disponible = 1
              AND tipo = 'normal'
              ORDER BY inicio_hora";

    $resultado = $conexion->query($query);
    $bloques = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];

    // Si hay horario personalizado para ese día, lo usamos
    if (!empty($bloques)) {
        return $bloques;
    }

    // Si no hay horario especial, usamos el horario genérico del barbero
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
 * obtener_slots_disponibles_barbero($dni_barbero, $fecha, $id_servicio, $conexion)
 * -------------------------------------------------------------------------------
 * Calcula todos los horarios libres para un barbero en una fecha concreta,
 * teniendo en cuenta la duración del servicio, los slots ocupados y la hora actual.
 * Devuelve un array de strings con formato "HH:MM".
 */
function obtener_slots_disponibles_barbero($dni_barbero, $fecha, $id_servicio, $conexion) {
    $servicio = obtener_servicio($id_servicio, $conexion);
    $barbero = obtener_barbero($dni_barbero, $conexion);
    $config = obtener_config_sistema($conexion);

    // Si no existe el servicio, el barbero o la configuración, no hay slots
    if (!$servicio || !$barbero || !$config) {
        return [];
    }

    // No permitir reservas en fechas pasadas
    if (strtotime($fecha) < strtotime(date('Y-m-d'))) {
        return [];
    }

    $duracion_servicio = max(1, intval($servicio['duracion_minutos']));
    $duracion_slot = max(5, intval($config['duracion_slot_minutos'] ?? 30));
    $tiempo_preparacion = max(0, intval($config['tiempo_preparacion_minutos'] ?? 0));

    // Obtener los bloques donde el barbero trabaja ese día
    $bloques = obtener_bloques_disponibles_barbero($dni_barbero, $fecha, $conexion);
    if (empty($bloques)) {
        return [];
    }

    // Obtener citas ya ocupadas y convertirlas a intervalos [inicio, fin] en minutos
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

    // Recorrer cada bloque horario y dividirlo en slots
    foreach ($bloques as $bloque) {
        $inicio_bloque = hora_a_minutos($bloque['inicio_hora']);
        $fin_bloque = hora_a_minutos($bloque['fin_hora']);

        // Avanzar por el bloque en pasos del tamaño del slot
        for ($inicio = $inicio_bloque; $inicio + $duracion_servicio <= $fin_bloque; $inicio += $duracion_slot) {
            // Si es hoy, saltar slots que ya pasaron
            if ($es_hoy && $inicio <= $ahora_minutos) {
                continue;
            }

            $fin_reserva = $inicio + $duracion_servicio;
            $fin_con_preparacion = $fin_reserva + $tiempo_preparacion;
            $ocupado = false;

            // Comprobar si este slot choca con alguna cita existente
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

    // Eliminar duplicados y ordenar
    $slots = array_values(array_unique($slots));
    sort($slots);

    return $slots;
}

// ================================================================
// FORMATEO DE FECHAS Y HORAS
// ================================================================

/**
 * formatear_fecha($fecha)
 * -----------------------
 * Convierte una fecha "Y-m-d" a un formato legible en español.
 * Ejemplo: "2026-05-26" → "Martes, 26 de Mayo de 2026".
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
 * formatear_hora($hora)
 * ---------------------
 * Convierte una hora en formato "H:i:s" o "H:i" a formato 12h con AM/PM.
 * Ejemplo: "09:15:00" → "9:15 AM".
 */
function formatear_hora($hora) {
    return date('g:i A', strtotime($hora));
}

/**
 * generar_slots_disponibles($hora_inicio, $hora_fin, $duracion_minutos, $citas_ocupadas)
 * --------------------------------------------------------------------------------------
 * Función auxiliar que divide un rango horario en slots de cierta duración,
 * excluyendo aquellos que coinciden con citas ocupadas.
 * Se usa en tests y como alternativa a obtener_slots_disponibles_barbero.
 */
function generar_slots_disponibles($hora_inicio, $hora_fin, $duracion_minutos, $citas_ocupadas = []) {
    $slots = [];
    $inicio = strtotime($hora_inicio);
    $fin = strtotime($hora_fin);
    
    while ($inicio < $fin) {
        $hora_actual = date('H:i', $inicio);
        $hora_siguiente = date('H:i', strtotime('+' . $duracion_minutos . ' minutes', $inicio));
        
        // Verificar si el slot actual está ocupado por otra cita
        $ocupado = false;
        foreach ($citas_ocupadas as $cita) {
            $cita_inicio = strtotime($cita['hora']);
            $cita_fin = strtotime('+' . $cita['duracion_minutos'] . ' minutes', $cita_inicio);
            $slot_inicio = $inicio;
            $slot_fin = strtotime('+' . $duracion_minutos . ' minutes', $inicio);
            
            // Hay colisión si los intervalos se solapan
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

// ================================================================
// REGISTRO DE ACCESOS
// ================================================================

/**
 * registrar_acceso($id_usuario, $conexion)
 * ----------------------------------------
 * Guarda un registro en la tabla historial_acceso cada vez que un usuario inicia sesión.
 * Almacena la IP y el User-Agent del navegador.
 */
function registrar_acceso($id_usuario, $conexion) {
    $ip = escapar($_SERVER['REMOTE_ADDR'], $conexion);
    $navegador = escapar($_SERVER['HTTP_USER_AGENT'], $conexion);
    
    $query = "INSERT INTO historial_acceso (ID_usuario, ip_address, navegador) 
              VALUES ($id_usuario, '$ip', '$navegador')";
    $conexion->query($query);
}

// ================================================================
// MENSAJES Y ALERTAS
// ================================================================

/**
 * alerta($mensaje, $tipo)
 * -----------------------
 * Genera el HTML de una alerta de Bootstrap con el mensaje y tipo indicados.
 * Tipos válidos: success, error, warning, info.
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
 * redirigir_con_mensaje($url, $mensaje, $tipo)
 * ---------------------------------------------
 * Guarda un mensaje flash en sesión y redirige a otra URL.
 * El mensaje se mostrará en la página de destino con mostrar_mensaje().
 */
function redirigir_con_mensaje($url, $mensaje, $tipo = 'info') {
    $_SESSION['mensaje'] = $mensaje;
    $_SESSION['tipo_mensaje'] = $tipo;
    header('Location: ' . $url);
    exit;
}

/**
 * mostrar_mensaje()
 * -----------------
 * Si hay un mensaje flash guardado en sesión, lo muestra y lo elimina.
 * Se usa en las plantillas para mostrar alertas después de una redirección.
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
 * obtener_horas_cancelacion($conexion)
 * ------------------------------------
 * Lee de la configuración del sistema cuántas horas de antelación
 * se necesitan para cancelar una cita. Mínimo 1 hora, por defecto 24.
 */
function obtener_horas_cancelacion($conexion) {
    $config = obtener_config_sistema($conexion);

    return max(1, (int)($config['aviso_cancelacion_horas'] ?? 24));
}
?>