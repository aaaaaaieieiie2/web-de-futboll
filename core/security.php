<?php
/* core/security.php — Capa de seguridad NicoSport (Fase 1)
   - Lista blanca CORS con credenciales
   - Verificación CSRF para escrituras
   - Sanitizado de HTML (XSS almacenado)
   - Rate limiting de login
*/

/* ===== CORS: lista blanca estricta ===== */
function cors_origenes_permitidos(): array {
    // Orígenes extra vía variable de entorno, separados por coma.
    $extra = getenv('CORS_ALLOWED_ORIGINS');
    $lista = [
        'http://localhost', 'http://localhost:8080',
        'http://127.0.0.1', 'http://127.0.0.1:8080',
    ];
    if ($extra) {
        foreach (explode(',', $extra) as $o) {
            $o = trim($o);
            if ($o !== '') $lista[] = $o;
        }
    }
    return $lista;
}

function cors_aplicar(): void {
    $origen = $_SERVER['HTTP_ORIGIN'] ?? '';
    if ($origen !== '' && in_array($origen, cors_origenes_permitidos(), true)) {
        header('Access-Control-Allow-Origin: ' . $origen);
        header('Vary: Origin');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token, X-Requested-With');
    }
    // Preflight: responder 204 y salir.
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

/* ===== CSRF ===== */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_validar(): bool {
    // El frontend envía la cabecera X-CSRF-TOKEN -> HTTP_X_CSRF_TOKEN
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
    return !empty($_SESSION['csrf_token']) && is_string($token) && $token !== ''
        && hash_equals($_SESSION['csrf_token'], $token);
}

function csrf_exigir(): void {
    if (!csrf_validar()) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'error' => 'Invalid CSRF token']);
        exit;
    }
}

/* ===== Cabeceras de seguridad ===== */
function cabeceras_seguridad(): void {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: same-origin');
    header_remove('X-Powered-By');
}

/* ===== Sanitizado de HTML (anti-XSS almacenado) ===== */
/* Devuelve HTML seguro: solo etiquetas/atributos de formato. Elimina
   scripts, eventos on*, javascript:, data: en href/src, etc. */
function sanitizar_html(?string $html): string {
    if ($html === null || trim($html) === '') return '';
    $allowed = '<p><br><b><strong><i><em><u><s><ul><ol><li><h1><h2><h3><h4><span><a><img><table><thead><tbody><tr><td><th><blockquote>';
    $html = strip_tags($html, $allowed);
    // Quitar atributos de eventos (onclick=, onerror= ...) incluso ofuscados
    $html = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
    // Quitar URLs peligrosas
    $html = preg_replace('/(href|src)\s*=\s*(["\']?)\s*(javascript|vbscript|data)\s*:[^"\'>\s]*(\2)/i', '$1=$2#$2', $html);
    return $html;
}

/* Texto plano: normaliza (para nombres, conceptos, referencias...) */
function limpiar_texto(?string $t, int $maxLen = 500): string {
    if ($t === null) return '';
    $t = trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $t));
    return mb_substr($t, 0, $maxLen);
}

/* ===== Rate limiting de login (anti-fuerza bruta) ===== */
function login_throttle_check(int $maxIntentos = 5, int $ventanaSeg = 900): ?int {
    // Devuelve segundos restantes de bloqueo, o null si puede intentar.
    if (empty($_SESSION['login_ts'])) $_SESSION['login_ts'] = [];
    $ahora = time();
    $_SESSION['login_ts'] = array_values(array_filter($_SESSION['login_ts'], fn($t) => $t > $ahora - $ventanaSeg));
    if (count($_SESSION['login_ts']) >= $maxIntentos) {
        $espera = $ventanaSeg - ($ahora - $_SESSION['login_ts'][0]);
        return max(1, $espera);
    }
    return null;
}

function login_throttle_fail(): void {
    if (empty($_SESSION['login_ts'])) $_SESSION['login_ts'] = [];
    $_SESSION['login_ts'][] = time();
}

function login_throttle_reset(): void {
    unset($_SESSION['login_ts']);
}
