<?php
/* api/exportar_datos.php — Descarga completa de la información de la Base de Datos (Excel .xls y Respaldo .sql) */
require_once __DIR__ . '/../core/conexion.php';

if (($_SESSION['ns_rol'] ?? '') !== 'admin') {
    http_response_code(403);
    die('Acceso denegado. Exclusivo para el Administrador.');
}

$token = $_GET['csrf'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token) || !verificarCsrfToken($token)) {
    http_response_code(403);
    die('Token de seguridad inválido. Recarga el panel e intenta nuevamente.');
}

$formato = strtolower(trim((string)($_GET['formato'] ?? 'excel')));
$fechaHoy = date('Y-m-d_His');
$clavesSub = obtenerCategoriasClaves();
$mapaLabels = obtenerMapaLabelsCategorias();

if ($formato === 'sql') {
    header('Content-Type: application/sql; charset=utf-8');
    header('Content-Disposition: attachment; filename="Respaldo_BD_Completo_' . $fechaHoy . '.sql"');
    header('X-Robots-Tag: noindex, nofollow');

    $pdoConn = pdo();
    echo "-- ==========================================================\n";
    echo "-- RESPALDO COMPLETO DE BASE DE DATOS (PORTABILIDAD DE DATOS)\n";
    echo "-- Fecha de generación: " . date('d/m/Y h:i:s A') . "\n";
    echo "-- ==========================================================\n\n";
    echo "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\n\n";

    $tablas = [];
    foreach (consultar("SHOW TABLES") as $r) {
        $tablas[] = reset($r);
    }

    foreach ($tablas as $t) {
        echo "-- ----------------------------------------------------------\n";
        echo "-- Tabla: `$t`\n";
        echo "-- ----------------------------------------------------------\n";
        $createRow = consultar("SHOW CREATE TABLE `$t`");
        if (!empty($createRow[0]['Create Table'])) {
            echo "DROP TABLE IF EXISTS `$t`;\n";
            echo $createRow[0]['Create Table'] . ";\n\n";
        }
        $filas = consultar("SELECT * FROM `$t`");
        if (!empty($filas)) {
            foreach ($filas as $f) {
                $cols = array_map(fn($c) => "`$c`", array_keys($f));
                $vals = array_map(function($v) use ($pdoConn) {
                    if ($v === null) return 'NULL';
                    return $pdoConn->quote((string)$v);
                }, array_values($f));
                echo "INSERT INTO `$t` (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ");\n";
            }
            echo "\n";
        }
    }
    echo "SET FOREIGN_KEY_CHECKS = 1;\n";
    exit;
}

// Formato por defecto: Excel (.xls en HTML UTF-8 compatible con Microsoft Excel / LibreOffice / Google Sheets)
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="Informacion_Completa_Club_' . $fechaHoy . '.xls"');
header('X-Robots-Tag: noindex, nofollow');

