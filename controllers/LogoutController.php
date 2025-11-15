<?php
register_shutdown_function(function() { global $pdo; $pdo = null; });
require_once __DIR__ . '/../includes/session.php';

class LogoutController {

    public function logout() {
        // Start the session to access session variables
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Check if the user is logged in before trying to log out
        if (isset($_SESSION['user_id']) && isset($_SESSION['session_token'])) {
            $userId = $_SESSION['user_id'];
            $sessionToken = $_SESSION['session_token'];

            // First, destroy the local PHP session immediately
            session_destroy();

            // Next, attempt to terminate the session on the auth server
            $response = auth_server_logout($userId, $sessionToken);

            // If the remote logout fails, log the error for administrative review.
            // We don't show an error to the user because they are effectively logged out
            // from this site anyway.
            if (!$response['success']) {
                error_log("Failed to terminate session on auth server for user ID: " . $userId . ". Reason: " . ($response['message'] ?? 'Unknown error'));
            }
        }

        // Redirect the user to the homepage after logout
        header('Location: /');
        exit;
    }
}
