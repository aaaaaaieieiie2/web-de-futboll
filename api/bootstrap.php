<?php
/* api/bootstrap.php — Devuelve TODA la data de MySQL en un solo JSON */
require __DIR__ . '/../core/conexion.php';
header('Content-Type: application/json; charset=utf-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

$rol = $_SESSION['ns_rol'] ?? 'publico';
$userId = (int)($_SESSION['ns_user_id'] ?? 0);

// Sesión válida solo si el usuario sigue existiendo y no está bloqueado
if ($rol !== 'publico') {
    $chk = q("SELECT rol, bloqueado FROM usuarios WHERE id = ?", "i", [$userId]);
    if (count($chk) === 0 || !empty($chk[0]['bloqueado'])) {
        $_SESSION = [];
        session_destroy();
        $rol = 'publico';
        $userId = 0;
    } else {
        // Sincronizar rol real por si cambió
        $rol = $_SESSION['ns_rol'] = $chk[0]['rol'];
    }
}

$out = [];

/* usuarios (Solo ADMIN ve todos. Público no ve nada.) */
$rows = [];
if ($rol === 'admin') {
    foreach (consultar("SELECT * FROM usuarios ORDER BY id") as $r) {
        $rows[] = ['id' => (int)$r['id'], 'usuario' => $r['usuario'], 'rol' => $r['rol'],
            'nombre' => $r['nombre'], 'hijoCat' => $r['hijo_cat'], 'hijoId' => (int)$r['hijo_id'],
            'catKey' => $r['cat_key'], 'catLabel' => $r['cat_label'], 
            'telefono' => $r['telefono'], 'metodoPago' => $r['metodo_pago'] ?: 'linea', 'fechaVencimiento' => normalizar_fecha($r['fecha_vencimiento'])];
    }
}
$out['usuarios'] = $rows;

/* evaluaciones */
$rows = [];
foreach (consultar("SELECT * FROM evaluaciones") as $r) {
    $rows[] = ['catKey' => $r['cat_key'], 'playerId' => (int)$r['player_id'], 'fecha' => normalizar_fecha($r['fecha']),
        'tecnicos' => json_decode($r['tecnicos'], true) ?: [], 'tacticos' => json_decode($r['tacticos'], true) ?: [],
        'fisicos' => json_decode($r['fisicos'], true) ?: [], 'actitudinales' => json_decode($r['actitudinales'], true) ?: []];
}
$out['evaluaciones'] = $rows;

/* asistencias */
$rows = [];
foreach (consultar("SELECT * FROM asistencias") as $r) {
    $rows[] = ['catKey' => $r['cat_key'], 'fecha' => normalizar_fecha($r['fecha']), 'playerId' => (int)$r['player_id'], 'estado' => $r['estado']];
}
$out['asistencias'] = $rows;

/* configuraciones */
$rows = [];
foreach (consultar("SELECT * FROM configuraciones") as $r) {
    $rows[$r['clave']] = $r['valor'];
}
$out['configuraciones'] = $rows;

/* comentarios_padres (Solo ADMIN ve todos, Padre ve los suyos) */
$rows = [];
if ($rol === 'admin') {
    foreach (consultar("SELECT c.*, u.nombre as padre_nombre FROM comentarios_padres c LEFT JOIN usuarios u ON c.padre_id = u.id ORDER BY c.created_at DESC") as $r) {
        $rows[] = $r;
    }
} else if ($rol === 'padre') {
    foreach (consultar("SELECT c.*, u.nombre as padre_nombre FROM comentarios_padres c LEFT JOIN usuarios u ON c.padre_id = u.id WHERE c.padre_id = $userId ORDER BY c.created_at DESC") as $r) {
        $rows[] = $r;
    }
}
$out['comentarios'] = $rows;

/* encuestas */
$rows = [];
foreach (consultar("SELECT * FROM encuestas ORDER BY id") as $r) {
    $rows[] = ['padre' => $r['padre'], 'fecha' => normalizar_fecha($r['fecha']), 'aspectos' => json_decode($r['aspectos'], true) ?: [],
        'gustaMas' => $r['gusta_mas'], 'mejorar' => $r['mejorar'], 'crecimiento' => $r['crecimiento'],
        'crecimientoWhy' => $r['crecimiento_why'], 'recomienda' => $r['recomienda'],
        'recomiendaWhy' => $r['recomienda_why'], 'observaciones' => $r['observaciones']];
}
$out['encuestas'] = $rows;

/* player_stats */
$rows = [];
foreach (consultar("SELECT * FROM player_stats") as $r) {
    $rows[] = ['catKey' => $r['cat_key'], 'playerId' => (int)$r['player_id'], 'goles' => (int)$r['goles'],
        'asistencias' => (int)$r['asistencias'], 'mvp' => (int)$r['mvp'], 'updatedAt' => $r['updated_at']];
}
$out['playerStats'] = $rows;

/* jugadores -> plantillas por categoría */
$map = [];
foreach (consultar("SELECT * FROM jugadores ORDER BY cat_key, dorsal") as $r) {
    $map[$r['cat_key']][] = ['id' => (int)$r['id'], 'catKey' => $r['cat_key'], 'name' => $r['name'],
        'dorsal' => (int)$r['dorsal'], 'pos' => $r['pos'], 'foto' => $r['foto']];
}
$out['plantillas'] = $map;

/* trofeos */
$rows = [];
foreach (consultar("SELECT * FROM trofeos_custom") as $r) {
    $rows[] = ['id' => $r['id'], 'nombre' => $r['nombre'], 'catKey' => $r['cat_key'], 'categoria' => $r['categoria'],
        'año' => $r['anio'], 'icono' => $r['icono'], 'color' => $r['color'], 'descripcion' => $r['descripcion'],
        'evaluacion' => json_decode($r['evaluacion']) ?: (object)[]];
}
$out['trofeosCustom'] = $rows;
$out['trofeosOcultos'] = array_map(fn($r) => $r['id'], consultar("SELECT id FROM trofeos_ocultos"));
$map = [];
foreach (consultar("SELECT * FROM trofeos_editados") as $r) $map[$r['id']] = json_decode($r['data']);
$out['trofeosEditados'] = (object)$map;

/* textos y documentos */
$map = [];
    foreach (consultar("SELECT * FROM textos") as $r) $map[$r['clave']] = sanitizar_html($r['html']);
$out['textos'] = (object)$map;
// Sanitizar el HTML/texto embebido en trofeos custom y editados (anti-XSS almacenado)
foreach ($rows as &$t) { if (isset($t['descripcion'])) $t['descripcion'] = sanitizar_html((string)$t['descripcion']); }
unset($t);
foreach ($map as &$t2) { if (is_object($t2) && isset($t2->descripcion)) $t2->descripcion = sanitizar_html((string)$t2->descripcion); }
unset($t2);
$docs = [];
foreach (consultar("SELECT * FROM documentos") as $r) $docs[$r['doc_key']] = $r['value'];
$out['torneo'] = isset($docs['torneo']) ? json_decode($docs['torneo']) : null;
$out['evalGlobal'] = isset($docs['eval_global']) ? json_decode($docs['eval_global']) : null;

/* staff y categorías */
$rows = [];
foreach (consultar("SELECT * FROM staff ORDER BY orden") as $r) {
    $rows[] = ['id' => (int)$r['id'], 'nombre' => $r['nombre'], 'cargo' => $r['cargo'], 'licencia' => $r['licencia'],
        'exp' => $r['exp'], 'especialidad' => $r['especialidad'], 'bio' => $r['bio'],
        'fotoIcon' => $r['foto_icon'], 'color' => $r['color']];
}
$out['staff'] = count($rows) ? $rows : null;
$rows = [];
foreach (consultar("SELECT * FROM categorias ORDER BY orden") as $r) {
    $rows[] = ['id' => (int)$r['id'], 'key' => $r['cat_key'], 'label' => $r['label'], 'descripcion' => $r['descripcion'], 'core' => (int)$r['core'], 'foto' => $r['foto'] ?? ''];
}
$out['categorias'] = count($rows) ? $rows : null;

/* papelera */
$rows = [];
if ($rol === 'admin') {
    foreach (consultar("SELECT * FROM jugadores_reciclados") as $r) {
        $p = json_decode($r['data']) ?: (object)[]; $p->guardadoEn = $r['guardado_en']; $rows[] = $p;
    }
}
$out['jugadoresReciclados'] = $rows;

/* pagos */
$rowsClub = []; $rowsPadre = []; $rowsComprobantes = []; $rowsPlataforma = [];

if ($rol === 'admin') {
    foreach (consultar("SELECT * FROM pagos_club ORDER BY id") as $r) {
        $rowsClub[] = ['id' => (int)$r['id'], 'padreId' => (int)$r['padre_id'], 'padreNombre' => $r['padre_nombre'],
            'concepto' => $r['concepto'], 'monto' => (float)$r['monto'], 'fecha' => normalizar_fecha($r['fecha']),
            'referencia' => $r['referencia'], 'metodo' => $r['metodo'], 'estado' => $r['estado']];
    }
    foreach (consultar("SELECT * FROM comprobantes ORDER BY id") as $r) {
        $rowsComprobantes[] = ['id' => (string)$r['id'], 'padreId' => (int)$r['padre_id'], 'padreNombre' => $r['padre_nombre'],
            'concepto' => $r['concepto'], 'monto' => (float)$r['monto'], 'fecha' => normalizar_fecha($r['fecha']), 'referencia' => $r['referencia'],
            'observaciones' => $r['observaciones'], 'comprobante' => $r['comprobante'], 'estado' => $r['estado']];
    }
    foreach (consultar("SELECT * FROM pagos_padre ORDER BY id") as $r) {
        $rowsPadre[] = ['id' => (int)$r['id'], 'padreId' => (int)$r['padre_id'], 'concepto' => $r['concepto'],
            'monto' => (float)$r['monto'], 'fecha' => normalizar_fecha($r['fecha']), 'referencia' => $r['referencia'],
            'observaciones' => $r['observaciones'], 'comprobante' => $r['comprobante'], 'estado' => $r['estado']];
    }
    foreach (consultar("SELECT * FROM pagos_plataforma ORDER BY id") as $r) {
        $rowsPlataforma[] = ['id' => (int)$r['id'], 'tipo' => $r['tipo'], 'monto' => (float)$r['monto'], 'referencia' => $r['referencia'],
            'fecha' => normalizar_fecha($r['fecha']), 'observaciones' => $r['observaciones'], 'comprobante' => $r['comprobante'], 'estado' => $r['estado']];
    }
} else if ($rol === 'padre') {
    foreach (consultar("SELECT * FROM comprobantes WHERE padre_id = " . $userId . " ORDER BY id") as $r) {
        $rowsComprobantes[] = ['id' => (string)$r['id'], 'padreId' => (int)$r['padre_id'], 'padreNombre' => $r['padre_nombre'],
            'concepto' => $r['concepto'], 'monto' => (float)$r['monto'], 'fecha' => normalizar_fecha($r['fecha']), 'referencia' => $r['referencia'],
            'observaciones' => $r['observaciones'], 'comprobante' => $r['comprobante'], 'estado' => $r['estado']];
    }
    foreach (consultar("SELECT * FROM pagos_padre WHERE padre_id = $userId ORDER BY id") as $r) {
        $rowsPadre[] = ['id' => (int)$r['id'], 'padreId' => (int)$r['padre_id'], 'concepto' => $r['concepto'],
            'monto' => (float)$r['monto'], 'fecha' => normalizar_fecha($r['fecha']), 'referencia' => $r['referencia'],
            'observaciones' => $r['observaciones'], 'comprobante' => $r['comprobante'], 'estado' => $r['estado']];
    }
}

$out['pagosClub'] = $rowsClub;
$out['comprobantes'] = $rowsComprobantes;
$out['pagosPadre'] = $rowsPadre;
$out['pagosPlataforma'] = $rowsPlataforma;

salida($out);
