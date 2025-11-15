<?php
header('Content-Type: application/json');
register_shutdown_function(function() { global $pdo; $pdo = null; });
// It's crucial that db.php is included, as it should create the global $pdo object.
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php'; 
require_once __DIR__ . '/models/Product.php';
require_once __DIR__ . '/models/Category.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_products':
        handle_get_products();
        break;
    case 'get_category_data': // New action
        handle_get_category_data();
        break;
    default:
        echo json_encode(['error' => 'Unknown API action']);
        http_response_code(400);
        break;
}

function handle_get_products() {
    $pdo = DBConnection::get();
    if (!isset($pdo)) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection not available.']);
        return;
    }

    $productModel = new Product($pdo);
    $categoryName = $_GET['category'] ?? 'all';
    $products = $productModel->getProductsByCategory($categoryName);
    echo json_encode($products);
}

/**
 * Handles fetching both category details and its products.
 */
function handle_get_category_data() {
    $pdo = DBConnection::get();
    if (!isset($pdo)) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection not available.']);
        return;
    }

    $categoryName = $_GET['category'] ?? 'all';

    $categoryModel = new Category($pdo);
    $productModel = new Product($pdo);

    if ($categoryName === 'all') {
        $categoryDetails = [
            'name' => 'All Products',
            'description' => 'Browse our entire collection of products.'
        ];
    } else {
        $categoryDetails = $categoryModel->getCategoryByName($categoryName);
    }

    $products = $productModel->getProductsByCategory($categoryName);

    // Combine both results into a single response
    $response = [
        'details' => $categoryDetails,
        'products' => $products
    ];

    echo json_encode($response);
}
