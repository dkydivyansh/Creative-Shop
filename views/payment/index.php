<div class="payment-container">
    <div class="payment-card">
        <h1 class="payment-title">Final Step: Complete Your Payment</h1>
        <div id="payment-error" style="color: #dc3545; margin-top: 15px; font-weight: 500; display: none;"></div>
        <p class="order-id">Order ID: <strong>#<?php echo htmlspecialchars($order['id']); ?></strong></p>
        <div class="order-total">
            <span>Total Amount to Pay</span>
            <strong class="total-amount">₹<?php echo number_format($order['total_amount'], 2); ?></strong>
        </div>
        <p class="payment-instructions">Click the button below to pay securely with Razorpay. This is a test environment, and you will not be charged.</p>

        <div id="razorpay-button-container">
             <button id="rzp-button1" class="btn btn-primary razorpay-secure-button">Pay with Razorpay</button>
        </div>


        <div class="payment-timer">
            <p>Your session will expire in: <span id="timer">10:00</span></p>
        </div>
    </div>
</div>

<script>
    const razorpayConfig = <?php echo json_encode($razorpayConfig); ?>;
    const orderData = {
        orderId: "<?php echo htmlspecialchars($order['id']); ?>"
    };
    const paymentExpiryTimestamp = <?php echo $expirationTimestamp; ?>;
    const serverCurrentTimestamp = <?php echo time(); ?>;
</script>