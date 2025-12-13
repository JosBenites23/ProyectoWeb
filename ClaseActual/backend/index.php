<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start(); // Iniciar la sesión PHP

// 1. CONFIGURACIÓN GLOBAL
// Permitimos acceso desde cualquier origen, definimos la respuesta como JSON, etc.
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Incluimos archivos de configuración importantes.
require_once __DIR__ . '/settings/bd.php';
// require_once __DIR__ . '/settings/client.php'; // Si es necesario, también se carga aquí.

// 2. ENRUTADOR SIMPLE
// Obtenemos el recurso solicitado desde la URL (ej: /backend/index.php?recurso=usuarios)
$recurso = isset($_GET['recurso']) ? $_GET['recurso'] : null;

// Construimos la ruta al archivo del controlador
$controladorPath = __DIR__ . '/controllers/' . $recurso . '.controller.php';

// Verificamos si el controlador existe
if ($recurso && file_exists($controladorPath)) {
    // Si existe, lo cargamos. El controlador se encargará del resto.
    require_once $controladorPath;
} else {
    // Si el recurso no existe o no se especificó, devolvemos un error 404.
    http_response_code(404);
    echo json_encode(['message' => 'Recurso no encontrado.']);
}

