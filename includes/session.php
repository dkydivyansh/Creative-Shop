<?php
// /includes/session.php
// Database-backed session management for multi-server environments.
// The SSO provides a long-lived access_token; we track expiry locally in the DB.

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/DbSessionHandler.php';
require_once __DIR__ . '/../db.php';

// Register the database session handler BEFORE starting the session
$dbSessionHandler = new DbSessionHandler(DBConnection::get());
session_set_save_handler($dbSessionHandler, true);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Checks if a user is currently logged in with a valid (non-expired) session.
 * This is a purely local check — no remote API calls.
 *
 * @return bool True if the user has a valid local session.
 */
function is_user_logged_in()
{
    if (!isset($_SESSION['user_id'], $_SESSION['access_token'], $_SESSION['expires_at'])) {
        return false;
    }

    // If the access token has expired, destroy the session and return false
    if (time() > $_SESSION['expires_at']) {
        session_destroy();
        return false;
    }

    return true;
}

/**
 * Validates the current session. For backward compatibility with controllers
 * that call this function directly. Behaves the same as is_user_logged_in().
 *
 * @return bool True if the session is valid.
 */
function validate_session()
{
    return is_user_logged_in();
}

/**
 * Ensures a user has a valid session. If not, redirects to login.
 */
function require_login()
{
    if (!is_user_logged_in()) {
        header('Location: /login');
        exit;
    }
}

/**
 * Ensures a user has a valid session for API endpoints.
 * Returns a 401 JSON response instead of redirecting.
 */
function require_login_api()
{
    if (!is_user_logged_in()) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Your session has expired. Please log in again.']);
        exit;
    }
}

/**
 * If a user with a valid session tries to access a guest-only page (like login),
 * redirect them to the home page.
 */
function redirect_if_logged_in()
{
    if (is_user_logged_in()) {
        header('Location: /');
        exit;
    }
}

/**
 * Reads and clears a one-time flash message from the session.
 *
 * @return string|null The flash message, or null if none.
 */
function get_flash_message()
{
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}
