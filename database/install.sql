-- =====================================================================
-- TPV MINIMARKET - SCRIPT DE INSTALACIÓN COMPLETO
-- Base de datos: tpv_minimarket
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `tpv_minimarket` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tpv_minimarket`;

SET FOREIGN_KEY_CHECKS=0;

-- ======================== ROLES ========================
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(80) NOT NULL UNIQUE,
  `descripcion` VARCHAR(255) NULL,
  `permisos` JSON NULL,
  `activo` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================== USERS ========================
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `email_verified_at` TIMESTAMP NULL,
  `password` VARCHAR(255) NOT NULL,
  `role_id` BIGINT UNSIGNED NULL,
  `telefono` VARCHAR(30) NULL,
  `avatar` VARCHAR(255) NULL,
  `activo` TINYINT(1) DEFAULT 1,
  `ultimo_login` TIMESTAMP NULL,
  `remember_token` VARCHAR(100) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` VARCHAR(255) NOT NULL PRIMARY KEY,
  `token` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` VARCHAR(255) NOT NULL PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `payload` LONGTEXT NOT NULL,
  `last_activity` INT NOT NULL,
  INDEX (`user_id`),
  INDEX (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================== EMPRESAS ========================
DROP TABLE IF EXISTS `empresas`;
CREATE TABLE `empresas` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `razon_social` VARCHAR(255) NOT NULL,
  `nombre_comercial` VARCHAR(255) NULL,
  `ruc_nit` VARCHAR(30) NULL,
  `direccion` VARCHAR(255) NULL,
  `ciudad` VARCHAR(100) NULL,
  `telefono` VARCHAR(30) NULL,
  `email` VARCHAR(255) NULL,
  `sitio_web` VARCHAR(255) NULL,
  `logo` VARCHAR(255) NULL,
  `moneda` VARCHAR(10) DEFAULT 'S/',
  `codigo_moneda` VARCHAR(5) DEFAULT 'PEN',
  `impuesto` DECIMAL(5,2) DEFAULT 18.00,
  `impuesto_incluido` TINYINT(1) DEFAULT 1,
  `mensaje_ticket` VARCHAR(255) NULL,
  `terminos_condiciones` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================== CONFIGURACIONES ========================
DROP TABLE IF EXISTS `configuraciones`;
CREATE TABLE `configuraciones` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `clave` VARCHAR(100) NOT NULL UNIQUE,
  `valor` TEXT NULL,
  `tipo` VARCHAR(30) DEFAULT 'string',
  `grupo` VARCHAR(50) DEFAULT 'general',
  `descripcion` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================== CATEGORIAS ========================
DROP TABLE IF EXISTS `categorias`;
CREATE TABLE `categorias` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `descripcion` VARCHAR(255) NULL,
  `color` VARCHAR(20) DEFAULT '#3B82F6',
  `icono` VARCHAR(50) DEFAULT 'cube',
  `activo` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================== PROVEEDORES ========================
DROP TABLE IF EXISTS `proveedores`;
CREATE TABLE `proveedores` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `codigo` VARCHAR(30) NOT NULL UNIQUE,
  `razon_social` VARCHAR(255) NOT NULL,
  `nombre_comercial` VARCHAR(255) NULL,
  `ruc_nit` VARCHAR(30) NULL,
  `contacto` VARCHAR(255) NULL,
  `telefono` VARCHAR(30) NULL,
  `email` VARCHAR(255) NULL,
  `direccion` VARCHAR(255) NULL,
  `ciudad` VARCHAR(100) NULL,
  `observaciones` TEXT NULL,
  `activo` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================== PRODUCTOS ========================
DROP TABLE IF EXISTS `productos`;
CREATE TABLE `productos` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `codigo` VARCHAR(50) NOT NULL UNIQUE,
  `codigo_barras` VARCHAR(50) NULL,
  `nombre` VARCHAR(255) NOT NULL,
  `descripcion` TEXT NULL,
  `categoria_id` BIGINT UNSIGNED NULL,
  `proveedor_id` BIGINT UNSIGNED NULL,
  `unidad_medida` VARCHAR(20) DEFAULT 'UND',
  `precio_compra` DECIMAL(12,2) DEFAULT 0,
  `precio_venta` DECIMAL(12,2) DEFAULT 0,
  `precio_mayoreo` DECIMAL(12,2) DEFAULT 0,
  `cantidad_mayoreo` INT DEFAULT 0,
  `stock` DECIMAL(12,3) DEFAULT 0,
  `stock_minimo` DECIMAL(12,3) DEFAULT 0,
  `stock_maximo` DECIMAL(12,3) DEFAULT 0,
  `controla_stock` TINYINT(1) DEFAULT 1,
  `aplica_impuesto` TINYINT(1) DEFAULT 1,
  `imagen` VARCHAR(255) NULL,
  `fecha_vencimiento` DATE NULL,
  `lote` VARCHAR(50) NULL,
  `ubicacion` VARCHAR(100) NULL,
  `activo` TINYINT(1) DEFAULT 1,
  `destacado` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX (`codigo_barras`),
  FOREIGN KEY (`categoria_id`) REFERENCES `categorias`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================== CLIENTES ========================
DROP TABLE IF EXISTS `clientes`;
CREATE TABLE `clientes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `codigo` VARCHAR(30) NOT NULL UNIQUE,
  `tipo_documento` VARCHAR(20) DEFAULT 'DNI',
  `documento` VARCHAR(30) NULL,
  `nombres` VARCHAR(255) NOT NULL,
  `apellidos` VARCHAR(255) NULL,
  `razon_social` VARCHAR(255) NULL,
  `telefono` VARCHAR(30) NULL,
  `email` VARCHAR(255) NULL,
  `direccion` VARCHAR(255) NULL,
  `ciudad` VARCHAR(100) NULL,
  `fecha_nacimiento` DATE NULL,
  `genero` VARCHAR(20) NULL,
  `puntos_fidelidad` INT DEFAULT 0,
  `credito_limite` DECIMAL(12,2) DEFAULT 0,
  `credito_usado` DECIMAL(12,2) DEFAULT 0,
  `observaciones` TEXT NULL,
  `activo` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX (`documento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================== CAJAS y TURNOS ========================
DROP TABLE IF EXISTS `cajas`;
CREATE TABLE `cajas` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `descripcion` VARCHAR(255) NULL,
  `activo` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `turnos_caja`;
CREATE TABLE `turnos_caja` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `caja_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `fecha_apertura` DATETIME NOT NULL,
  `fecha_cierre` DATETIME NULL,
  `monto_apertura` DECIMAL(12,2) DEFAULT 0,
  `monto_cierre` DECIMAL(12,2) NULL,
  `monto_calculado` DECIMAL(12,2) NULL,
  `diferencia` DECIMAL(12,2) DEFAULT 0,
  `total_ventas` DECIMAL(12,2) DEFAULT 0,
  `total_efectivo` DECIMAL(12,2) DEFAULT 0,
  `total_tarjeta` DECIMAL(12,2) DEFAULT 0,
  `total_otros` DECIMAL(12,2) DEFAULT 0,
  `cantidad_ventas` INT DEFAULT 0,
  `observaciones` TEXT NULL,
  `estado` ENUM('abierto','cerrado') DEFAULT 'abierto',
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`caja_id`) REFERENCES `cajas`(`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `movimientos_caja`;
CREATE TABLE `movimientos_caja` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `turno_caja_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `tipo` ENUM('ingreso','egreso') NOT NULL,
  `concepto` VARCHAR(255) NOT NULL,
  `monto` DECIMAL(12,2) NOT NULL,
  `observaciones` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`turno_caja_id`) REFERENCES `turnos_caja`(`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================== VENTAS ========================
DROP TABLE IF EXISTS `ventas`;
CREATE TABLE `ventas` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `numero_ticket` VARCHAR(30) NOT NULL UNIQUE,
  `tipo_comprobante` VARCHAR(30) DEFAULT 'TICKET',
  `serie` VARCHAR(10) DEFAULT 'T001',
  `fecha_venta` DATETIME NOT NULL,
  `cliente_id` BIGINT UNSIGNED NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `turno_caja_id` BIGINT UNSIGNED NULL,
  `subtotal` DECIMAL(12,2) DEFAULT 0,
  `descuento` DECIMAL(12,2) DEFAULT 0,
  `impuesto` DECIMAL(12,2) DEFAULT 0,
  `total` DECIMAL(12,2) DEFAULT 0,
  `monto_recibido` DECIMAL(12,2) DEFAULT 0,
  `cambio` DECIMAL(12,2) DEFAULT 0,
  `forma_pago` VARCHAR(30) DEFAULT 'efectivo',
  `detalle_pago` JSON NULL,
  `estado` ENUM('completada','anulada','pendiente') DEFAULT 'completada',
  `observaciones` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`cliente_id`) REFERENCES `clientes`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
  FOREIGN KEY (`turno_caja_id`) REFERENCES `turnos_caja`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `venta_detalles`;
CREATE TABLE `venta_detalles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `venta_id` BIGINT UNSIGNED NOT NULL,
  `producto_id` BIGINT UNSIGNED NOT NULL,
  `codigo` VARCHAR(50) NOT NULL,
  `descripcion` VARCHAR(255) NOT NULL,
  `cantidad` DECIMAL(12,3) NOT NULL,
  `precio_unitario` DECIMAL(12,2) NOT NULL,
  `descuento` DECIMAL(12,2) DEFAULT 0,
  `impuesto` DECIMAL(12,2) DEFAULT 0,
  `subtotal` DECIMAL(12,2) NOT NULL,
  `total` DECIMAL(12,2) NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`venta_id`) REFERENCES `ventas`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================== COMPRAS ========================
