<?php
/* api/me.php — Validador continuo de sesión y bloqueo en caliente (PDO + OWASP) */
require_once __DIR__ . '/../core/conexion.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if (!isset($_SESSION['ns_user_id'])) {
    echo json_encode(['success' => false, 'error' => 'No session']);
    exit;
}

$userId = (int)$_SESSION['ns_user_id'];
$r = buscarUsuarioGlobalPorId($userId, $_SESSION['ns_rol'] ?? null);

if ($r) {
    if (isset($r['bloqueado']) && (int)$r['bloqueado'] === 1) {
        $_SESSION = [];
        session_destroy();
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Cuenta suspendida']);
        exit;
    }

    $_SESSION['ns_rol']     = $r['rol'];
    $_SESSION['ns_nombre']  = $r['nombre'];
    $_SESSION['ns_cat_key'] = $r['cat_key'];

    $userObj = [
        'id'               => (int)$r['id'],
        'usuario'          => htmlspecialchars((string)$r['usuario'], ENT_QUOTES, 'UTF-8'),
        'rol'              => $r['rol'],
        'nombre'           => htmlspecialchars((string)$r['nombre'], ENT_QUOTES, 'UTF-8'),
        'hijoCat'          => $r['hijo_cat'],
        'hijoId'           => (int)$r['hijo_id'],
        'catKey'           => $r['cat_key'],
        'catLabel'         => $r['cat_label'],
        'telefono'         => htmlspecialchars((string)($r['telefono'] ?? ''), ENT_QUOTES, 'UTF-8'),
        'hijos'            => $r['hijos'] ?? [],
        'metodoPago'       => $r['metodo_pago'] ?: 'linea',
        'fechaVencimiento' => $r['fecha_vencimiento']
    ];
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = firmarToken(bin2hex(random_bytes(16)));
    }
    echo json_encode(['success' => true, 'usuario' => $userObj, 'csrf_token' => $_SESSION['csrf_token']]);
} else {
    $_SESSION = [];
    session_destroy();
    echo json_encode(['success' => false, 'error' => 'User not found']);
}
