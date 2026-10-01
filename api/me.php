<?php
/* api/me.php — Devuelve el usuario de la sesión activa + token CSRF */
require __DIR__ . '/../core/conexion.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['ns_user_id'])) {
    echo json_encode(['success' => false, 'error' => 'No session']);
    exit;
}

$userId = (int)$_SESSION['ns_user_id'];
$result = q("SELECT * FROM usuarios WHERE id = ?", "i", [$userId]);

if (count($result) > 0) {
    $r = $result[0];
    // Si el usuario fue bloqueado mientras tenía sesión, cerrarla
    if (!empty($r['bloqueado'])) {
        $_SESSION = [];
        session_destroy();
        echo json_encode(['success' => false, 'error' => 'Cuenta bloqueada']);
        exit;
    }
    $userObj = [
        'id' => (int)$r['id'],
        'usuario' => $r['usuario'],
        'rol' => $r['rol'],
        'nombre' => $r['nombre'],
        'hijoCat' => $r['hijo_cat'],
        'hijoId' => (int)$r['hijo_id'],
        'catKey' => $r['cat_key'],
        'catLabel' => $r['cat_label'],
        'telefono' => $r['telefono'],
        'metodoPago' => $r['metodo_pago'] ?: 'linea',
        'fechaVencimiento' => $r['fecha_vencimiento']
    ];
    echo json_encode(['success' => true, 'usuario' => $userObj, 'csrf_token' => csrf_token()]);
} else {
    // Sesión huérfana (usuario eliminado): destruirla
    $_SESSION = [];
    session_destroy();
    echo json_encode(['success' => false, 'error' => 'User not found']);
}
