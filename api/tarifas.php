<?php
header('Content-Type: application/json');
$file = 'config_tarifas.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['mensualidad']) && isset($input['inscripcion'])) {
        $data = [
            'mensualidad' => (float)$input['mensualidad'],
            'inscripcion' => (float)$input['inscripcion']
        ];
        file_put_contents($file, json_encode($data));
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Faltan datos']);
    }
} else {
    if (file_exists($file)) {
        echo file_get_contents($file);
    } else {
        echo json_encode(['mensualidad' => 15.00, 'inscripcion' => 15.00]);
    }
}
?>
