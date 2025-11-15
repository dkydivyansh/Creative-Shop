<div class="orders-container">
    <h1 class="orders-title">My Orders</h1>

    <?php if (empty($orders)): ?>
        <div class="no-orders-card">
            <p>You haven't placed any orders yet.</p>
            <a href="/" class="btn btn-primary">Start Shopping</a>
        </div>
    <?php else: ?>
        <div class="orders-list">
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="header-left">
                            <div class="header-info">
                                <span class="info-label">ORDER PLACED</span>
                                <span class="info-value"><?php echo date('d M Y', strtotime($order['order_date'])); ?></span>
                            </div>
                            <div class="header-info">
                                <span class="info-label">TOTAL</span>
                                <span class="info-value">₹<?php echo number_format($order['total_amount'], 2); ?></span>
                            </div>
                        </div>
                        <div class="header-right">
                            <div class="header-info order-id">
                                <span class="info-label">ORDER #</span>
                                <span class="info-value"><?php echo htmlspecialchars($order['id']); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="order-body">
                        <div class="order-details-left">
                            <span class="status status-<?php echo htmlspecialchars($order['status']); ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                            </span>
                            <?php 
                                // Specific messages or actions based on status
                                switch ($order['status']) {
                                    case 'pending_payment':
                                        echo '<a href="/payment/' . $order['id'] . '" class="btn-order-action">Complete Payment</a>';
                                        break;
                                    case 'cancelled':
                                        echo '<p class="status-note">If you have been charged, a refund will be processed in 3-5 business days.</p>';
                                        break;
                                    case 'delivered':
                                        echo '<a href="/orders/' . $order['id'] . '" class="btn-order-action">View Details & Keys</a>';
                                        break;
                                }
                            ?>
                        </div>
                        
                        <div class="order-details-right">
                            <div class="order-items-preview">
                                <?php if (!empty($order['items'])): ?>
                                    <?php $firstItem = $order['items'][0]; ?>
                                    <img src="<?php echo htmlspecialchars($firstItem['image'] ?? '/public/images/preholder.jpg'); ?>" alt="<?php echo htmlspecialchars($firstItem['name']); ?>" class="item-thumbnail">
                                    <div class="item-summary">
                                        <p class="item-name"><?php echo htmlspecialchars($firstItem['name']); ?></p>
                                        <?php if (count($order['items']) > 1): ?>
                                            <p class="other-items">+ <?php echo count($order['items']) - 1; ?> other item(s)</p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

