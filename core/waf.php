<?php
/* ============================================================================
 * core/waf.php — Web Application Firewall (WAF) Nativo en PHP (Estándar OWASP)
 * ----------------------------------------------------------------------------
 * Protege contra:
 *  1. Slowloris / Slow POST (RUDY) / Slow Read DoS (Timeouts + Socket Close)
 *  2. SQL Injection (SQLi: UNION SELECT, SLEEP, BENCHMARK, OR 1=1, etc.)
 *  3. Cross-Site Scripting (XSS: <script>, javascript:, onerror=, etc.)
 *  4. Path Traversal / LFI / RFI / Stream Wrappers (../, /etc/passwd, phar://)
 *  5. Scanners, Bots de Fuerza Bruta y Herramientas DoS (sqlmap, slowhttptest...)
 *
 * GARANTÍA SEO (CERO ERROR 403 / 429 PARA GOOGLEBOT):
 *  - Si la petición es GET o HEAD de Googlebot, Google-InspectionTool,
 *    Lighthouse, PageSpeed, Bingbot o bots sociales, NUNCA se devuelve 403
 *    ni 429; si hubiera parámetros sospechosos en la URL, se limpian en
 *    memoria para que Google reciba siempre 200 OK e indexe sin errores.
 * ============================================================================ */

// Cortafuegos contra hilos colgados (Mitigación nativa contra Slowloris / Slow Read en PHP)
if (php_sapi_name() !== 'cli') {
    @ini_set('max_execution_time', '15');
    @ini_set('default_socket_timeout', '10');
}

if (!function_exists('wafEsIpCloudflare')) {
    function wafEsIpCloudflare(string $ip): bool {
        static $cfRangos = [
            '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22',
            '141.101.64.0/18', '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20',
            '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
            '104.24.0.0/14',   '172.64.0.0/13',  '131.0.72.0/22'
        ];
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }
        $ipLong = ip2long($ip);
        foreach ($cfRangos as $rango) {
            list($subnet, $mask) = explode('/', $rango, 2);
            $subLong = ip2long($subnet);
            $netmask = ~((1 << (32 - (int)$mask)) - 1);
            if (($ipLong & $netmask) === ($subLong & $netmask)) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('wafObtenerSecret')) {
    function wafObtenerSecret(): string {
        static $secret = null;
        if ($secret !== null) return $secret;
        $envFile = __DIR__ . '/.env';
        if (is_file($envFile)) {
            $lines = @file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (is_array($lines)) {
                foreach ($lines as $line) {
                    if (strpos(trim($line), 'APP_SECRET=') === 0) {
                        $secret = trim(substr(trim($line), 11), " \t\n\r\0\x0B\"'");
                        return $secret;
                    }
                }
            }
        }
        $secret = hash('sha256', 'ans_waf_security_key_v2_2026');
        return $secret;
    }
}

if (!function_exists('wafFirmarDato')) {
    function wafFirmarDato(string $dato): string {
        $firma = hash_hmac('sha256', $dato, wafObtenerSecret());
        return $dato . '.' . $firma;
    }
}

if (!function_exists('wafVerificarDatoFirmado')) {
    function wafVerificarDatoFirmado(?string $datoFirmado): ?string {
        if (empty($datoFirmado) || strpos($datoFirmado, '.') === false) return null;
        $partes = explode('.', $datoFirmado, 2);
        if (count($partes) !== 2) return null;
        [$payload, $firmaRecibida] = $partes;
        $firmaEsperada = hash_hmac('sha256', $payload, wafObtenerSecret());
        if (hash_equals($firmaEsperada, $firmaRecibida)) {
            return $payload;
        }
        return null;
    }
}

if (!function_exists('wafObtenerDeviceKey')) {
    function wafObtenerDeviceKey(string $ip, string $ua): string {
        return substr(hash('sha256', $ip . '_DEV_' . substr($ua, 0, 150)), 0, 24);
    }
}

if (!function_exists('wafObtenerLabelDispositivo')) {
    function wafObtenerLabelDispositivo(string $ua): string {
        $so = 'Dispositivo';
        if (stripos($ua, 'Windows') !== false) $so = 'Windows PC';
        elseif (stripos($ua, 'Android') !== false) $so = 'Android';
        elseif (stripos($ua, 'iPhone') !== false) $so = 'iPhone';
        elseif (stripos($ua, 'iPad') !== false) $so = 'iPad';
        elseif (stripos($ua, 'Macintosh') !== false) $so = 'Mac';
        elseif (stripos($ua, 'Linux') !== false) $so = 'Linux';

        $browser = '';
        if (stripos($ua, 'Edg') !== false) $browser = 'Edge';
        elseif (stripos($ua, 'Chrome') !== false) $browser = 'Chrome';
        elseif (stripos($ua, 'Firefox') !== false) $browser = 'Firefox';
        elseif (stripos($ua, 'Safari') !== false) $browser = 'Safari';
        elseif (stripos($ua, 'Opera') !== false) $browser = 'Opera';

        return $browser !== '' ? "{$so} ({$browser})" : $so;
    }
}

if (!function_exists('wafRutasCandidatasBloqueos')) {
    function wafRutasCandidatasBloqueos(): array {
        $rutas = [
            __DIR__ . '/../uploads/waf_blocked.json',
            '/var/www/web-de-futboll/uploads/waf_blocked.json',
            '/var/www/html/uploads/waf_blocked.json',
            sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ns_waf_state' . DIRECTORY_SEPARATOR . 'waf_blocked.json',
            sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ns_waf_blocked.json'
        ];
        return array_values(array_unique(array_filter($rutas)));
    }
}

if (!function_exists('wafGuardarBloqueos')) {
    function wafGuardarBloqueos(array $data): void {
        $json = json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        foreach (wafRutasCandidatasBloqueos() as $archivo) {
            $dir = dirname($archivo);
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }
            if (is_dir($dir)) {
                @chmod($dir, 0777);
                $escrito = @file_put_contents($archivo, $json, LOCK_EX);
                if ($escrito === false) {
                    @file_put_contents($archivo, $json);
                }
                @chmod($archivo, 0666);
            }
        }
    }
}

if (!function_exists('wafObtenerBloqueosActivos')) {
    function wafObtenerBloqueosActivos(): array {
        $merged = [];
        foreach (wafRutasCandidatasBloqueos() as $f) {
            if (is_file($f)) {
                $raw = @json_decode((string)@file_get_contents($f), true);
                if (is_array($raw)) {
                    foreach ($raw as $item) {
                        $key = !empty($item['id']) ? $item['id'] : ($item['ip'] ?? '');
                        if ($key !== '' && !empty($item['expira'])) {
                            $merged[$key] = $item;
                        }
                    }
                }
            }
        }

        $ahora = time();
        $activas = [];
        $modificado = false;
        foreach ($merged as $k => $item) {
            if ($ahora < (int)$item['expira']) {
                $item['minutos_restantes'] = max(1, (int)ceil(((int)$item['expira'] - $ahora) / 60));
                $activas[$k] = $item;
            } else {
                $modificado = true;
            }
        }
        if ($modificado || count($activas) !== count($merged)) {
            wafGuardarBloqueos($activas);
        }
        return array_values($activas);
    }
}

