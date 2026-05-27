<?php
/*
 * index.php
 * =========
 * Página principal del sitio.
 * Muestra el héroe (hero), la información de contacto,
 * la lista de servicios, el equipo de barberos y las citas próximas.
 * Incluye llamadas a la acción según si el usuario está logueado o no.
 */

require_once 'config.php';
require_once 'funciones.php';

$titulo_pagina = 'Inicio - ' . APP_NAME;

// ---------------------------------------------------------------
// CARGA DE DATOS
// ---------------------------------------------------------------
// Traemos la configuración, servicios y barberos desde la base de datos
$config = obtener_config_sistema($mysqli);
$servicios = obtener_servicios($mysqli);
$barberos = obtener_barberos($mysqli);

// Mapas de fotos por defecto para barberos (fallback cuando no hay foto subida)
$fotos_barberos = [
    'carlos' => 'carlos.png',
    'juan' => 'Juan.png',
    'pepe' => 'pepe.png',
    'default' => 'juanPerez.jpg'
];
// Mapas de fotos por defecto para servicios (fallback cuando no hay foto subida)
$fotos_servicios = [
    'corte' => 'cortePelo.png',
    'barba' => 'Barba.png',
    'corte_barba' => 'CorteBarbaPelo.png',
    'tinte' => 'tinte.png',
    'lavado' => 'lavado_corte.jpg',
    'default' => 'cortePelo.png'
];
$citas_proximas = [];
$proximas_citas = 0;

// Si el usuario está logueado, cargamos sus próximas citas
if ($usuario_logueado) {
    $citas_proximas = obtener_citas_usuario($usuario_id, $mysqli);
    // Filtramos solo las que están pendientes o confirmadas y son futuras
    $proximas_citas = count(array_filter($citas_proximas, fn($c) => ($c['estado'] === 'pendiente' || $c['estado'] === 'confirmada') && $c['fecha'] >= date('Y-m-d')));
}

// ---------------------------------------------------------------
// ESTILOS ADICIONALES (CSS embebido)
// ---------------------------------------------------------------
$estilos_adicionales = '<style>
    .container {
        max-width: 1300px;
    }

    .home-hero {
        background: linear-gradient(140deg, rgba(39,39,39,0.95) 0%, rgba(21,21,21,0.97) 100%);
        color: #f3f3f3;
        padding: 64px 28px;
        border: 1px solid rgba(255,255,255,0.14);
        border-radius: 16px;
        margin-bottom: 42px;
        overflow: hidden;
        position: relative;
    }

    .home-hero::after {
        content: "";
        position: absolute;
        right: -70px;
        top: -70px;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, rgba(255,255,255,0) 70%);
    }

    .home-hero-title {
        font-size: 46px;
        margin-bottom: 16px;
    }

    .home-section-title {
        margin-bottom: 24px;
        color: #000000 !important;
    }

    .home-info-icon {
        font-size: 32px;
        margin-bottom: 10px;
        color: #e5e5e5;
    }

    .home-hero-text {
        font-size: 19px;
        margin-bottom: 25px;
        opacity: 0.95;
    }

    .home-hero-action-primary,
    .home-cta-register {
        margin-right: 12px;
    }

    .home-hero-photo {
        max-width: 100%;
        height: 330px;
        object-fit: cover;
        border-radius: 14px;
        border: 2px solid rgba(255, 255, 255, 0.10);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3);
        margin-left: -10px;
    }

    .home-team-head {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 24px;
    }

    .home-barber-photo {
        width: 280px;
        height: 280px;
        object-fit: cover;
        border-radius: 14px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 10px 22px rgba(0, 0, 0, 0.35);
        transform: none;
        margin-left: 0;
    }

    .home-service-photo {
        width: 100%;
        height: 280px;
        object-fit: cover;
        border-radius: 14px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 10px 22px rgba(0, 0, 0, 0.35);
        transform: none;
        margin-left: 0;
    }

    .home-cta {
        padding: 24px;
        background: linear-gradient(165deg, rgba(30, 30, 30, 0.93), rgba(25, 25, 25, 0.96)) !important;
        color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.12);
    }

    .home-cta h3 {
        margin-bottom: 12px;
        color: #ffffff !important;
    }

    .home-cta p {
        color: #ffffff !important;
        margin-bottom: 18px;
    }

    @media (max-width: 768px) {
        .container {
            max-width: 100%;
        }

        .home-hero {
            padding: 32px 20px;
        }

        .home-hero-title {
            font-size: 34px;
        }

        .home-hero-text {
            font-size: 17px;
        }

        .home-hero-photo {
            max-width: 100%;
            height: 240px;
            margin-top: 16px;
        }

        .row.mb-40 .card {
            min-height: auto;
        }

        .row.mb-40 .card .card-body {
            padding: 20px;
        }

        .row.mb-40 .card .card-title {
            font-size: 24px;
        }

        .row.mb-40 .card .card-text {
            font-size: 17px;
        }

        .home-barber-photo {
            width: 150px;
            height: 150px;
            transform: none;
            margin: 20px 0 0 0;
        }
        .home-team-head {
            flex-direction: column;
            align-items: center;
        }

        .home-service-photo {
            height: 200px;
        }
    }
