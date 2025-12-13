<?php

// Ensure session is started, although it should be started in index.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Checks if a user is currently logged in.
 * @return bool True if a user is logged in, false otherwise.
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Gets the current logged-in user's role ID.
 * @return int|null The user's id_rol if logged in, null otherwise.
 */
function get_user_role_id() {
    return is_logged_in() ? $_SESSION['user_id_rol'] : null;
}

/**
 * Checks if the logged-in user has the specified role ID.
 * @param int $required_role_id The role ID to check against.
 * @return bool True if the user has the required role, false otherwise.
 */
function has_role_id($required_role_id) {
    return get_user_role_id() === $required_role_id;
}

/**
 * Requires the user to be logged in and optionally have a specific role.
 * If not authorized, stops script execution and sends a 403 Forbidden response.
 * @param int|null $required_role_id Optional. The role ID required. If null, only checks if logged in.
 */
function require_auth($required_role_id = null) {
    if (!is_logged_in()) {
        http_response_code(401); // Unauthorized
        echo json_encode(['message' => 'Acceso no autorizado. Inicie sesión para continuar.']);
        exit();
    }

    if ($required_role_id !== null && !has_role_id($required_role_id)) {
        http_response_code(403); // Forbidden
        echo json_encode(['message' => 'No tiene los permisos necesarios para realizar esta acción.']);
        exit();
    }
}

/**
 * Provides a logout mechanism.
 * Destroys the current session.
 */
function logout_user() {
    $_SESSION = array(); // Unset all of the session variables
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy(); // Destroy the session
}

?>