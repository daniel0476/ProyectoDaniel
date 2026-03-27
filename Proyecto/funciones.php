<?php
/**
 * ╔══════════════════════════════════════════════════════════════════════════╗
 * ║                      🟦 BLOQUE 1: BACKEND BÁSICO                        ║
 * ║                funciones.php - Utilidades Reutilizables                 ║
 * ╚══════════════════════════════════════════════════════════════════════════╝
 * 
 * Propósito:
 *   - Funciones de seguridad (hash, validación)
 *   - Consultas a base de datos
 *   - Funciones de manejo de sesiones
 *   - Utilidades generales
 * 
 * Uso: require_once 'funciones.php' después de config.php
 */

// ═══════════════════════════════════════════════════════════════════════════
// FUNCIÓN 1: VALIDAR EMAIL
// ═══════════════════════════════════════════════════════════════════════════

function validar_email($email) {
    /**
     * Valida que el email tenga formato correcto
     * 
     * @param string $email Email a validar
     * @return bool true si es válido, false si no
     */
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// ═══════════════════════════════════════════════════════════════════════════
// FUNCIÓN 2: HASHEAR CONTRASEÑA (BCRYPT)
// ═══════════════════════════════════════════════════════════════════════════

function hashear_contrasena($contrasena) {
    /**
     * Hashea una contraseña usando bcrypt (algoritmo seguro)
     * 
     * @param string $contrasena Contraseña en texto plano
     * @return string Contraseña hasheada
     */
    return password_hash($contrasena, PASSWORD_BCRYPT);
}

// ═══════════════════════════════════════════════════════════════════════════
// FUNCIÓN 3: VERIFICAR CONTRASEÑA
// ═══════════════════════════════════════════════════════════════════════════

function verificar_contrasena($contrasena, $hash) {
    /**
     * Verifica si una contraseña coincide con su hash
     * 
     * @param string $contrasena Contraseña ingresada por usuario
     * @param string $hash Hash de la BD
     * @return bool true si coincide, false si no
     */
    return password_verify($contrasena, $hash);
}

// ═══════════════════════════════════════════════════════════════════════════
// FUNCIÓN 4: ESCAPAR STRINGS (SQL INJECTION)
// ═══════════════════════════════════════════════════════════════════════════

function escapar($texto) {
    /**
     * Escapa caracteres especiales para proteger contra SQL injection
     * 
     * @param string $texto Texto a escapar
     * @return string Texto escapado
     */
    global $mysqli;
    return $mysqli->real_escape_string($texto);
}

// ═══════════════════════════════════════════════════════════════════════════
// FUNCIÓN 5: OBTENER USUARIO POR EMAIL
// ═══════════════════════════════════════════════════════════════════════════

function obtener_usuario_por_email($email) {
    /**
     * Busca un usuario en la BD por su email
     * 
     * @param string $email Email a buscar
     * @return array Datos del usuario o false si no existe
     */
    global $mysqli;
    
    $email = escapar($email);
    $query = "SELECT * FROM usuarios WHERE email = '$email' AND activo = 1 LIMIT 1";
    $resultado = $mysqli->query($query);
    
    if ($resultado && $resultado->num_rows > 0) {
        return $resultado->fetch_assoc();
    }
    
    return false;
}

// ═══════════════════════════════════════════════════════════════════════════
// FUNCIÓN 6: OBTENER USUARIO POR ID
// ═══════════════════════════════════════════════════════════════════════════

function obtener_usuario($id_usuario) {
    /**
     * Busca un usuario en la BD por su ID
     * 
     * @param int $id_usuario ID del usuario
     * @return array Datos del usuario o false si no existe
     */
    global $mysqli;
    
    $id_usuario = (int)$id_usuario;
    $query = "SELECT * FROM usuarios WHERE ID_usuario = $id_usuario AND activo = 1 LIMIT 1";
    $resultado = $mysqli->query($query);
    
    if ($resultado && $resultado->num_rows > 0) {
        return $resultado->fetch_assoc();
    }
    
    return false;
}

// ═══════════════════════════════════════════════════════════════════════════
// FUNCIÓN 7: VERIFICAR AUTENTICACIÓN
// ═══════════════════════════════════════════════════════════════════════════

function verificar_autenticacion() {
    /**
     * Verifica si el usuario está autenticado
     * Si no, redirige a login.php
     */
    if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit;
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// FUNCIÓN 8: VERIFICAR ADMIN
// ═══════════════════════════════════════════════════════════════════════════

function verificar_admin() {
    /**
     * Verifica si el usuario es admin
     * Si no, muestra error y detiene la ejecución
     */
    if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
        die('❌ Acceso denegado. Solo administradores pueden acceder aquí.');
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// FUNCIÓN 9: REDIRIGIR CON MENSAJE
// ═══════════════════════════════════════════════════════════════════════════

function redirigir_con_mensaje($url, $mensaje, $tipo = 'info') {
    /**
     * Guarda un mensaje en sesión y redirige a una URL
     * 
     * @param string $url URL de destino (ej: 'index.php')
     * @param string $mensaje Texto del mensaje
     * @param string $tipo Tipo de alerta (success, danger, warning, info)
     */
    $_SESSION['mensaje'] = $mensaje;
    $_SESSION['tipo_mensaje'] = $tipo;
    header("Location: $url");
    exit;
}

// ═══════════════════════════════════════════════════════════════════════════
// FUNCIÓN 10: REGISTRAR ACCESO (AUDITORÍA)
// ═══════════════════════════════════════════════════════════════════════════

function registrar_acceso($id_usuario) {
    /**
     * Registra cada acceso del usuario (para auditoría)
     * 
     * @param int $id_usuario ID del usuario que accede
     */
    global $mysqli;
    
    $id_usuario = (int)$id_usuario;
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
    $navegador = $_SERVER['HTTP_USER_AGENT'] ?? 'desconocido';
    
    $query = "INSERT INTO historial_acceso (ID_usuario, fecha_acceso, ip_address, navegador) 
              VALUES ($id_usuario, NOW(), '$ip_address', '$navegador')";
    
    $mysqli->query($query);
}

// ═══════════════════════════════════════════════════════════════════════════
// FUNCIÓN 11: MOSTRAR MENSAJE DE SESIÓN
// ═══════════════════════════════════════════════════════════════════════════

function mostrar_mensaje() {
    /**
     * Muestra un mensaje guardado en sesión y lo elimina
     * 
     * @return string HTML del mensaje o vacío
     */
    if (isset($_SESSION['mensaje']) && !empty($_SESSION['mensaje'])) {
        $mensaje = $_SESSION['mensaje'];
        $tipo = $_SESSION['tipo_mensaje'] ?? 'info';
        
        unset($_SESSION['mensaje']);
        unset($_SESSION['tipo_mensaje']);
        
        $colores = [
            'success' => 'alert-success',
            'danger' => 'alert-danger',
            'warning' => 'alert-warning',
            'info' => 'alert-info'
        ];
        
        $clase = $colores[$tipo] ?? 'alert-info';
        
        return "<div class='alert $clase alert-dismissible fade show' role='alert'>
                    $mensaje
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                </div>";
    }
    
    return '';
}

// ═══════════════════════════════════════════════════════════════════════════
// FUNCIÓN 12: FORMATEAR FECHA
// ═══════════════════════════════════════════════════════════════════════════

function formatear_fecha($fecha) {
    /**
     * Convierte fecha de formato YYYY-MM-DD a formato legible
     * 
     * @param string $fecha Fecha en formato YYYY-MM-DD
     * @return string Fecha formateada (ej: Lunes, 28 de Marzo de 2026)
     */
    $date = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$date) return $fecha;
    
    $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
    $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
              'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    
    $dia_semana = $dias[$date->format('N') - 1];
    $dia = $date->format('d');
    $mes = $meses[$date->format('n') - 1];
    $anio = $date->format('Y');
    
    return "$dia_semana, $dia de $mes de $anio";
}

// ═══════════════════════════════════════════════════════════════════════════
// FUNCIÓN 13: FORMATEAR HORA
// ═══════════════════════════════════════════════════════════════════════════

function formatear_hora($hora) {
    /**
     * Formatea horario de TIME a formato legible
     * 
     * @param string $hora Hora en formato HH:MM:SS
     * @return string Hora formateada (HH:MM)
     */
    if (empty($hora)) return '';
    return substr($hora, 0, 5); // Toma HH:MM
}

?>
