<?php
require __DIR__ . '/../core/conexion.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['ns_user_id'])) {
    echo json_encode(['success' => false, 'error' => 'No session']);
    exit;
}

$userId = (int)$_SESSION['ns_user_id'];
$sql = "SELECT * FROM usuarios WHERE id = $userId";
$result = consultar($sql);

if (count($result) > 0) {
    $r = $result[0];
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
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    echo json_encode(['success' => true, 'usuario' => $userObj, 'csrf_token' => $_SESSION['csrf_token']]);
} else {
    echo json_encode(['success' => false, 'error' => 'User not found']);
}
