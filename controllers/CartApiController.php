<?php
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../includes/session.php';

class CartApiController {

    public function __construct() {
        register_shutdown_function(function() { $pdo = DBConnection::get(); $pdo = null; });
        // All methods in this controller require a valid, active session.
        if (!validate_session()) {
            header('Content-Type: application/json');
            // Use a 401 Unauthorized status code for session failures
            http_response_code(401); 
            echo json_encode(['success' => false, 'message' => 'Your session has expired. Please log in again.']);
            exit;
        }
    }

    public function updateQuantity() {
        //require_login();
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $sku = $input['sku'] ?? null;
        $quantity = $input['quantity'] ?? null;

        if (!$sku || !is_numeric($quantity) || $quantity < 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid input.']);
            return;
        }

        $pdo = DBConnection::get();
        $productModel = new Product($pdo);
        $product = $productModel->getProductBySku($sku);

        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found.']);
            return;
        }

        // Validate the requested quantity against the product's stock and limits
        $effectiveLimit = PHP_INT_MAX;
        if (in_array($product['type'], ['physical', 'key_lim'])) {
            $effectiveLimit = (int)($product['stock'] ?? 0);
        }
        $maxPerPerson = (int)($product['max_item_per_person'] ?? 1);
        if ($maxPerPerson > 0) {
            $effectiveLimit = min($effectiveLimit, $maxPerPerson);
        }

        if ($quantity > $effectiveLimit) {
            echo json_encode(['success' => false, 'message' => 'Quantity exceeds the maximum allowed.']);
            return;
        }

        $cartModel = new Cart($pdo);
        $success = $cartModel->updateCartItemQuantity($_SESSION['user_id'], $sku, $quantity);

        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update cart.']);
        }
    }

    public function deleteItem() {
        //require_login();
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $sku = $input['sku'] ?? null;

        if (!$sku) {
            echo json_encode(['success' => false, 'message' => 'Invalid input.']);
            return;
        }

        $pdo = DBConnection::get();
        $cartModel = new Cart($pdo);
        $success = $cartModel->deleteCartItem($_SESSION['user_id'], $sku);

        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete item from cart.']);
        }
    }
    public function addItem() {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $sku = $input['sku'] ?? null;
        $quantity = $input['quantity'] ?? 1;

        if (!$sku || !is_numeric($quantity) || $quantity <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid input provided.']);
            return;
        }

        $pdo = DBConnection::get();
        $productModel = new Product($pdo);
        $product = $productModel->getProductBySku($sku);

        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found.']);
            return;
        }

        // --- Validation Logic (crucial for security and data integrity) ---
        $effectiveLimit = PHP_INT_MAX;
        if (in_array($product['type'], ['physical', 'key_lim'])) {
            $effectiveLimit = (int)($product['stock'] ?? 0);
        }
        $maxPerPerson = (int)($product['max_item_per_person'] ?? 1);
        if ($maxPerPerson > 0) {
            $effectiveLimit = min($effectiveLimit, $maxPerPerson);
        }

        if ($quantity > $effectiveLimit) {
            echo json_encode(['success' => false, 'message' => 'Quantity exceeds the maximum allowed for this item.']);
            return;
        }
        // --- End Validation ---

        $cartModel = new Cart($pdo);
        
        // Prevent adding an item that already exists via this endpoint
        if ($cartModel->getCartItemBySku($_SESSION['user_id'], $sku)) {
            echo json_encode(['success' => false, 'message' => 'This item is already in your cart.']);
            return;
        }

        $success = $cartModel->addCartItem($_SESSION['user_id'], $sku, $quantity);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Item added to cart.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Could not add item to cart. Please try again.']);
        }
    }
}
