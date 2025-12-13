<?php

require_once __DIR__ . '/../models/usuario.class.php';
require_once __DIR__ . '/../models/espacio.class.php';
require_once __DIR__ . '/../models/reserva.class.php';
require_once __DIR__ . '/../utils/auth_helper.php';

$method = $_SERVER['REQUEST_METHOD'];

// Only allow GET requests for stats
if ($method !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['message' => 'Método no permitido.']);
    exit();
}

// All stats endpoints require admin role
require_auth(2); // Assuming admin role is ID 2

$usuario = new Usuario();
$espacio = new Espacio();
$reserva = new Reserva();

$stats = [];

try {
    // User Stats
    $stats['total_users'] = $usuario->get_total_users();
    $stats['total_admins'] = $usuario->get_total_users_by_role(2); // Assuming admin role is ID 2
    $stats['total_regular_users'] = $usuario->get_total_users_by_role(1); // Assuming regular user role is ID 1

    // Space Stats
    $stats['total_spaces'] = $espacio->get_total_spaces();
    $stats['total_active_spaces'] = $espacio->get_total_active_spaces();

    // Reservation Stats
    $stats['total_reservations'] = $reserva->get_total_reservations();
    $stats['pending_reservations'] = $reserva->get_reservations_count_by_status('pendiente');
    $stats['confirmed_reservations'] = $reserva->get_reservations_count_by_status('confirmada');
    $stats['cancelled_reservations'] = $reserva->get_reservations_count_by_status('cancelada');
    $stats['most_reserved_spaces'] = $reserva->get_most_reserved_spaces(5);

    http_response_code(200);
    echo json_encode($stats);

} catch (PDOException $e) {
    error_log("Error al obtener estadísticas: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['message' => 'Error interno del servidor al obtener estadísticas.']);
}
?>
