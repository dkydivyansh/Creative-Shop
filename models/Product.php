<?php
register_shutdown_function(function () {
    global $pdo;
    $pdo = null;
});
class Product
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    public function getRelatedProducts($categoryId, $currentSku, $limit = 3)
    {
        try {
            $sql = "
                SELECT 
                    p.name, p.price, p.sku, p.image, p.discount, 
                    c.name as category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.category_id = :category_id AND p.sku != :current_sku
                ORDER BY RAND()
                LIMIT :limit
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'category_id' => $categoryId,
                'current_sku' => $currentSku,
                'limit' => $limit
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Related Products Error: " . $e->getMessage());
            return [];
        }
    }



    public function getProductBySku($sku)
    {
        try {
            $sql = "
                SELECT 
                    p.*, 
                    c.name as category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.sku = :sku
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['sku' => $sku]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Product by SKU Error: " . $e->getMessage());
            return false;
        }
    }

    public function searchProducts($query)
    {
        try {
            $sql = "
                SELECT 
                    p.name, 
                    p.description, 
                    p.price, 
                    p.sku, 
                    p.image, 
                    p.discount, 
                    p.type,
                    p.specs,
                    c.name as category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE LOWER(p.name) LIKE LOWER(?) 
                   OR LOWER(p.description) LIKE LOWER(?)
                   OR LOWER(p.sku) LIKE LOWER(?)
                   OR LOWER(COALESCE(p.specs, '')) LIKE LOWER(?)
                ORDER BY p.name ASC
            ";

            $stmt = $this->pdo->prepare($sql);
            $searchTerm = '%' . $query . '%';

            // Use positional parameters (?) - now 4 parameters
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $results;
        } catch (PDOException $e) {
            error_log("Search Error: " . $e->getMessage());
            return [];
        }
    }

    public function getProductsByCategory($categoryName)
    {
        try {
            $sql = "SELECT p.name, p.description, p.price, p.sku, p.image, p.discount, p.specs, c.name as category_name 
                    FROM products p 
                    LEFT JOIN categories c ON p.category_id = c.id";

            if ($categoryName !== 'all' && !empty($categoryName)) {
                $sql .= " WHERE c.name = ? ORDER BY p.name ASC";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$categoryName]);
            } else {
                $sql .= " ORDER BY p.name ASC";
                $stmt = $this->pdo->query($sql);
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Product Fetch Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Reserve stock for order items
     * Decreases stock, increases reserved_quantity
     */
    public function reserveStockForItems($items)
    {
        try {
            $this->pdo->beginTransaction();

            foreach ($items as $item) {
                if (!in_array($item['type'], ['physical', 'key_lim'])) {
                    continue; // Only reserve stock for limited items
                }

                // Handle NULL stock (unlimited)
                $sql = "
    UPDATE products
    SET stock = CASE WHEN stock IS NOT NULL THEN stock - :quantity1 ELSE stock END,
        reserved_quantity = reserved_quantity + :quantity2
    WHERE sku = :sku
      AND (stock IS NULL OR stock >= :quantity3)
";

                $stmt = $this->pdo->prepare($sql);
                error_log('PARAMS: ' . json_encode([
                    ':quantity' => (int)$item['quantity'],
                    ':sku'      => $item['product_sku'] ?? 'NULL'
                ]));

                $stmt->execute([
                    ':quantity1' => (int)$item['quantity'],
                    ':quantity2' => (int)$item['quantity'],
                    ':quantity3' => (int)$item['quantity'],
                    ':sku'       => $item['product_sku'] ?? ''
                ]);



                if ($stmt->rowCount() === 0) {
                    $this->pdo->rollBack();
                    return false; // Not enough stock
                }
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $sku = $item['product_sku'] ?? 'N/A';
            error_log("Reserve Stock Error on SKU {$sku}: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Release reserved stock if order is cancelled or failed
     * Restores stock, decreases reserved_quantity
     */
   public function releaseReservedStockForOrder($orderId, Order $orderModel) {
        $items = $orderModel->getOrderItems($orderId);
        if (empty($items)) return true;
    
        try {
            $this->pdo->beginTransaction();
    
            $sql = "
                UPDATE products
                SET stock = CASE WHEN stock IS NOT NULL THEN stock + :quantity1 ELSE stock END,
                    reserved_quantity = reserved_quantity - :quantity2
                WHERE sku = :sku
                  AND reserved_quantity >= :quantity3
            ";
            $stmt = $this->pdo->prepare($sql);
    
            foreach ($items as $item) {
                // Only release stock for items that have their stock managed
                if (!in_array($item['product_type'], ['physical', 'key_lim'])) {
                    continue;
                }
    
                $stmt->execute([
                    ':quantity1' => (int)$item['quantity'],
                    ':quantity2' => (int)$item['quantity'],
                    ':quantity3' => (int)$item['quantity'],
                    ':sku'       => $item['product_sku']
                ]);
            }
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Stock Release Error for Order #{$orderId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Commit stock reservation (when order is completed)
     * Just decreases reserved stock without restoring it
     */
   public function commitSoldStockForOrder($orderId, Order $orderModel) {
        $items = $orderModel->getOrderItems($orderId);
        if (empty($items)) return true;
    
        try {
            $this->pdo->beginTransaction();
    
            $sql = "
                UPDATE products
                SET reserved_quantity = reserved_quantity - :quantity1
                WHERE sku = :sku
                  AND reserved_quantity >= :quantity2
            ";
            $stmt = $this->pdo->prepare($sql);
    
            foreach ($items as $item) {
                // Only commit stock for items that have their stock managed
                if (!in_array($item['product_type'], ['physical', 'key_lim'])) {
                    continue;
                }
    
                $params = [
                    ':quantity1' => (int)$item['quantity'],
                    ':quantity2' => (int)$item['quantity'],
                    ':sku'       => $item['product_sku']
                ];

                $stmt->execute($params);

                if ($stmt->rowCount() === 0) {
                    $this->pdo->rollBack();
                    error_log("Stock Commit Failed for SKU {$item['product_sku']}: Not enough reserved quantity.");
                    return false;
                }
            }
    
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Stock Commit Error for Order #{$orderId}: " . $e->getMessage());
            return false;
        }
    }
}