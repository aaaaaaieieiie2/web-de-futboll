<?php
/* api/upload_comprobante.php — Subida Segura de Imágenes (Auth + CSRF + finfo Magic Bytes + GD Re-encode) */
require_once __DIR__ . '/../core/conexion.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// 1. Autenticación obligatoria (solo padre o admin)
$rol = $_SESSION['ns_rol'] ?? '';
if (!in_array($rol, ['admin', 'padre'], true)) {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado para subir archivos']);
    exit;
}

// 2. Verificación Anti-CSRF
$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token) || !verificarCsrfToken($token)) {
    http_response_code(403);
    echo json_encode(['error' => 'Token CSRF inválido']);
    exit;
}

$uploadDir = __DIR__ . '/../uploads/comprobantes/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'No se recibió un archivo válido']);
    exit;
}

$file = $_FILES['file'];
$tmpPath = $file['tmp_name'];

// 3. Validación de archivo subido legítimo y límite de tamaño (máx. 5 MB)
$maxBytes = 5 * 1024 * 1024;
if (!is_uploaded_file($tmpPath) || $file['size'] <= 0 || $file['size'] > $maxBytes) {
    http_response_code(400);
    echo json_encode(['error' => 'Archivo inválido o supera el límite de 5 MB']);
    exit;
}

// 4. Inspección real de Magic Bytes y dimensiones (nunca confiar en $_FILES['type'])
$finfo = new finfo(FILEINFO_MIME_TYPE);
$realMime = $finfo->file($tmpPath);
$allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];

$imgInfo = @getimagesize($tmpPath);
if (!in_array($realMime, $allowedMimes, true) || $imgInfo === false) {
    http_response_code(400);
    echo json_encode(['error' => 'Formato no permitido. Solo imágenes JPG, PNG o WEBP reales.']);
    exit;
}

// 5. Re-codificación estricta con GD o almacenamiento seguro con nombre inmunizado
$img = null;
if (extension_loaded('gd')) {
    if ($realMime === 'image/jpeg' && function_exists('imagecreatefromjpeg')) {
        $img = @imagecreatefromjpeg($tmpPath);
    } elseif ($realMime === 'image/png' && function_exists('imagecreatefrompng')) {
        $img = @imagecreatefrompng($tmpPath);
    } elseif ($realMime === 'image/webp' && function_exists('imagecreatefromwebp')) {
        $img = @imagecreatefromwebp($tmpPath);
    }
}

if ($img) {
    $width = imagesx($img);
    $height = imagesy($img);
    $maxWidth = 1200;

    if ($width > $maxWidth && function_exists('imagecreatetruecolor')) {
        $newHeight = (int)floor($height * ($maxWidth / $width));
        $tmpImg = imagecreatetruecolor($maxWidth, $newHeight);
        imagealphablending($tmpImg, false);
        imagesavealpha($tmpImg, true);
        imagecopyresampled($tmpImg, $img, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);
        imagedestroy($img);
        $img = $tmpImg;
    }

    $filename = 'comp_' . time() . '_' . bin2hex(random_bytes(8)) . '.webp';
    $destPath = $uploadDir . $filename;
    imagewebp($img, $destPath, 80);
    imagedestroy($img);
} else {
    $extMap = ['image/jpeg' => '.jpg', 'image/png' => '.png', 'image/webp' => '.webp'];
    $ext = $extMap[$realMime] ?? '.jpg';
    $filename = 'comp_' . time() . '_' . bin2hex(random_bytes(8)) . $ext;
    $destPath = $uploadDir . $filename;
    if (!move_uploaded_file($tmpPath, $destPath)) {
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo guardar el archivo en el servidor']);
        exit;
    }
}

// Si se estaba reemplazando un comprobante previo aún no enviado, eliminar el archivo anterior para no acumular basura
$prevPath = $_POST['old_path'] ?? '';
if (is_string($prevPath) && preg_match('#^uploads/comprobantes/comp_[0-9]+_[a-f0-9]+\.(webp|jpg|jpeg|png)$#', $prevPath)) {
    $fullPrev = __DIR__ . '/../' . $prevPath;
    if (is_file($fullPrev)) {
        @unlink($fullPrev);
    }
}

$relUrl = 'uploads/comprobantes/' . $filename;
echo json_encode(['ok' => true, 'success' => true, 'path' => $relUrl, 'url' => $relUrl]);