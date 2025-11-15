<?php
register_shutdown_function(function() { global $pdo; $pdo = null; });
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../models/Category.php';
class ProfileController {
    private $countryTaxRates;

    public function __construct($taxRates = []) {
        $this->countryTaxRates = $taxRates;
    }

    public function profile() {
        require_login();

        $pdo = DBConnection::get();
        $userModel = new User($pdo);
        $user = $userModel->findByAuthId($_SESSION['user_id']);
        $categoryModel = new Category($pdo);
        $categories = $categoryModel->getAllCategories();
        

        if (!$user) {
            echo "Error: Could not find user profile.";
            return;
        }
        $supportedCountries = array_keys($this->countryTaxRates);
        sort($supportedCountries); // Sort them alphabetically

        $csc_api_key = defined('CSC_API_KEY') ? CSC_API_KEY : '';

        $extra_styles = ['/public/css/profile.css'];
        $extra_scripts = ['/public/js/profile.js'];

        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/profile/index.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }

    public function updateProfile() {
        // 1. Validate session first (important)
        if (!validate_session()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Your session has expired. Please log in again.']);
            return;
        }
        
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            return;
        }

        $name = $_POST['name'] ?? '';
        $phone = $_POST['phone_number'] ?? '';

        if (empty($name) || empty($phone)) {
            echo json_encode(['success' => false, 'message' => 'Name and phone number are required.']);
            return;
        }
        
        if (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
            echo json_encode(['success' => false, 'message' => 'Name can only contain letters and spaces.']);
            return;
        }

        if (!preg_match("/^\+?[0-9\s]{10,15}$/", $phone)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid phone number.']);
            return;
        }

    

        $pdo = DBConnection::get();
        $userModel = new User($pdo);
        $localUser = $userModel->findByAuthId($_SESSION['user_id']);

        // 2. Check for data changes
        $profileDataChanged = ($localUser['name'] !== $name || $localUser['phone_number'] !== $phone);

        // 3. Sync and check for email changes
        $profileResponse = auth_server_profile($_SESSION['user_id'], $_SESSION['session_token']);
        if (!$profileResponse['success']) {
            echo json_encode(['success' => false, 'message' => 'Could not verify your session. Please log in again.']);
            return;
        }
        $authEmail = $profileResponse['data']['user']['email'];
        $emailChanged = ($localUser['email'] !== $authEmail);
        $emailToUpdate = $emailChanged ? $authEmail : null;

        // 4. Determine response based on what changed
        if (!$profileDataChanged && !$emailChanged) {
            echo json_encode(['success' => true, 'message' => 'No changes were detected.']);
            return;
        }

        $success = $userModel->updateUserProfile($_SESSION['user_id'], $name, $phone, $emailToUpdate);

        if ($success) {
            $message = '';
            if ($profileDataChanged && $emailChanged) {
                $message = 'Profile and email updated successfully!';
            } elseif ($profileDataChanged) {
                $message = 'Profile updated successfully!';
            } else {
                $message = 'Email successfully synced from auth server!';
            }
            echo json_encode(['success' => true, 'message' => $message]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update profile. Please try again.']);
        }
    }

    public function updateAddress() {
        if (!validate_session()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Your session has expired. Please log in again.']);
            return;
        }
        
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            return;
        }

        $requiredFields = ['address1', 'city', 'state', 'country', 'pincode'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                echo json_encode(['success' => false, 'message' => 'Please fill in all required address fields.']);
                return;
            }
        }

        $submittedCountry = $_POST['country'];
        if (!isset($this->countryTaxRates[$submittedCountry])) {
            echo json_encode(['success' => false, 'message' => 'The selected country is not supported.']);
            return;
        }

        $pdo = DBConnection::get();
        $userModel = new User($pdo);
        $localUser = $userModel->findByAuthId($_SESSION['user_id']);

        $addressData = [
            'address1' => $_POST['address1'],
            'address2' => $_POST['address2'] ?? null,
            'landmark' => $_POST['landmark'] ?? null,
            'city' => $_POST['city'],
            'state' => $_POST['state'],
            'country' => $_POST['country'],
            'pincode' => $_POST['pincode'],
        ];
        
        $addressChanged = false;
        foreach ($addressData as $key => $value) {
            if ($localUser[$key] != $value) { // Use != to handle null vs empty string
                $addressChanged = true;
                break;
            }
        }

        if (!$addressChanged) {
            echo json_encode(['success' => true, 'message' => 'No changes were detected in your address.']);
            return;
        }

        $success = $userModel->updateUserAddress($_SESSION['user_id'], $addressData);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Address updated successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update address. Please try again.']);
        }
    }
}
