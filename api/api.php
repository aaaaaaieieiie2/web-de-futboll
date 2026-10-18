<?php
/* api/api.php — Endpoint de escrituras endurecido (PDO + Anti-CSRF + Anti-IDOR + Anti-XSS) */
require_once __DIR__ . '/../core/conexion.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    salida(['status' => 'error', 'mensaje' => 'Método no permitido. Use POST.'], 405);
}

$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token) || !verificarCsrfToken($token)) {
    salida(['status' => 'error', 'mensaje' => 'Token CSRF inválido o ausente'], 403);
}

$d = entrada();
$pdoConn = pdo();
$op = isset($_GET['op']) ? (string)$_GET['op'] : '';

function auth(array $roles): void {
    if (!isset($_SESSION['ns_rol']) || !in_array($_SESSION['ns_rol'], $roles, true)) {
        salida(['status' => 'error', 'mensaje' => 'HTTP 403 Forbidden - No autorizado'], 403);
    }
    if (isset($_SESSION['ns_user_id'])) {
        $u = buscarUsuarioGlobalPorId((int)$_SESSION['ns_user_id'], $_SESSION['ns_rol'] ?? null);
        if ($u) {
            if (isset($u['bloqueado']) && (int)$u['bloqueado'] === 1) {
                $_SESSION = [];
                session_destroy();
                salida(['status' => 'error', 'mensaje' => 'Cuenta suspendida'], 403);
            }
            if ($_SESSION['ns_rol'] === 'entrenador' && !empty($u['cat_key'])) {
                $_SESSION['ns_cat_key'] = $u['cat_key'];
            }
        }
    }
}

function obtenerCatKeyAutorizada(?string $catKeySolicitada): string {
    $cat = sanitizarTexto($catKeySolicitada ?? '');
    if (($_SESSION['ns_rol'] ?? '') === 'entrenador') {
        $catAsignada = $_SESSION['ns_cat_key'] ?? '';
        if ($catAsignada === '' || ($cat !== '' && $cat !== $catAsignada)) {
            salida(['status' => 'error', 'mensaje' => 'No tienes permisos para modificar otra categoría'], 403);
        }
        return $catAsignada;
    }
    return $cat;
}

function esSuperAdminSesion(): bool {
    if (empty($_SESSION['ns_rol']) || $_SESSION['ns_rol'] !== 'admin') return false;
    $id = (int)($_SESSION['ns_user_id'] ?? 0);
    $usuario = strtolower((string)($_SESSION['ns_usuario'] ?? ''));
    if ($id === 1 || $usuario === 'admin') return true;
    $rows = q("SELECT es_top FROM usuarios WHERE id = ? AND rol = 'admin' LIMIT 1", 'i', [$id]);
    return !empty($rows) && (int)($rows[0]['es_top'] ?? 0) === 1;
}

