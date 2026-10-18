<?php
ob_start();
/* ============================================================================
 * public/soporte.php — Portal de Soporte Técnico, Cortafuegos WAF y Logs
 * ----------------------------------------------------------------------------
 * Acceso protegido para soporte técnico e ingenieros de seguridad.
 * Permite:
 *  1. Gestión total de bloqueos de IP en el WAF (listar, bloquear manual, desbloquear).
 *  2. Visualización y rotación en vivo de alertas del Honeypot (rutas trampa).
 *  3. Gestión y cambio de credenciales de la cuenta de soporte en MySQL.
 * ============================================================================ */

if (file_exists(__DIR__ . '/core/conexion.php')) {
    require_once __DIR__ . '/core/conexion.php';
} else {
    require_once __DIR__ . '/../core/conexion.php';
}

header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet');

// Asegurar que exista al menos una cuenta de soporte en la base de datos
asegurarUsuarioSoporte();

// Descarga directa y segura de archivos comprimidos de logs .log.gz
if (isset($_GET['descargar_gz'])) {
    if (empty($_SESSION['ns_soporte_auth'])) {
        http_response_code(403);
        exit('Acceso denegado');
    }
    $archivoDescargar = basename((string)$_GET['descargar_gz']);
    $rutaCompleta = __DIR__ . '/../uploads/logs_archive/' . $archivoDescargar;
    if (is_file($rutaCompleta) && preg_match('/^waf_archive_[0-9_]+\.log\.gz$/', $archivoDescargar)) {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/gzip');
        header('Content-Disposition: attachment; filename="' . $archivoDescargar . '"');
        header('Content-Length: ' . filesize($rutaCompleta));
        header('Cache-Control: no-cache, must-revalidate');
        readfile($rutaCompleta);
        exit;
    }
}

$csrfToken = $_SESSION['csrf_token'] ?? '';
$cspNonce  = $_SESSION['csp_nonce'] ?? '';
$nonceAttr = !empty($cspNonce) ? ' nonce="' . htmlspecialchars($cspNonce, ENT_QUOTES, 'UTF-8') . '"' : '';

$mensajeFlash = null;
$tipoFlash = 'info';

// Detectar IP real para rate-limiting
$rawIp = $_SERVER['HTTP_CF_CONNECTING_IP'] 
      ?? $_SERVER['HTTP_X_REAL_IP'] 
      ?? $_SERVER['HTTP_X_FORWARDED_FOR'] 
      ?? $_SERVER['REMOTE_ADDR'] 
      ?? '0.0.0.0';
$ipVisitante = trim(explode(',', $rawIp)[0]);

