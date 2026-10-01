<?php
require __DIR__ . '/../core/conexion.php';
header('Content-Type: application/json; charset=utf-8');

session_destroy();
echo json_encode(['success' => true]);
