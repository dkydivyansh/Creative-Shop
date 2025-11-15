<?php

use Razorpay\Api\Api;
// --- SETUP ---
// 1. Load the Composer autoloader to access the Razorpay SDK
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/helpers/CheckoutHelper.php';

class PaymentController
{

    private $countryTaxRates;
    const PAYMENT_SESSION_MINUTES = 10; // Increased session time for Razorpay

    public function __construct($taxRates)
    {
        $this->countryTaxRates = $taxRates;
    }
   private function createRazorpayOrder($order)
    {
        try {
            $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

            $razorpayOrder = $api->order->create([
                'receipt'         => (string)$order['id'], // FIX: Cast the order ID to a string
                'amount'          => round($order['total_amount'] * 100), // amount in the smallest currency unit
                'currency'        => 'INR',
                'payment_capture' => 1
            ]);

            return $razorpayOrder;
        } catch (Exception $e) {
            error_log("Razorpay Order Creation Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Initiates the order by performing rigorous final validation before reserving stock.
     */
    public function initiate($sku = null)
    {
        require_login();
        $pdo = DBConnection::get();
        $authUserId = $_SESSION['user_id'];

        $itemsToProcess = [];
        $isSingleProductBuy = false;

        if ($sku !== null) {
            $isSingleProductBuy = true;
            $productModel = new Product($pdo);
            $product = $productModel->getProductBySku($sku);
            if (!$product) {
                $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'The selected product could not be found.'];
                header('Location: /');
                exit;
            }
            $itemsToProcess = [
                [
                    'product_sku' => $product['sku'],
                    'quantity' => 1,
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'discount' => $product['discount'],
                    'image' => $product['image'],
                    'stock' => $product['stock'],
                    'type' => $product['type'],
                    'max_item_per_person' => $product['max_item_per_person']
                ]
            ];
        } else {
            $isSingleProductBuy = false;
            $cartModel = new Cart($pdo);
            $itemsToProcess = $cartModel->getCartItems($authUserId);
        }

        if (empty($itemsToProcess)) {
            header('Location: /cart');
            exit;
        }

        $userModel = new User($pdo);
        $user = $userModel->findByAuthId($authUserId);
        $userCountry = trim($user['country']);

        $validItemsForOrder = [];
        $subtotal = 0;

        foreach ($itemsToProcess as $item) {
            $isOutOfStock = false;
            if (in_array($item['type'], ['key', 'file'])) {
                if (!is_null($item['stock']) && (int) $item['stock'] === 0)
                    $isOutOfStock = true;
            } else {
                if (is_null($item['stock']) || (int) $item['stock'] <= 0)
                    $isOutOfStock = true;
            }
            if ($isOutOfStock) {
                $_SESSION['flash_message'] = ['type' => 'error', 'message' => "Sorry, '{$item['name']}' just went out of stock."];
                header('Location: /cart');
                exit;
            }
            $validItemsForOrder[] = $item;
            $price = floatval($item['price']);
            $discount = intval($item['discount']);
            $discountedPrice = ($discount > 0) ? $price - ($price * $discount / 100) : $price;
            $subtotal += $discountedPrice * $item['quantity'];
        }

        $priceBreakdown = CheckoutHelper::calculateTotals($subtotal, $userCountry, $this->countryTaxRates);
        $totalAmount = $priceBreakdown['total_amount'];

        $productModel = new Product($pdo);
        if (!$productModel->reserveStockForItems($validItemsForOrder)) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'An item sold out just before you paid. Please review your cart.'];
            header('Location: /cart');
            exit;
        }

        $orderModel = new Order($pdo);
        $shippingAddress = "{$user['name']}\n{$user['address1']}\n{$user['address2']}\n{$user['city']}, {$user['state']} - {$user['pincode']}\n{$user['country']}";
        $orderId = $orderModel->createPendingOrder($user['email'], $user['name'], $shippingAddress, $totalAmount, $validItemsForOrder);

        if (!$orderId) {
            $productModel->releaseReservedStock($validItemsForOrder);
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Could not create your order. Please try again.'];
            header('Location: /checkout');
            exit;
        }

        if (!$isSingleProductBuy) {
            $cartModel = new Cart($pdo);
            $cartModel->clearCart($authUserId);
        }

        header("Location: /payment/{$orderId}");
        exit;
    }

