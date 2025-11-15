<?php
class Order {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function createPendingOrder($userEmail, $customerName, $shippingAddress, $totalAmount, $cartItems) {
        try {
            $this->pdo->beginTransaction();

            $sqlOrder = "INSERT INTO orders (user_email, customer_name, shipping_address, total_amount, status, order_date) 
                         VALUES (:user_email, :customer_name, :shipping_address, :total_amount, 'pending', NOW())";
            
            $stmtOrder = $this->pdo->prepare($sqlOrder);
            $stmtOrder->execute([
                ':user_email' => $userEmail,
                ':customer_name' => $customerName,
                ':shipping_address' => $shippingAddress,
                ':total_amount' => $totalAmount
            ]);
            $orderId = $this->pdo->lastInsertId();

            $sqlItems = "INSERT INTO order_items (order_id, product_sku, product_type, price_per_item, quantity, discount, total) 
                         VALUES (:order_id, :product_sku, :product_type, :price_per_item, :quantity, :discount, :total)";
            $stmtItems = $this->pdo->prepare($sqlItems);

            foreach ($cartItems as $item) {
                $price = floatval($item['price']);
                $discount = intval($item['discount']);
                $discountedPrice = ($discount > 0) ? $price - ($price * $discount / 100) : $price;
                $lineTotal = $discountedPrice * $item['quantity'];

                $stmtItems->execute([
                    ':order_id' => $orderId,
                    ':product_sku' => $item['product_sku'],
                    ':product_type' => $item['type'],
                    ':price_per_item' => $discountedPrice,
                    ':quantity' => $item['quantity'],
                    ':discount' => $discount,
                    ':total' => $lineTotal
                ]);
            }

            $this->pdo->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Create Pending Order Error: " . $e->getMessage());
            return false;
        }
    }

    public function findOrderById($orderId) {
        $stmt = $this->pdo->prepare("SELECT o.*, u.auth_user_id FROM orders o JOIN users u ON o.user_email = u.email WHERE o.id = :order_id");
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getOrderItems($orderId) {
        // MODIFIED: Added product_type to the selection
        $stmt = $this->pdo->prepare("SELECT product_sku, quantity, product_type FROM order_items WHERE order_id = :order_id");
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function updateOrderStatus($orderId, $status) {
    // Optional: Validate status against the allowed ENUM values first
    $allowed_statuses = ['pending', 'paid', 'delivered', 'shipped', 'cancelled'];
    if (!in_array($status, $allowed_statuses)) {
        error_log("Invalid status value provided: " . $status);
        return false;
    }

    try {
        $sql = "UPDATE orders SET status = :status WHERE id = :order_id";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            ':status' => $status, 
            ':order_id' => $orderId
        ]);
    } catch (PDOException $e) {
        // Log the actual database error for debugging
        error_log("Update Order Status Error: " . $e->getMessage());
        return false;
    }
}
     public function getUserOrdersWithItems($authUserId) {
        try {
            // First, get all orders for the user
            $orderSql = "
                SELECT o.* FROM orders o
                JOIN users u ON o.user_email = u.email
                WHERE u.auth_user_id = :auth_user_id
                ORDER BY o.order_date DESC
            ";
            $orderStmt = $this->pdo->prepare($orderSql);
            // FIX: Corrected the typo in the parameter name
            $orderStmt->execute([':auth_user_id' => $authUserId]);
            $orders = $orderStmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($orders)) {
                return [];
            }

            // Get all order IDs to fetch items in a single query
            $orderIds = array_column($orders, 'id');
            $placeholders = implode(',', array_fill(0, count($orderIds), '?'));

            // Now, fetch all items for those orders
            $itemSql = "
                SELECT oi.*, p.name, p.image 
                FROM order_items oi
                JOIN products p ON oi.product_sku = p.sku
                WHERE oi.order_id IN ($placeholders)
            ";
            $itemStmt = $this->pdo->prepare($itemSql);
            $itemStmt->execute($orderIds);
            $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

            // Group items by their order_id
            $itemsByOrderId = [];
            foreach ($items as $item) {
                $itemsByOrderId[$item['order_id']][] = $item;
            }

            // Attach the items to their corresponding orders
            foreach ($orders as &$order) {
                $order['items'] = $itemsByOrderId[$order['id']] ?? [];
            }

            return $orders;
        } catch (PDOException $e) {
            error_log("Get User Orders Error: " . $e->getMessage());
            return [];
        }
    }
}