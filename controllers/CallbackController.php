<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/User.php';

class CallbackController
{

    public function __construct()
    {
        register_shutdown_function(function () {
            $pdo = DBConnection::get();
            $pdo = null;
        });
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Handles the OAuth2 callback from the SSO server.
     * Expects ?code=...&state=... on success, or ?error=...&error_description=... on failure.
     */
    public function handle()
    {
        $pdo = DBConnection::get();

        // 1. Handle error responses from the SSO (e.g., user denied access)
        if (isset($_GET['error'])) {
            $_SESSION['auth_error'] = $_GET['error_description'] ?? $_GET['error'];
            header('Location: /login');
            exit;
        }

        // 2. Read the authorization code and state
        $code = $_GET['code'] ?? null;
        $state = $_GET['state'] ?? null;

        // 3. Verify CSRF state parameter
        $expectedState = $_SESSION['oauth_state'] ?? null;
        unset($_SESSION['oauth_state']); // Consume the state token

        if (!$code || !$state || $state !== $expectedState) {
            $_SESSION['auth_error'] = 'Invalid callback request. State verification failed.';
            header('Location: /login');
            exit;
        }

        // 4. Exchange the authorization code for an access token
        $tokenResponse = sso_exchange_code($code);

        if (!$tokenResponse['success']) {
            $_SESSION['auth_error'] = $tokenResponse['message'];
            header('Location: /login');
            exit;
        }

        $tokenData = $tokenResponse['data'];

        // 5. Store session data locally
        $_SESSION['user_id'] = $tokenData['user_id'];
        $_SESSION['access_token'] = $tokenData['access_token'];
        $_SESSION['expires_at'] = time() + $tokenData['expires_in'];
        $_SESSION['oauth_scope'] = $tokenData['scope'];

        // 6. Fetch user profile from SSO
        $userModel = new User($pdo);
        $localUser = $userModel->findByAuthId($tokenData['user_id']);

        if (!$localUser) {
            // First-time login — create local user from SSO profile
            $this->handleNewUser($tokenData, $userModel);
        } else {
            // Returning user — update last login
            $userModel->updateLastLogin($tokenData['user_id']);
        }

        header('Location: /');
        exit;
    }

    /**
     * Handles the logic for a user logging in for the first time.
     * Fetches their profile from the SSO and creates a local DB record.
     *
     * @param array $tokenData The token data from sso_exchange_code().
     * @param User $userModel The user model instance.
     */
    private function handleNewUser($tokenData, $userModel)
    {
        $profileResponse = sso_get_userinfo($tokenData['access_token']);

        if ($profileResponse['success']) {
            $profileData = $profileResponse['data']['user'];

            // SSO returns first_name + last_name; concatenate for the local `name` field
            $fullName = trim(($profileData['first_name'] ?? '') . ' ' . ($profileData['last_name'] ?? ''));

            // Create the new user in the local database
            $userModel->createUser([
                'auth_user_id' => $profileData['user_id'],
                'email' => $profileData['email'],
                'name' => $fullName,
                'phone_number' => $profileData['phone_number'] ?? null
            ]);

            // If SSO provides address data, save it too
            if (!empty($profileData['address'])) {
                $addr = $profileData['address'];
                $userModel->updateUserLocation(
                    $profileData['user_id'],
                    $addr['country'] ?? null,
                    $addr['city'] ?? null,
                    $addr['state'] ?? null
                );
            }

        } else {
            // If fetching the profile fails, clean up and show error
            error_log("Failed to fetch SSO profile for new user: " . $profileResponse['message']);
            session_destroy();
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['auth_error'] = 'Could not set up your account. Please try logging in again.';
            header('Location: /login');
            exit;
        }
    }
}
