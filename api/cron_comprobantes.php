<?php
// Script de limpieza de comprobantes viejos
// Purga imágenes con más de 30 días de antigüedad en la carpeta
$folder = '../uploads/comprobantes/';
$files = glob($folder . '*.*');
$days = 30;
$seconds = $days * 24 * 60 * 60;
$now = time();
$deleted = 0;

foreach ($files as $file) {
    if (is_file($file)) {
        if ($now - filemtime($file) >= $seconds) {
            unlink($file);
            $deleted++;
        }
    }
}
echo "Purga completa. Archivos eliminados: " . $deleted;
?>
