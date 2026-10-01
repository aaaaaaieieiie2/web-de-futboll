<?php
/* api/conexion.php - ConexiAA3n a Base de Datos Local / Docker */

// 1. Manejo de SesiAA3n Segura
if (session_status() === PHP_SESSION_NONE) {
    session_start();
  if (empty($_SESSION['csrf_token'])) {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
}

// 2. Cabeceras CORS y Seguridad HTTP
$origen = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'http://localhost';
header("Access-Control-Allow-Origin: $origen");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
// header('Content-Type: application/json; charset=utf-8');

// 3. ConexiAA3n MySQLi (Capa Base)
$mysqli = new mysqli("localhost", "root", "", "nicosport");
if ($mysqli->connect_error) {
    die(json_encode(['error' => 'Error de conexiAA3n a la base de datos']));
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
        return ['error' => 'Error en prepare: ' . $mysqli->error];
    }
    if ($tipos && $params) {
        $stmt->bind_param($tipos, ...$params);
    }
    if (!$stmt->execute()) {
        return ['error' => 'Error en execute: ' . $stmt->error];
    }
    if (strpos(strtoupper($sql), 'SELECT') === 0) {
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
    $rows = [];
    if ($res) {
        while ($r = $res->fetch_assoc()) { $rows[] = $r; }
    }
    return $rows;
}
