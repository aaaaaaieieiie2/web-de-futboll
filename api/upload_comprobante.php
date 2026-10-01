<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Metodo no permitido']);
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

$allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['error' => 'Tipo de archivo no permitido. Usa JPG, PNG o WEBP.']);
    exit;
}

$filename = 'comp_' . time() . '_' . uniqid() . '.webp';
$destination = '../uploads/comprobantes/' . $filename;

// Compresión con GD
$source_image = null;
if ($file['type'] == 'image/jpeg') {
    $source_image = imagecreatefromjpeg($file['tmp_name']);
} elseif ($file['type'] == 'image/png') {
    $source_image = imagecreatefrompng($file['tmp_name']);
} elseif ($file['type'] == 'image/webp') {
    $source_image = imagecreatefromwebp($file['tmp_name']);
}

if ($source_image) {
    // Redimensionar si es mayor a 1200px
    $width = imagesx($source_image);
    $height = imagesy($source_image);
    if ($width > 1200) {
        $new_width = 1200;
        $new_height = floor($height * ($new_width / $width));
        $virtual_image = imagecreatetruecolor($new_width, $new_height);
        // Preservar transparencia para PNG si es que luego se pasa a webp (webp soporta alfa)
        imagealphablending($virtual_image, false);
        imagesavealpha($virtual_image, true);
        imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
        imagewebp($virtual_image, $destination, 75); // Calidad 75% WebP
        imagedestroy($virtual_image);
    } else {
        imagewebp($source_image, $destination, 75);
    }
    imagedestroy($source_image);
    echo json_encode(['success' => true, 'url' => 'uploads/comprobantes/' . $filename]);
} else {
    // Fallback if GD fails
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        echo json_encode(['success' => true, 'url' => 'uploads/comprobantes/' . $filename]);
    } else {
        echo json_encode(['error' => 'No se pudo procesar la imagen.']);
    }
}
?>