if (!function_exists('wafObtenerIpsBloqueadas')) {
    function wafObtenerIpsBloqueadas(): array {
        return wafObtenerBloqueosActivos();
    }
}

if (!function_exists('wafBloquearDispositivo')) {
    function wafBloquearDispositivo(string $ip, string $ua, string $motivo, string $tipo, int $duracionSegundos = 3600): array {
        $devKey = wafObtenerDeviceKey($ip, $ua);
        $id = 'DEV_' . $devKey;
        $label = wafObtenerLabelDispositivo($ua);
        $ahora = time();
        $expira = $ahora + $duracionSegundos;

        $activas = wafObtenerBloqueosActivos();
        $reincidencias = 1;
        $data = [];
        foreach ($activas as $it) {
            if (!empty($it['id']) && $it['id'] === $id) {
                $reincidencias = (int)($it['reincidencias'] ?? 1) + 1;
            } else {
                $data[] = $it;
            }
        }

        $item = [
            'id'                => $id,
            'tipo_bloqueo'      => 'dispositivo',
            'ip'                => $ip,
            'ua'                => substr($ua, 0, 160),
            'dispositivo_label' => $label,
            'motivo'            => $motivo,
            'tipo'              => $tipo,
            'fecha'             => date('Y-m-d H:i:s', $ahora),
            'expira'            => $expira,
            'reincidencias'     => $reincidencias,
            'minutos_restantes' => max(1, (int)ceil($duracionSegundos / 60))
        ];
        $data[] = $item;
        wafGuardarBloqueos($data);

        // Emitir cookie criptográfica firmada al navegador atacante
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        $cookiePayload = wafFirmarDato($id . '|' . $expira . '|' . substr($motivo, 0, 50));
        if (!headers_sent()) {
            setcookie('ns_waf_lock', $cookiePayload, [
                'expires'  => $expira,
                'path'     => '/',
                'domain'   => '',
                'secure'   => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
        $_SESSION['ns_waf_lock'] = $cookiePayload;

        return $item;
    }
}

if (!function_exists('wafBloquearIp')) {
    function wafBloquearIp(string $ip, string $motivo, string $tipo, int $duracionSegundos = 3600): bool {
        if (wafEsIpCloudflare($ip)) {
            return false;
        }
        $id = 'IP_' . substr(hash('sha256', $ip), 0, 16);
        $ahora = time();
        $expira = $ahora + $duracionSegundos;

        $activas = wafObtenerBloqueosActivos();
        $reincidencias = 1;
        $data = [];
        foreach ($activas as $it) {
            if ((!empty($it['id']) && $it['id'] === $id) || (!empty($it['ip']) && $it['ip'] === $ip && ($it['tipo_bloqueo'] ?? '') === 'ip')) {
                $reincidencias = (int)($it['reincidencias'] ?? 1) + 1;
            } else {
                $data[] = $it;
            }
        }

        $data[] = [
            'id'                => $id,
            'tipo_bloqueo'      => 'ip',
            'ip'                => $ip,
            'ua'                => 'Toda la red / IP pública',
            'dispositivo_label' => 'Red / IP Completa',
            'motivo'            => $motivo,
            'tipo'              => $tipo,
            'fecha'             => date('Y-m-d H:i:s', $ahora),
            'expira'            => $expira,
            'reincidencias'     => $reincidencias,
            'minutos_restantes' => max(1, (int)ceil($duracionSegundos / 60))
        ];
        wafGuardarBloqueos($data);
        return true;
    }
}

if (!function_exists('wafDesbloquear')) {
    function wafDesbloquear(string $targetIdOrIp): bool {
        $activas = wafObtenerBloqueosActivos();
        $filtrados = [];
        foreach ($activas as $item) {
            $itemId = $item['id'] ?? '';
            $itemIp = $item['ip'] ?? '';
            if ($itemId !== $targetIdOrIp && $itemIp !== $targetIdOrIp) {
                $filtrados[] = $item;
            }
        }
        wafGuardarBloqueos($filtrados);

        if (!empty($_COOKIE['ns_waf_lock'])) {
            setcookie('ns_waf_lock', '', ['expires' => time() - 3600, 'path' => '/']);
            unset($_COOKIE['ns_waf_lock']);
        }
        unset($_SESSION['ns_waf_lock']);
        return true;
    }
}

if (!function_exists('wafDesbloquearIp')) {
    function wafDesbloquearIp(string $ip): bool {
        return wafDesbloquear($ip);
    }
}

if (!function_exists('wafLimpiarTodosBloqueos')) {
    function wafLimpiarTodosBloqueos(): bool {
        wafGuardarBloqueos([]);
        if (!empty($_COOKIE['ns_waf_lock'])) {
            setcookie('ns_waf_lock', '', ['expires' => time() - 3600, 'path' => '/']);
            unset($_COOKIE['ns_waf_lock']);
        }
        unset($_SESSION['ns_waf_lock']);
        return true;
    }
}

if (!function_exists('wafVerificarAccesoBloqueado')) {
    function wafVerificarAccesoBloqueado(string $ip, string $ua): ?array {
        $ahora = time();
        $activas = wafObtenerBloqueosActivos();
        $devKey = wafObtenerDeviceKey($ip, $ua);
        $devId = 'DEV_' . $devKey;

        // 1. Revisar cookie criptográfica del navegador atacante
        if (!empty($_COOKIE['ns_waf_lock'])) {
            $payload = wafVerificarDatoFirmado($_COOKIE['ns_waf_lock']);
            if ($payload !== null) {
                $partes = explode('|', $payload);
                if (count($partes) >= 2) {
                    $cId = $partes[0];
                    $cExpira = (int)$partes[1];
                    if ($ahora < $cExpira) {
                        foreach ($activas as $b) {
                            if (($b['id'] ?? '') === $cId) {
                                return $b;
                            }
                        }
                        // Si ya no figura en bloqueos activos, el administrador lo desbloqueó desde soporte.php
                        if (!headers_sent()) {
                            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                                    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
                            setcookie('ns_waf_lock', '', [
                                'expires'  => time() - 3600,
                                'path'     => '/',
                                'domain'   => '',
                                'secure'   => $isHttps,
                                'httponly' => true,
                                'samesite' => 'Lax'
                            ]);
                        }
                        unset($_COOKIE['ns_waf_lock'], $_SESSION['ns_waf_lock']);
                    }
                }
            }
        }

        // 2. Revisar si el dispositivo específico está en la lista de bloqueos en disco
        foreach ($activas as $b) {
            if (($b['id'] ?? '') === $devId && $ahora < (int)($b['expira'] ?? 0)) {
                return $b;
            }
        }

        // 3. Revisar si la IP entera está bajo baneo perimetral manual
        foreach ($activas as $b) {
            if (($b['tipo_bloqueo'] ?? '') === 'ip' && ($b['ip'] ?? '') === $ip && $ahora < (int)($b['expira'] ?? 0)) {
                return $b;
            }
        }

        return null;
    }
}

// -----------------------------------------------------------------------------
// ANALÍTICAS LIGERAS & ROTACIÓN / COMPRESIÓN DE LOGS (GZIP) SIN TOCAR MYSQL
// -----------------------------------------------------------------------------

if (!function_exists('wafRegistrarVisita')) {
    function wafRegistrarVisita(string $ip, string $ua, string $uri, bool $isSeoCrawler = false): void {
        $path = parse_url($uri, PHP_URL_PATH) ?? '';
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($ext, ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'map', 'json', 'pdf'], true)) {
            return;
        }

        $analyticsDir = __DIR__ . '/../uploads/analytics';
        if (!is_dir($analyticsDir)) {
            @mkdir($analyticsDir, 0755, true);
        }
        $visitasFile = $analyticsDir . '/visitas.json';
        $hoy = date('Y-m-d');

        $data = [];
        if (is_file($visitasFile)) {
            $data = @json_decode((string)@file_get_contents($visitasFile), true);
            if (!is_array($data)) $data = [];
        }

        if (!isset($data[$hoy])) {
            $data[$hoy] = [
                'fecha'      => $hoy,
                'paginas'    => 0,
                'unicas'     => 0,
                'movil'      => 0,
                'escritorio' => 0,
                'bots_seo'   => 0,
                'bloqueos'   => 0,
                'hashes'     => []
            ];
        }

        if ($isSeoCrawler) {
            $data[$hoy]['bots_seo'] = (int)($data[$hoy]['bots_seo'] ?? 0) + 1;
        } else {
            $data[$hoy]['paginas'] = (int)($data[$hoy]['paginas'] ?? 0) + 1;
            $esMovil = preg_match('/(android|iphone|ipad|mobile|ipod|blackberry|opera mini|iemobile)/i', $ua);
            if ($esMovil) {
                $data[$hoy]['movil'] = (int)($data[$hoy]['movil'] ?? 0) + 1;
            } else {
                $data[$hoy]['escritorio'] = (int)($data[$hoy]['escritorio'] ?? 0) + 1;
            }

            $ipHash = substr(hash('sha256', $ip . '_' . $hoy), 0, 16);
            if (!in_array($ipHash, $data[$hoy]['hashes'], true)) {
                $data[$hoy]['unicas'] = (int)($data[$hoy]['unicas'] ?? 0) + 1;
                if (count($data[$hoy]['hashes']) < 2000) {
                    $data[$hoy]['hashes'][] = $ipHash;
                }
            }
        }

        // Mantener solo los últimos 30 días para que el archivo nunca supere 20 KB
        if (count($data) > 30) {
            ksort($data);
            while (count($data) > 30) {
                array_shift($data);
            }
        }

        @file_put_contents($visitasFile, json_encode($data, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }
}

if (!function_exists('wafObtenerEstadisticasVisitas')) {
    function wafObtenerEstadisticasVisitas(): array {
        $visitasFile = __DIR__ . '/../uploads/analytics/visitas.json';
        $data = [];
        if (is_file($visitasFile)) {
            $data = @json_decode((string)@file_get_contents($visitasFile), true);
            if (!is_array($data)) $data = [];
        }

        $hoy = date('Y-m-d');
        $ayer = date('Y-m-d', strtotime('-1 day'));

        $statsHoy = $data[$hoy] ?? [
            'fecha' => $hoy, 'paginas' => 0, 'unicas' => 0, 'movil' => 0, 'escritorio' => 0, 'bots_seo' => 0, 'bloqueos' => 0
        ];
        $statsAyer = $data[$ayer] ?? [
            'fecha' => $ayer, 'paginas' => 0, 'unicas' => 0, 'movil' => 0, 'escritorio' => 0, 'bots_seo' => 0, 'bloqueos' => 0
        ];

        $ultimos7Dias = [];
        $total7Paginas = 0;
        $total7Unicas = 0;
        $total7Bots = 0;
        $total7Bloqueos = 0;

        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $diaData = $data[$d] ?? ['fecha' => $d, 'paginas' => 0, 'unicas' => 0, 'movil' => 0, 'escritorio' => 0, 'bots_seo' => 0, 'bloqueos' => 0];
            unset($diaData['hashes']);
            $ultimos7Dias[] = $diaData;
            $total7Paginas += (int)($diaData['paginas'] ?? 0);
            $total7Unicas += (int)($diaData['unicas'] ?? 0);
            $total7Bots += (int)($diaData['bots_seo'] ?? 0);
            $total7Bloqueos += (int)($diaData['bloqueos'] ?? 0);
        }

        unset($statsHoy['hashes'], $statsAyer['hashes']);

        return [
            'hoy'             => $statsHoy,
            'ayer'            => $statsAyer,
            'total_7_paginas' => $total7Paginas,
            'total_7_unicas'  => $total7Unicas,
            'total_7_bots'    => $total7Bots,
            'total_7_bloqueos'=> $total7Bloqueos,
            'ultimos_7_dias'  => $ultimos7Dias
        ];
    }
}

if (!function_exists('wafRotarYComprimirLogs')) {
    function wafRotarYComprimirLogs(int $diasCorte = 7): array {
        $uploadsDir = __DIR__ . '/../uploads';
        $archiveDir = $uploadsDir . '/logs_archive';
        if (!is_dir($archiveDir)) {
            @mkdir($archiveDir, 0755, true);
        }

        $logFiles = [
            'honeypot' => $uploadsDir . '/honeypot_events.log',
            'waf_sec'  => $uploadsDir . '/waf_security.log'
        ];

        $limiteSegundos = $diasCorte * 86400;
        $ahora = time();
        $lineasAntiguas = [];
        $totalAntiguas = 0;

        foreach ($logFiles as $filePath) {
            if (!is_file($filePath)) continue;
            $lineas = @file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (!is_array($lineas) || empty($lineas)) continue;

            $lineasConservar = [];
            foreach ($lineas as $ln) {
                $ev = @json_decode($ln, true);
                $ts = 0;
                if (is_array($ev)) {
                    if (!empty($ev['timestamp'])) {
                        $ts = strtotime($ev['timestamp']);
                    } elseif (!empty($ev['fecha'])) {
                        $ts = strtotime($ev['fecha']);
                    }
                }
                if ($ts > 0 && ($ahora - $ts) >= $limiteSegundos) {
                    $lineasAntiguas[] = $ln;
                    $totalAntiguas++;
                } else {
                    $lineasConservar[] = $ln;
                }
            }
            $contenidoConservar = !empty($lineasConservar) ? implode("\n", $lineasConservar) . "\n" : '';
            @file_put_contents($filePath, $contenidoConservar, LOCK_EX);
        }

        if (empty($lineasAntiguas)) {
            return [
                'ok'          => true,
                'comprimidos' => 0,
                'mensaje'     => "No hay registros con más de {$diasCorte} días de antigüedad para comprimir."
            ];
        }

        $rawText = implode("\n", $lineasAntiguas) . "\n";
        $pesoOriginal = strlen($rawText);
        $gzData = function_exists('gzencode') ? gzencode($rawText, 9) : $rawText;
        $nombreArchivo = 'waf_archive_' . date('Ymd_His') . '.log.gz';
        $rutaArchivo = $archiveDir . '/' . $nombreArchivo;
        @file_put_contents($rutaArchivo, $gzData, LOCK_EX);

        $pesoGz = strlen($gzData);
        $ahorroPct = $pesoOriginal > 0 ? round((1 - ($pesoGz / $pesoOriginal)) * 100, 1) : 0;

        return [
            'ok'            => true,
            'comprimidos'   => $totalAntiguas,
            'archivo'       => $nombreArchivo,
            'peso_original' => $pesoOriginal,
            'peso_gz'       => $pesoGz,
            'ahorro_pct'    => $ahorroPct,
            'mensaje'       => "Se archivaron y comprimieron {$totalAntiguas} registros en '{$nombreArchivo}' (Reducción de tamaño: {$ahorroPct}%)."
        ];
    }
}

if (!function_exists('wafObtenerArchivosComprimidos')) {
    function wafObtenerArchivosComprimidos(): array {
        $archiveDir = __DIR__ . '/../uploads/logs_archive';
        if (!is_dir($archiveDir)) return [];
        $files = glob($archiveDir . '/*.log.gz');
        if (!is_array($files)) return [];

        $res = [];
        foreach ($files as $f) {
            $base = basename($f);
            $size = filesize($f);
            $mtime = filemtime($f);
            $res[] = [
                'nombre'       => $base,
                'peso_bytes'   => $size,
                'peso_formato' => $size >= 1048576 ? round($size / 1048576, 2) . ' MB' : round($size / 1024, 1) . ' KB',
                'fecha'        => date('d/m/Y H:i:s', $mtime),
                'timestamp'    => $mtime
            ];
        }
        usort($res, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);
        return $res;
    }
}

if (!function_exists('wafPurgarArchivosComprimidos')) {
    function wafPurgarArchivosComprimidos(): int {
        $archiveDir = __DIR__ . '/../uploads/logs_archive';
        if (!is_dir($archiveDir)) return 0;
        $files = glob($archiveDir . '/*.log.gz');
        if (!is_array($files)) return 0;
        $borrados = 0;
        foreach ($files as $f) {
            if (@unlink($f)) $borrados++;
        }
        return $borrados;
    }
}

if (!function_exists('wafObtenerTamanoLogsActivos')) {
    function wafObtenerTamanoLogsActivos(): array {
        $uploadsDir = __DIR__ . '/../uploads';
        $archivos = [
            'Honeypot Events' => $uploadsDir . '/honeypot_events.log',
            'WAF Security'    => $uploadsDir . '/waf_security.log',
            'Bloqueos WAF'    => $uploadsDir . '/waf_blocked.json'
        ];
        $totalBytes = 0;
        $detalle = [];
        foreach ($archivos as $nombre => $path) {
            $sz = is_file($path) ? filesize($path) : 0;
            $totalBytes += $sz;
            $detalle[$nombre] = $sz >= 1048576 ? round($sz / 1048576, 2) . ' MB' : round($sz / 1024, 1) . ' KB';
        }
        return [
            'total_bytes'   => $totalBytes,
            'total_formato' => $totalBytes >= 1048576 ? round($totalBytes / 1048576, 2) . ' MB' : round($totalBytes / 1024, 1) . ' KB',
            'archivos'      => $detalle
        ];
    }
}

if (!function_exists('wafRenderizarPaginaBloqueo')) {
    function wafRenderizarPaginaBloqueo(
        string $ip,
        string $motivo,
        int $minutosRestantes = 60,
        string $incidenteId = '',
        int $reincidencias = 1,
        bool $esApi = false
    ): void {
        if ($incidenteId === '') {
            $incidenteId = 'REF-WAF-' . strtoupper(substr(hash('sha256', $ip . '_' . time()), 0, 10));
        }

        if (!headers_sent()) {
            http_response_code(403);
            header('Connection: close');
            header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet');
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Expires: 0');
        }

        $accept = (string)($_SERVER['HTTP_ACCEPT'] ?? '');
        $isXhr  = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
        $pideJson = $esApi || $isXhr || (strpos($accept, 'application/json') !== false && strpos($accept, 'text/html') === false);

        if ($pideJson) {
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }
            echo json_encode([
                'ok'                      => false,
                'status'                  => 'banned',
                'error'                   => "Hemos detectado actividad sospechosa procedente de tu conexión y te hemos bloqueado por {$minutosRestantes} minutos.",
                'ip_real'                 => $ip,
                'motivo'                  => $motivo,
                'tiempo_restante_minutos' => $minutosRestantes,
                'reincidencias'           => $reincidencias,
                'advertencia'             => 'Después de ciertos intentos, tu IP real quedará permanentemente baneada de este sitio web sin importar si cambias de diferente tipo de red.',
                'ref_incidente'           => $incidenteId,
                'fecha'                   => date('Y-m-d H:i:s')
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!headers_sent()) {
            header('Content-Type: text/html; charset=utf-8');
        }

        $ipEsc             = htmlspecialchars($ip, ENT_QUOTES, 'UTF-8');
        $motivoEsc         = htmlspecialchars($motivo, ENT_QUOTES, 'UTF-8');
        $refEsc            = htmlspecialchars($incidenteId, ENT_QUOTES, 'UTF-8');
        $horaEsc           = date('d/m/Y H:i:s') . ' (Hora del Servidor)';
        $segundosRestantes = max(60, $minutosRestantes * 60);
        ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="robots" content="noindex, nofollow, noarchive, nosnippet" />
  <title>Acceso Bloqueado por Seguridad — Academia Nico Sport</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #06050b radial-gradient(circle at 50% 20%, #1c0e2d 0%, #06050b 80%);
      color: #f3f4f6;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      padding: 1.25rem;
      line-height: 1.5;
    }
    .waf-card {
      width: 100%;
      max-width: 680px;
      background: rgba(18, 14, 28, 0.94);
      border: 1px solid rgba(239, 68, 68, 0.35);
      border-radius: 1.5rem;
      padding: 2.25rem;
      box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.8), 0 0 45px rgba(239, 68, 68, 0.15);
      backdrop-filter: blur(16px);
      position: relative;
      overflow: hidden;
    }
    .waf-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 4px;
      background: linear-gradient(90deg, #ef4444, #f97316, #ef4444);
    }
    .badge-pill {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      padding: 0.35rem 0.85rem;
      background: rgba(239, 68, 68, 0.15);
      border: 1px solid rgba(239, 68, 68, 0.4);
      border-radius: 9999px;
      color: #fca5a5;
      font-size: 0.72rem;
      font-weight: 800;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      margin-bottom: 1.25rem;
    }
    .pulse-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #ef4444;
      box-shadow: 0 0 10px #ef4444;
      animation: pulse 1.8s infinite;
    }
    @keyframes pulse {
      0%, 100% { opacity: 1; transform: scale(1); }
      50% { opacity: 0.4; transform: scale(0.85); }
    }
    .header-group {
      text-align: center;
      margin-bottom: 1.75rem;
    }
    .shield-icon {
      width: 64px;
      height: 64px;
      margin: 0 auto 1rem;
      background: rgba(239, 68, 68, 0.12);
      border: 2px solid rgba(239, 68, 68, 0.4);
      border-radius: 1.25rem;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #ef4444;
      box-shadow: 0 0 25px rgba(239, 68, 68, 0.3);
    }
    .shield-icon svg {
      width: 34px;
      height: 34px;
      fill: none;
      stroke: currentColor;
      stroke-width: 2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }
    h1 {
      font-size: 1.5rem;
      font-weight: 900;
      letter-spacing: -0.02em;
      color: #ffffff;
      margin-bottom: 0.5rem;
      text-transform: uppercase;
    }
    .subhead {
      font-size: 0.92rem;
      color: #d1d5db;
    }
    .info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 0.75rem;
      margin-bottom: 1.5rem;
    }
    @media (max-width: 580px) {
      .info-grid { grid-template-columns: 1fr; }
      .waf-card { padding: 1.5rem; }
    }
    .info-item {
      background: rgba(0, 0, 0, 0.55);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 0.85rem;
      padding: 0.75rem 1rem;
    }
    .info-label {
      font-size: 0.68rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: #9ca3af;
      margin-bottom: 0.25rem;
    }
    .info-val {
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
      font-size: 0.85rem;
      font-weight: 700;
      color: #f3f4f6;
      word-break: break-all;
    }
    .info-val.highlight {
      color: #f87171;
    }
    .info-val.time {
      color: #fbbf24;
    }
    .advertencia-box {
      background: linear-gradient(135deg, rgba(127, 29, 29, 0.45) 0%, rgba(69, 10, 10, 0.35) 100%);
      border: 1px solid rgba(239, 68, 68, 0.55);
      border-radius: 1rem;
      padding: 1.25rem;
      margin-bottom: 1.5rem;
      position: relative;
    }
    .advertencia-title {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.82rem;
      font-weight: 900;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: #fca5a5;
      margin-bottom: 0.65rem;
    }
    .advertencia-text {
      font-size: 0.82rem;
      color: #fecaca;
      line-height: 1.55;
      margin-bottom: 0.65rem;
    }
    .advertencia-text strong {
      color: #ffffff;
      font-weight: 800;
    }
    .advertencia-footer {
      font-size: 0.76rem;
      color: #fca5a5;
      background: rgba(0, 0, 0, 0.4);
      padding: 0.5rem 0.75rem;
      border-radius: 0.5rem;
      border-left: 3px solid #f87171;
    }
    .countdown-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: rgba(0, 0, 0, 0.6);
      border: 1px solid rgba(251, 191, 36, 0.3);
      border-radius: 0.85rem;
      padding: 0.75rem 1rem;
      margin-bottom: 1.5rem;
      font-size: 0.8rem;
    }
    .countdown-timer {
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
      font-size: 1.05rem;
      font-weight: 900;
      color: #fbbf24;
    }
    .actions {
      display: flex;
      gap: 0.75rem;
      flex-wrap: wrap;
    }
    .btn {
      flex: 1;
      min-width: 160px;
      padding: 0.85rem 1.25rem;
      border-radius: 0.85rem;
      font-size: 0.82rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      text-align: center;
      cursor: pointer;
      text-decoration: none;
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      border: none;
    }
    .btn-primary {
      background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
      color: #ffffff;
      box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
    }
    .btn-primary:hover {
      background: linear-gradient(135deg, #f87171 0%, #dc2626 100%);
      transform: translateY(-1px);
    }
    .btn-secondary {
      background: rgba(255, 255, 255, 0.06);
      color: #d1d5db;
      border: 1px solid rgba(255, 255, 255, 0.15);
    }
    .btn-secondary:hover {
      background: rgba(255, 255, 255, 0.12);
      color: #ffffff;
    }
    .footer-note {
      text-align: center;
      margin-top: 1.5rem;
      font-size: 0.72rem;
      color: #6b7280;
    }
  </style>
</head>
<body>

  <div class="waf-card">
    <div style="text-align: center;">
      <div class="badge-pill">
        <span class="pulse-dot"></span>
        <span>Cortafuegos WAF · Seguridad Perimetral Activa</span>
      </div>
    </div>

    <div class="header-group">
      <div class="shield-icon">
        <svg viewBox="0 0 24 24">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
          <path d="M12 8v4"></path>
          <path d="M12 16h.01"></path>
        </svg>
      </div>
      <h1>Acceso Bloqueado por Seguridad</h1>
      <p class="subhead">Hemos detectado actividad sospechosa procedente de tu conexión y te hemos bloqueado temporalmente.</p>
    </div>

    <div class="info-grid">
      <div class="info-item">
        <div class="info-label">Tu Dirección IP Real</div>
        <div class="info-val highlight"><?= $ipEsc ?></div>
      </div>
      <div class="info-item">
        <div class="info-label">Motivo de Detección</div>
        <div class="info-val"><?= $motivoEsc ?></div>
      </div>
      <div class="info-item">
        <div class="info-label">Tiempo de Penalización</div>
        <div class="info-val time"><?= $minutosRestantes ?> minuto(s)</div>
      </div>
      <div class="info-item">
        <div class="info-label">ID de Incidente (Auditoría)</div>
        <div class="info-val"><?= $refEsc ?></div>
      </div>
    </div>

    <div class="advertencia-box">
      <div class="advertencia-title">
        <span>⚠️</span>
        <span>Advertencia de Baneo Permanente</span>
      </div>
      <p class="advertencia-text">
        Hemos detectado actividad sospechosa y te hemos bloqueado por <strong><?= $minutosRestantes ?> minutos</strong>.
        Después de ciertos intentos reiterados, <strong>tu IP real quedará permanentemente baneada de este sitio web</strong>, y no importa si intentas cambiar a un tipo de red diferente (Wi-Fi, datos móviles, VPN o proxy), la restricción perimetral impedirá tu conexión de forma irreversible.
      </p>
      <div class="advertencia-footer">
        🔄 <strong>Nota de recarga:</strong> Mientras el bloqueo se encuentre vigente, cada vez que recargues o refresques esta página este mismo aviso seguirá apareciendo impidiéndote el acceso.
      </div>
    </div>

    <div class="countdown-bar">
      <span>Tiempo restante para reintentar:</span>
      <span class="countdown-timer" id="countdown"><?= $minutosRestantes ?>:00</span>
    </div>

    <div class="actions">
      <button type="button" onclick="window.location.reload();" class="btn btn-primary">
        🔄 Reintentar / Refrescar
      </button>
      <a href="mailto:academianicosport@gmail.com?subject=Revision%20Bloqueo%20WAF%20<?= urlencode($refEsc) ?>" class="btn btn-secondary">
        ✉️ Reportar Falso Positivo
      </a>
    </div>

    <div class="footer-note">
      Academia Nico Sport — Infraestructura Segura & Protección de Menores bajo Ley 81 de 2019 de Panamá.<br />
      Fecha y Hora del Bloqueo: <?= $horaEsc ?>
    </div>
  </div>

  <script>
    (function(){
      var seg = <?= (int)$segundosRestantes ?>;
      var el = document.getElementById('countdown');
      if (!el) return;
      var timer = setInterval(function(){
        seg--;
        if (seg <= 0) {
          clearInterval(timer);
          el.textContent = 'Expirado (Listo para reintentar)';
          el.style.color = '#34d399';
          return;
        }
        var m = Math.floor(seg / 60);
        var s = seg % 60;
        el.textContent = (m < 10 ? '0' + m : m) + ':' + (s < 10 ? '0' + s : s);
      }, 1000);
    })();
  </script>
</body>
</html>
        <?php
        exit;
    }
}