</style>';

// ---------------------------------------------------------------
// CAPTURAR CONTENIDO
// ---------------------------------------------------------------
ob_start();
?>

<!-- ============================================================
     HERO SECTION
     ============================================================ -->
<section class="home-hero">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="home-hero-title">
                Bienvenido a <?php echo e($config['nombre_barberia']); ?>
            </h1>
            <p class="home-hero-text">
                La forma ms fcil de reservar tu corte de barba. Elige barbero, servicio y hora en segundos.
            </p>
            <?php if ($usuario_logueado): ?>
                <a href="nueva_reserva.php" class="btn btn-light btn-lg home-hero-action-primary">
                    <i class="bi bi-calendar-plus"></i> Reservar Cita
                </a>
                <a href="reservas.php" class="btn btn-secondary btn-lg">
                    <i class="bi bi-calendar-check"></i> Mis Reservas (<?php echo $proximas_citas; ?>)
                </a>
            <?php else: ?>
                <a href="login.php" class="btn btn-light btn-lg">
                    <i class="bi bi-box-arrow-in-right"></i> Inicia Sesin para Reservar
                </a>
            <?php endif; ?>
        </div>
        <div class="col-md-6 text-center">
            <img class="home-hero-photo" src="<?php echo APP_URL; ?>/assets/img/hero-barberia.jpg" alt="Foto de la barbera">
        </div>
    </div>
</section>

<!-- ============================================================
     INFORMACIN GENERAL (Horario, Contacto, Ubicacin)
     ============================================================ -->
<div class="row mb-40">
    <div class="col-md-4 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-clock home-info-icon"></i>
                <h5 class="card-title">Horarios</h5>
                <p class="card-text text-muted">
                    <strong><?php echo formatear_hora($config['horario_apertura']); ?></strong> - 
                    <strong><?php echo formatear_hora($config['horario_cierre']); ?></strong>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-telephone home-info-icon"></i>
                <h5 class="card-title">Contacto</h5>
                <p class="card-text text-muted">
                    <strong><?php echo e($config['telefono_barberia']); ?></strong>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-geo-alt home-info-icon"></i>
                <h5 class="card-title">Ubicacin</h5>
                <p class="card-text text-muted">
                    <strong><?php echo e($config['ciudad']); ?></strong>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     SECCIN DE SERVICIOS
     ============================================================ -->
<h2 class="home-section-title">
    <i class="bi bi-scissors"></i> Nuestros Servicios
</h2>

<div class="row mb-40 home-services-row">
    <?php foreach ($servicios as $servicio): ?>
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <?php
            // Determinamos qu foto mostrar: primero la de la BD, si no, fallback por nombre
            $foto_servicio = $servicio['foto'] ?? '';
            if (empty($foto_servicio)) {
                $nombre_servicio = strtolower($servicio['nombre']);
                // Buscamos coincidencias en el nombre para asignar una foto por defecto
                if ((strpos($nombre_servicio, 'corte') !== false || strpos($nombre_servicio, 'cabello') !== false) && strpos($nombre_servicio, 'barba') !== false) {
                    $foto_servicio = $fotos_servicios['corte_barba'];
                } elseif (strpos($nombre_servicio, 'lavado') !== false) {
                    $foto_servicio = $fotos_servicios['lavado'];
                } elseif (strpos($nombre_servicio, 'tinte') !== false || strpos($nombre_servicio, 'color') !== false) {
                    $foto_servicio = $fotos_servicios['tinte'];
                } elseif (strpos($nombre_servicio, 'barba') !== false) {
                    $foto_servicio = $fotos_servicios['barba'];
                } elseif (strpos($nombre_servicio, 'corte') !== false || strpos($nombre_servicio, 'cabello') !== false) {
                    $foto_servicio = $fotos_servicios['corte'];
                } else {
                    // Si no encontramos coincidencia, usamos la imagen por defecto
                    $foto_servicio = $fotos_servicios['default'];
                }
            }
            ?>
            <img class="home-service-photo" src="<?php echo APP_URL; ?>/assets/img/servir_imagen.php?tipo=servicio&archivo=<?php echo urlencode($foto_servicio); ?>" alt="Servicio de <?php echo e($servicio['nombre']); ?>">
            <div class="card-body">
                <h5 class="card-title home-service-title">
                    <?php echo e($servicio['nombre']); ?>
                </h5>
                <p class="card-text"><?php echo e($servicio['descripcion']); ?></p>
                <div class="home-service-meta">
                    <span class="home-service-price">
                        €<?php echo number_format($servicio['precio'], 2, ',', '.'); ?>
                    </span>
                    <span class="badge bg-secondary">
                        <i class="bi bi-clock"></i> <?php echo $servicio['duracion_minutos']; ?> min
                    </span>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ============================================================
     SECCIN DE BARBEROS (Nuestro Equipo)
     ============================================================ -->
