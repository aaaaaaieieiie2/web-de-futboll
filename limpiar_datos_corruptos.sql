-- ============================================================================
-- SCRIPT DE LIMPIEZA DE DATOS CORRUPTOS, MOJIBAKE Y DOBLE ESCAPADO (v133)
-- Academia de Fútbol Nico Sport (academianicosport.com)
-- ============================================================================

-- 1. Limpieza de descripciones de categorías (remover etiquetas span y estilos residuales)
UPDATE categorias 
SET descripcion = TRIM(REPLACE(REPLACE(descripcion, '</span>', ''), '<span style="background-color: rgba(8, 8, 8, 0.88);">', ''))
WHERE descripcion LIKE '%<span%';

UPDATE categorias 
SET descripcion = TRIM(REGEXP_REPLACE(descripcion, '<span[^>]*>|</span>', ''))
WHERE descripcion LIKE '%<span%';

-- 2. Limpieza de Mojibake y etiquetas span en el documento torneo
UPDATE documentos
SET value = REPLACE(REPLACE(value, 'âœ…', '✅'), '</span>', '')
WHERE doc_key = 'torneo';

UPDATE documentos
SET value = REGEXP_REPLACE(value, '<span[^>]*>', '')
WHERE doc_key = 'torneo' AND value LIKE '%<span%';

-- 3. Limpieza de staff (nombres, cargos, bios con doble escape &amp;amp; o textos de placeholder)
UPDATE staff
SET cargo = REPLACE(cargo, '&amp;amp;', '&'),
    nombre = REPLACE(nombre, '&amp;amp;', '&'),
    licencia = REPLACE(licencia, '&amp;amp;', '&'),
    especialidad = REPLACE(especialidad, '&amp;amp;', '&'),
    bio = REPLACE(REPLACE(bio, '&amp;amp;', '&'), ' (Edita este texto con el lápiz naranja)', '');

UPDATE staff
SET cargo = REPLACE(cargo, '&amp;', '&'),
    nombre = REPLACE(nombre, '&amp;', '&')
WHERE cargo LIKE '%&amp;%' OR nombre LIKE '%&amp;%';

-- 4. Limpieza de textos CMS
UPDATE textos
SET html = REPLACE(REPLACE(html, '&amp;amp;', '&'), 'âœ…', '✅')
WHERE html LIKE '%&amp;amp;%' OR html LIKE '%âœ…%';

-- 5. Actualizar versión de esquema a v133
REPLACE INTO configuraciones (clave, valor) VALUES ('schema_version', 'v133');