switch ($op) {

case 'usuarios/guardar':
    auth(['admin']);
    $passRaw = trim((string)($d['password'] ?? ''));
    $rolNuevo = in_array($d['rol'] ?? '', ['admin', 'entrenador', 'padre'], true) ? $d['rol'] : 'padre';
    if (in_array($rolNuevo, ['padre', 'entrenador'], true) && $passRaw !== '') {
        $errPass = validarPasswordSegura12($passRaw);
        if ($errPass !== null) {
            salida(['ok' => false, 'error' => $errPass], 400);
        }
    }
    $passHash = $passRaw !== '' ? password_hash($passRaw, PASSWORD_BCRYPT) : '';

    if ($rolNuevo === 'admin' || $rolNuevo === 'entrenador') {
        $idUsr = !empty($d['id']) ? (int)$d['id'] : siguienteUsuarioIdGlobal();
        $catK = !empty($d['catKey']) ? slugTablaCat(sanitizarTexto($d['catKey'])) : ($rolNuevo === 'admin' ? 'todas' : '');
        $mapaLabels = obtenerMapaLabelsCategorias();
        $catL = !empty($d['catLabel']) ? sanitizarTexto($d['catLabel']) : ($mapaLabels[$catK] ?? ($rolNuevo === 'admin' ? 'Administración General' : strtoupper($catK)));
        $tel = sanitizarTexto($d['telefono'] ?? '');
        $catsRaw = is_array($d['categorias'] ?? null) ? $d['categorias'] : (!empty($d['catKey']) ? [$d['catKey']] : []);
        $catsJson = json_encode(array_values(array_unique(array_map('sanitizarTexto', $catsRaw))), JSON_UNESCAPED_UNICODE);
        q("INSERT INTO usuarios (id,usuario,password,rol,nombre,cat_key,cat_label,telefono,categorias,bloqueado)
           VALUES (?,?,?,?,?,?,?,?,?,0)
           ON DUPLICATE KEY UPDATE
             password=IF(VALUES(password)='', password, VALUES(password)),
             rol=VALUES(rol), nombre=VALUES(nombre), cat_key=VALUES(cat_key), cat_label=VALUES(cat_label),
             telefono=VALUES(telefono), categorias=VALUES(categorias)",
          'issssssss', [
              $idUsr,
              sanitizarTexto($d['usuario'] ?? ''),
              $passHash,
              $rolNuevo,
              sanitizarTexto($d['nombre'] ?? ''),
              $catK,
              $catL,
              $tel,
              $catsJson
          ]);
    } else {
        $catPadre = slugTablaCat(sanitizarTexto($d['hijoCat'] ?? $d['catKey'] ?? 'sub6'));
        [$tJug, , $tPadres] = asegurarTablasCategoria($catPadre);
        $mapaLabels = obtenerMapaLabelsCategorias();
        $labelSub = $mapaLabels[$catPadre] ?? strtoupper($catPadre);
        $idPadre = !empty($d['id']) ? (int)$d['id'] : siguienteUsuarioIdGlobal();
        $hijoId = (float)($d['hijoId'] ?? 0);
        $hijoNombre = sanitizarTexto($d['hijoNombre'] ?? '');
        $hijoDorsal = (int)($d['hijoDorsal'] ?? 0);
        $hijoPos = sanitizarTexto($d['hijoPos'] ?? 'MED');
        $hijoEdad = (int)($d['hijoEdad'] ?? 0);
        if ($hijoId > 0) {
            $jInfo = q("SELECT name, dorsal, pos, edad FROM `$tJug` WHERE id = ?", 'i', [$hijoId]);
            if (!empty($jInfo)) {
                $hijoNombre = (string)$jInfo[0]['name'];
                $hijoDorsal = (int)$jInfo[0]['dorsal'];
                $hijoPos    = (string)$jInfo[0]['pos'];
                if ($hijoEdad === 0 && !empty($jInfo[0]['edad'])) {
                    $hijoEdad = (int)$jInfo[0]['edad'];
                }
            }
        }
        $hijosArr = !empty($d['hijos']) && is_array($d['hijos']) ? $d['hijos'] : [];
        if (empty($hijosArr) && $hijoId > 0) {
            $hijosArr = [[
                'id'       => $hijoId,
                'nombre'   => $hijoNombre,
                'edad'     => $hijoEdad,
                'catKey'   => $catPadre,
                'sub'      => $labelSub,
                'catLabel' => $labelSub,
                'dorsal'   => $hijoDorsal,
                'pos'      => $hijoPos
            ]];
        }

        try {
            $pdoConn->beginTransaction();

            // 1. Inserción atómica y explícita de CADA hijo en la tabla de jugadores de su respectiva categoría
            foreach ($hijosArr as &$hItem) {
                $hId = (float)($hItem['id'] ?? 0);
                if ($hId <= 0) {
                    $hId = siguienteJugadorIdGlobal();
                    $hItem['id'] = $hId;
                }
                $hCat = slugTablaCat($hItem['catKey'] ?? $catPadre);
                [$tJugH, $tStatsH] = array_slice(asegurarTablasCategoria($hCat), 0, 2);
                $hNom = sanitizarTexto($hItem['nombre'] ?? 'Jugador');
                $hEd = max(0, (int)($hItem['edad'] ?? 0));
                $hDor = (int)($hItem['dorsal'] ?? 0);
                $hPo = in_array(strtoupper($hItem['pos'] ?? ''), ['POR', 'DEF', 'MED', 'DEL'], true) ? strtoupper($hItem['pos']) : 'MED';
                $hItem['pos'] = $hPo;
                $hItem['dorsal'] = $hDor;

                $stInsJ = $pdoConn->prepare("INSERT INTO `$tJugH` (id, cat_key, name, edad, dorsal, pos, foto)
                   VALUES (?, ?, ?, ?, ?, ?, 'img/escudo.png')
                   ON DUPLICATE KEY UPDATE
                     name = VALUES(name), edad = VALUES(edad), pos = VALUES(pos)");
                $stInsJ->execute([$hId, $hCat, $hNom, $hEd, $hDor, $hPo]);

                $stInsS = $pdoConn->prepare("INSERT IGNORE INTO `$tStatsH` (player_id, dorsal, nombre, cat_key, goles, asistencias, mvp)
                   VALUES (?, ?, ?, ?, 0, 0, 0)");
                $stInsS->execute([$hId, $hDor, $hNom, $hCat]);

                sincronizarStatsCategoria($hCat);
            }
            unset($hItem);

            $hijosJson = !empty($hijosArr) ? json_encode($hijosArr, JSON_UNESCAPED_UNICODE) : null;
            $venc30 = calcularFechaVencimiento30($d['fechaVencimiento'] ?? null);
            $usuarioNombre = sanitizarTexto(!empty($d['usuario']) ? $d['usuario'] : ($d['nombre'] ?? ''));
            $nombreCompleto = sanitizarTexto($d['nombre'] ?? (!empty($d['usuario']) ? $d['usuario'] : ''));
            $telefono = sanitizarTexto($d['telefono'] ?? '');
            $metodoPago = sanitizarTexto($d['metodoPago'] ?? 'linea');

            if (!empty($hijosArr[0])) {
                $hijoId = (float)$hijosArr[0]['id'];
                $hijoNombre = sanitizarTexto($hijosArr[0]['nombre']);
                $hijoEdad = (int)$hijosArr[0]['edad'];
                $hijoDorsal = (int)$hijosArr[0]['dorsal'];
                $hijoPos = sanitizarTexto($hijosArr[0]['pos']);
            }

            // 2. Guardar padre en la tabla principal
            $stInsP = $pdoConn->prepare("INSERT INTO `$tPadres` (id,usuario,nombre,telefono,sub,hijo_cat,hijo_id,hijo_nombre,hijo_edad,hijo_dorsal,hijo_pos,hijos_json,metodo_pago,fecha_vencimiento,bloqueado,password)
               VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,0,?)
               ON DUPLICATE KEY UPDATE
                 password=IF(VALUES(password)='', password, VALUES(password)),
                 nombre=VALUES(nombre), telefono=VALUES(telefono), sub=VALUES(sub), hijo_cat=VALUES(hijo_cat),
                 hijo_id=VALUES(hijo_id), hijo_nombre=VALUES(hijo_nombre), hijo_edad=VALUES(hijo_edad), hijo_dorsal=VALUES(hijo_dorsal), hijo_pos=VALUES(hijo_pos),
                 hijos_json=VALUES(hijos_json), metodo_pago=VALUES(metodo_pago), fecha_vencimiento=VALUES(fecha_vencimiento)");
            $stInsP->execute([
                $idPadre,
                $usuarioNombre,
                $nombreCompleto,
                $telefono,
                $labelSub,
                $catPadre,
                $hijoId,
                $hijoNombre,
                $hijoEdad,
                $hijoDorsal,
                $hijoPos,
                $hijosJson,
                $metodoPago,
                $venc30,
                $passHash
            ]);

            // 3. Replicar usuario con su hijos_json consolidado en otras categorías donde tenga hijos
            if (!empty($hijosArr)) {
                foreach ($hijosArr as $hExtra) {
                    $cExtra = slugTablaCat($hExtra['catKey'] ?? '');
                    if ($cExtra !== '' && $cExtra !== $catPadre) {
                        [$tJExtra, , $tPExtra] = asegurarTablasCategoria($cExtra);
                        $labelExtra = $mapaLabels[$cExtra] ?? strtoupper($cExtra);
                        $stInsPE = $pdoConn->prepare("INSERT INTO `$tPExtra` (id,usuario,nombre,telefono,sub,hijo_cat,hijo_id,hijo_nombre,hijo_edad,hijo_dorsal,hijo_pos,hijos_json,metodo_pago,fecha_vencimiento,bloqueado,password)
                           VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,0,?)
                           ON DUPLICATE KEY UPDATE
                             password=IF(VALUES(password)='', password, VALUES(password)),
                             nombre=VALUES(nombre), telefono=VALUES(telefono), sub=VALUES(sub), hijo_cat=VALUES(hijo_cat),
                             hijo_id=VALUES(hijo_id), hijo_nombre=VALUES(hijo_nombre), hijo_edad=VALUES(hijo_edad), hijo_dorsal=VALUES(hijo_dorsal), hijo_pos=VALUES(hijo_pos),
                             hijos_json=VALUES(hijos_json), metodo_pago=VALUES(metodo_pago), fecha_vencimiento=VALUES(fecha_vencimiento)");
                        $stInsPE->execute([
                            $idPadre,
                            $usuarioNombre,
                            $nombreCompleto,
                            $telefono,
                            $labelExtra,
                            $cExtra,
                            (float)($hExtra['id'] ?? 0),
                            sanitizarTexto($hExtra['nombre'] ?? ''),
                            (int)($hExtra['edad'] ?? 0),
                            (int)($hExtra['dorsal'] ?? 0),
                            sanitizarTexto($hExtra['pos'] ?? 'MED'),
                            $hijosJson,
                            $metodoPago,
                            $venc30,
                            $passHash
                        ]);
                    }
                }
            }

            $pdoConn->commit();
        } catch (Exception $e) {
            if ($pdoConn->inTransaction()) $pdoConn->rollBack();
            error_log('[Transaction Error usuarios/guardar] ' . $e->getMessage());
            salida(['ok' => false, 'error' => 'Error al registrar familia y jugadores en la base de datos'], 500);
        }
    }
    break;

case 'usuarios/actualizarPago':
    auth(['admin']);
    $idPadre = (int)($d['id'] ?? 0);
    $esDeshacer = !empty($d['deshacer']);
    if ($esDeshacer) {
        $venc30 = !empty($d['fechaVencimiento']) ? sanitizarTexto($d['fechaVencimiento']) : date('Y-m-d', strtotime('-35 days'));
    } else {
        $venc30 = calcularFechaVencimiento30($d['fechaVencimiento'] ?? null, true);
    }
    $metodoPago = sanitizarTexto($d['metodoPago'] ?? 'linea');
    $tel = !empty($d['telefono']) ? sanitizarTexto($d['telefono']) : null;

    foreach (obtenerCategoriasClaves() as $catKey) {
        [, , $tPadres] = asegurarTablasCategoria($catKey);
        q("UPDATE `$tPadres` SET telefono = COALESCE(?, telefono), metodo_pago = ?, fecha_vencimiento = ? WHERE id = ?",
          'sssi', [$tel, $metodoPago, $venc30, $idPadre]);
    }
    q("UPDATE usuarios SET telefono = COALESCE(?, telefono), metodo_pago = ?, fecha_vencimiento = ? WHERE id = ?",
      'sssi', [$tel, $metodoPago, $venc30, $idPadre]);
    salida(['ok' => true, 'fechaVencimiento' => $venc30]);
    break;

case 'entrenadores/actualizar_nombre':
    auth(['admin']);
    $id = (int)($d['id'] ?? 0);
    $nombre = sanitizarTexto($d['nombre'] ?? '');
    if ($id <= 0 || $nombre === '') salida(['ok' => false, 'error' => 'Nombre requerido'], 400);
    q("UPDATE usuarios SET nombre = ?, usuario = ? WHERE id = ? AND rol = 'entrenador'", 'ssi', [$nombre, $nombre, $id]);
    salida(['ok' => true]);
    break;

case 'entrenadores/actualizar_telefono':
    auth(['admin']);
    $id = (int)($d['id'] ?? 0);
    $tel = sanitizarTexto($d['telefono'] ?? '');
    if ($id <= 0) salida(['ok' => false, 'error' => 'ID requerido'], 400);
    q("UPDATE usuarios SET telefono = ? WHERE id = ? AND rol = 'entrenador'", 'si', [$tel, $id]);
    salida(['ok' => true]);
    break;

case 'entrenadores/actualizar_categorias':
    auth(['admin']);
    $id = (int)($d['id'] ?? 0);
    $cats = is_array($d['categorias'] ?? null) ? $d['categorias'] : [];
    if ($id <= 0) salida(['ok' => false, 'error' => 'ID requerido'], 400);
    $catKey = !empty($cats[0]) ? slugTablaCat(sanitizarTexto($cats[0])) : '';
    $catJson = json_encode(array_values(array_unique(array_map('sanitizarTexto', $cats))), JSON_UNESCAPED_UNICODE);
    q("UPDATE usuarios SET categorias = ?, cat_key = ? WHERE id = ? AND rol = 'entrenador'", 'ssi', [$catJson, $catKey, $id]);
    salida(['ok' => true]);
    break;

case 'entrenadores/actualizar_password':
    auth(['admin']);
    $id = (int)($d['id'] ?? 0);
    $passRaw = trim((string)($d['password'] ?? ''));
    if ($id <= 0) salida(['ok' => false, 'error' => 'ID requerido'], 400);
    $errPass = validarPasswordSegura12($passRaw);
    if ($errPass !== null) salida(['ok' => false, 'error' => $errPass], 400);
    $hash = password_hash($passRaw, PASSWORD_BCRYPT);
    q("UPDATE usuarios SET password = ? WHERE id = ? AND rol = 'entrenador'", 'si', [$hash, $id]);
    salida(['ok' => true]);
    break;

case 'usuarios/designar_superadmin':
    auth(['admin']);
    if (!esSuperAdminSesion()) salida(['ok' => false, 'error' => 'Solo el Super Administrador puede delegar este rango'], 403);
    $id = (int)($d['id'] ?? 0);
    if ($id <= 0) salida(['ok' => false, 'error' => 'ID requerido'], 400);
    q("UPDATE usuarios SET es_top = 1, permisos = '[\"*\"]' WHERE id = ? AND rol = 'admin'", 'i', [$id]);
    salida(['ok' => true]);
    break;

case 'usuarios/actualizar_politicas':
    auth(['admin']);
    if (!esSuperAdminSesion()) salida(['ok' => false, 'error' => 'Solo el Super Administrador puede gestionar permisos'], 403);
    $id = (int)($d['id'] ?? 0);
    $permisos = is_array($d['permisos'] ?? null) ? $d['permisos'] : [];
    if ($id <= 0) salida(['ok' => false, 'error' => 'ID requerido'], 400);
    $permJson = json_encode(array_values(array_unique(array_map('sanitizarTexto', $permisos))), JSON_UNESCAPED_UNICODE);
    q("UPDATE usuarios SET permisos = ? WHERE id = ? AND rol = 'admin'", 'si', [$permJson, $id]);
    salida(['ok' => true]);
    break;

case 'usuarios/bloquear':
    auth(['admin']);
    $estadoBloqueo = ((int)($d['bloqueado'] ?? $d['estado'] ?? 0) === 1) ? 1 : 0;
    $idUsr = (int)($d['id'] ?? 0);
    q("UPDATE usuarios SET bloqueado=? WHERE id=?", 'ii', [$estadoBloqueo, $idUsr]);
    foreach (obtenerCategoriasClaves() as $catKey) {
        [, , $tPadres] = asegurarTablasCategoria($catKey);
        q("UPDATE `$tPadres` SET bloqueado=? WHERE id=?", 'ii', [$estadoBloqueo, $idUsr]);
    }
    salida(['ok' => true, 'success' => true, 'bloqueado' => $estadoBloqueo]);
    break;

case 'waf/listar':
    auth(['admin', 'soporte']);
    salida(['ok' => true, 'bloqueados' => wafObtenerIpsBloqueadas()]);
    break;

case 'waf/bloquear':
    auth(['admin', 'soporte']);
    $ipTarget = trim((string)($d['ip'] ?? ''));
    $motivo   = trim((string)($d['motivo'] ?? 'Bloqueo manual por el Administrador'));
    $horas    = max(1, min(720, (int)($d['horas'] ?? 24)));
    if (!filter_var($ipTarget, FILTER_VALIDATE_IP)) {
        salida(['ok' => false, 'error' => 'Por favor introduce una dirección IP válida (ej: 198.51.100.12).'], 400);
    }
    if (wafEsIpCloudflare($ipTarget) || in_array($ipTarget, ['127.0.0.1', '::1', '0.0.0.0'], true)) {
        salida(['ok' => false, 'error' => 'Por seguridad no se puede bloquear localhost ni los servidores de Cloudflare.'], 400);
    }
    wafBloquearIp($ipTarget, $motivo, 'Bloqueo Manual del Administrador', $horas * 3600);
    salida([
        'ok'         => true,
        'mensaje'    => "IP $ipTarget bloqueada por $horas hora(s).",
        'bloqueados' => wafObtenerIpsBloqueadas()
    ]);
    break;

case 'waf/desbloquear':
    auth(['admin', 'soporte']);
    $ipTarget = trim((string)($d['ip'] ?? ''));
    if ($ipTarget === 'TODAS') {
        wafLimpiarTodosBloqueos();
        salida([
            'ok'         => true,
            'mensaje'    => 'Todas las IPs han sido desbloqueadas con éxito.',
            'bloqueados' => []
        ]);
    }
    wafDesbloquearIp($ipTarget);
    salida([
        'ok'         => true,
        'mensaje'    => "IP $ipTarget desbloqueada correctamente.",
        'bloqueados' => wafObtenerIpsBloqueadas()
    ]);
    break;

case 'usuarios/eliminar':
    auth(['admin']);
    $idUsr = (int)($d['id'] ?? 0);
    $rolDel = sanitizarTexto($d['rol'] ?? '');
    if ($rolDel === 'padre') {
        $pdoConn = pdo();
        $pdoConn->beginTransaction();
        try {
            // Cumplimiento Ley 81: eliminar datos del padre, todos sus hijos vinculados y registros asociados
            foreach (obtenerCategoriasClaves() as $catKey) {
                [$tJug, $tStats, $tPadres] = asegurarTablasCategoria($catKey);
                $stP = $pdoConn->prepare("SELECT hijo_id, hijos_json FROM `$tPadres` WHERE id = ?");
                $stP->execute([$idUsr]);
                $padInfo = $stP->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($padInfo)) {
                    $hijosAEliminar = [];
                    foreach ($padInfo as $pi) {
                        if (!empty($pi['hijo_id'])) {
                            $hijosAEliminar[] = (float)$pi['hijo_id'];
                        }
                        if (!empty($pi['hijos_json'])) {
                            $hjArr = json_decode($pi['hijos_json'], true);
                            if (is_array($hjArr)) {
                                foreach ($hjArr as $hj) {
                                    if (!empty($hj['id'])) {
                                        $hijosAEliminar[] = (float)$hj['id'];
                                    }
                                }
                            }
                        }
                    }
                    $hijosAEliminar = array_unique($hijosAEliminar);
                    foreach ($hijosAEliminar as $hijoIdDel) {
                        if ($hijoIdDel > 0) {
                            $delJ = $pdoConn->prepare("DELETE FROM `$tJug` WHERE id = ?");
                            $delJ->execute([$hijoIdDel]);
                            $delS = $pdoConn->prepare("DELETE FROM `$tStats` WHERE player_id = ?");
                            $delS->execute([$hijoIdDel]);
                            $delA = $pdoConn->prepare("DELETE FROM asistencias WHERE cat_key = ? AND player_id = ?");
                            $delA->execute([$catKey, $hijoIdDel]);
                            $delE = $pdoConn->prepare("DELETE FROM evaluaciones WHERE cat_key = ? AND player_id = ?");
                            $delE->execute([$catKey, $hijoIdDel]);
                        }
                    }
                    $delP = $pdoConn->prepare("DELETE FROM `$tPadres` WHERE id = ?");
                    $delP->execute([$idUsr]);
                }
            }
            $delPP = $pdoConn->prepare("DELETE FROM pagos_padre WHERE padre_id = ?");
            $delPP->execute([$idUsr]);
            $delC = $pdoConn->prepare("DELETE FROM comprobantes WHERE padre_id = ?");
            $delC->execute([$idUsr]);
            $delCP = $pdoConn->prepare("DELETE FROM comentarios_padres WHERE padre_id = ?");
            $delCP->execute([$idUsr]);
            $pdoConn->commit();
        } catch (Exception $e) {
            $pdoConn->rollBack();
            salida(['ok' => false, 'error' => 'Error al eliminar usuario: ' . $e->getMessage()]);
        }
    } else {
        q("DELETE FROM usuarios WHERE id=? AND rol=?", 'is', [$idUsr, $rolDel]);
    }
    break;

case 'usuarios/cambiar_mis_credenciales':
    auth(['admin', 'soporte']);
    $userId = (int)($_SESSION['ns_user_id'] ?? 0);
    $userRol = $_SESSION['ns_rol'] ?? '';
    if ($userId <= 0 || !in_array($userRol, ['admin', 'soporte'], true)) {
        salida(['ok' => false, 'error' => 'No autorizado para modificar estas credenciales.'], 403);
    }

    $passActual   = trim((string)($d['passActual'] ?? ''));
    $passNuevo    = trim((string)($d['passNuevo'] ?? ''));
    $passConfirm  = trim((string)($d['passConfirm'] ?? ''));
    $usuarioNuevo = trim(sanitizarTexto($d['usuarioNuevo'] ?? ''));

    if ($passActual === '') {
        salida(['ok' => false, 'error' => 'Debes ingresar tu contraseña actual para confirmar el cambio.'], 400);
    }

    // Buscar usuario en base de datos usando sesión segura
    $rowUser = q("SELECT id, usuario, password, nombre FROM usuarios WHERE id = ? AND rol = ? LIMIT 1", 'is', [$userId, $userRol]);
    if (empty($rowUser)) {
        salida(['ok' => false, 'error' => 'Usuario no encontrado en la base de datos.'], 404);
    }
    $uData = $rowUser[0];

    // Verificar contraseña actual con bcrypt
    if (!password_verify($passActual, $uData['password'])) {
        salida(['ok' => false, 'error' => 'La contraseña actual no es correcta.'], 403);
    }

    $cambios = [];
    $usuarioFinal = $uData['usuario'];

    // Si desea cambiar nombre de usuario
    if ($usuarioNuevo !== '' && strtolower($usuarioNuevo) !== strtolower($uData['usuario'])) {
        $estadoCooldown = puedeCambiarNombreUsuario($userRol);
        if (!$estadoCooldown['puede']) {
            salida([
                'ok' => false,
                'error' => "Por políticas de seguridad, el nombre de usuario solo puede modificarse una vez cada 7 días. Podrás cambiarlo nuevamente en {$estadoCooldown['diasRestantes']} día(s)."
            ], 429);
        }
        if (!preg_match('/^[a-zA-Z0-9._\-]{3,50}$/', $usuarioNuevo)) {
            salida(['ok' => false, 'error' => 'El nuevo nombre de usuario debe tener entre 3 y 50 caracteres (solo letras, números, puntos o guiones).'], 400);
        }
        $yaExiste = q("SELECT id FROM usuarios WHERE LOWER(usuario) = LOWER(?) AND id != ? LIMIT 1", 'si', [$usuarioNuevo, $userId]);
        if (!empty($yaExiste)) {
            salida(['ok' => false, 'error' => "El nombre de usuario '$usuarioNuevo' ya está ocupado por otra cuenta."], 409);
        }
        $usuarioFinal = $usuarioNuevo;
        $cambios['usuario'] = $usuarioNuevo;
    }

    // Si desea cambiar contraseña
    if ($passNuevo !== '') {
        if ($passNuevo !== $passConfirm) {
            salida(['ok' => false, 'error' => 'La confirmación de la nueva contraseña no coincide.'], 400);
        }
        $errPass = validarPasswordSegura12($passNuevo);
        if ($errPass !== null) {
            salida(['ok' => false, 'error' => $errPass], 400);
        }
        $nuevoHash = password_hash($passNuevo, PASSWORD_BCRYPT);
        $cambios['password'] = $nuevoHash;
    }

    if (empty($cambios)) {
        salida(['ok' => false, 'error' => 'No has especificado ningún cambio (nuevo usuario o nueva contraseña).'], 400);
    }

    if (isset($cambios['usuario']) && isset($cambios['password'])) {
        q("UPDATE usuarios SET usuario = ?, password = ? WHERE id = ? AND rol = ?", 'ssis', [$cambios['usuario'], $cambios['password'], $userId, $userRol]);
    } elseif (isset($cambios['usuario'])) {
        q("UPDATE usuarios SET usuario = ? WHERE id = ? AND rol = ?", 'sis', [$cambios['usuario'], $userId, $userRol]);
    } elseif (isset($cambios['password'])) {
        q("UPDATE usuarios SET password = ? WHERE id = ? AND rol = ?", 'sis', [$cambios['password'], $userId, $userRol]);
    }

    $_SESSION['ns_user'] = $usuarioFinal;
    if (isset($cambios['usuario'])) {
        $_SESSION['ns_nombre'] = $usuarioFinal;
        registrarCambioNombreUsuario($userRol);
    }

    salida([
        'ok'      => true,
        'mensaje' => 'Credenciales actualizadas correctamente en la base de datos.',
        'usuario' => $usuarioFinal
    ]);
    break;

case 'evaluaciones/guardar':
    auth(['admin', 'entrenador']);
    $catKey = obtenerCatKeyAutorizada($d['catKey'] ?? '');
    q("REPLACE INTO evaluaciones (cat_key,player_id,fecha,tecnicos,tacticos,fisicos,actitudinales) VALUES (?,?,?,?,?,?,?)",
      'sisssss', [
          $catKey,
          (int)($d['playerId'] ?? 0),
          sanitizarTexto($d['fecha'] ?? ''),
          json_encode($d['tecnicos'] ?? [], JSON_UNESCAPED_UNICODE),
          json_encode($d['tacticos'] ?? [], JSON_UNESCAPED_UNICODE),
          json_encode($d['fisicos'] ?? [], JSON_UNESCAPED_UNICODE),
          json_encode($d['actitudinales'] ?? [], JSON_UNESCAPED_UNICODE)
      ]);
    break;

case 'asistencias/guardar':
    auth(['admin', 'entrenador']);
    $catKey = obtenerCatKeyAutorizada($d['catKey'] ?? '');
    q("REPLACE INTO asistencias (cat_key,fecha,player_id,estado) VALUES (?,?,?,?)",
      'ssis', [
          $catKey,
          sanitizarTexto($d['fecha'] ?? ''),
          (int)($d['playerId'] ?? 0),
          sanitizarTexto($d['estado'] ?? '')
      ]);
    break;

case 'asistencias/quitar':
    auth(['admin', 'entrenador']);
    $catKey = obtenerCatKeyAutorizada($d['catKey'] ?? '');
    q("DELETE FROM asistencias WHERE cat_key=? AND fecha=? AND player_id=?",
      'ssi', [$catKey, sanitizarTexto($d['fecha'] ?? ''), (int)($d['playerId'] ?? 0)]);
    break;

case 'comentarios/guardar':
    auth(['admin', 'padre']);
    $padreId = ($_SESSION['ns_rol'] === 'padre') ? (int)$_SESSION['ns_user_id'] : (int)($d['padre_id'] ?? 0);
    q("INSERT INTO comentarios_padres (padre_id, tipo, asunto, mensaje, es_anonimo) VALUES (?, ?, ?, ?, ?)",
      'isssi', [
          $padreId,
          sanitizarTexto($d['tipo'] ?? 'sugerencia'),
          sanitizarTexto($d['asunto'] ?? ''),
          sanitizarTexto($d['mensaje'] ?? ''),
          !empty($d['es_anonimo']) ? 1 : 0
      ]);
    break;

case 'comentarios/borrar':
    auth(['admin']);
    q("DELETE FROM comentarios_padres WHERE id=?", 'i', [(int)($d['id'] ?? 0)]);
    break;

case 'comentarios/marcar_leido':
    auth(['admin']);
    q("UPDATE comentarios_padres SET estado='leido' WHERE id=?", 'i', [(int)($d['id'] ?? 0)]);
    break;

case 'comentarios/marcar_todo_leido':
    auth(['admin']);
    q("UPDATE comentarios_padres SET estado='leido' WHERE estado='nuevo'");
    break;

case 'comentarios/estado':
    auth(['admin']);
    $estadoNuevo = in_array($d['estado'] ?? '', ['nuevo', 'leido', 'archivado'], true) ? $d['estado'] : 'leido';
    q("UPDATE comentarios_padres SET estado=? WHERE id=?", 'si', [$estadoNuevo, (int)($d['id'] ?? 0)]);
    break;

case 'encuestas/guardar':
    auth(['admin', 'padre']);
    $nombrePadre = ($_SESSION['ns_rol'] === 'padre') ? sanitizarTexto($_SESSION['ns_nombre'] ?? '') : sanitizarTexto($d['padre'] ?? '');
    q("INSERT INTO encuestas (padre,fecha,aspectos,gusta_mas,mejorar,crecimiento,crecimiento_why,recomienda,recomienda_why,observaciones) VALUES (?,?,?,?,?,?,?,?,?,?)",
      'ssssssssss', [
          $nombrePadre,
          sanitizarTexto($d['fecha'] ?? ''),
          json_encode($d['aspectos'] ?? [], JSON_UNESCAPED_UNICODE),
          sanitizarTexto($d['gustaMas'] ?? ''),
          sanitizarTexto($d['mejorar'] ?? ''),
          sanitizarTexto($d['crecimiento'] ?? ''),
          sanitizarTexto($d['crecimientoWhy'] ?? ''),
          sanitizarTexto($d['recomienda'] ?? ''),
          sanitizarTexto($d['recomiendaWhy'] ?? ''),
          sanitizarTexto($d['observaciones'] ?? '')
      ]);
    break;

case 'stats/guardar':
    auth(['admin', 'entrenador']);
    $catKey = obtenerCatKeyAutorizada($d['catKey'] ?? '');
    [$tJug, $tStats] = array_slice(asegurarTablasCategoria($catKey), 0, 2);
    $playerId = (int)($d['playerId'] ?? 0);
    $jugInfo = q("SELECT name, dorsal FROM `$tJug` WHERE id=?", 'i', [$playerId]);
    $nombreJug = !empty($jugInfo) ? (string)$jugInfo[0]['name'] : '';
    $dorsalJug = !empty($jugInfo) ? (int)$jugInfo[0]['dorsal'] : 0;
    q("REPLACE INTO `$tStats` (player_id,dorsal,nombre,cat_key,goles,asistencias,mvp,updated_at) VALUES (?,?,?,?,?,?,?,?)",
      'iissiiis', [
          $playerId,
          $dorsalJug,
          $nombreJug,
          slugTablaCat($catKey),
          max(0, (int)($d['goles'] ?? 0)),
          max(0, (int)($d['asistencias'] ?? 0)),
          max(0, (int)($d['mvp'] ?? 0)),
          sanitizarTexto($d['updatedAt'] ?? '')
      ]);
    break;

case 'plantillas/guardar':
    auth(['admin', 'entrenador']);
    $catKey = obtenerCatKeyAutorizada($d['catKey'] ?? '');
    [$tJug, $tStats] = array_slice(asegurarTablasCategoria($catKey), 0, 2);
    $slug = slugTablaCat($catKey);
    try {
        $pdoConn->beginTransaction();
        $stDel = $pdoConn->prepare("DELETE FROM `$tJug`");
        $stDel->execute();
        $stIns = $pdoConn->prepare("INSERT INTO `$tJug` (id,cat_key,name,edad,dorsal,pos,foto) VALUES (?,?,?,?,?,?,?)");
        foreach (($d['lista'] ?? []) as $p) {
            if (!is_array($p)) continue;
            $pId = (float)($p['id'] ?? 0);
            if ($pId <= 0) $pId = siguienteJugadorIdGlobal();
            $pName = sanitizarTexto($p['name'] ?? 'Jugador');
            $pEdad = max(0, (int)($p['edad'] ?? 0));
            $pDorsal = (int)($p['dorsal'] ?? 0);
            $pPos = sanitizarTexto($p['pos'] ?? 'MED');
            $pFoto = !empty($p['foto']) ? $p['foto'] : 'img/escudo.png';
            $stIns->execute([$pId, $slug, $pName, $pEdad, $pDorsal, $pPos, $pFoto]);
        }
        $pdoConn->commit();
        sincronizarStatsCategoria($catKey);
    } catch (Exception $e) {
        if ($pdoConn->inTransaction()) $pdoConn->rollBack();
        error_log('[Transaction Error plantillas/guardar] ' . $e->getMessage());
    }
    break;

case 'trofeosCustom/guardar':
    auth(['admin']);
    q("REPLACE INTO trofeos_custom (id,nombre,cat_key,categoria,anio,icono,color,descripcion,evaluacion) VALUES (?,?,?,?,?,?,?,?,?)",
      'sssssssss', [
          sanitizarTexto((string)($d['id'] ?? '')),
          sanitizarTexto($d['nombre'] ?? ''),
          sanitizarTexto($d['catKey'] ?? ''),
          sanitizarTexto($d['categoria'] ?? ''),
          sanitizarTexto((string)($d['año'] ?? '')),
          sanitizarTexto($d['icono'] ?? 'fa-trophy'),
          sanitizarTexto($d['color'] ?? 'text-amber-400'),
          sanitizarTexto($d['descripcion'] ?? ''),
          json_encode($d['evaluacion'] ?? (object)[], JSON_UNESCAPED_UNICODE)
      ]);
    break;

case 'trofeosCustom/eliminar':
    auth(['admin']);
    q("DELETE FROM trofeos_custom WHERE id=?", 's', [sanitizarTexto((string)($d['id'] ?? ''))]);
    break;

case 'trofeosOcultos/agregar':
    auth(['admin']);
    q("INSERT IGNORE INTO trofeos_ocultos (id) VALUES (?)", 's', [sanitizarTexto((string)($d['id'] ?? ''))]);
    break;

case 'trofeosEditados/guardar':
    auth(['admin']);
    q("REPLACE INTO trofeos_editados (id,data) VALUES (?,?)", 'ss', [
        sanitizarTexto((string)($d['id'] ?? '')),
        json_encode($d, JSON_UNESCAPED_UNICODE)
    ]);
    break;

case 'textos/guardar_lote':
case 'textos/guardar':
    auth(['admin']);
    if (!empty($d['lote']) && is_array($d['lote'])) {
        $stmtBatch = pdo()->prepare("REPLACE INTO textos (clave, html) VALUES (?, ?)");
        foreach ($d['lote'] as $k => $h) {
            $kLimpio = sanitizarTexto((string)$k);
            if ($kLimpio !== '') {
                $stmtBatch->execute([$kLimpio, sanitizarHtmlSeguro((string)$h)]);
            }
        }
        salida(['ok' => true, 'total' => count($d['lote'])]);
    }
    if (!empty($d['clave'])) {
        q("REPLACE INTO textos (clave,html) VALUES (?,?)", 'ss', [
            sanitizarTexto($d['clave'] ?? ''),
            sanitizarHtmlSeguro($d['html'] ?? '')
        ]);
        salida(['ok' => true]);
    }
    salida(['ok' => true]);
    break;

case 'documentos/guardar':
    auth(['admin']);
    if (in_array($d['key'] ?? '', ['torneo', 'eval_global'], true)) {
        q("REPLACE INTO documentos (doc_key,value) VALUES (?,?)", 'ss', [
            $d['key'],
            json_encode($d['value'] ?? null, JSON_UNESCAPED_UNICODE)
        ]);
    }
    break;

case 'categorias/actualizar_foto':
    auth(['admin']);
    q("UPDATE categorias SET foto=? WHERE id=?", 'si', [$d['foto'] ?? '', (int)($d['id'] ?? 0)]);
    break;

case 'configuraciones/guardar':
    auth(['admin']);
    try {
        pdo()->exec("CREATE TABLE IF NOT EXISTS configuraciones (clave VARCHAR(50) PRIMARY KEY, valor TEXT)");
    } catch (Exception $e) {}

    $camposPermitidos = [
        'tarifa_mensualidad' => 'float',
        'tarifa_inscripcion' => 'float',
        'mensualidad'        => 'float_alias_mensualidad',
        'inscripcion'        => 'float_alias_inscripcion',
        'numero_yape'        => 'texto',
        'titular_yape'       => 'texto',
        'detalles_yape'      => 'texto',
        'yape'               => 'texto_alias_numero_yape',
        'whatsapp_admin'     => 'texto'
    ];

    foreach ($camposPermitidos as $clave => $tipo) {
        if (!isset($d[$clave])) continue;
        $val = $d[$clave];
        if ($tipo === 'float') {
            $num = max(0, (float)$val);
            q("REPLACE INTO configuraciones (clave, valor) VALUES (?, ?)", 'ss', [$clave, number_format($num, 2, '.', '')]);
        } elseif ($tipo === 'float_alias_mensualidad') {
            $num = max(0, (float)$val);
            q("REPLACE INTO configuraciones (clave, valor) VALUES (?, ?)", 'ss', ['tarifa_mensualidad', number_format($num, 2, '.', '')]);
        } elseif ($tipo === 'float_alias_inscripcion') {
            $num = max(0, (float)$val);
            q("REPLACE INTO configuraciones (clave, valor) VALUES (?, ?)", 'ss', ['tarifa_inscripcion', number_format($num, 2, '.', '')]);
        } elseif ($tipo === 'texto_alias_numero_yape') {
            $limpio = sanitizarTexto((string)$val);
            q("REPLACE INTO configuraciones (clave, valor) VALUES (?, ?)", 'ss', ['numero_yape', $limpio]);
        } else {
            $limpio = sanitizarTexto((string)$val);
            q("REPLACE INTO configuraciones (clave, valor) VALUES (?, ?)", 'ss', [$clave, $limpio]);
        }
    }

    $todas = [];
    foreach (consultar("SELECT clave, valor FROM configuraciones") as $r) {
        $todas[$r['clave']] = $r['valor'];
    }
    salida(['ok' => true, 'configuraciones' => $todas]);
    break;

case 'jugadores/actualizar_foto':
    auth(['admin']);
    $catKey = sanitizarTexto($d['catKey'] ?? '');
    [$tJug, $tStats] = array_slice(asegurarTablasCategoria($catKey), 0, 2);
    q("UPDATE `$tJug` SET foto=? WHERE id=?", 'si', [
        $d['foto'] ?? '',
        (int)($d['id'] ?? 0)
    ]);
    break;

case 'jugadores/editar':
    auth(['admin', 'entrenador']);
    $playerId = (int)($d['id'] ?? 0);
    $catOrigen = slugTablaCat(sanitizarTexto($d['catOrigen'] ?? $d['catKey'] ?? ''));
    $nombreNuevo = sanitizarTexto($d['name'] ?? $d['nombre'] ?? '');
    $edadNueva = max(0, (int)($d['edad'] ?? 0));
    $dorsalNuevo = isset($d['dorsal']) ? (int)$d['dorsal'] : 0;
    $posNueva = in_array(strtoupper($d['pos'] ?? ''), ['POR', 'DEF', 'MED', 'DEL'], true) ? strtoupper($d['pos']) : 'MED';

    if ($playerId <= 0 || $nombreNuevo === '') {
        salida(['ok' => false, 'error' => 'Datos inválidos del jugador'], 400);
    }

    // Calcular categoría automática basada en edad (si pasa de 16 -> submayor)
    $catDestino = !empty($d['catDestino']) ? slugTablaCat(sanitizarTexto($d['catDestino'])) : '';
    if ($catDestino === '') {
        if ($edadNueva <= 6) $catDestino = 'sub6';
        elseif ($edadNueva <= 12) $catDestino = 'sub12';
        elseif ($edadNueva <= 16) $catDestino = 'sub16';
        else $catDestino = 'submayor';
    }

    [$tJugOrig, $tStatsOrig] = array_slice(asegurarTablasCategoria($catOrigen), 0, 2);
    [$tJugDest, $tStatsDest] = array_slice(asegurarTablasCategoria($catDestino), 0, 2);
    $mapaPadresTablas = [];
    foreach (obtenerCategoriasClaves() as $cKey) {
        [, , $tP] = asegurarTablasCategoria($cKey);
        $mapaPadresTablas[$cKey] = $tP;
    }

    $pdoConn = pdo();
    $pdoConn->beginTransaction();
    try {
        $fotoActual = 'img/escudo.png';
        if ($catOrigen !== '') {
            $infoOld = q("SELECT foto, dorsal FROM `$tJugOrig` WHERE id=?", 'i', [$playerId]);
            if (!empty($infoOld)) {
                $fotoActual = $infoOld[0]['foto'] ?? 'img/escudo.png';
                if ($dorsalNuevo === 0 && !empty($infoOld[0]['dorsal'])) {
                    $dorsalNuevo = (int)$infoOld[0]['dorsal'];
                }
            }
        }

        if ($catOrigen !== '' && $catOrigen !== $catDestino) {
            if ($dorsalNuevo > 0) {
                $chkDor = q("SELECT id FROM `$tJugDest` WHERE dorsal=? AND id!=?", 'ii', [$dorsalNuevo, $playerId]);
                if (!empty($chkDor)) {
                    $dorsalNuevo = 0;
                }
            }
            $pdoConn->exec("DELETE FROM `$tJugOrig` WHERE id = $playerId");
            $pdoConn->exec("DELETE FROM `$tStatsOrig` WHERE player_id = $playerId");
        }

        $stIns = $pdoConn->prepare("INSERT INTO `$tJugDest` (id, cat_key, name, edad, dorsal, pos, foto)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE name=VALUES(name), edad=VALUES(edad), dorsal=VALUES(dorsal), pos=VALUES(pos), cat_key=VALUES(cat_key)");
        $stIns->execute([$playerId, $catDestino, $nombreNuevo, $edadNueva, $dorsalNuevo, $posNueva, $fotoActual]);

        $stStats = $pdoConn->prepare("INSERT INTO `$tStatsDest` (player_id, dorsal, nombre, cat_key, goles, asistencias, mvp)
            VALUES (?, ?, ?, ?, 0, 0, 0)
            ON DUPLICATE KEY UPDATE dorsal=VALUES(dorsal), nombre=VALUES(nombre), cat_key=VALUES(cat_key)");
        $stStats->execute([$playerId, $dorsalNuevo, $nombreNuevo, $catDestino]);

        $mapaLabels = obtenerMapaLabelsCategorias();
        $labelDest = $mapaLabels[$catDestino] ?? strtoupper($catDestino);

        foreach ($mapaPadresTablas as $cKey => $tPadres) {
            $pdoConn->exec("UPDATE `$tPadres` SET hijo_nombre = " . $pdoConn->quote($nombreNuevo) . ", hijo_edad = $edadNueva, hijo_cat = '$catDestino', sub = '$labelDest' WHERE hijo_id = $playerId");

            $stP = $pdoConn->prepare("SELECT id, hijos_json FROM `$tPadres` WHERE hijos_json LIKE ?");
            $stP->execute(['%"id":' . $playerId . '%']);
            $rowsP = $stP->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rowsP as $rp) {
                $hArr = json_decode($rp['hijos_json'], true);
                if (is_array($hArr)) {
                    foreach ($hArr as &$hItem) {
                        if (($hItem['id'] ?? 0) == $playerId) {
                            $hItem['nombre'] = $nombreNuevo;
                            $hItem['edad'] = $edadNueva;
                            $hItem['catKey'] = $catDestino;
                            $hItem['sub'] = $labelDest;
                            $hItem['catLabel'] = $labelDest;
                            $hItem['dorsal'] = $dorsalNuevo;
                            $hItem['pos'] = $posNueva;
                        }
                    }
                    unset($hItem);
                    $pdoConn->exec("UPDATE `$tPadres` SET hijos_json = " . $pdoConn->quote(json_encode($hArr, JSON_UNESCAPED_UNICODE)) . " WHERE id = " . (int)$rp['id']);
                }
            }
        }

        if ($pdoConn->inTransaction()) {
            $pdoConn->commit();
        }

        if ($catOrigen !== '' && $catOrigen !== $catDestino) {
            sincronizarStatsCategoria($catOrigen);
        }
        sincronizarStatsCategoria($catDestino);

        salida([
            'ok' => true,
            'catDestino' => $catDestino,
            'labelDestino' => $labelDest,
            'dorsal' => $dorsalNuevo,
            'msg' => 'Jugador actualizado correctamente'
        ]);
    } catch (Exception $e) {
        if ($pdoConn->inTransaction()) $pdoConn->rollBack();
        salida(['ok' => false, 'error' => 'Error al actualizar jugador: ' . $e->getMessage()], 500);
    }
    break;

case 'staff/guardar':
    auth(['admin']);
    try {
        $pdoConn->beginTransaction();
        q("DELETE FROM staff");
        foreach (($d['lista'] ?? $d ?? []) as $i => $s) {
            if (!is_array($s)) continue;
            q("REPLACE INTO staff (id,nombre,cargo,licencia,exp,especialidad,bio,foto_icon,color,orden) VALUES (?,?,?,?,?,?,?,?,?,?)",
              'issssssssi', [
                  (int)($s['id'] ?? ($i + 1)),
                  sanitizarTexto($s['nombre'] ?? ''),
                  sanitizarTexto($s['cargo'] ?? ''),
                  sanitizarTexto($s['licencia'] ?? ''),
                  sanitizarTexto($s['exp'] ?? ''),
                  sanitizarTexto($s['especialidad'] ?? ''),
                  sanitizarTexto($s['bio'] ?? ''),
                  sanitizarTexto($s['fotoIcon'] ?? 'fa-user'),
                  sanitizarTexto($s['color'] ?? ''),
                  $i + 1
              ]);
        }
        $pdoConn->commit();
    } catch (Exception $e) {
        if ($pdoConn->inTransaction()) $pdoConn->rollBack();
        error_log('[Transaction Error staff/guardar] ' . $e->getMessage());
    }
    break;

case 'categorias/guardar':
    auth(['admin']);
    try {
        $clavesAnteriores = obtenerCategoriasClaves();
        $clavesNuevas = [];
        $pdoConn->beginTransaction();
        q("DELETE FROM categorias");
        foreach (($d['lista'] ?? []) as $i => $c) {
            if (!is_array($c)) continue;
            $k = slugTablaCat(sanitizarTexto($c['key'] ?? ''));
            $clavesNuevas[] = $k;
            q("REPLACE INTO categorias (cat_key,label,descripcion,core,orden) VALUES (?,?,?,?,?)",
              'sssii', [
                  $k,
                  sanitizarTexto($c['label'] ?? ''),
                  sanitizarTexto($c['descripcion'] ?? ''),
                  !empty($c['core']) ? 1 : 0,
                  $i + 1
              ]);
        }
        $pdoConn->commit();
        foreach ($clavesNuevas as $k) {
            asegurarTablasCategoria($k);
            sincronizarStatsCategoria($k);
        }
        $coreKeys = ['sub6', 'sub12', 'sub16', 'submayor'];
        foreach ($clavesAnteriores as $oldK) {
            if (!in_array($oldK, $clavesNuevas, true) && !in_array($oldK, $coreKeys, true)) {
                $slugOld = slugTablaCat($oldK);
                $pdoConn->exec("DROP TABLE IF EXISTS `jugadores_$slugOld`, `player_stats_$slugOld`, `padres_$slugOld`");
            }
        }
    } catch (Exception $e) {
        if ($pdoConn->inTransaction()) $pdoConn->rollBack();
        error_log('[Transaction Error categorias/guardar] ' . $e->getMessage());
    }
    break;

case 'reciclados/guardar':
    auth(['admin']);
    q("REPLACE INTO jugadores_reciclados (cat_key,dorsal,data,guardado_en) VALUES (?,?,?,?)",
      'siss', [
          sanitizarTexto($d['catKey'] ?? ''),
          (int)($d['dorsal'] ?? 0),
          json_encode($d, JSON_UNESCAPED_UNICODE),
          sanitizarTexto($d['guardadoEn'] ?? '')
      ]);
    break;

case 'reciclados/quitar':
    auth(['admin']);
    q("DELETE FROM jugadores_reciclados WHERE cat_key=? AND dorsal=?", 'si', [
        sanitizarTexto($d['catKey'] ?? ''),
        (int)($d['dorsal'] ?? 0)
    ]);
    break;

case 'pagosClub/agregar':
    auth(['admin']);
    q("REPLACE INTO pagos_club (id,padre_id,padre_nombre,concepto,monto,fecha,referencia,metodo,estado) VALUES (?,?,?,?,?,?,?,?,?)",
      'iissdssss', [
          (int)($d['id'] ?? round(microtime(true) * 1000)),
          (int)($d['padreId'] ?? 0),
          sanitizarTexto($d['padreNombre'] ?? ''),
          sanitizarTexto($d['concepto'] ?? ''),
          max(0, (float)($d['monto'] ?? 0)),
          sanitizarTexto($d['fecha'] ?? ''),
          sanitizarTexto($d['referencia'] ?? ''),
          sanitizarTexto($d['metodo'] ?? 'efectivo'),
          sanitizarTexto($d['estado'] ?? 'validado')
      ]);
    break;

case 'comprobantes/agregar':
    auth(['admin', 'padre']);
    if ($_SESSION['ns_rol'] === 'padre') {
        $d['padreId'] = (int)$_SESSION['ns_user_id'];
        $d['padreNombre'] = $_SESSION['ns_nombre'] ?? '';
        $d['estado'] = 'en_revision';
    }
    q("REPLACE INTO comprobantes (id,padre_id,padre_nombre,concepto,monto,fecha,referencia,observaciones,comprobante,estado) VALUES (?,?,?,?,?,?,?,?,?,?)",
      'iissdsssss', [
          (int)($d['id'] ?? round(microtime(true) * 1000)),
          (int)($d['padreId'] ?? 0),
          sanitizarTexto($d['padreNombre'] ?? ''),
          sanitizarTexto($d['concepto'] ?? ''),
          max(0, (float)($d['monto'] ?? 0)),
          sanitizarTexto($d['fecha'] ?? ''),
          sanitizarTexto($d['referencia'] ?? ''),
          sanitizarTexto($d['observaciones'] ?? ''),
          sanitizarComprobanteUrl($d['comprobante'] ?? ''),
          sanitizarTexto($d['estado'] ?? 'en_revision')
      ]);
    break;

case 'comprobantes/validar':
    auth(['admin']);
    $aprobar = !empty($d['aprobar']);
    q("UPDATE comprobantes SET estado=? WHERE id=?", 'si', [$aprobar ? 'validado' : 'rechazado', (int)($d['id'] ?? 0)]);
    if ($aprobar && isset($d['pago']) && is_array($d['pago'])) {
        $p = $d['pago'];
        q("REPLACE INTO pagos_club (id,padre_id,padre_nombre,concepto,monto,fecha,referencia,metodo,estado) VALUES (?,?,?,?,?,?,?,?,?)",
          'iissdssss', [
              (int)($p['id'] ?? round(microtime(true) * 1000)),
              (int)($p['padreId'] ?? 0),
              sanitizarTexto($p['padreNombre'] ?? ''),
              sanitizarTexto($p['concepto'] ?? ''),
              max(0, (float)($p['monto'] ?? 0)),
              sanitizarTexto($p['fecha'] ?? ''),
              sanitizarTexto($p['referencia'] ?? ''),
              'digital',
              'validado'
          ]);
        q("UPDATE pagos_padre SET estado='validado' WHERE padre_id=? AND referencia=? AND estado IN ('pendiente', 'en_revision')",
          'is', [(int)($p['padreId'] ?? 0), sanitizarTexto($p['referencia'] ?? '')]);
    } elseif (!$aprobar) {
        $comp = q("SELECT padre_id, referencia, comprobante FROM comprobantes WHERE id=?", 'i', [(int)($d['id'] ?? 0)]);
        if (!empty($comp)) {
            $rutaComp = (string)($comp[0]['comprobante'] ?? '');
            if (preg_match('#^uploads/comprobantes/comp_[0-9]+_[a-f0-9]+\.webp$#', $rutaComp)) {
                $fullComp = __DIR__ . '/../' . $rutaComp;
                if (is_file($fullComp)) @unlink($fullComp);
            }
            q("UPDATE pagos_padre SET estado='rechazado' WHERE padre_id=? AND referencia=? AND estado IN ('pendiente', 'en_revision')",
              'is', [(int)$comp[0]['padre_id'], (string)$comp[0]['referencia']]);
        }
    }
    break;

case 'pagosPadre/agregar':
    auth(['admin', 'padre']);
    if ($_SESSION['ns_rol'] === 'padre') {
        $d['padreId'] = (int)$_SESSION['ns_user_id'];
        $d['estado'] = 'en_revision';
    }
    q("REPLACE INTO pagos_padre (id,padre_id,concepto,monto,fecha,referencia,observaciones,comprobante,estado) VALUES (?,?,?,?,?,?,?,?,?)",
      'iisdsssss', [
          (int)($d['id'] ?? round(microtime(true) * 1000)),
          (int)($d['padreId'] ?? 0),
          sanitizarTexto($d['concepto'] ?? ''),
          max(0, (float)($d['monto'] ?? 0)),
          sanitizarTexto($d['fecha'] ?? ''),
          sanitizarTexto($d['referencia'] ?? ''),
          sanitizarTexto($d['observaciones'] ?? ''),
          sanitizarComprobanteUrl($d['comprobante'] ?? ''),
          sanitizarTexto($d['estado'] ?? 'en_revision')
      ]);
    break;

case 'pagosPlataforma/agregar':
    auth(['admin']);
    q("REPLACE INTO pagos_plataforma (id,tipo,monto,referencia,fecha,observaciones,comprobante,estado) VALUES (?,?,?,?,?,?,?,?)",
      'isdsssss', [
          (int)($d['id'] ?? round(microtime(true) * 1000)),
          sanitizarTexto($d['tipo'] ?? ''),
          max(0, (float)($d['monto'] ?? 0)),
          sanitizarTexto($d['referencia'] ?? ''),
          sanitizarTexto($d['fecha'] ?? ''),
          sanitizarTexto($d['observaciones'] ?? ''),
          sanitizarComprobanteUrl($d['comprobante'] ?? ''),
          sanitizarTexto($d['estado'] ?? 'en_revision')
      ]);
    break;

case 'registroLink/generar':
case 'registroLink/desactivar':
    auth(['admin']);
    // Forzar rotación de un nuevo código de confirmación del ciclo de 7 días (168 horas)
    $info = obtenerOGenerarTokenCiclo7Dias(true);
    salida(['ok' => true, 'linkInfo' => $info]);
    break;

case 'jugador/dorsal':
    auth(['admin', 'entrenador']);
    $catKey = obtenerCatKeyAutorizada($d['catKey'] ?? '');
    $slug = slugTablaCat($catKey);
    [$tJug, $tStats, $tPadres] = asegurarTablasCategoria($slug);
    $playerId = (float)($d['playerId'] ?? 0);
    $nuevoDorsal = max(0, (int)($d['dorsal'] ?? 0));
    if ($playerId <= 0) {
        salida(['ok' => false, 'error' => 'Jugador inválido'], 400);
    }
    if ($nuevoDorsal > 0) {
        $stDup = $pdoConn->prepare("SELECT id, name FROM `$tJug` WHERE dorsal = ? AND id != ? LIMIT 1");
        $stDup->execute([$nuevoDorsal, $playerId]);
        $dup = $stDup->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($dup)) {
            salida(['ok' => false, 'error' => 'El dorsal #' . $nuevoDorsal . ' ya lo tiene ' . $dup[0]['name']], 409);
        }
    }
    $stU1 = $pdoConn->prepare("UPDATE `$tJug` SET dorsal = ? WHERE id = ?");
    $stU1->execute([$nuevoDorsal, $playerId]);
    $stU2 = $pdoConn->prepare("UPDATE `$tStats` SET dorsal = ? WHERE player_id = ?");
    $stU2->execute([$nuevoDorsal, $playerId]);
    $stU3 = $pdoConn->prepare("UPDATE `$tPadres` SET hijo_dorsal = ? WHERE hijo_id = ?");
    $stU3->execute([$nuevoDorsal, $playerId]);

    // Sincronizar también dentro de hijos_json en todas las tablas de padres donde figure este hijo
    foreach (obtenerCategoriasClaves() as $ck) {
        [, , $tP] = asegurarTablasCategoria($ck);
        $stP = $pdoConn->prepare("SELECT id, hijos_json FROM `$tP` WHERE hijos_json LIKE ?");
        $stP->execute(['%' . $playerId . '%']);
        $filasP = $stP->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filasP as $fp) {
            $hList = json_decode($fp['hijos_json'], true);
            if (is_array($hList)) {
                $mod = false;
                foreach ($hList as &$h) {
                    if ((float)($h['id'] ?? 0) == $playerId) {
                        $h['dorsal'] = $nuevoDorsal;
                        $mod = true;
                    }
                }
                unset($h);
                if ($mod) {
                    $updH = $pdoConn->prepare("UPDATE `$tP` SET hijos_json = ? WHERE id = ?");
                    $updH->execute([json_encode($hList, JSON_UNESCAPED_UNICODE), (int)$fp['id']]);
                }
            }
        }
    }

    sincronizarStatsCategoria($slug);
    salida(['ok' => true, 'dorsal' => $nuevoDorsal]);
    break;

