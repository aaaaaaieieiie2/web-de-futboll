<?php
// Fronterización (Front-Controller Pattern)
// Este archivo es el único punto de entrada público. Toda la lógica de negocio y renderizado
// está encapsulada en la zona privada (core/).

require_once __DIR__ . '/../core/controllers/FrontController.php';

// Iniciar renderizado
FrontController::render();
