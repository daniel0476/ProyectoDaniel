<?php
/*
 * plantilla.php
 * =============
 * Plantilla base (layout) que usan todas las páginas del proyecto.
 * Incluye: cabecera con nav, estilos globales, contenedor principal y footer.
 * Cada página inyecta su contenido en $contenido y opcionalmente
 * $estilos_adicionales y $scripts_adicionales.
 */

// ---------------------------------------------------------------
// CABECERAS HTTP ANTI-CACHÉ
// ---------------------------------------------------------------
// Evitamos que el navegador guarde en caché páginas dinámicas
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Detectamos si es una página del panel admin (para ajustar rutas relativas)
$es_admin_page = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo_pagina ?? APP_NAME; ?></title>
    <link rel="icon" type="image/png" href="<?php echo APP_URL; ?>/assets/img/logoBarberia.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css" rel="stylesheet">
    <style>
        @font-face {
            font-family: 'Maxlock';
            src: url('<?php echo APP_URL; ?>/assets/fonts/maxlock/Maxlock.otf') format('opentype');
        }

        @font-face {
            font-family: 'BlendaScript';
            src: url('<?php echo APP_URL; ?>/assets/fonts/blenda_script/Blenda Script.otf') format('opentype');
        }

        :root {
            --accent: #d9d9d9;
            --accent-soft: #f1f1f1;
            --ink: #121212;
            --panel: #1f1f1f;
            --panel-soft: #303030;
            --text-main: #f3f3f3;
            --text-muted: #b8b8b8;
            --success: #9f9f9f;
            --warning: #7f7f7f;
            --danger: #5f5f5f;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-main);
            background: linear-gradient(180deg, #ffffff 0%, #f7f7f7 55%, #efefef 100%);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            animation: fadeInBody 1s ease-out;
            position: relative;
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center center;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: transparent;
            z-index: -1;
            pointer-events: none;
            opacity: 1;
        }

        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: transparent;
            z-index: 0;
            pointer-events: none;
            opacity: 0;
        }

        body {
            background: url("<?php echo APP_URL; ?>/assets/img/barberia_vigo_blanco.png") center center / cover no-repeat;
        }

        @keyframes fadeInBody {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        /* HEADER */
        header {
            background: #141414;
            color: var(--text-main);
            border-bottom: 1px solid rgba(255, 255, 255, 0.16);
            padding: 8px 0;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.32);
            position: static;
        }
        
        .navbar-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0;
            font-family: inherit;
            font-weight: 700;
            font-size: 32px;
            letter-spacing: 1px;
            color: var(--accent) !important;
            margin-right: 0;
            text-align: center;
        }
        
        .navbar-brand .brand-logo {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.28);
            box-shadow: 0 5px 12px rgba(0, 0, 0, 0.35);
        }

        .header-guest .container {
            justify-content: center;
        }

        .header-guest .navbar-toggler,
        .header-guest .navbar-collapse {
            display: none !important;
        }
        
        .nav-link {
            color: var(--text-main) !important;
            margin: 0 8px;
            font-family: inherit;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.25s ease;
            border-bottom: 2px solid transparent;
        }
        
        .nav-link:hover {
            color: var(--accent-soft) !important;
            border-bottom-color: var(--accent-soft);
            transform: translateY(-1px);
        }
        
        .nav-link.active {
            color: var(--accent) !important;
            border-bottom-color: var(--accent);
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        
        .user-info {
            color: var(--text-muted);
            font-size: 15px;
        }
        
        .user-info strong {
            color: var(--accent-soft);
            font-family: inherit;
            font-weight: 700;
            font-size: 16px;
        }
        
        .btn-logout {
            background: transparent;
            border: 1px solid var(--accent);
            color: var(--accent);
            padding: 7px 14px;
            border-radius: 999px;
            text-decoration: none;
            transition: all 0.25s ease;
        }
        
        .btn-logout:hover {
            background: var(--accent);
            color: #1b1b1b;
        }
        
        /* MAIN CONTENT */
        main {
            flex: 1;
            padding: 38px 20px;
        }
        
        .container {
            max-width: 1200px;
        }
        
        /* CARDS */
        .card {
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 14px;
            background: linear-gradient(165deg, rgba(30, 30, 30, 0.93), rgba(25, 25, 25, 0.96));
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.3);
            transition: all 0.25s ease;
            margin-bottom: 20px;
        }
        
        .card:hover {
            transform: translateY(-4px);
            border-color: rgba(255, 255, 255, 0.25);
            box-shadow: 0 18px 30px rgba(0, 0, 0, 0.4);
        }
        
        .card-header {
            background: linear-gradient(120deg, #2f2f2f 0%, #1f1f1f 100%);
            color: var(--accent-soft);
            border: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 14px 14px 0 0;
            font-family: inherit;
            font-weight: 700;
            letter-spacing: 0.4px;
            padding: 18px 20px;
        }
        
        .card-body {
            padding: 24px;
            color: var(--text-main);
        }
        
        /* BUTTONS */
        .btn {
            border-radius: 999px;
            padding: 8px 20px;
            font-family: inherit;
            font-weight: 600;
            letter-spacing: 0.4px;
            transition: all 0.25s ease;
            border: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #f5f5f5 0%, #bdbdbd 100%);
            color: #141414;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(255, 255, 255, 0.14);
            color: #141414;
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.08);
            color: var(--text-main);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.16);
            color: var(--text-main);
        }
        
        .btn-sm {
            padding: 5px 12px;
            font-size: 13px;
        }

        /* ICONOS BOOTSTRAP */
        .bi {
            color: #333333;
        }

        .btn-primary .bi {
            color: #141414;
        }

        .btn-secondary .bi {
            color: var(--text-main);
        }
        
        /* FORMULARIOS */
        .form-label {
            color: var(--accent-soft);
            font-family: inherit;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 10px;
            transition: all 0.25s;
            background: rgba(255, 255, 255, 0.08);
            color: var(--text-main);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--accent-soft);
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.14);
            background: rgba(255, 255, 255, 0.12);
            color: var(--text-main);
        }

        .form-control::placeholder {
            color: rgba(233, 233, 233, 0.6);
        }
        
        /* TABLAS */
        .table {
            background: transparent;
            color: var(--text-main);
        }
        
        .table thead {
            background: rgba(255, 255, 255, 0.06);
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }
        
        .table th {
            color: var(--accent-soft);
            border: none;
            font-family: 'Maxlock', serif;
        }
        
        .table tbody tr {
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            transition: background 0.3s;
        }
        
        .table tbody tr:hover {
            background: rgba(255, 255, 255, 0.04);
        }

        .table td,
        .table th,
        .table small,
        .table .text-muted {
            color: var(--text-main) !important;
        }
        
        .badge {
            border-radius: 20px;
            padding: 5px 12px;
            font-family: 'Maxlock', serif;
        }
        
        .badge-estado-pendiente {
            background: #8f8f8f;
            color: #111;
        }
        
        .badge-estado-confirmada {
            background: #cfcfcf;
            color: #111;
        }
        
        .badge-estado-cancelada {
            background: #555555;
            color: white;
        }
        
        .badge-estado-completada {
            background: #a8a8a8;
            color: white;
        }
        
        /* ALERTAS */
        .alert {
            border: none;
            border-radius: 8px;
            border-left: 4px solid;
            margin-bottom: 20px;
        }
        
        .alert-success {
            border-left-color: #bcbcbc;
            background: #f3f3f3;
            color: #1f1f1f;
        }
        
        .alert-danger {
            border-left-color: #6b6b6b;
            background: #ececec;
            color: #202020;
        }
        
        .alert-warning {
            border-left-color: #8e8e8e;
            background: #f5f5f5;
            color: #232323;
        }
        
        .alert-info {
            border-left-color: #9d9d9d;
            background: #efefef;
            color: #232323;
        }

        a {
            color: var(--accent-soft);
        }

        a:hover {
            color: var(--accent);
        }
        
        /* FOOTER */
        footer {
            background: rgba(18, 18, 18, 0.96);
            color: var(--text-muted);
            text-align: center;
            padding: 30px 20px;
            margin-top: 40px;
            border-top: 1px solid rgba(255, 255, 255, 0.14);
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 14px;
            margin: 12px 0 18px;
        }
        
        .footer-links a {
            color: #ffffff;
            text-decoration: none;
            font-weight: 500;
            font-size: 16px;
        }
        
        .footer-links a i,
        .footer-copy-icon {
            margin-right: 10px;
            font-size: 20px;
            vertical-align: middle;
            color: #ffffff;
        }

        .footer-links a:hover {
            color: var(--accent);
        }
        
        footer a {
            color: #ffffff;
            text-decoration: none;
        }
        
        footer a:hover {
            color: var(--accent);
        }
        
        /* UTILIDADES */
        .text-muted {
            color: var(--text-muted) !important;
        }

        .bg-light {
            background: rgba(255, 255, 255, 0.06) !important;
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: var(--text-main);
        }

        .rounded,
        .rounded-3 {
            border-radius: 14px !important;
        }

        .modal-content {
            background: linear-gradient(165deg, rgba(30, 30, 30, 0.98), rgba(22, 22, 22, 0.98));
            color: var(--text-main);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .modal-header,
        .modal-footer {
            border-color: rgba(255, 255, 255, 0.12);
        }

        .btn-close {
            filter: invert(1) brightness(1.1);
        }
        
        .mb-20 {
            margin-bottom: 20px;
        }
        
        .mt-20 {
            margin-top: 20px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
        }
        
        .no-data i {
            font-size: 48px;
            margin-bottom: 10px;
            opacity: 0.5;
        }
        
        /* RESPONSIVE */
        @media (max-width: 768px) {
            main {
                padding: 20px 10px;
            }
            
            .navbar-brand {
                font-size: 24px;
                margin-right: 10px;
            }

            .navbar-brand .brand-logo {
                width: 40px;
                height: 40px;
            }
            
            .nav-link {
                margin: 5px 0;
            }
            
            .table {
                font-size: 12px;
            }
            
            .user-menu {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }

        .dropdown-menu {
            background: #1f1f1f;
            border: 1px solid rgba(255, 255, 255, 0.16);
        }

        .dropdown-item {
            color: var(--text-main);
        }

        .dropdown-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--accent-soft);
        }

        .dropdown-divider {
            border-top-color: rgba(255, 255, 255, 0.16);
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Maxlock', serif;
            color: var(--accent-soft);
            letter-spacing: 0.4px;
        }

        .navbar-toggler {
            border-color: rgba(255, 255, 255, 0.24);
        }

        .navbar-toggler-icon {
            filter: invert(1);
        }

        @media (max-width: 576px) {
            .card-body {
                padding: 18px;
            }

            .btn {
                padding: 8px 16px;
                font-size: 14px;
            }

            h1 {
                font-size: 30px;
            }

            h2 {
                font-size: 26px;
            }
        }
    </style>
    <?php if (isset($estilos_adicionales)): ?>
        <?php echo $estilos_adicionales; ?>
    <?php endif; ?>
