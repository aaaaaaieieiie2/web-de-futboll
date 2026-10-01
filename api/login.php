<?php
require __DIR__ . '/../core/conexion.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$usuario = $data['usuario'] ?? '';
$password = $data['password'] ?? '';

// Uso seguro con prepare (Anti-SQLi)
$result = q("SELECT * FROM usuarios WHERE usuario = ? AND password = ?", "ss", [$usuario, $password]);

if (count($result) > 0) {
    $r = $result[0];
    
    // Set sessions
    $_SESSION['ns_rol'] = $r['rol'];
    $_SESSION['ns_user_id'] = $r['id'];
    $_SESSION['ns_nombre'] = $r['nombre'];
    $_SESSION['ns_cat_key'] = $r['cat_key'];
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    
    // Exact mapping that frontend expects
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
    
    echo json_encode(['success' => true, 'usuario' => $userObj, 'csrf_token' => $_SESSION['csrf_token']]);
    exit;
}

echo json_encode(['error' => 'Credenciales invalidas']);
