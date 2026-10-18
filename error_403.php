<?php
/* ============================================================================
 * error_403.php — Manejador de Error 403 de Apache con Integración al WAF
 * ============================================================================ */
if (file_exists(__DIR__ . '/core/conexion.php')) {
    require_once __DIR__ . '/core/conexion.php';
} elseif (file_exists(__DIR__ . '/../core/conexion.php')) {
    require_once __DIR__ . '/../core/conexion.php';
}

$rawIp = $_SERVER['HTTP_CF_CONNECTING_IP'] 
      ?? $_SERVER['HTTP_X_REAL_IP'] 
      ?? $_SERVER['HTTP_X_FORWARDED_FOR'] 
      ?? $_SERVER['REMOTE_ADDR'] 
      ?? '0.0.0.0';
$ip  = trim(explode(',', $rawIp)[0]);
$uri = (string)($_SERVER['REQUEST_URI'] ?? '');

$ua  = trim((string)($_SERVER['HTTP_USER_AGENT'] ?? ''));
$motivo = 'Intento de acceso a archivo restringido o ruta protegida del servidor (' . substr(htmlspecialchars($uri, ENT_QUOTES, 'UTF-8'), 0, 50) . ')';

if (function_exists('wafBloquearDispositivo')) {
    wafBloquearDispositivo($ip, $ua, $motivo, 'APACHE_403_FORBIDDEN', 3600);
} elseif (function_exists('wafBloquearIp')) {
    wafBloquearIp($ip, $motivo, 'APACHE_403_FORBIDDEN', 3600);
}

$_SESSION = [];
if (session_status() === PHP_SESSION_ACTIVE) {
    @session_destroy();
}

$refIncidente = 'REF-WAF-' . strtoupper(substr(hash('sha256', $ip . '_' . time()), 0, 10));

if (function_exists('wafRenderizarPaginaBloqueo')) {
    wafRenderizarPaginaBloqueo($ip, $motivo, 60, $refIncidente, 1, false);
} else {
    http_response_code(403);
    echo '<!DOCTYPE html><html><body style="background:#06050b;color:#fff;font-family:sans-serif;text-align:center;padding:50px;"><h1>403 Forbidden</h1><p>Acceso bloqueado por el cortafuegos WAF de seguridad.</p></body></html>';
}
exit;