</head>
<body>
    <!-- HEADER -->
    <header class="sticky-top">
        <nav class="navbar navbar-expand-lg <?php echo $usuario_logueado ? 'header-auth' : 'header-guest'; ?>">
            <div class="container">
                <a class="navbar-brand" href="<?php echo APP_URL; ?>/index.php">
                    <img class="brand-logo" src="<?php echo APP_URL; ?>/assets/img/logoBarberia.png" alt="Logo">
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <?php if ($usuario_logueado): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo APP_URL; ?>/index.php">
                                    <i class="bi bi-house"></i> Inicio
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo APP_URL; ?>/reservas.php">
                                    <i class="bi bi-calendar-check"></i> Mis Reservas
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo APP_URL; ?>/nueva_reserva.php">
                                    <i class="bi bi-plus-circle"></i> Nueva Reserva
                                </a>
                            </li>
                            
                            <!-- Menú de administración (solo visible para admins) -->
                    <?php if ($es_admin): ?>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="adminMenu" role="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-shield-lock"></i> Admin
                                    </a>
                                    <!-- Submenú con las secciones del panel de control -->
                                    <ul class="dropdown-menu" aria-labelledby="adminMenu">
                                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/admin/dashboard.php">Dashboard</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/admin/usuarios.php">Usuarios</a></li>
                                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/admin/barberos.php">Barberos</a></li>
                                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/admin/servicios.php">Servicios</a></li>
                                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/admin/citas.php">Citas</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/admin/configuracion.php">Configuración</a></li>
                                    </ul>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                    </ul>
                    
                    <?php if ($usuario_logueado): ?>
                    <div class="user-menu">
                            <div class="user-info">
                                <i class="bi bi-person-circle"></i>
                                <strong><?php echo e($_SESSION['usuario_nombre']); ?></strong>
                            </div>
                            <a href="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false ? '../' : '') . 'mi_perfil.php'; ?>" class="btn btn-secondary btn-sm">
                                <i class="bi bi-gear"></i> Perfil
                            </a>
                            <a href="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false ? '../' : '') . 'logout.php'; ?>" class="btn-logout">
                                <i class="bi bi-box-arrow-right"></i> Salir
                            </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>
    
    <!-- MAIN CONTENT -->
    <main>
        <div class="container">
            <?php echo mostrar_mensaje(); ?>
            <?php echo $contenido; ?>
        </div>
    </main>
    
    <!-- FOOTER -->
    <footer>
        <p><i class="bi bi-shop footer-copy-icon"></i>&copy; 2026 <?php echo APP_NAME; ?> - Todos los derechos reservados</p>
        <div class="footer-links">
            <a href="https://www.instagram.com" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-instagram"></i> Instagram
            </a>
            <a href="https://www.facebook.com" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-facebook"></i> Facebook
            </a>
            <a href="https://www.twitter.com" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-twitter"></i> Twitter
            </a>
            <a href="mailto:info@barberia.com">
                <i class="bi bi-envelope"></i> info@barberia.com
            </a>
        </div>
        <small>Proyecto Final de Curso - Desarrollo Web</small>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/es.js"></script>
    <?php if (isset($scripts_adicionales)): ?>
        <?php echo $scripts_adicionales; ?>
    <?php endif; ?>
</body>
</html>