case 'padre/agregar_hijo':
    auth(['admin']);
    $padreId = (int)($d['padreId'] ?? 0);
    $hijoData = $d['hijo'] ?? [];
    $nombreHijo = sanitizarTexto($hijoData['nombre'] ?? '');
    $edadHijo = max(3, min(50, (int)($hijoData['edad'] ?? 0)));
    $posHijo = in_array(strtoupper($hijoData['pos'] ?? ''), ['POR', 'DEF', 'MED', 'DEL'], true) ? strtoupper($hijoData['pos']) : 'MED';

    if ($padreId <= 0 || $nombreHijo === '' || $edadHijo === 0) {
        salida(['ok' => false, 'error' => 'Datos incompletos para agregar hijo'], 400);
    }

    // Auto-asignar sub por edad
    $clavesCats = obtenerCategoriasClaves();
    $catElegida = 'submayor';
    $mapaNums = [];
    foreach ($clavesCats as $ck) {
        if (preg_match('/^sub_?(\d+)$/i', $ck, $m)) {
            $mapaNums[(int)$m[1]] = $ck;
        }
    }
    ksort($mapaNums);
    foreach ($mapaNums as $lim => $ck) {
        if ($edadHijo <= $lim) {
            $catElegida = $ck;
            break;
        }
    }
    if (!in_array($catElegida, $clavesCats, true)) {
        $catElegida = in_array('submayor', $clavesCats, true) ? 'submayor' : ($clavesCats[count($clavesCats) - 1] ?? 'sub6');
    }

    // Buscar datos del padre en las tablas de padres
    $padreEncontrado = null;
    $tablaPadreOrigen = null;
    $catPadreOrigen = null;
    foreach ($clavesCats as $ck) {
        [, , $tPad] = asegurarTablasCategoria($ck);
        $resP = q("SELECT * FROM `$tPad` WHERE id = ? LIMIT 1", 'i', [$padreId]);
        if (!empty($resP)) {
            $padreEncontrado = $resP[0];
            $tablaPadreOrigen = $tPad;
            $catPadreOrigen = $ck;
            break;
        }
    }

    if (!$padreEncontrado) {
        salida(['ok' => false, 'error' => 'Padre no encontrado en el sistema'], 404);
    }

    $padreUsuario = $padreEncontrado['usuario'];
    $padreNombre = $padreEncontrado['nombre'];
    $padreTel = $padreEncontrado['telefono'] ?? '';
    $padrePass = $padreEncontrado['password'] ?? '';
    $padreVenc = $padreEncontrado['fecha_vencimiento'] ?? calcularFechaVencimiento30();
    $padreMetodo = $padreEncontrado['metodo_pago'] ?? 'linea';

    // Crear el nuevo jugador en la tabla de su categoría
    [$tJugDest, $tStatsDest, $tPadDest] = asegurarTablasCategoria($catElegida);
    $newPlayerId = siguienteJugadorIdGlobal();

    q("INSERT INTO `$tJugDest` (id, cat_key, name, edad, dorsal, pos, foto) VALUES (?, ?, ?, ?, 0, ?, 'img/escudo.png')",
      'issis', [$newPlayerId, $catElegida, $nombreHijo, $edadHijo, $posHijo]);
    sincronizarStatsCategoria($catElegida);

    $mapaLabels = obtenerMapaLabelsCategorias();
    $subLabel = $mapaLabels[$catElegida] ?? strtoupper($catElegida);

    $nuevoHijoObj = [
        'id'       => $newPlayerId,
        'nombre'   => $nombreHijo,
        'edad'     => $edadHijo,
        'pos'      => $posHijo,
        'dorsal'   => 0,
        'catKey'   => $catElegida,
        'sub'      => $subLabel,
        'catLabel' => $subLabel
    ];

    // Obtener los hijos actuales del padre (consolidando de todas las tablas de padres para este usuario)
    $todosHijosPadre = [];
    $vistosHijos = [];
    foreach ($clavesCats as $ck) {
        [, , $tPad] = asegurarTablasCategoria($ck);
        $filasP = q("SELECT * FROM `$tPad` WHERE LOWER(usuario) = LOWER(?)", 's', [$padreUsuario]);
        foreach ($filasP as $fp) {
            $norm = normalizarFilaPadre($fp, $ck);
            if (!empty($norm['hijos']) && is_array($norm['hijos'])) {
                foreach ($norm['hijos'] as $h) {
                    $k = !empty($h['id']) ? ('id_' . $h['id']) : (strtolower($h['nombre'] ?? '') . '_' . ($h['catKey'] ?? ''));
                    if (!isset($vistosHijos[$k])) {
                        $vistosHijos[$k] = true;
                        $todosHijosPadre[] = $h;
                    }
                }
            }
        }
    }

    // Agregar el nuevo hijo
    $todosHijosPadre[] = $nuevoHijoObj;
    $jsonHijos = json_encode($todosHijosPadre, JSON_UNESCAPED_UNICODE);

    // Actualizar hijos_json en todos los registros existentes del padre
    foreach ($clavesCats as $ck) {
        [, , $tPad] = asegurarTablasCategoria($ck);
        q("UPDATE `$tPad` SET hijos_json = ? WHERE LOWER(usuario) = LOWER(?)", 'ss', [$jsonHijos, $padreUsuario]);
    }

    // Asegurarse de que el padre también exista en la categoría del nuevo hijo ($tPadDest)
    $existeEnDest = q("SELECT id FROM `$tPadDest` WHERE LOWER(usuario) = LOWER(?) LIMIT 1", 's', [$padreUsuario]);
    if (empty($existeEnDest)) {
        q("INSERT INTO `$tPadDest` (id, usuario, nombre, telefono, sub, hijo_cat, hijo_id, hijo_nombre, hijo_edad, hijo_dorsal, hijo_pos, hijos_json, metodo_pago, fecha_vencimiento, bloqueado, password)
           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, 0, ?)",
          'isssssisisssss', [
              $padreId,
              $padreUsuario,
              $padreNombre,
              $padreTel,
              $subLabel,
              $catElegida,
              $newPlayerId,
              $nombreHijo,
              $edadHijo,
              $posHijo,
              $jsonHijos,
              $padreMetodo,
              $padreVenc,
              $padrePass
          ]);
    }

    salida([
        'ok'   => true,
        'msg'  => "Hijo $nombreHijo agregado exitosamente",
        'hijo' => $nuevoHijoObj
    ]);
    break;

default:
    salida(['error' => 'Operación desconocida: ' . sanitizarTexto($op)], 400);
}
salida(['ok' => true]);