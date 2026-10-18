-- ============================================================================
-- ACTUALIZACIÓN DE BASE DE DATOS - VERSIÓN 3.0 (RBAC + MULTI-SUB + ESCALA 10)
-- ============================================================================

-- 1. Agregar columnas para políticas de administrador (RBAC) y categorías múltiples a usuarios
ALTER TABLE `usuarios`
  ADD COLUMN IF NOT EXISTS `telefono` VARCHAR(30) NULL AFTER `cat_label`,
  ADD COLUMN IF NOT EXISTS `bloqueado` TINYINT(1) NOT NULL DEFAULT 0 AFTER `fecha_vencimiento`,
  ADD COLUMN IF NOT EXISTS `permisos` LONGTEXT NULL AFTER `bloqueado`,
  ADD COLUMN IF NOT EXISTS `categorias` LONGTEXT NULL AFTER `permisos`,
  ADD COLUMN IF NOT EXISTS `es_top` TINYINT(1) NOT NULL DEFAULT 0 AFTER `categorias`;

-- 2. Asegurar clave única en evaluaciones para prevenir duplicados y permitir REPLACE atómico
ALTER TABLE `evaluaciones`
  ADD UNIQUE KEY IF NOT EXISTS `uq_eval` (`cat_key`, `player_id`);

-- 3. Asignar permisos globales al Administrador Principal (Super Admin ID 1)
UPDATE `usuarios`
SET `es_top` = 1, `permisos` = '["*"]'
WHERE `id` = 1 OR `usuario` = 'admin';
