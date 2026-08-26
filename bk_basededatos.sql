-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.4.3 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para tpv_minimarket
CREATE DATABASE IF NOT EXISTS `tpv_minimarket` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `tpv_minimarket`;

-- Volcando estructura para tabla tpv_minimarket.actividad_log
CREATE TABLE IF NOT EXISTS `actividad_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `accion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modulo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `datos` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `actividad_log_user_id_foreign` (`user_id`),
  CONSTRAINT `actividad_log_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla tpv_minimarket.actividad_log: ~0 rows (aproximadamente)
DELETE FROM `actividad_log`;

-- Volcando estructura para tabla tpv_minimarket.backups
CREATE TABLE IF NOT EXISTS `backups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `archivo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tamano` bigint NOT NULL DEFAULT '0',
  `tipo` enum('manual','automatico') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `user_id` bigint unsigned DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `backups_user_id_foreign` (`user_id`),
  CONSTRAINT `backups_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla tpv_minimarket.backups: ~10 rows (aproximadamente)
DELETE FROM `backups`;
INSERT INTO `backups` (`id`, `nombre`, `archivo`, `tamano`, `tipo`, `user_id`, `observaciones`, `created_at`, `updated_at`) VALUES
	(1, 'backup_20260403_071400', 'backup_20260403_071400.sql', 3763450, 'manual', 5, 'Backup demo del 03/04/2026', '2026-04-03 12:14:00', '2026-04-03 12:14:00'),
	(2, 'backup_20260407_032400', 'backup_20260407_032400.sql', 6209999, 'automatico', 11, 'Backup demo del 07/04/2026', '2026-04-07 08:24:00', '2026-04-07 08:24:00'),
	(3, 'backup_20260411_192600', 'backup_20260411_192600.sql', 4535296, 'automatico', 9, 'Backup demo del 11/04/2026', '2026-04-12 00:26:00', '2026-04-12 00:26:00'),
	(4, 'backup_20260415_235900', 'backup_20260415_235900.sql', 6053181, 'manual', 12, 'Backup demo del 15/04/2026', '2026-04-16 04:59:00', '2026-04-16 04:59:00'),
	(5, 'backup_20260419_211100', 'backup_20260419_211100.sql', 965926, 'automatico', 9, 'Backup demo del 19/04/2026', '2026-04-20 02:11:00', '2026-04-20 02:11:00'),
	(6, 'backup_20260423_182300', 'backup_20260423_182300.sql', 6901103, 'automatico', 9, 'Backup demo del 23/04/2026', '2026-04-23 23:23:00', '2026-04-23 23:23:00'),
	(7, 'backup_20260427_124500', 'backup_20260427_124500.sql', 1904122, 'manual', 9, 'Backup demo del 27/04/2026', '2026-04-27 17:45:00', '2026-04-27 17:45:00'),
	(8, 'backup_20260501_130200', 'backup_20260501_130200.sql', 1851095, 'automatico', 1, 'Backup demo del 01/05/2026', '2026-05-01 18:02:00', '2026-05-01 18:02:00'),
	(9, 'backup_20260505_053500', 'backup_20260505_053500.sql', 2436142, 'automatico', 13, 'Backup demo del 05/05/2026', '2026-05-05 10:35:00', '2026-05-05 10:35:00'),
	(10, 'backup_20260509_163900', 'backup_20260509_163900.sql', 6867481, 'manual', 8, 'Backup demo del 09/05/2026', '2026-05-09 21:39:00', '2026-05-09 21:39:00');

-- Volcando estructura para tabla tpv_minimarket.cajas
CREATE TABLE IF NOT EXISTS `cajas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla tpv_minimarket.cajas: ~4 rows (aproximadamente)
DELETE FROM `cajas`;
INSERT INTO `cajas` (`id`, `nombre`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES
	(1, 'Caja Principal', 'Caja principal del minimarket', 1, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(2, 'Caja 2', 'Segunda caja', 1, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(3, 'Caja 3', 'Caja secundaria N° 3', 1, '2026-05-19 01:27:22', '2026-05-19 01:27:22'),
	(4, 'Caja 4', 'Caja secundaria N° 4', 1, '2026-05-19 01:27:22', '2026-05-19 01:27:22');

-- Volcando estructura para tabla tpv_minimarket.categorias
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#3B82F6',
  `icono` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cube',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla tpv_minimarket.categorias: ~22 rows (aproximadamente)
DELETE FROM `categorias`;
INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `color`, `icono`, `activo`, `created_at`, `updated_at`) VALUES
	(1, 'Abarrotes', 'Productos básicos secos', '#f59e0b', 'cube', 1, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(2, 'Bebidas', 'Bebidas alcohólicas y no alcohólicas', '#3b82f6', 'wine-bottle', 1, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(3, 'Lácteos', 'Leche, queso, yogurt', '#ec4899', 'cheese', 1, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(4, 'Panadería', 'Pan, pasteles', '#d97706', 'bread-slice', 1, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(5, 'Frutas y Verduras', 'Productos frescos', '#10b981', 'apple-alt', 1, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(6, 'Carnes', 'Carnes y embutidos', '#dc2626', 'drumstick-bite', 1, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(7, 'Snacks', 'Galletas, papas fritas', '#fbbf24', 'cookie', 1, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(8, 'Limpieza', 'Productos de limpieza', '#06b6d4', 'soap', 1, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(9, 'Cuidado Personal', 'Higiene personal', '#a855f7', 'tshirt', 1, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(10, 'Bebés', 'Productos para bebés', '#f472b6', 'baby', 1, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(11, 'Mascotas', 'Productos para mascotas', '#84cc16', 'paw', 1, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(12, 'Helados', 'Helados y postres congelados', '#06b6d4', 'ice-cream', 1, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(13, 'Embutidos', 'Jamones, salchichas y embutidos', '#b91c1c', 'bacon', 1, '2026-03-25 01:27:20', '2026-03-25 01:27:20'),
	(14, 'Congelados', 'Productos congelados', '#0ea5e9', 'snowflake', 1, '2026-03-30 01:27:20', '2026-03-30 01:27:20'),
	(15, 'Confitería', 'Caramelos y dulces', '#f97316', 'candy-cane', 1, '2026-04-04 01:27:20', '2026-04-04 01:27:20'),
	(16, 'Licores', 'Vinos y licores', '#7c2d12', 'glass-cheers', 1, '2026-04-09 01:27:20', '2026-04-09 01:27:20'),
	(17, 'Cereales', 'Cereales para desayuno', '#ca8a04', 'wheat-awn', 1, '2026-04-14 01:27:20', '2026-04-14 01:27:20'),
	(18, 'Conservas', 'Productos enlatados', '#475569', 'jar', 1, '2026-04-19 01:27:20', '2026-04-19 01:27:20'),
	(19, 'Útiles Escolares', 'Material escolar', '#2563eb', 'pencil-alt', 1, '2026-04-24 01:27:20', '2026-04-24 01:27:20'),
	(20, 'Ferretería Básica', 'Artículos de ferretería', '#71717a', 'hammer', 1, '2026-04-29 01:27:20', '2026-04-29 01:27:20'),
	(21, 'Cuidado del Hogar', 'Productos para el hogar', '#65a30d', 'home', 1, '2026-05-04 01:27:20', '2026-05-04 01:27:20'),
	(22, 'Salud', 'Productos farmacéuticos OTC', '#059669', 'first-aid', 1, '2026-05-09 01:27:20', '2026-05-09 01:27:20');

-- Volcando estructura para tabla tpv_minimarket.clientes
CREATE TABLE IF NOT EXISTS `clientes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_documento` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DNI',
  `documento` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombres` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `razon_social` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ciudad` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `genero` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `puntos_fidelidad` int NOT NULL DEFAULT '0',
  `credito_limite` decimal(12,2) NOT NULL DEFAULT '0.00',
  `credito_usado` decimal(12,2) NOT NULL DEFAULT '0.00',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clientes_codigo_unique` (`codigo`),
  KEY `clientes_documento_index` (`documento`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla tpv_minimarket.clientes: ~10 rows (aproximadamente)
DELETE FROM `clientes`;
INSERT INTO `clientes` (`id`, `codigo`, `tipo_documento`, `documento`, `nombres`, `apellidos`, `razon_social`, `telefono`, `email`, `direccion`, `ciudad`, `fecha_nacimiento`, `genero`, `puntos_fidelidad`, `credito_limite`, `credito_usado`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES
	(1, 'C000001', 'DNI', '72345671', 'María', 'Quispe Huamán', NULL, '987-001-001', 'maria.quispe@gmail.com', 'Calle 10 #100', 'Lima', '2006-04-16', 'F', 34, 500.00, 0.00, NULL, 1, '2026-03-30 01:27:20', '2026-03-30 01:27:20'),
	(2, 'C000002', 'DNI', '72345672', 'José', 'Ramírez Soto', NULL, '987-001-002', 'jramirez@hotmail.com', 'Calle 11 #105', 'Lima', '2004-07-19', 'M', 148, 0.00, 0.00, NULL, 1, '2026-04-03 01:27:20', '2026-04-03 01:27:20'),
	(3, 'C000003', 'DNI', '72345673', 'Ana', 'Vargas Torres', NULL, '987-001-003', 'ana.vargas@gmail.com', 'Calle 12 #110', 'Callao', '2003-08-14', 'F', 165, 0.00, 0.00, NULL, 1, '2026-04-07 01:27:20', '2026-04-07 01:27:20'),
	(4, 'C000004', 'DNI', '72345674', 'Luis', 'Mamani Apaza', NULL, '987-001-004', 'luismamani@yahoo.com', 'Calle 13 #115', 'Arequipa', '2002-07-17', 'M', 44, 500.00, 0.00, NULL, 1, '2026-04-11 01:27:20', '2026-04-11 01:27:20'),
	(5, 'C000005', 'DNI', '72345675', 'Carmen', 'Flores Salinas', NULL, '987-001-005', 'carmenfs@gmail.com', 'Calle 14 #120', 'Lima', '2002-03-30', 'F', 209, 0.00, 0.00, NULL, 1, '2026-04-15 01:27:20', '2026-04-15 01:27:20'),
	(6, 'C000006', 'DNI', '72345676', 'Roberto', 'Chávez Pinto', NULL, '987-001-006', 'rchavez@outlook.com', 'Calle 15 #125', 'Trujillo', '2000-10-16', 'M', 108, 0.00, 0.00, NULL, 1, '2026-04-19 01:27:20', '2026-04-19 01:27:20'),
	(7, 'C000007', 'RUC', '20100777888', 'Empresa', 'Comercial Sol', 'Empresa Comercial Sol', '01-222-3344', 'compras@solperu.com', 'Calle 16 #130', 'Lima', '2000-04-05', NULL, 140, 500.00, 0.00, NULL, 1, '2026-04-23 01:27:20', '2026-04-23 01:27:20'),
	(8, 'C000008', 'DNI', '72345678', 'Patricia', 'Mendoza Ruiz', NULL, '987-001-008', 'patyM@gmail.com', 'Calle 17 #135', 'Cusco', '1998-08-01', 'F', 39, 0.00, 0.00, NULL, 1, '2026-04-27 01:27:20', '2026-04-27 01:27:20'),
	(9, 'C000009', 'DNI', '72345679', 'Eduardo', 'Sánchez León', NULL, '987-001-009', 'eduslv@gmail.com', 'Calle 18 #140', 'Lima', '1997-08-20', 'M', 109, 0.00, 0.00, NULL, 1, '2026-05-01 01:27:20', '2026-05-01 01:27:20'),
	(10, 'C000010', 'DNI', '72345680', 'Sofía', 'Paredes Rivas', NULL, '987-001-010', 'sofiapr@gmail.com', 'Calle 19 #145', 'Lima', '1997-02-12', 'F', 68, 500.00, 0.00, NULL, 1, '2026-05-05 01:27:20', '2026-05-05 01:27:20');

-- Volcando estructura para tabla tpv_minimarket.compras
CREATE TABLE IF NOT EXISTS `compras` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero_factura` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_compra` datetime NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `proveedor_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `descuento` decimal(12,2) NOT NULL DEFAULT '0.00',
  `impuesto` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `forma_pago` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'efectivo',
  `estado` enum('recibida','pendiente','anulada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'recibida',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `compras_numero_unique` (`numero`),
  KEY `compras_proveedor_id_foreign` (`proveedor_id`),
  KEY `compras_user_id_foreign` (`user_id`),
  CONSTRAINT `compras_proveedor_id_foreign` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`),
  CONSTRAINT `compras_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla tpv_minimarket.compras: ~10 rows (aproximadamente)
DELETE FROM `compras`;
INSERT INTO `compras` (`id`, `numero`, `numero_factura`, `fecha_compra`, `fecha_vencimiento`, `proveedor_id`, `user_id`, `subtotal`, `descuento`, `impuesto`, `total`, `forma_pago`, `estado`, `observaciones`, `created_at`, `updated_at`) VALUES
	(1, 'C000001', 'F-2026-00001', '2026-03-29 12:51:00', NULL, 1, 2, 213.30, 0.00, 38.39, 251.69, 'transferencia', 'recibida', 'Compra demo registrada', '2026-03-29 17:51:00', '2026-03-29 17:51:00'),
	(2, 'C000002', 'F-2026-00002', '2026-04-03 11:54:00', NULL, 2, 3, 230.40, 0.00, 41.47, 271.87, 'transferencia', 'recibida', 'Compra demo registrada', '2026-04-03 16:54:00', '2026-04-03 16:54:00'),
	(3, 'C000003', 'F-2026-00003', '2026-04-08 17:34:00', NULL, 1, 6, 164.30, 0.00, 29.57, 193.87, 'efectivo', 'recibida', 'Compra demo registrada', '2026-04-08 22:34:00', '2026-04-08 22:34:00'),
	(4, 'C000004', 'F-2026-00004', '2026-04-13 12:42:00', NULL, 7, 11, 604.50, 0.00, 108.81, 713.31, 'efectivo', 'recibida', 'Compra demo registrada', '2026-04-13 17:42:00', '2026-04-13 17:42:00'),
	(5, 'C000005', 'F-2026-00005', '2026-04-18 11:18:00', NULL, 13, 10, 420.00, 0.00, 75.60, 495.60, 'transferencia', 'recibida', 'Compra demo registrada', '2026-04-18 16:18:00', '2026-04-18 16:18:00'),
	(6, 'C000006', 'F-2026-00006', '2026-04-23 09:12:00', NULL, 11, 12, 469.90, 0.00, 84.58, 554.48, 'credito', 'recibida', 'Compra demo registrada', '2026-04-23 14:12:00', '2026-04-23 14:12:00'),
	(7, 'C000007', 'F-2026-00007', '2026-04-28 09:29:00', NULL, 1, 4, 400.00, 0.00, 72.00, 472.00, 'transferencia', 'recibida', 'Compra demo registrada', '2026-04-28 14:29:00', '2026-04-28 14:29:00'),
	(8, 'C000008', 'F-2026-00008', '2026-05-03 11:52:00', NULL, 5, 7, 269.70, 0.00, 48.55, 318.25, 'efectivo', 'recibida', 'Compra demo registrada', '2026-05-03 16:52:00', '2026-05-03 16:52:00'),
	(9, 'C000009', 'F-2026-00009', '2026-05-08 11:28:00', NULL, 6, 9, 346.90, 0.00, 62.44, 409.34, 'credito', 'recibida', 'Compra demo registrada', '2026-05-08 16:28:00', '2026-05-08 16:28:00'),
	(10, 'C000010', 'F-2026-00010', '2026-05-13 15:12:00', NULL, 11, 6, 148.30, 0.00, 26.69, 174.99, 'efectivo', 'recibida', 'Compra demo registrada', '2026-05-13 20:12:00', '2026-05-13 20:12:00');

-- Volcando estructura para tabla tpv_minimarket.compra_detalles
CREATE TABLE IF NOT EXISTS `compra_detalles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `compra_id` bigint unsigned NOT NULL,
  `producto_id` bigint unsigned NOT NULL,
  `codigo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cantidad` decimal(12,3) NOT NULL,
  `precio_unitario` decimal(12,2) NOT NULL,
  `descuento` decimal(12,2) NOT NULL DEFAULT '0.00',
  `impuesto` decimal(12,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(12,2) NOT NULL,
  `total` decimal(12,2) NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `lote` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `compra_detalles_compra_id_foreign` (`compra_id`),
  KEY `compra_detalles_producto_id_foreign` (`producto_id`),
  CONSTRAINT `compra_detalles_compra_id_foreign` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`) ON DELETE CASCADE,
  CONSTRAINT `compra_detalles_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla tpv_minimarket.compra_detalles: ~39 rows (aproximadamente)
DELETE FROM `compra_detalles`;
INSERT INTO `compra_detalles` (`id`, `compra_id`, `producto_id`, `codigo`, `descripcion`, `cantidad`, `precio_unitario`, `descuento`, `impuesto`, `subtotal`, `total`, `fecha_vencimiento`, `lote`, `created_at`, `updated_at`) VALUES
	(1, 1, 36, 'P000036', 'Caramelos Surtidos x100', 18.000, 4.50, 0.00, 0.00, 81.00, 81.00, NULL, NULL, '2026-03-29 17:51:00', '2026-03-29 17:51:00'),
	(2, 1, 29, 'P000029', 'Lejía Clorox 1L', 16.000, 4.50, 0.00, 0.00, 72.00, 72.00, NULL, NULL, '2026-03-29 17:51:00', '2026-03-29 17:51:00'),
	(3, 1, 22, 'P000022', 'Cebolla', 9.000, 2.50, 0.00, 0.00, 22.50, 22.50, NULL, NULL, '2026-03-29 17:51:00', '2026-03-29 17:51:00'),
	(4, 1, 18, 'P000018', 'Tostadas Bimbo', 9.000, 4.20, 0.00, 0.00, 37.80, 37.80, NULL, NULL, '2026-03-29 17:51:00', '2026-03-29 17:51:00'),
	(5, 2, 7, 'P000007', 'Inca Kola 1.5L', 20.000, 4.50, 0.00, 0.00, 90.00, 90.00, NULL, NULL, '2026-04-03 16:54:00', '2026-04-03 16:54:00'),
	(6, 2, 19, 'P000019', 'Manzana Roja', 16.000, 4.50, 0.00, 0.00, 72.00, 72.00, NULL, NULL, '2026-04-03 16:54:00', '2026-04-03 16:54:00'),
	(7, 2, 11, 'P000011', 'Jugo Frugos Manzana 1L', 18.000, 3.80, 0.00, 0.00, 68.40, 68.40, NULL, NULL, '2026-04-03 16:54:00', '2026-04-03 16:54:00'),
	(8, 3, 33, 'P000033', 'Papel Higiénico Suave x4', 9.000, 7.50, 0.00, 0.00, 67.50, 67.50, NULL, NULL, '2026-04-08 22:34:00', '2026-04-08 22:34:00'),
	(9, 3, 42, 'P000042', 'Pastillas Paracetamol x10', 18.000, 2.80, 0.00, 0.00, 50.40, 50.40, NULL, NULL, '2026-04-08 22:34:00', '2026-04-08 22:34:00'),
	(10, 3, 15, 'P000015', 'Mantequilla Gloria 200g', 7.000, 6.20, 0.00, 0.00, 43.40, 43.40, NULL, NULL, '2026-04-08 22:34:00', '2026-04-08 22:34:00'),
	(11, 3, 16, 'P000016', 'Pan Francés', 15.000, 0.20, 0.00, 0.00, 3.00, 3.00, NULL, NULL, '2026-04-08 22:34:00', '2026-04-08 22:34:00'),
	(12, 4, 33, 'P000033', 'Papel Higiénico Suave x4', 20.000, 7.50, 0.00, 0.00, 150.00, 150.00, NULL, NULL, '2026-04-13 17:42:00', '2026-04-13 17:42:00'),
	(13, 4, 13, 'P000013', 'Yogurt Gloria Fresa 1kg', 12.000, 7.50, 0.00, 0.00, 90.00, 90.00, NULL, NULL, '2026-04-13 17:42:00', '2026-04-13 17:42:00'),
	(14, 4, 34, 'P000034', 'Jamón Inglés 200g', 14.000, 8.50, 0.00, 0.00, 119.00, 119.00, NULL, NULL, '2026-04-13 17:42:00', '2026-04-13 17:42:00'),
	(15, 4, 42, 'P000042', 'Pastillas Paracetamol x10', 10.000, 2.80, 0.00, 0.00, 28.00, 28.00, NULL, NULL, '2026-04-13 17:42:00', '2026-04-13 17:42:00'),
	(16, 4, 31, 'P000031', 'Shampoo H&S 200ml', 15.000, 14.50, 0.00, 0.00, 217.50, 217.50, NULL, NULL, '2026-04-13 17:42:00', '2026-04-13 17:42:00'),
	(17, 5, 20, 'P000020', 'Plátano de Seda', 19.000, 1.80, 0.00, 0.00, 34.20, 34.20, NULL, NULL, '2026-04-18 16:18:00', '2026-04-18 16:18:00'),
	(18, 5, 1, 'P000001', 'Arroz Costeño 5kg', 8.000, 22.50, 0.00, 0.00, 180.00, 180.00, NULL, NULL, '2026-04-18 16:18:00', '2026-04-18 16:18:00'),
	(19, 5, 42, 'P000042', 'Pastillas Paracetamol x10', 11.000, 2.80, 0.00, 0.00, 30.80, 30.80, NULL, NULL, '2026-04-18 16:18:00', '2026-04-18 16:18:00'),
	(20, 5, 28, 'P000028', 'Detergente Ariel 850g', 14.000, 12.50, 0.00, 0.00, 175.00, 175.00, NULL, NULL, '2026-04-18 16:18:00', '2026-04-18 16:18:00'),
	(21, 6, 37, 'P000037', 'Vino Tinto Tabernero 750ml', 18.000, 18.50, 0.00, 0.00, 333.00, 333.00, NULL, NULL, '2026-04-23 14:12:00', '2026-04-23 14:12:00'),
	(22, 6, 14, 'P000014', 'Queso Fresco 250g', 9.000, 6.50, 0.00, 0.00, 58.50, 58.50, NULL, NULL, '2026-04-23 14:12:00', '2026-04-23 14:12:00'),
	(23, 6, 32, 'P000032', 'Pasta Dental Colgate', 14.000, 5.20, 0.00, 0.00, 72.80, 72.80, NULL, NULL, '2026-04-23 14:12:00', '2026-04-23 14:12:00'),
	(24, 6, 17, 'P000017', 'Pan Integral', 14.000, 0.40, 0.00, 0.00, 5.60, 5.60, NULL, NULL, '2026-04-23 14:12:00', '2026-04-23 14:12:00'),
	(25, 7, 37, 'P000037', 'Vino Tinto Tabernero 750ml', 9.000, 18.50, 0.00, 0.00, 166.50, 166.50, NULL, NULL, '2026-04-28 14:29:00', '2026-04-28 14:29:00'),
	(26, 7, 33, 'P000033', 'Papel Higiénico Suave x4', 13.000, 7.50, 0.00, 0.00, 97.50, 97.50, NULL, NULL, '2026-04-28 14:29:00', '2026-04-28 14:29:00'),
	(27, 7, 43, 'P000043', 'Escoba Plástica', 16.000, 8.50, 0.00, 0.00, 136.00, 136.00, NULL, NULL, '2026-04-28 14:29:00', '2026-04-28 14:29:00'),
	(28, 8, 39, 'P000039', 'Atún Florida en Aceite', 14.000, 4.20, 0.00, 0.00, 58.80, 58.80, NULL, NULL, '2026-05-03 16:52:00', '2026-05-03 16:52:00'),
	(29, 8, 10, 'P000010', 'Cerveza Cristal 630ml', 14.000, 4.50, 0.00, 0.00, 63.00, 63.00, NULL, NULL, '2026-05-03 16:52:00', '2026-05-03 16:52:00'),
	(30, 8, 32, 'P000032', 'Pasta Dental Colgate', 12.000, 5.20, 0.00, 0.00, 62.40, 62.40, NULL, NULL, '2026-05-03 16:52:00', '2026-05-03 16:52:00'),
	(31, 8, 19, 'P000019', 'Manzana Roja', 19.000, 4.50, 0.00, 0.00, 85.50, 85.50, NULL, NULL, '2026-05-03 16:52:00', '2026-05-03 16:52:00'),
	(32, 9, 32, 'P000032', 'Pasta Dental Colgate', 16.000, 5.20, 0.00, 0.00, 83.20, 83.20, NULL, NULL, '2026-05-08 16:28:00', '2026-05-08 16:28:00'),
	(33, 9, 5, 'P000005', 'Fideos Don Vittorio 500g', 8.000, 3.20, 0.00, 0.00, 25.60, 25.60, NULL, NULL, '2026-05-08 16:28:00', '2026-05-08 16:28:00'),
	(34, 9, 34, 'P000034', 'Jamón Inglés 200g', 20.000, 8.50, 0.00, 0.00, 170.00, 170.00, NULL, NULL, '2026-05-08 16:28:00', '2026-05-08 16:28:00'),
	(35, 9, 26, 'P000026', 'Chocman Costa', 7.000, 0.80, 0.00, 0.00, 5.60, 5.60, NULL, NULL, '2026-05-08 16:28:00', '2026-05-08 16:28:00'),
	(36, 9, 28, 'P000028', 'Detergente Ariel 850g', 5.000, 12.50, 0.00, 0.00, 62.50, 62.50, NULL, NULL, '2026-05-08 16:28:00', '2026-05-08 16:28:00'),
	(37, 10, 6, 'P000006', 'Atún Real Lata 170g', 12.000, 4.50, 0.00, 0.00, 54.00, 54.00, NULL, NULL, '2026-05-13 20:12:00', '2026-05-13 20:12:00'),
	(38, 10, 30, 'P000030', 'Jabón Bolívar 250g', 19.000, 2.20, 0.00, 0.00, 41.80, 41.80, NULL, NULL, '2026-05-13 20:12:00', '2026-05-13 20:12:00'),
	(39, 10, 21, 'P000021', 'Tomate', 15.000, 3.50, 0.00, 0.00, 52.50, 52.50, NULL, NULL, '2026-05-13 20:12:00', '2026-05-13 20:12:00');

-- Volcando estructura para tabla tpv_minimarket.comprobantes_electronicos
CREATE TABLE IF NOT EXISTS `comprobantes_electronicos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `venta_id` bigint unsigned DEFAULT NULL,
  `tipo_documento` varchar(2) NOT NULL,
  `serie` varchar(4) NOT NULL,
  `numero` varchar(8) NOT NULL,
  `numero_completo` varchar(20) NOT NULL,
  `emisor_ruc` varchar(15) NOT NULL,
  `emisor_razon_social` varchar(255) NOT NULL,
  `receptor_tipo_doc` varchar(2) NOT NULL,
  `receptor_numero_doc` varchar(20) NOT NULL,
  `receptor_razon_social` varchar(255) NOT NULL,
  `receptor_direccion` varchar(255) DEFAULT NULL,
  `receptor_email` varchar(255) DEFAULT NULL,
  `fecha_emision` date NOT NULL,
  `hora_emision` time NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `moneda` varchar(3) DEFAULT 'PEN',
  `total_gravadas` decimal(12,2) DEFAULT '0.00',
  `total_exoneradas` decimal(12,2) DEFAULT '0.00',
  `total_inafectas` decimal(12,2) DEFAULT '0.00',
  `total_gratuitas` decimal(12,2) DEFAULT '0.00',
  `total_igv` decimal(12,2) DEFAULT '0.00',
  `total_isc` decimal(12,2) DEFAULT '0.00',
  `total_descuentos` decimal(12,2) DEFAULT '0.00',
  `importe_total` decimal(12,2) NOT NULL,
  `importe_letras` varchar(255) NOT NULL,
  `doc_referencia_tipo` varchar(2) DEFAULT NULL,
  `doc_referencia_serie_numero` varchar(20) DEFAULT NULL,
  `motivo_referencia` varchar(255) DEFAULT NULL,
  `codigo_motivo_nc` varchar(3) DEFAULT NULL,
  `estado_sunat` enum('pendiente','enviado','aceptado','rechazado','observado','anulado','baja','excepcion') DEFAULT 'pendiente',
  `codigo_respuesta_sunat` varchar(10) DEFAULT NULL,
  `mensaje_sunat` text,
  `hash` varchar(255) DEFAULT NULL,
  `xml_path` varchar(255) DEFAULT NULL,
  `cdr_path` varchar(255) DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `qr_data` varchar(500) DEFAULT NULL,
  `fecha_envio_sunat` timestamp NULL DEFAULT NULL,
  `intentos_envio` int DEFAULT '0',
  `user_id` bigint unsigned DEFAULT NULL,
  `observaciones` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_completo` (`numero_completo`),
  KEY `tipo_documento` (`tipo_documento`,`serie`,`numero`),
  KEY `estado_sunat` (`estado_sunat`),
  KEY `fecha_emision` (`fecha_emision`),
  KEY `venta_id` (`venta_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `comprobantes_electronicos_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `comprobantes_electronicos_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla tpv_minimarket.comprobantes_electronicos: ~0 rows (aproximadamente)
DELETE FROM `comprobantes_electronicos`;

-- Volcando estructura para tabla tpv_minimarket.comunicaciones_baja
CREATE TABLE IF NOT EXISTS `comunicaciones_baja` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `identificador` varchar(20) NOT NULL,
  `comprobante_id` bigint unsigned NOT NULL,
  `fecha_generacion` date NOT NULL,
  `motivo` varchar(255) NOT NULL,
  `estado_sunat` enum('pendiente','enviado','aceptado','rechazado') DEFAULT 'pendiente',
  `ticket_sunat` varchar(100) DEFAULT NULL,
  `codigo_respuesta` varchar(10) DEFAULT NULL,
  `mensaje_respuesta` text,
  `xml_path` varchar(255) DEFAULT NULL,
  `cdr_path` varchar(255) DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `identificador` (`identificador`),
  KEY `comprobante_id` (`comprobante_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `comunicaciones_baja_ibfk_1` FOREIGN KEY (`comprobante_id`) REFERENCES `comprobantes_electronicos` (`id`),
  CONSTRAINT `comunicaciones_baja_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla tpv_minimarket.comunicaciones_baja: ~0 rows (aproximadamente)
DELETE FROM `comunicaciones_baja`;

-- Volcando estructura para tabla tpv_minimarket.configuraciones
CREATE TABLE IF NOT EXISTS `configuraciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `clave` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` text COLLATE utf8mb4_unicode_ci,
  `tipo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `grupo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `configuraciones_clave_unique` (`clave`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla tpv_minimarket.configuraciones: ~9 rows (aproximadamente)
DELETE FROM `configuraciones`;
INSERT INTO `configuraciones` (`id`, `clave`, `valor`, `tipo`, `grupo`, `descripcion`, `created_at`, `updated_at`) VALUES
	(1, 'puntos_por_moneda', '0.1', 'string', 'fidelidad', 'Puntos por unidad de moneda', '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(2, 'dias_aviso_vencimiento', '30', 'integer', 'inventario', NULL, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(3, 'stock_minimo_default', '5', 'integer', 'inventario', NULL, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(4, 'serie_ticket', 'T001', 'string', 'facturacion', NULL, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(5, 'serie_boleta', 'B001', 'string', 'facturacion', NULL, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(6, 'serie_factura', 'F001', 'string', 'facturacion', NULL, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(7, 'ancho_ticket', '80', 'integer', 'ticket', NULL, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(8, 'imprimir_auto', '1', 'boolean', 'ticket', NULL, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(9, 'mostrar_logo_ticket', '1', 'boolean', 'ticket', NULL, '2026-05-19 01:12:41', '2026-05-19 01:12:41');

-- Volcando estructura para tabla tpv_minimarket.empresas
CREATE TABLE IF NOT EXISTS `empresas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `razon_social` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_comercial` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ruc_nit` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ciudad` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ubigeo` varchar(6) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `departamento` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provincia` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `distrito` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_pais` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PE',
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sitio_web` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `moneda` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'S/',
  `codigo_moneda` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PEN',
  `impuesto` decimal(5,2) NOT NULL DEFAULT '18.00',
  `impuesto_incluido` tinyint(1) NOT NULL DEFAULT '1',
  `mensaje_ticket` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `terminos_condiciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sunat_modo` enum('beta','produccion') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'beta',
  `sunat_usuario_sol` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sunat_clave_sol` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sunat_certificado_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sunat_certificado_password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facturacion_electronica_activa` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla tpv_minimarket.empresas: ~1 rows (aproximadamente)
DELETE FROM `empresas`;
INSERT INTO `empresas` (`id`, `razon_social`, `nombre_comercial`, `ruc_nit`, `direccion`, `ciudad`, `ubigeo`, `departamento`, `provincia`, `distrito`, `codigo_pais`, `telefono`, `email`, `sitio_web`, `logo`, `moneda`, `codigo_moneda`, `impuesto`, `impuesto_incluido`, `mensaje_ticket`, `terminos_condiciones`, `created_at`, `updated_at`, `sunat_modo`, `sunat_usuario_sol`, `sunat_clave_sol`, `sunat_certificado_path`, `sunat_certificado_password`, `facturacion_electronica_activa`) VALUES
	(1, 'TPV Minimarket Demo S.A.C.', 'Mi Minimarket', '20100100100', 'Av. Principal 123', 'Lima', NULL, NULL, NULL, NULL, 'PE', '01-555-1234', 'contacto@minimarket.com', 'www.minimarket.com', NULL, 'S/', 'PEN', 18.00, 1, '¡Gracias por su preferencia! Vuelva pronto.', 'No se aceptan devoluciones después de 24 horas.', '2026-05-19 01:12:41', '2026-05-19 01:12:41', 'beta', NULL, NULL, NULL, NULL, 0);

-- Volcando estructura para tabla tpv_minimarket.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla tpv_minimarket.migrations: ~0 rows (aproximadamente)
DELETE FROM `migrations`;
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '2024_01_01_000000_create_roles_table', 1),
	(2, '2024_01_01_000001_create_users_table', 1),
	(3, '2024_01_01_000002_create_empresas_table', 1),
	(4, '2024_01_01_000003_create_configuraciones_table', 1),
	(5, '2024_01_01_000004_create_categorias_table', 1),
	(6, '2024_01_01_000005_create_proveedores_table', 1),
	(7, '2024_01_01_000006_create_productos_table', 1),
	(8, '2024_01_01_000007_create_clientes_table', 1),
	(9, '2024_01_01_000008_create_cajas_table', 1),
	(10, '2024_01_01_000009_create_ventas_table', 1),
	(11, '2024_01_01_000010_create_compras_table', 1),
	(12, '2024_01_01_000011_create_movimientos_inventario_table', 1),
	(13, '2024_01_01_000012_create_promociones_table', 1),
	(14, '2024_01_01_000013_create_backups_table', 1),
	(15, '2024_01_01_000014_create_facturacion_electronica_tables', 2);

-- Volcando estructura para tabla tpv_minimarket.movimientos_caja
CREATE TABLE IF NOT EXISTS `movimientos_caja` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `turno_caja_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `tipo` enum('ingreso','egreso') COLLATE utf8mb4_unicode_ci NOT NULL,
  `concepto` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `movimientos_caja_turno_caja_id_foreign` (`turno_caja_id`),
  KEY `movimientos_caja_user_id_foreign` (`user_id`),
  CONSTRAINT `movimientos_caja_turno_caja_id_foreign` FOREIGN KEY (`turno_caja_id`) REFERENCES `turnos_caja` (`id`),
  CONSTRAINT `movimientos_caja_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla tpv_minimarket.movimientos_caja: ~10 rows (aproximadamente)
DELETE FROM `movimientos_caja`;
INSERT INTO `movimientos_caja` (`id`, `turno_caja_id`, `user_id`, `tipo`, `concepto`, `monto`, `observaciones`, `created_at`, `updated_at`) VALUES
	(1, 8, 6, 'ingreso', 'Fondo inicial adicional', 78.00, 'Registro demo #1', '2026-04-08 21:18:00', '2026-04-08 21:18:00'),
	(2, 2, 3, 'egreso', 'Compra de útiles oficina', 120.00, 'Registro demo #2', '2026-04-12 21:24:00', '2026-04-12 21:24:00'),
	(3, 9, 5, 'ingreso', 'Cobro extra contado', 159.00, 'Registro demo #3', '2026-04-16 19:43:00', '2026-04-16 19:43:00'),
	(4, 7, 10, 'egreso', 'Pago de delivery', 128.00, 'Registro demo #4', '2026-04-20 21:57:00', '2026-04-20 21:57:00'),
	(5, 7, 13, 'egreso', 'Compra de bolsas', 159.00, 'Registro demo #5', '2026-04-24 21:40:00', '2026-04-24 21:40:00'),
	(6, 6, 11, 'ingreso', 'Devolución de cliente', 105.00, 'Registro demo #6', '2026-04-28 16:31:00', '2026-04-28 16:31:00'),
	(7, 9, 10, 'egreso', 'Pago de servicio agua', 143.00, 'Registro demo #7', '2026-05-02 18:16:00', '2026-05-02 18:16:00'),
	(8, 6, 1, 'ingreso', 'Préstamo socio', 37.00, 'Registro demo #8', '2026-05-06 22:39:00', '2026-05-06 22:39:00'),
	(9, 9, 8, 'egreso', 'Combustible', 192.00, 'Registro demo #9', '2026-05-10 16:43:00', '2026-05-10 16:43:00'),
	(10, 10, 3, 'egreso', 'Pago publicidad volantes', 178.00, 'Registro demo #10', '2026-05-15 00:00:00', '2026-05-15 00:00:00');

-- Volcando estructura para tabla tpv_minimarket.movimientos_inventario
CREATE TABLE IF NOT EXISTS `movimientos_inventario` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `producto_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `tipo` enum('entrada','salida','ajuste','merma','transferencia') COLLATE utf8mb4_unicode_ci NOT NULL,
  `motivo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cantidad` decimal(12,3) NOT NULL,
  `stock_anterior` decimal(12,3) NOT NULL,
  `stock_nuevo` decimal(12,3) NOT NULL,
  `referencia_tipo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referencia_id` bigint unsigned DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `fecha` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `movimientos_inventario_producto_id_foreign` (`producto_id`),
  KEY `movimientos_inventario_user_id_foreign` (`user_id`),
  CONSTRAINT `movimientos_inventario_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  CONSTRAINT `movimientos_inventario_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla tpv_minimarket.movimientos_inventario: ~10 rows (aproximadamente)
DELETE FROM `movimientos_inventario`;
INSERT INTO `movimientos_inventario` (`id`, `producto_id`, `user_id`, `tipo`, `motivo`, `cantidad`, `stock_anterior`, `stock_nuevo`, `referencia_tipo`, `referencia_id`, `observaciones`, `fecha`, `created_at`, `updated_at`) VALUES
	(1, 30, 9, 'entrada', 'Recepción de compra', 3.000, 50.000, 53.000, NULL, NULL, 'Movimiento demo #1', '2026-04-08 09:35:00', '2026-04-08 14:35:00', '2026-04-08 14:35:00'),
	(2, 27, 1, 'salida', 'Venta directa', 12.000, 60.000, 48.000, NULL, NULL, 'Movimiento demo #2', '2026-04-12 12:22:00', '2026-04-12 17:22:00', '2026-04-12 17:22:00'),
	(3, 42, 8, 'ajuste', 'Conteo físico', 6.000, 8.000, 14.000, NULL, NULL, 'Movimiento demo #3', '2026-04-16 10:34:00', '2026-04-16 15:34:00', '2026-04-16 15:34:00'),
	(4, 29, 4, 'merma', 'Producto vencido', 6.000, 40.000, 34.000, NULL, NULL, 'Movimiento demo #4', '2026-04-20 11:44:00', '2026-04-20 16:44:00', '2026-04-20 16:44:00'),
	(5, 43, 8, 'entrada', 'Reposición de stock', 7.000, 20.000, 27.000, NULL, NULL, 'Movimiento demo #5', '2026-04-24 09:30:00', '2026-04-24 14:30:00', '2026-04-24 14:30:00'),
	(6, 39, 13, 'salida', 'Salida por venta', 15.000, 60.000, 45.000, NULL, NULL, 'Movimiento demo #6', '2026-04-28 12:28:00', '2026-04-28 17:28:00', '2026-04-28 17:28:00'),
	(7, 12, 8, 'ajuste', 'Ajuste manual', 3.000, 60.000, 63.000, NULL, NULL, 'Movimiento demo #7', '2026-05-02 08:10:00', '2026-05-02 13:10:00', '2026-05-02 13:10:00'),
	(8, 16, 2, 'entrada', 'Devolución a proveedor', 6.000, 200.000, 206.000, NULL, NULL, 'Movimiento demo #8', '2026-05-06 11:08:00', '2026-05-06 16:08:00', '2026-05-06 16:08:00'),
	(9, 40, 6, 'salida', 'Transferencia interna', 5.000, 80.000, 75.000, NULL, NULL, 'Movimiento demo #9', '2026-05-10 09:59:00', '2026-05-10 14:59:00', '2026-05-10 14:59:00'),
	(10, 31, 9, 'merma', 'Daño en producto', 7.000, 25.000, 18.000, NULL, NULL, 'Movimiento demo #10', '2026-05-14 18:09:00', '2026-05-14 23:09:00', '2026-05-14 23:09:00');

-- Volcando estructura para tabla tpv_minimarket.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla tpv_minimarket.password_reset_tokens: ~0 rows (aproximadamente)
DELETE FROM `password_reset_tokens`;

-- Volcando estructura para tabla tpv_minimarket.productos
CREATE TABLE IF NOT EXISTS `productos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_barras` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `categoria_id` bigint unsigned DEFAULT NULL,
  `proveedor_id` bigint unsigned DEFAULT NULL,
  `unidad_medida` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'UND',
  `precio_compra` decimal(12,2) NOT NULL DEFAULT '0.00',
  `precio_venta` decimal(12,2) NOT NULL DEFAULT '0.00',
  `precio_mayoreo` decimal(12,2) NOT NULL DEFAULT '0.00',
  `cantidad_mayoreo` int NOT NULL DEFAULT '0',
  `stock` decimal(12,3) NOT NULL DEFAULT '0.000',
  `stock_minimo` decimal(12,3) NOT NULL DEFAULT '0.000',
  `stock_maximo` decimal(12,3) NOT NULL DEFAULT '0.000',
  `controla_stock` tinyint(1) NOT NULL DEFAULT '1',
  `aplica_impuesto` tinyint(1) NOT NULL DEFAULT '1',
  `imagen` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `lote` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ubicacion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `destacado` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `productos_codigo_unique` (`codigo`),
  KEY `productos_categoria_id_foreign` (`categoria_id`),
  KEY `productos_proveedor_id_foreign` (`proveedor_id`),
  KEY `productos_codigo_barras_index` (`codigo_barras`),
  CONSTRAINT `productos_categoria_id_foreign` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL,
  CONSTRAINT `productos_proveedor_id_foreign` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla tpv_minimarket.productos: ~43 rows (aproximadamente)
DELETE FROM `productos`;
INSERT INTO `productos` (`id`, `codigo`, `codigo_barras`, `nombre`, `descripcion`, `categoria_id`, `proveedor_id`, `unidad_medida`, `precio_compra`, `precio_venta`, `precio_mayoreo`, `cantidad_mayoreo`, `stock`, `stock_minimo`, `stock_maximo`, `controla_stock`, `aplica_impuesto`, `imagen`, `fecha_vencimiento`, `lote`, `ubicacion`, `activo`, `destacado`, `created_at`, `updated_at`) VALUES
	(1, 'P000001', '7501000000001', 'Arroz Costeño 5kg', NULL, 1, 1, 'KG', 22.50, 28.90, 27.46, 12, 50.000, 10.000, 100.000, 1, 1, NULL, NULL, NULL, NULL, 1, 1, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(2, 'P000002', '7501000000002', 'Aceite Primor 1L', NULL, 1, 1, 'LT', 9.80, 12.50, 11.88, 12, 30.000, 5.000, 60.000, 1, 1, NULL, NULL, NULL, NULL, 1, 1, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(3, 'P000003', '7501000000003', 'Azúcar Rubia 1kg', NULL, 1, 1, 'KG', 4.20, 5.50, 5.23, 12, 80.000, 15.000, 160.000, 1, 1, NULL, NULL, NULL, NULL, 1, 1, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(4, 'P000004', '7501000000004', 'Sal Yodada 1kg', NULL, 1, 1, 'KG', 1.50, 2.20, 2.09, 12, 60.000, 10.000, 120.000, 1, 1, NULL, NULL, NULL, NULL, 1, 1, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(5, 'P000005', '7501000000005', 'Fideos Don Vittorio 500g', NULL, 1, 1, 'PAQ', 3.20, 4.50, 4.28, 12, 100.000, 20.000, 200.000, 1, 1, NULL, NULL, NULL, NULL, 1, 1, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(6, 'P000006', '7501000000006', 'Atún Real Lata 170g', NULL, 1, 1, 'UND', 4.50, 6.20, 5.89, 12, 80.000, 15.000, 160.000, 1, 1, NULL, NULL, NULL, NULL, 1, 1, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(7, 'P000007', '7501000000007', 'Inca Kola 1.5L', NULL, 2, 1, 'UND', 4.50, 6.50, 6.18, 12, 80.000, 12.000, 160.000, 1, 1, NULL, NULL, NULL, NULL, 1, 1, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(8, 'P000008', '7501000000008', 'Coca Cola 1.5L', NULL, 2, 1, 'UND', 4.80, 6.80, 6.46, 12, 70.000, 10.000, 140.000, 1, 1, NULL, NULL, NULL, NULL, 1, 1, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(9, 'P000009', '7501000000009', 'Agua Cielo 625ml', NULL, 2, 1, 'UND', 1.20, 2.00, 1.90, 12, 120.000, 20.000, 240.000, 1, 1, NULL, NULL, NULL, NULL, 1, 1, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(10, 'P000010', '7501000000010', 'Cerveza Cristal 630ml', NULL, 2, 1, 'UND', 4.50, 6.50, 6.18, 12, 60.000, 12.000, 120.000, 1, 1, NULL, NULL, NULL, NULL, 1, 1, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(11, 'P000011', '7501000000011', 'Jugo Frugos Manzana 1L', NULL, 2, 1, 'UND', 3.80, 5.20, 4.94, 12, 40.000, 8.000, 80.000, 1, 1, NULL, NULL, NULL, NULL, 1, 1, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(12, 'P000012', '7501000000012', 'Leche Gloria Entera 1L', NULL, 3, 1, 'UND', 3.80, 5.00, 4.75, 12, 60.000, 10.000, 120.000, 1, 1, NULL, NULL, NULL, NULL, 1, 1, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(13, 'P000013', '7501000000013', 'Yogurt Gloria Fresa 1kg', NULL, 3, 1, 'UND', 7.50, 9.90, 9.41, 12, 25.000, 5.000, 50.000, 1, 1, NULL, NULL, NULL, NULL, 1, 0, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(14, 'P000014', '7501000000014', 'Queso Fresco 250g', NULL, 3, 1, 'UND', 6.50, 9.00, 8.55, 12, 20.000, 4.000, 40.000, 1, 1, NULL, NULL, NULL, NULL, 1, 0, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(15, 'P000015', '7501000000015', 'Mantequilla Gloria 200g', NULL, 3, 1, 'UND', 6.20, 8.50, 8.08, 12, 15.000, 3.000, 30.000, 1, 1, NULL, NULL, NULL, NULL, 1, 0, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(16, 'P000016', '7501000000016', 'Pan Francés', NULL, 4, 1, 'UND', 0.20, 0.40, 0.38, 12, 200.000, 30.000, 400.000, 1, 1, NULL, NULL, NULL, NULL, 1, 0, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(17, 'P000017', '7501000000017', 'Pan Integral', NULL, 4, 1, 'UND', 0.40, 0.70, 0.67, 12, 100.000, 20.000, 200.000, 1, 1, NULL, NULL, NULL, NULL, 1, 0, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(18, 'P000018', '7501000000018', 'Tostadas Bimbo', NULL, 4, 1, 'PAQ', 4.20, 5.80, 5.51, 12, 30.000, 8.000, 60.000, 1, 1, NULL, NULL, NULL, NULL, 1, 0, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(19, 'P000019', '7501000000019', 'Manzana Roja', NULL, 5, 1, 'KG', 4.50, 6.90, 6.56, 12, 30.000, 5.000, 60.000, 1, 1, NULL, NULL, NULL, NULL, 1, 0, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(20, 'P000020', '7501000000020', 'Plátano de Seda', NULL, 5, 1, 'KG', 1.80, 2.80, 2.66, 12, 40.000, 8.000, 80.000, 1, 1, NULL, NULL, NULL, NULL, 1, 0, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(21, 'P000021', '7501000000021', 'Tomate', NULL, 5, 1, 'KG', 3.50, 5.00, 4.75, 12, 25.000, 5.000, 50.000, 1, 1, NULL, NULL, NULL, NULL, 1, 0, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(22, 'P000022', '7501000000022', 'Cebolla', NULL, 5, 1, 'KG', 2.50, 3.80, 3.61, 12, 30.000, 6.000, 60.000, 1, 1, NULL, NULL, NULL, NULL, 1, 0, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(23, 'P000023', '7501000000023', 'Limón', NULL, 5, 1, 'KG', 4.20, 6.00, 5.70, 12, 20.000, 4.000, 40.000, 1, 1, NULL, NULL, NULL, NULL, 1, 0, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(24, 'P000024', '7501000000024', 'Lays Original 105g', NULL, 7, 1, 'UND', 4.50, 6.50, 6.18, 12, 50.000, 10.000, 100.000, 1, 1, NULL, NULL, NULL, NULL, 1, 0, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(25, 'P000025', '7501000000025', 'Doritos Nacho 110g', NULL, 7, 1, 'UND', 4.80, 6.80, 6.46, 12, 45.000, 8.000, 90.000, 1, 1, NULL, NULL, NULL, NULL, 1, 0, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(26, 'P000026', '7501000000026', 'Chocman Costa', NULL, 7, 1, 'UND', 0.80, 1.20, 1.14, 12, 100.000, 20.000, 200.000, 1, 1, NULL, NULL, NULL, NULL, 1, 0, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(27, 'P000027', '7501000000027', 'Galletas Oreo', NULL, 7, 1, 'UND', 2.50, 3.80, 3.61, 12, 60.000, 12.000, 120.000, 1, 1, NULL, NULL, NULL, NULL, 1, 0, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(28, 'P000028', '7501000000028', 'Detergente Ariel 850g', NULL, 8, 1, 'UND', 12.50, 16.50, 15.68, 12, 30.000, 5.000, 60.000, 1, 1, NULL, NULL, NULL, NULL, 1, 0, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(29, 'P000029', '7501000000029', 'Lejía Clorox 1L', NULL, 8, 1, 'LT', 4.50, 6.50, 6.18, 12, 40.000, 8.000, 80.000, 1, 1, NULL, NULL, NULL, NULL, 1, 0, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(30, 'P000030', '7501000000030', 'Jabón Bolívar 250g', NULL, 8, 1, 'UND', 2.20, 3.50, 3.33, 12, 50.000, 10.000, 100.000, 1, 1, NULL, NULL, NULL, NULL, 1, 0, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(31, 'P000031', '7501000000031', 'Shampoo H&S 200ml', NULL, 9, 1, 'UND', 14.50, 18.90, 17.96, 12, 25.000, 5.000, 50.000, 1, 1, NULL, NULL, NULL, NULL, 1, 0, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(32, 'P000032', '7501000000032', 'Pasta Dental Colgate', NULL, 9, 1, 'UND', 5.20, 7.50, 7.13, 12, 40.000, 8.000, 80.000, 1, 1, NULL, NULL, NULL, NULL, 1, 0, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(33, 'P000033', '7501000000033', 'Papel Higiénico Suave x4', NULL, 9, 1, 'PAQ', 7.50, 10.50, 9.98, 12, 50.000, 10.000, 100.000, 1, 1, NULL, NULL, NULL, NULL, 1, 0, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(34, 'P000034', '7501000000034', 'Jamón Inglés 200g', NULL, 2, 4, 'UND', 8.50, 12.50, 11.50, 12, 3.000, 5.000, 50.000, 1, 1, NULL, '2026-06-02', NULL, NULL, 1, 1, '2026-04-04 01:27:20', '2026-04-04 01:27:20'),
	(35, 'P000035', '7501000000035', 'Pollo Congelado 1kg', NULL, 22, 7, 'KG', 9.20, 13.90, 12.79, 12, 18.000, 4.000, 36.000, 1, 1, NULL, NULL, NULL, NULL, 1, 1, '2026-04-08 01:27:20', '2026-04-08 01:27:20'),
	(36, 'P000036', '7501000000036', 'Caramelos Surtidos x100', NULL, 15, 13, 'PAQ', 4.50, 7.50, 6.90, 12, 40.000, 8.000, 80.000, 1, 1, NULL, NULL, NULL, NULL, 1, 1, '2026-04-12 01:27:20', '2026-04-12 01:27:20'),
	(37, 'P000037', '7501000000037', 'Vino Tinto Tabernero 750ml', NULL, 9, 7, 'UND', 18.50, 26.90, 24.75, 12, 12.000, 3.000, 24.000, 1, 1, NULL, '2026-06-05', NULL, NULL, 1, 0, '2026-04-16 01:27:20', '2026-04-16 01:27:20'),
	(38, 'P000038', '7501000000038', 'Cereal Ángel Choco 500g', NULL, 8, 1, 'UND', 8.90, 12.50, 11.50, 12, 3.000, 5.000, 44.000, 1, 1, NULL, NULL, NULL, NULL, 1, 0, '2026-04-20 01:27:20', '2026-04-20 01:27:20'),
	(39, 'P000039', '7501000000039', 'Atún Florida en Aceite', NULL, 5, 3, 'UND', 4.20, 5.90, 5.43, 12, 60.000, 12.000, 120.000, 1, 1, NULL, NULL, NULL, NULL, 1, 0, '2026-04-24 01:27:20', '2026-04-24 01:27:20'),
	(40, 'P000040', '7501000000040', 'Cuaderno Cuadriculado A4', NULL, 17, 5, 'UND', 3.50, 5.50, 5.06, 12, 80.000, 15.000, 160.000, 1, 1, NULL, '2026-06-08', NULL, NULL, 1, 0, '2026-04-28 01:27:20', '2026-04-28 01:27:20'),
	(41, 'P000041', '7501000000041', 'Foco LED 9W', NULL, 19, 10, 'UND', 4.50, 7.90, 7.27, 12, 35.000, 8.000, 70.000, 1, 1, NULL, NULL, NULL, NULL, 1, 0, '2026-05-02 01:27:20', '2026-05-02 01:27:20'),
	(42, 'P000042', '7501000000042', 'Pastillas Paracetamol x10', NULL, 13, 3, 'CAJA', 2.80, 4.50, 4.14, 12, 8.000, 10.000, 100.000, 1, 1, NULL, NULL, NULL, NULL, 1, 0, '2026-05-06 01:27:20', '2026-05-06 01:27:20'),
	(43, 'P000043', '7501000000043', 'Escoba Plástica', NULL, 8, 8, 'UND', 8.50, 13.50, 12.42, 12, 20.000, 4.000, 40.000, 1, 1, NULL, '2026-06-11', NULL, NULL, 1, 0, '2026-05-10 01:27:20', '2026-05-10 01:27:20');

-- Volcando estructura para tabla tpv_minimarket.promociones
CREATE TABLE IF NOT EXISTS `promociones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `tipo` enum('descuento_porcentaje','descuento_fijo','2x1','3x2','precio_especial') COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` decimal(12,2) NOT NULL DEFAULT '0.00',
  `producto_id` bigint unsigned DEFAULT NULL,
  `categoria_id` bigint unsigned DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `cantidad_minima` int NOT NULL DEFAULT '1',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `promociones_producto_id_foreign` (`producto_id`),
  KEY `promociones_categoria_id_foreign` (`categoria_id`),
  CONSTRAINT `promociones_categoria_id_foreign` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL,
  CONSTRAINT `promociones_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla tpv_minimarket.promociones: ~10 rows (aproximadamente)
DELETE FROM `promociones`;
INSERT INTO `promociones` (`id`, `nombre`, `descripcion`, `tipo`, `valor`, `producto_id`, `categoria_id`, `fecha_inicio`, `fecha_fin`, `cantidad_minima`, `activo`, `created_at`, `updated_at`) VALUES
	(1, 'Descuento 10% Abarrotes', 'Promoción demo: Descuento 10% Abarrotes', 'descuento_porcentaje', 10.00, NULL, 8, '2026-04-08', '2026-05-09', 1, 1, '2026-04-09 01:27:22', '2026-04-09 01:27:22'),
	(2, 'Combo Bebidas 2x1', 'Promoción demo: Combo Bebidas 2x1', '2x1', 0.00, NULL, 12, '2026-04-12', '2026-05-03', 1, 1, '2026-04-13 01:27:22', '2026-04-13 01:27:22'),
	(3, 'Oferta Lácteos -15%', 'Promoción demo: Oferta Lácteos -15%', 'descuento_porcentaje', 15.00, NULL, 9, '2026-04-16', '2026-05-04', 1, 1, '2026-04-17 01:27:22', '2026-04-17 01:27:22'),
	(4, 'Liquidación Snacks S/2', 'Promoción demo: Liquidación Snacks S/2', 'descuento_fijo', 2.00, 34, NULL, '2026-04-20', '2026-05-05', 1, 1, '2026-04-21 01:27:22', '2026-04-21 01:27:22'),
	(5, 'Promo Limpieza 3x2', 'Promoción demo: Promo Limpieza 3x2', '3x2', 0.00, NULL, 16, '2026-04-24', '2026-05-25', 1, 1, '2026-04-25 01:27:22', '2026-04-25 01:27:22'),
	(6, 'Cuidado Personal -20%', 'Promoción demo: Cuidado Personal -20%', 'descuento_porcentaje', 20.00, NULL, 21, '2026-04-28', '2026-05-28', 1, 1, '2026-04-29 01:27:22', '2026-04-29 01:27:22'),
	(7, 'Precio Especial Cerveza', 'Promoción demo: Precio Especial Cerveza', 'precio_especial', 5.50, 13, NULL, '2026-05-02', '2026-05-19', 1, 1, '2026-05-03 01:27:22', '2026-05-03 01:27:22'),
	(8, 'Frutas y Verduras -10%', 'Promoción demo: Frutas y Verduras -10%', 'descuento_porcentaje', 10.00, NULL, 1, '2026-05-06', '2026-05-29', 1, 1, '2026-05-07 01:27:22', '2026-05-07 01:27:22'),
	(9, 'Pan a S/0.30', 'Promoción demo: Pan a S/0.30', 'precio_especial', 0.30, 18, NULL, '2026-05-10', '2026-05-29', 1, 1, '2026-05-11 01:27:22', '2026-05-11 01:27:22'),
	(10, 'Mega Combo Mes Aniversario', 'Promoción demo: Mega Combo Mes Aniversario', 'descuento_porcentaje', 25.00, NULL, 22, '2026-05-14', '2026-06-09', 1, 1, '2026-05-15 01:27:22', '2026-05-15 01:27:22');

-- Volcando estructura para tabla tpv_minimarket.proveedores
CREATE TABLE IF NOT EXISTS `proveedores` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `razon_social` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_comercial` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ruc_nit` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contacto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ciudad` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `proveedores_codigo_unique` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla tpv_minimarket.proveedores: ~14 rows (aproximadamente)
DELETE FROM `proveedores`;
INSERT INTO `proveedores` (`id`, `codigo`, `razon_social`, `nombre_comercial`, `ruc_nit`, `contacto`, `telefono`, `email`, `direccion`, `ciudad`, `observaciones`, `activo`, `created_at`, `updated_at`) VALUES
	(1, 'PR00001', 'Distribuidora Alimentos S.A.', NULL, '20100200300', 'Juan Pérez', '987-654-321', 'ventas@dasa.com', NULL, 'Lima', NULL, 1, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(2, 'PR00002', 'Bebidas y Más SAC', NULL, '20200300400', 'María Gómez', '976-543-210', 'pedidos@bebidasymas.com', NULL, 'Lima', NULL, 1, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(3, 'PR00003', 'Lácteos del Norte', NULL, '20300400500', 'Carlos Ruiz', '965-432-109', 'lacteos@delnorte.com', NULL, 'Trujillo', NULL, 1, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(4, 'PR00004', 'Panificadora La Espiga', NULL, '20400500600', 'Ana Torres', '954-321-098', 'pedidos@laespiga.com', NULL, 'Lima', NULL, 1, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(5, 'PR00005', 'Frigoríficos del Sur SAC', NULL, '20500600700', 'Pedro Salinas', '987-111-222', 'ventas@frigosur.com', 'Av. Comercial 100, Arequipa', 'Arequipa', NULL, 1, '2026-03-30 01:27:20', '2026-03-30 01:27:20'),
	(6, 'PR00006', 'Congelados Andinos EIRL', NULL, '20500600701', 'Lucía Mendoza', '987-111-223', 'pedidos@congandinos.pe', 'Av. Comercial 101, Cusco', 'Cusco', NULL, 1, '2026-04-03 01:27:20', '2026-04-03 01:27:20'),
	(7, 'PR00007', 'Dulcería Nacional SA', NULL, '20500600702', 'Roberto Vega', '987-111-224', 'rvega@dulcerianac.com', 'Av. Comercial 102, Lima', 'Lima', NULL, 1, '2026-04-07 01:27:20', '2026-04-07 01:27:20'),
	(8, 'PR00008', 'Importadora de Licores Perú', NULL, '20500600703', 'Sandra Quispe', '987-111-225', 'sq@licoresperu.com', 'Av. Comercial 103, Lima', 'Lima', NULL, 1, '2026-04-11 01:27:20', '2026-04-11 01:27:20'),
	(9, 'PR00009', 'Cereales y Más SAC', NULL, '20500600704', 'Diego Apaza', '987-111-226', 'diego@cerealesymas.com', 'Av. Comercial 104, Junín', 'Junín', NULL, 1, '2026-04-15 01:27:20', '2026-04-15 01:27:20'),
	(10, 'PR00010', 'Distribuidora Limpia Hogar', NULL, '20500600705', 'Patricia Lazo', '987-111-227', 'plazo@limpiahogar.pe', 'Av. Comercial 105, Lima', 'Lima', NULL, 1, '2026-04-19 01:27:20', '2026-04-19 01:27:20'),
	(11, 'PR00011', 'Útiles del Norte SA', NULL, '20500600706', 'Jorge Castañeda', '987-111-228', 'jcaste@utilesnorte.com', 'Av. Comercial 106, Trujillo', 'Trujillo', NULL, 1, '2026-04-23 01:27:20', '2026-04-23 01:27:20'),
	(12, 'PR00012', 'Ferretería Mayorista Lima', NULL, '20500600707', 'Ricardo Fonseca', '987-111-229', 'rfonseca@fmlmlima.pe', 'Av. Comercial 107, Lima', 'Lima', NULL, 1, '2026-04-27 01:27:20', '2026-04-27 01:27:20'),
	(13, 'PR00013', 'Farmadrogas Comercial SAC', NULL, '20500600708', 'Elena Rodríguez', '987-111-230', 'elena@farmadrogas.com', 'Av. Comercial 108, Lima', 'Lima', NULL, 1, '2026-05-01 01:27:20', '2026-05-01 01:27:20'),
	(14, 'PR00014', 'Distribuidora Selva Verde', NULL, '20500600709', 'Manuel Ipanaqué', '987-111-231', 'manuel@selvaverde.pe', 'Av. Comercial 109, Iquitos', 'Iquitos', NULL, 1, '2026-05-05 01:27:20', '2026-05-05 01:27:20');

-- Volcando estructura para tabla tpv_minimarket.puntos_fidelidad
CREATE TABLE IF NOT EXISTS `puntos_fidelidad` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cliente_id` bigint unsigned NOT NULL,
  `venta_id` bigint unsigned DEFAULT NULL,
  `tipo` enum('ganado','canjeado','expirado','ajuste') COLLATE utf8mb4_unicode_ci NOT NULL,
  `puntos` int NOT NULL,
  `saldo_anterior` int NOT NULL,
  `saldo_nuevo` int NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `puntos_fidelidad_cliente_id_foreign` (`cliente_id`),
  KEY `puntos_fidelidad_venta_id_foreign` (`venta_id`),
  CONSTRAINT `puntos_fidelidad_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  CONSTRAINT `puntos_fidelidad_venta_id_foreign` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla tpv_minimarket.puntos_fidelidad: ~10 rows (aproximadamente)
DELETE FROM `puntos_fidelidad`;
INSERT INTO `puntos_fidelidad` (`id`, `cliente_id`, `venta_id`, `tipo`, `puntos`, `saldo_anterior`, `saldo_nuevo`, `descripcion`, `created_at`, `updated_at`) VALUES
	(1, 5, 1, 'ganado', 43, 209, 252, 'Puntos por compra', '2026-04-14 00:11:00', '2026-04-14 00:11:00'),
	(2, 2, 3, 'ganado', 25, 148, 173, 'Puntos por compra', '2026-04-16 23:02:00', '2026-04-16 23:02:00'),
	(3, 8, 2, 'ganado', 8, 39, 47, 'Puntos por compra', '2026-04-19 23:45:00', '2026-04-19 23:45:00'),
	(4, 3, 2, 'canjeado', 19, 165, 146, 'Canje de puntos', '2026-04-22 20:38:00', '2026-04-22 20:38:00'),
	(5, 9, 4, 'ganado', 20, 109, 129, 'Puntos por compra', '2026-04-25 23:06:00', '2026-04-25 23:06:00'),
	(6, 2, 7, 'canjeado', 45, 148, 103, 'Canje de puntos', '2026-04-28 19:36:00', '2026-04-28 19:36:00'),
	(7, 9, 10, 'ganado', 6, 109, 115, 'Puntos por compra', '2026-05-01 15:15:00', '2026-05-01 15:15:00'),
	(8, 8, 1, 'ajuste', 37, 39, 2, 'Ajuste manual', '2026-05-04 16:51:00', '2026-05-04 16:51:00'),
	(9, 3, 7, 'ganado', 35, 165, 200, 'Puntos por compra', '2026-05-07 21:34:00', '2026-05-07 21:34:00'),
	(10, 6, 9, 'ganado', 14, 108, 122, 'Puntos por compra', '2026-05-10 15:22:00', '2026-05-10 15:22:00');

-- Volcando estructura para tabla tpv_minimarket.resumenes_diarios
CREATE TABLE IF NOT EXISTS `resumenes_diarios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `identificador` varchar(20) NOT NULL,
  `fecha_resumen` date NOT NULL,
  `fecha_generacion` date NOT NULL,
  `cantidad_comprobantes` int DEFAULT '0',
  `total_general` decimal(12,2) DEFAULT '0.00',
  `estado_sunat` enum('pendiente','enviado','aceptado','rechazado') DEFAULT 'pendiente',
  `ticket_sunat` varchar(100) DEFAULT NULL,
  `codigo_respuesta` varchar(10) DEFAULT NULL,
  `mensaje_respuesta` text,
  `xml_path` varchar(255) DEFAULT NULL,
  `cdr_path` varchar(255) DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `identificador` (`identificador`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `resumenes_diarios_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla tpv_minimarket.resumenes_diarios: ~0 rows (aproximadamente)
DELETE FROM `resumenes_diarios`;

-- Volcando estructura para tabla tpv_minimarket.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permisos` json DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_nombre_unique` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla tpv_minimarket.roles: ~4 rows (aproximadamente)
DELETE FROM `roles`;
INSERT INTO `roles` (`id`, `nombre`, `descripcion`, `permisos`, `activo`, `created_at`, `updated_at`) VALUES
	(1, 'Administrador', 'Acceso completo al sistema', '["*"]', 1, '2026-05-19 01:12:40', '2026-05-19 01:12:40'),
	(2, 'Gerente', 'Gestión completa excepto configuración del sistema', '["productos", "ventas", "compras", "clientes", "proveedores", "caja", "reportes", "promociones"]', 1, '2026-05-19 01:12:40', '2026-05-19 01:12:40'),
	(3, 'Cajero', 'Acceso al punto de venta y caja', '["ventas", "caja", "clientes"]', 1, '2026-05-19 01:12:40', '2026-05-19 01:12:40'),
	(4, 'Almacenero', 'Gestión de inventario y compras', '["productos", "compras", "proveedores", "reportes"]', 1, '2026-05-19 01:12:40', '2026-05-19 01:12:40');

-- Volcando estructura para tabla tpv_minimarket.series_documentos
CREATE TABLE IF NOT EXISTS `series_documentos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tipo_documento` varchar(2) NOT NULL,
  `serie` varchar(4) NOT NULL,
  `correlativo_actual` int DEFAULT '0',
  `correlativo_max` int DEFAULT '99999999',
  `activo` tinyint(1) DEFAULT '1',
  `descripcion` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_serie` (`tipo_documento`,`serie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcando datos para la tabla tpv_minimarket.series_documentos: ~0 rows (aproximadamente)
DELETE FROM `series_documentos`;

-- Volcando estructura para tabla tpv_minimarket.sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla tpv_minimarket.sessions: ~0 rows (aproximadamente)
DELETE FROM `sessions`;

-- Volcando estructura para tabla tpv_minimarket.turnos_caja
CREATE TABLE IF NOT EXISTS `turnos_caja` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `caja_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `fecha_apertura` datetime NOT NULL,
  `fecha_cierre` datetime DEFAULT NULL,
  `monto_apertura` decimal(12,2) NOT NULL DEFAULT '0.00',
  `monto_cierre` decimal(12,2) DEFAULT NULL,
  `monto_calculado` decimal(12,2) DEFAULT NULL,
  `diferencia` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_ventas` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_efectivo` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_tarjeta` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_otros` decimal(12,2) NOT NULL DEFAULT '0.00',
  `cantidad_ventas` int NOT NULL DEFAULT '0',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `estado` enum('abierto','cerrado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'abierto',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `turnos_caja_caja_id_foreign` (`caja_id`),
  KEY `turnos_caja_user_id_foreign` (`user_id`),
  CONSTRAINT `turnos_caja_caja_id_foreign` FOREIGN KEY (`caja_id`) REFERENCES `cajas` (`id`),
  CONSTRAINT `turnos_caja_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla tpv_minimarket.turnos_caja: ~10 rows (aproximadamente)
DELETE FROM `turnos_caja`;
INSERT INTO `turnos_caja` (`id`, `caja_id`, `user_id`, `fecha_apertura`, `fecha_cierre`, `monto_apertura`, `monto_cierre`, `monto_calculado`, `diferencia`, `total_ventas`, `total_efectivo`, `total_tarjeta`, `total_otros`, `cantidad_ventas`, `observaciones`, `estado`, `created_at`, `updated_at`) VALUES
	(1, 2, 1, '2026-04-03 08:08:00', '2026-04-03 20:17:00', 100.00, 382.00, 382.00, -1.00, 282.00, 197.40, 56.40, 28.20, 11, NULL, 'cerrado', '2026-04-03 13:08:00', '2026-04-04 01:17:00'),
	(2, 3, 11, '2026-04-07 08:10:00', '2026-04-07 20:48:00', 100.00, 570.00, 574.00, -3.00, 474.00, 331.80, 94.80, 47.40, 22, NULL, 'cerrado', '2026-04-07 13:10:00', '2026-04-08 01:48:00'),
	(3, 4, 3, '2026-04-11 08:21:00', '2026-04-11 20:47:00', 100.00, 721.00, 716.00, -3.00, 616.00, 431.20, 123.20, 61.60, 23, NULL, 'cerrado', '2026-04-11 13:21:00', '2026-04-12 01:47:00'),
	(4, 3, 11, '2026-04-15 08:01:00', '2026-04-15 20:01:00', 100.00, 654.00, 652.00, 3.00, 552.00, 386.40, 110.40, 55.20, 13, NULL, 'cerrado', '2026-04-15 13:01:00', '2026-04-16 01:01:00'),
	(5, 3, 4, '2026-04-19 08:24:00', '2026-04-19 20:38:00', 100.00, 728.00, 723.00, 2.00, 623.00, 436.10, 124.60, 62.30, 7, NULL, 'cerrado', '2026-04-19 13:24:00', '2026-04-20 01:38:00'),
	(6, 1, 4, '2026-04-23 08:16:00', '2026-04-23 20:31:00', 100.00, 737.00, 740.00, -5.00, 640.00, 448.00, 128.00, 64.00, 12, NULL, 'cerrado', '2026-04-23 13:16:00', '2026-04-24 01:31:00'),
	(7, 1, 12, '2026-04-27 08:08:00', '2026-04-27 20:12:00', 100.00, 945.00, 946.00, -3.00, 846.00, 592.20, 169.20, 84.60, 25, NULL, 'cerrado', '2026-04-27 13:08:00', '2026-04-28 01:12:00'),
	(8, 1, 13, '2026-05-01 08:10:00', '2026-05-01 20:42:00', 100.00, 434.00, 436.00, 3.00, 336.00, 235.20, 67.20, 33.60, 18, NULL, 'cerrado', '2026-05-01 13:10:00', '2026-05-02 01:42:00'),
	(9, 1, 9, '2026-05-05 08:12:00', '2026-05-05 20:21:00', 100.00, 455.00, 451.00, 0.00, 351.00, 245.70, 70.20, 35.10, 5, NULL, 'cerrado', '2026-05-05 13:12:00', '2026-05-06 01:21:00'),
	(10, 1, 1, '2026-05-09 08:13:00', '2026-05-09 20:49:00', 100.00, 526.00, 528.00, -3.00, 428.00, 299.60, 85.60, 42.80, 5, NULL, 'cerrado', '2026-05-09 13:13:00', '2026-05-10 01:49:00');

-- Volcando estructura para tabla tpv_minimarket.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_id` bigint unsigned DEFAULT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `ultimo_login` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_id_foreign` (`role_id`),
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla tpv_minimarket.users: ~13 rows (aproximadamente)
DELETE FROM `users`;
INSERT INTO `users` (`id`, `name`, `username`, `email`, `email_verified_at`, `password`, `role_id`, `telefono`, `avatar`, `activo`, `ultimo_login`, `remember_token`, `created_at`, `updated_at`) VALUES
	(1, 'Administrador del Sistema', 'admin', 'admin@tpvminimarket.com', NULL, '$2y$12$KlI3Io9QZ857uJU.ZR3ELu9KCd2GdhtTtUyruds8oe2bMKQCeenma', 1, '999-888-777', NULL, 1, '2026-05-19 20:42:46', NULL, '2026-05-19 01:12:40', '2026-05-19 20:42:46'),
	(2, 'Gerente Demo', 'gerente', 'gerente@tpvminimarket.com', NULL, '$2y$12$FaNGVc7JIKd1hD2D1fFcC.9QA9uWaD8LhLKORy9DcS62NKuIxQq/W', 2, NULL, NULL, 1, NULL, NULL, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(3, 'Cajero Demo', 'cajero', 'cajero@tpvminimarket.com', NULL, '$2y$12$PU7vDdYdumnL4w7PsCxr0uylUp/6uXWkIUNVSrkYJNSmRJjNc3/m2', 3, NULL, NULL, 1, NULL, NULL, '2026-05-19 01:12:41', '2026-05-19 01:12:41'),
	(4, 'Carlos Méndez', 'cmendez', 'carlos.mendez@tpv.com', NULL, '$2y$12$OP3E9KjHkd0WQ.pU8j0gGeYm/exNnDullNsMVmFxF7NYvum6s2Psa', 4, '987-100-001', NULL, 1, NULL, NULL, '2026-03-20 01:27:20', '2026-03-20 01:27:20'),
	(5, 'Lucía Torres', 'ltorres', 'lucia.torres@tpv.com', NULL, '$2y$12$R4AaLlNStPVXSV.YmJdTf.UgFQ7gdnbuaBrEYwKab9NMAZTCzqodm', 1, '987-100-002', NULL, 1, NULL, NULL, '2026-03-25 01:27:20', '2026-03-25 01:27:20'),
	(6, 'Miguel Salas', 'msalas', 'miguel.salas@tpv.com', NULL, '$2y$12$InKWKFyWQZQ0zkTlTDxAVuWLZ/7p8Bm9uejXkwvfz6IKOpH8b7DHu', 3, '987-100-003', NULL, 1, NULL, NULL, '2026-03-30 01:27:20', '2026-03-30 01:27:20'),
	(7, 'Rosa Huamán', 'rhuaman', 'rosa.huaman@tpv.com', NULL, '$2y$12$oEJEZviu7nO78veeYfvTXehs9j8Ix9wZp26XzEMMIpM0ps98BM8.y', 1, '987-100-004', NULL, 1, NULL, NULL, '2026-04-04 01:27:20', '2026-04-04 01:27:20'),
	(8, 'Jorge Quispe', 'jquispe', 'jorge.quispe@tpv.com', NULL, '$2y$12$j/T4fasECZmxRmg59y.nCeTBLVY8ShqkOG/7xQfAauKcHbwidspsu', 4, '987-100-005', NULL, 1, NULL, NULL, '2026-04-09 01:27:21', '2026-04-09 01:27:21'),
	(9, 'Elena Rivas', 'erivas', 'elena.rivas@tpv.com', NULL, '$2y$12$bqG0jCQku3s2OVVE9sVN5eqxitHFJ.MaAvjM4X5RtmOORcI1jVi1q', 2, '987-100-006', NULL, 1, NULL, NULL, '2026-04-14 01:27:21', '2026-04-14 01:27:21'),
	(10, 'Daniel Vásquez', 'dvasquez', 'daniel.vasquez@tpv.com', NULL, '$2y$12$N/cqIudvttAo4y.4W1iHOO0OIBG0KjEHWqXLRoYMS/uWjBBg0P5l6', 4, '987-100-007', NULL, 1, NULL, NULL, '2026-04-19 01:27:21', '2026-04-19 01:27:21'),
	(11, 'Isabel Cárdenas', 'icardenas', 'isabel.cardenas@tpv.com', NULL, '$2y$12$23ld1xojwP0EO5aFHV3N8uBXanQkG6UYnvmjDRNqBK/gswcumK8aO', 4, '987-100-008', NULL, 1, NULL, NULL, '2026-04-24 01:27:21', '2026-04-24 01:27:21'),
	(12, 'Andrés Pérez', 'aperez', 'andres.perez@tpv.com', NULL, '$2y$12$2AWq1kGr/VxwOqDBKyD6duQdRc.ki.DiMiKTIAs3v.kLdBtlp5..q', 4, '987-100-009', NULL, 1, NULL, NULL, '2026-04-29 01:27:21', '2026-04-29 01:27:21'),
	(13, 'Verónica Soto', 'vsoto', 'veronica.soto@tpv.com', NULL, '$2y$12$VPu4Fjnsg4ZzV4trrfqDmuOU/gmnOkygjnyWxySY8PFvvbthlHbaa', 1, '987-100-010', NULL, 1, NULL, NULL, '2026-05-04 01:27:22', '2026-05-04 01:27:22');

-- Volcando estructura para tabla tpv_minimarket.ventas
CREATE TABLE IF NOT EXISTS `ventas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero_ticket` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_comprobante` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'TICKET',
  `serie` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'T001',
  `fecha_venta` datetime NOT NULL,
  `cliente_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `turno_caja_id` bigint unsigned DEFAULT NULL,
  `comprobante_electronico_id` bigint unsigned DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `descuento` decimal(12,2) NOT NULL DEFAULT '0.00',
  `impuesto` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `monto_recibido` decimal(12,2) NOT NULL DEFAULT '0.00',
  `cambio` decimal(12,2) NOT NULL DEFAULT '0.00',
  `forma_pago` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'efectivo',
  `detalle_pago` json DEFAULT NULL,
  `estado` enum('completada','anulada','pendiente') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'completada',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ventas_numero_ticket_unique` (`numero_ticket`),
  KEY `ventas_cliente_id_foreign` (`cliente_id`),
  KEY `ventas_user_id_foreign` (`user_id`),
  KEY `ventas_turno_caja_id_foreign` (`turno_caja_id`),
  KEY `ventas_comprobante_electronico_id_foreign` (`comprobante_electronico_id`),
  CONSTRAINT `ventas_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ventas_comprobante_electronico_id_foreign` FOREIGN KEY (`comprobante_electronico_id`) REFERENCES `comprobantes_electronicos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ventas_turno_caja_id_foreign` FOREIGN KEY (`turno_caja_id`) REFERENCES `turnos_caja` (`id`),
  CONSTRAINT `ventas_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla tpv_minimarket.ventas: ~12 rows (aproximadamente)
DELETE FROM `ventas`;
INSERT INTO `ventas` (`id`, `numero_ticket`, `tipo_comprobante`, `serie`, `fecha_venta`, `cliente_id`, `user_id`, `turno_caja_id`, `comprobante_electronico_id`, `subtotal`, `descuento`, `impuesto`, `total`, `monto_recibido`, `cambio`, `forma_pago`, `detalle_pago`, `estado`, `observaciones`, `created_at`, `updated_at`) VALUES
	(1, 'T001-000001', 'TICKET', 'T001', '2026-05-18 10:15:00', 5, 8, 9, NULL, 30.25, 0.00, 5.45, 35.70, 40.00, 4.30, 'efectivo', NULL, 'completada', NULL, '2026-05-18 15:15:00', '2026-05-18 15:15:00'),
	(2, 'T001-000002', 'TICKET', 'T001', '2026-05-18 15:40:00', 10, 10, 5, NULL, 66.10, 0.00, 11.90, 78.00, 78.00, 0.00, 'tarjeta', NULL, 'completada', NULL, '2026-05-18 20:40:00', '2026-05-18 20:40:00'),
	(3, 'T001-000003', 'TICKET', 'T001', '2026-05-17 11:25:00', 8, 13, 6, NULL, 18.90, 0.00, 3.40, 22.30, 22.30, 0.00, 'tarjeta', NULL, 'completada', NULL, '2026-05-17 16:25:00', '2026-05-17 16:25:00'),
	(4, 'T001-000004', 'TICKET', 'T001', '2026-05-16 17:10:00', 5, 5, 9, NULL, 9.24, 0.00, 1.66, 10.90, 20.00, 9.10, 'efectivo', NULL, 'completada', NULL, '2026-05-16 22:10:00', '2026-05-16 22:10:00'),
	(5, 'T001-000005', 'TICKET', 'T001', '2026-05-15 09:30:00', 8, 8, 1, NULL, 83.39, 0.00, 15.01, 98.40, 98.40, 0.00, 'tarjeta', NULL, 'completada', NULL, '2026-05-15 14:30:00', '2026-05-15 14:30:00'),
	(6, 'T001-000006', 'TICKET', 'T001', '2026-05-14 13:55:00', 3, 4, 4, NULL, 41.69, 0.00, 7.51, 49.20, 49.20, 0.00, 'transferencia', NULL, 'completada', NULL, '2026-05-14 18:55:00', '2026-05-14 18:55:00'),
	(7, 'T001-000007', 'TICKET', 'T001', '2026-05-13 18:20:00', 4, 12, 9, NULL, 62.20, 0.00, 11.20, 73.40, 80.00, 6.60, 'efectivo', NULL, 'completada', NULL, '2026-05-13 23:20:00', '2026-05-13 23:20:00'),
	(8, 'T001-000008', 'TICKET', 'T001', '2026-05-11 10:45:00', 3, 7, 6, NULL, 108.64, 0.00, 19.56, 128.20, 130.00, 1.80, 'efectivo', NULL, 'completada', NULL, '2026-05-11 15:45:00', '2026-05-11 15:45:00'),
	(9, 'T001-000009', 'TICKET', 'T001', '2026-05-08 14:30:00', 7, 8, 8, NULL, 51.19, 0.00, 9.21, 60.40, 60.40, 0.00, 'tarjeta', NULL, 'completada', NULL, '2026-05-08 19:30:00', '2026-05-08 19:30:00'),
	(10, 'T001-000010', 'TICKET', 'T001', '2026-05-03 16:00:00', 3, 11, 10, NULL, 52.46, 0.00, 9.44, 61.90, 70.00, 8.10, 'efectivo', NULL, 'completada', NULL, '2026-05-03 21:00:00', '2026-05-03 21:00:00'),
	(11, 'T001-000011', 'TICKET', 'T001', '2026-04-28 12:15:00', 10, 7, 2, NULL, 48.31, 0.00, 8.69, 57.00, 60.00, 3.00, 'efectivo', NULL, 'completada', NULL, '2026-04-28 17:15:00', '2026-04-28 17:15:00'),
	(12, 'T001-000012', 'TICKET', 'T001', '2026-04-23 11:30:00', 1, 9, 4, NULL, 148.39, 0.00, 26.71, 175.10, 180.00, 4.90, 'efectivo', NULL, 'completada', NULL, '2026-04-23 16:30:00', '2026-04-23 16:30:00');

-- Volcando estructura para tabla tpv_minimarket.venta_detalles
CREATE TABLE IF NOT EXISTS `venta_detalles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `venta_id` bigint unsigned NOT NULL,
  `producto_id` bigint unsigned NOT NULL,
  `codigo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cantidad` decimal(12,3) NOT NULL,
  `precio_unitario` decimal(12,2) NOT NULL,
  `descuento` decimal(12,2) NOT NULL DEFAULT '0.00',
  `impuesto` decimal(12,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(12,2) NOT NULL,
  `total` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `venta_detalles_venta_id_foreign` (`venta_id`),
  KEY `venta_detalles_producto_id_foreign` (`producto_id`),
  CONSTRAINT `venta_detalles_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  CONSTRAINT `venta_detalles_venta_id_foreign` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla tpv_minimarket.venta_detalles: ~41 rows (aproximadamente)
DELETE FROM `venta_detalles`;
INSERT INTO `venta_detalles` (`id`, `venta_id`, `producto_id`, `codigo`, `descripcion`, `cantidad`, `precio_unitario`, `descuento`, `impuesto`, `subtotal`, `total`, `created_at`, `updated_at`) VALUES
	(1, 1, 19, 'P000019', 'Manzana Roja', 3.000, 6.90, 0.00, 0.00, 20.70, 20.70, '2026-05-18 15:15:00', '2026-05-18 15:15:00'),
	(2, 1, 5, 'P000005', 'Fideos Don Vittorio 500g', 2.000, 4.50, 0.00, 0.00, 9.00, 9.00, '2026-05-18 15:15:00', '2026-05-18 15:15:00'),
	(3, 1, 9, 'P000009', 'Agua Cielo 625ml', 3.000, 2.00, 0.00, 0.00, 6.00, 6.00, '2026-05-18 15:15:00', '2026-05-18 15:15:00'),
	(4, 2, 42, 'P000042', 'Pastillas Paracetamol x10', 4.000, 4.50, 0.00, 0.00, 18.00, 18.00, '2026-05-18 20:40:00', '2026-05-18 20:40:00'),
	(5, 2, 36, 'P000036', 'Caramelos Surtidos x100', 3.000, 7.50, 0.00, 0.00, 22.50, 22.50, '2026-05-18 20:40:00', '2026-05-18 20:40:00'),
	(6, 2, 2, 'P000002', 'Aceite Primor 1L', 3.000, 12.50, 0.00, 0.00, 37.50, 37.50, '2026-05-18 20:40:00', '2026-05-18 20:40:00'),
	(7, 3, 30, 'P000030', 'Jabón Bolívar 250g', 1.000, 3.50, 0.00, 0.00, 3.50, 3.50, '2026-05-17 16:25:00', '2026-05-17 16:25:00'),
	(8, 3, 23, 'P000023', 'Limón', 3.000, 6.00, 0.00, 0.00, 18.00, 18.00, '2026-05-17 16:25:00', '2026-05-17 16:25:00'),
	(9, 3, 16, 'P000016', 'Pan Francés', 2.000, 0.40, 0.00, 0.00, 0.80, 0.80, '2026-05-17 16:25:00', '2026-05-17 16:25:00'),
	(10, 4, 4, 'P000004', 'Sal Yodada 1kg', 2.000, 2.20, 0.00, 0.00, 4.40, 4.40, '2026-05-16 22:10:00', '2026-05-16 22:10:00'),
	(11, 4, 10, 'P000010', 'Cerveza Cristal 630ml', 1.000, 6.50, 0.00, 0.00, 6.50, 6.50, '2026-05-16 22:10:00', '2026-05-16 22:10:00'),
	(12, 5, 7, 'P000007', 'Inca Kola 1.5L', 1.000, 6.50, 0.00, 0.00, 6.50, 6.50, '2026-05-15 14:30:00', '2026-05-15 14:30:00'),
	(13, 5, 31, 'P000031', 'Shampoo H&S 200ml', 3.000, 18.90, 0.00, 0.00, 56.70, 56.70, '2026-05-15 14:30:00', '2026-05-15 14:30:00'),
	(14, 5, 11, 'P000011', 'Jugo Frugos Manzana 1L', 1.000, 5.20, 0.00, 0.00, 5.20, 5.20, '2026-05-15 14:30:00', '2026-05-15 14:30:00'),
	(15, 5, 4, 'P000004', 'Sal Yodada 1kg', 1.000, 2.20, 0.00, 0.00, 2.20, 2.20, '2026-05-15 14:30:00', '2026-05-15 14:30:00'),
	(16, 5, 35, 'P000035', 'Pollo Congelado 1kg', 2.000, 13.90, 0.00, 0.00, 27.80, 27.80, '2026-05-15 14:30:00', '2026-05-15 14:30:00'),
	(17, 6, 25, 'P000025', 'Doritos Nacho 110g', 4.000, 6.80, 0.00, 0.00, 27.20, 27.20, '2026-05-14 18:55:00', '2026-05-14 18:55:00'),
	(18, 6, 40, 'P000040', 'Cuaderno Cuadriculado A4', 4.000, 5.50, 0.00, 0.00, 22.00, 22.00, '2026-05-14 18:55:00', '2026-05-14 18:55:00'),
	(19, 7, 9, 'P000009', 'Agua Cielo 625ml', 4.000, 2.00, 0.00, 0.00, 8.00, 8.00, '2026-05-13 23:20:00', '2026-05-13 23:20:00'),
	(20, 7, 24, 'P000024', 'Lays Original 105g', 2.000, 6.50, 0.00, 0.00, 13.00, 13.00, '2026-05-13 23:20:00', '2026-05-13 23:20:00'),
	(21, 7, 29, 'P000029', 'Lejía Clorox 1L', 4.000, 6.50, 0.00, 0.00, 26.00, 26.00, '2026-05-13 23:20:00', '2026-05-13 23:20:00'),
	(22, 7, 19, 'P000019', 'Manzana Roja', 1.000, 6.90, 0.00, 0.00, 6.90, 6.90, '2026-05-13 23:20:00', '2026-05-13 23:20:00'),
	(23, 7, 7, 'P000007', 'Inca Kola 1.5L', 3.000, 6.50, 0.00, 0.00, 19.50, 19.50, '2026-05-13 23:20:00', '2026-05-13 23:20:00'),
	(24, 8, 7, 'P000007', 'Inca Kola 1.5L', 1.000, 6.50, 0.00, 0.00, 6.50, 6.50, '2026-05-11 15:45:00', '2026-05-11 15:45:00'),
	(25, 8, 31, 'P000031', 'Shampoo H&S 200ml', 3.000, 18.90, 0.00, 0.00, 56.70, 56.70, '2026-05-11 15:45:00', '2026-05-11 15:45:00'),
	(26, 8, 36, 'P000036', 'Caramelos Surtidos x100', 2.000, 7.50, 0.00, 0.00, 15.00, 15.00, '2026-05-11 15:45:00', '2026-05-11 15:45:00'),
	(27, 8, 2, 'P000002', 'Aceite Primor 1L', 4.000, 12.50, 0.00, 0.00, 50.00, 50.00, '2026-05-11 15:45:00', '2026-05-11 15:45:00'),
	(28, 9, 27, 'P000027', 'Galletas Oreo', 3.000, 3.80, 0.00, 0.00, 11.40, 11.40, '2026-05-08 19:30:00', '2026-05-08 19:30:00'),
	(29, 9, 29, 'P000029', 'Lejía Clorox 1L', 2.000, 6.50, 0.00, 0.00, 13.00, 13.00, '2026-05-08 19:30:00', '2026-05-08 19:30:00'),
	(30, 9, 14, 'P000014', 'Queso Fresco 250g', 4.000, 9.00, 0.00, 0.00, 36.00, 36.00, '2026-05-08 19:30:00', '2026-05-08 19:30:00'),
	(31, 10, 8, 'P000008', 'Coca Cola 1.5L', 3.000, 6.80, 0.00, 0.00, 20.40, 20.40, '2026-05-03 21:00:00', '2026-05-03 21:00:00'),
	(32, 10, 36, 'P000036', 'Caramelos Surtidos x100', 2.000, 7.50, 0.00, 0.00, 15.00, 15.00, '2026-05-03 21:00:00', '2026-05-03 21:00:00'),
	(33, 10, 39, 'P000039', 'Atún Florida en Aceite', 3.000, 5.90, 0.00, 0.00, 17.70, 17.70, '2026-05-03 21:00:00', '2026-05-03 21:00:00'),
	(34, 10, 4, 'P000004', 'Sal Yodada 1kg', 4.000, 2.20, 0.00, 0.00, 8.80, 8.80, '2026-05-03 21:00:00', '2026-05-03 21:00:00'),
	(35, 11, 28, 'P000028', 'Detergente Ariel 850g', 1.000, 16.50, 0.00, 0.00, 16.50, 16.50, '2026-04-28 17:15:00', '2026-04-28 17:15:00'),
	(36, 11, 15, 'P000015', 'Mantequilla Gloria 200g', 3.000, 8.50, 0.00, 0.00, 25.50, 25.50, '2026-04-28 17:15:00', '2026-04-28 17:15:00'),
	(37, 11, 36, 'P000036', 'Caramelos Surtidos x100', 2.000, 7.50, 0.00, 0.00, 15.00, 15.00, '2026-04-28 17:15:00', '2026-04-28 17:15:00'),
	(38, 12, 33, 'P000033', 'Papel Higiénico Suave x4', 4.000, 10.50, 0.00, 0.00, 42.00, 42.00, '2026-04-23 16:30:00', '2026-04-23 16:30:00'),
	(39, 12, 28, 'P000028', 'Detergente Ariel 850g', 1.000, 16.50, 0.00, 0.00, 16.50, 16.50, '2026-04-23 16:30:00', '2026-04-23 16:30:00'),
	(40, 12, 5, 'P000005', 'Fideos Don Vittorio 500g', 2.000, 4.50, 0.00, 0.00, 9.00, 9.00, '2026-04-23 16:30:00', '2026-04-23 16:30:00'),
	(41, 12, 37, 'P000037', 'Vino Tinto Tabernero 750ml', 4.000, 26.90, 0.00, 0.00, 107.60, 107.60, '2026-04-23 16:30:00', '2026-04-23 16:30:00');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
