<?php
/* api/bootstrap.php — Devuelve la data de MySQL filtrada por rol (PDO + OWASP) */
require_once __DIR__ . '/../core/conexion.php';
header('Content-Type: application/json; charset=utf-8');
header('X-Robots-Tag: noindex, nofollow');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");

$rol = $_SESSION['ns_rol'] ?? 'publico';
$userId = (int)($_SESSION['ns_user_id'] ?? 0);

$u = null;
if ($userId > 0) {
    $u = buscarUsuarioGlobalPorId($userId, $rol);
    if ($u && isset($u['bloqueado']) && (int)$u['bloqueado'] === 1) {
        $_SESSION = [];
        session_destroy();
        http_response_code(403);
        echo json_encode(['error' => 'Cuenta suspendida']);
        exit;
    }
}

$out = [];

/* usuarios (Admin/Entrenadores en 'usuarios' + Padres en 'padres_<subX>') */
$todosUsuarios = obtenerTodosLosUsuariosUnificados();
$mapaNombresPadres = [];
foreach ($todosUsuarios as $usr) {
    $mapaNombresPadres[(int)$usr['id']] = $usr['nombre'];
}
$out['usuarios'] = ($rol === 'admin') ? $todosUsuarios : [];

/* evaluaciones (Protegidas: solo Admin/Entrenador o Padre para sus propios hijos) */
$rows = [];
$hijosIds = [];
if ($rol === 'padre' && $userId > 0 && $u) {
    if (!empty($u['hijos']) && is_array($u['hijos'])) {
        foreach ($u['hijos'] as $h) {
            if (!empty($h['id'])) $hijosIds[] = (int)$h['id'];
        }
    }
    if (!empty($u['hijo_id'])) {
        $hijosIds[] = (int)$u['hijo_id'];
    }
    $hijosIds = array_values(array_unique(array_filter($hijosIds)));
}

if ($rol === 'admin' || $rol === 'entrenador') {
    foreach (consultar("SELECT * FROM evaluaciones") as $r) {
        $rows[] = [
            'catKey'        => $r['cat_key'],
            'playerId'      => (int)$r['player_id'],
            'fecha'         => $r['fecha'],
            'tecnicos'      => json_decode($r['tecnicos'], true) ?: [],
            'tacticos'      => json_decode($r['tacticos'], true) ?: [],
            'fisicos'       => json_decode($r['fisicos'], true) ?: [],
            'actitudinales' => json_decode($r['actitudinales'], true) ?: []
        ];
    }
} elseif ($rol === 'padre' && !empty($hijosIds)) {
    $inClause = implode(',', array_fill(0, count($hijosIds), '?'));
    $stmtEval = pdo()->prepare("SELECT * FROM evaluaciones WHERE player_id IN ($inClause)");
    $stmtEval->execute($hijosIds);
    foreach ($stmtEval->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $rows[] = [
            'catKey'        => $r['cat_key'],
            'playerId'      => (int)$r['player_id'],
            'fecha'         => $r['fecha'],
            'tecnicos'      => json_decode($r['tecnicos'], true) ?: [],
            'tacticos'      => json_decode($r['tacticos'], true) ?: [],
            'fisicos'       => json_decode($r['fisicos'], true) ?: [],
            'actitudinales' => json_decode($r['actitudinales'], true) ?: []
        ];
    }
}
$out['evaluaciones'] = $rows;

