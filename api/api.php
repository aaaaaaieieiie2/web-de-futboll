<?php
/* api/api.php — Endpoint de escrituras: POST api/api.php?op=... */
require __DIR__ . '/../core/conexion.php';
header('Content-Type: application/json; charset=utf-8');

$d = entrada(); $db = db();
$op = isset($_GET['op']) ? $_GET['op'] : '';

// === CSRF MIDDLEWARE (obligatorio en toda escritura) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_exigir();
}
// =======================================================

function auth($roles) {
    if (!autenticado() || !in_array($_SESSION['ns_rol'], $roles)) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'error' => 'Unauthorized']);
        exit;
    }
}

/* Detecta errores devueltos por q() y aborta con 500 */
function chk($res) {
    if (is_array($res) && isset($res['error'])) {
        http_response_code(500);
        salida(['ok' => false, 'error' => $res['error']]);
    }
    return $res;
}

/* Normaliza una fecha de entrada a ISO YYYY-MM-DD (null si vacía) */
function f_in($v): ?string {
    if ($v === null || $v === '') return null;
    return normalizar_fecha((string)$v);
}

switch ($op) {

case 'usuarios/guardar':
    auth(['admin']);
    // La contraseña SIEMPRE se hashea con bcrypt antes de guardar
    $passPlana = (string)($d['password'] ?? '');
    if ($passPlana === '') {
        salida(['ok' => false, 'error' => 'La contraseña es obligatoria']);
    }
    $hash = password_hash($passPlana, PASSWORD_DEFAULT);
    chk(q("INSERT INTO usuarios (usuario,password,rol,nombre,hijo_cat,hijo_id,cat_key,cat_label,telefono,metodo_pago,fecha_vencimiento) VALUES (?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE password=VALUES(password), rol=VALUES(rol), nombre=VALUES(nombre), hijo_cat=VALUES(hijo_cat), hijo_id=VALUES(hijo_id), cat_key=VALUES(cat_key), cat_label=VALUES(cat_label), telefono=VALUES(telefono), metodo_pago=VALUES(metodo_pago), fecha_vencimiento=VALUES(fecha_vencimiento)",
    'ssssissssss', [limpiar_texto($d['usuario'] ?? '', 80), $hash, in_array($d['rol'] ?? 'padre', ['admin','entrenador','padre'], true) ? $d['rol'] : 'padre', limpiar_texto($d['nombre'] ?? '', 150),
    $d['hijoCat'] ?? null, !empty($d['hijoId']) ? (int)$d['hijoId'] : null, $d['catKey'] ?? null, $d['catLabel'] ?? null,
    $d['telefono'] ?? null, $d['metodoPago'] ?? 'linea', f_in($d['fechaVencimiento'] ?? null)]));
    break;

case 'usuarios/actualizarPago':
    auth(['admin']);
    chk(q("UPDATE usuarios SET telefono=?, metodo_pago=?, fecha_vencimiento=? WHERE id=?",
      'sssi', [$d['telefono'] ?? null, $d['metodoPago'] ?? 'linea', f_in($d['fechaVencimiento'] ?? null), (int)$d['id']]));
    break;

case 'usuarios/bloquear':
    auth(['admin']);
    // Auto-create column if not exists
    try {
        global $mysqli;
        $mysqli->query("ALTER TABLE usuarios ADD COLUMN bloqueado TINYINT(1) DEFAULT 0");
    } catch(Exception $e){}

    // No permitir bloquearse a sí mismo
    if ((int)($d['id'] ?? 0) === user_id_actual()) {
        salida(['ok' => false, 'error' => 'No puedes bloquear tu propia cuenta']);
    }
    chk(q("UPDATE usuarios SET bloqueado=? WHERE id=?", 'ii', [(int)$d['bloqueado'], (int)$d['id']]));
    break;

case 'usuarios/eliminar':
    auth(['admin']);
    if ((int)($d['id'] ?? 0) === user_id_actual()) {
        salida(['ok' => false, 'error' => 'No puedes eliminar tu propia cuenta']);
    }
    chk(q("DELETE FROM usuarios WHERE id=? AND rol=?", 'is', [(int)$d['id'], $d['rol'] ?? '']));
    break;

case 'evaluaciones/guardar':
    auth(['admin', 'entrenador']);
    // UPSERT: no borra la fila si algún campo falla (a diferencia de REPLACE INTO)
    chk(q("INSERT INTO evaluaciones (cat_key,player_id,fecha,tecnicos,tacticos,fisicos,actitudinales) VALUES (?,?,?,?,?,?,?)
           ON DUPLICATE KEY UPDATE fecha=VALUES(fecha), tecnicos=VALUES(tecnicos), tacticos=VALUES(tacticos), fisicos=VALUES(fisicos), actitudinales=VALUES(actitudinales)",
      'sisssss', [$d['catKey'], (int)$d['playerId'], f_in($d['fecha'] ?? '') ?: hoy_iso(), json_encode($d['tecnicos'] ?? []),
      json_encode($d['tacticos'] ?? []), json_encode($d['fisicos'] ?? []), json_encode($d['actitudinales'] ?? [])]));
    break;

case 'asistencias/guardar':
    auth(['admin', 'entrenador']);
    chk(q("INSERT INTO asistencias (cat_key,fecha,player_id,estado) VALUES (?,?,?,?)
           ON DUPLICATE KEY UPDATE estado=VALUES(estado)",
      'ssis', [$d['catKey'], f_in($d['fecha']) ?? hoy_iso(), (int)$d['playerId'], $d['estado']]));
    break;

case 'asistencias/quitar':
    auth(['admin', 'entrenador']);
    chk(q("DELETE FROM asistencias WHERE cat_key=? AND fecha=? AND player_id=?", 'ssi', [$d['catKey'], f_in($d['fecha']), (int)$d['playerId']]));
    break;

case 'comentarios/guardar':
    auth(['admin', 'padre']);
    if ($_SESSION['ns_rol'] === 'padre') { $d['padre_id'] = user_id_actual(); }
    chk(q("INSERT INTO comentarios_padres (padre_id, tipo, asunto, mensaje, es_anonimo) VALUES (?, ?, ?, ?, ?)",
      'isssi', [
        (int)($d['padre_id'] ?? 0),
        $d['tipo'] ?? '',
        limpiar_texto($d['asunto'] ?? '', 200),
        limpiar_texto($d['mensaje'] ?? '', 5000),
        !empty($d['es_anonimo']) ? 1 : 0
      ]));
    break;

case 'comentarios/borrar':
    auth(['admin']);
    chk(q("DELETE FROM comentarios_padres WHERE id=?", 'i', [(int)$d['id']]));
    break;

case 'comentarios/estado':
    auth(['admin']);
    chk(q("UPDATE comentarios_padres SET estado=? WHERE id=?", 'si', [$d['estado'] ?? '', (int)$d['id']]));
    break;

case 'encuestas/guardar':
    auth(['admin', 'padre']);
    if ($_SESSION['ns_rol'] === 'padre') { $d['padre'] = $_SESSION['ns_nombre']; }
    chk(q("INSERT INTO encuestas (padre,fecha,aspectos,gusta_mas,mejorar,crecimiento,crecimiento_why,recomienda,recomienda_why,observaciones) VALUES (?,?,?,?,?,?,?,?,?,?)",
      'ssssssssss', [limpiar_texto($d['padre'] ?? '', 150), f_in($d['fecha'] ?? '') ?? hoy_iso(), json_encode($d['aspectos'] ?? []), limpiar_texto($d['gustaMas'] ?? '', 500),
      limpiar_texto($d['mejorar'] ?? '', 2000), limpiar_texto($d['crecimiento'] ?? '', 500), limpiar_texto($d['crecimientoWhy'] ?? '', 2000), limpiar_texto($d['recomienda'] ?? '', 500),
      limpiar_texto($d['recomiendaWhy'] ?? '', 2000), limpiar_texto($d['observaciones'] ?? '', 2000)]));
    break;

case 'stats/guardar':
    auth(['admin', 'entrenador']);
    chk(q("INSERT INTO player_stats (cat_key,player_id,goles,asistencias,mvp,updated_at) VALUES (?,?,?,?,?,?)
           ON DUPLICATE KEY UPDATE goles=VALUES(goles), asistencias=VALUES(asistencias), mvp=VALUES(mvp), updated_at=VALUES(updated_at)",
      'siiiis', [$d['catKey'], (int)$d['playerId'], (int)($d['goles'] ?? 0), (int)($d['asistencias'] ?? 0), (int)($d['mvp'] ?? 0), f_in($d['updatedAt'] ?? '') ?: hoy_iso()]));
    break;

case 'plantillas/guardar':
    auth(['admin', 'entrenador']);
    $db->begin_transaction();
    try {
        chk(q("DELETE FROM jugadores WHERE cat_key=?", 's', [$d['catKey']]));
        foreach (($d['lista'] ?? []) as $p) {
            chk(q("INSERT INTO jugadores (id,cat_key,name,dorsal,pos,foto) VALUES (?,?,?,?,?,?)",
              'ississ', [(int)$p['id'], $d['catKey'], limpiar_texto($p['name'] ?? '', 150), (int)$p['dorsal'], $p['pos'] ?? '', $p['foto'] ?? null]));
        }
        $db->commit();
    } catch (Exception $e) {
        $db->rollback();
        http_response_code(500);
        salida(['ok' => false, 'error' => 'No se pudo guardar la plantilla']);
    }
    break;

case 'trofeosCustom/guardar':
    auth(['admin']);
    chk(q("REPLACE INTO trofeos_custom (id,nombre,cat_key,categoria,anio,icono,color,descripcion,evaluacion) VALUES (?,?,?,?,?,?,?,?,?)",
      'sssssssss', [limpiar_texto($d['id'], 60), limpiar_texto($d['nombre'] ?? '', 150), $d['catKey'] ?? '', limpiar_texto($d['categoria'] ?? '', 120), $d['anio'] ?? '',
      $d['icono'] ?? 'fa-trophy', $d['color'] ?? 'text-amber-400', limpiar_texto($d['descripcion'] ?? '', 2000), json_encode($d['evaluacion'] ?? (object)[])]));
    break;

case 'trofeosCustom/eliminar':
    auth(['admin']);
    chk(q("DELETE FROM trofeos_custom WHERE id=?", 's', [$d['id']]));
    break;

case 'trofeosOcultos/agregar':
    auth(['admin']);
    chk(q("INSERT IGNORE INTO trofeos_ocultos (id) VALUES (?)", 's', [$d['id']]));
    break;

case 'trofeosEditados/guardar':
    auth(['admin']);
    chk(q("REPLACE INTO trofeos_editados (id,data) VALUES (?,?)", 'ss', [$d['id'], json_encode($d)]));
    break;

case 'textos/guardar':
    auth(['admin']);
    // Sanitizar HTML para evitar XSS almacenado
    chk(q("REPLACE INTO textos (clave,html) VALUES (?,?)", 'ss', [$d['clave'], sanitizar_html($d['html'] ?? '')]));
    break;

case 'documentos/guardar':
    auth(['admin']);
    if (in_array($d['key'] ?? '', ['torneo', 'eval_global'], true))
        chk(q("REPLACE INTO documentos (doc_key,value) VALUES (?,?)", 'ss', [$d['key'], json_encode($d['value'] ?? null)]));
    break;

case 'categorias/actualizar_foto':
    auth(['admin']);
    chk(q("UPDATE categorias SET foto=? WHERE id=?", 'si', [$d['foto'] ?? '', (int)$d['id']]));
    break;

case 'configuraciones/guardar':
    auth(['admin']);
    $mysqli->query("CREATE TABLE IF NOT EXISTS configuraciones (clave VARCHAR(50) PRIMARY KEY, valor TEXT) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    // Validar que sean números positivos antes de guardar
    $mensualidad = filter_var($d['mensualidad'] ?? null, FILTER_VALIDATE_FLOAT);
    $inscripcion = filter_var($d['inscripcion'] ?? null, FILTER_VALIDATE_FLOAT);
    if ($mensualidad === false || $mensualidad < 0 || $inscripcion === false || $inscripcion < 0) {
        http_response_code(400);
        salida(['ok' => false, 'error' => 'Tarifas inválidas: deben ser números ≥ 0']);
    }
    chk(q("REPLACE INTO configuraciones (clave, valor) VALUES (?, ?)", 'ss', ['tarifa_mensualidad', (string)$mensualidad]));
    chk(q("REPLACE INTO configuraciones (clave, valor) VALUES (?, ?)", 'ss', ['tarifa_inscripcion', (string)$inscripcion]));
    break;

case 'configuraciones/guardarValor':
    auth(['admin']);
    $mysqli->query("CREATE TABLE IF NOT EXISTS configuraciones (clave VARCHAR(50) PRIMARY KEY, valor TEXT) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $clave = substr((string)($d['clave'] ?? ''), 0, 50);
    if ($clave === '') {
        http_response_code(400);
        salida(['ok' => false, 'error' => 'Clave de configuración requerida']);
    }
    chk(q("REPLACE INTO configuraciones (clave, valor) VALUES (?, ?)", 'ss', [$clave, (string)($d['valor'] ?? '')]));
    break;

case 'jugadores/actualizar_foto':
    auth(['admin']);
    chk(q("UPDATE jugadores SET foto=? WHERE id=? AND cat_key=?", 'sis', [$d['foto'] ?? '', (int)$d['id'], $d['catKey'] ?? '']));
    break;

case 'staff/guardar':
    auth(['admin']);
    $db->begin_transaction();
    try {
        chk(q("DELETE FROM staff"));
        foreach (($d['lista'] ?? $d ?? []) as $i => $s) {
            if (!is_array($s)) continue;
            chk(q("REPLACE INTO staff (id,nombre,cargo,licencia,exp,especialidad,bio,foto_icon,color,orden) VALUES (?,?,?,?,?,?,?,?,?,?)",
              'issssssssi', [(int)$s['id'], limpiar_texto($s['nombre'] ?? '', 150), limpiar_texto($s['cargo'] ?? '', 150), limpiar_texto($s['licencia'] ?? '', 150), limpiar_texto($s['exp'] ?? '', 150),
              limpiar_texto($s['especialidad'] ?? '', 500), limpiar_texto($s['bio'] ?? '', 2000), $s['fotoIcon'] ?? 'fa-user', $s['color'] ?? '', $i + 1]));
        }
        $db->commit();
    } catch (Exception $e) {
        $db->rollback();
        http_response_code(500);
        salida(['ok' => false, 'error' => 'No se pudo guardar el staff']);
    }
    break;

case 'categorias/guardar':
    auth(['admin']);
    $db->begin_transaction();
    try {
        chk(q("DELETE FROM categorias"));
        foreach (($d['lista'] ?? []) as $i => $c) {
            chk(q("REPLACE INTO categorias (cat_key,label,descripcion,core,orden) VALUES (?,?,?,?,?)",
              'sssii', [$c['key'], limpiar_texto($c['label'] ?? '', 80), limpiar_texto($c['descripcion'] ?? '', 500), !empty($c['core']) ? 1 : 0, $i + 1]));
        }
        $db->commit();
    } catch (Exception $e) {
        $db->rollback();
        http_response_code(500);
        salida(['ok' => false, 'error' => 'No se pudieron guardar las categorias']);
    }
    break;

case 'reciclados/guardar':
    auth(['admin']);
    chk(q("REPLACE INTO jugadores_reciclados (cat_key,dorsal,data,guardado_en) VALUES (?,?,?,?)",
      'siss', [$d['catKey'], (int)$d['dorsal'], json_encode($d), f_in($d['guardadoEn'] ?? '') ?? hoy_iso()]));
    break;

case 'reciclados/quitar':
    auth(['admin']);
    chk(q("DELETE FROM jugadores_reciclados WHERE cat_key=? AND dorsal=?", 'si', [$d['catKey'], (int)$d['dorsal']]));
    break;

case 'pagosClub/agregar':
    auth(['admin']);
    // El ID SIEMPRE lo genera el servidor (nunca Date.now() del cliente)
    chk(q("INSERT INTO pagos_club (id,padre_id,padre_nombre,concepto,monto,fecha,referencia,metodo,estado) VALUES (?,?,?,?,?,?,?,?,?)",
      'iissdssss', [generar_id(), (int)($d['padreId'] ?? 0), limpiar_texto($d['padreNombre'] ?? '', 150), limpiar_texto($d['concepto'] ?? '', 80),
      (float)($d['monto'] ?? 0), f_in($d['fecha'] ?? '') ?: hoy_iso(), limpiar_texto($d['referencia'] ?? '', 150), $d['metodo'] ?? 'efectivo', $d['estado'] ?? 'validado']));
    break;

case 'comprobantes/agregar':
    auth(['admin', 'padre']);
    if ($_SESSION['ns_rol'] === 'padre') { $d['padreId'] = user_id_actual(); $d['padreNombre'] = $_SESSION['ns_nombre']; }
    // Solo INSERT con ID del servidor: un registro financiero NUNCA se pisa
    chk(q("INSERT INTO comprobantes (id,padre_id,padre_nombre,concepto,monto,fecha,referencia,observaciones,comprobante,estado) VALUES (?,?,?,?,?,?,?,?,?,?)",
      'iissdsssss', [generar_id(), (int)($d['padreId'] ?? 0), limpiar_texto($d['padreNombre'] ?? '', 150), limpiar_texto($d['concepto'] ?? '', 80),
      (float)($d['monto'] ?? 0), f_in($d['fecha'] ?? '') ?: hoy_iso(), limpiar_texto($d['referencia'] ?? '', 150), limpiar_texto($d['observaciones'] ?? '', 2000),
      $d['comprobante'] ?? '', $_SESSION['ns_rol'] === 'padre' ? 'en_revision' : ($d['estado'] ?? 'en_revision')]));
    break;

case 'comprobantes/validar':
    auth(['admin']);
    $aprobar = !empty($d['aprobar']);
    // Transacción atómica: estado del comprobante + pago al club + sincronía de pagos_padre
    $db->begin_transaction();
    try {
        // Solo cambiar si está pendiente/en revisión; si nadie lo modificó, no hubo error real de BD
        $res = q("UPDATE comprobantes SET estado=? WHERE id=? AND estado IN ('pendiente','en_revision')", 'si', [$aprobar ? 'validado' : 'rechazado', (int)$d['id']]);
        chk($res);
        if (empty($res['affected_rows'])) {
            $db->rollback();
            salida(['ok' => false, 'error' => 'El comprobante ya fue procesado o no existe']);
        }
        if ($aprobar && isset($d['pago'])) {
            $p = $d['pago'];
            // El ID del pago SIEMPRE lo genera el servidor para evitar colisiones
            $pid = generar_id();
            chk(q("INSERT INTO pagos_club (id,padre_id,padre_nombre,concepto,monto,fecha,referencia,metodo,estado) VALUES (?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE monto=VALUES(monto), estado='validado'",
              'iissdssss', [$pid, (int)($p['padreId'] ?? 0), limpiar_texto($p['padreNombre'] ?? '', 150), limpiar_texto($p['concepto'] ?? '', 80),
              (float)($p['monto'] ?? 0), f_in($p['fecha'] ?? '') ?? hoy_iso(), limpiar_texto($p['referencia'] ?? '', 150), 'digital', 'validado']));
            chk(q("UPDATE pagos_padre SET estado='validado' WHERE padre_id=? AND referencia=? AND estado='pendiente'",
              'is', [(int)($p['padreId'] ?? 0), $p['referencia'] ?? '']));
        }
        $db->commit();
    } catch (Exception $e) {
        $db->rollback();
        http_response_code(500);
        salida(['ok' => false, 'error' => 'No se pudo validar el comprobante']);
    }
    break;

case 'pagosPadre/agregar':
    auth(['admin', 'padre']);
    if ($_SESSION['ns_rol'] === 'padre') { $d['padreId'] = user_id_actual(); }
    if (empty($d['id']) || (int)$d['id'] <= 0) { $d['id'] = generar_id(); }
    chk(q("INSERT INTO pagos_padre (id,padre_id,concepto,monto,fecha,referencia,observaciones,comprobante,estado) VALUES (?,?,?,?,?,?,?,?,?)",
      'iisdsssss', [(int)$d['id'], (int)$d['padreId'], limpiar_texto($d['concepto'] ?? '', 80), (float)($d['monto'] ?? 0),
      f_in($d['fecha'] ?? '') ?? hoy_iso(), limpiar_texto($d['referencia'] ?? '', 150), limpiar_texto($d['observaciones'] ?? '', 2000), $d['comprobante'] ?? '', $d['estado'] ?? 'en_revision']));
    break;

case 'pagosPlataforma/agregar':
    auth(['admin']);
    if (empty($d['id']) || (int)$d['id'] <= 0) { $d['id'] = generar_id(); }
    chk(q("INSERT INTO pagos_plataforma (id,tipo,monto,referencia,fecha,observaciones,comprobante,estado) VALUES (?,?,?,?,?,?,?,?)",
      'isdsssss', [(int)$d['id'], $d['tipo'] ?? '', (float)($d['monto'] ?? 0), limpiar_texto($d['referencia'] ?? '', 150),
      f_in($d['fecha'] ?? '') ?? hoy_iso(), limpiar_texto($d['observaciones'] ?? '', 2000), $d['comprobante'] ?? '', $d['estado'] ?? 'en_revision']));
    break;

default:
    http_response_code(400);
    salida(['error' => 'Operación desconocida: ' . htmlspecialchars($op, ENT_QUOTES)]);
}
salida(['ok' => true]);
