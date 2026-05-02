-- phpMyAdmin SQL Dump
-- version 5.2.1
-- PROYECTO BARBERÍA - VERSIÓN MEJORADA
-- Base de datos con campos adicionales para reservas completas

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@OLD_COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- =====================================================
-- Base de datos: `barberia`
-- =====================================================

-- =====================================================
-- TABLA: usuarios
-- Descripción: Clientes registrados en la plataforma
-- =====================================================
CREATE TABLE `usuarios` (
  `ID_usuario` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellidos` varchar(80) NOT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `rol` enum('cliente','admin') NOT NULL DEFAULT 'cliente',
  `fecha_registro` timestamp DEFAULT CURRENT_TIMESTAMP,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insertar datos de ejemplo
INSERT INTO `usuarios` (`ID_usuario`, `nombre`, `apellidos`, `telefono`, `email`, `contrasena`, `rol`, `fecha_registro`, `activo`) VALUES
(1, 'Juan', 'Perez', '611111111', 'juan@email.com', '$2y$10$rS9Pv3M4.8qL9nX2K5tN.OW1jZ6mP7kL2hG5dY8wQ3rE4tU7nO', 'cliente', NOW(), 1),
(2, 'Ana', 'Martinez', '612222222', 'ana@email.com', '$2y$10$rS9Pv3M4.8qL9nX2K5tN.OW1jZ6mP7kL2hG5dY8wQ3rE4tU7nO', 'cliente', NOW(), 1),
(3, 'Luis', 'Rodriguez', '613333333', 'luis@email.com', '$2y$10$rS9Pv3M4.8qL9nX2K5tN.OW1jZ6mP7kL2hG5dY8wQ3rE4tU7nO', 'cliente', NOW(), 1),
(4, 'Marta', 'Sanchez', '614444444', 'marta@email.com', '$2y$10$rS9Pv3M4.8qL9nX2K5tN.OW1jZ6mP7kL2hG5dY8wQ3rE4tU7nO', 'cliente', NOW(), 1),
(5, 'Laura', 'Garcia', '615555555', 'admin@email.com', '$2y$10$rS9Pv3M4.8qL9nX2K5tN.OW1jZ6mP7kL2hG5dY8wQ3rE4tU7nO', 'admin', NOW(), 1);

-- =====================================================
-- TABLA: barberos
-- Descripción: Profesionales que realizan los servicios
-- =====================================================
CREATE TABLE `barberos` (
  `DNI_barbero` varchar(15) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellidos` varchar(80) NOT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `especialidad` varchar(100) DEFAULT 'Cortes generales',
  `experiencia_anos` int(2) DEFAULT 1,
  `horario_inicio` time DEFAULT '09:00:00',
  `horario_fin` time DEFAULT '18:00:00',
  `dias_atiende` varchar(50) DEFAULT 'Lun-Vie',
  `activo` tinyint(1) DEFAULT 1,
  `fecha_registro` timestamp DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insertar datos de ejemplo
INSERT INTO `barberos` (`DNI_barbero`, `nombre`, `apellidos`, `telefono`, `especialidad`, `experiencia_anos`, `horario_inicio`, `horario_fin`, `dias_atiende`, `activo`, `fecha_registro`) VALUES
('12345678A', 'Carlos', 'Gomez', '600111111', 'Cortes degradados', 8, '09:00:00', '18:00:00', 'Lun-Vie', 1, NOW()),
('87654321B', 'Miguel', 'Lopez', '600222222', 'Barba y estilos', 5, '10:00:00', '19:00:00', 'Lun-Sab', 1, NOW());

-- =====================================================
-- TABLA: servicios
-- Descripción: Servicios ofrecidos con precio y duración
-- =====================================================
CREATE TABLE `servicios` (
  `ID_servicio` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(6,2) NOT NULL,
  `duracion_minutos` int(3) DEFAULT 30,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insertar datos de ejemplo
INSERT INTO `servicios` (`ID_servicio`, `nombre`, `descripcion`, `precio`, `duracion_minutos`, `activo`) VALUES
(1, 'Corte de pelo', 'Corte básico con máquina', 12.00, 30, 1),
(2, 'Corte degradado', 'Corte con gradación personalizada', 15.00, 45, 1),
(3, 'Arreglo de barba', 'Recorte y modelado de barba', 8.00, 20, 1),
(4, 'Afeitado clásico', 'Afeitado tradicional con brocha', 10.00, 25, 1),
(5, 'Lavado y corte', 'Lavado + corte + secado', 18.00, 45, 1);

-- =====================================================
-- TABLA: citas
-- Descripción: Reservas de clientes con barberos
-- =====================================================
CREATE TABLE `citas` (
  `ID_cita` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `DNI_barbero` varchar(15) NOT NULL,
  `ID_usuario` int(11) NOT NULL,
  `ID_servicio` int(11) NOT NULL,
  `precio_final` decimal(6,2) NOT NULL,
  `estado` enum('pendiente','confirmada','cancelada','completada') DEFAULT 'pendiente',
  `notas_cliente` text DEFAULT NULL,
  `notas_admin` text DEFAULT NULL,
  `fecha_creacion` timestamp DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insertar datos de ejemplo
INSERT INTO `citas` (`ID_cita`, `fecha`, `hora`, `DNI_barbero`, `ID_usuario`, `ID_servicio`, `precio_final`, `estado`, `fecha_creacion`) VALUES
(1, '2026-04-01', '10:00:00', '12345678A', 1, 1, 12.00, 'confirmada', NOW()),
(2, '2026-04-01', '11:00:00', '87654321B', 2, 2, 15.00, 'confirmada', NOW()),
(3, '2026-04-02', '09:30:00', '12345678A', 3, 3, 8.00, 'pendiente', NOW()),
(4, '2026-04-02', '12:00:00', '87654321B', 4, 4, 10.00, 'confirmada', NOW()),
(5, '2026-04-03', '10:30:00', '12345678A', 1, 5, 18.00, 'pendiente', NOW());

-- =====================================================
-- TABLA: horarios_disponibles
-- Descripción: Define bloques de tiempo disponibles por barbero y día
-- =====================================================
CREATE TABLE `horarios_disponibles` (
  `ID_horario` int(11) NOT NULL,
  `DNI_barbero` varchar(15) NOT NULL,
  `fecha` date NOT NULL,
  `inicio_hora` time NOT NULL,
  `fin_hora` time NOT NULL,
  `disponible` tinyint(1) DEFAULT 1,
  `tipo` enum('normal','descanso','festivo') DEFAULT 'normal'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insertar datos de ejemplo - Horarios disponibles próximos 30 días
INSERT INTO `horarios_disponibles` (`DNI_barbero`, `fecha`, `inicio_hora`, `fin_hora`, `disponible`, `tipo`) VALUES
('12345678A', '2026-03-28', '09:00:00', '18:00:00', 1, 'normal'),
('12345678A', '2026-03-29', '09:00:00', '18:00:00', 1, 'normal'),
('87654321B', '2026-03-28', '10:00:00', '19:00:00', 1, 'normal'),
('87654321B', '2026-03-29', '10:00:00', '19:00:00', 1, 'normal');

-- =====================================================
-- TABLA: configuracion_sistema
-- Descripción: Configuración general de la barbería
-- =====================================================
CREATE TABLE `configuracion_sistema` (
  `ID_config` int(11) NOT NULL,
  `nombre_barberia` varchar(100) DEFAULT 'Barbería Premium',
  `email_barberia` varchar(100) DEFAULT 'info@barberia.com',
  `telefono_barberia` varchar(15) DEFAULT '+34 911 222 333',
  `direccion` varchar(200) DEFAULT '',
  `ciudad` varchar(50) DEFAULT 'Madrid',
  `horario_apertura` time DEFAULT '09:00:00',
  `horario_cierre` time DEFAULT '18:00:00',
  `duracion_slot_minutos` int(3) DEFAULT 30,
  `tiempo_preparacion_minutos` int(3) DEFAULT 5,
  `aviso_cancelacion_horas` int(2) DEFAULT 24,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_actualizacion` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insertar configuración por defecto
INSERT INTO `configuracion_sistema` (`ID_config`, `nombre_barberia`, `email_barberia`, `telefono_barberia`, `direccion`, `ciudad`, `horario_apertura`, `horario_cierre`, `duracion_slot_minutos`, `tiempo_preparacion_minutos`, `aviso_cancelacion_horas`) VALUES
(1, 'Barbería Barbers', 'contacto@barberia.com', '+34 600 123 456', 'Calle Principal 123', 'Madrid', '09:00:00', '18:00:00', 30, 5, 24);

-- =====================================================
-- TABLA: historial_acceso
-- Descripción: Log de accesos para auditoría
-- =====================================================
CREATE TABLE `historial_acceso` (
  `ID_acceso` int(11) NOT NULL,
  `ID_usuario` int(11) NOT NULL,
  `fecha_acceso` timestamp DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) DEFAULT NULL,
  `navegador` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- DEFINIR CLAVES PRIMARIAS
-- =====================================================
ALTER TABLE `usuarios` ADD PRIMARY KEY (`ID_usuario`);
ALTER TABLE `usuarios` ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `barberos` ADD PRIMARY KEY (`DNI_barbero`);

ALTER TABLE `servicios` ADD PRIMARY KEY (`ID_servicio`);

ALTER TABLE `citas` ADD PRIMARY KEY (`ID_cita`);
ALTER TABLE `citas` ADD KEY `DNI_barbero` (`DNI_barbero`);
ALTER TABLE `citas` ADD KEY `ID_usuario` (`ID_usuario`);
ALTER TABLE `citas` ADD KEY `ID_servicio` (`ID_servicio`);

ALTER TABLE `horarios_disponibles` ADD PRIMARY KEY (`ID_horario`);
ALTER TABLE `horarios_disponibles` ADD KEY `DNI_barbero` (`DNI_barbero`);

ALTER TABLE `configuracion_sistema` ADD PRIMARY KEY (`ID_config`);

ALTER TABLE `historial_acceso` ADD PRIMARY KEY (`ID_acceso`);
ALTER TABLE `historial_acceso` ADD KEY `ID_usuario` (`ID_usuario`);

-- =====================================================
-- AUTO_INCREMENT
-- =====================================================
ALTER TABLE `usuarios` MODIFY `ID_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
ALTER TABLE `servicios` MODIFY `ID_servicio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
ALTER TABLE `citas` MODIFY `ID_cita` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
ALTER TABLE `horarios_disponibles` MODIFY `ID_horario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
ALTER TABLE `configuracion_sistema` MODIFY `ID_config` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `historial_acceso` MODIFY `ID_acceso` int(11) NOT NULL AUTO_INCREMENT;

-- =====================================================
-- RESTRICCIONES DE CLAVE FORÁNEA
-- =====================================================
ALTER TABLE `citas`
  ADD CONSTRAINT `citas_ibfk_1` FOREIGN KEY (`DNI_barbero`) REFERENCES `barberos` (`DNI_barbero`) ON DELETE RESTRICT,
  ADD CONSTRAINT `citas_ibfk_2` FOREIGN KEY (`ID_usuario`) REFERENCES `usuarios` (`ID_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `citas_ibfk_3` FOREIGN KEY (`ID_servicio`) REFERENCES `servicios` (`ID_servicio`) ON DELETE RESTRICT;

ALTER TABLE `horarios_disponibles`
  ADD CONSTRAINT `horarios_ibfk_1` FOREIGN KEY (`DNI_barbero`) REFERENCES `barberos` (`DNI_barbero`) ON DELETE CASCADE;

ALTER TABLE `historial_acceso`
  ADD CONSTRAINT `historial_ibfk_1` FOREIGN KEY (`ID_usuario`) REFERENCES `usuarios` (`ID_usuario`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