/* asistencias (Protegidas: solo Admin/Entrenador o Padre para sus propios hijos) */
$rows = [];
if ($rol === 'admin' || $rol === 'entrenador') {
    foreach (consultar("SELECT * FROM asistencias") as $r) {
        $rows[] = [
            'catKey'   => $r['cat_key'],
            'fecha'    => $r['fecha'],
            'playerId' => (int)$r['player_id'],
            'estado'   => $r['estado']
        ];
    }
} elseif ($rol === 'padre' && !empty($hijosIds)) {
    $inClause = implode(',', array_fill(0, count($hijosIds), '?'));
    $stmtAsist = pdo()->prepare("SELECT * FROM asistencias WHERE player_id IN ($inClause)");
    $stmtAsist->execute($hijosIds);
    foreach ($stmtAsist->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $rows[] = [
            'catKey'   => $r['cat_key'],
            'fecha'    => $r['fecha'],
            'playerId' => (int)$r['player_id'],
            'estado'   => $r['estado']
        ];
    }
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
    foreach (consultar("SELECT * FROM comentarios_padres ORDER BY created_at DESC") as $r) {
        $r['padre_nombre'] = $mapaNombresPadres[(int)($r['padre_id'] ?? 0)] ?? 'Familia';
        $rows[] = $r;
    }
} elseif ($rol === 'padre') {
    foreach (q("SELECT * FROM comentarios_padres WHERE padre_id = ? ORDER BY created_at DESC", "i", [$userId]) as $r) {
        $r['padre_nombre'] = $mapaNombresPadres[(int)($r['padre_id'] ?? 0)] ?? 'Familia';
        $rows[] = $r;
    }
}
$out['comentarios'] = $rows;

/* encuestas (Solo ADMIN ve resultados de encuestas) */
$rows = [];
if ($rol === 'admin') {
    foreach (consultar("SELECT * FROM encuestas ORDER BY id") as $r) {
        $rows[] = [
            'padre'         => $r['padre'],
            'fecha'         => $r['fecha'],
            'aspectos'      => json_decode($r['aspectos'], true) ?: [],
            'gustaMas'      => $r['gusta_mas'],
            'mejorar'       => $r['mejorar'],
            'crecimiento'   => $r['crecimiento'],
            'crecimientoWhy'=> $r['crecimiento_why'],
            'recomienda'    => $r['recomienda'],
            'recomiendaWhy' => $r['recomienda_why'],
            'observaciones' => $r['observaciones']
        ];
    }
}
$out['encuestas'] = $rows;

/* player_stats y jugadores divididos por tabla de cada Sub */
/* Sincronización defensiva: asegurar que todo hijo vinculado a padres exista en su plantilla */
foreach ($todosUsuarios as $usr) {
    if (($usr['rol'] ?? '') === 'padre') {
        $hijosAChequear = !empty($usr['hijos']) && is_array($usr['hijos']) ? $usr['hijos'] : [];
        if (empty($hijosAChequear) && !empty($usr['hijoId'])) {
            $hijosAChequear[] = [
                'id' => (int)$usr['hijoId'],
                'nombre' => $usr['hijoNombre'] ?? '',
                'catKey' => $usr['hijoCat'] ?? 'sub6',
                'pos' => $usr['hijoPos'] ?? 'MED',
                'edad' => (int)($usr['hijoEdad'] ?? 0),
                'dorsal' => (int)($usr['hijoDorsal'] ?? 0)
            ];
        }
        foreach ($hijosAChequear as $hChk) {
            $cKey = slugTablaCat($hChk['catKey'] ?? 'sub6');
            $hId = (int)($hChk['id'] ?? 0);
            if ($hId > 0 && !empty($hChk['nombre'])) {
                [$tJ] = asegurarTablasCategoria($cKey);
                $yaExiste = q("SELECT id FROM `$tJ` WHERE id = ? LIMIT 1", 'i', [$hId]);
                if (empty($yaExiste)) {
                    q("INSERT INTO `$tJ` (id, cat_key, name, edad, dorsal, pos, foto) VALUES (?, ?, ?, ?, ?, ?, ?)",
                      'issiiss', [$hId, $cKey, (string)$hChk['nombre'], (int)($hChk['edad'] ?? 0), (int)($hChk['dorsal'] ?? 0), (string)($hChk['pos'] ?? 'MED'), 'img/escudo.png']);
                    sincronizarStatsCategoria($cKey);
                }
            }
        }
    }
}