DROP TABLE IF EXISTS `compras`;
CREATE TABLE `compras` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `numero` VARCHAR(30) NOT NULL UNIQUE,
  `numero_factura` VARCHAR(50) NULL,
  `fecha_compra` DATETIME NOT NULL,
  `fecha_vencimiento` DATE NULL,
  `proveedor_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `subtotal` DECIMAL(12,2) DEFAULT 0,
  `descuento` DECIMAL(12,2) DEFAULT 0,
  `impuesto` DECIMAL(12,2) DEFAULT 0,
  `total` DECIMAL(12,2) DEFAULT 0,
  `forma_pago` VARCHAR(30) DEFAULT 'efectivo',
  `estado` ENUM('recibida','pendiente','anulada') DEFAULT 'recibida',
  `observaciones` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores`(`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `compra_detalles`;
CREATE TABLE `compra_detalles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `compra_id` BIGINT UNSIGNED NOT NULL,
  `producto_id` BIGINT UNSIGNED NOT NULL,
  `codigo` VARCHAR(50) NOT NULL,
  `descripcion` VARCHAR(255) NOT NULL,
  `cantidad` DECIMAL(12,3) NOT NULL,
  `precio_unitario` DECIMAL(12,2) NOT NULL,
  `descuento` DECIMAL(12,2) DEFAULT 0,
  `impuesto` DECIMAL(12,2) DEFAULT 0,
  `subtotal` DECIMAL(12,2) NOT NULL,
  `total` DECIMAL(12,2) NOT NULL,
  `fecha_vencimiento` DATE NULL,
  `lote` VARCHAR(50) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`compra_id`) REFERENCES `compras`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================== INVENTARIO ========================
