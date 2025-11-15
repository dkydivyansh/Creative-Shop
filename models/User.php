<?php
register_shutdown_function(function() { global $pdo; $pdo = null; });
require_once __DIR__ . '/../includes/usernotifications.php';
class User {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Finds a user by their external authentication ID.
     *
     * @param string $authUserId The user's ID from the auth server.
     * @return mixed The user record if found, otherwise false.
     */
    public function findByAuthId($authUserId) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE auth_user_id = :auth_user_id");
            $stmt->execute(['auth_user_id' => $authUserId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error finding user by auth ID: " . $e->getMessage());
            return false;
        }
    }
    public function getUserNameByAuthId($authUserId) {
        try {
            $stmt = $this->pdo->prepare("SELECT name FROM users WHERE auth_user_id = :auth_user_id");
            $stmt->execute(['auth_user_id' => $authUserId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['name'] ?? null;
        } catch (PDOException $e) {
            error_log("Error finding user by auth ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Creates a new user in the local database.
     *
     * @param array $userData An associative array of user data.
     * @return bool True on success, false on failure.
     */
    public function createUser(array $userData) {
        try {
            $sql = "INSERT INTO users (auth_user_id, email, name, phone_number, last_login) 
                    VALUES (:auth_user_id, :email, :name, :phone_number, NOW())";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':auth_user_id' => $userData['auth_user_id'],
                ':email' => $userData['email'],
                ':name' => $userData['name'],
                ':phone_number' => $userData['phone_number'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log("Error creating user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates the last_login timestamp for an existing user.
     *
     * @param string $authUserId The user's external auth ID.
     * @return bool True on success, false on failure.
     */
    public function updateLastLogin($authUserId) {
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET last_login = NOW() WHERE auth_user_id = :auth_user_id");
            return $stmt->execute([':auth_user_id' => $authUserId]);
        } catch (PDOException $e) {
            error_log("Error updating last login: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates a user's location information.
     *
     * @param string $authUserId The user's external auth ID.
     * @param string $country The user's country code.
     * @param string $city The user's city.
     * @param string $state The user's state/region.
     * @return bool True on success, false on failure.
     */
    public function updateUserLocation($authUserId, $country, $city, $state) {
        try {
            // The SQL query now includes the 'state' field
            $stmt = $this->pdo->prepare("UPDATE users SET country = :country, city = :city, state = :state WHERE auth_user_id = :auth_user_id");
            return $stmt->execute([
                ':country' => $country,
                ':city' => $city,
                ':state' => $state,
                ':auth_user_id' => $authUserId
            ]);
        } catch (PDOException $e) {
            error_log("Error updating user location: " . $e->getMessage());
            return false;
        }
    }
    public function updateUserProfile($authUserId, $name, $phoneNumber, $email = null) {
        try {
            // Get the user's current data before updating
            $currentUser = $this->findByAuthId($authUserId);
            if (!$currentUser) return false;

            $sql = "UPDATE users SET name = :name, phone_number = :phone_number";
            $params = [
                ':name' => $name,
                ':phone_number' => $phoneNumber,
                ':auth_user_id' => $authUserId
            ];

            if ($email !== null) {
                $sql .= ", email = :email";
                $params[':email'] = $email;
            }

            $sql .= " WHERE auth_user_id = :auth_user_id";
            
            $stmt = $this->pdo->prepare($sql);
            $success = $stmt->execute($params);

            if ($success) {
                usernotificationprofileupdate($authUserId);
                if ($email !== null) {
                    // Pass old and new user data to the notification function
                    $updatedUser = $this->findByAuthId($authUserId);
                    $updatedUser['old_email'] = $currentUser['email'];
                    notifyemailupdate($updatedUser);
                }
            }

            return $success;
        } catch (PDOException $e) {
            error_log("Error updating user profile: " . $e->getMessage());
            return false;
        }
    }

    public function updateUserAddress($authUserId, array $addressData) {
        try {
            $sql = "UPDATE users SET 
                        address1 = :address1, 
                        address2 = :address2, 
                        landmark = :landmark, 
                        city = :city, 
                        state = :state, 
                        country = :country, 
                        pincode = :pincode 
                    WHERE auth_user_id = :auth_user_id";
            $stmt = $this->pdo->prepare($sql);
            $addressData['auth_user_id'] = $authUserId;
            $success = $stmt->execute($addressData);

            if ($success) {
                usernotificationaddressupdate($authUserId);
            }

            return $success;
        } catch (PDOException $e) {
            error_log("Error updating user address: " . $e->getMessage());
            return false;
        }
    }
}
