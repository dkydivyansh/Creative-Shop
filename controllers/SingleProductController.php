<?php
register_shutdown_function(function() { global $pdo; $pdo = null; });
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Cart.php'; // Added Cart model
require_once __DIR__ . '/../includes/session.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

class SingleProductController {

    public function show($sku) {
        validate_session();
        $pdo = DBConnection::get();

        if (!isset($pdo)) {
            error_log("FATAL: Database connection not found in SingleProductController.");
            // Handle error gracefully in a real application
            die("A critical error occurred. Please contact support.");
        }

        $productModel = new Product($pdo);
        $categoryModel = new Category($pdo);
        $cartModel = new Cart($pdo); // Instantiate Cart model

        $product = $productModel->getProductBySku($sku);

        if (!$product) {
            http_response_code(404);
            require __DIR__ . '/../views/404.php';
            return;
        }

        // --- Data Processing ---
        $product['specs'] = !empty($product['specs']) ? json_decode($product['specs'], true) : [];
        $mainImageArray = !empty($product['image']) ? [trim($product['image'])] : [];
        $galleryImages = !empty($product['gallery']) ? array_filter(array_map('trim', explode(',', $product['gallery']))) : [];
        $product['images'] = array_values(array_unique(array_merge($mainImageArray, $galleryImages)));
        
        // --- Cart & Stock Logic ---
        $productInCart = null;
        $maxBuyableQuantity = 0;
        $isOutOfStock = false;

        // Check cart status only if the user is logged in
        if (isset($_SESSION['user_id'])) {
            $productInCart = $cartModel->getCartItemBySku($_SESSION['user_id'], $sku);
        }

        // Determine if the product is out of stock
        if (in_array($product['type'], ['key', 'file'])) {
            if (!is_null($product['stock']) && (int)$product['stock'] === 0) {
                $isOutOfStock = true;
            }
        } else { // For 'physical' and 'key_lim'
            if (is_null($product['stock']) || (int)$product['stock'] <= 0) {
                $isOutOfStock = true;
            }
        }

        // Calculate max buyable quantity if it's not out of stock
        if (!$isOutOfStock) {
            $effectiveLimit = PHP_INT_MAX;
            if (in_array($product['type'], ['physical', 'key_lim'])) {
                $effectiveLimit = (int)($product['stock'] ?? 0);
            }

            $maxPerPerson = (int)($product['max_item_per_person'] ?? 1);
            if ($maxPerPerson > 0) {
                $effectiveLimit = min($effectiveLimit, $maxPerPerson);
            }
            $maxBuyableQuantity = $effectiveLimit;
        }


        // --- Fetch Related Products ---
        $relatedProducts = [];
        if (!empty($product['category_id'])) {
            $relatedProducts = $productModel->getRelatedProducts($product['category_id'], $product['sku'], 3);
        }

        $categories = $categoryModel->getAllCategories();
        $extra_styles = ['/public/css/product-page.css'];
        $extra_scripts = ['/public/js/product-page.js'];

        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/products/single.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }
}
