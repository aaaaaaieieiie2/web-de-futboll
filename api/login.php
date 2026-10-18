<?php
/* api/login.php — Endpoint de Autenticación Segura con Bcrypt y Anti-Brute-Force */
require_once __DIR__ . '/../core/conexion.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$data = entrada();
$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($data['csrf_token'] ?? '');
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token) || !verificarCsrfToken($token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Token CSRF inválido o ausente']);
    exit;
}

// Honeypot anti-bots
if (!empty($data['hp'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Acceso denegado']);
    exit;
}

$usuario = sanitizarTexto($data['usuario'] ?? '');
$password = (string)($data['password'] ?? '');

$rawIp = $_SERVER['HTTP_CF_CONNECTING_IP'] 
      ?? $_SERVER['HTTP_X_REAL_IP'] 
      ?? $_SERVER['HTTP_X_FORWARDED_FOR'] 
      ?? $_SERVER['REMOTE_ADDR'] 
      ?? '0.0.0.0';
if (strpos($rawIp, ',') !== false) {
    $ip = trim(explode(',', $rawIp)[0]);
} else {
    $ip = trim($rawIp);
}

$rlDir = __DIR__ . '/../uploads/ratelimit';
if (!is_dir($rlDir)) {
    @mkdir($rlDir, 0750, true);
}
// Clave combinada IP + usuario para no bloquear a toda una red ni proxies de Cloudflare
$keyUser = strtolower(trim($usuario));
$rlFile = $rlDir . '/login_' . md5($ip . '_' . $keyUser) . '.json';
$ahora = time();
$maxIntentos = 5;
$ventanaSegundos = 60;

$rlData = ['intentos' => 0, 'expira' => $ahora + $ventanaSegundos];
if (is_file($rlFile)) {
    $contenido = @file_get_contents($rlFile);
    $dec = $contenido ? json_decode($contenido, true) : null;
    if (is_array($dec) && isset($dec['expira'], $dec['intentos'])) {
        if ($ahora < $dec['expira']) {
            $rlData = $dec;
        }
    }
}

if ($rlData['intentos'] >= $maxIntentos) {
    if (function_exists('wafBloquearIp')) {
        wafBloquearIp($ipVisitante, 'BRUTE_FORCE_LOGIN', 'Fuerza bruta: 5 intentos fallidos de inicio de sesión', 3600);
    }
    $restante = max(1, $rlData['expira'] - $ahora);
    http_response_code(429);
    header('Retry-After: ' . $restante);
    echo json_encode([
        'success' => false,
        'error'   => "Demasiados intentos fallidos. Tu dirección IP ($ipVisitante) ha sido bloqueada por el firewall de seguridad."
    ]);
    exit;
}

if ($usuario === '' || $password === '') {
    http_response_code(401);
    echo json_encode(['error' => 'Credenciales inválidas']);
    exit;
}

$r = buscarUsuarioGlobalPorLogin($usuario);

$hashFicticio = '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG';
$hashReal = $r ? (string)$r['password'] : $hashFicticio;
$credencialesValidas = password_verify($password, $hashReal) && ($r !== null);

if ($credencialesValidas) {
    if (is_file($rlFile)) {
        @unlink($rlFile);
    }

    if (isset($r['bloqueado']) && (int)$r['bloqueado'] === 1) {
        http_response_code(403);
        echo json_encode([
            'success'   => false,
            'bloqueado' => true,
            'error'     => 'Cuenta suspendida'
        ]);
        exit;
    }

    if (isset($r['rol']) && $r['rol'] === 'soporte') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error'   => 'Las credenciales de Soporte Técnico son exclusivas del portal /soporte.php.'
        ]);
        exit;
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        @session_regenerate_id(true);
        if (function_exists('asegurarSesionSegura')) {
            asegurarSesionSegura();
        }
    }

    $_SESSION['ns_rol']     = $r['rol'];
    $_SESSION['ns_user_id'] = (int)$r['id'];
    $_SESSION['ns_nombre']  = $r['nombre'];
    $_SESSION['ns_cat_key'] = $r['cat_key'];
    $_SESSION['csrf_token'] = firmarToken(bin2hex(random_bytes(16)));
    $csrfToken = $_SESSION['csrf_token'];
    @session_write_close();

    $userObj = [
        'id'               => (int)$r['id'],
        'usuario'          => htmlspecialchars((string)$r['usuario'], ENT_QUOTES, 'UTF-8'),
        'rol'              => $r['rol'],
        'nombre'           => htmlspecialchars((string)$r['nombre'], ENT_QUOTES, 'UTF-8'),
        'hijoCat'          => $r['hijo_cat'],
        'hijoId'           => (int)$r['hijo_id'],
        'catKey'           => $r['cat_key'],
        'catLabel'         => $r['cat_label'],
        'telefono'         => $r['telefono'],
        'hijos'            => $r['hijos'] ?? [],
        'metodoPago'       => $r['metodo_pago'] ?: 'linea',
        'fechaVencimiento' => $r['fecha_vencimiento']
    ];

    echo json_encode([
        'success'    => true,
        'usuario'    => $userObj,
        'csrf_token' => $csrfToken
    ]);
    exit;
}

$rlData['intentos']++;
$rlData['expira'] = $ahora + $ventanaSegundos;
@file_put_contents($rlFile, json_encode($rlData), LOCK_EX);

http_response_code(401);
echo json_encode(['error' => 'Credenciales inválidas']);