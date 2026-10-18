<?php
require_once __DIR__ . '/waf.php';
if (defined('CORE_CONEXION_CARGADA')) {
    return;
}
define('CORE_CONEXION_CARGADA', true);
/* core/conexion.php — Capa de Conexión Segura (PDO + MySQLi) y Endurecimiento OWASP */

// 0. Funciones Criptográficas Centralizadas (HMAC-SHA256 Anti-Falsificación y Anti-Replicación)
if (!function_exists('obtenerAppSecret')) {
    function obtenerAppSecret(): string {
        static $secret = null;
        if ($secret !== null) return $secret;
        $sec = getenv('APP_SECRET');
        if ($sec) { $secret = (string)$sec; return $secret; }
        $envFile = __DIR__ . '/.env';
        if (is_file($envFile) && is_readable($envFile)) {
            $lines = @file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (is_array($lines)) {
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (strpos($line, 'APP_SECRET=') === 0) {
                        $secret = trim(substr($line, 11), " \t\n\r\0\x0B\"'");
                        return $secret;
                    }
                }
            }
        }
        $secret = 'a8f9c2d1e7b4039581c629f104d88e2b5093fa716e289410cb632d4e8a15fc90';
        return $secret;
    }
}

if (!function_exists('firmarToken')) {
    function firmarToken(string $payload): string {
        $secret = obtenerAppSecret();
        $firma = hash_hmac('sha256', $payload, $secret);
        return $payload . '.' . $firma;
    }
}

if (!function_exists('verificarTokenFirmado')) {
    function verificarTokenFirmado(?string $tokenCompleto): ?string {
        if (empty($tokenCompleto) || !is_string($tokenCompleto)) return null;
        $partes = explode('.', $tokenCompleto, 2);
        if (count($partes) !== 2) return null;
        [$payload, $firmaRecibida] = $partes;
        if (!preg_match('/^[a-f0-9]{64}$/i', $firmaRecibida)) return null;
        $secret = obtenerAppSecret();
        $firmaEsperada = hash_hmac('sha256', $payload, $secret);
        if (hash_equals($firmaEsperada, $firmaRecibida)) {
            return $payload;
        }
        return null;
    }
}

if (!function_exists('firmarCodigoConfirmacion')) {
    function firmarCodigoConfirmacion(string $codigo): string {
        return hash_hmac('sha256', strtoupper(trim($codigo)), obtenerAppSecret());
    }
}

if (!function_exists('verificarCodigoConfirmacion')) {
    function verificarCodigoConfirmacion(string $codigo, string $firmaEsperada): bool {
        return hash_equals(firmarCodigoConfirmacion($codigo), $firmaEsperada);
    }
}

if (!function_exists('validarTokenConFirma')) {
    function validarTokenConFirma(string $tokenRecibido, string $tokenEsperado): bool {
        $tokenRecibido = trim($tokenRecibido);
        $tokenEsperado = trim($tokenEsperado);
        if ($tokenRecibido === '' || $tokenEsperado === '') return false;

        if (strpos($tokenRecibido, '.') !== false) {
            $payloadRecibido = verificarTokenFirmado($tokenRecibido);
            if ($payloadRecibido === null) {
                return false;
            }
            $payloadEsperado = (strpos($tokenEsperado, '.') !== false) ? explode('.', $tokenEsperado, 2)[0] : $tokenEsperado;
            return hash_equals($payloadEsperado, $payloadRecibido);
        }

        if (strpos($tokenEsperado, '.') !== false) {
            $payloadEsperado = explode('.', $tokenEsperado, 2)[0];
            return hash_equals($payloadEsperado, $tokenRecibido);
        }

        return hash_equals($tokenEsperado, $tokenRecibido);
    }
}

if (!function_exists('obtenerOGenerarTokenCiclo7Dias')) {
    function obtenerOGenerarTokenCiclo7Dias(bool $forzarNuevo = false): array {
        $ahora = time();
        $doc = consultar("SELECT `value` FROM documentos WHERE doc_key = 'registro_link_temp' LIMIT 1");
        $linkInfo = !empty($doc[0]['value']) ? json_decode($doc[0]['value'], true) : null;

        $expirado = !is_array($linkInfo)
            || empty($linkInfo['activo'])
            || empty($linkInfo['codigoAcademia'])
            || $ahora >= (int)($linkInfo['expira'] ?? 0);

        if ($forzarNuevo || $expirado) {
            $codigoAcademia = 'NS-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
            $codigoFirma    = firmarCodigoConfirmacion($codigoAcademia);
            $tokenRaw       = bin2hex(random_bytes(16));
            $tokenFirmado   = firmarToken($tokenRaw);
            $duracionSeg    = 7 * 86400; // 7 días exactos
            $expira         = $ahora + $duracionSeg;
            $linkInfo = [
                'token'          => $tokenFirmado,
                'tokenRaw'       => $tokenRaw,
                'codigoAcademia' => $codigoAcademia,
                'codigoFirma'    => $codigoFirma,
                'horas'          => 168,
                'creado'         => $ahora,
                'expira'         => $expira,
                'expiraStr'      => date('d/m/Y h:i A', $expira),
                'activo'         => true,
                'cicloDias'      => 7
            ];
            q("REPLACE INTO documentos (doc_key, `value`) VALUES ('registro_link_temp', ?)", 's', [json_encode($linkInfo, JSON_UNESCAPED_UNICODE)]);
        }
        return $linkInfo;
    }
}

if (!function_exists('asegurarUsuarioSoporte')) {
    function asegurarUsuarioSoporte(): array {
        $usr = q("SELECT id, usuario, rol, nombre FROM usuarios WHERE rol = 'soporte' LIMIT 1");
        $usuarioSoporte = 'soporte.tecnico.ans';
        $passSoporte    = 'ANS#S0p0rt3!Sec2026$X9';
        $hash           = password_hash($passSoporte, PASSWORD_BCRYPT);

        if (empty($usr)) {
            // Crear usuario técnico especializado y contraseña robusta compleja
            q("INSERT INTO usuarios (usuario, password, rol, nombre) VALUES (?, ?, 'soporte', 'Soporte Técnico Especializado')",
              'ss', [$usuarioSoporte, $hash]);
            $usr = q("SELECT id, usuario, rol, nombre FROM usuarios WHERE rol = 'soporte' LIMIT 1");
        } elseif ($usr[0]['usuario'] === 'soporte') {
            // Migrar usuario básico inicial 'soporte' al nuevo usuario profesional
            q("UPDATE usuarios SET usuario = ?, password = ?, nombre = 'Soporte Técnico Especializado' WHERE id = ? AND rol = 'soporte'",
              'ssi', [$usuarioSoporte, $hash, (int)$usr[0]['id']]);
            $usr = q("SELECT id, usuario, rol, nombre FROM usuarios WHERE rol = 'soporte' LIMIT 1");
        }
        return $usr[0] ?? [];
    }
}

if (!function_exists('puedeCambiarNombreUsuario')) {
    function puedeCambiarNombreUsuario(string $rol): array {
        $clave = ($rol === 'soporte') ? 'ultimo_cambio_usuario_soporte' : 'ultimo_cambio_usuario_admin';
        $row = q("SELECT valor FROM configuraciones WHERE clave = ? LIMIT 1", 's', [$clave]);
        if (empty($row) || empty($row[0]['valor'])) {
            return ['puede' => true, 'diasRestantes' => 0];
        }
        $ultimoCambio = (int)$row[0]['valor'];
        $segundosTranscurridos = time() - $ultimoCambio;
        $segundosCooldown = 7 * 86400; // 7 días exactos

        if ($segundosTranscurridos < $segundosCooldown) {
            $diasRestantes = max(1, (int)ceil(($segundosCooldown - $segundosTranscurridos) / 86400));
            return ['puede' => false, 'diasRestantes' => $diasRestantes];
        }
        return ['puede' => true, 'diasRestantes' => 0];
    }
}

if (!function_exists('registrarCambioNombreUsuario')) {
    function registrarCambioNombreUsuario(string $rol): void {
        $clave = ($rol === 'soporte') ? 'ultimo_cambio_usuario_soporte' : 'ultimo_cambio_usuario_admin';
        q("REPLACE INTO configuraciones (clave, valor) VALUES (?, ?)", 'ss', [$clave, (string)time()]);
    }
}