    public function show($orderId)
    {
        require_login();
        $pdo = DBConnection::get();
        $orderModel = new Order($pdo);
        $order = $orderModel->findOrderById($orderId);

        if (!$order || $order['auth_user_id'] != $_SESSION['user_id']) {
            http_response_code(404);
            require __DIR__ . '/../views/404.php';
            return;
        }

        $orderTimestamp = strtotime($order['order_date']);
        $expirationTimestamp = $orderTimestamp + (self::PAYMENT_SESSION_MINUTES * 60);
        $isExpired = time() > $expirationTimestamp;

        if ($order['status'] !== 'pending' || $isExpired) {

            if ($isExpired && $order['status'] === 'pending') {
                $productModel = new Product($pdo);
                $productModel->releaseReservedStockForOrder($orderId, $orderModel);
                $orderModel->updateOrderStatus($orderId, 'cancelled');
            }

            $extra_styles = ['/public/css/payment.css'];
            require __DIR__ . '/../views/layouts/header.php';
            require __DIR__ . '/../views/payment/expired.php';
            require __DIR__ . '/../views/layouts/footer.php';
            return;
        }
        $razorpayOrder = $this->createRazorpayOrder($order);
        if (!$razorpayOrder) {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Could not connect to the payment gateway. Please try again later.'];
            header('Location: /cart');
            exit;
        }


        $userModel = new User($pdo);
        $user = $userModel->findByAuthId($_SESSION['user_id']);


        $razorpayConfig = [
            'key' => RAZORPAY_KEY_ID,
            'amount' => $razorpayOrder['amount'],
            'currency' => 'INR',
            'order_id' => $razorpayOrder['id'],
            'button_text' => 'Pay with Razorpay',
            'name' => 'Dkydivyansh.com',
            'description' => 'Order #' . $order['id'],
            'prefill' => [
                'name' => $user['name'],
                'email' => $user['email'],
                'contact' => $user['phone'] ?? ''
            ],
            'notes' => [
                'address' => $user['address1'],
                'merchant_order_id' => $order['id']
            ],
            'theme' => [
                'color' => '#000000'
            ]
        ];


        $categoryModel = new Category($pdo);
        $categories = $categoryModel->getAllCategories();
        $extra_styles = ['/public/css/payment.css'];
        $extra_scripts = [
            'https://checkout.razorpay.com/v1/checkout.js',
            '/public/js/payment.js'
        ];

        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/payment/index.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }
    public function markAsPaid()
    {
        require_login_api();
        header('Content-Type: application/json');
        $pdo = DBConnection::get();

        $input = json_decode(file_get_contents('php://input'), true);
        $orderId = $input['orderId'] ?? null;

        $orderModel = new Order($pdo);
        $order = $orderModel->findOrderById($orderId);

        if (!$order || $order['auth_user_id'] != $_SESSION['user_id'] || $order['status'] !== 'pending') {
            echo json_encode(['success' => false, 'message' => 'Invalid order for this action.']);
            exit;
        }

        if ($orderModel->updateOrderStatus($orderId, 'paid')) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Could not update order status.']);
        }
    }
    public function verify()
{
    require_login_api();
    header('Content-Type: application/json');
    $pdo = DBConnection::get();

    $input = json_decode(file_get_contents('php://input'), true);
    $orderId = $input['orderId'] ?? null;
    $razorpayPaymentId = $input['razorpay_payment_id'] ?? null;
    $razorpayOrderId = $input['razorpay_order_id'] ?? null;
    $razorpaySignature = $input['razorpay_signature'] ?? null;

    $orderModel = new Order($pdo);
    $productModel = new Product($pdo);
    $paymentModel = new Payment($pdo);

    $handleFailure = function ($reason, $logMessage = null) use ($orderId, $orderModel, $productModel, $paymentModel) {
        error_log("Payment verification failure for Order #$orderId: " . ($logMessage ?: $reason));
        $currentOrder = $orderModel->findOrderById($orderId);

        // Revert status to cancelled and release stock
        if ($currentOrder && ($currentOrder['status'] === 'paid' || $currentOrder['status'] === 'pending')) {
            $productModel->releaseReservedStockForOrder($orderId, $orderModel);
            $orderModel->updateOrderStatus($orderId, 'cancelled');
            $paymentModel->createPaymentLog($orderId, null, 'Razorpay', $currentOrder['total_amount'], 'INR', 'failed');
        }

        echo json_encode(['success' => false, 'message' => $reason, 'redirectUrl' => '/payment/failure']);
        exit;
    };

    $order = $orderModel->findOrderById($orderId);

    // FIX: Check if the order is in a valid state ('pending' or 'paid') to avoid race conditions.
    if (!$order || $order['auth_user_id'] != $_SESSION['user_id'] || !in_array($order['status'], ['pending', 'paid'])) {
        $handleFailure('This payment session is invalid or has expired.');
    }

    if (empty($razorpayPaymentId) || empty($razorpaySignature)) {
        $handleFailure('Payment failed. Please try again.');
    }

    try {
        $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

        $attributes = [
            'razorpay_order_id' => $razorpayOrderId,
            'razorpay_payment_id' => $razorpayPaymentId,
            'razorpay_signature' => $razorpaySignature
        ];

        $api->utility->verifyPaymentSignature($attributes);

        // Signature is valid, finalize the order and mark as shipped
        $productModel->commitSoldStockForOrder($orderId, $orderModel);
        $orderModel->updateOrderStatus($orderId, 'shipped');
        $paymentModel->createPaymentLog($orderId, $razorpayPaymentId, 'Razorpay', $order['total_amount'], 'INR', 'succeeded');
        
        echo json_encode(['success' => true, 'redirectUrl' => '/payment/success']);

    } catch (SignatureVerificationError $e) {
        $handleFailure('Your payment could not be verified.', $e->getMessage());
    } catch (Exception $e) {
        $handleFailure('An unexpected error occurred.', $e->getMessage());
    }
}
    // ... (success and failure functions remain the same) ...
    public function success()
    {
        $pdo = DBConnection::get();
        $categoryModel = new Category($pdo);
        $categories = $categoryModel->getAllCategories();
        $extra_styles = ['/public/css/status-pages.css'];
        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/payment/success.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }

    public function failure()
    {
        $pdo = DBConnection::get();
        $categoryModel = new Category($pdo);
        $categories = $categoryModel->getAllCategories();
        $extra_styles = ['/public/css/status-pages.css'];
        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/payment/failure.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }

}
