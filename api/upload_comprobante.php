<?php
/* api/upload_comprobante.php — Subida segura de comprobantes (auth + validación real de imagen) */
require __DIR__ . '/../core/conexion.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Metodo no permitido']);
    exit;
}

// 1. Autenticación obligatoria (padres y admin suben comprobantes)
if (!autenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

// 2. CSRF para la subida
if (!csrf_validar()) {
    http_response_code(403);
    echo json_encode(['error' => 'Token CSRF inválido']);
    exit;
}

if (!isset($_FILES['file'])) {
    echo json_encode(['error' => 'No se recibio ningun archivo']);
    exit;
}

$file = $_FILES['file'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'Error al subir el archivo. Codigo: ' . $file['error']]);
    exit;
}

// 3. Límite de tamaño: 5 MB
if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['error' => 'El archivo supera el limite de 5 MB']);
    exit;
}

// 4. Validación REAL del contenido (no confiar en $file['type'] enviado por el cliente)
$info = @getimagesize($file['tmp_name']);
if ($info === false) {
    echo json_encode(['error' => 'El archivo no es una imagen valida']);
    exit;
}
$mime = $info['mime'] ?? '';
$allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
if (!in_array($mime, $allowed_types, true)) {
    echo json_encode(['error' => 'Tipo de archivo no permitido. Usa JPG, PNG o WEBP.']);
    exit;
}

// 5. Destino dentro del docroot (uploads/) con nombre generado seguro
$dir = __DIR__ . '/../uploads/comprobantes';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}
$filename = 'comp_' . time() . '_' . bin2hex(random_bytes(6)) . '.webp';
$destination = $dir . '/' . $filename;

// 6. Re-codificar con GD (neutraliza payloads embebidos en la imagen)
$source_image = null;
if ($mime === 'image/jpeg') {
    $source_image = @imagecreatefromjpeg($file['tmp_name']);
} elseif ($mime === 'image/png') {
    $source_image = @imagecreatefrompng($file['tmp_name']);
} elseif ($mime === 'image/webp') {
    $source_image = @imagecreatefromwebp($file['tmp_name']);
}

if ($source_image) {
    $width = imagesx($source_image);
    $height = imagesy($source_image);
    if ($width > 1200) {
        $new_width = 1200;
        $new_height = (int)floor($height * ($new_width / $width));
        $virtual_image = imagecreatetruecolor($new_width, $new_height);
        imagealphablending($virtual_image, false);
        imagesavealpha($virtual_image, true);
        imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        imagewebp($virtual_image, $destination, 75);
        imagedestroy($virtual_image);
    } else {
        imagewebp($source_image, $destination, 75);
    }
    imagedestroy($source_image);
    echo json_encode(['success' => true, 'url' => 'uploads/comprobantes/' . $filename]);
    exit;
}

// Si GD falla, NO mover el original (podría contener payload): rechazar.
http_response_code(422);
echo json_encode(['error' => 'No se pudo procesar la imagen. Intenta con otro archivo.']);
