<?php
/* api/registro_publico.php — Registro temporal blindado (Nombre Padre = Usuario + Contraseña 12+ Alfanumérica + Auto-Sub por Edad) */
require_once __DIR__ . '/../core/conexion.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    salida(['ok' => false, 'error' => 'Método no permitido'], 405);
}

// 1. Protección Anti-CSRF
$csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfHeader) || !verificarCsrfToken($csrfHeader)) {
    salida(['ok' => false, 'error' => 'Sesión de seguridad inválida. Recarga la página e intenta nuevamente.'], 403);
}

// 2. Protección Anti-Fuerza Bruta / Rate Limit por IP y por Sesión
$rawIp = $_SERVER['HTTP_CF_CONNECTING_IP'] 
      ?? $_SERVER['HTTP_X_REAL_IP'] 
      ?? $_SERVER['HTTP_X_FORWARDED_FOR'] 
      ?? $_SERVER['REMOTE_ADDR'] 
      ?? '0.0.0.0';
$ipReg = trim(explode(',', $rawIp)[0]);
$rlDirReg = __DIR__ . '/../uploads/ratelimit';
if (!is_dir($rlDirReg)) @mkdir($rlDirReg, 0750, true);
$rlFileReg = $rlDirReg . '/reg_' . md5($ipReg) . '.json';
$ahora = time();

$regData = ['intentos' => 0, 'expira' => $ahora + 60];
if (is_file($rlFileReg)) {
    $decR = @json_decode((string)@file_get_contents($rlFileReg), true);
    if (is_array($decR) && isset($decR['expira'], $decR['intentos']) && $ahora < (int)$decR['expira']) {
        $regData = $decR;
        if ((int)$decR['intentos'] >= 5) {
            $rest = max(1, (int)$decR['expira'] - $ahora);
            header('Retry-After: ' . $rest);
            salida(['ok' => false, 'error' => "Demasiados intentos de registro. Intente nuevamente en {$rest} segundos."], 429);
        }
    }
}

$regData['intentos'] = (int)($regData['intentos'] ?? 0) + 1;
@file_put_contents($rlFileReg, json_encode($regData), LOCK_EX);

if (!empty($_SESSION['reg_bloqueo_hasta']) && $ahora < (int)$_SESSION['reg_bloqueo_hasta']) {
    $minRestantes = max(1, (int)ceil((((int)$_SESSION['reg_bloqueo_hasta']) - $ahora) / 60));
    salida(['ok' => false, 'error' => "Acceso temporalmente bloqueado por seguridad. Espera $minRestantes minuto(s)."], 429);
}

$d = entrada();

// 3. Trampa Anti-Bots (Honeypot)
if (!empty($d['website_hp'])) {
    $_SESSION['reg_bloqueo_hasta'] = $ahora + 1800;
    salida(['ok' => false, 'error' => 'Solicitud bloqueada por el filtro de seguridad.'], 403);
}

// 4. Escudo Interno Anti-SQL Injection (SQLi) y Anti-XSS en todos los campos recibidos
foreach ($d as $campo => $valor) {
    if (is_string($valor) && contieneAtaqueInyeccion($valor)) {
        $_SESSION['reg_bloqueo_hasta'] = $ahora + 1800;
        error_log("[ALERTA SEGURIDAD REGISTRO] Intento de inyección bloqueado en campo '$campo': " . substr($valor, 0, 120));
        salida(['ok' => false, 'error' => 'Alerta de Seguridad: Se detectaron caracteres o comandos no permitidos. Tu sesión ha sido bloqueada.'], 403);
    }
}

$tokenRecibido  = trim((string)($d['token'] ?? ''));
$codigoRecibido = strtoupper(trim((string)($d['codigoAcademia'] ?? '')));

// 5. Obtener el ciclo activo de 7 días del Token de la Academia
$linkInfo = obtenerOGenerarTokenCiclo7Dias();