$statsRows = [];
$plantillasMap = [];
foreach (obtenerCategoriasClaves() as $catKey) {
    [$tJug, $tStats] = array_slice(asegurarTablasCategoria($catKey), 0, 2);
    $plantillasMap[$catKey] = [];
    foreach (consultar("SELECT * FROM `$tJug` ORDER BY dorsal, id") as $r) {
        $plantillasMap[$catKey][] = [
            'id'     => is_numeric($r['id']) ? (float)$r['id'] : $r['id'],
            'catKey' => $catKey,
            'name'   => $r['name'],
            'edad'   => (int)($r['edad'] ?? 0),
            'dorsal' => (int)$r['dorsal'],
            'pos'    => $r['pos'],
            'foto'   => $r['foto']
        ];
    }
    foreach (consultar("SELECT * FROM `$tStats` ORDER BY dorsal, player_id") as $s) {
        $statsRows[] = [
            'catKey'      => $catKey,
            'playerId'    => is_numeric($s['player_id']) ? (float)$s['player_id'] : $s['player_id'],
            'goles'       => (int)$s['goles'],
            'asistencias' => (int)$s['asistencias'],
            'mvp'         => (int)$s['mvp'],
            'updatedAt'   => $s['updated_at']
        ];
    }
}
$out['playerStats'] = $statsRows;
$out['plantillas'] = $plantillasMap;

/* trofeos */
$rows = [];
foreach (consultar("SELECT * FROM trofeos_custom") as $r) {
    $rows[] = [
        'id'          => $r['id'],
        'nombre'      => $r['nombre'],
        'catKey'      => $r['cat_key'],
        'categoria'   => $r['categoria'],
        'año'         => $r['anio'],
        'icono'       => $r['icono'],
        'color'       => $r['color'],
        'descripcion' => $r['descripcion'],
        'evaluacion'  => json_decode($r['evaluacion']) ?: (object)[]
    ];
}
$out['trofeosCustom'] = $rows;
$out['trofeosOcultos'] = array_map(fn($r) => $r['id'], consultar("SELECT id FROM trofeos_ocultos"));
$map = [];
foreach (consultar("SELECT * FROM trofeos_editados") as $r) $map[$r['id']] = json_decode($r['data']);
$out['trofeosEditados'] = (object)$map;

/* textos y documentos */
$map = [];
foreach (consultar("SELECT * FROM textos") as $r) $map[$r['clave']] = $r['html'];
$out['textos'] = (object)$map;
$docs = [];
foreach (consultar("SELECT * FROM documentos") as $r) $docs[$r['doc_key']] = $r['value'];
$out['torneo'] = isset($docs['torneo']) ? json_decode($docs['torneo']) : null;
$out['evalGlobal'] = isset($docs['eval_global']) ? json_decode($docs['eval_global']) : null;
if ($rol === 'admin') {
    $out['registroLinkTemp'] = obtenerOGenerarTokenCiclo7Dias();
}

/* staff y categorías */
$rows = [];
foreach (consultar("SELECT * FROM staff ORDER BY orden") as $r) {
    $rows[] = [
        'id'           => (int)$r['id'],
        'nombre'       => decodificarEntidadesRecursivas($r['nombre']),
        'cargo'        => decodificarEntidadesRecursivas($r['cargo']),
        'licencia'     => decodificarEntidadesRecursivas($r['licencia']),
        'exp'          => decodificarEntidadesRecursivas($r['exp']),
        'especialidad' => decodificarEntidadesRecursivas($r['especialidad']),
        'bio'          => decodificarEntidadesRecursivas($r['bio']),
        'fotoIcon'     => $r['foto_icon'],
        'color'        => $r['color']
    ];
}
$out['staff'] = count($rows) ? $rows : null;
$rows = [];
foreach (consultar("SELECT * FROM categorias ORDER BY orden") as $r) {
    $rows[] = [
        'id'          => (int)$r['id'],
        'key'         => $r['cat_key'],
        'label'       => $r['label'],
        'descripcion' => $r['descripcion'],
        'core'        => (int)$r['core'],
        'foto'        => $r['foto'] ?? ''
    ];
}
$out['categorias'] = count($rows) ? $rows : null;

/* papelera */
$rows = [];
if ($rol === 'admin') {
    foreach (consultar("SELECT * FROM jugadores_reciclados") as $r) {
        $p = json_decode($r['data']) ?: (object)[];
        $p->guardadoEn = $r['guardado_en'];
        $rows[] = $p;
    }
}
$out['jugadoresReciclados'] = $rows;

