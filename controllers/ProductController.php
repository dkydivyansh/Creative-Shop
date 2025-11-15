<?php
register_shutdown_function(function() { global $pdo; $pdo = null; });
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';

class ProductController {

    // Show all products when visiting /category
    public function showAllCategories() {
        $pdo = DBConnection::get();

        if (!isset($pdo)) {
            error_log("FATAL: Database connection not found in ProductController.");
            echo "A critical error occurred. Please contact support.";
            return;
        }

        $categoryModel = new Category($pdo);
        $productModel = new Product($pdo);

        $categories = $categoryModel->getAllCategories();
        $products = $productModel->getProductsByCategory('all');

        // Fake category for All Products page
        $currentCategory = [
            'name' => 'All Products',
            'description' => 'Browse our full collection of products.',
        ];

        $extra_styles = ['/public/css/category.css'];
        $extra_scripts = ['/public/js/category.js'];

        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/products/category.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }

    // Modified: Show products by category or all categories if none is provided
    public function showCategory($categoryName = null) {
        $pdo = DBConnection::get();

        if (!isset($pdo)) {
            error_log("FATAL: Database connection not found in ProductController.");
            echo "A critical error occurred. Please contact support.";
            return;
        }

        $categoryModel = new Category($pdo);
        $productModel = new Product($pdo);

        // If no category provided, show all
        if ($categoryName === null || trim($categoryName) === '') {
            return $this->showAllCategories();
        }

        // Fetch current category
        $currentCategory = $categoryModel->getCategoryByName($categoryName);

        if (!$currentCategory) {
            http_response_code(404);
            require __DIR__ . '/../views/404.php';
            return;
        }

        // Fetch products for this category
        $categories = $categoryModel->getAllCategories();
        $products = $productModel->getProductsByCategory($categoryName);

        $extra_styles = ['/public/css/category.css'];
        $extra_scripts = ['/public/js/category.js'];

        // Render category page
        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/products/category.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }

    public function showProduct($sku) {
        // TODO: This also needs to be implemented using the same pattern
        // $pdo = DBConnection::get();
        // $productModel = new Product($pdo);
        // ... fetch and display product
        echo "Displaying product with SKU: " . htmlspecialchars($sku);
    }
}