// Si el cliente envía token firmado en la URL sin código, se valida su integridad
if (!empty($tokenRecibido) && empty($codigoRecibido)) {
    if (strpos($tokenRecibido, '.') !== false) {
        $payloadRecibido = verificarTokenFirmado($tokenRecibido);
        if ($payloadRecibido === null || !validarTokenConFirma($tokenRecibido, (string)$linkInfo['token'])) {
            salida(['ok' => false, 'error' => 'Firma digital de token inválida o caducada. Ingresa el código semanal de la academia.'], 403);
        }
    }
}

// 6. Verificar Código Oficial de Verificación del Club (Ciclo de 7 Días)
$codigoOficial = strtoupper(trim((string)($linkInfo['codigoAcademia'] ?? '')));
$codigoLimpio  = strtoupper(preg_replace('/[^A-Z0-9]/', '', $codigoRecibido));
$oficialLimpio = strtoupper(preg_replace('/[^A-Z0-9]/', '', $codigoOficial));

if ($oficialLimpio === '' || $codigoLimpio === '' || !hash_equals($oficialLimpio, $codigoLimpio)) {
    $_SESSION['reg_intentos_fallidos'] = (int)($_SESSION['reg_intentos_fallidos'] ?? 0) + 1;
    if ($_SESSION['reg_intentos_fallidos'] >= 5) {
        $_SESSION['reg_bloqueo_hasta'] = $ahora + 900;
        $_SESSION['reg_intentos_fallidos'] = 0;
        salida(['ok' => false, 'error' => 'Has superado el límite de intentos del Código de Verificación. Bloqueado por 15 minutos.'], 429);
    }
    $restantes = 5 - $_SESSION['reg_intentos_fallidos'];
    salida(['ok' => false, 'error' => "El Código de Verificación del Club es incorrecto o caducó ($restantes intentos restantes). Solicita el código semanal vigente a la administración."], 403);
}

$_SESSION['reg_intentos_fallidos'] = 0;
unset($_SESSION['reg_bloqueo_hasta']);

// 7. Validación de Datos del Padre y Acceso
$padreNombre = trim(sanitizarTexto($d['padreNombre'] ?? ''));
$telefono    = trim(sanitizarTexto($d['telefonoPadre'] ?? ''));
$usuario     = $padreNombre; // El nombre del padre es directamente su usuario de acceso
$passwordRaw = trim((string)($d['padrePassword'] ?? ''));

if (!preg_match('/^[\p{L}0-9\s._\'-]{3,80}$/u', $padreNombre)) {
    salida(['ok' => false, 'error' => 'Ingresa el nombre del padre/madre (mínimo 3 caracteres, sin símbolos especiales).'], 400);
}
if (!preg_match('/^[0-9+\-\s()]{6,20}$/', $telefono)) {
    salida(['ok' => false, 'error' => 'Ingresa un número de teléfono válido (solo números, guiones o espacios).'], 400);
}

// 8. Validar Contraseña Segura (Estándar Ciberseguridad: >= 12 caracteres + letras + números)
$errPass = validarPasswordSegura12($passwordRaw);
if ($errPass !== null) {
    salida(['ok' => false, 'error' => $errPass], 400);
}
if (strlen($passwordRaw) > 64) {
    salida(['ok' => false, 'error' => 'La contraseña no puede exceder 64 caracteres.'], 400);
}

// 9. Verificar que el nombre/usuario del padre no esté ocupado
$existente = buscarUsuarioGlobalPorLogin($usuario);
if ($existente !== null) {
    salida(['ok' => false, 'error' => 'Ya existe un registro con el nombre "' . $usuario . '". Agrega tu segundo apellido o un número (ej: ' . $usuario . ' 2).'], 409);
}

// 10. Recopilar y Validar la Lista de Hijos / Jugadores (Multi-Hijo o Individual)
$hijosEntrada = [];
if (!empty($d['hijos']) && is_array($d['hijos'])) {
    $hijosEntrada = $d['hijos'];
} else {
    $hijosEntrada = [[
        'nombre' => $d['jugadorNombre'] ?? '',
        'edad'   => $d['jugadorEdad'] ?? 0,
        'pos'    => $d['jugadorPos'] ?? 'MED'
    ]];
}