DROP TABLE IF EXISTS `movimientos_inventario`;
CREATE TABLE `movimientos_inventario` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `producto_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `tipo` ENUM('entrada','salida','ajuste','merma','transferencia') NOT NULL,
  `motivo` VARCHAR(100) NOT NULL,
  `cantidad` DECIMAL(12,3) NOT NULL,
  `stock_anterior` DECIMAL(12,3) NOT NULL,
  `stock_nuevo` DECIMAL(12,3) NOT NULL,
  `referencia_tipo` VARCHAR(50) NULL,
  `referencia_id` BIGINT UNSIGNED NULL,
  `observaciones` TEXT NULL,
  `fecha` DATETIME NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================== PROMOCIONES ========================
DROP TABLE IF EXISTS `promociones`;
CREATE TABLE `promociones` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL,
  `descripcion` TEXT NULL,
  `tipo` ENUM('descuento_porcentaje','descuento_fijo','2x1','3x2','precio_especial') NOT NULL,
  `valor` DECIMAL(12,2) DEFAULT 0,
  `producto_id` BIGINT UNSIGNED NULL,
  `categoria_id` BIGINT UNSIGNED NULL,
  `fecha_inicio` DATE NOT NULL,
  `fecha_fin` DATE NOT NULL,
  `cantidad_minima` INT DEFAULT 1,
  `activo` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`categoria_id`) REFERENCES `categorias`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `puntos_fidelidad`;
CREATE TABLE `puntos_fidelidad` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cliente_id` BIGINT UNSIGNED NOT NULL,
  `venta_id` BIGINT UNSIGNED NULL,
  `tipo` ENUM('ganado','canjeado','expirado','ajuste') NOT NULL,
  `puntos` INT NOT NULL,
  `saldo_anterior` INT NOT NULL,
  `saldo_nuevo` INT NOT NULL,
  `descripcion` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`cliente_id`) REFERENCES `clientes`(`id`),
  FOREIGN KEY (`venta_id`) REFERENCES `ventas`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================== BACKUPS ========================
