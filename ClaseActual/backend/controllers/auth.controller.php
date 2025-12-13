<?php

require_once __DIR__ . '/../models/usuario.class.php';
require_once __DIR__ . '/../utils/auth_helper.php'; // Include auth helper

$method = $_SERVER['REQUEST_METHOD'];

$usuario = new Usuario();

switch($method){
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));

        // Check if it's a login request
        if (isset($data->action) && $data->action === 'login') {
            if (!empty($data->correo) && !empty($data->contrasena)) {
                $user_data = $usuario->login_user($data->correo, $data->contrasena);

                if ($user_data) {
                    http_response_code(200); // OK
                    $_SESSION['user_id'] = $user_data['id'];
                    $_SESSION['user_id_rol'] = $user_data['id_rol']; // Store id_rol
                    // Optional: store other non-sensitive user data
                    $_SESSION['user_name'] = $user_data['nombre'];
                    $_SESSION['user_email'] = $user_data['correo'];
                    
                    echo json_encode(['message' => 'Inicio de sesión exitoso.', 'user' => $user_data]);
                } else {
                    http_response_code(401); // Unauthorized
                    echo json_encode(['message' => 'Correo o contraseña incorrectos.']);
                }
            } else {
                http_response_code(400); // Bad Request
                echo json_encode(['message' => 'Faltan correo o contraseña para iniciar sesión.']);
            }
        } else {
            http_response_code(400); // Bad Request
            echo json_encode(['message' => 'Acción no válida.']);
        }
        break;

    case 'GET':
        if (isset($_GET['action']) && $_GET['action'] === 'logout') {
            logout_user(); // Call the logout function from auth_helper.php
            http_response_code(200);
            echo json_encode(['message' => 'Sesión cerrada exitosamente.']);
            exit();
        }
        http_response_code(400); // Bad Request for GET without action
        echo json_encode(['message' => 'Acción GET no válida.']);
        break;

    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(['message' => 'Método no permitido.']);
        break;
}