<h2 class="home-section-title">
    <i class="bi bi-person-check"></i> Nuestro Equipo
</h2>

<div class="row mb-40 home-team-row">
    <?php foreach ($barberos as $barbero): ?>
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="home-team-head">
                    <?php
                    // Foto del barbero: primero mirar el campo foto de la BD, si no usar el mapa por defecto
                    $foto_barbero = !empty($barbero['foto']) ? $barbero['foto'] : ($fotos_barberos[strtolower($barbero['nombre'])] ?? $fotos_barberos['default']);
                    ?>
                    <div>
                        <h5 class="card-title">
                            <i class="bi bi-person-fill home-team-name-icon"></i>
                            <?php echo e($barbero['nombre'] . ' ' . $barbero['apellidos']); ?>
                        </h5>
                        <p class="card-text text-muted">
                            <i class="bi bi-briefcase"></i> <?php echo e($barbero['especialidad']); ?>
                        </p>
                        <p class="card-text text-muted">
                            <i class="bi bi-award"></i> <?php echo $barbero['experiencia_anos']; ?> aos de experiencia
                        </p>
                        <p class="card-text text-muted">
                            <i class="bi bi-clock-history"></i> 
                            <?php echo formatear_hora($barbero['horario_inicio']); ?> - 
                            <?php echo formatear_hora($barbero['horario_fin']); ?>
                        </p>
                        <p class="card-text text-muted">
                            <i class="bi bi-calendar"></i> <?php echo e($barbero['dias_atiende']); ?>
                        </p>
                    </div>
                    <img class="home-barber-photo" src="<?php echo APP_URL; ?>/assets/img/servir_imagen.php?tipo=barbero&archivo=<?php echo urlencode($foto_barbero); ?>" alt="Foto de <?php echo e($barbero['nombre']); ?>">
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ============================================================
     SECCIN DE CITAS PRXIMAS (Solo si est logueado)
     ============================================================ -->
<?php if ($usuario_logueado && !empty($citas_proximas)): ?>
    <h2 class="home-section-title">
        <i class="bi bi-calendar-event"></i> Mis Prximas Citas
    </h2>
    
    <div class="row mb-40">
        <?php 
        $mostradas = 0;
        foreach ($citas_proximas as $cita): 
            // Solo mostramos citas pendientes/confirmadas, futuras, mximo 3
            if (($cita['estado'] === 'pendiente' || $cita['estado'] === 'confirmada') && $cita['fecha'] >= date('Y-m-d') && $mostradas < 3):
                $mostradas++;
        ?>
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <?php echo e($cita['servicio_nombre']); ?>
                    </h5>
                    <p class="card-text">
                        <strong>Barbero:</strong> <?php echo e($cita['barbero_nombre'] . ' ' . $cita['barbero_apellidos']); ?>
                    </p>
                    <p class="card-text">
                        <strong>Fecha:</strong> <?php echo e(formatear_fecha($cita['fecha'])); ?>
                    </p>
                    <p class="card-text">
                        <strong>Hora:</strong> <?php echo e(formatear_hora($cita['hora'])); ?>
                    </p>
                    <p class="card-text">
                        <strong>Duracin:</strong> <?php echo $cita['duracion_minutos']; ?> minutos
                    </p>
                    <div class="mt-20">
                        <span class="badge badge-estado-<?php echo $cita['estado']; ?>">
                            <i class="bi bi-circle-fill"></i> 
                            <?php echo ucfirst($cita['estado']); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; endforeach; ?>
    </div>
<?php endif; ?>

<!-- ============================================================
     CALL TO ACTION (Segn si est logueado o no)
     ============================================================ -->
<?php if ($usuario_logueado): ?>
    <section class="bg-light rounded text-center home-cta">
        <h3>Listo para tu siguiente corte?</h3>
        <a href="nueva_reserva.php" class="btn btn-primary btn-lg">
            <i class="bi bi-calendar-plus"></i> Reservar Ahora
        </a>
    </section>
<?php else: ?>
    <section class="bg-light rounded text-center home-cta">
        <h3>An no tienes cuenta?</h3>
        <p>Regstrate y disfruta de nuestros servicios</p>
        <a href="registro.php" class="btn btn-primary btn-lg home-cta-register">
            <i class="bi bi-person-plus"></i> Crear Cuenta
        </a>
        <a href="login.php" class="btn btn-primary btn-lg">
            <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesin
        </a>
    </section>
<?php endif; ?>

<?php
$contenido = ob_get_clean();
include 'plantilla.php';
?>
