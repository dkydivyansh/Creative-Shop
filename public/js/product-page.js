document.addEventListener('DOMContentLoaded', () => {
    const actionButtonsContainer = document.querySelector('.action-buttons');
    const errorBox = document.getElementById('error-box');

    /**
     * Displays a message in the error box and hides it after a delay.
     * @param {string} message The error message to display.
     */
    const showError = (message) => {
        if (errorBox) {
            errorBox.textContent = message;
            errorBox.style.display = 'block';
            setTimeout(() => {
                errorBox.style.display = 'none';
            }, 3000); // Hide the error message after 3 seconds
        }
    };
    if (actionButtonsContainer) {
        const sku = actionButtonsContainer.dataset.sku;

        // --- API Call Functions ---
        const apiCall = (endpoint, body) => {
            return fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            }).then(res => res.json());
        };

        const addToCart = (quantity) => apiCall('/api/cart/add', { sku, quantity });
        const updateCartQuantity = (quantity) => apiCall('/api/cart/quantity', { sku, quantity });
        const deleteCartItem = () => apiCall('/api/cart/delete', { sku });

        // --- Main Event Listener ---
        actionButtonsContainer.addEventListener('click', (e) => {
            const target = e.target;

            const handleResponse = (promise) => {
                promise.then(response => {
                    if (response.success) {
                        window.location.reload();
                    } else {
                        // Replace with a more elegant notification system in the future
                        alert(response.message || 'An unexpected error occurred.');
                    }
                }).catch(err => {
                    console.error('API Error:', err);
                    alert('Could not connect to the server. Please check your connection.');
                });
            };

            // Add to Cart Button
            if (target.id === 'add-to-cart-btn') {
                const quantitySelect = document.getElementById('quantity-select');
                const quantity = quantitySelect ? parseInt(quantitySelect.value, 10) : 1;
                handleResponse(addToCart(quantity));
            }
            if (target.id === 'buy-now-btn') {
                if (sku) {
                    window.location.href = `/checkout/${sku}`;
                }
            }

            // Quantity Controls (+/-)
            if (target.classList.contains('quantity-btn')) {
                const quantityValueEl = actionButtonsContainer.querySelector('.quantity-value');
                let currentQuantity = parseInt(quantityValueEl.textContent, 10);
                
                if (target.classList.contains('plus')) {
                    handleResponse(updateCartQuantity(currentQuantity + 1));
                } else if (target.classList.contains('minus')) {
                    // If quantity is 1, a minus click should remove the item
                    const promise = (currentQuantity > 1) ? updateCartQuantity(currentQuantity - 1) : deleteCartItem();
                    handleResponse(promise);
                }
            }

            // Remove Button
            if (target.classList.contains('delete-btn')) {
                handleResponse(deleteCartItem());
            }
        });
    }
    // Check if the productImages variable exists and has content.
    // This script will only run on the single product page.
    if (typeof productImages === 'undefined' || productImages.length === 0) {
        return;
    }

    let currentImageIndex = 0;
    const totalImages = productImages.length;

    // --- Main Gallery Elements ---
    const mainImage = document.querySelector('.main-image');
    const thumbnails = document.querySelectorAll('.thumbnail');
    const imageCounter = document.querySelector('.image-counter');
    const prevButton = document.querySelector('.product-gallery .nav-button.prev');
    const nextButton = document.querySelector('.product-gallery .nav-button.next');

    // --- Lightbox Elements ---
    const lightbox = document.querySelector('.lightbox');
    const lightboxImage = document.querySelector('.lightbox-image');
    const lightboxClose = document.querySelector('.lightbox-close');
    const lightboxPrev = document.querySelector('.lightbox .lightbox-nav.prev');
    const lightboxNext = document.querySelector('.lightbox .lightbox-nav.next');
    const lightboxCounter = document.querySelector('.lightbox-counter');

    /**
     * Updates the main gallery and lightbox state based on the current image index.
     * Also handles the visibility of navigation buttons.
     */
    function updateGallery(index) {
        currentImageIndex = index;

        // Update main image and counter
        mainImage.src = productImages[index];
        if (imageCounter) {
            imageCounter.textContent = `${index + 1} / ${totalImages}`;
        }

        // Update active thumbnail
        thumbnails.forEach((thumb, i) => {
            thumb.classList.toggle('active', i === index);
        });

        // Update lightbox image and counter if it's open
        if (lightbox && lightbox.classList.contains('active')) {
            lightboxImage.src = productImages[index];
            if (lightboxCounter) {
                lightboxCounter.textContent = `${index + 1} / ${totalImages}`;
            }
        }
        
        // --- Navigation Button Logic ---
        if (totalImages <= 1) {
            // Hide all navigation if there's only one image
            [prevButton, nextButton, lightboxPrev, lightboxNext].forEach(btn => {
                if(btn) btn.style.display = 'none';
            });
        } else {
            // Show/hide previous button
            const showPrev = index > 0;
            if (prevButton) prevButton.style.display = showPrev ? 'flex' : 'none';
            if (lightboxPrev) lightboxPrev.style.display = showPrev ? 'flex' : 'none';

            // Show/hide next button
            const showNext = index < totalImages - 1;
            if (nextButton) nextButton.style.display = showNext ? 'flex' : 'none';
            if (lightboxNext) lightboxNext.style.display = showNext ? 'flex' : 'none';
        }
    }

    function showNextImage() {
        if (currentImageIndex < totalImages - 1) {
            updateGallery(currentImageIndex + 1);
        }
    }

    function showPrevImage() {
        if (currentImageIndex > 0) {
            updateGallery(currentImageIndex - 1);
        }
    }

    // --- Event Listeners ---

    // Thumbnail clicks
    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', () => {
            updateGallery(parseInt(thumb.dataset.index, 10));
        });
    });

    // Gallery navigation buttons
    if (nextButton) nextButton.addEventListener('click', showNextImage);
    if (prevButton) prevButton.addEventListener('click', showPrevImage);

    // --- Lightbox Logic ---
    function openLightbox(index) {
        if (!lightbox) return;
        // FIX: Activate the lightbox *before* calling updateGallery
        lightbox.classList.add('active');
        document.body.style.overflow = 'hidden';
        updateGallery(index); // This will now correctly populate the active lightbox
    }

    function closeLightbox() {
        if (!lightbox) return;
        lightbox.classList.remove('active');
        document.body.style.overflow = '';
    }

    // Open lightbox when main image is clicked
    if (mainImage) {
        mainImage.addEventListener('click', () => openLightbox(currentImageIndex));
    }
    
    // Lightbox close button
    if (lightboxClose) lightboxClose.addEventListener('click', closeLightbox);
    
    // Close lightbox when clicking on the background
    if (lightbox) {
        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) {
                closeLightbox();
            }
        });
    }

    // Lightbox navigation buttons
    if (lightboxNext) {
        lightboxNext.addEventListener('click', (e) => {
            e.stopPropagation();
            showNextImage();
        });
    }
    if (lightboxPrev) {
        lightboxPrev.addEventListener('click', (e) => {
            e.stopPropagation();
            showPrevImage();
        });
    }
    
    // Keyboard navigation for lightbox
    document.addEventListener('keydown', (e) => {
        if (lightbox && lightbox.classList.contains('active')) {
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowRight') showNextImage();
            if (e.key === 'ArrowLeft') showPrevImage();
        }
    });

    // --- Initial State ---
    // Set the initial state of the gallery on page load
    updateGallery(0);
});
