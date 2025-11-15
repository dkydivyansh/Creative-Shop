<main>
    <div class="product-page-container">
        <div id="error-box" class="error-box" style="display: none;"></div>
        <div class="product-layout">
            <!-- Image Gallery -->
            <div class="product-gallery">
                <?php 
                $placeholder = '/public/images/preholder.jpg'; 
                ?>

                <?php if (!empty($product['images'])): ?>
                    <div class="gallery-wrapper">
                        <div class="thumbnail-column">
                            <div class="thumbnail-scroll">
                                <?php foreach ($product['images'] as $index => $img): ?>
                                    <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                                        <img src="<?php echo htmlspecialchars(!empty($img) ? $img : $placeholder); ?>" alt="Product thumbnail <?php echo $index + 1; ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="main-image-container">
                            <img src="<?php echo htmlspecialchars(!empty($product['images'][0]) ? $product['images'][0] : $placeholder); ?>" alt="Main product view" class="main-image">
                            <button class="nav-button prev">←</button>
                            <button class="nav-button next">→</button>
                            <div class="image-counter">1 / <?php echo count($product['images']); ?></div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="main-image-container">
                        <img src="<?php echo $placeholder; ?>" alt="No product image available" class="main-image">
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Product Details -->
            <div class="product-details">
                <h1 class="product-name2"><?php echo htmlspecialchars($product['name']); ?></h1>
                <p class="product-description2"><?php echo htmlspecialchars($product['description'] ?? ''); ?></p>
                
                <div class="product-meta">
                    <div class="meta-item"><strong>Category:</strong> <span><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></span></div>
                    <?php if ($product['type'] !== 'physical'): ?>
                        <div class="meta-item"><strong>Type:</strong> <span>Digital Product</span></div>
                    <?php endif; ?>
                    <?php if ($product['region'] !== 'all'): ?>
                        <div class="meta-item"><strong>Region:</strong> <span><?php echo htmlspecialchars($product['region']); ?></span></div>
                    <?php endif; ?>
                     <?php if (in_array($product['type'], ['physical', 'key_lim', 'key', 'file'])): ?>
                        <?php
                            $stockText = '';
                            if (in_array($product['type'], ['key', 'file'])) {
                                if (is_null($product['stock'])) {
                                    $stockText = '∞';
                                } elseif ((int)$product['stock'] === 0) {
                                    $stockText = 'Out of Stock';
                                } else {
                                    $stockText = '∞';
                                }
                            } else {
                                $stock = (int)($product['stock'] ?? 0);
                                $stockText = $stock > 0 ? $stock . ' Available' : 'Out of Stock';
                            }
                        ?>
                        <div class="meta-item"><strong>Stock:</strong> <span><?php echo $stockText; ?></span></div>
                    <?php endif; ?>
                </div>

                <div class="product-price2">
                    <?php
                        $price = floatval($product['price']);
                        $discount = intval($product['discount']);
                        if ($discount > 0) {
                            $discountedPrice = $price - ($price * $discount / 100);
                            echo '<del style="color: #666; font-size: 1.5rem; margin-right: 1rem;">₹' . number_format($price, 2) . '</del>';
                            echo '₹' . number_format($discountedPrice, 2);
                        } else {
                            echo '₹' . number_format($price, 2);
                        }
                    ?>
                </div>

                <!-- ACTION BUTTONS SECTION -->
                <div class="action-buttons" data-sku="<?php echo htmlspecialchars($product['sku']); ?>">
                    <?php if ($isOutOfStock): ?>
                        <button class="btn btn-primary" disabled>Out of Stock</button>
                    <?php elseif (!isset($_SESSION['user_id'])): ?>
                        <div class="login-prompt">
                            <a href="/login">Log in</a> to purchase this item.
                        </div>
                    <?php else: ?>
                        <?php if ($productInCart): ?>
                            <?php if ($maxBuyableQuantity <= 1): ?>
                                <button class="btn btn-primary" disabled>Added to Cart</button>
                                <button class="delete-btn">Remove</button>
                            <?php else: ?>
                                <div class="item-quantity-controls">
                                    <button class="quantity-btn minus" <?php if ($productInCart['quantity'] <= 1) echo 'disabled'; ?>>-</button>
                                    <span class="quantity-value"><?php echo $productInCart['quantity']; ?></span>
                                    <button class="quantity-btn plus" <?php if ($productInCart['quantity'] >= $maxBuyableQuantity) echo 'disabled'; ?>>+</button>
                                </div>
                                <button class="delete-btn">Remove</button>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if ($maxBuyableQuantity > 1): ?>
                                <div class="quantity-selector">
                                    <label for="quantity-select">Qty:</label>
                                    <select id="quantity-select" class="form-select">
                                        <?php for ($i = 1; $i <= $maxBuyableQuantity; $i++): ?>
                                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                            <button id="add-to-cart-btn" class="btn btn-primary">Add to Cart</button>
                            <button id="buy-now-btn" class="btn btn-secondary">Buy Now</button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>


                <?php if (!empty($product['specs'])): ?>
                <div class="specs-section">
                    <h3 class="specs-title">Product Specifications</h3>
                    <table class="spec-table">
                        <tbody>
                            <?php foreach ($product['specs'] as $key => $value): ?>
                                <tr>
                                    <th><?php echo htmlspecialchars($key); ?></th>
                                    <td><?php echo htmlspecialchars($value); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Lightbox -->
        <?php if (!empty($product['images'])): ?>
            <div class="lightbox">
                <div class="lightbox-content">
                    <button class="lightbox-close">×</button>
                    <img src="" alt="Product lightbox image" class="lightbox-image">
                    <button class="lightbox-nav prev">←</button>
                    <button class="lightbox-nav next">→</button>
                    <div class="lightbox-counter"></div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Related Products Section -->
    <?php if (!empty($relatedProducts)): ?>
    <section class="related-products-section">
        <h2 class="related-products-title">Explore products in <?php echo htmlspecialchars($product['category_name']); ?></h2>
        <div class="products-grid">
            <?php foreach ($relatedProducts as $relatedProduct): ?>
                <a href="/<?php echo htmlspecialchars($relatedProduct['sku']); ?>" class="product-card">
                    <div class="product-image">
                        <img src="<?php echo htmlspecialchars($relatedProduct['image'] ?? '/public/images/preholder3.gif'); ?>" alt="<?php echo htmlspecialchars($relatedProduct['name']); ?>">
                    </div>
                    <div class="product-info">
                        <div class="product-details">
                            <h3 class="product-name"><?php echo htmlspecialchars($relatedProduct['name']); ?></h3>
                            <p class="product-category"><?php echo htmlspecialchars($relatedProduct['category_name'] ?? 'Uncategorized'); ?></p>
                        </div>
                        <div class="product-price">
                            <?php
                            $price = floatval($relatedProduct['price']);
                            $discount = intval($relatedProduct['discount']);
                            if ($discount > 0) {
                                $discountedPrice = $price - ($price * $discount / 100);
                                echo '<del style="color: #666; margin-right: 8px;">₹' . number_format($price, 2) . '</del>';
                                echo '₹' . number_format($discountedPrice, 2);
                            } else {
                                echo '₹' . number_format($price, 2);
                            }
                            ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="view-more-container">
            <a href="/category/<?php echo htmlspecialchars(urlencode($product['category_name'])); ?>" class="btn btn-secondary">View More</a>
        </div>
    </section>
    <?php endif; ?>
</main>

<?php if (!empty($product['images'])): ?>
    <script>
        const productImages = <?php echo json_encode($product['images']); ?>;
    </script>
<?php endif; ?>
