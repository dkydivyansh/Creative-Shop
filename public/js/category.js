/**
 * JavaScript for the Category Page
 */
document.addEventListener('DOMContentLoaded', () => {


    // --- 2. Dynamic Category Filtering (AJAX) ---
    const filterTabs = document.querySelectorAll('.filter-tab');
    const productsGrid = document.querySelector('.products-grid');
    const categoryTitle = document.querySelector('.category-title');
    const categoryDescription = document.querySelector('.category-description');

    filterTabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const categoryName = this.dataset.category;

            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            fetchCategoryData(categoryName);
            history.pushState({ category: categoryName }, '', this.href);
        });
    });

    async function fetchCategoryData(categoryName) {
        try {
            productsGrid.innerHTML = '<p style="grid-column: 1 / -1; text-align: center;">Loading...</p>';
            const response = await fetch(`/api.php?action=get_category_data&category=${encodeURIComponent(categoryName)}`);
            if (!response.ok) throw new Error('Network response failed');
            const data = await response.json();
            
            updateCategoryHeader(data.details);
            renderProducts(data.products);
        } catch (error) {
            console.error('Failed to fetch category data:', error);
            productsGrid.innerHTML = '<p style="grid-column: 1 / -1; text-align: center;">Failed to load content.</p>';
        }
    }

    function updateCategoryHeader(details) {
        if (!details) return;
        categoryTitle.textContent = details.name;
        categoryDescription.textContent = details.description;
        document.title = `Category - ${details.name}`;
    }

    function renderProducts(products) {
        productsGrid.innerHTML = '';
        if (!products || products.length === 0) {
            productsGrid.innerHTML = '<p style="grid-column: 1 / -1; text-align: center;">No products found in this category.</p>';
            return;
        }

        products.forEach(product => {
            const price = parseFloat(product.price);
            const discount = parseInt(product.discount, 10);
            let priceHTML = `₹${price.toFixed(2)}`;

            if (discount > 0) {
                const discountedPrice = price - (price * discount / 100);
                priceHTML = `<del style="color: #666; margin-right: 8px;">₹${price.toFixed(2)}</del> ₹${discountedPrice.toFixed(2)}`;
            }

            const productCardHTML = `
                <a href="/${product.sku}" class="product-card">
                    <div class="product-image">
                        <img src="${product.image || '/public/images/preholder3.gif'}" alt="${product.name}">
                    </div>
                    <div class="product-info">
                        <div class="product-details">
                            <h3 class="product-name">${product.name}</h3>
                            <p class="product-category">${product.category_name || 'Uncategorized'}</p>
                        </div>
                        <div class="product-price">${priceHTML}</div>
                    </div>
                </a>`;
            productsGrid.insertAdjacentHTML('beforeend', productCardHTML);
        });
    }
});