if (empty($hijosEntrada)) {
    salida(['ok' => false, 'error' => 'Debes registrar al menos un hijo/jugador.'], 400);
}

$hijosValidados = [];
foreach ($hijosEntrada as $idx => $h) {
    $numHijo = $idx + 1;
    $nomH = trim(sanitizarTexto($h['nombre'] ?? ''));
    $edH  = (int)($h['edad'] ?? 0);
    $posH = strtoupper(trim(sanitizarTexto($h['pos'] ?? 'MED')));

    if (!preg_match('/^[\p{L}\s.\'-]{2,80}$/u', $nomH)) {
        salida(['ok' => false, 'error' => "El nombre del Hijo #$numHijo solo puede contener letras y espacios (mínimo 2 caracteres)."], 400);
    }
    if ($edH < 3 || $edH > 50) {
        salida(['ok' => false, 'error' => "Ingresa una edad válida para el Hijo #$numHijo (entre 3 y 50 años)."], 400);
    }
    if (!in_array($posH, ['POR', 'DEF', 'MED', 'DEL'], true)) {
        $posH = 'MED';
    }
    $hijosValidados[] = [
        'nombre' => $nomH,
        'edad'   => $edH,
        'pos'    => $posH
    ];
}

// 11. Inscribir a Cada Hijo en su respectiva Categoría (Sub) con Dorsal 0 (#S/N)
$mapaLabels = obtenerMapaLabelsCategorias();
$hijosRegistrados = [];

// Pre-asegurar todas las tablas necesarias ANTES de iniciar la transacción (evita commit implícito por DDL en MySQL)
$tablasPorHijo = [];
$catsAfectadas = [];
foreach ($hijosValidados as $idxH => $hVal) {
    $catSlug = calcularSubPorEdad($hVal['edad']);
    [$tJug, $tStats, $tPad] = asegurarTablasCategoria($catSlug);
    $tablasPorHijo[$idxH] = [
        'catSlug' => $catSlug,
        'tJug'    => $tJug,
        'tStats'  => $tStats,
        'tPadres' => $tPad
    ];
    $catsAfectadas[$catSlug] = true;
}

