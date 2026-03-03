<?php
require_once __DIR__ . '/../includes/session.php';

class LogoutController
{

    public function logout()
    {
        // Start the session to be able to destroy it
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Destroy the local session — no remote API call needed
        session_destroy();

        // Redirect the user to the homepage
        header('Location: /');
        exit;
    }
}
