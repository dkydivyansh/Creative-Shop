
<?php
// This snippet should be at the very top of the file, before any HTML.
$flash_message = get_flash_message(); 
?>
<div class="cart-container">
    <h1 class="cart-title">Shopping Cart</h1>
    <?php if ($flash_message): ?>
        <div class="flash-message-container error">
            <div class="flash-message <?php echo htmlspecialchars($flash_message['type']); ?>">
                <p><?php echo htmlspecialchars($flash_message['message']); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- This subtotal is only visible on mobile -->
    <div class="mobile-subtotal">
        Subtotal (<?php echo $totalItems; ?> item<?php if ($totalItems !== 1) echo 's'; ?>):
        <strong>₹<?php echo number_format($subtotal, 2); ?></strong>
    </div>

    <!-- Custom Error Box -->
    <div id="error-box" class="error-box" style="display: none;"></div>

    <?php if (empty($cartItems)): ?>
        <div class="cart-empty">
            <p>Your cart is currently empty.</p>
            <a href="/" class="btn btn-primary">Continue Shopping</a>
        </div>
    <?php else: ?>
        <div class="cart-layout">
            <div class="cart-items-container">
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item" data-sku="<?php echo htmlspecialchars($item['product_sku']); ?>">
                        <div class="item-image">
                            <img src="<?php echo htmlspecialchars($item['image'] ?? '/public/images/preholder3.gif'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        </div>
                        <div class="item-details">
                            <h2 class="item-name">
    <a href="/<?php echo htmlspecialchars($item['product_sku']); ?>">
        <?php echo htmlspecialchars($item['name']); ?>
    </a>
</h2>
                            <div class="item-stock-status">
                                <?php
                                    // Logic to determine stock status text
                                    $stockText = 'In Stock';
                                    $isOutOfStock = false;
                                    if (in_array($item['type'], ['key', 'file'])) {
                                        if (!is_null($item['stock']) && (int)$item['stock'] === 0) {
                                            $stockText = 'Out of Stock';
                                            $isOutOfStock = true;
                                        }
                                    } else {
                                        $stock = (int)($item['stock'] ?? 0);
                                        if ($stock > 0) {
                                            $stockText = $stock . ' Available';
                                        } else {
                                            $stockText = 'Out of Stock';
                                            $isOutOfStock = true;
                                        }
                                    }
                                    echo "<span class='" . ($isOutOfStock ? "out-of-stock" : "in-stock") . "'>{$stockText}</span>";
                                ?>
                            </div>
                            <?php if (!$isOutOfStock): ?>
                            <div class="item-quantity-controls">
                                <button class="quantity-btn minus" <?php if ($item['quantity'] <= 1) echo 'disabled'; ?>>-</button>
                                <span class="quantity-value"><?php echo $item['quantity']; ?></span>
                                <?php
                                    $maxQty = PHP_INT_MAX;
                                    if(in_array($item['type'], ['physical', 'key_lim'])) {
                                        $maxQty = (int)$item['stock'];
                                    }
                                    $maxPerPerson = (int)$item['max_item_per_person'];
                                    $effectiveMax = min($maxQty, $maxPerPerson);
                                ?>
                                <button class="quantity-btn plus" <?php if ($item['quantity'] >= $effectiveMax) echo 'disabled'; ?>>+</button>
                            </div>
                            <?php endif; ?>
                            <button class="delete-btn">Delete</button>
                        </div>
                        <?php if (!$isOutOfStock): ?>
                        <div class="item-price">
                            <?php
                                $price = floatval($item['price']);
                                $discount = intval($item['discount']);
                                if ($discount > 0) {
                                    $discountedPrice = $price - ($price * $discount / 100);
                                    echo '<strong>₹' . number_format($discountedPrice, 2) . '</strong>';
                                    echo '<del>₹' . number_format($price, 2) . '</del>';
                                } else {
                                    echo '<strong>₹' . number_format($price, 2) . '</strong>';
                                }
                            ?>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="cart-summary-container">
                <div class="cart-summary">
                    <div class="subtotal">
                        Subtotal (<?php echo $totalItems; ?> item<?php if ($totalItems !== 1) echo 's'; ?>):
                        <strong>₹<?php echo number_format($subtotal, 2); ?></strong>
                    </div>
                    <button onclick="window.location.href='/checkout';" class="btn btn-primary btn-proceed">Proceed to Buy</button>
                </div>
            </div>
        </div>

        <!-- Mobile fixed "Proceed to Buy" button -->
        <div class="mobile-checkout-bar">
            <button onclick="window.location.href='/checkout';"  class="btn btn-primary btn-proceed">
                Proceed to Buy (<?php echo $totalItems; ?> item<?php if ($totalItems !== 1) echo 's'; ?>)
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-confirm-modal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <p>Are you sure you want to remove this item from your cart?</p>
        <div class="modal-actions">
            <button id="modal-cancel-btn" class="btn btn-secondary">Cancel</button>
            <button id="modal-confirm-btn" class="btn btn-danger">Remove</button>
        </div>
    </div>
</div>
