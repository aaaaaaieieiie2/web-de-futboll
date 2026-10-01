-- Parche SQL para sincronizar la base de datos de Nico Sport
-- Ejecutar en phpMyAdmin

-- 1. Agregar campos de teléfono y control de pagos a la tabla de usuarios (Familias)
ALTER TABLE `usuarios` ADD COLUMN IF NOT EXISTS `telefono` VARCHAR(20) NULL DEFAULT NULL;
ALTER TABLE `usuarios` ADD COLUMN IF NOT EXISTS `metodo_pago` VARCHAR(20) NULL DEFAULT 'linea';
ALTER TABLE `usuarios` ADD COLUMN IF NOT EXISTS `fecha_vencimiento` VARCHAR(12) NULL DEFAULT NULL;

-- (Nota: Si el servidor de phpMyAdmin es muy antiguo y da error de sintaxis por "IF NOT EXISTS", 
-- simplemente borra las palabras "IF NOT EXISTS" de las sentencias anteriores).

-- 2. Asegurar que las tablas de pagos existan (por si hubo algún problema previo en el volcado)
CREATE TABLE IF NOT EXISTS `comprobantes` (
  `id` bigint(20) NOT NULL PRIMARY KEY,
  `padre_id` int(11) DEFAULT NULL,
  `padre_nombre` varchar(150) DEFAULT NULL,
  `concepto` varchar(80) DEFAULT NULL,
  `monto` decimal(10,2) DEFAULT NULL,
  `fecha` varchar(12) DEFAULT NULL,
  `referencia` varchar(150) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `comprobante` longtext DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'en_revision'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pagos_club` (
  `id` bigint(20) NOT NULL PRIMARY KEY,
  `padre_id` int(11) DEFAULT NULL,
  `padre_nombre` varchar(150) DEFAULT NULL,
  `concepto` varchar(80) DEFAULT NULL,
  `monto` decimal(10,2) DEFAULT NULL,
  `fecha` varchar(12) DEFAULT NULL,
  `referencia` varchar(150) DEFAULT NULL,
  `metodo` varchar(20) DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pagos_padre` (
  `id` bigint(20) NOT NULL PRIMARY KEY,
  `padre_id` int(11) DEFAULT NULL,
  `concepto` varchar(80) DEFAULT NULL,
  `monto` decimal(10,2) DEFAULT NULL,
  `fecha` varchar(12) DEFAULT NULL,
  `referencia` varchar(150) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `comprobante` longtext DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabla de Tarifas (Opcional, ya que el sistema actualmente lee de config_tarifas.json para mayor velocidad y menor latencia, pero se crea por seguridad estructural)
CREATE TABLE IF NOT EXISTS `tarifas` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `mensualidad` decimal(10,2) DEFAULT 15.00,
  `inscripcion` decimal(10,2) DEFAULT 15.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