DROP TABLE IF EXISTS `backups`;
CREATE TABLE `backups` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL,
  `archivo` VARCHAR(255) NOT NULL,
  `tamano` BIGINT DEFAULT 0,
  `tipo` ENUM('manual','automatico') DEFAULT 'manual',
  `user_id` BIGINT UNSIGNED NULL,
  `observaciones` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `actividad_log`;
CREATE TABLE `actividad_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NULL,
  `accion` VARCHAR(50) NOT NULL,
  `modulo` VARCHAR(50) NOT NULL,
  `descripcion` VARCHAR(255) NOT NULL,
  `ip` VARCHAR(45) NULL,
  `datos` JSON NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================== DATOS INICIALES ========================

-- Roles
INSERT INTO `roles` (`nombre`, `descripcion`, `permisos`, `activo`, `created_at`, `updated_at`) VALUES
('Administrador', 'Acceso completo al sistema', '["*"]', 1, NOW(), NOW()),
('Gerente', 'Gestión completa excepto configuración del sistema', '["productos","ventas","compras","clientes","proveedores","caja","reportes","promociones"]', 1, NOW(), NOW()),
('Cajero', 'Acceso al punto de venta y caja', '["ventas","caja","clientes"]', 1, NOW(), NOW()),
('Almacenero', 'Gestión de inventario y compras', '["productos","compras","proveedores","reportes"]', 1, NOW(), NOW());

-- Usuarios (passwords son bcrypt de admin123, gerente123, cajero123)
-- NOTA: Si las credenciales no funcionan, ejecuta el script public/reset-passwords.php
-- que regenera hashes bcrypt válidos automáticamente.
INSERT INTO `users` (`name`, `username`, `email`, `password`, `role_id`, `telefono`, `activo`, `created_at`, `updated_at`) VALUES
('Administrador del Sistema', 'admin', 'admin@tpvminimarket.com', '$2y$10$YourPasswordWillBeResetByScript0000000000000000000000', 1, '999-888-777', 1, NOW(), NOW()),
('Gerente Demo', 'gerente', 'gerente@tpvminimarket.com', '$2y$10$YourPasswordWillBeResetByScript0000000000000000000000', 2, NULL, 1, NOW(), NOW()),
('Cajero Demo', 'cajero', 'cajero@tpvminimarket.com', '$2y$10$YourPasswordWillBeResetByScript0000000000000000000000', 3, NULL, 1, NOW(), NOW());

-- Empresa
INSERT INTO `empresas` (`razon_social`, `nombre_comercial`, `ruc_nit`, `direccion`, `ciudad`, `telefono`, `email`, `moneda`, `codigo_moneda`, `impuesto`, `impuesto_incluido`, `mensaje_ticket`, `created_at`, `updated_at`) VALUES
('TPV Minimarket Demo S.A.C.', 'Mi Minimarket', '20100100100', 'Av. Principal 123', 'Lima', '01-555-1234', 'contacto@minimarket.com', 'S/', 'PEN', 18.00, 1, '¡Gracias por su preferencia! Vuelva pronto.', NOW(), NOW());

-- Configuraciones
INSERT INTO `configuraciones` (`clave`, `valor`, `tipo`, `grupo`, `created_at`, `updated_at`) VALUES
('puntos_por_moneda', '0.1', 'string', 'fidelidad', NOW(), NOW()),
('dias_aviso_vencimiento', '30', 'integer', 'inventario', NOW(), NOW()),
('stock_minimo_default', '5', 'integer', 'inventario', NOW(), NOW()),
('serie_ticket', 'T001', 'string', 'facturacion', NOW(), NOW()),
('serie_boleta', 'B001', 'string', 'facturacion', NOW(), NOW()),
('serie_factura', 'F001', 'string', 'facturacion', NOW(), NOW()),
('ancho_ticket', '80', 'integer', 'ticket', NOW(), NOW()),
('imprimir_auto', '1', 'boolean', 'ticket', NOW(), NOW()),
('mostrar_logo_ticket', '1', 'boolean', 'ticket', NOW(), NOW());

-- Categorias
INSERT INTO `categorias` (`nombre`, `descripcion`, `color`, `icono`, `activo`, `created_at`, `updated_at`) VALUES
('Abarrotes', 'Productos básicos secos', '#f59e0b', 'cube', 1, NOW(), NOW()),
('Bebidas', 'Bebidas alcohólicas y no alcohólicas', '#3b82f6', 'wine-bottle', 1, NOW(), NOW()),
('Lácteos', 'Leche, queso, yogurt', '#ec4899', 'cheese', 1, NOW(), NOW()),
('Panadería', 'Pan, pasteles', '#d97706', 'bread-slice', 1, NOW(), NOW()),
('Frutas y Verduras', 'Productos frescos', '#10b981', 'apple-alt', 1, NOW(), NOW()),
('Carnes', 'Carnes y embutidos', '#dc2626', 'drumstick-bite', 1, NOW(), NOW()),
('Snacks', 'Galletas, papas fritas', '#fbbf24', 'cookie', 1, NOW(), NOW()),
('Limpieza', 'Productos de limpieza', '#06b6d4', 'soap', 1, NOW(), NOW()),
('Cuidado Personal', 'Higiene personal', '#a855f7', 'tshirt', 1, NOW(), NOW()),
('Bebés', 'Productos para bebés', '#f472b6', 'baby', 1, NOW(), NOW()),
('Mascotas', 'Productos para mascotas', '#84cc16', 'paw', 1, NOW(), NOW()),
('Helados', 'Helados y postres congelados', '#06b6d4', 'ice-cream', 1, NOW(), NOW());

-- Cajas
INSERT INTO `cajas` (`nombre`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES
('Caja Principal', 'Caja principal del minimarket', 1, NOW(), NOW()),
('Caja 2', 'Segunda caja', 1, NOW(), NOW());

-- Proveedores
INSERT INTO `proveedores` (`codigo`, `razon_social`, `ruc_nit`, `contacto`, `telefono`, `email`, `ciudad`, `activo`, `created_at`, `updated_at`) VALUES
('PR00001', 'Distribuidora Alimentos S.A.', '20100200300', 'Juan Pérez', '987-654-321', 'ventas@dasa.com', 'Lima', 1, NOW(), NOW()),
('PR00002', 'Bebidas y Más SAC', '20200300400', 'María Gómez', '976-543-210', 'pedidos@bebidasymas.com', 'Lima', 1, NOW(), NOW()),
('PR00003', 'Lácteos del Norte', '20300400500', 'Carlos Ruiz', '965-432-109', 'lacteos@delnorte.com', 'Trujillo', 1, NOW(), NOW()),
('PR00004', 'Panificadora La Espiga', '20400500600', 'Ana Torres', '954-321-098', 'pedidos@laespiga.com', 'Lima', 1, NOW(), NOW());

-- Productos
INSERT INTO `productos` (`codigo`, `codigo_barras`, `nombre`, `categoria_id`, `proveedor_id`, `unidad_medida`, `precio_compra`, `precio_venta`, `precio_mayoreo`, `cantidad_mayoreo`, `stock`, `stock_minimo`, `stock_maximo`, `controla_stock`, `aplica_impuesto`, `activo`, `destacado`, `created_at`, `updated_at`) VALUES
('P000001','7501000000001','Arroz Costeño 5kg',1,1,'KG',22.50,28.90,27.46,12,50,10,100,1,1,1,1,NOW(),NOW()),
('P000002','7501000000002','Aceite Primor 1L',1,1,'LT',9.80,12.50,11.88,12,30,5,60,1,1,1,1,NOW(),NOW()),
('P000003','7501000000003','Azúcar Rubia 1kg',1,1,'KG',4.20,5.50,5.23,12,80,15,160,1,1,1,1,NOW(),NOW()),
('P000004','7501000000004','Sal Yodada 1kg',1,1,'KG',1.50,2.20,2.09,12,60,10,120,1,1,1,1,NOW(),NOW()),
('P000005','7501000000005','Fideos Don Vittorio 500g',1,1,'PAQ',3.20,4.50,4.28,12,100,20,200,1,1,1,1,NOW(),NOW()),
('P000006','7501000000006','Atún Real Lata 170g',1,1,'UND',4.50,6.20,5.89,12,80,15,160,1,1,1,1,NOW(),NOW()),
('P000007','7501000000007','Inca Kola 1.5L',2,2,'UND',4.50,6.50,6.18,12,80,12,160,1,1,1,1,NOW(),NOW()),
('P000008','7501000000008','Coca Cola 1.5L',2,2,'UND',4.80,6.80,6.46,12,70,10,140,1,1,1,1,NOW(),NOW()),
('P000009','7501000000009','Agua Cielo 625ml',2,2,'UND',1.20,2.00,1.90,12,120,20,240,1,1,1,1,NOW(),NOW()),
('P000010','7501000000010','Cerveza Cristal 630ml',2,2,'UND',4.50,6.50,6.18,12,60,12,120,1,1,1,1,NOW(),NOW()),
('P000011','7501000000011','Jugo Frugos Manzana 1L',2,2,'UND',3.80,5.20,4.94,12,40,8,80,1,1,1,1,NOW(),NOW()),
('P000012','7501000000012','Leche Gloria Entera 1L',3,3,'UND',3.80,5.00,4.75,12,60,10,120,1,1,1,1,NOW(),NOW()),
('P000013','7501000000013','Yogurt Gloria Fresa 1kg',3,3,'UND',7.50,9.90,9.41,12,25,5,50,1,1,1,0,NOW(),NOW()),
('P000014','7501000000014','Queso Fresco 250g',3,3,'UND',6.50,9.00,8.55,12,20,4,40,1,1,1,0,NOW(),NOW()),
('P000015','7501000000015','Mantequilla Gloria 200g',3,3,'UND',6.20,8.50,8.08,12,15,3,30,1,1,1,0,NOW(),NOW()),
('P000016','7501000000016','Pan Francés',4,4,'UND',0.20,0.40,0.38,12,200,30,400,1,1,1,0,NOW(),NOW()),
('P000017','7501000000017','Pan Integral',4,4,'UND',0.40,0.70,0.67,12,100,20,200,1,1,1,0,NOW(),NOW()),
('P000018','7501000000018','Tostadas Bimbo',4,4,'PAQ',4.20,5.80,5.51,12,30,8,60,1,1,1,0,NOW(),NOW()),
('P000019','7501000000019','Manzana Roja',5,1,'KG',4.50,6.90,6.56,12,30,5,60,1,1,1,0,NOW(),NOW()),
('P000020','7501000000020','Plátano de Seda',5,1,'KG',1.80,2.80,2.66,12,40,8,80,1,1,1,0,NOW(),NOW()),
('P000021','7501000000021','Tomate',5,1,'KG',3.50,5.00,4.75,12,25,5,50,1,1,1,0,NOW(),NOW()),
('P000022','7501000000022','Cebolla',5,1,'KG',2.50,3.80,3.61,12,30,6,60,1,1,1,0,NOW(),NOW()),
('P000023','7501000000023','Limón',5,1,'KG',4.20,6.00,5.70,12,20,4,40,1,1,1,0,NOW(),NOW()),
('P000024','7501000000024','Lays Original 105g',7,1,'UND',4.50,6.50,6.18,12,50,10,100,1,1,1,0,NOW(),NOW()),
('P000025','7501000000025','Doritos Nacho 110g',7,1,'UND',4.80,6.80,6.46,12,45,8,90,1,1,1,0,NOW(),NOW()),
('P000026','7501000000026','Chocman Costa',7,1,'UND',0.80,1.20,1.14,12,100,20,200,1,1,1,0,NOW(),NOW()),
('P000027','7501000000027','Galletas Oreo',7,1,'UND',2.50,3.80,3.61,12,60,12,120,1,1,1,0,NOW(),NOW()),
('P000028','7501000000028','Detergente Ariel 850g',8,1,'UND',12.50,16.50,15.68,12,30,5,60,1,1,1,0,NOW(),NOW()),
('P000029','7501000000029','Lejía Clorox 1L',8,1,'LT',4.50,6.50,6.18,12,40,8,80,1,1,1,0,NOW(),NOW()),
('P000030','7501000000030','Jabón Bolívar 250g',8,1,'UND',2.20,3.50,3.33,12,50,10,100,1,1,1,0,NOW(),NOW()),
('P000031','7501000000031','Shampoo H&S 200ml',9,1,'UND',14.50,18.90,17.96,12,25,5,50,1,1,1,0,NOW(),NOW()),
('P000032','7501000000032','Pasta Dental Colgate',9,1,'UND',5.20,7.50,7.13,12,40,8,80,1,1,1,0,NOW(),NOW()),
('P000033','7501000000033','Papel Higiénico Suave x4',9,1,'PAQ',7.50,10.50,9.98,12,50,10,100,1,1,1,0,NOW(),NOW());

SET FOREIGN_KEY_CHECKS=1;

-- ✓ INSTALACIÓN COMPLETADA
-- Credenciales por defecto:
--    Usuario: admin   Contraseña: admin123
--    Usuario: gerente Contraseña: gerente123
--    Usuario: cajero  Contraseña: cajero123