// ==========================================
// PROCESAR ACCIONES POST
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokenRecibido = $_POST['csrf_token'] ?? '';
    if (!verificarCsrfToken($tokenRecibido)) {
        $mensajeFlash = 'Error de validación de seguridad (CSRF). Por favor recarga e intenta nuevamente.';
        $tipoFlash = 'error';
    } else {
        $accion = trim((string)($_POST['accion'] ?? ''));

        // 1. LOGIN DE SOPORTE
        if ($accion === 'login') {
            // Trampa Honeypot
            if (!empty($_POST['soporte_hp'])) {
                $mensajeFlash = 'Petición rechazada por el filtro de seguridad.';
                $tipoFlash = 'error';
            } else {
                // Rate limit: 5 intentos / 60 segundos por IP
                $rlDir = __DIR__ . '/../uploads/ratelimit';
                if (!is_dir($rlDir)) @mkdir($rlDir, 0750, true);
                $rlFile = $rlDir . '/sop_rl_' . md5($ipVisitante) . '.json';
                $ahora = time();
                $rlData = ['intentos' => 0, 'expira' => $ahora + 60];
                if (is_file($rlFile)) {
                    $dec = @json_decode((string)@file_get_contents($rlFile), true);
                    if (is_array($dec) && isset($dec['expira'], $dec['intentos']) && $ahora < (int)$dec['expira']) {
                        $rlData = $dec;
                        if ((int)$dec['intentos'] >= 5) {
                            if (function_exists('wafBloquearIp')) {
                                wafBloquearIp($ipVisitante, 'BRUTE_FORCE_SOPORTE', 'Fuerza bruta: 5 intentos fallidos en acceso de soporte técnico', 3600);
                            }
                            $rest = max(1, (int)$dec['expira'] - $ahora);
                            header('Retry-After: ' . $rest);
                            $mensajeFlash = "Demasiados intentos fallidos. Tu IP ({$ipVisitante}) ha sido bloqueada por el cortafuegos de seguridad.";
                            $tipoFlash = 'error';
                        }
                    }
                }

                if ($tipoFlash !== 'error') {
                    $usuario = trim((string)($_POST['usuario'] ?? ''));
                    $password = (string)($_POST['password'] ?? '');

                    $uRows = q("SELECT id, usuario, password, rol, nombre FROM usuarios WHERE LOWER(usuario) = LOWER(?) AND rol = 'soporte' LIMIT 1", 's', [$usuario]);
                    if (!empty($uRows) && password_verify($password, $uRows[0]['password'])) {
                        // Login exitoso
                        @unlink($rlFile);
                        $_SESSION['ns_soporte_auth'] = true;
                        $_SESSION['ns_soporte_user_id'] = (int)$uRows[0]['id'];
                        $_SESSION['ns_soporte_user'] = $uRows[0]['usuario'];
                        $_SESSION['ns_soporte_rol'] = $uRows[0]['rol'];
                        $_SESSION['ns_soporte_nombre'] = $uRows[0]['nombre'];
                        // Sincronizar también sesión general
                        $_SESSION['ns_user_id'] = (int)$uRows[0]['id'];
                        $_SESSION['ns_rol'] = $uRows[0]['rol'];
                        $_SESSION['ns_user'] = $uRows[0]['usuario'];
                        $_SESSION['ns_nombre'] = $uRows[0]['nombre'];

                        header('Location: soporte.php');
                        exit;
                    } else {
                        $rlData['intentos'] = (int)($rlData['intentos'] ?? 0) + 1;
                        @file_put_contents($rlFile, json_encode($rlData), LOCK_EX);
                        $mensajeFlash = 'Credenciales de acceso incorrectas o cuenta sin privilegios de soporte.';
                        $tipoFlash = 'error';
                    }
                }
            }
        }

        // VERIFICAR QUE EL USUARIO ESTÉ AUTENTICADO PARA LAS DEMÁS ACCIONES
        $estaAutenticado = !empty($_SESSION['ns_soporte_auth']) && !empty($_SESSION['ns_soporte_user_id']);

        if ($estaAutenticado) {
            // 2. LOGOUT
            if ($accion === 'logout') {
                unset($_SESSION['ns_soporte_auth'], $_SESSION['ns_soporte_user_id'], $_SESSION['ns_soporte_user'], $_SESSION['ns_soporte_rol'], $_SESSION['ns_soporte_nombre']);
                header('Location: soporte.php');
                exit;
            }

            // 3. BLOQUEAR IP MANUALMENTE
            if ($accion === 'bloquear_ip') {
                $ipTarget = trim((string)($_POST['ip'] ?? ''));
                $horas = max(1, min(720, (int)($_POST['horas'] ?? 24)));
                $motivo = trim((string)($_POST['motivo'] ?? 'Bloqueo manual desde Soporte Técnico'));
                if (!filter_var($ipTarget, FILTER_VALIDATE_IP)) {
                    $mensajeFlash = 'Por favor introduce una dirección IP válida (ej: 198.51.100.12).';
                    $tipoFlash = 'error';
                } elseif (wafEsIpCloudflare($ipTarget) || in_array($ipTarget, ['127.0.0.1', '::1', '0.0.0.0'], true)) {
                    $mensajeFlash = 'Por seguridad no se puede bloquear localhost ni los servidores de Cloudflare.';
                    $tipoFlash = 'error';
                } else {
                    wafBloquearIp($ipTarget, $motivo, 'Bloqueo Manual Soporte Técnico', $horas * 3600);
                    $mensajeFlash = "La dirección IP {$ipTarget} fue bloqueada exitosamente por {$horas} hora(s).";
                    $tipoFlash = 'exito';
                }
            }

            // 4. DESBLOQUEAR IP O DISPOSITIVO ESPECÍFICO
            if ($accion === 'desbloquear_ip' || $accion === 'desbloquear') {
                $target = trim((string)($_POST['target_id'] ?? $_POST['ip'] ?? ''));
                if ($target === 'TODAS' || $target === 'TODOS') {
                    wafLimpiarTodosBloqueos();
                    $mensajeFlash = 'Todos los bloqueos (dispositivos e IPs) han sido levantados con éxito del WAF.';
                    $tipoFlash = 'exito';
                } elseif ($target !== '') {
                    wafDesbloquear($target);
                    $mensajeFlash = "El bloqueo fue removido correctamente del cortafuegos WAF.";
                    $tipoFlash = 'exito';
                } else {
                    $mensajeFlash = 'Identificador o IP no válido para desbloquear.';
                    $tipoFlash = 'error';
                }
            }

            // 5a. COMPRIMIR Y ARCHIVAR LOGS (> 7 días) CON GZIP
            if ($accion === 'comprimir_logs') {
                $dias = max(1, (int)($_POST['dias_corte'] ?? 7));
                $resComp = wafRotarYComprimirLogs($dias);
                $mensajeFlash = $resComp['mensaje'] ?? 'Operación completada.';
                $tipoFlash = !empty($resComp['comprimidos']) ? 'exito' : 'info';
            }

            // 5b. PURGAR ARCHIVOS HISTÓRICOS COMPRIMIDOS (.GZ) DEL DISCO
            if ($accion === 'purgar_archivos_gz') {
                $borrados = wafPurgarArchivosComprimidos();
                $mensajeFlash = "Se eliminaron {$borrados} archivo(s) comprimido(s) de logs del disco. Espacio liberado con éxito.";
                $tipoFlash = 'exito';
            }

            // 5c. VACIAR LOGS ACTIVOS (Honeypot + WAF Security)
            if ($accion === 'limpiar_logs') {
                $hpLogFile = __DIR__ . '/../uploads/honeypot_events.log';
                $secLogFile = __DIR__ . '/../uploads/waf_security.log';
                if (is_file($hpLogFile)) @file_put_contents($hpLogFile, '', LOCK_EX);
                if (is_file($secLogFile)) @file_put_contents($secLogFile, '', LOCK_EX);
                $mensajeFlash = 'Los registros activos de seguridad fueron vaciados exitosamente.';
                $tipoFlash = 'exito';
            }

            // 6. CAMBIAR CREDENCIALES DE SOPORTE
            if ($accion === 'cambiar_credenciales') {
                $userId = (int)$_SESSION['ns_soporte_user_id'];
                $passActual = trim((string)($_POST['pass_actual'] ?? ''));
                $usuarioNuevo = trim(sanitizarTexto($_POST['usuario_nuevo'] ?? ''));
                $passNuevo = trim((string)($_POST['pass_nuevo'] ?? ''));
                $passConfirm = trim((string)($_POST['pass_confirm'] ?? ''));

                $rowUsr = q("SELECT id, usuario, password FROM usuarios WHERE id = ? LIMIT 1", 'i', [$userId]);
                if (empty($rowUsr) || !password_verify($passActual, $rowUsr[0]['password'])) {
                    $mensajeFlash = 'La contraseña actual no es correcta.';
                    $tipoFlash = 'error';
                } else {
                    $cambios = [];
                    $usuarioFinal = $rowUsr[0]['usuario'];

                    if ($usuarioNuevo !== '' && strtolower($usuarioNuevo) !== strtolower($rowUsr[0]['usuario'])) {
                        $chkCooldown = puedeCambiarNombreUsuario('soporte');
                        if (!$chkCooldown['puede']) {
                            $mensajeFlash = "Por políticas de seguridad, el nombre de usuario de soporte solo puede modificarse una vez cada 7 días. Podrás cambiarlo nuevamente en {$chkCooldown['diasRestantes']} día(s). (La contraseña sí puedes cambiarla cuando gustes).";
                            $tipoFlash = 'error';
                        } elseif (!preg_match('/^[a-zA-Z0-9._\-]{3,50}$/', $usuarioNuevo)) {
                            $mensajeFlash = 'El nuevo usuario debe tener entre 3 y 50 caracteres (letras, números, puntos o guiones).';
                            $tipoFlash = 'error';
                        } else {
                            $dup = q("SELECT id FROM usuarios WHERE LOWER(usuario) = LOWER(?) AND id != ? LIMIT 1", 'si', [$usuarioNuevo, $userId]);
                            if (!empty($dup)) {
                                $mensajeFlash = "El nombre de usuario '{$usuarioNuevo}' ya está en uso.";
                                $tipoFlash = 'error';
                            } else {
                                $usuarioFinal = $usuarioNuevo;
                                $cambios['usuario'] = $usuarioNuevo;
                            }
                        }
                    }

                    if ($tipoFlash !== 'error' && $passNuevo !== '') {
                        if ($passNuevo !== $passConfirm) {
                            $mensajeFlash = 'La confirmación de la nueva contraseña no coincide.';
                            $tipoFlash = 'error';
                        } else {
                            $errP = validarPasswordSegura12($passNuevo);
                            if ($errP !== null) {
                                $mensajeFlash = $errP;
                                $tipoFlash = 'error';
                            } else {
                                $cambios['password'] = password_hash($passNuevo, PASSWORD_BCRYPT);
                            }
                        }
                    }

                    if ($tipoFlash !== 'error') {
                        if (empty($cambios)) {
                            $mensajeFlash = 'No especificaste ningún cambio a realizar.';
                            $tipoFlash = 'info';
                        } else {
                            if (isset($cambios['usuario'], $cambios['password'])) {
                                q("UPDATE usuarios SET usuario = ?, password = ? WHERE id = ? AND rol = 'soporte'", 'ssi', [$cambios['usuario'], $cambios['password'], $userId]);
                            } elseif (isset($cambios['usuario'])) {
                                q("UPDATE usuarios SET usuario = ? WHERE id = ? AND rol = 'soporte'", 'si', [$cambios['usuario'], $userId]);
                            } elseif (isset($cambios['password'])) {
                                q("UPDATE usuarios SET password = ? WHERE id = ? AND rol = 'soporte'", 'si', [$cambios['password'], $userId]);
                            }
                            if (isset($cambios['usuario'])) {
                                registrarCambioNombreUsuario('soporte');
                            }
                            $_SESSION['ns_soporte_user'] = $usuarioFinal;
                            $_SESSION['ns_user'] = $usuarioFinal;
                            $mensajeFlash = 'Credenciales de soporte actualizadas exitosamente en la base de datos.';
                            $tipoFlash = 'exito';
                        }
                    }
                }
            }
        }
    }
}

