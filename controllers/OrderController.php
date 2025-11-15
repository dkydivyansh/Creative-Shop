<?php

require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../includes/session.php';

class OrderController {

    public function list() {
        require_login();

        global $pdo;
        $orderModel = new Order($pdo);
        $categoryModel = new Category($pdo);
        
        $orders = $orderModel->getUserOrdersWithItems($_SESSION['user_id']);
        $categories = $categoryModel->getAllCategories();

        $extra_styles = ['/public/css/orders.css'];

        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/orders/index.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }
}