echo "\xEF\xBB\xBF"; // BOM UTF-8
?>
<html>
<head>
<meta charset="UTF-8">
<style>
  body { font-family: Arial, sans-serif; font-size: 12px; }
  h2 { background: #FF5500; color: #FFFFFF; padding: 8px; margin-top: 20px; }
  table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
  th { background: #111111; color: #FFFFFF; font-weight: bold; border: 1px solid #555555; padding: 6px; text-align: left; }
  td { border: 1px solid #CCCCCC; padding: 6px; }
</style>
</head>
<body>
  <h1>REPORTE Y EXPORTACIÓN COMPLETA DE DATOS DEL CLUB (<?= date('d/m/Y h:i A') ?>)</h1>

  <!-- 1. JUGADORES Y ESTADÍSTICAS POR CATEGORÍA (SUB) -->
  <?php foreach ($clavesSub as $catKey):
      [$tJug, $tStats, $tPadres] = asegurarTablasCategoria($catKey);
      $lbl = $mapaLabels[$catKey] ?? strtoupper($catKey);
      $jugadores = consultar("SELECT j.*, COALESCE(s.goles,0) AS goles, COALESCE(s.asistencias,0) AS asistencias, COALESCE(s.mvp,0) AS mvp
                              FROM `$tJug` j LEFT JOIN `$tStats` s ON j.id = s.player_id ORDER BY j.dorsal, j.id");
  ?>
    <h2>1. PLANTILLA Y ESTADÍSTICAS — <?= htmlspecialchars($lbl) ?> (<?= count($jugadores) ?> jugadores)</h2>
    <table>
      <thead>
        <tr>
          <th>ID Jugador</th>
          <th>Categoría</th>
          <th>Nombre del Jugador</th>
          <th>Edad</th>
          <th>Dorsal (#)</th>
          <th>Posición</th>
          <th>Goles</th>
          <th>Asistencias</th>
          <th>MVP</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($jugadores)): ?>
          <tr><td colspan="9">Sin jugadores registrados en esta categoría.</td></tr>
        <?php else: foreach ($jugadores as $j): ?>
          <tr>
            <td><?= (int)$j['id'] ?></td>
            <td><?= htmlspecialchars($lbl) ?></td>
            <td><?= htmlspecialchars((string)$j['name']) ?></td>
            <td><?= (int)($j['edad'] ?? 0) ?></td>
            <td><?= (int)$j['dorsal'] > 0 ? '#' . (int)$j['dorsal'] : 'S/N' ?></td>
            <td><?= htmlspecialchars((string)$j['pos']) ?></td>
            <td><?= (int)$j['goles'] ?></td>
            <td><?= (int)$j['asistencias'] ?></td>
            <td><?= (int)$j['mvp'] ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  <?php endforeach; ?>

  <!-- 2. PADRES DE FAMILIA POR CATEGORÍA (SUB) -->
  <?php foreach ($clavesSub as $catKey):
      [, , $tPadres] = asegurarTablasCategoria($catKey);
      $lbl = $mapaLabels[$catKey] ?? strtoupper($catKey);
      $padres = consultar("SELECT * FROM `$tPadres` ORDER BY id");
  ?>
    <h2>2. PADRES Y FAMILIAS REGISTRADAS — <?= htmlspecialchars($lbl) ?> (<?= count($padres) ?> familias)</h2>
    <table>
      <thead>
        <tr>
          <th>ID Padre</th>
          <th>Usuario / Nombre del Padre</th>
          <th>Teléfono</th>
          <th>Categoría (Sub)</th>
          <th>Hijo Inscrito</th>
          <th>Edad Hijo</th>
          <th>Dorsal Hijo</th>
          <th>Posición Hijo</th>
          <th>Método de Pago</th>
          <th>Fecha de Vencimiento</th>
          <th>Estado Cuenta</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($padres)): ?>
          <tr><td colspan="11">Sin familias registradas en esta categoría.</td></tr>
        <?php else: foreach ($padres as $p): ?>
          <tr>
            <td><?= (int)$p['id'] ?></td>
            <td><?= htmlspecialchars((string)$p['nombre']) ?></td>
            <td><?= htmlspecialchars((string)$p['telefono']) ?></td>
            <td><?= htmlspecialchars((string)$p['sub']) ?></td>
            <td><?= htmlspecialchars((string)$p['hijo_nombre']) ?></td>
            <td><?= (int)($p['hijo_edad'] ?? 0) ?></td>
            <td><?= (int)$p['hijo_dorsal'] > 0 ? '#' . (int)$p['hijo_dorsal'] : 'S/N' ?></td>
            <td><?= htmlspecialchars((string)$p['hijo_pos']) ?></td>
            <td><?= htmlspecialchars((string)$p['metodo_pago']) ?></td>
            <td><?= htmlspecialchars((string)$p['fecha_vencimiento']) ?></td>
            <td><?= !empty($p['bloqueado']) ? 'Suspendido' : 'Activo' ?></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  <?php endforeach; ?>

  <!-- 3. HISTORIAL DE PAGOS DEL CLUB -->
  <?php $pagosClub = consultar("SELECT * FROM pagos_club ORDER BY id DESC"); ?>
  <h2>3. HISTORIAL DE PAGOS Y MENSUALIDADES DEL CLUB (<?= count($pagosClub) ?> registros)</h2>
  <table>
    <thead>
      <tr>
        <th>ID Pago</th>
        <th>ID Padre</th>
        <th>Nombre del Padre</th>
        <th>Concepto</th>
        <th>Monto ($)</th>
        <th>Fecha</th>
        <th>Referencia</th>
        <th>Método</th>
        <th>Estado</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($pagosClub)): ?>
        <tr><td colspan="9">Sin pagos registrados.</td></tr>
      <?php else: foreach ($pagosClub as $pg): ?>
        <tr>
          <td><?= (int)$pg['id'] ?></td>
          <td><?= (int)$pg['padre_id'] ?></td>
          <td><?= htmlspecialchars((string)$pg['padre_nombre']) ?></td>
          <td><?= htmlspecialchars((string)$pg['concepto']) ?></td>
          <td><?= number_format((float)$pg['monto'], 2) ?></td>
          <td><?= htmlspecialchars((string)$pg['fecha']) ?></td>
          <td><?= htmlspecialchars((string)$pg['referencia']) ?></td>
          <td><?= htmlspecialchars((string)$pg['metodo']) ?></td>
          <td><?= htmlspecialchars((string)$pg['estado']) ?></td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>

  <!-- 4. ASISTENCIAS REGISTRADAS -->
  <?php $asistencias = consultar("SELECT * FROM asistencias ORDER BY fecha DESC"); ?>
  <h2>4. REGISTRO DE ASISTENCIAS (<?= count($asistencias) ?> registros)</h2>
  <table>
    <thead>
      <tr>
        <th>Categoría</th>
        <th>Fecha</th>
        <th>ID Jugador</th>
        <th>Estado</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($asistencias)): ?>
        <tr><td colspan="4">Sin asistencias registradas.</td></tr>
      <?php else: foreach ($asistencias as $as): ?>
        <tr>
          <td><?= htmlspecialchars((string)$as['cat_key']) ?></td>
          <td><?= htmlspecialchars((string)$as['fecha']) ?></td>
          <td><?= (int)$as['player_id'] ?></td>
          <td><?= htmlspecialchars((string)$as['estado']) ?></td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>

  <!-- 5. CUERPO TÉCNICO Y ENTRENADORES -->
  <?php $usuariosStaff = consultar("SELECT id, usuario, rol, nombre, cat_key, cat_label FROM usuarios ORDER BY id"); ?>
  <h2>5. USUARIOS ADMINISTRATIVOS Y ENTRENADORES (<?= count($usuariosStaff) ?> cuentas)</h2>
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Usuario</th>
        <th>Rol</th>
        <th>Nombre</th>
        <th>Categoría Asignada</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($usuariosStaff as $us): ?>
        <tr>
          <td><?= (int)$us['id'] ?></td>
          <td><?= htmlspecialchars((string)$us['usuario']) ?></td>
          <td><?= htmlspecialchars((string)$us['rol']) ?></td>
          <td><?= htmlspecialchars((string)$us['nombre']) ?></td>
          <td><?= htmlspecialchars((string)($us['cat_label'] ?: $us['cat_key'])) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>