$estaAutenticado = !empty($_SESSION['ns_soporte_auth']) && !empty($_SESSION['ns_soporte_user_id']);
$ipsBloqueadas = $estaAutenticado ? wafObtenerIpsBloqueadas() : [];
$cooldownSoporte = $estaAutenticado ? puedeCambiarNombreUsuario('soporte') : ['puede' => true, 'diasRestantes' => 0];
$statsVisitas = $estaAutenticado ? wafObtenerEstadisticasVisitas() : [];
$archivosComprimidos = $estaAutenticado ? wafObtenerArchivosComprimidos() : [];
$pesoLogsActivos = $estaAutenticado ? wafObtenerTamanoLogsActivos() : ['total_formato' => '0 KB', 'archivos' => []];

// Cargar últimos eventos del honeypot si está autenticado
$eventosHoneypot = [];
if ($estaAutenticado) {
    $hpLogFile = __DIR__ . '/../uploads/honeypot_events.log';
    if (is_file($hpLogFile)) {
        $lineas = @file($hpLogFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (is_array($lineas)) {
            $lineas = array_reverse($lineas);
            foreach (array_slice($lineas, 0, 50) as $ln) {
                $ev = @json_decode($ln, true);
                if (is_array($ev)) {
                    $eventosHoneypot[] = $ev;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex, nofollow, noarchive, nosnippet" />
  <title>Soporte Técnico & Cortafuegos WAF — Academia Nico Sport</title>
  <link rel="icon" type="image/png" href="img/escudo.png" />
  <script<?= $nonceAttr ?>>
    (function(){
      var _w = console.warn;
      console.warn = function(msg) {
        if (typeof msg === 'string' && msg.indexOf('cdn.tailwindcss.com') !== -1) return;
        _w.apply(console, arguments);
      };
    })();
  </script>
  <script<?= $nonceAttr ?> src="https://cdn.tailwindcss.com"></script>
  <script<?= $nonceAttr ?>>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            nico: {
              orange: '#FF5500',
              orangeDark: '#CC4400',
              green: '#00E676',
              greenDark: '#00B359',
              red: '#FF1744',
              blue: '#0A3D7A'
            }
          }
        }
      }
    };
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=JetBrains+Mono:wght@400;600;800&display=swap" rel="stylesheet" />
  <style>
    body { font-family: 'Montserrat', sans-serif; background: radial-gradient(circle at top, #141024 0%, #05040a 100%); color: #fff; min-height: 100vh; }
    .font-mono { font-family: 'JetBrains Mono', monospace; }
    .glass-card { background: rgba(18, 14, 28, 0.88); backdrop-filter: blur(16px); border: 1px solid rgba(168, 85, 247, 0.25); }
    .sop-input {
      width: 100%; background: rgba(0, 0, 0, 0.8); border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 0.75rem; padding: 0.75rem 0.95rem; font-size: 0.85rem; color: #fff; font-weight: 600;
      transition: all 0.2s; outline: none;
    }
    .sop-input:focus { border-color: #a855f7; box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.25); }
    .tab-btn.active { background: rgba(168, 85, 247, 0.2); border-color: rgba(168, 85, 247, 0.6); color: #fff; }
  </style>
</head>
<body class="p-4 sm:p-8 min-h-screen flex flex-col justify-between">

  <div class="w-full max-w-5xl mx-auto space-y-6">

    <!-- CABECERA PRINCIPAL -->
    <header class="flex flex-col sm:flex-row items-center justify-between gap-4 pb-4 border-b border-purple-500/20">
      <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-2xl bg-black/80 border-2 border-purple-500/50 p-1.5 flex items-center justify-center shadow-[0_0_20px_rgba(168,85,247,0.3)]">
          <img src="img/escudo.png" alt="Escudo Nico Sport" class="w-full h-full object-contain" />
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h1 class="text-xl font-black uppercase tracking-tight text-white">Soporte Técnico</h1>
            <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded bg-purple-500/20 text-purple-300 border border-purple-500/40 font-mono">WAF Guard</span>
          </div>
          <p class="text-[11px] text-gray-400">Cortafuegos OWASP, Control de Amenazas y Gestión de Servidor</p>
        </div>
      </div>

      <?php if ($estaAutenticado): ?>
        <div class="flex items-center gap-3">
          <div class="text-right hidden sm:block">
            <span class="text-[10px] text-gray-500 block">Conectado como</span>
            <span class="text-xs font-mono font-black text-purple-300"><?= htmlspecialchars($_SESSION['ns_soporte_user'] ?? 'soporte') ?></span>
            <span class="text-[9px] text-emerald-400 font-bold ml-1">(<?= htmlspecialchars(strtoupper($_SESSION['ns_soporte_rol'] ?? 'SOPORTE')) ?>)</span>
          </div>
          <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="accion" value="logout">
            <button type="submit" class="bg-red-600/20 hover:bg-red-600 text-red-300 hover:text-white border border-red-500/40 font-black text-xs px-3.5 py-2 rounded-xl transition-all flex items-center gap-1.5">
              <i class="fas fa-arrow-right-from-bracket"></i> Cerrar Sesión
            </button>
          </form>
        </div>
      <?php endif; ?>
    </header>

    <!-- MENSAJES FLASH -->
    <?php if ($mensajeFlash): ?>
      <div class="p-4 rounded-2xl border text-xs font-bold flex items-center gap-3 shadow-lg <?= $tipoFlash === 'error' ? 'bg-red-950/70 border-red-500/60 text-red-200' : ($tipoFlash === 'exito' ? 'bg-emerald-950/70 border-emerald-500/60 text-emerald-200' : 'bg-purple-950/70 border-purple-500/60 text-purple-200') ?>">
        <i class="fas <?= $tipoFlash === 'error' ? 'fa-triangle-exclamation text-base text-red-400' : ($tipoFlash === 'exito' ? 'fa-circle-check text-base text-emerald-400' : 'fa-circle-info text-base text-purple-400') ?>"></i>
        <span><?= htmlspecialchars($mensajeFlash) ?></span>
      </div>
    <?php endif; ?>

    <?php if (!$estaAutenticado): ?>
      <!-- PANTALLA DE LOGIN DE SOPORTE -->
      <div class="max-w-md mx-auto my-8">
        <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-5 shadow-2xl">
          <div class="text-center space-y-2">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-purple-500/10 border border-purple-500/30 flex items-center justify-center text-purple-400 text-2xl shadow-inner">
              <i class="fas fa-shield-halved"></i>
            </div>
            <h2 class="text-lg font-black uppercase text-white tracking-wide">Acceso al Cortafuegos WAF</h2>
            <p class="text-xs text-gray-400">Ingresa tus credenciales autorizadas de Soporte Técnico para gestionar el firewall del servidor.</p>
          </div>

          <form method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="accion" value="login">

            <!-- Honeypot invisible -->
            <div style="position:absolute;left:-9999px;opacity:0;pointer-events:none;" aria-hidden="true">
              <input type="text" name="soporte_hp" tabindex="-1" autocomplete="off" value="" />
            </div>

            <div>
              <label class="text-[10px] font-black uppercase text-gray-300 block mb-1">Usuario de Soporte</label>
              <div class="relative">
                <input type="text" name="usuario" required placeholder="soporte.tecnico.ans" autocomplete="username" class="sop-input font-mono pl-9" />
                <i class="fas fa-user-shield absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xs"></i>
              </div>
            </div>

            <div>
              <label class="text-[10px] font-black uppercase text-gray-300 block mb-1">Contraseña</label>
              <div class="relative">
                <input type="password" name="password" required placeholder="••••••••••••" autocomplete="current-password" class="sop-input font-mono pl-9" />
                <i class="fas fa-key absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xs"></i>
              </div>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-indigo-700 hover:from-purple-500 hover:to-indigo-600 text-white font-black uppercase text-xs py-3.5 rounded-xl shadow-[0_0_20px_rgba(168,85,247,0.4)] transition-all flex items-center justify-center gap-2">
              <i class="fas fa-lock"></i> Iniciar Sesión de Soporte
            </button>
          </form>

          <div class="pt-2 text-center">
            <a href="index.php" class="text-[11px] text-gray-400 hover:text-white transition-colors">
              <i class="fas fa-arrow-left mr-1"></i> Volver a la Web Principal
            </a>
          </div>
        </div>
      </div>

    <?php else: ?>

      <!-- PANEL PRINCIPAL AUTENTICADO DE SOPORTE -->
      <div class="space-y-6">

        <!-- RESUMEN DE ESTADO RÁPIDO -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
          <div class="glass-card rounded-2xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-500/20 border border-purple-500/40 flex items-center justify-center text-purple-400 text-lg">
              <i class="fas fa-shield-virus"></i>
            </div>
            <div>
              <span class="text-[10px] text-gray-400 uppercase font-black block">WAF Activo</span>
              <span class="text-xs sm:text-sm font-black text-emerald-400 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span> 100% Protegido
              </span>
            </div>
          </div>

          <div class="glass-card rounded-2xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-500/20 border border-blue-500/40 flex items-center justify-center text-blue-400 text-lg">
              <i class="fas fa-chart-line"></i>
            </div>
            <div>
              <span class="text-[10px] text-gray-400 uppercase font-black block">Visitas Hoy</span>
              <span class="text-xs sm:text-sm font-black font-mono text-white"><?= (int)($statsVisitas['hoy']['unicas'] ?? 0) ?> <span class="text-[10px] text-gray-400 font-normal">(<?= (int)($statsVisitas['hoy']['paginas'] ?? 0) ?> págs)</span></span>
            </div>
          </div>

          <div class="glass-card rounded-2xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-red-500/20 border border-red-500/40 flex items-center justify-center text-red-400 text-lg">
              <i class="fas fa-ban"></i>
            </div>
            <div>
              <span class="text-[10px] text-gray-400 uppercase font-black block">IPs Bloqueadas</span>
              <span class="text-xs sm:text-sm font-black font-mono text-white"><?= count($ipsBloqueadas) ?> activa(s)</span>
            </div>
          </div>

          <div class="glass-card rounded-2xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center text-emerald-400 text-lg">
              <i class="fas fa-hard-drive"></i>
            </div>
            <div>
              <span class="text-[10px] text-gray-400 uppercase font-black block">Espacio Logs</span>
              <span class="text-xs sm:text-sm font-black font-mono text-emerald-300"><?= htmlspecialchars($pesoLogsActivos['total_formato'] ?? '0 KB') ?> <span class="text-[9px] text-gray-400 block font-normal">(0 MB en MySQL)</span></span>
            </div>
          </div>
        </div>

        <!-- SECCIÓN 0: MÉTRICAS DE TRÁFICO WEB Y RASTREADORES SEO (GOOGLEBOT) -->
        <section class="glass-card rounded-3xl p-5 sm:p-7 space-y-4 border-blue-500/40">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-white/10">
            <div>
              <h2 class="text-base font-black uppercase text-white flex items-center gap-2">
                <i class="fas fa-chart-pie text-blue-400"></i> Métricas de Tráfico Web & Rastreadores SEO
              </h2>
              <p class="text-[11px] text-gray-400">Monitoreo de visitantes reales en vivo y actividad de indexación de Googlebot / Bingbot.</p>
            </div>
            <div class="flex items-center gap-2">
              <span class="px-2.5 py-1 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-[10px] font-black text-emerald-300 flex items-center gap-1.5">
                <i class="fas fa-database text-[9px]"></i> Almacenamiento JSON ligero (Cero carga en MySQL)
              </span>
            </div>
          </div>

          <!-- Tarjetas Detalladas de Tráfico -->
          <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5">
            <div class="p-3 rounded-xl bg-black/60 border border-white/10">
              <span class="text-[9px] text-gray-400 uppercase font-bold block">Personas Únicas Hoy</span>
              <span class="text-lg font-black font-mono text-blue-400"><?= (int)($statsVisitas['hoy']['unicas'] ?? 0) ?></span>
              <span class="text-[10px] text-gray-500 block"><?= (int)($statsVisitas['hoy']['paginas'] ?? 0) ?> páginas vistas</span>
            </div>

            <div class="p-3 rounded-xl bg-black/60 border border-white/10">
              <span class="text-[9px] text-gray-400 uppercase font-bold block">Visitas Ayer</span>
              <span class="text-lg font-black font-mono text-gray-300"><?= (int)($statsVisitas['ayer']['unicas'] ?? 0) ?></span>
              <span class="text-[10px] text-gray-500 block"><?= (int)($statsVisitas['ayer']['paginas'] ?? 0) ?> páginas vistas</span>
            </div>

            <div class="p-3 rounded-xl bg-black/60 border border-white/10">
              <span class="text-[9px] text-gray-400 uppercase font-bold block"><i class="fas fa-mobile-screen mr-1 text-purple-400"></i> Dispositivos Móviles</span>
              <span class="text-lg font-black font-mono text-purple-300"><?= (int)($statsVisitas['hoy']['movil'] ?? 0) ?></span>
              <span class="text-[10px] text-gray-500 block">Celulares / Tablets</span>
            </div>

            <div class="p-3 rounded-xl bg-black/60 border border-white/10">
              <span class="text-[9px] text-gray-400 uppercase font-bold block"><i class="fas fa-laptop mr-1 text-emerald-400"></i> Escritorio / PC</span>
              <span class="text-lg font-black font-mono text-emerald-300"><?= (int)($statsVisitas['hoy']['escritorio'] ?? 0) ?></span>
              <span class="text-[10px] text-gray-500 block">Navegadores PC</span>
            </div>

            <div class="p-3 rounded-xl bg-black/60 border border-white/10 col-span-2 sm:col-span-1">
              <span class="text-[9px] text-gray-400 uppercase font-bold block"><i class="fab fa-google mr-1 text-amber-400"></i> Rastreo SEO (Bots)</span>
              <span class="text-lg font-black font-mono text-amber-300"><?= (int)($statsVisitas['hoy']['bots_seo'] ?? 0) ?></span>
              <span class="text-[10px] text-gray-500 block">Googlebot & Bingbot</span>
            </div>
          </div>

          <!-- Historial de los últimos 7 días -->
          <?php if (!empty($statsVisitas['ultimos_7_dias'])): ?>
            <div class="space-y-1.5 pt-2">
              <span class="text-[10px] font-black uppercase text-gray-400 tracking-wider block">
                Historial de Actividad (Últimos 7 Días)
              </span>
              <div class="overflow-x-auto rounded-xl border border-white/10 bg-black/60">
                <table class="w-full text-left text-xs font-mono">
                  <thead class="bg-black/80 text-gray-400 uppercase text-[9px] border-b border-white/10">
                    <tr>
                      <th class="p-2.5">Fecha</th>
                      <th class="p-2.5">Visitas Únicas</th>
                      <th class="p-2.5">Páginas Vistas</th>
                      <th class="p-2.5">Móvil</th>
                      <th class="p-2.5">Escritorio</th>
                      <th class="p-2.5">Bots Google / Bing</th>
                      <th class="p-2.5">Amenazas Bloqueadas</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-white/5 text-[11px]">
                    <?php foreach ($statsVisitas['ultimos_7_dias'] as $dia): ?>
                      <tr class="hover:bg-white/5 transition-colors">
                        <td class="p-2.5 text-gray-300 font-bold"><?= htmlspecialchars($dia['fecha']) ?><?= $dia['fecha'] === date('Y-m-d') ? ' <span class="text-[9px] text-emerald-400 font-sans font-black">(Hoy)</span>' : '' ?></td>
                        <td class="p-2.5 text-blue-400 font-black"><?= (int)($dia['unicas'] ?? 0) ?></td>
                        <td class="p-2.5 text-white"><?= (int)($dia['paginas'] ?? 0) ?></td>
                        <td class="p-2.5 text-purple-300"><?= (int)($dia['movil'] ?? 0) ?></td>
                        <td class="p-2.5 text-emerald-300"><?= (int)($dia['escritorio'] ?? 0) ?></td>
                        <td class="p-2.5 text-amber-300 font-bold"><?= (int)($dia['bots_seo'] ?? 0) ?></td>
                        <td class="p-2.5 <?= (int)($dia['bloqueos'] ?? 0) > 0 ? 'text-red-400 font-black' : 'text-gray-600' ?>"><?= (int)($dia['bloqueos'] ?? 0) ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          <?php endif; ?>
        </section>

        <!-- SECCIÓN 1: GESTIÓN DE BLOQUEOS (DISPOSITIVOS Y REDES IP) Y CORTAFUEGOS WAF -->
        <section class="glass-card rounded-3xl p-5 sm:p-7 space-y-5 border-purple-500/40">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-white/10">
            <div>
              <h2 class="text-base font-black uppercase text-white flex items-center gap-2">
                <i class="fas fa-shield-halved text-purple-400"></i> Cortafuegos WAF & Control de Bloqueos
              </h2>
              <p class="text-[11px] text-gray-400">Gestiona dispositivos atacantes aislados o bloqueos perimetrales de red.</p>
            </div>
            <?php if (!empty($ipsBloqueadas)): ?>
              <form method="POST" onsubmit="return confirm('¿Levantar TODOS los bloqueos activos (dispositivos y redes IP)?');">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="accion" value="desbloquear_ip">
                <input type="hidden" name="target_id" value="TODOS">
                <button type="submit" class="bg-red-600/20 hover:bg-red-600 border border-red-500/40 text-red-400 hover:text-white px-3.5 py-2 rounded-xl text-xs font-black transition-all flex items-center gap-1.5 shadow-sm">
                  <i class="fas fa-lock-open"></i> Desbloquear Todos
                </button>
              </form>
            <?php endif; ?>
          </div>

          <!-- Formulario de Bloqueo Manual -->
          <div class="p-4 rounded-2xl bg-black/60 border border-purple-500/20 space-y-3">
            <span class="text-[10px] font-black uppercase text-purple-400 tracking-wider flex items-center gap-1.5">
              <i class="fas fa-ban"></i> Bloquear una Dirección IP Manualmente (Toda la red)
            </span>
            <form method="POST" class="grid grid-cols-1 sm:grid-cols-4 gap-2.5">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
              <input type="hidden" name="accion" value="bloquear_ip">

              <div class="sm:col-span-2">
                <input type="text" name="ip" required placeholder="Ej: 198.51.100.45" class="sop-input text-xs font-mono" />
              </div>
              <div>
                <select name="horas" class="sop-input text-xs">
                  <option value="1">Bloquear 1 Hora</option>
                  <option value="24" selected>Bloquear 24 Horas</option>
                  <option value="168">Bloquear 7 Días</option>
                  <option value="720">Bloquear 30 Días</option>
                </select>
              </div>
              <div>
                <button type="submit" class="w-full h-full min-h-[38px] bg-gradient-to-r from-red-600 to-rose-700 hover:from-red-500 hover:to-rose-600 text-white font-black px-4 py-2 rounded-xl text-xs uppercase flex items-center justify-center gap-1.5 transition-all shadow-md">
                  <i class="fas fa-shield-xmark"></i> Bloquear IP
                </button>
              </div>
            </form>
          </div>

          <!-- Lista de Bloqueos Activos -->
          <div class="space-y-2">
            <div class="flex items-center justify-between text-xs font-bold text-gray-400 px-1">
              <span>Bloqueos Activos (<span class="text-white font-mono"><?= count($ipsBloqueadas) ?></span>)</span>
              <span class="text-[10px] text-nico-green flex items-center gap-1"><i class="fas fa-circle-check"></i> Cloudflare y localhost protegidos</span>
            </div>

            <?php if (empty($ipsBloqueadas)): ?>
              <div class="p-4 rounded-xl bg-black/40 border border-white/5 text-center text-xs text-gray-500 italic">
                <i class="fas fa-shield-check text-nico-green text-base mb-1 block"></i>
                No hay dispositivos ni direcciones IP bloqueadas actualmente. El cortafuegos está operando con total normalidad.
              </div>
            <?php else: ?>
              <?php foreach ($ipsBloqueadas as $item): 
                $esDispositivo = ($item['tipo_bloqueo'] ?? '') === 'dispositivo';
                $targetId = $item['id'] ?? $item['ip'] ?? '';
                $dispLabel = $item['dispositivo_label'] ?? ($esDispositivo ? 'Dispositivo Específico' : 'Red / IP Completa');
              ?>
                <div class="p-3.5 rounded-xl bg-black/70 border border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-purple-500/40 transition-colors">
                  <div class="space-y-1.5">
                    <div class="flex flex-wrap items-center gap-2">
                      <?php if ($esDispositivo): ?>
                        <span class="text-[10px] font-black uppercase text-indigo-300 bg-indigo-950/60 px-2.5 py-0.5 rounded-md border border-indigo-700/50 flex items-center gap-1.5">
                          <i class="fas fa-laptop"></i> <?= htmlspecialchars($dispLabel) ?>
                        </span>
                      <?php else: ?>
                        <span class="text-[10px] font-black uppercase text-rose-300 bg-rose-950/60 px-2.5 py-0.5 rounded-md border border-rose-700/50 flex items-center gap-1.5">
                          <i class="fas fa-network-wired"></i> Red / IP Completa
                        </span>
                      <?php endif; ?>
                      <span class="font-mono font-black text-xs text-red-400 bg-red-950/40 px-2 py-0.5 rounded border border-red-800/40"><?= htmlspecialchars($item['ip'] ?? '') ?></span>
                      <span class="text-[10px] font-bold text-purple-300 uppercase px-2 py-0.5 rounded bg-purple-950/40 border border-purple-800/40"><?= htmlspecialchars($item['tipo'] ?? 'Amenaza detectada') ?></span>
                    </div>
                    <p class="text-[11px] text-gray-300 font-medium"><?= htmlspecialchars($item['motivo'] ?? '') ?></p>
                    <div class="text-[10px] text-gray-400 flex flex-wrap items-center gap-3">
                      <span><i class="far fa-clock mr-1 text-gray-500"></i> Bloqueado: <?= htmlspecialchars($item['fecha'] ?? '') ?></span>
                      <span class="text-amber-400 font-bold"><i class="fas fa-hourglass-half mr-1"></i> Expira en: <?= htmlspecialchars((string)($item['minutos_restantes'] ?? '0')) ?> min</span>
                      <?php if (!empty($item['reincidencias']) && (int)$item['reincidencias'] > 1): ?>
                        <span class="text-rose-400 font-bold"><i class="fas fa-triangle-exclamation mr-1"></i> Reincidencias: <?= (int)$item['reincidencias'] ?></span>
                      <?php endif; ?>
                    </div>
                  </div>
                  <form method="POST" onsubmit="return confirm('¿Levantar bloqueo a <?= htmlspecialchars($dispLabel . ' [' . ($item['ip'] ?? '') . ']') ?>?');" class="shrink-0">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="accion" value="desbloquear_ip">
                    <input type="hidden" name="target_id" value="<?= htmlspecialchars($targetId) ?>">
                    <input type="hidden" name="ip" value="<?= htmlspecialchars($item['ip'] ?? '') ?>">
                    <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-emerald-500/20 hover:bg-emerald-500 border border-emerald-500/40 text-emerald-300 hover:text-black font-black text-xs transition-all flex items-center gap-1.5 whitespace-nowrap shadow-sm">
                      <i class="fas fa-lock-open"></i> Desbloquear
                    </button>
                  </form>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </section>

        <!-- SECCIÓN 2: REGISTRO DE EVENTOS DEL HONEYPOT (LIVE AUDIT TRAIL) -->
        <section class="glass-card rounded-3xl p-5 sm:p-7 space-y-4 border-amber-400/30">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-white/10">
            <div>
              <h2 class="text-base font-black uppercase text-white flex items-center gap-2">
                <i class="fas fa-bug text-amber-400"></i> Registro de Eventos del Honeypot (Rutas Trampa)
              </h2>
              <p class="text-[11px] text-gray-400">Atacantes o bots que intentaron tocar rutas señuelo (/backup.sql, /wp-config.php.bak, /.env.bak, etc.).</p>
            </div>
            <?php if (!empty($eventosHoneypot)): ?>
              <form method="POST" onsubmit="return confirm('¿Vaciar el historial de eventos del honeypot?');">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="accion" value="limpiar_logs">
                <button type="submit" class="bg-white/10 hover:bg-amber-500 text-white hover:text-black font-black text-xs px-3 py-1.5 rounded-xl border border-white/15 transition-all flex items-center gap-1.5">
                  <i class="fas fa-eraser"></i> Vaciar Registro
                </button>
              </form>
            <?php endif; ?>
          </div>

          <?php if (empty($eventosHoneypot)): ?>
            <div class="p-4 rounded-xl bg-black/40 border border-white/5 text-center text-xs text-gray-500 italic">
              <i class="fas fa-shield-heart text-nico-green text-base mb-1 block"></i>
              No se han registrado intentos de intrusión en las rutas trampa. El sistema está limpio.
            </div>
          <?php else: ?>
            <div class="overflow-x-auto rounded-xl border border-white/10 bg-black/60">
              <table class="w-full text-left text-xs font-mono">
                <thead class="bg-black/80 text-gray-400 uppercase text-[10px] border-b border-white/10">
                  <tr>
                    <th class="p-3">Fecha y Hora</th>
                    <th class="p-3">IP Atacante</th>
                    <th class="p-3">Ruta Trampa (URI)</th>
                    <th class="p-3">User-Agent</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                  <?php foreach ($eventosHoneypot as $ev): ?>
                    <tr class="hover:bg-white/5 transition-colors">
                      <td class="p-3 text-gray-400 whitespace-nowrap"><?= htmlspecialchars($ev['fecha'] ?? '') ?></td>
                      <td class="p-3 text-red-400 font-black whitespace-nowrap"><?= htmlspecialchars($ev['ip'] ?? '') ?></td>
                      <td class="p-3 text-amber-300 font-bold"><?= htmlspecialchars($ev['uri'] ?? '') ?></td>
                      <td class="p-3 text-gray-500 truncate max-w-xs" title="<?= htmlspecialchars($ev['ua'] ?? '') ?>"><?= htmlspecialchars($ev['ua'] ?? 'Desconocido') ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </section>

        <!-- SECCIÓN 3: ROTACIÓN, COMPRESIÓN GZIP & PURGA DE LOGS (RETENCIÓN 7 DÍAS) -->
        <section class="glass-card rounded-3xl p-5 sm:p-7 space-y-5 border-emerald-500/40">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-white/10">
            <div>
              <h2 class="text-base font-black uppercase text-white flex items-center gap-2">
                <i class="fas fa-file-zipper text-emerald-400"></i> Rotación, Compresión GZIP & Purga de Logs
              </h2>
              <p class="text-[11px] text-gray-400">Archiva logs de más de 7 días comprimidos en el disco con 95% de ahorro. Cero saturación en MySQL.</p>
            </div>
            <div class="flex items-center gap-2">
              <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="accion" value="comprimir_logs">
                <input type="hidden" name="dias_corte" value="7">
                <button type="submit" class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black px-4 py-2 rounded-xl text-xs uppercase flex items-center gap-1.5 shadow-md transition-all">
                  <i class="fas fa-compress"></i> Comprimir Logs (> 7 días)
                </button>
              </form>
            </div>
          </div>

          <!-- Estado de Logs Activos en Disco -->
          <div class="p-4 rounded-2xl bg-black/60 border border-emerald-500/20 space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
              <span class="text-[10px] font-black uppercase text-emerald-400 tracking-wider flex items-center gap-1.5">
                <i class="fas fa-microchip"></i> Archivos de Log Activos en Disco (Total: <?= htmlspecialchars($pesoLogsActivos['total_formato'] ?? '0 KB') ?>)
              </span>
              <span class="text-[10px] text-emerald-300 font-bold flex items-center gap-1">
                <i class="fas fa-shield-halved text-[9px]"></i> Almacenamiento fuera de la BD
              </span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-1">
              <?php foreach (($pesoLogsActivos['archivos'] ?? []) as $nom => $peso): ?>
                <div class="p-3 rounded-xl bg-black/40 border border-white/10 flex items-center justify-between">
                  <span class="text-[11px] text-gray-300 font-medium"><?= htmlspecialchars($nom) ?></span>
                  <span class="text-xs font-mono font-black text-emerald-400"><?= htmlspecialchars($peso) ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Historial de Archivos Comprimidos (.log.gz) -->
          <div class="space-y-3">
            <div class="flex items-center justify-between flex-wrap gap-2">
              <span class="text-[11px] font-black uppercase text-gray-300 flex items-center gap-1.5">
                <i class="fas fa-box-archive text-purple-400"></i> Archivos Comprimidos Históricos (.log.gz)
              </span>
              <?php if (!empty($archivosComprimidos)): ?>
                <form method="POST" onsubmit="return confirm('¿Eliminar todos los archivos comprimidos históricos (.log.gz) del disco? Esta acción no se puede deshacer.');">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                  <input type="hidden" name="accion" value="purgar_archivos_gz">
                  <button type="submit" class="bg-red-600/20 hover:bg-red-600 border border-red-500/40 text-red-300 hover:text-white px-3 py-1.5 rounded-xl text-xs font-black transition-all flex items-center gap-1.5">
                    <i class="fas fa-trash-can"></i> Purgar Archivos Comprimidos
                  </button>
                </form>
              <?php endif; ?>
            </div>

            <?php if (empty($archivosComprimidos)): ?>
              <div class="p-4 rounded-xl bg-black/40 border border-white/5 text-center text-xs text-gray-500 italic">
                <i class="fas fa-folder-open text-gray-600 text-base mb-1 block"></i>
                No hay archivos comprimidos en el histórico. Usa el botón superior para archivar registros cuando superen los 7 días.
              </div>
            <?php else: ?>
              <div class="overflow-x-auto rounded-xl border border-white/10 bg-black/60">
                <table class="w-full text-left text-xs font-mono">
                  <thead class="bg-black/80 text-gray-400 uppercase text-[9px] border-b border-white/10">
                    <tr>
                      <th class="p-3">Nombre del Archivo</th>
                      <th class="p-3">Tamaño Comprimido</th>
                      <th class="p-3">Fecha de Creación</th>
                      <th class="p-3 text-right">Acción</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-white/5">
                    <?php foreach ($archivosComprimidos as $arc): ?>
                      <tr class="hover:bg-white/5 transition-colors">
                        <td class="p-3 text-purple-300 font-bold flex items-center gap-1.5">
                          <i class="fas fa-file-zipper text-purple-400"></i> <?= htmlspecialchars($arc['nombre']) ?>
                        </td>
                        <td class="p-3 text-emerald-400 font-black"><?= htmlspecialchars($arc['peso_formato']) ?></td>
                        <td class="p-3 text-gray-400"><?= htmlspecialchars($arc['fecha']) ?></td>
                        <td class="p-3 text-right">
                          <a href="soporte.php?descargar_gz=<?= urlencode($arc['nombre']) ?>" class="inline-flex items-center gap-1 bg-purple-600/20 hover:bg-purple-600 text-purple-300 hover:text-white border border-purple-500/40 px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all">
                            <i class="fas fa-download"></i> Descargar
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>

          <!-- Nota Informativa de Seguridad & BD -->
          <div class="p-3 rounded-xl bg-emerald-950/30 border border-emerald-500/20 text-[11px] text-emerald-200 flex items-center gap-2">
            <i class="fas fa-shield-heart text-emerald-400 text-sm"></i>
            <span><strong>Protección total de Base de Datos:</strong> Los registros de seguridad se comprimen al 95% en disco local con GZIP. La base de datos MySQL <strong>no almacena logs</strong>, manteniéndose liviana, rápida y libre de saturación.</span>
          </div>
        </section>

        <!-- SECCIÓN 4: GESTIÓN DE CREDENCIALES DE LA CUENTA DE SOPORTE -->
        <section class="glass-card rounded-3xl p-5 sm:p-7 space-y-4 border-nico-orange/30">
          <div class="pb-3 border-b border-white/10">
            <h2 class="text-base font-black uppercase text-white flex items-center gap-2">
              <i class="fas fa-key text-nico-orange"></i> Credenciales de la Cuenta de Soporte Técnico
            </h2>
            <p class="text-[11px] text-gray-400">Cambia el nombre de usuario de soporte o la contraseña directamente en MySQL con cifrado Bcrypt.</p>
            <div class="mt-2 p-2.5 rounded-xl bg-purple-950/40 border border-purple-500/30 text-[11px] text-purple-200 flex items-center justify-between flex-wrap gap-2">
              <span><i class="fas fa-info-circle text-purple-400 mr-1.5"></i> <strong>Política:</strong> El <em>usuario</em> se rota cada <strong>7 días</strong>. La <em>contraseña</em> puede actualizarse en <strong>cualquier momento</strong>.</span>
              <?php if (!$cooldownSoporte['puede']): ?>
                <span class="px-2 py-0.5 rounded bg-amber-500/20 border border-amber-500/40 text-amber-300 font-bold text-[10px]">
                  <i class="far fa-clock mr-1"></i> Usuario bloqueado por cooldown: <?= (int)$cooldownSoporte['diasRestantes'] ?> día(s) restante(s)
                </span>
              <?php else: ?>
                <span class="px-2 py-0.5 rounded bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 font-bold text-[10px]">
                  <i class="fas fa-check mr-1"></i> Cambio de usuario disponible
                </span>
              <?php endif; ?>
            </div>
          </div>

          <form method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="accion" value="cambiar_credenciales">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="text-[10px] font-black uppercase text-gray-300 block mb-1">
                  Nuevo Nombre de Usuario <span class="text-gray-500 font-normal">(Cooldown 7 días)</span>
                </label>
                <input type="text" name="usuario_nuevo" autocomplete="username" placeholder="soporte.tecnico.ans" value="<?= htmlspecialchars($_SESSION['ns_soporte_user'] ?? '') ?>" class="sop-input font-mono text-xs" />
              </div>

              <div>
                <label class="text-[10px] font-black uppercase text-amber-400 block mb-1">Contraseña Actual * (Requerida)</label>
                <input type="password" name="pass_actual" autocomplete="current-password" required placeholder="Tu contraseña actual" class="sop-input font-mono text-xs" />
              </div>

              <div>
                <label class="text-[10px] font-black uppercase text-nico-green block mb-1">Nueva Contraseña Segura (Mín. 12 car.)</label>
                <input type="password" name="pass_nuevo" autocomplete="new-password" minlength="12" placeholder="Mínimo 12 caracteres alfanuméricos" class="sop-input font-mono text-xs" />
              </div>

              <div>
                <label class="text-[10px] font-black uppercase text-nico-green block mb-1">Confirmar Nueva Contraseña</label>
                <input type="password" name="pass_confirm" autocomplete="new-password" minlength="12" placeholder="Repite la nueva contraseña" class="sop-input font-mono text-xs" />
              </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
              <p class="text-[10px] text-gray-500 italic">
                <i class="fas fa-shield-alt text-nico-orange mr-1"></i> Requiere contraseña de al menos 12 caracteres combinando letras y números.
              </p>
              <button type="submit" class="w-full sm:w-auto bg-gradient-to-r from-nico-orange to-red-600 hover:from-nico-orangeDark hover:to-red-700 text-white font-black px-6 py-2.5 rounded-xl text-xs uppercase flex items-center justify-center gap-2 shadow-lg transition-all">
                <i class="fas fa-save"></i> Guardar Credenciales de Soporte
              </button>
            </div>
          </form>
        </section>

      </div>

    <?php endif; ?>

    <!-- PIE DE PÁGINA -->
    <footer class="pt-6 border-t border-white/5 text-center text-xs text-gray-600">
      <p>Academia Nico Sport — Infraestructura Segura & Cortafuegos WAF Nativo. Ley 81 de 2019 de Panamá.</p>
    </footer>

  </div>

</body>
</html>
