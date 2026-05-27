<?php
/*
 * admin/dashboard.php
 * ===================
 * Pantalla principal del panel de administración.
 * Muestra estadísticas rápidas (total de usuarios, barberos,
 * servicios y citas), las próximas citas del día,
 * y enlaces directos a las secciones de gestión.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../funciones.php';

// Solo administradores pueden ver esta página
verificar_autenticacion();
verificar_admin();

$titulo_pagina = 'Dashboard Admin - ' . APP_NAME;

// Cargar la configuración del sistema
$config = obtener_config_sistema($mysqli);

// ---------------------------------------------------------------
// CONTEO DE DATOS GENERALES
// ---------------------------------------------------------------

// Total de clientes activos registrados
$query_usuarios = "SELECT COUNT(*) as total FROM usuarios WHERE rol = 'cliente' AND activo = 1";
$total_usuarios = $mysqli->query($query_usuarios)->fetch_assoc()['total'];

// Total de barberos activos
$query_barberos = "SELECT COUNT(*) as total FROM barberos WHERE activo = 1";
$total_barberos = $mysqli->query($query_barberos)->fetch_assoc()['total'];

// Total de servicios activos
$query_servicios = "SELECT COUNT(*) as total FROM servicios WHERE activo = 1";
$total_servicios = $mysqli->query($query_servicios)->fetch_assoc()['total'];

// Total de citas registradas
$query_citas = "SELECT COUNT(*) as total FROM citas";
$total_citas = $mysqli->query($query_citas)->fetch_assoc()['total'];

// ---------------------------------------------------------------
// CITAS DEL DÍA DE HOY
// ---------------------------------------------------------------
$fecha_hoy = date('Y-m-d');
$query_citas_hoy = "SELECT c.*, u.nombre as nombre_cliente, u.email, b.nombre as barbero_nombre, b.apellidos as barbero_apellidos, s.nombre as servicio_nombre, s.duracion_minutos
                     FROM citas c
                     JOIN usuarios u ON c.ID_usuario = u.ID_usuario
                     JOIN barberos b ON c.DNI_barbero = b.DNI_barbero
                     JOIN servicios s ON c.ID_servicio = s.ID_servicio
                     WHERE c.fecha = '$fecha_hoy'
                     ORDER BY c.hora ASC";
$resultado_hoy = $mysqli->query($query_citas_hoy);
$citas_hoy = $resultado_hoy ? $resultado_hoy->fetch_all(MYSQLI_ASSOC) : [];

// ---------------------------------------------------------------
// ESTILOS ADICIONALES (CSS embebido)
// ---------------------------------------------------------------
$estilos_adicionales = '<style>
    .dashboard-card {
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.14) !important;
    }

    .dashboard-icon-bg {
        font-size: 38px;
        color: #000000;
    }

    .dashboard-stat {
        font-size: 36px;
        font-weight: 700;
        color: #171717;
    }

    .dashboard-label {
        font-size: 14px;
        color: #606060;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }

    .cita-hoy-card {
        border-left: 4px solid #e39a56;
        margin-bottom: 12px;
    }

    .cita-hoy-hora {
        font-weight: 700;
        color: #171717;
        font-size: 18px;
    }

    .cita-hoy-cliente {
        font-weight: 600;
    }

    .quick-link {
        text-decoration: none;
        color: #171717;
        transition: 0.2s;
    }

    .quick-link:hover {
        color: #e39a56;
    }
</style>';

ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>
        <i class="bi bi-speedometer2"></i> Dashboard
    </h1>
    <span class="text-muted">
        <i class="bi bi-calendar"></i> <?php echo formatear_fecha($fecha_hoy); ?>
    </span>
</div>

<!-- TARJETAS DE ESTADÍSTICAS RÁPIDAS -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card dashboard-card">
            <div class="card-body text-center">
                <i class="bi bi-people dashboard-icon-bg"></i>
                <h3 class="dashboard-stat"><?php echo $total_usuarios; ?></h3>
                <p class="dashboard-label">Usuarios</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card dashboard-card">
            <div class="card-body text-center">
                <i class="bi bi-person-badge dashboard-icon-bg"></i>
                <h3 class="dashboard-stat"><?php echo $total_barberos; ?></h3>
                <p class="dashboard-label">Barberos</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card dashboard-card">
            <div class="card-body text-center">
                <i class="bi bi-scissors dashboard-icon-bg"></i>
                <h3 class="dashboard-stat"><?php echo $total_servicios; ?></h3>
                <p class="dashboard-label">Servicios</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card dashboard-card">
            <div class="card-body text-center">
                <i class="bi bi-calendar-check dashboard-icon-bg"></i>
                <h3 class="dashboard-stat"><?php echo $total_citas; ?></h3>
                <p class="dashboard-label">Citas</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- CITAS DE HOY -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-calendar-day"></i> Citas de Hoy (<?php echo count($citas_hoy); ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($citas_hoy)): ?>
                    <p class="text-muted mb-0">No hay citas programadas para hoy.</p>
                <?php else: ?>
                    <?php foreach ($citas_hoy as $cita): ?>
                    <div class="cita-hoy-card p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="cita-hoy-hora"><?php echo formatear_hora($cita['hora']); ?></span>
                                <span class="ms-3 cita-hoy-cliente"><?php echo e($cita['nombre_cliente']); ?></span>
                                <br>
                                <small class="text-muted">
                                    <i class="bi bi-person-badge"></i> <?php echo e($cita['barbero_nombre'] . ' ' . $cita['barbero_apellidos']); ?>
                                    <i class="bi bi-scissors ms-2"></i> <?php echo e($cita['servicio_nombre']); ?>
                                    (<?php echo $cita['duracion_minutos']; ?> min)
                                </small>
                            </div>
                            <span class="badge badge-estado-<?php echo $cita['estado']; ?>">
                                <i class="bi bi-circle-fill"></i> <?php echo ucfirst($cita['estado']); ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- ACCESOS RÁPIDOS -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-link-45deg"></i> Acceso Rápido</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <a href="citas.php" class="list-group-item list-group-item-action quick-link">
                        <i class="bi bi-calendar-check"></i> Gestionar Citas
                    </a>
                    <a href="usuarios.php" class="list-group-item list-group-item-action quick-link">
                        <i class="bi bi-people"></i> Gestionar Usuarios
                    </a>
                    <a href="barberos.php" class="list-group-item list-group-item-action quick-link">
                        <i class="bi bi-person-badge"></i> Gestionar Barberos
                    </a>
                    <a href="servicios.php" class="list-group-item list-group-item-action quick-link">
                        <i class="bi bi-scissors"></i> Gestionar Servicios
                    </a>
                    <a href="configuracion.php" class="list-group-item list-group-item-action quick-link">
                        <i class="bi bi-gear"></i> Configuración
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$contenido = ob_get_clean();
include __DIR__ . '/../plantilla.php';
?>
