<?php
namespace routes;

use Controllers\ElDniController;
use Controllers\RUCController;

$rucController = new RUCController();
$dniController = new ElDniController();

// Manejar solicitudes OPTIONS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit;
}

// Configurar encabezados CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// Rutas para DNI
$app->route("POST /dni/@dni", [$dniController, 'elDni']);

// Rutas para RUC
$app->route("POST /ruc/@ruc", [$rucController, 'getRUC']);

// Ruta pagina no encontrada
$app->route("*", function () {
    $data = [
        'status' => 404,
        'message' => 'Metodo o Pagina no encontrada'
    ];

    echo json_encode($data, JSON_PRETTY_PRINT);
});