if (!function_exists('verificarCsrfToken')) {
    function verificarCsrfToken(?string $token): bool {
        if (empty($token) || empty($_SESSION['csrf_token'])) return false;
        if (!hash_equals($_SESSION['csrf_token'], $token)) return false;
        if (strpos($token, '.') !== false) {
            return verificarTokenFirmado($token) !== null;
        }
        return true;
    }
}

// 1. Endurecimiento de Sesión (OWASP A07:2021 - Identification and Authentication Failures)
if (!function_exists('asegurarSesionSegura')) {
    function asegurarSesionSegura(): void {
        if (session_status() === PHP_SESSION_NONE) {
            $esHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                || (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'domain'   => '',
                'secure'   => $esHttps,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
    }
}
asegurarSesionSegura();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = firmarToken(bin2hex(random_bytes(16)));
}
if (empty($_SESSION['csp_nonce'])) {
    $_SESSION['csp_nonce'] = bin2hex(random_bytes(16));
}
$cspNonce = $_SESSION['csp_nonce'];

// 2. Cabeceras de Seguridad Nativas y CORS Restringido (OWASP A05:2021 - Security Misconfiguration)
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=()');
    header("Content-Security-Policy: default-src 'self' https: data: blob:; script-src 'self' 'nonce-{$cspNonce}' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://static.cloudflareinsights.com 'unsafe-eval'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data: blob: https:; media-src 'self' data: blob: https:; connect-src 'self' https:; frame-src 'self' https://www.youtube.com https://youtube.com; frame-ancestors 'self'; object-src 'none'; base-uri 'self'; form-action 'self'");

    $hostActual = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $origenesPermitidos = [
        'http://localhost',
        'https://localhost',
        'http://127.0.0.1',
        'http://' . $hostActual,
        'https://' . $hostActual,
        'https://academianicosport.com',
        'https://www.academianicosport.com'
    ];
    $origen = $_SERVER['HTTP_ORIGIN'] ?? '';
    if ($origen && in_array($origen, $origenesPermitidos, true)) {
        header("Access-Control-Allow-Origin: $origen");
        header('Access-Control-Allow-Credentials: true');
        header('Vary: Origin');
    }
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN');
}

// 3. Credenciales Centralizadas (Lee core/.env y variables de entorno en VPS/Docker)
if (!function_exists('cargarCredenciales')) {
function cargarCredenciales(): array {
    $creds = [
        'DB_HOST' => getenv('DB_HOST') ?: 'localhost',
        'DB_USER' => getenv('DB_USER') ?: 'root',
        'DB_PASS' => getenv('DB_PASS') !== false ? getenv('DB_PASS') : '',
        'DB_NAME' => getenv('DB_NAME') ?: 'nicosport',
        'APP_SECRET' => getenv('APP_SECRET') ?: 'a8f9c2d1e7b4039581c629f104d88e2b5093fa716e289410cb632d4e8a15fc90'
    ];
    $envFile = __DIR__ . '/.env';
    if (is_file($envFile) && is_readable($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (is_array($lines)) {
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === '#') continue;
                $parts = explode('=', $line, 2);
                if (count($parts) === 2) {
                    $k = trim($parts[0]);
                    $v = trim($parts[1], " \t\n\r\0\x0B\"'");
                    if (array_key_exists($k, $creds)) {
                        $creds[$k] = $v;
                    }
                }
            }
        }
    }
    return $creds;
}
}

global $pdo, $mysqli;
$creds = cargarCredenciales();