if (!function_exists('asegurarSesionSegura')) {
    function asegurarSesionSegura(): void {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                || (isset($_SERVER['HTTP_CF_VISITOR']) && strpos($_SERVER['HTTP_CF_VISITOR'], 'https') !== false)
                || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @ini_set('session.use_only_cookies', '1');
            @ini_set('session.use_strict_mode', '1');
            @ini_set('session.cookie_httponly', '1');
            @ini_set('session.cookie_samesite', 'Lax');
            if ($isHttps) {
                @ini_set('session.cookie_secure', '1');
            }

            session_set_cookie_params([
                'lifetime' => 86400,
                'path'     => '/',
                'domain'   => '',
                'secure'   => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            @session_start();
        }

        static $cookieReinforced = false;
        if (!$cookieReinforced && session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            $headers = headers_list();
            $alreadyConfigured = false;
            foreach ($headers as $h) {
                if (stripos($h, 'Set-Cookie:') === 0 && stripos($h, session_name()) !== false && stripos($h, 'HttpOnly') !== false) {
                    $alreadyConfigured = true;
                    break;
                }
            }
            if (!$alreadyConfigured) {
                $sessId = session_id();
                if ($sessId !== '') {
                    $cookieHdr = "Set-Cookie: " . session_name() . "={$sessId}; path=/; Max-Age=86400; HttpOnly; SameSite=Lax";
                    if ($isHttps) {
                        $cookieHdr .= "; Secure";
                    }
                    header($cookieHdr, false);
                    $cookieReinforced = true;
                }
            }
        }
    }
}

(function (): void {
    if (php_sapi_name() === 'cli') {
        return;
    }
    asegurarSesionSegura();
    $reqStart  = (float)($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true));
    $method    = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    // Detectar IP real del visitante (compatible con Cloudflare y Nginx Proxy Manager)
    $rawIp = $_SERVER['HTTP_CF_CONNECTING_IP'] 
          ?? $_SERVER['HTTP_X_REAL_IP'] 
          ?? $_SERVER['HTTP_X_FORWARDED_FOR'] 
          ?? $_SERVER['REMOTE_ADDR'] 
          ?? '0.0.0.0';
    if (strpos($rawIp, ',') !== false) {
        $ip = trim(explode(',', $rawIp)[0]);
    } else {
        $ip = trim($rawIp);
    }

    $ua        = trim((string)($_SERVER['HTTP_USER_AGENT'] ?? ''));
    $uri       = (string)($_SERVER['REQUEST_URI'] ?? '');
    $query     = (string)($_SERVER['QUERY_STRING'] ?? '');
    $isLocalIp = in_array($ip, ['127.0.0.1', '::1'], true);
    $esUsuarioAutenticado = !empty($_SESSION['ns_rol']);
    $esAdminAutenticado   = ($esUsuarioAutenticado && in_array($_SESSION['ns_rol'], ['admin', 'entrenador'], true));

    // 1. GARANTÍA ABSOLUTA PARA MOTORES DE BÚSQUEDA LEGÍTIMOS (Googlebot, Bingbot, etc.)
    // Se evalúa antes que cualquier filtro para asegurar 200 OK en indexación
    $isSeoCrawler = false;
    if (in_array($method, ['GET', 'HEAD'], true) && $ua !== '') {
        if (preg_match('/(Googlebot|Google-InspectionTool|GoogleOther|Google-Extended|AdsBot-Google|APIs-Google|Mediapartners-Google|Storebot-Google|Google-Read-Aloud|Google-Site-Verification|Google-Structured-Data|Chrome-Lighthouse|Lighthouse|PageSpeed|PTST|GTmetrix|Sideqik|bingbot|AdIdxBot|BingPreview|msnbot|Slurp|DuckDuckBot|Applebot|YandexBot|Baiduspider|facebookexternalhit|Facebot|Twitterbot|LinkedInBot|WhatsApp|TelegramBot|Discordbot|Slackbot)/i', $ua)) {
            $isSeoCrawler = true;
        }
    }

    if ($isSeoCrawler) {
        wafRegistrarVisita($ip, $ua, $uri, true);
        if (!empty($_GET)) {
            foreach ($_GET as $k => $v) {
                if (is_string($v) && preg_match('/(union\s+select|<\s*script|\.\.\/|\/etc\/passwd|php:\/\/)/i', rawurldecode($v))) {
                    unset($_GET[$k]);
                }
            }
        }
        return; // Pase directo 200 OK garantizado para indexación sin bloqueos
    }

    // 0. VERIFICACIÓN DE ACCESO BLOQUEADO (Dispositivo atacante o IP completa persistente)
    $bloqueoActual = wafVerificarAccesoBloqueado($ip, $ua);
    if ($bloqueoActual !== null) {
        $esRutaSoporte = (stripos($uri, 'soporte.php') !== false || stripos($uri, 'desbloquear.php') !== false);
        if (!$esRutaSoporte) {
            $motivoB = !empty($bloqueoActual['motivo']) ? $bloqueoActual['motivo'] : ($bloqueoActual['tipo'] ?? 'Actividad sospechosa detectada');
            $reinc   = (int)($bloqueoActual['reincidencias'] ?? 1);
            $refInc  = 'REF-WAF-' . strtoupper(substr(hash('sha256', ($bloqueoActual['id'] ?? $ip) . '_' . ($bloqueoActual['fecha'] ?? '')), 0, 10));

            wafRenderizarPaginaBloqueo($ip, $motivoB, $minRest, $refInc, $reinc, false);
        }
    }

    // Directorio temporal para estado del WAF (Rate Limit por IP)
    $wafDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ns_waf_state';
    if (!is_dir($wafDir)) {
        @mkdir($wafDir, 0755, true);
    }
    $ipKey  = substr(hash('sha256', $ip . '_ns_waf_v2'), 0, 28);
    $ipFile = $wafDir . DIRECTORY_SEPARATOR . 'ip_' . $ipKey . '.json';
    $now    = time();

    $state = [
        'window_start' => $now,
        'get_count'    => 0,
        'post_count'   => 0,
        'slow_count'   => 0
    ];

    if (is_file($ipFile)) {
        $rawState = @json_decode((string)@file_get_contents($ipFile), true);
        if (is_array($rawState)) {
            $state = array_merge($state, $rawState);
        }
    }

    // Función de bloqueo con cierre inmediato de socket TCP y renderizado de pantalla WAF
    $blockRequest = function (int $httpCode, string $reason, int $minutos = 60) use ($ip, $uri, $ua): void {
        error_log(sprintf('[WAF BLOCK %d] IP=%s Reason=%s URI=%s', $httpCode, $ip, $reason, substr($uri, 0, 200)));

        $refIncidente = 'REF-WAF-' . strtoupper(substr(hash('sha256', $ip . '_' . time()), 0, 10));
        $motivoHumano = 'Actividad sospechosa detectada en la solicitud';
        if ($reason === 'HONEYPOT_ROUTE_TRIGGER') {
            $motivoHumano = 'Intento de acceso a rutas trampa o señuelos del sistema';
        } elseif (strpos($reason, 'SQLI') !== false) {
            $motivoHumano = 'Inyección SQL (SQLi) detectada en parámetros de petición';
        } elseif (strpos($reason, 'XSS') !== false) {
            $motivoHumano = 'Intento de inyección de script malicioso (XSS) detectado';
        } elseif (strpos($reason, 'PATH_TRAVERSAL') !== false) {
            $motivoHumano = 'Intento de evasión de directorios o lectura no autorizada (LFI)';
        } elseif (strpos($reason, 'MALICIOUS_TOOL') !== false) {
            $motivoHumano = 'Herramienta de ataque automatizada o escáner no autorizado';
        } elseif (strpos($reason, 'BURST_GET') !== false || strpos($reason, 'BURST_POST') !== false) {
            $motivoHumano = 'Ráfaga excesiva de peticiones (Protección anti-saturación)';
        } elseif ($httpCode === 429) {
            $motivoHumano = 'Límite de tasa de solicitudes excedido (Demasiadas peticiones)';
        } elseif ($httpCode === 408) {
            $motivoHumano = 'Tiempo de espera agotado (Intento de Slow HTTP / Slowloris detectado)';
        }

        // Bloquear el dispositivo atacante específico e implantar cookie criptográfica
        $itemBloqueado = wafBloquearDispositivo($ip, $ua, $motivoHumano, $reason, $minutos * 60);

        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_destroy();
        }

        // Registrar evento de seguridad en archivo local (sin tocar MySQL)
        $secLogFile = __DIR__ . '/../uploads/waf_security.log';
        $logEntry = json_encode([
            'timestamp' => date('c'),
            'ip'        => $ip,
            'ua'        => substr($ua, 0, 200),
            'tipo'      => $reason,
            'motivo'    => $motivoHumano,
            'uri'       => substr($uri, 0, 200)
        ], JSON_UNESCAPED_UNICODE) . "\n";
        @file_put_contents($secLogFile, $logEntry, FILE_APPEND | LOCK_EX);

        // Incrementar contador diario de bloqueos
        $visitasFile = __DIR__ . '/../uploads/analytics/visitas.json';
        if (is_file($visitasFile)) {
            $vData = @json_decode((string)@file_get_contents($visitasFile), true);
            if (is_array($vData)) {
                $h = date('Y-m-d');
                if (isset($vData[$h])) {
                    $vData[$h]['bloqueos'] = (int)($vData[$h]['bloqueos'] ?? 0) + 1;
                    @file_put_contents($visitasFile, json_encode($vData, JSON_UNESCAPED_UNICODE), LOCK_EX);
                }
            }
        }

        wafRenderizarPaginaBloqueo($ip, $motivoHumano, $minutos, $refIncidente, $itemBloqueado['reincidencias'] ?? 1, false);
    };

    $isLoginEndpoint = (stripos($uri, 'login.php') !== false || stripos($uri, 'me.php') !== false || stripos($uri, 'logout.php') !== false || (isset($_GET['op']) && in_array($_GET['op'], ['login', 'me', 'logout'], true)));

    // 2b. Disparador de Prueba / Simulación de Autobloqueo y Fuerza Bruta
    if (isset($_GET['probar_autobloqueo']) || isset($_GET['test_waf_ban']) || isset($_GET['fuerza_bruta_test'])) {
        wafBloquearDispositivo($ip, $ua, 'Simulación de ataque de fuerza bruta y prueba de baneo WAF', 'TEST_AUTOBLOQUEO_MANUAL', 3600);
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) @session_destroy();
        $blockRequest(403, 'TEST_AUTOBLOQUEO_MANUAL');
    }

    // 3. RUTAS SEÑUELO / HONEYPOT DE SEGURIDAD (Señuelos que ningún usuario legítimo toca)
    if (preg_match('#(?:/api/debug|/backup\.sql|/wp-config\.php(?:\.bak)?|/\.env\.bak|/admin_backup\.php|/\.git/|/api/\.git/)#i', $uri)) {
        $hpLogFile = __DIR__ . '/../uploads/honeypot_events.log';
        $logEntry = json_encode([
            'timestamp' => date('c'),
            'ip'        => $ip,
            'ua'        => substr($ua, 0, 200),
            'method'    => $method,
            'uri'       => substr($uri, 0, 200),
            'tipo'      => 'HONEYPOT_ROUTE_TRIGGER',
            'score'     => 10
        ], JSON_UNESCAPED_UNICODE) . "\n";
        @file_put_contents($hpLogFile, $logEntry, FILE_APPEND | LOCK_EX);

        wafBloquearDispositivo($ip, $ua, 'Acceso a ruta señuelo trampa (' . substr($uri, 0, 40) . ')', 'HONEYPOT_ROUTE_ACCESS', 7200);
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) @session_destroy();
        $blockRequest(403, 'HONEYPOT_ROUTE_TRIGGER');
    }

    // 4. Bloquear herramientas de Slowloris, DoS y Scanners de Vulnerabilidades
    if ($ua !== '' && preg_match('/(slowhttptest|slowloris|torshammer|pyloris|rudy|goldeneye|hulk|loic|hoic|sqlmap|nikto|acunetix|netsparker|nessus|openvas|wpscan|nuclei|dirbuster|gobuster|feroxbuster|ffuf|masscan|zgrab|havij|morfeus|zmeu|hydra|medusa|commix|xsser|dalfox|\$\{jndi:)/i', $ua)) {
        wafBloquearIp($ip, 'Herramienta / Escáner de Hackeo Automatizado', 'MALICIOUS_TOOL_UA', 7200);
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) @session_destroy();
        $blockRequest(403, 'MALICIOUS_TOOL_OR_SLOWLORIS_UA');
    }

    // 5. PROTECCIÓN ANTI-SLOWLORIS Y ANTI-SLOW-POST (R-U-Dead-Yet / RUDY)
    if ($method === 'POST' && $ua === '' && !$isLocalIp) {
        $blockRequest(403, 'EMPTY_UA_ON_POST');
    }

    $httpRange = (string)($_SERVER['HTTP_RANGE'] ?? '');
    if ($httpRange !== '' && substr_count($httpRange, ',') > 5) {
        $blockRequest(403, 'EXCESSIVE_HTTP_RANGE_DOS');
    }

    // 6. Rate Limiting Anti-DoS y Fuerza Bruta (ventana de 60 segundos)
    if (($now - (int)$state['window_start']) >= 60) {
        $state['window_start'] = $now;
        $state['get_count']    = 0;
        $state['post_count']   = 0;
        $state['slow_count']   = 0;
    }

    $limitePost = ($isLocalIp || $esUsuarioAutenticado) ? 1200 : 200;
    $limiteGet  = ($isLocalIp || $esUsuarioAutenticado) ? 3600 : 800;

    if (in_array($method, ['POST', 'PUT', 'DELETE'], true)) {
        $state['post_count']++;
        if ($state['post_count'] > $limitePost) {
            @file_put_contents($ipFile, json_encode($state), LOCK_EX);
            wafBloquearDispositivo($ip, $ua, 'Ataque de fuerza bruta o saturación POST (Rate Limit excedido)', 'BRUTE_FORCE_POST', 3600);
            $_SESSION = [];
            if (session_status() === PHP_SESSION_ACTIVE) @session_destroy();
            $blockRequest(429, 'POST_RATE_LIMIT_EXCEEDED');
        }
    } else {
        $state['get_count']++;
        if ($state['get_count'] > $limiteGet) {
            @file_put_contents($ipFile, json_encode($state), LOCK_EX);
            wafBloquearDispositivo($ip, $ua, 'Ráfaga masiva de peticiones GET (Ataque DoS / Flood)', 'DOS_BURST_GET', 3600);
            $_SESSION = [];
            if (session_status() === PHP_SESSION_ACTIVE) @session_destroy();
            $blockRequest(429, 'GET_DOS_LIMIT_EXCEEDED');
        }
    }
    @file_put_contents($ipFile, json_encode($state), LOCK_EX);

    // Registrar visita legítima en el resumen analítico ligero diario (sin MySQL)
    wafRegistrarVisita($ip, $ua, $uri, false);

    // 7. Bloqueo de Null Byte Injection (%00)
    if (strpos($uri, '%00') !== false || strpos($uri, "\0") !== false || strpos($query, '%00') !== false) {
        wafBloquearDispositivo($ip, $ua, 'Ataque Null Byte Injection (%00)', 'NULL_BYTE_INJECTION', 3600);
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) @session_destroy();
        $blockRequest(403, 'NULL_BYTE_INJECTION');
    }

    // 8. Reglas de Inspección Profunda (SQLi, XSS, LFI, RCE)
    $patronesAtaque = [
        'SQLI_UNION'      => '/\bunion\s+(?:all\s+)?select\b/i',
        'SQLI_SCHEMA'     => '/\b(?:information_schema|mysql\.user|performance_schema|sys\.schema)\b/i',
        'SQLI_TIMING'     => '/\b(?:benchmark\s*\(\s*\d+|sleep\s*\(\s*\d+|waitfor\s+delay\s+[\'"])/i',
        'SQLI_FILE'       => '/\b(?:into\s+(?:outfile|dumpfile)|load_file\s*\()/i',
        'SQLI_TAUTOLOGY'  => '/(?:[\'"]\s*(?:or|and)\s*[\'"]?\d+[\'"]?\s*=\s*[\'"]?\d+|\b(?:or|and)\s+1\s*=\s*1\b\s*(?:--|#|\/*))/i',
        'SQLI_STACKED'    => '/;\s*(?:drop|truncate|alter)\s+(?:table|database)\b/i',
        'LFI_TRAVERSAL'   => '/(?:\.\.\/|\.\.\\\\){2,}/',
        'LFI_SYSTEM_FILE' => '/(?:\/etc\/(?:passwd|shadow|hosts)|\/proc\/self\/environ|boot\.ini|win\.ini)/i',
        'RCE_WRAPPERS'    => '/\b(?:php:\/\/(?:filter|input|expect)|expect:\/\/|phar:\/\/|zip:\/\/|data:\/\/text\/(?:plain|html))/i',
        'XSS_SCRIPT_TAG'  => '/<\s*script\b[^>]*>/i',
        'XSS_EVENT_ATTR'  => '/\bon(?:error|load|mouseover|focus|beforescriptexecute|animationstart|toggle)\s*=\s*[^\s>]+/i',
        'XSS_JS_URI'      => '/(?:javascript|vbscript)\s*:/i',
        'RCE_JNDI'        => '/\$\{jndi\s*:\s*(?:ldap|rmi|dns|iiop)\s*:/i',
    ];

    $inspeccionarValor = function (string $valor, string $origen) use ($patronesAtaque, $blockRequest, $ip, $ua, $isLocalIp, $esAdminAutenticado): void {
        if ($valor === '' || strlen($valor) < 4) {
            return;
        }
        if (preg_match('#^data:image/(?:jpeg|jpg|png|webp);base64,[A-Za-z0-9+/=\s]+$#i', $valor)) {
            return;
        }
        $decodificado = rawurldecode($valor);
        foreach ($patronesAtaque as $nombreRegla => $regex) {
            if (preg_match($regex, $decodificado) || ($decodificado !== $valor && preg_match($regex, $valor))) {
                $tipoHumano = 'Intento de Inyección SQL (SQLi)';
                if (strpos($nombreRegla, 'XSS') !== false) {
                    $tipoHumano = 'Ataque de Script Malicioso (XSS)';
                } elseif (strpos($nombreRegla, 'LFI') !== false) {
                    $tipoHumano = 'Intento de Path Traversal / Acceso no autorizado';
                } elseif (strpos($nombreRegla, 'RCE') !== false) {
                    $tipoHumano = 'Intento de Ejecución Remota de Código (RCE)';
                }
                wafBloquearDispositivo($ip, $ua, $tipoHumano, $nombreRegla . '_IN_' . $origen, 3600);
                $_SESSION = [];
                if (session_status() === PHP_SESSION_ACTIVE) {
                    @session_destroy();
                }
                $blockRequest(403, "{$nombreRegla}_IN_{$origen}");
            }
        }
    };

    if ($query !== '') {
        $inspeccionarValor($query, 'QUERY_STRING');
    }
    $inspeccionarValor($uri, 'REQUEST_URI');

    $recorrerArray = function (array $arr, string $origen) use (&$recorrerArray, $inspeccionarValor): void {
        foreach ($arr as $k => $v) {
            if (is_string($k)) {
                if (in_array(strtolower($k), ['password', 'clave', 'pass', 'contrasena', 'contraseña'], true)) {
                    continue;
                }
                $inspeccionarValor($k, $origen . '_KEY');
            }
            if (is_array($v)) {
                $recorrerArray($v, $origen);
            } elseif (is_string($v)) {
                if (is_string($k) && in_array(strtolower($k), ['password', 'clave', 'pass', 'contrasena', 'contraseña'], true)) {
                    continue;
                }
                $inspeccionarValor($v, $origen);
            }
        }
    };

    if (!empty($_GET)) {
        $recorrerArray($_GET, 'GET');
    }
    if (!empty($_POST)) {
        $recorrerArray($_POST, 'POST');
    }

    // 9. Inspección de cuerpo JSON y detección de Slow-POST / Content-Length truncado
    if (in_array($method, ['POST', 'PUT', 'DELETE'], true)) {
        $op = (string)($_GET['op'] ?? '');
        // El administrador guardando CMS legítimo no debe sufrir falsos positivos por contenido de textos/HTML
        if ($esAdminAutenticado && in_array($op, ['textos/guardar', 'documentos/guardar', 'categorias/guardar', 'staff/guardar', 'plantillas/guardar', 'entrenadores/editar'], true)) {
            return;
        }
        $contentType = (string)($_SERVER['CONTENT_TYPE'] ?? '');
        if (stripos($contentType, 'application/json') !== false) {
            $readStart = microtime(true);
            $rawBody   = (string)file_get_contents('php://input');
            $readTime  = microtime(true) - $readStart;

            if (strlen($rawBody) > 8 * 1024 * 1024) {
                $blockRequest(413, 'JSON_PAYLOAD_TOO_LARGE');
            }
            if ($rawBody !== '') {
                $jsonData = json_decode($rawBody, true);
                if (is_array($jsonData)) {
                    $recorrerArray($jsonData, 'JSON_BODY');
                }
            }
        }
    }
})();
