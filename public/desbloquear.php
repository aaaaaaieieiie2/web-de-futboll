<?php
// public/desbloquear.php — Script independiente de desbloqueo total
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

$wafDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ns_waf_state';
$borrados = 0;
if (is_dir($wafDir)) {
    foreach (glob($wafDir . DIRECTORY_SEPARATOR . '*.json') as $f) {
        if (@unlink($f)) $borrados++;
    }
}

$rlDir = __DIR__ . '/../uploads/ratelimit';
if (is_dir($rlDir)) {
    foreach (glob($rlDir . DIRECTORY_SEPARATOR . '*.json') as $f) {
        @unlink($f);
    }
}

$wafFile = __DIR__ . '/../uploads/waf_blocked.json';
if (is_file($wafFile)) {
    @file_put_contents($wafFile, '[]', LOCK_EX);
}

if (!empty($_COOKIE['ns_waf_lock'])) {
    setcookie('ns_waf_lock', '', ['expires' => time() - 3600, 'path' => '/']);
}

echo json_encode([
    'ok' => true,
    'mensaje' => "Se eliminaron $borrados bloqueos de IP en el servidor. Todas las IPs quedan 100% desbloqueadas.",
    'hora' => date('Y-m-d H:i:s')
], JSON_UNESCAPED_UNICODE);
