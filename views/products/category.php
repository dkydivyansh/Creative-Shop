<!-- This header section was missing. It's now included. -->
<div class="category-header">
    <h1 class="category-title">
        <?php echo htmlspecialchars($currentCategory['name'] ?? 'Category'); ?>
    </h1>
    <p class="category-description">
        <?php echo htmlspecialchars($currentCategory['description'] ?? 'Browse our collection of products in this category.'); ?>
    </p>
</div>

<!-- The filter and product grid remain the same -->
<section class="filter-section2">
    <div class="filter-tabs2">
        <a href="/category"
           data-category="all"
           class="filter-tab2 <?php echo (isset($currentCategory) && $currentCategory['name'] === 'All Products') ? 'active' : ''; ?>">
            All
        </a>
        
        <?php if (!empty($categories)): ?>
            <?php foreach ($categories as $category): ?>
                <a href="/category/<?php echo htmlspecialchars(urlencode($category['name'])); ?>" 
                   data-category="<?php echo htmlspecialchars($category['name']); ?>"
                   class="filter-tab2 <?php echo (!empty($currentCategory) && $currentCategory['name'] === $category['name']) ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($category['name']); ?>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>



<div class="products-grid">
    <?php if (!empty($products)): ?>
        <?php foreach ($products as $product): ?>
            <a href="/<?php echo htmlspecialchars($product['sku']); ?>" class="product-card">
                <div class="product-image">
                    <img src="<?php echo htmlspecialchars($product['image'] ?? '/public/images/preholder3.gif'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                <div class="product-info">
                    <div class="product-details">
                        <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></p>
                    </div>
                    <div class="product-price">
                        <?php
                        $price = floatval($product['price']);
                        $discount = intval($product['discount']);

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
    <?php else: ?>
        <p style="grid-column: 1 / -1; text-align: center;">No products found in this category.</p>
    <?php endif; ?>
</div>
