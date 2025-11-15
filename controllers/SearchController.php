<?php
register_shutdown_function(function() { global $pdo; $pdo = null; });
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';

class SearchController {
    public function search() {
        $pdo = DBConnection::get();

        if (!isset($pdo)) {
            error_log("FATAL: Database connection not found in SearchController.");
            echo "A critical error occurred. Please contact support.";
            return;
        }
        
        $query = $_GET['q'] ?? '';
        
        $productModel = new Product($pdo);
        $categoryModel = new Category($pdo);

        $products = [];
        if (!empty($query)) {
            $products = $productModel->searchProducts($query);
        }

        // For the header menu, we need the categories.
        $categories = $categoryModel->getAllCategories();

        $extra_styles = ['/public/css/category.css'];

        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/search.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }
}