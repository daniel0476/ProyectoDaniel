<?php
/**
 * admin/dashboard.php
 * Panel principal del administrador
 */

require_once '../config.php';
require_once '../funciones.php';

verificar_autenticacion();
verificar_admin();

$titulo_pagina = 'Dashboard Admin - ' . APP_NAME;

// Obtener estadísticas
$config = obtener_config_sistema($mysqli);

// TOTAL USUARIOS
$query_usuarios = "SELECT COUNT(*) as total FROM usuarios WHERE rol = 'cliente' AND activo = 1";
$total_usuarios = $mysqli->query($query_usuarios)->fetch_assoc()['total'];

// TOTAL BARBEROS
$query_barberos = "SELECT COUNT(*) as total FROM barberos WHERE activo = 1";
$total_barberos = $mysqli->query($query_barberos)->fetch_assoc()['total'];

// TOTAL SERVICIOS
$query_servicios = "SELECT COUNT(*) as total FROM servicios WHERE activo = 1";
$total_servicios = $mysqli->query($query_servicios)->fetch_assoc()['total'];

// TOTAL CITAS
$query_citas = "SELECT COUNT(*) as total FROM citas";
$total_citas = $mysqli->query($query_citas)->fetch_assoc()['total'];

// CITAS DE HOY
$query_hoy = "SELECT COUNT(*) as total FROM citas WHERE fecha = '" . date('Y-m-d') . "'";
$citas_hoy = $mysqli->query($query_hoy)->fetch_assoc()['total'];

// INGRESOS TOTALES
$query_ingresos = "SELECT SUM(precio_final) as total FROM citas WHERE estado = 'completada'";
$ingresos = $mysqli->query($query_ingresos)->fetch_assoc()['total'] ?? 0;

// CITAS PENDIENTES DE CONFIRMACIÓN
$query_pendientes = "SELECT COUNT(*) as total FROM citas WHERE estado = 'pendiente'";
$citas_pendientes = $mysqli->query($query_pendientes)->fetch_assoc()['total'];

// PRÓXIMAS CITAS
$query_proximas = "SELECT citas.*, 
                   usuarios.nombre AS cliente_nombre,
                   barberos.nombre AS barbero_nombre,
                   barberos.apellidos AS barbero_apellidos,
                   servicios.nombre AS servicio_nombre
                   FROM citas 
                   LEFT JOIN usuarios ON citas.ID_usuario = usuarios.ID_usuario
                   LEFT JOIN barberos ON citas.DNI_barbero = barberos.DNI_barbero
                   LEFT JOIN servicios ON citas.ID_servicio = servicios.ID_servicio
                   WHERE citas.fecha >= '" . date('Y-m-d') . "' 
                   AND citas.estado != 'cancelada'
                   ORDER BY citas.fecha, citas.hora
                   LIMIT 5";
$proximas_citas = $mysqli->query($query_proximas)->fetch_all(MYSQLI_ASSOC);

$estilos_adicionales = '<style>
    .admin-metric-icon {
        font-size: 32px;
        margin-bottom: 10px;
        display: block;
    }

    .admin-metric-value {
        color: #e39a56;
        font-weight: bold;
    }

    .admin-kpi-card {
        border-left: 4px solid rgba(227, 154, 86, 0.7) !important;
    }

    .admin-kpi-card .admin-kpi-value {
        color: #e39a56;
        font-weight: bold;
    }

    .admin-shortcut {
        text-decoration: none;
        color: inherit;
        display: block;
        height: 100%;
    }

    .admin-shortcut .card-body {
        min-height: 150px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
    }

    .admin-shortcut i {
        font-size: 32px;
        color: #e39a56;
        margin-bottom: 10px;
        display: block;
    }

    h1 {
        color: #000000 !important;
    }

    h5 {
        color: #000000 !important;
    }

    .card-header h5 {
        color: #e39a56 !important;
    }

    .card-header h5 i {
        color: #ffffff !important;
    }
</style>';

ob_start();
?>

<h1 class="mb-30">
    <i class="bi bi-speedometer2"></i> Panel de Control
</h1>

<!-- ESTADÍSTICAS PRINCIPALES -->
<div class="row mb-40">
    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="admin-metric-icon">👥</div>
                <h6 class="text-muted">Clientes Registrados</h6>
                <h3 class="admin-metric-value mb-0">
                    <?php echo $total_usuarios; ?>
                </h3>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="admin-metric-icon">💈</div>
                <h6 class="text-muted">Barberos Activos</h6>
                <h3 class="admin-metric-value mb-0">
                    <?php echo $total_barberos; ?>
                </h3>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="admin-metric-icon">✂️</div>
                <h6 class="text-muted">Servicios Disponibles</h6>
                <h3 class="admin-metric-value mb-0">
                    <?php echo $total_servicios; ?>
                </h3>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="admin-metric-icon">📅</div>
                <h6 class="text-muted">Total de Citas</h6>
                <h3 class="admin-metric-value mb-0">
                    <?php echo $total_citas; ?>
                </h3>
            </div>
        </div>
    </div>
