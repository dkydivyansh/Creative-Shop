<?php
register_shutdown_function(function() { global $pdo; $pdo = null; });
// Include the models needed for this controller
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Product.php';

class HomeController {

    /**
     * Handles the logic for displaying the home page.
     */
    public function index() {
        $pdo = DBConnection::get();

        if (!isset($pdo)) {
            error_log("FATAL: Database connection not found in HomeController.");
            echo "A critical error occurred. Please contact support.";
            return;
        }
        
        // 1. Fetch all categories for the header and filter tabs
        $categoryModel = new Category($pdo);
        $categories = $categoryModel->getAllCategories();

        // 2. Fetch products, filtered by a category if one is selected
        $productModel = new Product($pdo);
        $selectedCategoryName = $_GET['category'] ?? 'all';
        $products = $productModel->getProductsByCategory($selectedCategoryName);

        // 3. Load the view files. The variables defined above will be available
        //    within these included view files.
        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/home.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }
}
