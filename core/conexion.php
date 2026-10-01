<?php
/* core/conexion.php - Conexión a Base de Datos (XAMPP / Docker) + capa de seguridad */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/security.php';

cabeceras_seguridad();
cors_aplicar();

/* ===== Credenciales desde variables de entorno (.env en desarrollo) ===== */
function cargar_credenciales(): array {
    $env = [];
    $f = __DIR__ . '/.env';
    if (is_readable($f)) {
        foreach (file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
            $linea = trim($linea);
            if ($linea === '' || $linea[0] === '#' || strpos($linea, '=') === false) continue;
            [$k, $v] = explode('=', $linea, 2);
            $env[trim($k)] = trim($v);
        }
    }
    return [
        'host' => getenv('DB_HOST') ?: ($env['DB_HOST'] ?? 'localhost'),
        'user' => getenv('DB_USER') ?: ($env['DB_USER'] ?? 'root'),
        'pass' => (getenv('DB_PASS') !== false && getenv('DB_PASS') !== '') ? getenv('DB_PASS') : ($env['DB_PASS'] ?? ''),
        'name' => getenv('DB_NAME') ?: ($env['DB_NAME'] ?? 'nicosport'),
    ];
}

$creds = cargar_credenciales();

// 3. Conexión MySQLi (Capa Base)
$mysqli = @new mysqli($creds['host'], $creds['user'], $creds['pass'], $creds['name']);
if ($mysqli->connect_error) {
    http_response_code(500);
    die(json_encode(['error' => 'Error de conexión a la base de datos']));
}
$mysqli->set_charset("utf8mb4");

// 4. Funciones Helper
function db() {
    global $mysqli;
    return $mysqli;
}
function entrada() {
    return json_decode(file_get_contents('php://input'), true) ?: [];
}
function salida($data) {
    echo json_encode($data);
    exit;
}
function q($sql, $tipos = '', $params = []) {
    global $mysqli;
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        error_log('[DB prepare] ' . $mysqli->error . ' | SQL: ' . $sql);
        return ['error' => 'Error interno de base de datos'];
    }
    if ($tipos && $params) {
        $stmt->bind_param($tipos, ...$params);
    }
    if (!$stmt->execute()) {
        error_log('[DB execute] ' . $stmt->error . ' | SQL: ' . $sql);
        return ['error' => 'Error interno de base de datos'];
    }
    if (strpos(strtoupper(ltrim($sql)), 'SELECT') === 0) {
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) { $rows[] = $r; }
        return $rows;
    }
    return ['insert_id' => $stmt->insert_id, 'affected_rows' => $stmt->affected_rows];
}
function consultar($sql) {
    global $mysqli;
    $res = $mysqli->query($sql);
    if ($res === false) {
        error_log('[DB query] ' . $mysqli->error . ' | SQL: ' . $sql);
        return [];
    }
    $rows = [];
    if ($res instanceof mysqli_result) {
        while ($r = $res->fetch_assoc()) { $rows[] = $r; }
    }
    return $rows;
}

/* ===== Normalización de fechas: ISO (YYYY-MM-DD) como único formato ===== */
/** Convierte cualquier fecha almacenada (ISO, DD/MM/YYYY, "5/8/2026", locale es-PA) a YYYY-MM-DD. */
function normalizar_fecha(?string $f): ?string {
    if ($f === null) return null;
    $f = trim($f);
    if ($f === '') return '';
    // Ya ISO
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $f)) return $f;
    // ISO con hora
    if (preg_match('/^(\d{4}-\d{2}-\d{2})[T ]\d{2}:\d{2}/', $f, $m)) return $m[1];
    // D/M/YYYY o DD/MM/YY (separadores / . -)
    if (preg_match('#^(\d{1,2})[/.\-](\d{1,2})[/.\-](\d{2,4})$#', $f, $m)) {
        $y = (int)$m[3]; if ($y < 100) $y += ($y < 70 ? 2000 : 1900);
        return sprintf('%04d-%02d-%02d', $y, (int)$m[2], (int)$m[1]);
    }
    // Fallback: intentar strtotime
    $ts = strtotime($f);
    return $ts !== false ? date('Y-m-d', $ts) : $f;
}

/** Fecha de hoy en ISO. */
function hoy_iso(): string { return date('Y-m-d'); }

/** ID numérico generado en el servidor para tablas sin AUTO_INCREMENT (monótono y único). */
function generar_id(): int {
    return (int)(microtime(true) * 10000) + random_int(0, 9);
}

/* ===== Helpers de sesión/roles ===== */
function rol_actual(): string {
    return $_SESSION['ns_rol'] ?? 'publico';
}
function user_id_actual(): int {
    return (int)($_SESSION['ns_user_id'] ?? 0);
}
function autenticado(): bool {
    return isset($_SESSION['ns_user_id']) && isset($_SESSION['ns_rol']);
}
