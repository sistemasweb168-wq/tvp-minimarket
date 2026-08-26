-- =====================================================================
-- TPV MINIMARKET - MODULO FACTURACION ELECTRONICA SUNAT
-- Ejecutar DESPUES de install.sql
-- =====================================================================

USE `tpv_minimarket`;

SET FOREIGN_KEY_CHECKS=0;

-- Comprobantes electrónicos
DROP TABLE IF EXISTS `comprobantes_electronicos`;
CREATE TABLE `comprobantes_electronicos` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `venta_id` BIGINT UNSIGNED NULL,
  `tipo_documento` VARCHAR(2) NOT NULL,
  `serie` VARCHAR(4) NOT NULL,
  `numero` VARCHAR(8) NOT NULL,
  `numero_completo` VARCHAR(20) NOT NULL UNIQUE,
  `emisor_ruc` VARCHAR(15) NOT NULL,
  `emisor_razon_social` VARCHAR(255) NOT NULL,
  `receptor_tipo_doc` VARCHAR(2) NOT NULL,
  `receptor_numero_doc` VARCHAR(20) NOT NULL,
  `receptor_razon_social` VARCHAR(255) NOT NULL,
  `receptor_direccion` VARCHAR(255) NULL,
  `receptor_email` VARCHAR(255) NULL,
  `fecha_emision` DATE NOT NULL,
  `hora_emision` TIME NOT NULL,
  `fecha_vencimiento` DATE NULL,
  `moneda` VARCHAR(3) DEFAULT 'PEN',
  `total_gravadas` DECIMAL(12,2) DEFAULT 0,
  `total_exoneradas` DECIMAL(12,2) DEFAULT 0,
  `total_inafectas` DECIMAL(12,2) DEFAULT 0,
  `total_gratuitas` DECIMAL(12,2) DEFAULT 0,
  `total_igv` DECIMAL(12,2) DEFAULT 0,
  `total_isc` DECIMAL(12,2) DEFAULT 0,
  `total_descuentos` DECIMAL(12,2) DEFAULT 0,
  `importe_total` DECIMAL(12,2) NOT NULL,
  `importe_letras` VARCHAR(255) NOT NULL,
  `doc_referencia_tipo` VARCHAR(2) NULL,
  `doc_referencia_serie_numero` VARCHAR(20) NULL,
  `motivo_referencia` VARCHAR(255) NULL,
  `codigo_motivo_nc` VARCHAR(3) NULL,
  `estado_sunat` ENUM('pendiente','enviado','aceptado','rechazado','observado','anulado','baja','excepcion') DEFAULT 'pendiente',
  `codigo_respuesta_sunat` VARCHAR(10) NULL,
  `mensaje_sunat` TEXT NULL,
  `hash` VARCHAR(255) NULL,
  `xml_path` VARCHAR(255) NULL,
  `cdr_path` VARCHAR(255) NULL,
  `pdf_path` VARCHAR(255) NULL,
  `qr_data` VARCHAR(500) NULL,
  `fecha_envio_sunat` TIMESTAMP NULL,
  `intentos_envio` INT DEFAULT 0,
  `user_id` BIGINT UNSIGNED NULL,
  `observaciones` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX (`tipo_documento`, `serie`, `numero`),
  INDEX (`estado_sunat`),
  INDEX (`fecha_emision`),
  FOREIGN KEY (`venta_id`) REFERENCES `ventas`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Series de documentos
