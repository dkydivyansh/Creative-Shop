<?php

class Cart {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Gets all items in a user's cart with detailed product information.
     *
     * @param string $authUserId The user's external auth ID.
     * @return array An array of items in the cart.
     */
    public function getCartItems($authUserId) {
        try {
            $sql = "
                SELECT 
                    uc.product_sku,
                    uc.quantity,
                    p.name,
                    p.price,
                    p.discount,
                    p.image,
                    p.stock,
                    p.type,
                    p.max_item_per_person
                FROM user_cart uc
                JOIN products p ON uc.product_sku = p.sku
                JOIN users u ON uc.user_email = u.email
                WHERE u.auth_user_id = :auth_user_id
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':auth_user_id' => $authUserId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting cart items: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Updates the quantity of an item in the user's cart.
     *
     * @param string $authUserId The user's external auth ID.
     * @param string $productSku The SKU of the product to update.
     * @param int $newQuantity The new quantity for the item.
     * @return bool True on success, false on failure.
     */
    public function updateCartItemQuantity($authUserId, $productSku, $newQuantity) {
        try {
            $sql = "
                UPDATE user_cart uc
                JOIN users u ON uc.user_email = u.email
                SET uc.quantity = :quantity
                WHERE u.auth_user_id = :auth_user_id AND uc.product_sku = :product_sku
            ";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':quantity' => $newQuantity,
                ':auth_user_id' => $authUserId,
                ':product_sku' => $productSku
            ]);
        } catch (PDOException $e) {
            error_log("Error updating cart item quantity: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deletes an item from the user's cart.
     *
     * @param string $authUserId The user's external auth ID.
     * @param string $productSku The SKU of the product to delete.
     * @return bool True on success, false on failure.
     */
    public function deleteCartItem($authUserId, $productSku) {
        try {
            $sql = "
                DELETE uc FROM user_cart uc
                JOIN users u ON uc.user_email = u.email
                WHERE u.auth_user_id = :auth_user_id AND uc.product_sku = :product_sku
            ";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':auth_user_id' => $authUserId,
                ':product_sku' => $productSku
            ]);
        } catch (PDOException $e) {
            error_log("Error deleting cart item: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Adds a new item to a user's cart.
     *
     * @param string $authUserId The user's external auth ID.
     * @param string $productSku The SKU of the product to add.
     * @param int $quantity The quantity to add.
     * @return bool True on success, false on failure.
     */
    public function getCartItemCount($authUserId) {
        try {
            $sql = "
                SELECT SUM(uc.quantity) as total_items
                FROM user_cart uc
                JOIN users u ON uc.user_email = u.email
                WHERE u.auth_user_id = :auth_user_id
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':auth_user_id' => $authUserId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['total_items'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error getting cart item count: " . $e->getMessage());
            return 0;
        }
    }
    public function addCartItem($authUserId, $productSku, $quantity) {
        try {
            // First, get the user's email from their auth ID
            $userStmt = $this->pdo->prepare("SELECT email FROM users WHERE auth_user_id = :auth_user_id");
            $userStmt->execute([':auth_user_id' => $authUserId]);
            $user = $userStmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                error_log("Add to cart failed: User not found for auth_user_id " . $authUserId);
                return false;
            }
            $userEmail = $user['email'];

            // Now, insert the item into the cart
            $sql = "INSERT INTO user_cart (user_email, product_sku, quantity) VALUES (:user_email, :product_sku, :quantity)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':user_email' => $userEmail,
                ':product_sku' => $productSku,
                ':quantity' => $quantity
            ]);
        } catch (PDOException $e) {
            // Handle cases where the item might already be in the cart
            if ($e->getCode() == 23000) { // Integrity constraint violation (duplicate entry)
                return $this->updateCartItemQuantity($authUserId, $productSku, $quantity);
            }
            error_log("Error adding cart item: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieves a single item from the cart by its SKU for a specific user.
     *
     * @param string $authUserId The user's external auth ID.
     * @param string $productSku The SKU of the product to find.
     * @return mixed An associative array of the cart item or false if not found.
     */
    public function getCartItemBySku($authUserId, $productSku) {
        try {
            $sql = "
                SELECT uc.* FROM user_cart uc
                JOIN users u ON uc.user_email = u.email
                WHERE u.auth_user_id = :auth_user_id AND uc.product_sku = :product_sku
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':auth_user_id' => $authUserId,
                ':product_sku' => $productSku
            ]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting cart item by SKU: " . $e->getMessage());
            return false;
        }
    }

    public function clearCart($authUserId) {
        try {
            $sql = "
                DELETE uc FROM user_cart uc
                JOIN users u ON uc.user_email = u.email
                WHERE u.auth_user_id = :auth_user_id
            ";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':auth_user_id' => $authUserId]);
        } catch (PDOException $e) {
            error_log("Error clearing cart: " . $e->getMessage());
            return false;
        }
    }
}
