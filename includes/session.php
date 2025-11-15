<?php
// /includes/session.php

// Make sure the functions from the auth server are available
require_once __DIR__ . '/functions.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Validates the current user session against the authentication server.
 * It will first try to validate the session. If that fails, it will
 * attempt to refresh the session using the refresh token.
 *
 * @return bool True if the session is valid or was successfully refreshed, false otherwise.
 */
function validate_session() {
    // If there's no session data, the user is not logged in.
    if (!isset($_SESSION['user_id'], $_SESSION['session_token'], $_SESSION['refresh_token'])) {
        return false;
    }

    // 1. First, try to validate the current session token.
    $validationResponse = auth_server_validate($_SESSION['user_id'], $_SESSION['session_token']);
    if ($validationResponse['success'] && isset($validationResponse['data']['valid']) && $validationResponse['data']['valid']) {
        // The session is valid. Update the expiry time.
        $_SESSION['expires_at'] = time() + $validationResponse['data']['expires_in'];
        return true;
    }

    // 2. If validation fails, try to refresh the session.
    $refreshResponse = auth_server_refresh($_SESSION['user_id'], $_SESSION['session_token'], $_SESSION['refresh_token']);
    if ($refreshResponse['success']) {
        // The session was refreshed successfully. Update the session data.
        $newSessionData = $refreshResponse['data'];
        $_SESSION['session_token'] = $newSessionData['session_token'];
        $_SESSION['refresh_token'] = $newSessionData['refresh_token'];
        $_SESSION['expires_at'] = time() + $newSessionData['expires_in'];
        return true;
    }

    // 3. If both validation and refresh fail, the session is invalid.
    // Log the error, destroy the local session, and return false.
    //error_log("Session validation and refresh failed for user: " . ($_SESSION['user_id'] ?? 'Unknown'));
    session_destroy();
    return false;
}


/**
 * Checks if a user is logged in. This is the main function to use in controllers.
 * It performs a quick local check first and only validates with the server if needed.
 *
 * @return bool True if the user has a valid session, false otherwise.
 */
function is_user_logged_in() {
    if (!isset($_SESSION['user_id'], $_SESSION['expires_at'])) {
        return false;
    }

    // If the session has expired locally, attempt to validate/refresh it.
    if (time() > $_SESSION['expires_at']) {
        return validate_session();
    }

    // If the session has not expired locally, we can trust it for now.
    return true;
}

/**
 * Ensures a user has a valid, active session. If not, redirects to login.
 * This function performs the full session validation.
 */
function require_login() {
    if (!validate_session()) {
        header('Location: /login');
        exit;
    }
}

/**
 * If a user with a valid session tries to access a guest-only page (like login),
 * redirect them to the home page.
 */
function redirect_if_logged_in() {
    // FIX: Use the faster, local check. If a user has an invalid session,
    // this will return false, and they will be allowed to see the login page.
    if (validate_session()) {
        header('Location: /');
        exit;
    }
}
function require_login_api() {
    if (!validate_session()) {
        header('Content-Type: application/json');
        http_response_code(401); // Unauthorized
        echo json_encode(['success' => false, 'message' => 'Your session has expired. Please log in again.']);
        exit;
    }
}

function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}
