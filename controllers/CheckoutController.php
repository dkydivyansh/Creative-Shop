<?php
register_shutdown_function(function() { global $pdo; $pdo = null; });
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Product.php'; // <-- Added Product model
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/helpers/CheckoutHelper.php';

class CheckoutController {
    
    private $countryTaxRates;

    public function __construct($taxRates) {
        $this->countryTaxRates = $taxRates;
    }

    public function view($sku = null) {
        require_login();
        $pdo = DBConnection::get();

        $itemsToProcess = [];
        $isSingleProductBuy = false;

        // --- Determine Checkout Mode ---
        if ($sku !== null) {
            // MODE 1: Single Product "Buy Now"
            $isSingleProductBuy = true;
            $productModel = new Product($pdo);
            $product = $productModel->getProductBySku($sku);

            if (!$product) {
                http_response_code(404);
                require __DIR__ . '/../views/404.php';
                return;
            }
            // Create a temporary "cart-like" array for the single product
            $itemsToProcess = [[
                'product_sku' => $product['sku'], 'quantity' => 1, 'name' => $product['name'],
                'price' => $product['price'], 'discount' => $product['discount'], 'image' => $product['image'],
                'stock' => $product['stock'], 'type' => $product['type'],
                'max_item_per_person' => $product['max_item_per_person']
            ]];
        } else {
            // MODE 2: Standard Cart Checkout
            $isSingleProductBuy = false;
            $cartModel = new Cart($pdo);
            $itemsToProcess = $cartModel->getCartItems($_SESSION['user_id']);
        }

        if (empty($itemsToProcess)) {
            header('Location: /cart');
            exit;
        }

        // --- Unified Validation Logic ---
        $validItemsForDisplay = [];
        $cartWasModifiedInDb = false;
        $subtotal = 0;
        
        foreach ($itemsToProcess as $item) {
            $originalQuantity = $item['quantity'];

            // Stock Availability Check
            $isOutOfStock = false;
            if (in_array($item['type'], ['key', 'file'])) {
                if (!is_null($item['stock']) && (int)$item['stock'] === 0) $isOutOfStock = true;
            } else {
                if (is_null($item['stock']) || (int)$item['stock'] <= 0) $isOutOfStock = true;
            }

            if ($isOutOfStock) {
                if ($isSingleProductBuy) {
                    // For "Buy Now", redirect back to product page with an error
                    $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'This product is currently out of stock.'];
                    header('Location: /' . $sku);
                    exit;
                }
                $item['quantity'] = 0; // For cart, set quantity to 0 for update
            }

            // Quantity Limit Validation
            $effectiveLimit = PHP_INT_MAX;
            if (in_array($item['type'], ['physical', 'key_lim'])) $effectiveLimit = (int)($item['stock'] ?? 0);
            $maxPerPerson = (int)($item['max_item_per_person'] ?? 1);
            if ($maxPerPerson > 0) $effectiveLimit = min($effectiveLimit, $maxPerPerson);
            if ($item['quantity'] > $effectiveLimit) $item['quantity'] = $effectiveLimit;

            // Check if cart needs updating (only for cart checkout)
            if (!$isSingleProductBuy && $originalQuantity != $item['quantity']) {
                $cartModel->updateCartItemQuantity($_SESSION['user_id'], $item['product_sku'], $item['quantity']);
                $cartWasModifiedInDb = true;
            }

            // Add to final list if still valid
            if ($item['quantity'] > 0) {
                $validItemsForDisplay[] = $item;
                $price = floatval($item['price']);
                $discount = intval($item['discount']);
                $discountedPrice = ($discount > 0) ? $price - ($price * $discount / 100) : $price;
                $subtotal += $discountedPrice * $item['quantity'];
            }
        }

        // --- Post-Validation Actions ---
        if (!$isSingleProductBuy && $cartWasModifiedInDb) {
            header('Location: /cart');
            exit;
        }
        if (empty($validItemsForDisplay)) {
            header('Location: ' . ($isSingleProductBuy ? '/product/' . $sku : '/cart'));
            exit;
        }
        $cartItems = $validItemsForDisplay; // Use the final validated list for the view

        // --- Fetch User and Address ---
        $userModel = new User($pdo);
        $user = $userModel->findByAuthId($_SESSION['user_id']);
        $isAddressComplete = true;
        $requiredAddressFields = ['address1', 'city', 'state', 'country', 'pincode'];
        foreach ($requiredAddressFields as $field) {
            if (empty($user[$field])) {
                $isAddressComplete = false;
                break;
            }
        }
        $userCountry = isset($user['country']) ? trim($user['country']) : '';
        $isCountrySupported = !empty($userCountry) && isset($this->countryTaxRates[$userCountry]);

        // --- Calculate Final Price ---
        $priceBreakdown = CheckoutHelper::calculateTotals($subtotal, $userCountry, $this->countryTaxRates);

        // --- Render View ---
        $categoryModel = new Category($pdo);
        $categories = $categoryModel->getAllCategories();
        $extra_styles = ['/public/css/checkout.css'];
        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/checkout/index.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }
}
