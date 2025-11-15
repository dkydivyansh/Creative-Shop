document.addEventListener('DOMContentLoaded', function() {
    
    window.onbeforeunload = null;

    // --- Element Selectors ---
    const errorBox = document.getElementById('error-box');
    const loader = document.getElementById('loader-overlay');

    // --- UI Helper Functions ---

    const showLoader = () => { if (loader) loader.style.display = 'flex'; };
    const hideLoader = () => { if (loader) loader.style.display = 'none'; };

    const showError = (message) => {
        if (errorBox) {
            errorBox.textContent = message;
            errorBox.style.display = 'block';
            setTimeout(() => {
                errorBox.style.display = 'none';
            }, 3000);
        }
    };

    // --- API Call Functions ---

    const apiCall = (endpoint, body) => {
        showLoader(); // Show loader before every API call
        return fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        })
        .then(res => res.json())
        .finally(hideLoader); // Hide loader after every API call, regardless of outcome
    };

    const updateCartQuantity = (sku, newQuantity) => apiCall('/api/cart/quantity', { sku, quantity: newQuantity });
    const deleteCartItem = (sku) => apiCall('/api/cart/delete', { sku });

    // --- Cart Item Event Listeners ---
    document.querySelectorAll('.cart-item').forEach(item => {
        const sku = item.dataset.sku;
        const minusBtn = item.querySelector('.quantity-btn.minus');
        const plusBtn = item.querySelector('.quantity-btn.plus');
        const quantityValue = item.querySelector('.quantity-value');
        const deleteBtn = item.querySelector('.delete-btn');

        const handleResponse = (promise) => {
            promise.then(response => {
                if (response.success) {
                    window.location.reload();
                } else {
                    showError(response.message || 'An unexpected error occurred.');
                }
            }).catch(err => {
                console.error('API Error:', err);
                showError('Could not connect to the server. Please try again.');
            });
        };

        if (minusBtn) {
            minusBtn.addEventListener('click', () => {
                let currentQuantity = parseInt(quantityValue.textContent, 10);
                if (currentQuantity > 1) {
                    handleResponse(updateCartQuantity(sku, currentQuantity - 1));
                }
            });
        }

        if (plusBtn) {
            plusBtn.addEventListener('click', () => {
                let currentQuantity = parseInt(quantityValue.textContent, 10);
                handleResponse(updateCartQuantity(sku, currentQuantity + 1));
            });
        }

        if (deleteBtn) {
            deleteBtn.addEventListener('click', () => {
                handleResponse(deleteCartItem(sku));
            });
        }
    });
});
