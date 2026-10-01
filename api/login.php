<?php
/* api/login.php — Autenticación con bcrypt, verificación de bloqueo y anti-fuerza bruta */
require __DIR__ . '/../core/conexion.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = entrada();
$usuario = limpiar_texto($data['usuario'] ?? '', 80);
$password = (string)($data['password'] ?? ''); // NO hacer trim: espacios pueden ser parte de la contraseña

// Anti fuerza de bruta (por sesión)
$bloqueo = login_throttle_check();
if ($bloqueo !== null) {
    http_response_code(429);
    echo json_encode(['error' => "Demasiados intentos. Espera {$bloqueo} segundos."]);
    exit;
}

$result = q("SELECT * FROM usuarios WHERE usuario = ?", "s", [$usuario]);

// Si hay error interno de BD, avisar sin credenciales
if (isset($result['error'])) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno']);
    exit;
}

if (count($result) === 0) {
    login_throttle_fail();
    echo json_encode(['error' => 'Credenciales invalidas']);
    exit;
}

$r = $result[0];

// Verificar cuenta bloqueada (si la columna existe)
if (!empty($r['bloqueado'])) {
    echo json_encode(['error' => 'Cuenta bloqueada. Contacta al administrador.']);
    exit;
}

// Verificar contraseña: soporta bcrypt y migra texto plano a hash automáticamente
$passAlmacenada = (string)$r['password'];
$ok = false;
if (password_verify($password, $passAlmacenada)) {
    $ok = true;
} elseif (hash_equals($passAlmacenada, $password)) {
    // Legado en texto plano: aceptar una vez y migrar a bcrypt
    $ok = true;
    $hash = password_hash($password, PASSWORD_DEFAULT);
    q("UPDATE usuarios SET password = ? WHERE id = ?", "si", [$hash, (int)$r['id']]);
}

if (!$ok) {
    login_throttle_fail();
    echo json_encode(['error' => 'Credenciales invalidas']);
    exit;
}

// Regenerar ID de sesión para evitar fijación de sesión
session_regenerate_id(true);
login_throttle_reset();

$_SESSION['ns_rol'] = $r['rol'];
$_SESSION['ns_user_id'] = (int)$r['id'];
$_SESSION['ns_nombre'] = $r['nombre'];
$_SESSION['ns_cat_key'] = $r['cat_key'];
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

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
