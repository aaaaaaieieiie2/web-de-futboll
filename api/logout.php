<?php
require_once __DIR__ . '/../core/conexion.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if (session_status() === PHP_SESSION_NONE) {
    if (function_exists('asegurarSesionSegura')) {
        asegurarSesionSegura();
    } else {
        @session_start();
    }
}

$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 86400, $params["path"] ?? '/', $params["domain"] ?? '', $params["secure"] ?? false, true);
    setcookie(session_name(), '', time() - 86400, '/');
}
@session_unset();
@session_destroy();

echo json_encode(['success' => true]);