// 4. Conexión PDO con Prepared Statements Nativos (OWASP A03:2021 - Injection)
$pdo = null;
try {
    $dsn = "mysql:host={$creds['DB_HOST']};dbname={$creds['DB_NAME']};charset=utf8mb4";
    $pdo = new PDO($dsn, $creds['DB_USER'], $creds['DB_PASS'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    if ($creds['DB_HOST'] === 'localhost' && $creds['DB_USER'] !== 'root' && empty($creds['DB_PASS'])) {
        try {
            $dsn = "mysql:host=localhost;dbname={$creds['DB_NAME']};charset=utf8mb4";
            $pdo = new PDO($dsn, 'root', '', [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
            $creds['DB_USER'] = 'root';
            $creds['DB_PASS'] = '';
        } catch (PDOException $e2) {
            error_log('[DB PDO Error] ' . $e->getMessage());
            http_response_code(500);
            die(json_encode(['error' => 'Error interno de conexión a la base de datos']));
        }
    } else {
        error_log('[DB PDO Error] ' . $e->getMessage());
        http_response_code(500);
        die(json_encode(['error' => 'Error interno de conexión a la base de datos']));
    }
}

// Conexión MySQLi (Compatibilidad con módulos existentes)
mysqli_report(MYSQLI_REPORT_OFF);
$mysqli = @new mysqli($creds['DB_HOST'], $creds['DB_USER'], $creds['DB_PASS'], $creds['DB_NAME']);
if ($mysqli->connect_error && $creds['DB_HOST'] === 'localhost' && $creds['DB_USER'] !== 'root' && empty($creds['DB_PASS'])) {
    $mysqli = @new mysqli('localhost', 'root', '', $creds['DB_NAME']);
}
if ($mysqli->connect_error) {
    error_log('[DB MySQLi Error] ' . $mysqli->connect_error);
    http_response_code(500);
    die(json_encode(['error' => 'Error interno de conexión a la base de datos']));
}
$mysqli->set_charset("utf8mb4");
$GLOBALS['pdo'] = $pdo;
$GLOBALS['mysqli'] = $mysqli;
asegurarTablasGlobales();

// 5. Funciones Helper y Sanitización XSS
function pdo(): PDO {
    global $pdo;
    return $pdo;
}

function db(): mysqli {
    global $mysqli;
    return $mysqli;
}

function esAdminGlobal(): bool {
    return (isset($_SESSION['ns_rol']) && $_SESSION['ns_rol'] === 'admin');
}

function esEntrenadorGlobal(): bool {
    return (isset($_SESSION['ns_rol']) && $_SESSION['ns_rol'] === 'entrenador');
}

function entrada(): array {
    $raw = file_get_contents('php://input');
    if (!$raw) return [];
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function salida($data, int $httpCode = 200): void {
    if (!headers_sent() && $httpCode !== 200) {
        http_response_code($httpCode);
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function decodificarEntidadesRecursivas(?string $str): string {
    if ($str === null || $str === '') return '';
    $val = trim($str);
    for ($i = 0; $i < 12; $i++) {
        if (strpos($val, '&amp;') === false && strpos($val, '&lt;') === false && strpos($val, '&gt;') === false && strpos($val, '&quot;') === false && strpos($val, '&#039;') === false && strpos($val, '&nbsp;') === false) {
            break;
        }
        $prev = $val;
        $val = html_entity_decode($val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($val === $prev) break;
    }
    $val = str_replace('&amp;', '&', $val);
    return trim(strip_tags($val));
}

function sanitizarTexto(?string $str): string {
    return decodificarEntidadesRecursivas($str);
}

function sanitizarComprobanteUrl(?string $val): string {
    if ($val === null || $val === '') return '';
    $val = trim($val);
    if (preg_match('#^data:image/(jpeg|jpg|png|webp);base64,[A-Za-z0-9+/=\s]+$#i', $val)) {
        return $val;
    }
    if (preg_match('#^(uploads/comprobantes/|img/)[A-Za-z0-9_.\-/]+$#', $val) && strpos($val, '..') === false) {
        return $val;
    }
    return '';
}

function sanitizarHtmlSeguro(?string $html): string {
    if ($html === null || $html === '') return '';
    $limpio = preg_replace('#<(script|iframe|object|embed|applet|meta|link|style|form|base)[^>]*>.*?</\1>#is', '', $html);
    $limpio = preg_replace('#<(script|iframe|object|embed|applet|meta|link|style|form|base)[^>]*>#is', '', $limpio);
    $limpio = preg_replace('/\s+on[a-z]+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $limpio);
    $limpio = preg_replace('/(?:javascript|vbscript|data\s*:\s*text\/html)\s*:/i', 'bloqueado:', $limpio);
    return trim($limpio);
}

function q(string $sql, string $tipos = '', array $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_values($params));
        if (stripos(ltrim($sql), 'SELECT') === 0) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return [
            'insert_id'     => (int)$pdo->lastInsertId(),
            'affected_rows' => $stmt->rowCount()
        ];
    } catch (PDOException $e) {
        error_log('[SQL Error] ' . $e->getMessage() . ' | Query: ' . $sql);
        return ['error' => 'Error al procesar la operación en la base de datos'];
    }
}

function consultar(string $sql): array {
    global $pdo;
    try {
        $stmt = $pdo->query($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    } catch (PDOException $e) {
        error_log('[SQL Query Error] ' . $e->getMessage());
        return [];
    }
}

// 7. Organización de Tablas Separadas por Categoría (Sub) para Jugadores, Stats y Padres + Pagos Día 30
function slugTablaCat(string $catKey): string {
    $clean = strtolower((string)preg_replace('/[^a-zA-Z0-9_]/', '', $catKey));
    return $clean !== '' ? $clean : 'sub6';
}

function calcularFechaVencimiento30(?string $fechaActual = null, bool $avanzarMes = false): string {
    $hoy = new DateTime('now');
    if ($avanzarMes) {
        $anio = (int)$hoy->format('Y');
        $mes = (int)$hoy->format('n') + 1;
        if ($mes > 12) { $mes = 1; $anio++; }
        $maxDia = (int)date('t', mktime(0, 0, 0, $mes, 1, $anio));
        $dia = min(30, $maxDia);
        return sprintf('%04d-%02d-%02d', $anio, $mes, $dia);
    }
    if ($fechaActual !== null && trim($fechaActual) !== '') {
        $f = trim($fechaActual);
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $f, $m)) {
            $anio = (int)$m[1];
            $mes = (int)$m[2];
            $maxDia = (int)date('t', mktime(0, 0, 0, $mes, 1, $anio));
            $dia = min(30, $maxDia);
            return sprintf('%04d-%02d-%02d', $anio, $mes, $dia);
        }
    }
    $anio = (int)$hoy->format('Y');
    $mes = (int)$hoy->format('n');
    $diaHoy = (int)$hoy->format('j');
    if ($diaHoy >= 30) {
        $mes++;
        if ($mes > 12) { $mes = 1; $anio++; }
    }
    $maxDia = (int)date('t', mktime(0, 0, 0, $mes, 1, $anio));
    $dia = min(30, $maxDia);
    return sprintf('%04d-%02d-%02d', $anio, $mes, $dia);
}

function obtenerMapaLabelsCategorias(): array {
    $mapa = [
        'sub6'     => 'Sub 6',
        'sub12'    => 'Sub 12',
        'sub16'    => 'Sub 16',
        'submayor' => 'Sub Mayor'
    ];
    try {
        foreach (consultar("SELECT cat_key, label FROM categorias ORDER BY orden, id") as $r) {
            $k = slugTablaCat((string)($r['cat_key'] ?? ''));
            if ($k !== '') {
                $mapa[$k] = trim((string)($r['label'] ?? '')) ?: strtoupper($k);
            }
        }
    } catch (Exception $e) {}
    return $mapa;
}

function validarPasswordSegura12(string $pass): ?string {
    if (strlen($pass) < 12) {
        return 'Por seguridad, la contraseña debe tener al menos 12 caracteres.';
    }
    if (!preg_match('/[a-zA-Z]/', $pass) || !preg_match('/[0-9]/', $pass)) {
        return 'La contraseña debe combinar letras y números (mínimo 12 caracteres).';
    }
    return null;
}

function calcularSubPorEdad(int $edad): string {
    $claves = obtenerCategoriasClaves();
    $subsNumericas = [];
    foreach ($claves as $k) {
        if (preg_match('/^sub_?(\d+)$/i', $k, $m)) {
            $subsNumericas[(int)$m[1]] = $k;
        }
    }
    if (!empty($subsNumericas)) {
        ksort($subsNumericas, SORT_NUMERIC);
        foreach ($subsNumericas as $limiteEdad => $catKey) {
            if ($edad <= $limiteEdad) {
                return $catKey;
            }
        }
    }
    if (in_array('submayor', $claves, true)) {
        return 'submayor';
    }
    return !empty($claves) ? end($claves) : 'sub6';
}

function contieneAtaqueInyeccion(string $valor): bool {
    if ($valor === '') return false;
    $patronesPeligrosos = [
        '/(\b(SELECT|UNION|INSERT|UPDATE|DELETE|DROP|ALTER|CREATE|TRUNCATE|EXEC|DECLARE|CAST|CONVERT|SLEEP|BENCHMARK|INFORMATION_SCHEMA)\b)/i',
        '/(\b(OR|AND)\b\s+[\'"]?\d+[\'"]?\s*=\s*[\'"]?\d+)/i',
        '/(--|\/\*|\*\/|;|\x00|\x1a)/',
        '/(<script|<iframe|<object|<embed|<svg|javascript\s*:|vbscript\s*:|on[a-z]+\s*=)/i'
    ];
    foreach ($patronesPeligrosos as $patron) {
        if (preg_match($patron, $valor)) {
            return true;
        }
    }
    return false;
}

function obtenerCategoriasClaves(): array {
    return array_keys(obtenerMapaLabelsCategorias());
}

function asegurarTablaUsuarios(): void {
    global $pdo;
    if (!$pdo) return;
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `usuarios` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `usuario` VARCHAR(80) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `rol` VARCHAR(20) NOT NULL,
            `nombre` VARCHAR(150) NOT NULL,
            `hijo_cat` VARCHAR(40) NULL,
            `hijo_id` BIGINT NULL,
            `cat_key` VARCHAR(40) NULL,
            `cat_label` VARCHAR(80) NULL,
            `telefono` VARCHAR(30) NULL,
            `metodo_pago` VARCHAR(20) DEFAULT 'linea',
            `fecha_vencimiento` VARCHAR(15) NULL,
            `bloqueado` TINYINT(1) DEFAULT 0,
            PRIMARY KEY (`id`),
            INDEX `idx_usuario` (`usuario`),
            INDEX `idx_rol` (`rol`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $cols = $pdo->query("SHOW COLUMNS FROM `usuarios`")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('telefono', $cols, true)) {
            try { $pdo->exec("ALTER TABLE `usuarios` ADD COLUMN `telefono` VARCHAR(30) NULL AFTER `cat_label`"); } catch (Exception $e) {}
        }
        if (!in_array('bloqueado', $cols, true)) {
            try { $pdo->exec("ALTER TABLE `usuarios` ADD COLUMN `bloqueado` TINYINT(1) NOT NULL DEFAULT 0 AFTER `fecha_vencimiento`"); } catch (Exception $e) {}
        }
        if (!in_array('permisos', $cols, true)) {
            try { $pdo->exec("ALTER TABLE `usuarios` ADD COLUMN `permisos` LONGTEXT NULL AFTER `bloqueado`"); } catch (Exception $e) {}
        }
        if (!in_array('categorias', $cols, true)) {
            try { $pdo->exec("ALTER TABLE `usuarios` ADD COLUMN `categorias` LONGTEXT NULL AFTER `permisos`"); } catch (Exception $e) {}
        }
        if (!in_array('es_top', $cols, true)) {
            try { $pdo->exec("ALTER TABLE `usuarios` ADD COLUMN `es_top` TINYINT(1) NOT NULL DEFAULT 0 AFTER `categorias`"); } catch (Exception $e) {}
        }
        try {
            $pdo->exec("UPDATE `usuarios` SET `es_top` = 1, `permisos` = '[\"*\"]' WHERE `id` = 1 OR `usuario` = 'admin'");
        } catch (Exception $e) {}
    } catch (Exception $e) {}
}

function asegurarTablasGlobales(): void {
    global $pdo;
    if (!$pdo) return;
    try {
        asegurarTablaUsuarios();
        $pdo->exec("CREATE TABLE IF NOT EXISTS `evaluaciones` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `cat_key` VARCHAR(40) NOT NULL,
          `player_id` BIGINT NOT NULL,
          `fecha` VARCHAR(20) DEFAULT '',
          `tecnicos` LONGTEXT NULL,
          `tacticos` LONGTEXT NULL,
          `fisicos` LONGTEXT NULL,
          `actitudinales` LONGTEXT NULL,
          UNIQUE KEY `uq_eval` (`cat_key`, `player_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `asistencias` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `cat_key` VARCHAR(40) NOT NULL,
          `fecha` VARCHAR(12) NOT NULL,
          `player_id` BIGINT NOT NULL,
          `estado` VARCHAR(15) NOT NULL,
          UNIQUE KEY `uq_asis` (`cat_key`, `fecha`, `player_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `encuestas` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `padre` VARCHAR(150),
          `fecha` VARCHAR(20),
          `aspectos` LONGTEXT,
          `gusta_mas` LONGTEXT,
          `mejorar` LONGTEXT,
          `crecimiento` LONGTEXT,
          `crecimiento_why` LONGTEXT,
          `recomienda` LONGTEXT,
          `recomienda_why` LONGTEXT,
          `observaciones` LONGTEXT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `pagos_padres` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `padre_id` INT NOT NULL,
          `padre_nombre` VARCHAR(150) NOT NULL,
          `monto` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
          `mes` VARCHAR(20) NOT NULL,
          `metodo` VARCHAR(20) NOT NULL,
          `fecha` VARCHAR(20) NOT NULL,
          `referencia` VARCHAR(80) DEFAULT '',
          `notas` VARCHAR(255) DEFAULT '',
          INDEX `idx_padre_id` (`padre_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `documentos` (
          `doc_key` VARCHAR(60) NOT NULL PRIMARY KEY,
          `value` LONGTEXT NOT NULL,
          `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } catch (Exception $e) {}
}

function asegurarTablasCategoria(string $catKey): array {
    asegurarTablasGlobales();
    global $pdo;
    $pdoConn = $pdo;
    $slug = slugTablaCat($catKey);
    $mapaLabels = obtenerMapaLabelsCategorias();
    $labelSub = addslashes($mapaLabels[$slug] ?? strtoupper($slug));
    $vencDefault = calcularFechaVencimiento30();

    $tJug    = "jugadores_" . $slug;
    $tStats  = "player_stats_" . $slug;
    $tPadres = "padres_" . $slug;

    if ($pdoConn) {
        $pdoConn->exec("CREATE TABLE IF NOT EXISTS `$tJug` (
            `id` BIGINT NOT NULL,
            `cat_key` VARCHAR(50) NOT NULL DEFAULT '$slug',
            `name` VARCHAR(120) NOT NULL,
            `edad` INT NOT NULL DEFAULT 0,
            `dorsal` INT NOT NULL DEFAULT 0,
            `pos` VARCHAR(20) NOT NULL DEFAULT 'MED',
            `foto` TEXT NULL,
            PRIMARY KEY (`id`),
            INDEX `idx_dorsal` (`dorsal`),
            INDEX `idx_name` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        try { $pdoConn->exec("ALTER TABLE `$tJug` MODIFY `id` BIGINT NOT NULL"); } catch (Exception $e) {}
        $colEdadJ = $pdoConn->query("SHOW COLUMNS FROM `$tJug` LIKE 'edad'")->fetchAll();
        if (empty($colEdadJ)) {
            try { $pdoConn->exec("ALTER TABLE `$tJug` ADD COLUMN `edad` INT NOT NULL DEFAULT 0 AFTER `name`"); } catch (Exception $e) {}
        }

        $pdoConn->exec("CREATE TABLE IF NOT EXISTS `$tStats` (
            `player_id` BIGINT NOT NULL,
            `dorsal` INT NOT NULL DEFAULT 0,
            `nombre` VARCHAR(120) NOT NULL DEFAULT '',
            `cat_key` VARCHAR(50) NOT NULL DEFAULT '$slug',
            `goles` INT NOT NULL DEFAULT 0,
            `asistencias` INT NOT NULL DEFAULT 0,
            `mvp` INT NOT NULL DEFAULT 0,
            `updated_at` VARCHAR(40) NULL,
            PRIMARY KEY (`player_id`),
            INDEX `idx_dorsal` (`dorsal`),
            INDEX `idx_goles` (`goles`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        try { $pdoConn->exec("ALTER TABLE `$tStats` MODIFY `player_id` BIGINT NOT NULL"); } catch (Exception $e) {}

        $pdoConn->exec("CREATE TABLE IF NOT EXISTS `$tPadres` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `usuario` VARCHAR(80) NOT NULL UNIQUE,
            `nombre` VARCHAR(150) NOT NULL DEFAULT '',
            `telefono` VARCHAR(30) NOT NULL DEFAULT '',
            `sub` VARCHAR(60) NOT NULL DEFAULT '$labelSub',
            `hijo_cat` VARCHAR(50) NOT NULL DEFAULT '$slug',
            `hijo_id` BIGINT NOT NULL DEFAULT 0,
            `hijo_nombre` VARCHAR(150) NOT NULL DEFAULT '',
            `hijo_edad` INT NOT NULL DEFAULT 0,
            `hijo_dorsal` INT NOT NULL DEFAULT 0,
            `hijo_pos` VARCHAR(20) NOT NULL DEFAULT 'MED',
            `metodo_pago` VARCHAR(20) NOT NULL DEFAULT 'linea',
            `fecha_vencimiento` VARCHAR(15) NOT NULL DEFAULT '$vencDefault',
            `bloqueado` TINYINT(1) NOT NULL DEFAULT 0,
            `password` VARCHAR(255) NOT NULL,
            PRIMARY KEY (`id`),
            INDEX `idx_usuario` (`usuario`),
            INDEX `idx_hijo_id` (`hijo_id`),
            INDEX `idx_vencimiento` (`fecha_vencimiento`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $colEdadP = $pdoConn->query("SHOW COLUMNS FROM `$tPadres` LIKE 'hijo_edad'")->fetchAll();
        if (empty($colEdadP)) {
            try { $pdoConn->exec("ALTER TABLE `$tPadres` ADD COLUMN `hijo_edad` INT NOT NULL DEFAULT 0 AFTER `hijo_nombre`"); } catch (Exception $e) {}
        }
        $colHijosP = $pdoConn->query("SHOW COLUMNS FROM `$tPadres` LIKE 'hijos_json'")->fetchAll();
        if (empty($colHijosP)) {
            try { $pdoConn->exec("ALTER TABLE `$tPadres` ADD COLUMN `hijos_json` LONGTEXT NULL AFTER `hijo_pos`"); } catch (Exception $e) {}
        }
    }
    return [$tJug, $tStats, $tPadres];
}

function sincronizarStatsCategoria(string $catKey): void {
    global $pdo;
    $pdoConn = $pdo;
    if (!$pdoConn) return;
    [$tJug, $tStats, $tPadres] = asegurarTablasCategoria($catKey);
    $slug = slugTablaCat($catKey);
    $mapaLabels = obtenerMapaLabelsCategorias();
    $labelSub = $mapaLabels[$slug] ?? strtoupper($slug);
    try {
        // Sincronizar estadísticas con jugadores actuales de esta Sub
        $pdoConn->exec("DELETE FROM `$tStats` WHERE `player_id` NOT IN (SELECT `id` FROM `$tJug`)");
        $pdoConn->exec("INSERT INTO `$tStats` (`player_id`, `dorsal`, `nombre`, `cat_key`, `goles`, `asistencias`, `mvp`, `updated_at`)
                        SELECT j.`id`, j.`dorsal`, j.`name`, '$slug', 0, 0, 0, NULL FROM `$tJug` j
                        ON DUPLICATE KEY UPDATE `dorsal` = VALUES(`dorsal`), `nombre` = VALUES(`nombre`), `cat_key` = '$slug'");
        // Sincronizar datos del hijo (nombre, dorsal, pos, nombre de Sub) en la tabla de padres de esta Sub
        $stmtP = $pdoConn->prepare("UPDATE `$tPadres` p INNER JOIN `$tJug` j ON p.hijo_id = j.id
                                    SET p.hijo_nombre = j.name, p.hijo_edad = j.edad, p.hijo_dorsal = j.dorsal, p.hijo_pos = j.pos, p.sub = ?, p.hijo_cat = ?");
        $stmtP->execute([$labelSub, $slug]);
    } catch (Exception $e) {}
}

function siguienteUsuarioIdGlobal(): int {
    $max = 10;
    try {
        $rU = consultar("SELECT MAX(id) AS m FROM usuarios");
        if (!empty($rU[0]['m'])) $max = max($max, (int)$rU[0]['m']);
        foreach (obtenerCategoriasClaves() as $catKey) {
            [, , $tPadres] = asegurarTablasCategoria($catKey);
            $rP = consultar("SELECT MAX(id) AS m FROM `$tPadres`");
            if (!empty($rP[0]['m'])) $max = max($max, (int)$rP[0]['m']);
        }
    } catch (Exception $e) {}
    return $max + 1;
}

function siguienteJugadorIdGlobal(): int {
    $max = 100;
    try {
        foreach (obtenerCategoriasClaves() as $catKey) {
            $slug = slugTablaCat($catKey);
            $rJ = consultar("SELECT MAX(id) AS m FROM `jugadores_$slug`");
            if (!empty($rJ[0]['m'])) $max = max($max, (int)$rJ[0]['m']);
        }
    } catch (Exception $e) {}
    return $max + 1;
}

function normalizarFilaPadre(array $r, string $catKey): array {
    $hijosLista = !empty($r['hijos_json']) ? json_decode($r['hijos_json'], true) : [];
    if (!is_array($hijosLista)) $hijosLista = [];
    if (empty($hijosLista) && !empty($r['hijo_id'])) {
        $mapaLabels = obtenerMapaLabelsCategorias();
        $cK = (string)($r['hijo_cat'] ?? $catKey);
        $hijosLista = [
            [
                'id'       => (int)$r['hijo_id'],
                'nombre'   => (string)($r['hijo_nombre'] ?? ''),
                'edad'     => (int)($r['hijo_edad'] ?? 0),
                'catKey'   => $cK,
                'catLabel' => $mapaLabels[$cK] ?? (string)($r['sub'] ?? strtoupper($cK)),
                'dorsal'   => (int)($r['hijo_dorsal'] ?? 0),
                'pos'      => (string)($r['hijo_pos'] ?? 'MED')
            ]
        ];
    }
    return [
        'id'                => (int)$r['id'],
        'usuario'           => (string)$r['usuario'],
        'password'          => (string)$r['password'],
        'rol'               => 'padre',
        'nombre'            => (string)$r['nombre'],
        'sub'               => (string)($r['sub'] ?? ''),
        'hijo_cat'          => $catKey,
        'hijo_id'           => (int)($r['hijo_id'] ?? 0),
        'hijo_nombre'       => (string)($r['hijo_nombre'] ?? ''),
        'hijo_edad'         => (int)($r['hijo_edad'] ?? 0),
        'hijo_dorsal'       => (int)($r['hijo_dorsal'] ?? 0),
        'hijo_pos'          => (string)($r['hijo_pos'] ?? 'MED'),
        'hijos'             => $hijosLista,
        'cat_key'           => $catKey,
        'cat_label'         => (string)($r['sub'] ?? ''),
        'telefono'          => (string)($r['telefono'] ?? ''),
        'metodo_pago'       => !empty($r['metodo_pago']) ? (string)$r['metodo_pago'] : 'linea',
        'fecha_vencimiento' => calcularFechaVencimiento30($r['fecha_vencimiento'] ?? null),
        'bloqueado'         => (int)($r['bloqueado'] ?? 0)
    ];
}

function buscarUsuarioGlobalPorLogin(string $usuario): ?array {
    global $pdo;
    if (!$pdo) return null;
    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ? LIMIT 1");
        $stmt->execute([$usuario]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($r) {
            return [
                'id'                => (int)$r['id'],
                'usuario'           => (string)$r['usuario'],
                'password'          => (string)$r['password'],
                'rol'               => (string)$r['rol'],
                'nombre'            => (string)$r['nombre'],
                'hijo_cat'          => null,
                'hijo_id'           => 0,
                'cat_key'           => (string)($r['cat_key'] ?? ''),
                'cat_label'         => (string)($r['cat_label'] ?? ''),
                'telefono'          => '',
                'metodo_pago'       => 'linea',
                'fecha_vencimiento' => null,
                'bloqueado'         => (int)($r['bloqueado'] ?? 0)
            ];
        }
        $uClean = trim($usuario);
        $padreEncontrado = null;
        $todosHijos = [];
        foreach (obtenerCategoriasClaves() as $catKey) {
            [, , $tPadres] = asegurarTablasCategoria($catKey);
            $stmtP = $pdo->prepare("SELECT * FROM `$tPadres` WHERE LOWER(usuario) = LOWER(?) OR LOWER(nombre) = LOWER(?)");
            $stmtP->execute([$uClean, $uClean]);
            $filas = $stmtP->fetchAll(PDO::FETCH_ASSOC);
            foreach ($filas as $p) {
                if ($padreEncontrado === null) {
                    $padreEncontrado = normalizarFilaPadre($p, $catKey);
                }
                $normP = normalizarFilaPadre($p, $catKey);
                if (!empty($normP['hijos']) && is_array($normP['hijos'])) {
                    foreach ($normP['hijos'] as $h) {
                        $todosHijos[] = $h;
                    }
                }
            }
        }
        if ($padreEncontrado !== null) {
            $hijosDeduplicados = [];
            $vistosH = [];
            foreach ($todosHijos as $h) {
                $k = !empty($h['id']) ? ('id_' . $h['id']) : (strtolower($h['nombre'] ?? '') . '_' . ($h['catKey'] ?? ''));
                if (!isset($vistosH[$k])) {
                    $vistosH[$k] = true;
                    $hijosDeduplicados[] = $h;
                }
            }
            $padreEncontrado['hijos'] = $hijosDeduplicados;
            return $padreEncontrado;
        }
    } catch (Exception $e) {}
    return null;
}

function buscarUsuarioGlobalPorId(int $id, ?string $rolPreferido = null): ?array {
    global $pdo;
    if (!$pdo || $id <= 0) return null;
    try {
        if ($rolPreferido !== 'padre') {
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($r) {
                return [
                    'id'                => (int)$r['id'],
                    'usuario'           => (string)$r['usuario'],
                    'password'          => (string)$r['password'],
                    'rol'               => (string)$r['rol'],
                    'nombre'            => (string)$r['nombre'],
                    'hijo_cat'          => null,
                    'hijo_id'           => 0,
                    'cat_key'           => (string)($r['cat_key'] ?? ''),
                    'cat_label'         => (string)($r['cat_label'] ?? ''),
                    'telefono'          => '',
                    'metodo_pago'       => 'linea',
                    'fecha_vencimiento' => null,
                    'bloqueado'         => (int)($r['bloqueado'] ?? 0)
                ];
            }
        }
        $padreEncontrado = null;
        $usuarioNom = null;
        foreach (obtenerCategoriasClaves() as $catKey) {
            [, , $tPadres] = asegurarTablasCategoria($catKey);
            $stmtP = $pdo->prepare("SELECT * FROM `$tPadres` WHERE id = ? LIMIT 1");
            $stmtP->execute([$id]);
            $p = $stmtP->fetch(PDO::FETCH_ASSOC);
            if ($p) {
                $padreEncontrado = normalizarFilaPadre($p, $catKey);
                $usuarioNom = $p['usuario'];
                break;
            }
        }
        if ($padreEncontrado !== null) {
            $todosHijos = $padreEncontrado['hijos'] ?? [];
            if (!empty($usuarioNom)) {
                foreach (obtenerCategoriasClaves() as $catKey) {
                    [, , $tPadres] = asegurarTablasCategoria($catKey);
                    $stmtP = $pdo->prepare("SELECT * FROM `$tPadres` WHERE LOWER(usuario) = LOWER(?) AND id != ?");
                    $stmtP->execute([$usuarioNom, $id]);
                    foreach ($stmtP->fetchAll(PDO::FETCH_ASSOC) as $p) {
                        $normP = normalizarFilaPadre($p, $catKey);
                        if (!empty($normP['hijos']) && is_array($normP['hijos'])) {
                            foreach ($normP['hijos'] as $h) {
                                $todosHijos[] = $h;
                            }
                        }
                    }
                }
            }
            $hijosDeduplicados = [];
            $vistosH = [];
            foreach ($todosHijos as $h) {
                $k = !empty($h['id']) ? ('id_' . $h['id']) : (strtolower($h['nombre'] ?? '') . '_' . ($h['catKey'] ?? ''));
                if (!isset($vistosH[$k])) {
                    $vistosH[$k] = true;
                    $hijosDeduplicados[] = $h;
                }
            }
            $padreEncontrado['hijos'] = $hijosDeduplicados;
            return $padreEncontrado;
        }
    } catch (Exception $e) {}
    return null;
}

function obtenerTodosLosUsuariosUnificados(): array {
    $lista = [];
    try {
        foreach (consultar("SELECT * FROM usuarios WHERE rol != 'padre' ORDER BY id") as $r) {
            $rawCats = $r['categorias'] ?? '';
            $catsParsed = [];
            if (!empty($rawCats)) {
                $dec = json_decode($rawCats, true);
                if (is_array($dec)) {
                    $catsParsed = $dec;
                } else {
                    $catsParsed = array_filter(array_map('trim', explode(',', $rawCats)));
                }
            }
            if (empty($catsParsed) && !empty($r['cat_key'])) {
                $catsParsed = [$r['cat_key']];
            }
            $rawPerms = $r['permisos'] ?? '';
            $permsParsed = [];
            if (!empty($rawPerms)) {
                $decP = json_decode($rawPerms, true);
                if (is_array($decP)) $permsParsed = $decP;
            }
            if (empty($permsParsed) && ($r['rol'] === 'admin' && ((int)($r['es_top'] ?? 0) === 1 || (int)$r['id'] === 1 || strtolower($r['usuario']) === 'admin'))) {
                $permsParsed = ['*'];
            }

            $lista[] = [
                'id'               => (int)$r['id'],
                'usuario'          => (string)$r['usuario'],
                'rol'              => (string)$r['rol'],
                'nombre'           => (string)$r['nombre'],
                'hijoCat'          => null,
                'hijoId'           => 0,
                'catKey'           => (string)($r['cat_key'] ?? ''),
                'catLabel'         => (string)($r['cat_label'] ?? ''),
                'categorias'       => $catsParsed,
                'permisos'         => $permsParsed,
                'es_top'           => (int)($r['es_top'] ?? 0),
                'telefono'         => (string)($r['telefono'] ?? ''),
                'metodoPago'       => 'linea',
                'fechaVencimiento' => null,
                'bloqueado'        => (int)($r['bloqueado'] ?? 0)
            ];
        }

        $padresPorUsuario = [];
        foreach (obtenerCategoriasClaves() as $catKey) {
            [, , $tPadres] = asegurarTablasCategoria($catKey);
            foreach (consultar("SELECT * FROM `$tPadres` ORDER BY id") as $p) {
                $norm = normalizarFilaPadre($p, $catKey);
                $uKey = strtolower(trim((string)$norm['usuario']));
                if (!isset($padresPorUsuario[$uKey])) {
                    $padresPorUsuario[$uKey] = [
                        'id'               => $norm['id'],
                        'usuario'          => $norm['usuario'],
                        'rol'              => 'padre',
                        'nombre'           => $norm['nombre'],
                        'sub'              => $norm['sub'],
                        'hijoCat'          => $norm['hijo_cat'],
                        'hijoId'           => $norm['hijo_id'],
                        'hijoNombre'       => $norm['hijo_nombre'],
                        'hijoEdad'         => $norm['hijo_edad'],
                        'hijoDorsal'       => $norm['hijo_dorsal'],
                        'hijoPos'          => $norm['hijo_pos'],
                        'hijos'            => $norm['hijos'] ?? [],
                        'catKey'           => $norm['hijo_cat'],
                        'catLabel'         => $norm['sub'],
                        'telefono'         => $norm['telefono'],
                        'metodoPago'       => $norm['metodo_pago'],
                        'fechaVencimiento' => $norm['fecha_vencimiento'],
                        'bloqueado'        => $norm['bloqueado']
                    ];
                } else {
                    $existentes = $padresPorUsuario[$uKey]['hijos'] ?? [];
                    $nuevos = $norm['hijos'] ?? [];
                    $padresPorUsuario[$uKey]['hijos'] = array_merge($existentes, $nuevos);
                    if (empty($padresPorUsuario[$uKey]['telefono']) && !empty($norm['telefono'])) {
                        $padresPorUsuario[$uKey]['telefono'] = $norm['telefono'];
                    }
                }
            }
        }

        foreach ($padresPorUsuario as &$pad) {
            $hijosDeduplicados = [];
            $vistosH = [];
            foreach ($pad['hijos'] as $h) {
                $k = !empty($h['id']) ? ('id_' . $h['id']) : (strtolower($h['nombre'] ?? '') . '_' . ($h['catKey'] ?? ''));
                if (!isset($vistosH[$k])) {
                    $vistosH[$k] = true;
                    $hijosDeduplicados[] = $h;
                }
            }
            $pad['hijos'] = $hijosDeduplicados;
            if (!empty($hijosDeduplicados)) {
                $primerHijo = $hijosDeduplicados[0];
                $pad['hijoId']     = $primerHijo['id'] ?? $pad['hijoId'];
                $pad['hijoNombre'] = $primerHijo['nombre'] ?? $pad['hijoNombre'];
                $pad['hijoEdad']   = $primerHijo['edad'] ?? $pad['hijoEdad'];
                $pad['hijoCat']    = $primerHijo['catKey'] ?? $pad['hijoCat'];
                $pad['sub']        = $primerHijo['catLabel'] ?? $primerHijo['sub'] ?? $pad['sub'];
                $pad['catKey']     = $pad['hijoCat'];
                $pad['catLabel']   = $pad['sub'];
            }
            $lista[] = $pad;
        }
        unset($pad);

    } catch (Exception $e) {}
    return $lista;
}

function migrarYOptimizarPorSubs(): void {
    global $pdo;
    $pdoConn = $pdo;
    if (!$pdoConn) return;
    try {
        // 0. Eliminar categorías de prueba antiguas (catmuue1wnx, kkdkfd) sin duplicar basura
        $basuraCats = ['catmuue1wnx', 'kkdkfd'];
        foreach ($basuraCats as $bCat) {
            try {
                $pdoConn->exec("DROP TABLE IF EXISTS `jugadores_$bCat`");
                $pdoConn->exec("DROP TABLE IF EXISTS `player_stats_$bCat`");
                $pdoConn->exec("DROP TABLE IF EXISTS `padres_$bCat`");
                $stmtDelCat = $pdoConn->prepare("DELETE FROM `categorias` WHERE cat_key = ?");
                $stmtDelCat->execute([$bCat]);
            } catch (Exception $e) {}
        }
        // Limpiar documentos JSON ('torneo') de categorías de prueba antiguas
        try {
            $docT = consultar("SELECT `value` FROM documentos WHERE doc_key = 'torneo'");
            if (!empty($docT[0]['value'])) {
                $arrT = json_decode($docT[0]['value'], true);
                if (is_array($arrT)) {
                    $filtrado = array_values(array_filter($arrT, function($c) use ($basuraCats) {
                        return !in_array($c['key'] ?? '', $basuraCats, true);
                    }));
                    if (count($filtrado) !== count($arrT)) {
                        $stT = $pdoConn->prepare("UPDATE documentos SET `value` = ? WHERE doc_key = 'torneo'");
                        $stT->execute([json_encode($filtrado, JSON_UNESCAPED_UNICODE)]);
                    }
                }
            }
        } catch (Exception $e) {}

        $claves = obtenerCategoriasClaves();
        $mapaLabels = obtenerMapaLabelsCategorias();

        // 1. Asegurar tablas por Sub (jugadores_<subX>, player_stats_<subX>, padres_<subX>)
        $checkOldJug = $pdoConn->query("SHOW TABLES LIKE 'jugadores'")->fetchAll();
        $checkOldStats = $pdoConn->query("SHOW TABLES LIKE 'player_stats'")->fetchAll();

        foreach ($claves as $catKey) {
            [$tJug, $tStats, $tPadres] = asegurarTablasCategoria($catKey);
            if (!empty($checkOldJug)) {
                $stmt = $pdoConn->prepare("SELECT id, cat_key, name, dorsal, pos, foto FROM jugadores WHERE LOWER(cat_key) = ? ORDER BY dorsal, id");
                $stmt->execute([$catKey]);
                $insJug = $pdoConn->prepare("REPLACE INTO `$tJug` (id, cat_key, name, dorsal, pos, foto) VALUES (?, ?, ?, ?, ?, ?)");
                foreach ($stmt->fetchAll() as $r) {
                    $insJug->execute([(int)$r['id'], $catKey, (string)$r['name'], (int)$r['dorsal'], (string)$r['pos'], $r['foto']]);
                }
            }
            if (!empty($checkOldStats)) {
                $stmtS = $pdoConn->prepare("SELECT player_id, goles, asistencias, mvp, updated_at FROM player_stats WHERE LOWER(cat_key) = ?");
                $stmtS->execute([$catKey]);
                $insSt = $pdoConn->prepare("REPLACE INTO `$tStats` (player_id, dorsal, nombre, cat_key, goles, asistencias, mvp, updated_at) VALUES (?, 0, '', ?, ?, ?, ?, ?)");
                foreach ($stmtS->fetchAll() as $s) {
                    $insSt->execute([(int)$s['player_id'], $catKey, (int)$s['goles'], (int)$s['asistencias'], (int)$s['mvp'], $s['updated_at']]);
                }
            }
            sincronizarStatsCategoria($catKey);
        }

        if (!empty($checkOldJug)) $pdoConn->exec("DROP TABLE IF EXISTS `jugadores`");
        if (!empty($checkOldStats)) $pdoConn->exec("DROP TABLE IF EXISTS `player_stats`");

        // 2. Migrar los padres que estaban en 'usuarios' hacia su tabla 'padres_<subX>' y limpiar 'usuarios'
        $colsUsuarios = $pdoConn->query("SHOW COLUMNS FROM `usuarios` LIKE 'hijo_cat'")->fetchAll();
        if (!empty($colsUsuarios)) {
            $padresAntiguos = consultar("SELECT * FROM `usuarios` WHERE rol = 'padre'");
            foreach ($padresAntiguos as $p) {
                $catPadre = slugTablaCat((string)($p['hijo_cat'] ?? 'sub6'));
                if (!in_array($catPadre, $claves, true)) {
                    $catPadre = 'sub6';
                }
                [$tJug, , $tPadres] = asegurarTablasCategoria($catPadre);
                $labelSub = $mapaLabels[$catPadre] ?? strtoupper($catPadre);
                $hijoId = (int)($p['hijo_id'] ?? 0);
                $hijoNombre = 'Por asignar';
                $hijoDorsal = 0;
                $hijoPos = 'MED';
                if ($hijoId > 0) {
                    $jInfo = q("SELECT name, dorsal, pos FROM `$tJug` WHERE id = ?", 'i', [$hijoId]);
                    if (!empty($jInfo)) {
                        $hijoNombre = (string)$jInfo[0]['name'];
                        $hijoDorsal = (int)$jInfo[0]['dorsal'];
                        $hijoPos    = (string)$jInfo[0]['pos'];
                    }
                }
                $venc30 = calcularFechaVencimiento30($p['fecha_vencimiento'] ?? null);
                $stmtInsP = $pdoConn->prepare("REPLACE INTO `$tPadres`
                    (id, usuario, nombre, telefono, sub, hijo_cat, hijo_id, hijo_nombre, hijo_dorsal, hijo_pos, metodo_pago, fecha_vencimiento, bloqueado, password)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmtInsP->execute([
                    (int)$p['id'],
                    (string)$p['usuario'],
                    (string)$p['nombre'],
                    (string)($p['telefono'] ?? ''),
                    $labelSub,
                    $catPadre,
                    $hijoId,
                    $hijoNombre,
                    $hijoDorsal,
                    $hijoPos,
                    !empty($p['metodo_pago']) ? (string)$p['metodo_pago'] : 'linea',
                    $venc30,
                    (int)($p['bloqueado'] ?? 0),
                    (string)$p['password']
                ]);
            }

            // Eliminar padres de 'usuarios' (dejar solo admin y entrenador) y quitar columnas de padres que salían en NULL
            $pdoConn->exec("DELETE FROM `usuarios` WHERE rol = 'padre'");
            $pdoConn->exec("UPDATE `usuarios` SET cat_key = 'todas', cat_label = 'Administración General' WHERE rol = 'admin' AND (cat_key IS NULL OR cat_key = '')");
            $dropCols = ['hijo_cat', 'hijo_id', 'metodo_pago', 'fecha_vencimiento'];
            foreach ($dropCols as $col) {
                try { $pdoConn->exec("ALTER TABLE `usuarios` DROP COLUMN `$col`"); } catch (Exception $e) {}
            }
        }

        // Asegurar que todos los padres en padres_<subX> tengan fecha de vencimiento al día 30 y datos sincronizados
        foreach ($claves as $catKey) {
            [, , $tPadres] = asegurarTablasCategoria($catKey);
            $vencDefault = calcularFechaVencimiento30();
            $pdoConn->exec("UPDATE `$tPadres` SET fecha_vencimiento = '$vencDefault' WHERE fecha_vencimiento IS NULL OR fecha_vencimiento = ''");
            foreach (consultar("SELECT id, fecha_vencimiento FROM `$tPadres`") as $pr) {
                $fNorm = calcularFechaVencimiento30($pr['fecha_vencimiento']);
                if ($fNorm !== $pr['fecha_vencimiento']) {
                    $stU = $pdoConn->prepare("UPDATE `$tPadres` SET fecha_vencimiento = ? WHERE id = ?");
                    $stU->execute([$fNorm, (int)$pr['id']]);
                }
            }
            sincronizarStatsCategoria($catKey);
        }

        // 3. Unificación multi-hijos entre tablas de padres: si un mismo usuario tiene registros de hijos en distintas categorías o filas,
        // consolidar hijos_json en todas sus filas para garantizar que en el Portal y en Familias Registradas figuren todos sus hijos.
        $todosLosPadresFilas = [];
        foreach ($claves as $catKey) {
            [, , $tPadres] = asegurarTablasCategoria($catKey);
            foreach (consultar("SELECT * FROM `$tPadres`") as $filaP) {
                $todosLosPadresFilas[] = ['catKey' => $catKey, 'data' => $filaP];
            }
        }
        $gruposPorUsuario = [];
        foreach ($todosLosPadresFilas as $item) {
            $u = strtolower(trim((string)$item['data']['usuario']));
            if ($u !== '') {
                $gruposPorUsuario[$u][] = $item;
            }
        }
        foreach ($gruposPorUsuario as $u => $items) {
            $hijosAgrupados = [];
            $vistosH = [];
            foreach ($items as $it) {
                $p = $it['data'];
                $cK = $it['catKey'];
                $norm = normalizarFilaPadre($p, $cK);
                if (!empty($norm['hijos']) && is_array($norm['hijos'])) {
                    foreach ($norm['hijos'] as $h) {
                        $k = !empty($h['id']) ? ('id_' . $h['id']) : (strtolower($h['nombre'] ?? '') . '_' . ($h['catKey'] ?? ''));
                        if (!isset($vistosH[$k])) {
                            $vistosH[$k] = true;
                            $hijosAgrupados[] = $h;
                        }
                    }
                }
            }
            if (count($hijosAgrupados) > 0) {
                $jsonConsolidado = json_encode($hijosAgrupados, JSON_UNESCAPED_UNICODE);
                foreach ($items as $it) {
                    $cK = $it['catKey'];
                    $pId = (int)$it['data']['id'];
                    [, , $tPad] = asegurarTablasCategoria($cK);
                    if (($it['data']['hijos_json'] ?? '') !== $jsonConsolidado) {
                        $stU = $pdoConn->prepare("UPDATE `$tPad` SET hijos_json = ? WHERE id = ?");
                        $stU->execute([$jsonConsolidado, $pId]);
                    }
                }
            }
        }

        // 4. Auto-Sanación de Jugadores Huérfanos: Si un jugador existe en la cuenta familiar de un padre pero falta en la tabla jugadores_<subX>, insertarlo automáticamente
        foreach ($claves as $catKey) {
            [$tJug, $tStats, $tPadres] = asegurarTablasCategoria($catKey);
            $padresRows = consultar("SELECT * FROM `$tPadres`");
            foreach ($padresRows as $pr) {
                $norm = normalizarFilaPadre($pr, $catKey);
                if (!empty($norm['hijos']) && is_array($norm['hijos'])) {
                    foreach ($norm['hijos'] as $hijo) {
                        $hId = (float)($hijo['id'] ?? 0);
                        if ($hId <= 0) continue;
                        $hCat = slugTablaCat($hijo['catKey'] ?? $catKey);
                        [$tJugH, $tStatsH] = array_slice(asegurarTablasCategoria($hCat), 0, 2);
                        $existe = consultar("SELECT id FROM `$tJugH` WHERE id = $hId LIMIT 1");
                        if (empty($existe)) {
                            $nom = sanitizarTexto($hijo['nombre'] ?? 'Jugador');
                            $edad = max(0, (int)($hijo['edad'] ?? 0));
                            $dorsal = (int)($hijo['dorsal'] ?? 0);
                            $pos = in_array(strtoupper($hijo['pos'] ?? ''), ['POR', 'DEF', 'MED', 'DEL'], true) ? strtoupper($hijo['pos']) : 'MED';
                            $stmtInsJ = $pdoConn->prepare("INSERT INTO `$tJugH` (id, cat_key, name, edad, dorsal, pos, foto) VALUES (?, ?, ?, ?, ?, ?, 'img/escudo.png')");
                            $stmtInsJ->execute([$hId, $hCat, $nom, $edad, $dorsal, $pos]);
                            $stmtInsS = $pdoConn->prepare("INSERT IGNORE INTO `$tStatsH` (player_id, dorsal, nombre, cat_key, goles, asistencias, mvp) VALUES (?, ?, ?, ?, 0, 0, 0)");
                            $stmtInsS->execute([$hId, $dorsal, $nom, $hCat]);
                        }
                    }
                }
            }
            sincronizarStatsCategoria($catKey);
        }

        // 5. Auto-limpieza de caracteres extraños y entidades HTML en la tabla staff
        try {
            $stmtStaff = $pdoConn->query("SELECT id, nombre, cargo, licencia, exp, especialidad, bio FROM staff");
            if ($stmtStaff) {
                $updStaff = $pdoConn->prepare("UPDATE staff SET nombre=?, cargo=?, licencia=?, exp=?, especialidad=?, bio=? WHERE id=?");
                while ($stRow = $stmtStaff->fetch(PDO::FETCH_ASSOC)) {
                    $updStaff->execute([
                        sanitizarTexto($stRow['nombre']),
                        sanitizarTexto($stRow['cargo']),
                        sanitizarTexto($stRow['licencia']),
                        sanitizarTexto($stRow['exp']),
                        sanitizarTexto($stRow['especialidad']),
                        sanitizarTexto($stRow['bio']),
                        (int)$stRow['id']
                    ]);
                }
            }
        } catch (Exception $e) {}
    } catch (Exception $e) {
        error_log('[Migracion Usuarios/Subs] ' . $e->getMessage());
    }
}

if (!function_exists('limpiarDatosCorruptosV133')) {
    function limpiarDatosCorruptosV133(): void {
        global $pdo;
        if (!$pdo) return;
        try {
            // 1. Limpiar staff: corregir &amp;amp; y remover placeholders de edición
            $stmtStaff = $pdo->query("SELECT id, nombre, cargo, licencia, exp, especialidad, bio FROM staff");
            if ($stmtStaff) {
                $updStaff = $pdo->prepare("UPDATE staff SET nombre=?, cargo=?, licencia=?, exp=?, especialidad=?, bio=? WHERE id=?");
                while ($st = $stmtStaff->fetch(PDO::FETCH_ASSOC)) {
                    $nom = html_entity_decode(html_entity_decode((string)$st['nombre'], ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $car = html_entity_decode(html_entity_decode((string)$st['cargo'], ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $lic = html_entity_decode(html_entity_decode((string)$st['licencia'], ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $exp = html_entity_decode(html_entity_decode((string)$st['exp'], ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $esp = html_entity_decode(html_entity_decode((string)$st['especialidad'], ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $bio = html_entity_decode(html_entity_decode((string)$st['bio'], ENT_QUOTES | ENT_HTML5, 'UTF-8'), ENT_QUOTES | ENT_HTML5, 'UTF-8');

                    $bio = preg_replace('/\s*\(Edita este texto con el l[aá]piz naranja\)/iu', '', $bio);
                    $bio = trim((string)$bio);

                    $updStaff->execute([$nom, $car, $lic, $exp, $esp, $bio, (int)$st['id']]);
                }
            }

            // 2. Limpiar categorias.descripcion: quitar etiquetas span y estilos residuales
            $stmtCat = $pdo->query("SELECT id, descripcion FROM categorias");
            if ($stmtCat) {
                $updCat = $pdo->prepare("UPDATE categorias SET descripcion=? WHERE id=?");
                while ($cr = $stmtCat->fetch(PDO::FETCH_ASSOC)) {
                    $desc = (string)$cr['descripcion'];
                    $descLimpia = preg_replace('/<span[^>]*>/i', '', $desc);
                    $descLimpia = preg_replace('/<\/span>/i', '', $descLimpia);
                    $descLimpia = trim($descLimpia);
                    if ($descLimpia !== $desc) {
                        $updCat->execute([$descLimpia, (int)$cr['id']]);
                    }
                }
            }

            // 3. Limpiar documentos (torneo): quitar <span style...>, </span> y arreglar âœ… -> ✅
            $stmtDoc = $pdo->query("SELECT doc_key, value FROM documentos WHERE doc_key = 'torneo'");
            if ($stmtDoc) {
                $updDoc = $pdo->prepare("UPDATE documentos SET value=? WHERE doc_key=?");
                while ($dr = $stmtDoc->fetch(PDO::FETCH_ASSOC)) {
                    $val = (string)$dr['value'];
                    $valLimpio = preg_replace('/<span[^>]*>/i', '', $val);
                    $valLimpio = preg_replace('/<\/span>/i', '', $valLimpio);
                    $valLimpio = str_replace('âœ…', '✅', $valLimpio);
                    if ($valLimpio !== $val) {
                        $updDoc->execute([$valLimpio, $dr['doc_key']]);
                    }
                }
            }

            // 4. Limpiar textos: decodificar &amp;amp;, quitar spans
            $stmtTxt = $pdo->query("SELECT clave, html FROM textos");
            if ($stmtTxt) {
                $updTxt = $pdo->prepare("UPDATE textos SET html=? WHERE clave=?");
                while ($tr = $stmtTxt->fetch(PDO::FETCH_ASSOC)) {
                    $h = (string)$tr['html'];
                    $hLimpio = str_replace('&amp;amp;', '&amp;', $h);
                    $hLimpio = preg_replace('/<span\s+style="[^"]*">/i', '', $hLimpio);
                    $hLimpio = preg_replace('/<\/span>/i', '', $hLimpio);
                    $hLimpio = str_replace('âœ…', '✅', $hLimpio);
                    $hLimpio = trim($hLimpio);
                    if ($hLimpio !== $h) {
                        $updTxt->execute([$hLimpio, $tr['clave']]);
                    }
                }
            }
        } catch (Exception $e) {
            error_log('[Migracion v133 Limpieza] ' . $e->getMessage());
        }
    }
}

if (!function_exists('debeEjecutarMigracion')) {
    function debeEjecutarMigracion(): bool {
        static $checked = null;
        if ($checked !== null) return $checked;
        try {
            $row = consultar("SELECT valor FROM configuraciones WHERE clave = 'schema_version' LIMIT 1");
            if (!empty($row) && $row[0]['valor'] === 'v133') {
                $checked = false;
                return false;
            }
        } catch (Exception $e) {}
        $checked = true;
        return true;
    }
}

if (debeEjecutarMigracion()) {
    migrarYOptimizarPorSubs();
    limpiarDatosCorruptosV133();
    // Limpieza automática de cualquier archivo de bloqueo temporal del WAF y rate limits
    $wafDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ns_waf_state';
    if (is_dir($wafDir)) {
        foreach (glob($wafDir . DIRECTORY_SEPARATOR . '*.json') as $bf) {
            @unlink($bf);
        }
    }
    $rlDir = __DIR__ . '/../uploads/ratelimit';
    if (is_dir($rlDir)) {
        foreach (glob($rlDir . DIRECTORY_SEPARATOR . '*.json') as $rf) {
            @unlink($rf);
        }
    }
    try {
        q("REPLACE INTO configuraciones (clave, valor) VALUES ('schema_version', 'v133')");
    } catch (Exception $e) {}
}
