<?php
class Payment {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function createPaymentLog($orderId, $transactionId, $gateway, $amount, $currency, $status, $gatewayResponse = null) {
        try {
            $sql = "INSERT INTO payments (order_id, transaction_id, payment_gateway, amount, currency, status, gateway_response, created_at)
                    VALUES (:order_id, :transaction_id, :gateway, :amount, :currency, :status, :response, NOW())";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':order_id' => $orderId,
                ':transaction_id' => $transactionId,
                ':gateway' => $gateway,
                ':amount' => $amount,
                ':currency' => $currency,
                ':status' => $status,
                ':response' => $gatewayResponse
            ]);
        } catch (Exception $e) {
            error_log("Create Payment Log Error: " . $e->getMessage());
            return false;
        }
    }
}