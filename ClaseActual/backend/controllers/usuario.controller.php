<?php

require_once __DIR__ . '/../models/usuario.class.php';
require_once __DIR__ . '/../utils/auth_helper.php'; // Incluir el helper de autenticación

//verificacion del metodo http
$method = $_SERVER['REQUEST_METHOD'];

//instancia de la clase usuario para usar sus metodos
$usuario = new Usuario();

switch($method){
    case 'GET':
        require_auth(); // Cualquier usuario logueado puede hacer peticiones GET

        if (isset($_GET['id'])) {
            $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
            if ($id === false) {
                http_response_code(400);
                echo json_encode(['message' => 'ID de usuario no válido.']);
            } else {
                // Permitir al usuario obtener su propio perfil, o al admin obtener cualquier perfil
                if ((int)$_SESSION['user_id'] === $id || has_role_id(2)) { // Asumiendo que el rol de admin es ID 2
                    $resultado = $usuario->get_user_by_id($id);
                    if ($resultado) {
                        http_response_code(200);
                        echo json_encode($resultado);
                    } else {
                        http_response_code(404);
                        echo json_encode(['message' => 'Usuario no encontrado.']);
                    }
                } else {
                    http_response_code(403); // Prohibido
                    echo json_encode(['message' => 'No tiene permisos para ver este perfil.']);
                }
            }
        } else {
            // Solo el administrador puede obtener todos los usuarios
            require_auth(2); // Requerir rol de admin para obtener todos los usuarios
            $resultados = $usuario->get_user();
            http_response_code(200);
            echo json_encode($resultados);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));

        if (
            !empty($data->nombre) &&
            !empty($data->correo) &&
            !empty($data->contrasena) && // Password is now required
            !empty($data->cedula) &&
            !empty($data->telefono)
        ) {
            $id_rol = isset($data->id_rol) ? filter_var($data->id_rol, FILTER_VALIDATE_INT) : 1; // Default to 'usuario' (ID 1)
            if ($id_rol === false) {
                http_response_code(400);
                echo json_encode(['message' => 'ID de rol no válido.']);
                break;
            }

            if ($usuario->create_user($data->nombre, $data->correo, $data->contrasena, $data->cedula, $data->telefono, $id_rol)) {
                http_response_code(201);
                echo json_encode(['message' => 'Usuario creado exitosamente.']);
            } else {
                http_response_code(500);
                echo json_encode(['message' => 'No se pudo crear el usuario.']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Datos incompletos. Faltan nombre, correo, contrasena, cedula, telefono o id_rol.']);
        }
        break;

    case 'PUT':
        require_auth(2); // Only admin can update users
        $data = json_decode(file_get_contents("php://input"));
        
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['message' => 'No se proporcionó un ID de usuario para actualizar.']);
            break;
        }

        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        $id_rol = isset($data->id_rol) ? filter_var($data->id_rol, FILTER_VALIDATE_INT) : null;
        if ($id_rol === false) {
            http_response_code(400);
            echo json_encode(['message' => 'ID de rol no válido.']);
            break;
        }

        // Check if required fields for update are present. Password is optional for update.
        if ($id === false || 
            empty($data->nombre) ||
            empty($data->correo) ||
            empty($data->cedula) ||
            empty($data->telefono) ||
            $id_rol === null // id_rol must be present
        ) {
            http_response_code(400);
            echo json_encode(['message' => 'Datos incompletos o ID no válido para actualizar.']);
            break;
        }

        $contrasena = isset($data->contrasena) ? $data->contrasena : null; // Password is optional for update

        if ($usuario->update_user($id, $data->nombre, $data->correo, $data->cedula, $data->telefono, $id_rol, $contrasena)) {
            http_response_code(200);
            echo json_encode(['message' => 'Usuario actualizado exitosamente.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'No se pudo actualizar el usuario.']);
        }
        break;

    case 'DELETE':
        require_auth(2); // Only admin can delete users
        // Lógica para peticiones DELETE (Eliminar)
        // El ID debe venir en la URL
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['message' => 'No se proporcionó un ID de usuario para eliminar.']);
            break;
        }

        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if ($id === false) {
            http_response_code(400);
            echo json_encode(['message' => 'ID de usuario no válido.']);
            break;
        }

        if ($usuario->delete_user($id)) {
            http_response_code(200);
            echo json_encode(['message' => 'Usuario eliminado exitosamente.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'No se pudo eliminar el usuario.']);
        }
        break;

    default:
        // Si se usa un método no soportado.
        http_response_code(405); // 405 Method Not Allowed
        echo json_encode(['message' => 'Método no permitido.']);
        break;
}
