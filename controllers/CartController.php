<?php
register_shutdown_function(function() { global $pdo; $pdo = null; });
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../includes/session.php';

class CartController {

    public function view() {
        
        require_login();

        $pdo = DBConnection::get();
        $cartModel = new Cart($pdo);
        $categoryModel = new Category($pdo);
        
        $cartItems = $cartModel->getCartItems($_SESSION['user_id']);
        $categories = $categoryModel->getAllCategories();

        $subtotal = 0;
        $totalItems = 0;
        
        if (!empty($cartItems)) {
            foreach ($cartItems as &$item) {
                $originalQuantity = $item['quantity'];

                // --- Step 1: Stock Availability Check ---
                $isOutOfStock = false;
                if (in_array($item['type'], ['key', 'file'])) {
                    if (!is_null($item['stock']) && (int)$item['stock'] === 0) {
                        $isOutOfStock = true;
                    }
                } else { // For 'physical' and 'key_lim'
                    if (is_null($item['stock']) || (int)$item['stock'] <= 0) {
                        $isOutOfStock = true;
                    }
                }

                if ($isOutOfStock) {
                    $item['quantity'] = 0;
                }

                // --- Step 2: Quantity Validation ---
                $effectiveLimit = PHP_INT_MAX;
                if (in_array($item['type'], ['physical', 'key_lim'])) {
                    $effectiveLimit = (int)($item['stock'] ?? 0);
                }

                $maxPerPerson = (int)($item['max_item_per_person'] ?? 1);
                if ($maxPerPerson > 0) {
                    $effectiveLimit = min($effectiveLimit, $maxPerPerson);
                }

                if ($item['quantity'] > $effectiveLimit) {
                    $item['quantity'] = $effectiveLimit;
                }

                // --- Update database if quantity changed ---
                if ($originalQuantity != $item['quantity']) {
                    if ($item['quantity'] > 0) {
                        $cartModel->updateCartItemQuantity($_SESSION['user_id'], $item['product_sku'], $item['quantity']);
                    } else {
                        // If quantity is 0, you might want to remove it from the cart
                        // For now, we'll just update it to 0 as per the logic
                        $cartModel->updateCartItemQuantity($_SESSION['user_id'], $item['product_sku'], 0);
                    }
                }

                // --- Step 3: Final Price Calculation ---
                if ($item['quantity'] > 0) {
                    $price = floatval($item['price']);
                    $discount = intval($item['discount']);
                    $discountedPrice = ($discount > 0) ? $price - ($price * $discount / 100) : $price;
                    
                    $subtotal += $discountedPrice * $item['quantity'];
                    $totalItems += $item['quantity'];
                }
            }
            // FIX: Unset the reference to prevent the "last item" bug
            unset($item);
        }

        $extra_styles = ['/public/css/cart.css'];
        $extra_scripts = ['/public/js/cart.js'];

        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/cart/index.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }
}
