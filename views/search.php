<div class="category-header">
    <h1 class="category-title">Search Results</h1>
    
    <form action="/search" method="get" class="search-form">
        <input type="text" name="q" value="<?php echo htmlspecialchars($query); ?>" placeholder="Search for products..." required>
        <button type="submit">Search</button>
    </form>

    <?php if (!empty($query)): ?>
        <p class="category-description">
            Showing results for: <strong>"<?php echo htmlspecialchars($query); ?>"</strong>
        </p>
    <?php endif; ?>
</div>

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
    <?php elseif (!empty($query)): ?>
        <p style="grid-column: 1 / -1; text-align: center;">No products found matching your search.</p>
    <?php endif; ?>
</div>

<style>
    .search-form {
        margin-top: 2rem;
        margin-bottom: 1rem;
        display: flex;
        gap: 10px;
        max-width: 600px;
    }
    .search-form input {
        flex-grow: 1;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        border-radius: 8px;
        border: 1px solid #555;
        background-color: #222;
        color: #fff;
    }
    .search-form button {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
        border: none;
        border-radius: 8px;
        background-color: #fff;
        color: #0a0a0a;
        cursor: pointer;
        font-weight: bold;
    }
</style>