/* pagos */
$rowsClub = []; $rowsPadre = []; $rowsComprobantes = []; $rowsPlataforma = [];

if ($rol === 'admin') {
    foreach (consultar("SELECT * FROM pagos_club ORDER BY id") as $r) {
        $rowsClub[] = [
            'id'          => (int)$r['id'],
            'padreId'     => (int)$r['padre_id'],
            'padreNombre' => $r['padre_nombre'],
            'concepto'    => $r['concepto'],
            'monto'       => (float)$r['monto'],
            'fecha'       => $r['fecha'],
            'referencia'  => $r['referencia'],
            'metodo'      => $r['metodo'],
            'estado'      => $r['estado']
        ];
    }
    foreach (consultar("SELECT * FROM comprobantes ORDER BY id") as $r) {
        $rowsComprobantes[] = [
            'id'            => (int)$r['id'],
            'padreId'       => (int)$r['padre_id'],
            'padreNombre'   => $r['padre_nombre'],
            'concepto'      => $r['concepto'],
            'monto'         => (float)$r['monto'],
            'fecha'         => $r['fecha'],
            'referencia'    => $r['referencia'],
            'observaciones' => $r['observaciones'],
            'comprobante'   => $r['comprobante'],
            'estado'        => $r['estado']
        ];
    }
    foreach (consultar("SELECT * FROM pagos_padre ORDER BY id") as $r) {
        $rowsPadre[] = [
            'id'            => (int)$r['id'],
            'padreId'       => (int)$r['padre_id'],
            'concepto'      => $r['concepto'],
            'monto'         => (float)$r['monto'],
            'fecha'         => $r['fecha'],
            'referencia'    => $r['referencia'],
            'observaciones' => $r['observaciones'],
            'comprobante'   => $r['comprobante'],
            'estado'        => $r['estado']
        ];
    }
    foreach (consultar("SELECT * FROM pagos_plataforma ORDER BY id") as $r) {
        $rowsPlataforma[] = [
            'id'            => (int)$r['id'],
            'tipo'          => $r['tipo'],
            'monto'         => (float)$r['monto'],
            'referencia'    => $r['referencia'],
            'fecha'         => $r['fecha'],
            'observaciones' => $r['observaciones'],
            'comprobante'   => $r['comprobante'],
            'estado'        => $r['estado']
        ];
    }
} elseif ($rol === 'padre') {
    foreach (q("SELECT * FROM comprobantes WHERE padre_id = ? ORDER BY id", "i", [$userId]) as $r) {
        $rowsComprobantes[] = [
            'id'            => (int)$r['id'],
            'padreId'       => (int)$r['padre_id'],
            'padreNombre'   => $r['padre_nombre'],
            'concepto'      => $r['concepto'],
            'monto'         => (float)$r['monto'],
            'fecha'         => $r['fecha'],
            'referencia'    => $r['referencia'],
            'observaciones' => $r['observaciones'],
            'comprobante'   => $r['comprobante'],
            'estado'        => $r['estado']
        ];
    }
    foreach (q("SELECT * FROM pagos_padre WHERE padre_id = ? ORDER BY id", "i", [$userId]) as $r) {
        $rowsPadre[] = [
            'id'            => (int)$r['id'],
            'padreId'       => (int)$r['padre_id'],
            'concepto'      => $r['concepto'],
            'monto'         => (float)$r['monto'],
            'fecha'         => $r['fecha'],
            'referencia'    => $r['referencia'],
            'observaciones' => $r['observaciones'],
            'comprobante'   => $r['comprobante'],
            'estado'        => $r['estado']
        ];
    }
}

$out['pagosClub'] = $rowsClub;
$out['comprobantes'] = $rowsComprobantes;
$out['pagosPadre'] = $rowsPadre;
$out['pagosPlataforma'] = $rowsPlataforma;
$out['csrf_token'] = $_SESSION['csrf_token'] ?? '';

salida($out);