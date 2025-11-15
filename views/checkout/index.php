<?php
// This snippet should be at the very top of the file, before any HTML.
$flash_message = get_flash_message(); 
?>
<div class="checkout-container">
    <h1 class="checkout-title">Checkout</h1>
    <div class="checkout-layout">
        <!-- Left Side: Address and Items -->
        <div class="checkout-main">
            <!-- Shipping Address Section -->
            <div class="checkout-section">
                <h2 class="section-title">Shipping Address</h2>
                <div class="address-card">
                    <p><strong><?php echo htmlspecialchars($user['name'] ?? ''); ?></strong></p>
                    <p><?php echo htmlspecialchars($user['address1'] ?? ''); ?></p>
                    <?php if (!empty($user['address2'])): ?>
                        <p><?php echo htmlspecialchars($user['address2']); ?></p>
                    <?php endif; ?>
                    <p><?php echo htmlspecialchars($user['city'] ?? ''); ?>, <?php echo htmlspecialchars($user['state'] ?? ''); ?> - <?php echo htmlspecialchars($user['pincode'] ?? ''); ?></p>
                    <p><?php echo htmlspecialchars($user['country'] ?? ''); ?></p>
                    <p>Phone: <?php echo htmlspecialchars($user['phone_number'] ?? ''); ?></p>
                    <a href="/profile" class="edit-address-link">Edit Address</a>
                </div>
            </div>

            <!-- Order Items Section -->
            <div class="checkout-section">
                <h2 class="section-title">Review Items</h2>
                <div class="cart-items-container">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item">
                            <div class="item-image">
                                <img src="<?php echo htmlspecialchars($item['image'] ?? '/public/images/preholder3.gif'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            </div>
                            <div class="item-details">
                                <h3 class="item-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p class="item-quantity">Quantity: <?php echo $item['quantity']; ?></p>
                            </div>
                            <div class="item-price">
                                <?php
                                    $price = floatval($item['price']);
                                    $discount = intval($item['discount']);
                                    if ($discount > 0) {
                                        $discountedPrice = $price - ($price * $discount / 100);
                                        echo '<strong>₹' . number_format($discountedPrice * $item['quantity'], 2) . '</strong>';
                                    } else {
                                        echo '<strong>₹' . number_format($price * $item['quantity'], 2) . '</strong>';
                                    }
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Right Side: Order Summary -->
        <div class="checkout-summary-container">
            <div class="checkout-summary">
                <h2 class="section-title">Order Summary</h2>
                <div class="price-breakdown">
                    <div class="price-row">
                        <span>Subtotal</span>
                        <span>₹<?php echo number_format($priceBreakdown['subtotal'], 2); ?></span>
                    </div>
                    <div class="price-row">
                        <span>Taxes (<?php echo $priceBreakdown['tax_percent']; ?>%)</span>
                        <span>₹<?php echo number_format($priceBreakdown['tax_amount'], 2); ?></span>
                    </div>
                    <div class="price-row">
                        <span>Transaction Fee (<?php echo $priceBreakdown['transaction_fee_percent']; ?>%)</span>
                        <span>₹<?php echo number_format($priceBreakdown['transaction_fee'], 2); ?></span>
                    </div>
                    <div class="price-row">
                        <span>Maintenance Fee (<?php echo $priceBreakdown['maintenance_fee_percent']; ?>%)</span>
                        <span>₹<?php echo number_format($priceBreakdown['maintenance_fee'], 2); ?></span>
                    </div>
                </div>
                <div class="price-total">
                    <span>Total Amount</span>
                    <strong>₹<?php echo number_format($priceBreakdown['total_amount'], 2); ?></strong>
                </div>

                <?php if (!$isAddressComplete): ?>
                    <div class="address-incomplete-message">
                        Please complete your shipping address in your profile before proceeding.
                        <a href="/profile" class="btn btn-secondary">Go to Profile</a>
                    </div>
                <?php elseif (!$isCountrySupported): ?>
                    <div class="address-incomplete-message">
                        Unfortunately, we cannot process orders for your selected country due to legal restrictions. Please update your address to a supported country.
                    </div>
                <?php else: ?>
                    <!-- This is the crucial change -->
                    <?php
                        // Determine the correct form action URL
                        $formAction = $isSingleProductBuy 
                            ? "/api/order/initiate/" . htmlspecialchars($cartItems[0]['product_sku']) 
                            : "/api/order/initiate";
                    ?>
                    <form action="<?php echo $formAction; ?>" method="POST" style="display: contents;">
                        <button type="submit" class="btn btn-primary btn-proceed">
                          Proceed to Buy
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
