<?php
/* api/cron_comprobantes.php — Limpieza de comprobantes de más de 30 días (Restringido a CLI o Admin) */
require_once __DIR__ . '/../core/conexion.php';
header('Content-Type: application/json; charset=utf-8');

$esCli = (php_sapi_name() === 'cli');
$esAdmin = (isset($_SESSION['ns_rol']) && $_SESSION['ns_rol'] === 'admin');

if (!$esCli && !$esAdmin) {
    http_response_code(403);
    echo json_encode(['error' => 'HTTP 403 Forbidden - Acceso restringido']);
    exit;
}

$dir = __DIR__ . '/../uploads/comprobantes/';
$deleted = 0;

if (is_dir($dir)) {
    $files = glob($dir . 'comp_*.*') ?: [];
    $now = time();
    $days30 = 30 * 24 * 60 * 60;

    foreach ($files as $file) {
        if (is_file($file) && ($now - filemtime($file) >= $days30)) {
            if (@unlink($file)) {
                $deleted++;
            }
        }
    }
}

echo json_encode(['ok' => true, 'deleted_files' => $deleted]);