$pdoConn = pdo();
try {
    $pdoConn->beginTransaction();

    foreach ($hijosValidados as $idxH => $hVal) {
        $infoT = $tablasPorHijo[$idxH];
        $catSlug = $infoT['catSlug'];
        $tJug = $infoT['tJug'];
        $tStats = $infoT['tStats'];
        $subLabel = $mapaLabels[$catSlug] ?? strtoupper($catSlug);

        $playerId = (float)siguienteJugadorIdGlobal() + count($hijosRegistrados);
        $dorsalInicial = 0; // El profesor o admin lo asigna luego en Categorías

        $stInsJ = $pdoConn->prepare("INSERT INTO `$tJug` (id, cat_key, name, edad, dorsal, pos, foto)
            VALUES (?, ?, ?, ?, ?, ?, 'img/escudo.png')
            ON DUPLICATE KEY UPDATE name=VALUES(name), edad=VALUES(edad), pos=VALUES(pos)");
        $stInsJ->execute([$playerId, $catSlug, $hVal['nombre'], $hVal['edad'], $dorsalInicial, $hVal['pos']]);

        $stInsS = $pdoConn->prepare("INSERT INTO `$tStats` (player_id, dorsal, nombre, cat_key, goles, asistencias, mvp)
            VALUES (?, ?, ?, ?, 0, 0, 0)
            ON DUPLICATE KEY UPDATE dorsal=VALUES(dorsal), nombre=VALUES(nombre)");
        $stInsS->execute([$playerId, $dorsalInicial, $hVal['nombre'], $catSlug]);

        $hijosRegistrados[] = [
            'id'       => $playerId,
            'nombre'   => $hVal['nombre'],
            'edad'     => $hVal['edad'],
            'catKey'   => $catSlug,
            'catLabel' => $subLabel,
            'sub'      => $subLabel,
            'dorsal'   => $dorsalInicial,
            'pos'      => $hVal['pos']
        ];
    }

    // 12. Crear la Cuenta del Padre en padres_<subX> vinculando todos los hijos en hijos_json
    $primerHijo = $hijosRegistrados[0];
    $catPrincipal = $primerHijo['catKey'];
    $tPadresPrincipal = $tablasPorHijo[0]['tPadres'];

    $idPadre   = siguienteUsuarioIdGlobal();
    $venc30    = calcularFechaVencimiento30();
    $passHash  = password_hash($passwordRaw, PASSWORD_BCRYPT);
    $hijosJson = json_encode($hijosRegistrados, JSON_UNESCAPED_UNICODE);

    $stInsP = $pdoConn->prepare("REPLACE INTO `$tPadresPrincipal`
       (id, usuario, nombre, telefono, sub, hijo_cat, hijo_id, hijo_nombre, hijo_edad, hijo_dorsal, hijo_pos, hijos_json, metodo_pago, fecha_vencimiento, bloqueado, password)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'linea', ?, 0, ?)");
    $stInsP->execute([
        $idPadre,
        $usuario,
        $padreNombre,
        $telefono,
        $primerHijo['catLabel'],
        $primerHijo['catKey'],
        $primerHijo['id'],
        $primerHijo['nombre'],
        $primerHijo['edad'],
        $primerHijo['dorsal'],
        $primerHijo['pos'],
        $hijosJson,
        $venc30,
        $passHash
    ]);

    // Si registró varios hijos en diferentes categorías, replicar el registro del padre para que aparezca en todas
    foreach ($hijosRegistrados as $idxH => $hExtra) {
        $cExtra = slugTablaCat($hExtra['catKey'] ?? '');
        if ($cExtra !== '' && $cExtra !== $catPrincipal) {
            $tPExtra = $tablasPorHijo[$idxH]['tPadres'];
            $labelExtra = $mapaLabels[$cExtra] ?? strtoupper($cExtra);
            $stInsPE = $pdoConn->prepare("REPLACE INTO `$tPExtra`
               (id, usuario, nombre, telefono, sub, hijo_cat, hijo_id, hijo_nombre, hijo_edad, hijo_dorsal, hijo_pos, hijos_json, metodo_pago, fecha_vencimiento, bloqueado, password)
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'linea', ?, 0, ?)");
            $stInsPE->execute([
                $idPadre,
                $usuario,
                $padreNombre,
                $telefono,
                $labelExtra,
                $cExtra,
                $hExtra['id'],
                $hExtra['nombre'],
                $hExtra['edad'],
                $hExtra['dorsal'],
                $hExtra['pos'],
                $hijosJson,
                $venc30,
                $passHash
            ]);
        }
    }

    if ($pdoConn->inTransaction()) {
        $pdoConn->commit();
    }

    foreach (array_keys($catsAfectadas) as $catAfectada) {
        sincronizarStatsCategoria($catAfectada);
    }
} catch (Exception $e) {
    if ($pdoConn->inTransaction()) $pdoConn->rollBack();
    error_log('[Transaction Error registro_publico] ' . $e->getMessage());
    salida(['ok' => false, 'error' => 'Error al procesar la inscripción de los jugadores y la familia en el servidor: ' . $e->getMessage()], 500);
}

salida([
    'ok' => true,
    'resumen' => [
        'jugadorNombre' => $primerHijo['nombre'],
        'jugadorEdad'   => $primerHijo['edad'],
        'subLabel'      => $primerHijo['catLabel'],
        'pos'           => $primerHijo['pos'],
        'dorsalTexto'   => 'Por asignar por el Profesor',
        'hijos'         => $hijosRegistrados,
        'cantidadHijos' => count($hijosRegistrados),
        'padreNombre'   => $padreNombre,
        'telefono'      => $telefono,
        'usuario'       => $usuario,
        'vencimiento'   => $venc30
    ]
]);