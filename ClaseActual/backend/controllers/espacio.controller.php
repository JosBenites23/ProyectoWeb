<?php

require_once __DIR__ . '/../models/espacio.class.php';
require_once __DIR__ . '/../utils/auth_helper.php'; // Incluir el helper de autenticación

$method = $_SERVER['REQUEST_METHOD'];

$espacio = new Espacio();

switch($method){
    case 'GET':
        // GET requests for spaces can be public or for any logged-in user.
        // "Visualización de Disponibilidad" (user) implica vista pública.
        // Por ahora, todos los GET son públicos, pero PUT/POST/DELETE son solo para admin.
        if (isset($_GET['id'])) {
            $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
            if ($id === false) {
                http_response_code(400);
                echo json_encode(['message' => 'ID de espacio no válido.']);
            } else {
                $resultado = $espacio->get_espacio_by_id($id);
                if ($resultado) {
                    http_response_code(200);
                    echo json_encode($resultado);
                } else {
                    http_response_code(404);
                    echo json_encode(['message' => 'Espacio no encontrado.']);
                }
            }
        } else {
            $resultados = $espacio->get_all_espacios();
            http_response_code(200);
            echo json_encode($resultados);
        }
        break;

    case 'POST':
        require_auth(2); // Only admin can create spaces
        $data = json_decode(file_get_contents("php://input"));

        if (
            !empty($data->nombre) &&
            !empty($data->capacidad) &&
            !empty($data->precio_diario) &&
            !empty($data->tipo_espacio)
        ) {
            if ($espacio->create_espacio(
                $data->nombre, 
                isset($data->descripcion) ? $data->descripcion : null, 
                $data->capacidad, 
                $data->precio_diario, 
                $data->tipo_espacio, 
                isset($data->politicas_reserva) ? $data->politicas_reserva : null
            )) {
                http_response_code(201);
                echo json_encode(['message' => 'Espacio creado exitosamente.']);
            } else {
                http_response_code(500);
                echo json_encode(['message' => 'No se pudo crear el espacio.']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Datos incompletos. Faltan nombre, capacidad, precio_diario o tipo_espacio.']);
        }
        break;

    case 'PUT':
        require_auth(2); // Only admin can update spaces
        $data = json_decode(file_get_contents("php://input"));
        
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['message' => 'No se proporcionó un ID de espacio para actualizar.']);
            break;
        }

        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if ($id === false || 
            empty($data->nombre) ||
            empty($data->capacidad) ||
            empty($data->precio_diario) ||
            empty($data->tipo_espacio)
        ) {
            http_response_code(400);
            echo json_encode(['message' => 'Datos incompletos o ID no válido para actualizar.']);            
            break;
        }

        $activo = isset($data->activo) ? (bool)$data->activo : true; // Default to true if not provided

        if ($espacio->update_espacio(
            $id, 
            $data->nombre, 
            isset($data->descripcion) ? $data->descripcion : null, 
            $data->capacidad, 
            $data->precio_diario, 
            $data->tipo_espacio, 
            isset($data->politicas_reserva) ? $data->politicas_reserva : null,
            $activo
        )) {
            http_response_code(200);
            echo json_encode(['message' => 'Espacio actualizado exitosamente.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'No se pudo actualizar el espacio.']);
        }
        break;

    case 'DELETE':
        require_auth(2); // Only admin can delete spaces
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['message' => 'No se proporcionó un ID de espacio para eliminar.']);
            break;
        }

        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if ($id === false) {
            http_response_code(400);
            echo json_encode(['message' => 'ID de espacio no válido.']);
            break;
        }

        if ($espacio->delete_espacio($id)) {
            http_response_code(200);
            echo json_encode(['message' => 'Espacio eliminado exitosamente.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'No se pudo eliminar el espacio.']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['message' => 'Método no permitido.']);
        break;
}
