-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 17-11-2025 a las 19:14:29
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `litosoft`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ls_areas`
--

CREATE TABLE `ls_areas` (
  `area_id` int(11) NOT NULL,
  `dep_id` int(11) NOT NULL,
  `area_nombre` varchar(100) NOT NULL,
  `area_estatus` enum('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  `area_fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `ls_areas`
--

INSERT INTO `ls_areas` (`area_id`, `dep_id`, `area_nombre`, `area_estatus`, `area_fecha_creacion`) VALUES
(1, 2, 'PEGADO Y EMPAQUE', 'ACTIVO', '2025-11-14 17:02:40'),
(2, 2, 'IMPRESIÓN', 'ACTIVO', '2025-11-14 17:07:40'),
(3, 2, 'SUAJE', 'ACTIVO', '2025-11-14 17:07:46'),
(4, 2, 'BARNIZ U.V', 'ACTIVO', '2025-11-14 17:07:57'),
(5, 2, 'LAMINADO', 'ACTIVO', '2025-11-14 17:08:21'),
(6, 2, 'CORTE Y DOBLADO', 'ACTIVO', '2025-11-14 17:08:37'),
(7, 2, 'REVISIÓN Y EMPAQUE', 'ACTIVO', '2025-11-14 17:09:01'),
(8, 2, 'HOT STAMPING', 'ACTIVO', '2025-11-14 17:09:17'),
(9, 6, 'COMPRAS', 'ACTIVO', '2025-11-14 17:14:07'),
(10, 2, 'INGENIERÍA', 'ACTIVO', '2025-11-14 17:22:21'),
(11, 2, 'PLANEACIÓN', 'ACTIVO', '2025-11-16 03:47:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ls_departamentos`
--

CREATE TABLE `ls_departamentos` (
  `dep_id` int(11) NOT NULL,
  `dep_nombre` varchar(100) NOT NULL,
  `dep_estatus` enum('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  `dep_fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `ls_departamentos`
--

INSERT INTO `ls_departamentos` (`dep_id`, `dep_nombre`, `dep_estatus`, `dep_fecha_creacion`) VALUES
(1, 'CAPITAL HUMANO', 'ACTIVO', '2025-11-14 16:33:46'),
(2, 'PRODUCCIÓN', 'ACTIVO', '2025-11-14 17:01:54'),
(3, 'CALIDAD', 'ACTIVO', '2025-11-14 17:09:30'),
(4, 'ALMACÉN', 'ACTIVO', '2025-11-14 17:09:37'),
(5, 'DIRECCIÓN', 'ACTIVO', '2025-11-14 17:09:54'),
(6, 'ADMINISTRACIÓN', 'ACTIVO', '2025-11-14 17:13:31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ls_puestos`
--

CREATE TABLE `ls_puestos` (
  `pue_id` int(11) NOT NULL,
  `area_id` int(11) NOT NULL,
  `pue_nombre` varchar(100) NOT NULL,
  `pue_estatus` enum('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  `pue_fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `ls_puestos`
--

INSERT INTO `ls_puestos` (`pue_id`, `area_id`, `pue_nombre`, `pue_estatus`, `pue_fecha_creacion`) VALUES
(1, 1, 'OPERADOR DE PEGADO Y EMPAQUE', 'ACTIVO', '2025-11-14 17:07:23'),
(2, 1, 'FEEDER DE PEGADO Y EMPAQUE', 'ACTIVO', '2025-11-14 17:14:47'),
(3, 1, 'AUXILIAR DE PEGADO Y EMPAQUE', 'ACTIVO', '2025-11-14 17:15:02'),
(4, 10, 'INGENIERO DE PROCESOS', 'ACTIVO', '2025-11-14 17:22:32'),
(5, 10, 'TÉCNICO DE PROCESOS', 'ACTIVO', '2025-11-14 17:22:41'),
(6, 11, 'COORDINADOR DE PRODUCCIÓN', 'ACTIVO', '2025-11-16 03:47:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ls_roles`
--

CREATE TABLE `ls_roles` (
  `rol_id` int(11) NOT NULL,
  `rol_nombre` varchar(50) NOT NULL,
  `rol_descripcion` varchar(255) DEFAULT NULL,
  `rol_estatus` enum('ACTIVO','INACTIVO') NOT NULL DEFAULT 'ACTIVO',
  `rol_fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `ls_roles`
--

INSERT INTO `ls_roles` (`rol_id`, `rol_nombre`, `rol_descripcion`, `rol_estatus`, `rol_fecha_creacion`) VALUES
(1, 'Empleado', 'Permisos básicos de usuario para la interacción diaria y consulta.', 'ACTIVO', '2025-11-16 01:25:04'),
(2, 'Administrador', 'Control total sobre el sistema y módulos de mantenimiento.', 'ACTIVO', '2025-11-16 01:25:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ls_saldo_vacaciones`
--

CREATE TABLE `ls_saldo_vacaciones` (
  `saldo_id` int(11) NOT NULL,
  `usu_id` int(11) NOT NULL COMMENT 'ID del empleado, FK a LS_USUARIOS',
  `fecha_corte` date NOT NULL COMMENT 'Fecha hasta la cual es válido este saldo',
  `usu_antiguedad_anos` int(3) DEFAULT NULL COMMENT 'Antigüedad al corte',
  `fecha_actualizacion` datetime DEFAULT NULL,
  `usu_dias_generados_lft` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Días generados por LFT al corte',
  `usu_dias_usados` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Días tomados y aprobados al corte',
  `usu_dias_disponibles` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Saldo final de días de vacaciones al corte'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `ls_saldo_vacaciones`
--

INSERT INTO `ls_saldo_vacaciones` (`saldo_id`, `usu_id`, `fecha_corte`, `usu_antiguedad_anos`, `fecha_actualizacion`, `usu_dias_generados_lft`, `usu_dias_usados`, `usu_dias_disponibles`) VALUES
(1, 1, '2025-11-17', 0, '2025-11-17 11:13:39', 0.00, 0.00, 0.00),
(2, 7, '2025-06-13', 1, '2025-11-17 11:13:39', 12.00, 0.00, 12.00),
(3, 8, '2025-06-16', 3, '2025-11-17 11:13:39', 16.00, 0.00, 16.00),
(4, 9, '2025-03-20', 0, '2025-11-17 11:13:39', 0.00, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ls_usuarios`
--

CREATE TABLE `ls_usuarios` (
  `usu_id` int(11) NOT NULL COMMENT 'Clave primaria del usuario',
  `usu_nombre` varchar(100) NOT NULL COMMENT 'Nombre(s) del usuario',
  `usu_apellido_paterno` varchar(100) NOT NULL COMMENT 'Apellido paterno del usuario',
  `usu_apellido_materno` varchar(100) NOT NULL COMMENT 'Apellido materno del usuario',
  `rol_id` int(11) NOT NULL COMMENT 'Rol del usuario (1=Operativo, 2=Administrativo)',
  `jefe_id` int(11) DEFAULT NULL COMMENT 'ID del jefe/supervisor directo. FK a LS_USUARIOS.usu_id',
  `usu_area` int(100) DEFAULT NULL COMMENT 'Área de trabajo del usuario',
  `usu_puesto` int(100) DEFAULT NULL COMMENT 'Puesto actual del usuario',
  `usu_departamento` int(100) DEFAULT NULL COMMENT 'Departamento al que pertenece',
  `usu_usuario_inicio` varchar(50) NOT NULL COMMENT 'Nombre de usuario para iniciar sesión (Login)',
  `usu_contraseña_inicio` varchar(255) NOT NULL COMMENT 'Contraseña hasheada para iniciar sesión (HASH)',
  `usu_telefono` varchar(20) DEFAULT NULL COMMENT 'Número de teléfono del usuario',
  `usu_RFC` varchar(13) DEFAULT NULL COMMENT 'Registro Federal de Contribuyentes',
  `usu_CURP` varchar(18) DEFAULT NULL COMMENT 'Clave Única de Registro de Población',
  `usu_NSS` varchar(15) DEFAULT NULL COMMENT 'Número de Seguridad Social',
  `usu_domicilio` varchar(255) DEFAULT NULL COMMENT 'Domicilio completo del usuario',
  `usu_edad` int(3) DEFAULT NULL COMMENT 'Edad del usuario',
  `usu_fecha_nacimiento` date DEFAULT NULL,
  `usu_foto` varchar(100) DEFAULT NULL COMMENT 'Foto del Usuario',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha de creación del registro',
  `fecha_modificacion` datetime DEFAULT NULL COMMENT 'Fecha de última modificación del registro',
  `fecha_ingreso_planta` date DEFAULT NULL COMMENT 'Fecha de ingreso a la planta/empresa',
  `fecha_puesto_inactivo` date DEFAULT NULL COMMENT 'Fecha en que el puesto se inactivó (si aplica)',
  `usu_estado` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Estado del usuario'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla de Usuarios de LITOSOFT';

--
-- Volcado de datos para la tabla `ls_usuarios`
--

INSERT INTO `ls_usuarios` (`usu_id`, `usu_nombre`, `usu_apellido_paterno`, `usu_apellido_materno`, `rol_id`, `jefe_id`, `usu_area`, `usu_puesto`, `usu_departamento`, `usu_usuario_inicio`, `usu_contraseña_inicio`, `usu_telefono`, `usu_RFC`, `usu_CURP`, `usu_NSS`, `usu_domicilio`, `usu_edad`, `usu_fecha_nacimiento`, `usu_foto`, `fecha_creacion`, `fecha_modificacion`, `fecha_ingreso_planta`, `fecha_puesto_inactivo`, `usu_estado`) VALUES
(1, 'SUPER', 'ADMINISTRADOR', 'LITOCALIDAD', 2, NULL, 0, 0, 0, 'admin', '$2y$10$dvcB77X7usp.YEQL0bvmQeC8oAUtkrM6R9X7rHuMQKnGZZpAREQU.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'admin.jpg', '2025-11-13 16:47:11', NULL, NULL, NULL, 1),
(7, 'ISMAEL', 'MARTINEZ', 'VELASCO', 2, 8, 10, 4, 2, 'IMARTINEZ', '$2y$10$yLaFW2VZ49ggOurkAE9bn..2prEIHkyHbNz5RYMJOd1pBmuZ4g22G', '2216565674', 'MAVI99100QL8', 'MAVI99100HVRLS09', '2481309622', 'TRUFAS 31 A', 26, '1999-10-09', 'ISMAEL_MARTINEZ_1763393376.jpeg', '2025-11-17 09:29:36', '2025-11-17 09:30:31', '2024-06-13', NULL, 1),
(8, 'ORLANDO', 'HERNANDEZ', 'LUNA', 2, 1, 11, 6, 2, 'OHERNANDEZ', '$2y$10$ovQNHdiE2zAUzBD47OZlyuG7uzTY8wv95TCcvgZPmbdc/iaOELapW', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 28, '1997-05-15', 'default.png', '2025-11-17 09:30:25', NULL, '2022-06-16', NULL, 1),
(9, 'IVONNE', 'GOMEZ', 'ZEMPOATECATL', 1, 7, 10, 5, 2, 'IGOMEZ', '$2y$10$NeJ/Y/WjrRH4o5vUq/5m5uaJzhi4HIKrnOmx/5rglWx6hTwKDLPpG', 'N/A', 'N/A1', 'N/A1', 'N/A1', 'N/A', 26, '1999-10-25', 'default.png', '2025-11-17 09:31:20', NULL, '2025-03-20', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ls_vacaciones_solicitudes`
--

CREATE TABLE `ls_vacaciones_solicitudes` (
  `vac_id` int(11) NOT NULL,
  `usu_id` int(11) NOT NULL COMMENT 'ID del empleado que solicita. FK a LS_USUARIOS',
  `vac_fecha_inicio` date NOT NULL,
  `vac_fecha_fin` date NOT NULL,
  `vac_dias_solicitados` int(11) NOT NULL COMMENT 'Número de días naturales solicitados (incluye fines de semana).',
  `vac_dias_habiles` int(11) NOT NULL COMMENT 'Días hábiles netos solicitados (excluye fines de semana y festivos).',
  `vac_estado` enum('Pendiente','Aprobada','Rechazada','Cancelada') NOT NULL DEFAULT 'Pendiente',
  `vac_motivo_rechazo` text DEFAULT NULL,
  `vac_fecha_solicitud` datetime NOT NULL DEFAULT current_timestamp(),
  `vac_jefe_id_aprobador` int(11) DEFAULT NULL COMMENT 'ID del jefe/admin que aprobó/rechazó. FK a LS_USUARIOS',
  `vac_fecha_aprobacion` datetime DEFAULT NULL,
  `vac_justificacion_adelanto` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `ls_areas`
--
ALTER TABLE `ls_areas`
  ADD PRIMARY KEY (`area_id`),
  ADD KEY `dep_id` (`dep_id`);

--
-- Indices de la tabla `ls_departamentos`
--
ALTER TABLE `ls_departamentos`
  ADD PRIMARY KEY (`dep_id`),
  ADD UNIQUE KEY `dep_nombre` (`dep_nombre`);

--
-- Indices de la tabla `ls_puestos`
--
ALTER TABLE `ls_puestos`
  ADD PRIMARY KEY (`pue_id`),
  ADD KEY `area_id` (`area_id`);

--
-- Indices de la tabla `ls_roles`
--
ALTER TABLE `ls_roles`
  ADD PRIMARY KEY (`rol_id`),
  ADD UNIQUE KEY `rol_nombre` (`rol_nombre`),
  ADD UNIQUE KEY `uk_rol_nombre` (`rol_nombre`);

--
-- Indices de la tabla `ls_saldo_vacaciones`
--
ALTER TABLE `ls_saldo_vacaciones`
  ADD PRIMARY KEY (`saldo_id`),
  ADD UNIQUE KEY `uk_saldo_usuario_fecha` (`usu_id`,`fecha_corte`);

--
-- Indices de la tabla `ls_usuarios`
--
ALTER TABLE `ls_usuarios`
  ADD PRIMARY KEY (`usu_id`),
  ADD UNIQUE KEY `usu_usuario_inicio` (`usu_usuario_inicio`),
  ADD UNIQUE KEY `usu_RFC` (`usu_RFC`),
  ADD UNIQUE KEY `usu_CURP` (`usu_CURP`),
  ADD UNIQUE KEY `usu_NSS` (`usu_NSS`),
  ADD KEY `idx_rol_id` (`rol_id`);

--
-- Indices de la tabla `ls_vacaciones_solicitudes`
--
ALTER TABLE `ls_vacaciones_solicitudes`
  ADD PRIMARY KEY (`vac_id`),
  ADD KEY `usu_id` (`usu_id`),
  ADD KEY `vac_jefe_id_aprobador` (`vac_jefe_id_aprobador`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `ls_areas`
--
ALTER TABLE `ls_areas`
  MODIFY `area_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `ls_departamentos`
--
ALTER TABLE `ls_departamentos`
  MODIFY `dep_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `ls_puestos`
--
ALTER TABLE `ls_puestos`
  MODIFY `pue_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `ls_roles`
--
ALTER TABLE `ls_roles`
  MODIFY `rol_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `ls_saldo_vacaciones`
--
ALTER TABLE `ls_saldo_vacaciones`
  MODIFY `saldo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `ls_usuarios`
--
ALTER TABLE `ls_usuarios`
  MODIFY `usu_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Clave primaria del usuario', AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `ls_vacaciones_solicitudes`
--
ALTER TABLE `ls_vacaciones_solicitudes`
  MODIFY `vac_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `ls_areas`
--
ALTER TABLE `ls_areas`
  ADD CONSTRAINT `ls_areas_ibfk_1` FOREIGN KEY (`dep_id`) REFERENCES `ls_departamentos` (`dep_id`);

--
-- Filtros para la tabla `ls_puestos`
--
ALTER TABLE `ls_puestos`
  ADD CONSTRAINT `ls_puestos_ibfk_1` FOREIGN KEY (`area_id`) REFERENCES `ls_areas` (`area_id`);

--
-- Filtros para la tabla `ls_saldo_vacaciones`
--
ALTER TABLE `ls_saldo_vacaciones`
  ADD CONSTRAINT `ls_saldo_vacaciones_ibfk_1` FOREIGN KEY (`usu_id`) REFERENCES `ls_usuarios` (`usu_id`);

--
-- Filtros para la tabla `ls_vacaciones_solicitudes`
--
ALTER TABLE `ls_vacaciones_solicitudes`
  ADD CONSTRAINT `ls_vacaciones_solicitudes_ibfk_1` FOREIGN KEY (`usu_id`) REFERENCES `ls_usuarios` (`usu_id`),
  ADD CONSTRAINT `ls_vacaciones_solicitudes_ibfk_2` FOREIGN KEY (`vac_jefe_id_aprobador`) REFERENCES `ls_usuarios` (`usu_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
