<?php
date_default_timezone_set('Asia/Kolkata');

/**
 * Main application entry point and router.
 */

// 1. BOOTSTRAP
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/session.php'; // Include the new session handler
require_once __DIR__ . '/config/tax.php';
spl_autoload_register(function ($class_name) {
    $file = __DIR__ . '/controllers/' . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// 2. ROUTING
$request_uri = $_GET['_url'] ?? '/';
$request_uri = rtrim($request_uri, '/');
if (empty($request_uri)) {
    $request_uri = '/';
}

// Define which routes require a user to be logged in
$protected_routes = [
    '/profile',
    '/orders',
    '/cart',
    '/checkout',
    '/auth/logout',
    '/payment'
];

// Define routes that logged-in users should NOT be able to access
$guest_only_routes = [
    '/login',
    '/register'
];
if (strpos($request_uri, '/payment') === 0 || in_array($request_uri, $protected_routes)) {
    require_login();
}
// --- Session Restrictions ---
if (in_array($request_uri, $protected_routes)) {
    require_login();
}

if (in_array($request_uri, $guest_only_routes)) {
    redirect_if_logged_in();
}


// Define the routes
$routes = [
    // Product & Catalog Routes
    '#^/$#' => ['HomeController', 'index'],
    '#^/category/([a-zA-Z0-9-]+)$#' => ['ProductController', 'showCategory'],
    '#^/category$#' => ['ProductController', 'showCategory'],
    '#^/search$#' => ['SearchController', 'search'],
    
    // Auth Routes
    '#^/login$#' => ['AuthController', 'login'],
    '#^/register$#' => ['AuthController', 'register'],
    '#^/auth/callback$#' => ['CallbackController', 'handle'],
    '#^/auth/logout$#' => ['LogoutController', 'logout'],
    
    // User Account Routes
    '#^/profile$#' => ['ProfileController', 'profile'],
 '#^/orders$#' => ['OrderController', 'list'],
    '#^/orders/([0-9]+)$#' => ['OrderController', 'showDetails'],
    '#^/order/([0-9]+)/invoice$#' => ['OrderController', 'showInvoice'],
    '#^/profile/update$#' => ['ProfileController', 'updateProfile'], // New Route
    '#^/profile/address/update$#' => ['ProfileController', 'updateAddress'], // New Route

        '#^/api/cart/add$#' => ['CartApiController', 'addItem'], // <-- ADDED THIS LINE

    // Cart & Checkout
    '#^/api/cart/quantity$#' => ['CartApiController', 'updateQuantity'],
    '#^/api/cart/delete$#' => ['CartApiController', 'deleteItem'],
    '#^/cart$#' => ['CartController', 'view'],
     '#^/checkout$#' => ['CheckoutController', 'view'],
     '#^/checkout/([a-zA-Z0-9_-]+)$#' => ['CheckoutController', 'view'],
     '#^/api/order/initiate$#' => ['PaymentController', 'initiate'],
    

    // Static Routes
    '#^/privacy-policy$#' => ['StaticPageController', 'privacy'],
    '#^/terms-and-conditions$#' => ['StaticPageController', 'terms'],
    '#^/about$#' => ['StaticPageController', 'about'],
    '#^/donate$#' => ['StaticPageController', 'donate'],

    // This is a catch-all for product SKUs. It should be last.
    '#^/([a-zA-Z0-9_-]+)$#' => ['SingleProductController', 'show'],

'#^/api/order/initiate/([a-zA-Z0-9_-]+)$#' => ['PaymentController', 'initiate'], // POST for "Buy Now"
'#^/api/order/initiate$#' => ['PaymentController', 'initiate'],                 // POST for Cart Checkout
'#^/payment/([0-9]+)$#' => ['PaymentController', 'show'],
'#^/api/payment/verify$#' => ['PaymentController', 'verify'],
'#^/payment/success$#' => ['PaymentController', 'success'],
'#^/payment/failure$#' => ['PaymentController', 'failure'],
];

$route_matched = false;
foreach ($routes as $pattern => $handler) {
    if (preg_match($pattern, $request_uri, $matches)) {
        array_shift($matches);
        $controllerName = $handler[0];
        $methodName = $handler[1];

        if (class_exists($controllerName)) {
            $controller = null;
            // <-- FIX: Pass tax rates to any controller that needs them -->
            if (in_array($controllerName, ['CheckoutController', 'ProfileController', 'PaymentController'])) {
                $controller = new $controllerName($countryTaxRates);
            } else {
                $controller = new $controllerName();
            }
            call_user_func_array([$controller, $methodName], $matches);
        } else {
            http_response_code(500);
            echo "Error: Controller class '$controllerName' not found.";
        }
        $route_matched = true;
        break;
    }
}

if (!$route_matched) {
    http_response_code(404);
    require_once __DIR__ . '/views/404.php';
}
