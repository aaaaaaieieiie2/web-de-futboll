<?php
/* api/tarifas.php — Gestión Segura de Tarifas (GET Público + POST Protegido con Admin, CSRF y PDO) */
require_once __DIR__ . '/../core/conexion.php';
header('Content-Type: application/json; charset=utf-8');

$pdoConn = pdo();
try {
    $pdoConn->exec("CREATE TABLE IF NOT EXISTS configuraciones (clave VARCHAR(50) PRIMARY KEY, valor TEXT) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (PDOException $e) {
    error_log('[Tarifas Init Error] ' . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['ns_rol']) || $_SESSION['ns_rol'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'HTTP 403 Forbidden - Solo administradores']);
        exit;
    }

    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token) || !verificarCsrfToken($token)) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Token CSRF inválido']);
        exit;
    }

    $d = entrada();
    $m = max(0, min(10000, (float)($d['mensualidad'] ?? 15)));
    $i = max(0, min(10000, (float)($d['inscripcion'] ?? 15)));

    $stmt = $pdoConn->prepare("REPLACE INTO configuraciones (clave, valor) VALUES (?, ?)");
    $stmt->execute(['tarifa_mensualidad', (string)$m]);
    $stmt->execute(['tarifa_inscripcion', (string)$i]);

    try {
        $stmtTxt = $pdoConn->prepare("REPLACE INTO textos (clave, html) VALUES (?, ?)");
        $stmtTxt->execute(['cuotaMensual', (string)$m]);
        $stmtTxt->execute(['cuotaInscripcion', (string)$i]);
        $checkTarifas = $pdoConn->query("SHOW TABLES LIKE 'tarifas'")->fetch();
        if ($checkTarifas) {
            $pdoConn->exec("CREATE TABLE IF NOT EXISTS tarifas (id INT PRIMARY KEY, mensualidad DECIMAL(10,2), inscripcion DECIMAL(10,2))");
            $stmtT = $pdoConn->prepare("REPLACE INTO tarifas (id, mensualidad, inscripcion) VALUES (1, ?, ?)");
            $stmtT->execute([$m, $i]);
        }
    } catch (Exception $e) {}

    echo json_encode(['ok' => true, 'success' => true, 'mensualidad' => $m, 'inscripcion' => $i]);
    exit;
}

// GET público sin bloqueos para visitantes y rastreadores (Googlebot)
$stmt = $pdoConn->prepare("SELECT clave, valor FROM configuraciones WHERE clave IN ('tarifa_mensualidad', 'tarifa_inscripcion')");
$stmt->execute();
$cfg = ['mensualidad' => 15.0, 'inscripcion' => 15.0];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    if ($r['clave'] === 'tarifa_mensualidad') $cfg['mensualidad'] = (float)$r['valor'];
    if ($r['clave'] === 'tarifa_inscripcion') $cfg['inscripcion'] = (float)$r['valor'];
}
echo json_encode($cfg);