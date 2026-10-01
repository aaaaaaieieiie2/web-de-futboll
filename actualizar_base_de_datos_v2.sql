-- MODO HÍBRIDO Y URLS - SCRIPT NO DESTRUCTIVO

-- 1. Adaptación de tablas para soporte de URLs largas
ALTER TABLE `staff` MODIFY `foto_icon` TEXT DEFAULT NULL;
ALTER TABLE `jugadores` MODIFY `foto` LONGTEXT DEFAULT NULL;
ALTER TABLE `categorias` ADD COLUMN IF NOT EXISTS `foto` TEXT DEFAULT NULL;

-- 2. Asegurar que la tabla textos soporte contenido ilimitado
ALTER TABLE `textos` MODIFY `html` LONGTEXT DEFAULT NULL;

-- 3. Tabla para configuraciones generales (cuota/pagos)
CREATE TABLE IF NOT EXISTS `configuraciones` (
  `clave` VARCHAR(50) NOT NULL PRIMARY KEY,
  `valor` TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- (Opcional) Migrar tarifa si existe, de lo contrario se usa la API ya construida con JSON
