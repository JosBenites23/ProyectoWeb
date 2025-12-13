<?php

require_once __DIR__ . '/../vendor/autoload.php'; // Autoloader de Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\Output\QROutputInterface;


require_once __DIR__ . '/../models/reserva.class.php';
require_once __DIR__ . '/../models/espacio.class.php'; // Necesario para obtener el precio del espacio
require_once __DIR__ . '/../utils/auth_helper.php'; // Incluir el helper de autenticación

$method = $_SERVER['REQUEST_METHOD'];

$reserva = new Reserva();
$espacio = new Espacio(); // Instancia para obtener datos del espacio

// Directorio para guardar los códigos QR
const QR_CODE_DIR = __DIR__ . '/../../public/qrcodes/';

switch($method){
    case 'GET':
        require_auth(); // Any logged-in user can access GET requests
        if (isset($_GET['id'])) {
            $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
            if ($id === false) {
                http_response_code(400);
                echo json_encode(['message' => 'ID de reserva no válido.']);
            } else {
                $resultado = $reserva->get_reserva_by_id($id);
                if ($resultado) {
                    // User can get their own reservation or admin can get any
                    if ((int)$_SESSION['user_id'] === (int)$resultado['id_usuario'] || has_role_id(2)) {
                        http_response_code(200);
                        echo json_encode($resultado);
                    } else {
                        http_response_code(403);
                        echo json_encode(['message' => 'No tiene permisos para ver esta reserva.']);
                    }
                } else {
                    http_response_code(404);
                    echo json_encode(['message' => 'Reserva no encontrada.']);
                }
            }
        } elseif (isset($_GET['id_usuario'])) {
            $id_usuario = filter_var($_GET['id_usuario'], FILTER_VALIDATE_INT);
            if ($id_usuario === false) {
                http_response_code(400);
                echo json_encode(['message' => 'ID de usuario no válido.']);
            } else {
                // User can get their own reservations, or admin can get any user's reservations
                if ((int)$_SESSION['user_id'] === $id_usuario || has_role_id(2)) {
                    $resultados = $reserva->get_reservas_by_user_id($id_usuario);
                    http_response_code(200);
                    echo json_encode($resultados);
                } else {
                    http_response_code(403);
                    echo json_encode(['message' => 'No tiene permisos para ver estas reservas.']);
                }
            }
        } elseif (isset($_GET['id_espacio'])) {
            // Admin only to get all reservations for a specific space
            require_auth(2); 
            $id_espacio = filter_var($_GET['id_espacio'], FILTER_VALIDATE_INT);
            if ($id_espacio === false) {
                http_response_code(400);
                echo json_encode(['message' => 'ID de espacio no válido.']);
            } else {
                $resultados = $reserva->get_reservas_by_espacio_id($id_espacio);
                http_response_code(200);
                echo json_encode($resultados);
            }
        } else {
            // Admin only to get all reservations
            require_auth(2);
            $resultados = $reserva->get_all_reservas();
            http_response_code(200);
            echo json_encode($resultados);
        }
        break;

    case 'POST':
        require_auth(); // Any logged-in user can create a reservation
        $data = json_decode(file_get_contents("php://input"));

        if (
            !empty($data->id_usuario) &&
            !empty($data->id_espacio) &&
            !empty($data->fecha_inicio) &&
            !empty($data->fecha_fin)
        ) {
            $id_usuario = filter_var($data->id_usuario, FILTER_VALIDATE_INT);
            $id_espacio = filter_var($data->id_espacio, FILTER_VALIDATE_INT);
            $fecha_inicio = $data->fecha_inicio;
            $fecha_fin = $data->fecha_fin;

            if ($id_usuario === false || $id_espacio === false) {
                http_response_code(400);
                echo json_encode(['message' => 'IDs de usuario o espacio no válidos.']);
                break;
            }
            
            // Crucial: A user can only create a reservation for themselves
            if ((int)$_SESSION['user_id'] !== $id_usuario && !has_role_id(2)) {
                http_response_code(403);
                echo json_encode(['message' => 'No tiene permisos para crear una reserva para otro usuario.']);
                break;
            }

            // 1. Verificar disponibilidad
            if (!$reserva->check_availability($id_espacio, $fecha_inicio, $fecha_fin)) {
                http_response_code(409); // Conflict
                echo json_encode(['message' => 'El espacio no está disponible en las fechas seleccionadas.']);
                break;
            }

            // 2. Calcular total pagado
            $espacio_info = $espacio->get_espacio_by_id($id_espacio);
            if (!$espacio_info) {
                http_response_code(404);
                echo json_encode(['message' => 'Espacio no encontrado para calcular el precio.']);
                break;
            }

            $precio_diario = (float)$espacio_info['precio_diario'];
            $start_date = new DateTime($fecha_inicio);
            $end_date = new DateTime($fecha_fin);
            $interval = $start_date->diff($end_date);
            $dias = $interval->days;

            if ($dias == 0) $dias = 1; // Mínimo un día si es el mismo día
            $total_pagado = $precio_diario * $dias;

            // 3. Generar código QR
            // La información del QR podría ser un URL al detalle de la reserva o un string de validación
            $qr_data_string = "ReservaID:" . uniqid() . "|EspacioID:" . $id_espacio . "|UsuarioID:" . $id_usuario . "|Inicio:" . $fecha_inicio . "|Fin:" . $fecha_fin;
            $qr_filename = 'qr_' . md5($qr_data_string) . '.png';
            $qr_filepath = QR_CODE_DIR . $qr_filename;
            
            // Ensure QR directory exists
            if (!is_dir(QR_CODE_DIR)) {
                mkdir(QR_CODE_DIR, 0777, true);
            }
            
            try {
                (new QRCode())->render($qr_data_string, $qr_filepath);
            } catch (\Exception $e) {
                error_log("Error generando QR: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['message' => 'Error al generar el código QR.']);
                break;
            }
            $codigo_qr_path_for_db = '/public/qrcodes/' . $qr_filename; // Path to save in DB

            // 4. Crear la reserva en la DB
            if ($reserva->create_reserva(
                $id_usuario, 
                $id_espacio, 
                $fecha_inicio, 
                $fecha_fin, 
                $total_pagado, 
                $codigo_qr_path_for_db, // Save QR path
                isset($data->estado_reserva) ? $data->estado_reserva : 'pendiente'
            )) {
                // 5. Enviar correo electrónico
                $mail = new PHPMailer(true);
                try {
                    //Server settings
                    $mail->SMTPDebug = SMTP::DEBUG_OFF; // Enable verbose debug output (DEBUG_SERVER for detailed)
                    $mail->isSMTP(); // Send using SMTP
                    $mail->Host       = 'sandbox.smtp.mailtrap.io'; // Set the SMTP server to send through (Use Mailtrap for development)
                    $mail->SMTPAuth   = true; // Enable SMTP authentication
                    $mail->Username   = '44ceeff993188e'; // SMTP username
                    $mail->Password   = 'cad49df98f708c'; // SMTP password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
                    $mail->Port       = 587; // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

                    //Recipients
                    $mail->setFrom('noreply@tuapp.com', 'Tu App de Reservas');
                    // Fetch user email from database using id_usuario for actual user data
                    // For now, let's use the email from session or dummy
                    $user_email = $_SESSION['user_email'] ?? 'test@example.com'; 
                    $user_name = $_SESSION['user_name'] ?? 'Usuario';
                    $mail->addAddress($user_email, $user_name); // Add a recipient

                    // Attach QR code
                    $mail->addAttachment($qr_filepath, $qr_filename);

                    // Content
                    $mail->isHTML(true); // Set email format to HTML
                    $mail->Subject = 'Confirmacion de Reserva - ' . $espacio_info['nombre'];
                    $mail->Body    = '
                        <h1>Confirmación de tu reserva</h1>
                        <p>Hola ' . $user_name . ',</p>
                        <p>Tu reserva para el espacio <strong>' . $espacio_info['nombre'] . '</strong> ha sido confirmada.</p>
                        <p><strong>Fechas:</strong> ' . date('d/m/Y H:i', strtotime($fecha_inicio)) . ' - ' . date('d/m/Y H:i', strtotime($fecha_fin)) . '</p>
                        <p><strong>Total pagado:</strong> $' . number_format($total_pagado, 2) . '</p>
                        <p>Por favor, presenta el código QR adjunto para validar tu ingreso.</p>
                        <p>¡Gracias por tu reserva!</p>
                    ';
                    $mail->AltBody = 'Tu reserva para ' . $espacio_info['nombre'] . ' ha sido confirmada. Fechas: ' . $fecha_inicio . ' - ' . $fecha_fin . '. Total: $' . $total_pagado . '. Presenta el código QR adjunto para validar tu ingreso.';

                    $mail->send();
                    http_response_code(201);
                    echo json_encode(['message' => 'Reserva creada exitosamente. Se ha enviado un correo de confirmación.', 'qr_code_path' => $codigo_qr_path_for_db]);
                } catch (Exception $e) {
                    error_log("Error al enviar correo de confirmación: {$mail->ErrorInfo}");
                    http_response_code(500);
                    echo json_encode(['message' => 'Reserva creada, pero no se pudo enviar el correo de confirmación.', 'qr_code_path' => $codigo_qr_path_for_db]);
                }
            } else {
                http_response_code(500);
                echo json_encode(['message' => 'No se pudo crear la reserva.']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'Datos incompletos. Faltan id_usuario, id_espacio, fecha_inicio o fecha_fin.']);
        }
        break;

    case 'PUT':
        require_auth(); // Any logged-in user can attempt to update their reservation. Admin can update any.
        $data = json_decode(file_get_contents("php://input"));
        
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['message' => 'No se proporcionó un ID de reserva para actualizar.']);
            break;
        }

        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if ($id === false) {
            http_response_code(400);
            echo json_encode(['message' => 'ID de reserva no válido.']);
            break;
        }

        // Fetch the reservation to check ownership/permissions
        $existing_reserva = $reserva->get_reserva_by_id($id);
        if (!$existing_reserva) {
            http_response_code(404);
            echo json_encode(['message' => 'Reserva no encontrada.']);
            break;
        }

        // Only owner or admin can update
        if ((int)$_SESSION['user_id'] !== (int)$existing_reserva['id_usuario'] && !has_role_id(2)) {
            http_response_code(403);
            echo json_encode(['message' => 'No tiene permisos para actualizar esta reserva.']);
            break;
        }

        // Validate required fields for update
        if ( 
            empty($data->id_usuario) ||
            empty($data->id_espacio) ||
            empty($data->fecha_inicio) ||
            empty($data->fecha_fin) ||
            empty($data->estado_reserva) ||
            empty($data->total_pagado)
        ) {
            http_response_code(400);
            echo json_encode(['message' => 'Datos incompletos para actualizar la reserva.']);
            break;
        }
        
        if ($reserva->update_reserva(
            $id, 
            $data->id_usuario, 
            $data->id_espacio, 
            $data->fecha_inicio, 
            $data->fecha_fin, 
            $data->estado_reserva, 
            isset($data->codigo_qr) ? $data->codigo_qr : null, 
            $data->total_pagado
        )) {
            http_response_code(200);
            echo json_encode(['message' => 'Reserva actualizada exitosamente.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'No se pudo actualizar la reserva.']);
        }
        break;

    case 'DELETE':
        require_auth(); // Any logged-in user can attempt to delete their reservation. Admin can delete any.
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['message' => 'No se proporcionó un ID de reserva para eliminar.']);
            break;
        }

        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if ($id === false) {
            http_response_code(400);
            echo json_encode(['message' => 'ID de reserva no válido.']);
            break;
        }

        // Fetch the reservation to check ownership/permissions
        $existing_reserva = $reserva->get_reserva_by_id($id);
        if (!$existing_reserva) {
            http_response_code(404);
            echo json_encode(['message' => 'Reserva no encontrada.']);
            break;
        }

        // Only owner or admin can delete
        if ((int)$_SESSION['user_id'] !== (int)$existing_reserva['id_usuario'] && !has_role_id(2)) {
            http_response_code(403);
            echo json_encode(['message' => 'No tiene permisos para eliminar esta reserva.']);
            break;
        }

        if ($reserva->delete_reserva($id)) {
            http_response_code(200);
            echo json_encode(['message' => 'Reserva eliminada exitosamente.']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'No se pudo eliminar la reserva.']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['message' => 'Método no permitido.']);
        break;
}