</div>

<!-- ESTADÍSTICAS DE HOY E INGRESOS -->
<div class="row mb-40">
    <div class="col-md-4 mb-3">
        <div class="card admin-kpi-card">
            <div class="card-body">
                <h6 class="text-muted mb-2">Citas de Hoy</h6>
                <h3 class="admin-kpi-value mb-0">
                    <?php echo $citas_hoy; ?>
                </h3>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card admin-kpi-card">
            <div class="card-body">
                <h6 class="text-muted mb-2">Citas Pendientes</h6>
                <h3 class="admin-kpi-value mb-0">
                    <?php echo $citas_pendientes; ?>
                </h3>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card admin-kpi-card">
            <div class="card-body">
                <h6 class="text-muted mb-2">Ingresos Totales</h6>
                <h3 class="admin-kpi-value mb-0">
                    €<?php echo number_format($ingresos, 2, ',', '.'); ?>
                </h3>
            </div>
        </div>
    </div>
</div>

<!-- PRÓXIMAS CITAS -->
<div class="card mb-40">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-calendar-event"></i> Próximas Citas
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($proximas_citas)): ?>
            <div class="no-data">
                <p>No hay citas próximas</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha y Hora</th>
                            <th>Cliente</th>
                            <th>Barbero</th>
                            <th>Servicio</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($proximas_citas as $cita): ?>
                        <tr>
                            <td>
                                <strong><?php echo formatear_fecha($cita['fecha']); ?></strong><br>
                                <small class="text-muted"><?php echo formatear_hora($cita['hora']); ?></small>
                            </td>
                            <td><?php echo e($cita['cliente_nombre'] ?? 'N/A'); ?></td>
                            <td><?php echo e(trim(($cita['barbero_nombre'] ?? '') . ' ' . ($cita['barbero_apellidos'] ?? '')) ?: 'N/A'); ?></td>
                            <td><?php echo e($cita['servicio_nombre'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="badge badge-estado-<?php echo $cita['estado']; ?>">
                                    <?php echo ucfirst($cita['estado']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="citas.php?editar=<?php echo $cita['ID_cita']; ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil"></i> Editar
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <a href="citas.php" class="btn btn-secondary mt-2">
                Ver Todas las Citas
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- ACCESOS RÁPIDOS -->
<h5 class="mb-3">
    <i class="bi bi-lightning"></i> Accesos Rápidos
</h5>

<div class="row">
    <div class="col-md-6 mb-3">
        <a href="usuarios.php" class="card admin-shortcut">
            <div class="card-body text-center">
                <i class="bi bi-people"></i>
                <h6>Gestionar Usuarios</h6>
                <small class="text-muted">Ver, editar y eliminar clientes</small>
            </div>
        </a>
    </div>
    
    <div class="col-md-6 mb-3">
        <a href="barberos.php" class="card admin-shortcut">
            <div class="card-body text-center">
                <i class="bi bi-person-badge"></i>
                <h6>Gestionar Barberos</h6>
                <small class="text-muted">Crear, editar y asignar horarios</small>
            </div>
        </a>
    </div>
    
    <div class="col-md-6 mb-3">
        <a href="servicios.php" class="card admin-shortcut">
            <div class="card-body text-center">
                <i class="bi bi-scissors"></i>
                <h6>Gestionar Servicios</h6>
                <small class="text-muted">Crear, editar y establecer precios</small>
            </div>
        </a>
    </div>
    
    <div class="col-md-6 mb-3">
        <a href="citas.php" class="card admin-shortcut">
            <div class="card-body text-center">
                <i class="bi bi-calendar-check"></i>
                <h6>Gestionar Citas</h6>
                <small class="text-muted">Confirmar, completar y cancelar citas</small>
            </div>
        </a>
    </div>
    
    <div class="col-md-6 mb-3">
        <a href="configuracion.php" class="card admin-shortcut">
            <div class="card-body text-center">
                <i class="bi bi-gear"></i>
                <h6>Configuración</h6>
                <small class="text-muted">Ajustes generales del sistema</small>
            </div>
        </a>
    </div>
    
    <div class="col-md-6 mb-3">
        <a href="../index.php" class="card admin-shortcut">
            <div class="card-body text-center">
                <i class="bi bi-house"></i>
                <h6>Salir al Sitio</h6>
                <small class="text-muted">Ver la página pública</small>
            </div>
        </a>
    </div>
</div>

<?php
$contenido = ob_get_clean();
include '../plantilla.php';
?>