DROP TABLE IF EXISTS `series_documentos`;
CREATE TABLE `series_documentos` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tipo_documento` VARCHAR(2) NOT NULL,
  `serie` VARCHAR(4) NOT NULL,
  `correlativo_actual` INT DEFAULT 0,
  `correlativo_max` INT DEFAULT 99999999,
  `activo` TINYINT(1) DEFAULT 1,
  `descripcion` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_serie` (`tipo_documento`, `serie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Resúmenes diarios
DROP TABLE IF EXISTS `resumenes_diarios`;
CREATE TABLE `resumenes_diarios` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `identificador` VARCHAR(20) NOT NULL UNIQUE,
  `fecha_resumen` DATE NOT NULL,
  `fecha_generacion` DATE NOT NULL,
  `cantidad_comprobantes` INT DEFAULT 0,
  `total_general` DECIMAL(12,2) DEFAULT 0,
  `estado_sunat` ENUM('pendiente','enviado','aceptado','rechazado') DEFAULT 'pendiente',
  `ticket_sunat` VARCHAR(100) NULL,
  `codigo_respuesta` VARCHAR(10) NULL,
  `mensaje_respuesta` TEXT NULL,
  `xml_path` VARCHAR(255) NULL,
  `cdr_path` VARCHAR(255) NULL,
  `user_id` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Comunicaciones de baja
DROP TABLE IF EXISTS `comunicaciones_baja`;
CREATE TABLE `comunicaciones_baja` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `identificador` VARCHAR(20) NOT NULL UNIQUE,
  `comprobante_id` BIGINT UNSIGNED NOT NULL,
  `fecha_generacion` DATE NOT NULL,
  `motivo` VARCHAR(255) NOT NULL,
  `estado_sunat` ENUM('pendiente','enviado','aceptado','rechazado') DEFAULT 'pendiente',
  `ticket_sunat` VARCHAR(100) NULL,
  `codigo_respuesta` VARCHAR(10) NULL,
  `mensaje_respuesta` TEXT NULL,
  `xml_path` VARCHAR(255) NULL,
  `cdr_path` VARCHAR(255) NULL,
  `user_id` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`comprobante_id`) REFERENCES `comprobantes_electronicos`(`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Agregar campos SUNAT a empresas
ALTER TABLE `empresas`
  ADD COLUMN `ubigeo` VARCHAR(6) NULL AFTER `ciudad`,
  ADD COLUMN `departamento` VARCHAR(100) NULL AFTER `ubigeo`,
  ADD COLUMN `provincia` VARCHAR(100) NULL AFTER `departamento`,
  ADD COLUMN `distrito` VARCHAR(100) NULL AFTER `provincia`,
  ADD COLUMN `codigo_pais` VARCHAR(2) DEFAULT 'PE' AFTER `distrito`,
  ADD COLUMN `sunat_modo` ENUM('beta','produccion') DEFAULT 'beta',
  ADD COLUMN `sunat_usuario_sol` VARCHAR(100) NULL,
  ADD COLUMN `sunat_clave_sol` VARCHAR(100) NULL,
  ADD COLUMN `sunat_certificado_path` VARCHAR(255) NULL,
  ADD COLUMN `sunat_certificado_password` VARCHAR(100) NULL,
  ADD COLUMN `facturacion_electronica_activa` TINYINT(1) DEFAULT 0;

-- Agregar campo a ventas para vincular CPE
ALTER TABLE `ventas`
  ADD COLUMN `comprobante_electronico_id` BIGINT UNSIGNED NULL AFTER `turno_caja_id`,
  ADD FOREIGN KEY (`comprobante_electronico_id`) REFERENCES `comprobantes_electronicos`(`id`) ON DELETE SET NULL;

-- Datos iniciales: series SUNAT por defecto
INSERT INTO `series_documentos` (`tipo_documento`, `serie`, `correlativo_actual`, `activo`, `descripcion`, `created_at`, `updated_at`) VALUES
('01', 'F001', 0, 1, 'Factura electrónica principal', NOW(), NOW()),
('03', 'B001', 0, 1, 'Boleta de venta electrónica', NOW(), NOW()),
('07', 'FC01', 0, 1, 'Nota de crédito de facturas', NOW(), NOW()),
('07', 'BC01', 0, 1, 'Nota de crédito de boletas', NOW(), NOW()),
('08', 'FD01', 0, 1, 'Nota de débito de facturas', NOW(), NOW()),
('08', 'BD01', 0, 1, 'Nota de débito de boletas', NOW(), NOW());

-- Configuración inicial de ubigeo Lima por defecto
UPDATE `empresas` SET
  `ubigeo` = '150101',
  `departamento` = 'LIMA',
  `provincia` = 'LIMA',
  `distrito` = 'LIMA'
WHERE `id` = 1;

SET FOREIGN_KEY_CHECKS=1;

-- ✓ MODULO SUNAT INSTALADO
-- Acceder a: Sistema → Config. SUNAT para configurar credenciales
