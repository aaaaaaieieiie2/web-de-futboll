<?php
/* api/api.php Ã¢â‚¬â€ Endpoint de escrituras: POST api/api.php?op=... */
require __DIR__ . '/../core/conexion.php';
header('Content-Type: application/json; charset=utf-8');

$d = entrada(); $db = db();
$op = isset($_GET['op']) ? $_GET['op'] : '';

// === CSRF MIDDLEWARE ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'mensaje' => 'Invalid CSRF token']);
        exit;
    }
}
// =======================



function auth($roles) {
    if (!isset($_SESSION['ns_rol']) || !in_array($_SESSION['ns_rol'], $roles)) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'mensaje' => 'HTTP 403 Forbidden - Unauthorized']);
        exit;
    }
}

switch ($op) {


case 'usuarios/guardar':
    auth(['admin']);

    q("INSERT INTO usuarios (usuario,password,rol,nombre,hijo_cat,hijo_id,cat_key,cat_label,telefono,metodo_pago,fecha_vencimiento) VALUES (?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE password=VALUES(password), rol=VALUES(rol), nombre=VALUES(nombre), hijo_cat=VALUES(hijo_cat), hijo_id=VALUES(hijo_id), cat_key=VALUES(cat_key), cat_label=VALUES(cat_label), telefono=VALUES(telefono), metodo_pago=VALUES(metodo_pago), fecha_vencimiento=VALUES(fecha_vencimiento)",
    'ssssissssss', [$d['usuario'] ?? '', $d['password'] ?? '', $d['rol'] ?? 'padre', $d['nombre'] ?? '',
    $d['hijoCat'] ?? null, !empty($d['hijoId']) ? (int)$d['hijoId'] : null, $d['catKey'] ?? null, $d['catLabel'] ?? null,
    $d['telefono'] ?? null, $d['metodoPago'] ?? 'linea', $d['fechaVencimiento'] ?? null]);
    break;

case 'usuarios/actualizarPago':
    auth(['admin']);
    q("UPDATE usuarios SET telefono=?, metodo_pago=?, fecha_vencimiento=? WHERE id=?", 
      'sssi', [$d['telefono'] ?? null, $d['metodoPago'] ?? 'linea', $d['fechaVencimiento'] ?? null, (int)$d['id']]);
    break;

case 'usuarios/bloquear':
    auth(['admin']);
    // Auto-create column if not exists
    try {
        global $mysqli;
        $mysqli->query("ALTER TABLE usuarios ADD COLUMN bloqueado TINYINT(1) DEFAULT 0");
    } catch(Exception $e){}
    
    q("UPDATE usuarios SET bloqueado=? WHERE id=?", 'ii', [(int)$d['bloqueado'], (int)$d['id']]);
    break;

case 'usuarios/eliminar':
    auth(['admin']);
    q("DELETE FROM usuarios WHERE id=? AND rol=?", 'is', [(int)$d['id'], $d['rol'] ?? '']);
    break;

case 'evaluaciones/guardar':
    auth(['admin', 'entrenador']);

    q("REPLACE INTO evaluaciones (cat_key,player_id,fecha,tecnicos,tacticos,fisicos,actitudinales) VALUES (?,?,?,?,?,?,?)",
      'sisssss', [$d['catKey'], (int)$d['playerId'], $d['fecha'] ?? '', json_encode($d['tecnicos'] ?? []),
      json_encode($d['tacticos'] ?? []), json_encode($d['fisicos'] ?? []), json_encode($d['actitudinales'] ?? [])]);
    break;

case 'asistencias/guardar':
    auth(['admin', 'entrenador']);
    q("REPLACE INTO asistencias (cat_key,fecha,player_id,estado) VALUES (?,?,?,?)",
      'ssis', [$d['catKey'], $d['fecha'], (int)$d['playerId'], $d['estado']]);
    break;

case 'asistencias/quitar':
    auth(['admin', 'entrenador']);
    q("DELETE FROM asistencias WHERE cat_key=? AND fecha=? AND player_id=?", 'ssi', [$d['catKey'], $d['fecha'], (int)$d['playerId']]);
    break;

case 'comentarios/guardar':
    auth(['admin', 'padre']);
    if ($_SESSION['ns_rol'] === 'padre') { $d['padre_id'] = $_SESSION['ns_user_id']; }
    q("INSERT INTO comentarios_padres (padre_id, tipo, asunto, mensaje, es_anonimo) VALUES (?, ?, ?, ?, ?)",
      'isssi', [
        (int)($d['padre_id'] ?? 0),
        $d['tipo'] ?? '',
        $d['asunto'] ?? '',
        $d['mensaje'] ?? '',
        !empty($d['es_anonimo']) ? 1 : 0
      ]);
    break;

case 'comentarios/borrar':
    auth(['admin']);
    q("DELETE FROM comentarios_padres WHERE id=?", 'i', [(int)$d['id']]);
    break;

case 'comentarios/estado':
    auth(['admin']);
    q("UPDATE comentarios_padres SET estado=? WHERE id=?", 'si', [$d['estado'] ?? '', (int)$d['id']]);
    break;

case 'encuestas/guardar':
    auth(['admin', 'padre']);
    if ($_SESSION['ns_rol'] === 'padre') { $d['padre'] = $_SESSION['ns_nombre']; }
    q("INSERT INTO encuestas (padre,fecha,aspectos,gusta_mas,mejorar,crecimiento,crecimiento_why,recomienda,recomienda_why,observaciones) VALUES (?,?,?,?,?,?,?,?,?,?)",
      'ssssssssss', [$d['padre'] ?? '', $d['fecha'] ?? '', json_encode($d['aspectos'] ?? []), $d['gustaMas'] ?? '',
      $d['mejorar'] ?? '', $d['crecimiento'] ?? '', $d['crecimientoWhy'] ?? '', $d['recomienda'] ?? '',
      $d['recomiendaWhy'] ?? '', $d['observaciones'] ?? '']);
    break;

case 'stats/guardar':
    auth(['admin', 'entrenador']);
    q("REPLACE INTO player_stats (cat_key,player_id,goles,asistencias,mvp,updated_at) VALUES (?,?,?,?,?,?)",
      'siiiis', [$d['catKey'], (int)$d['playerId'], (int)($d['goles'] ?? 0), (int)($d['asistencias'] ?? 0), (int)($d['mvp'] ?? 0), $d['updatedAt'] ?? '']);
    break;

case 'plantillas/guardar':
    auth(['admin', 'entrenador']);
    $db->begin_transaction();
    q("DELETE FROM jugadores WHERE cat_key=?", 's', [$d['catKey']]);
    foreach (($d['lista'] ?? []) as $p) {
        q("INSERT INTO jugadores (id,cat_key,name,dorsal,pos,foto) VALUES (?,?,?,?,?,?)",
          'ississ', [(int)$p['id'], $d['catKey'], $p['name'] ?? '', (int)$p['dorsal'], $p['pos'] ?? '', $p['foto'] ?? null]);
    }
    $db->commit();
    break;

case 'trofeosCustom/guardar':
    auth(['admin']);
    q("REPLACE INTO trofeos_custom (id,nombre,cat_key,categoria,anio,icono,color,descripcion,evaluacion) VALUES (?,?,?,?,?,?,?,?,?)",
      'sssssssss', [$d['id'], $d['nombre'] ?? '', $d['catKey'] ?? '', $d['categoria'] ?? '', $d['aÃƒÂ±o'] ?? '',
      $d['icono'] ?? 'fa-trophy', $d['color'] ?? 'text-amber-400', $d['descripcion'] ?? '', json_encode($d['evaluacion'] ?? (object)[])]);
    break;

case 'trofeosCustom/eliminar':
    auth(['admin']);
    q("DELETE FROM trofeos_custom WHERE id=?", 's', [$d['id']]);
    break;

case 'trofeosOcultos/agregar':
    auth(['admin']);
    q("INSERT IGNORE INTO trofeos_ocultos (id) VALUES (?)", 's', [$d['id']]);
    break;

case 'trofeosEditados/guardar':
    auth(['admin']);
    q("REPLACE INTO trofeos_editados (id,data) VALUES (?,?)", 'ss', [$d['id'], json_encode($d)]);
    break;

case 'textos/guardar':
    auth(['admin']);
    q("REPLACE INTO textos (clave,html) VALUES (?,?)", 'ss', [$d['clave'], $d['html'] ?? '']);
    break;

case 'documentos/guardar':
    auth(['admin']);
    if (in_array($d['key'] ?? '', ['torneo', 'eval_global'], true))
        q("REPLACE INTO documentos (doc_key,value) VALUES (?,?)", 'ss', [$d['key'], json_encode($d['value'] ?? null)]);
    break;

case 'categorias/actualizar_foto':
    auth(['admin']);
    q("UPDATE categorias SET foto=? WHERE id=?", 'si', [$d['foto'] ?? '', (int)$d['id']]);
    break;

case 'configuraciones/guardar':
    auth(['admin']);
    // Attempt auto-create if not exists
    try {
        global $mysqli;
        $mysqli->query("CREATE TABLE IF NOT EXISTS configuraciones (clave VARCHAR(50) PRIMARY KEY, valor TEXT)");
    } catch(Exception $e){}
    
    q("REPLACE INTO configuraciones (clave, valor) VALUES (?, ?)", 'ss', ['tarifa_mensualidad', (string)$d['mensualidad']]);
    q("REPLACE INTO configuraciones (clave, valor) VALUES (?, ?)", 'ss', ['tarifa_inscripcion', (string)$d['inscripcion']]);
    break;

case 'jugadores/actualizar_foto':
    auth(['admin']);
    q("UPDATE jugadores SET foto=? WHERE id=? AND cat_key=?", 'sis', [$d['foto'] ?? '', (int)$d['id'], $d['catKey'] ?? '']);
    break;

case 'staff/guardar':
    auth(['admin']);
    $db->begin_transaction();
    q("DELETE FROM staff");
    foreach (($d['lista'] ?? $d ?? []) as $i => $s) {
        if (!is_array($s)) continue;
        q("REPLACE INTO staff (id,nombre,cargo,licencia,exp,especialidad,bio,foto_icon,color,orden) VALUES (?,?,?,?,?,?,?,?,?,?)",
          'issssssssi', [(int)$s['id'], $s['nombre'] ?? '', $s['cargo'] ?? '', $s['licencia'] ?? '', $s['exp'] ?? '',
          $s['especialidad'] ?? '', $s['bio'] ?? '', $s['fotoIcon'] ?? 'fa-user', $s['color'] ?? '', $i + 1]);
    }
    $db->commit();
    break;

case 'categorias/guardar':
    auth(['admin']);
    $db->begin_transaction();
    q("DELETE FROM categorias");
    foreach (($d['lista'] ?? []) as $i => $c) {
        q("REPLACE INTO categorias (cat_key,label,descripcion,core,orden) VALUES (?,?,?,?,?)",
          'sssii', [$c['key'], $c['label'] ?? '', $c['descripcion'] ?? '', !empty($c['core']) ? 1 : 0, $i + 1]);
    }
    $db->commit();
    break;

case 'reciclados/guardar':
    auth(['admin']);
    q("REPLACE INTO jugadores_reciclados (cat_key,dorsal,data,guardado_en) VALUES (?,?,?,?)",
      'siss', [$d['catKey'], (int)$d['dorsal'], json_encode($d), $d['guardadoEn'] ?? '']);
    break;

case 'reciclados/quitar':
    auth(['admin']);
    q("DELETE FROM jugadores_reciclados WHERE cat_key=? AND dorsal=?", 'si', [$d['catKey'], (int)$d['dorsal']]);
    break;

case 'pagosClub/agregar':
    auth(['admin']);
    q("REPLACE INTO pagos_club (id,padre_id,padre_nombre,concepto,monto,fecha,referencia,metodo,estado) VALUES (?,?,?,?,?,?,?,?,?)",
      'iissdssss', [(int)$d['id'], (int)($d['padreId'] ?? 0), $d['padreNombre'] ?? '', $d['concepto'] ?? '',
      (float)($d['monto'] ?? 0), $d['fecha'] ?? '', $d['referencia'] ?? '', $d['metodo'] ?? 'efectivo', $d['estado'] ?? 'validado']);
    break;

case 'comprobantes/agregar':
    auth(['admin', 'padre']);
    if ($_SESSION['ns_rol'] === 'padre') { $d['padreId'] = $_SESSION['ns_user_id']; $d['padreNombre'] = $_SESSION['ns_nombre']; }
    q("REPLACE INTO comprobantes (id,padre_id,padre_nombre,concepto,monto,fecha,referencia,observaciones,comprobante,estado) VALUES (?,?,?,?,?,?,?,?,?,?)",
      'iissdsssss', [(int)$d['id'], (int)($d['padreId'] ?? 0), $d['padreNombre'] ?? '', $d['concepto'] ?? '',
      (float)($d['monto'] ?? 0), $d['fecha'] ?? '', $d['referencia'] ?? '', $d['observaciones'] ?? '',
      $d['comprobante'] ?? '', $d['estado'] ?? 'en_revision']);
    break;

case 'comprobantes/validar':
    auth(['admin']);
    $aprobar = !empty($d['aprobar']);
    q("UPDATE comprobantes SET estado=? WHERE id=?", 'si', [$aprobar ? 'validado' : 'rechazado', (int)$d['id']]);
    if ($aprobar && isset($d['pago'])) {
        $p = $d['pago'];
        q("REPLACE INTO pagos_club (id,padre_id,padre_nombre,concepto,monto,fecha,referencia,metodo,estado) VALUES (?,?,?,?,?,?,?,?,?)",
          'iissdssss', [(int)$p['id'], (int)($p['padreId'] ?? 0), $p['padreNombre'] ?? '', $p['concepto'] ?? '',
          (float)($p['monto'] ?? 0), $p['fecha'] ?? '', $p['referencia'] ?? '', 'digital', 'validado']);
        q("UPDATE pagos_padre SET estado='validado' WHERE padre_id=? AND referencia=? AND estado='pendiente'",
          'is', [(int)($p['padreId'] ?? 0), $p['referencia'] ?? '']);
    }
    break;

case 'pagosPadre/agregar':
    auth(['admin', 'padre']);
    if ($_SESSION['ns_rol'] === 'padre') { $d['padreId'] = $_SESSION['ns_user_id']; }
    q("REPLACE INTO pagos_padre (id,padre_id,concepto,monto,fecha,referencia,observaciones,comprobante,estado) VALUES (?,?,?,?,?,?,?,?,?)",
      'iisdsssss', [(int)$d['id'], (int)($d['padreId'] ?? 0), $d['concepto'] ?? '', (float)($d['monto'] ?? 0),
      $d['fecha'] ?? '', $d['referencia'] ?? '', $d['observaciones'] ?? '', $d['comprobante'] ?? '', $d['estado'] ?? 'en_revision']);
    break;

case 'pagosPlataforma/agregar':
    auth(['admin']);
    q("REPLACE INTO pagos_plataforma (id,tipo,monto,referencia,fecha,observaciones,comprobante,estado) VALUES (?,?,?,?,?,?,?,?)",
      'isdsssss', [(int)$d['id'], $d['tipo'] ?? '', (float)($d['monto'] ?? 0), $d['referencia'] ?? '',
      $d['fecha'] ?? '', $d['observaciones'] ?? '', $d['comprobante'] ?? '', $d['estado'] ?? 'en_revision']);
    break;

default:
    salida(['error' => 'OperaciÃƒÂ³n desconocida: ' . $op]);
}
salida(['ok' => true]);
