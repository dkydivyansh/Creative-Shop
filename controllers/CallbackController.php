<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/User.php';

class CallbackController {

    public function __construct() {
        register_shutdown_function(function() { $pdo = DBConnection::get(); $pdo = null; });
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function handle() {
        $pdo = DBConnection::get();

        // Handle error responses from the auth provider
        if (isset($_GET['error']) && isset($_GET['error_description'])) {
            $_SESSION['auth_error'] = $_GET['error_description'];
            header('Location: /login');
            exit;
        }

        $token = $_GET['token'] ?? null;

        if ($token) {
            $response = auth_toke_exchage($token);

            if ($response['success']) {
                $sessionData = $response['data'];
                
                // Store session data immediately
                $_SESSION['user_id'] = $sessionData['user_id'];
                $_SESSION['session_token'] = $sessionData['session_token'];
                $_SESSION['refresh_token'] = $sessionData['refresh_token'];
                $_SESSION['expires_at'] = time() + $sessionData['expires_in'];

                $userModel = new User($pdo);
                $localUser = $userModel->findByAuthId($sessionData['user_id']);

                if (!$localUser) {
                    // This is a first-time login
                    $this->handleNewUser($sessionData, $userModel);
                } else {
                    // This is a returning user
                    $userModel->updateLastLogin($sessionData['user_id']);
                }
                
                header('Location: /');
                exit;

            } else {
                $_SESSION['auth_error'] = $response['message'];
                header('Location: /login');
                exit;
            }
        }
        
        $_SESSION['auth_error'] = 'Invalid callback request. Token not found.';
        header('Location: /login');
        exit;
    }

    /**
     * Handles the logic for a user logging in for the first time.
     *
     * @param array $sessionData The session data from the auth server.
     * @param User $userModel The user model instance.
     */
    private function handleNewUser($sessionData, $userModel) {
        $profileResponse = auth_server_profile($sessionData['user_id'], $sessionData['session_token']);

        if ($profileResponse['success']) {
            $profileData = $profileResponse['data']['user'];
            
            // Create the new user in the local database
            $userModel->createUser([
                'auth_user_id' => $profileData['user_id'],
                'email' => $profileData['email'],
                'name' => $profileData['name'],
                'phone_number' => $profileData['phone_number'] ?? null
            ]);

            // Attempt to get and store the user's location
            $this->updateUserLocation($profileData['user_id'], $userModel);

        } else {
            // If fetching the profile fails, something is wrong.
            error_log("Failed to fetch profile for new user: " . $profileResponse['message']);
            session_destroy();
            $_SESSION['auth_error'] = 'Could not set up your account. Please try logging in again.';
            header('Location: /login');
            exit;
        }
    }

    /**
     * Fetches and updates the user's location based on their IP address.
     *
     * @param string $authUserId The user's external auth ID.
     * @param User $userModel The user model instance.
     */
    private function updateUserLocation($authUserId, $userModel) {
        $ip = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) ? '' : $_SERVER['REMOTE_ADDR'];
        
        // The URL now includes the 'regionName' field
        $locationJson = @file_get_contents("http://ip-api.com/php/{$ip}?fields=status,message,countryCode,regionName,city");
        
        if ($locationJson) {
            $locationData = unserialize($locationJson);
            if ($locationData && $locationData['status'] === 'success') {
                // Pass the state ('regionName') to the model
                $userModel->updateUserLocation(
                    $authUserId,
                    $locationData['countryCode'],
                    $locationData['city'],
                    $locationData['regionName'] 
                );
            }
        }
    }
}
