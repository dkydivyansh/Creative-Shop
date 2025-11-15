document.addEventListener('DOMContentLoaded', () => {
    // --- Modal Handling ---
    const profileModal = document.getElementById('profile-modal');
    const addressModal = document.getElementById('address-modal');
    const editProfileBtn = document.getElementById('edit-profile-btn');
    const editAddressBtn = document.getElementById('edit-address-btn');
    const closeBtns = document.querySelectorAll('.close-btn');
    const cancelBtns = document.querySelectorAll('.btn-cancel');

    const openModal = (modal) => modal.style.display = 'block';
    const closeModal = (modal) => modal.style.display = 'none';

    if (editProfileBtn) editProfileBtn.addEventListener('click', () => openModal(profileModal));
    if (editAddressBtn) editAddressBtn.addEventListener('click', () => openModal(addressModal));

    closeBtns.forEach(btn => btn.addEventListener('click', (e) => closeModal(e.target.closest('.modal'))));
    cancelBtns.forEach(btn => btn.addEventListener('click', (e) => closeModal(e.target.closest('.modal'))));
    window.addEventListener('click', (e) => {
        if (e.target === profileModal) closeModal(profileModal);
        if (e.target === addressModal) closeModal(addressModal);
    });

    // --- Notification Handling ---
    const notificationArea = document.getElementById('notification-area');
    const showNotification = (message, type = 'success') => {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        notificationArea.appendChild(notification);
        setTimeout(() => notification.remove(), 4000);
    };

    // --- Form Submission ---
    const handleFormSubmit = async (form, url) => {
        const saveButton = form.querySelector('.btn-save');
        saveButton.disabled = true;
        saveButton.textContent = 'Saving...';

        const formData = new FormData(form);
        try {
            const response = await fetch(url, { method: 'POST', body: formData });
            const result = await response.json();

            if (result.success) {
                showNotification(result.message, 'success');

                // <-- FIX: Update UI dynamically instead of reloading -->
                if (form.id === 'profile-form') {
                    document.querySelector('[data-field="name"]').textContent = formData.get('name');
                    document.querySelector('[data-field="phone_number"]').textContent = formData.get('phone_number');
                    closeModal(profileModal);
                } else if (form.id === 'address-form') {
                    // This part is for the address form, which can also be updated dynamically
                    document.querySelector('[data-field="address1"]').textContent = formData.get('address1');
                    document.querySelector('[data-field="address2"]').textContent = formData.get('address2') || 'Not set';
                    document.querySelector('[data-field="landmark"]').textContent = formData.get('landmark') || 'Not set';
                    document.querySelector('[data-field="city"]').textContent = formData.get('city');
                    document.querySelector('[data-field="state"]').textContent = formData.get('state');
                    document.querySelector('[data-field="country"]').textContent = formData.get('country');
                    document.querySelector('[data-field="pincode"]').textContent = formData.get('pincode');
                    closeModal(addressModal);
                }
            } else {
                showNotification(result.message, 'error');
            }
        } catch (error) {
            showNotification('An unexpected error occurred. Please try again.', 'error');
        } finally {
            saveButton.disabled = false;
            saveButton.textContent = 'Save';
        }
    };

    const profileForm = document.getElementById('profile-form');
    if (profileForm) profileForm.addEventListener('submit', (e) => {
        e.preventDefault();
        handleFormSubmit(profileForm, '/profile/update');
    });

    const addressForm = document.getElementById('address-form');
    if (addressForm) addressForm.addEventListener('submit', (e) => {
        e.preventDefault();
        handleFormSubmit(addressForm, '/profile/address/update');
    });

    // --- Location Dropdown Logic ---
    const countrySelect = document.getElementById('country');
    const stateSelect = document.getElementById('state');

    const populateStates = async (countryName) => {
        stateSelect.innerHTML = '<option value="">Loading states...</option>';
        stateSelect.disabled = true;

        if (!countryName) {
            stateSelect.innerHTML = '<option value="">Select a country first</option>';
            return;
        }

        try {
            const response = await fetch(`https://liveapi.in/geo/state/?country=${countryName}`);
            const data = await response.json();
            
            stateSelect.innerHTML = '<option value="">Select a state...</option>';
            let statesFound = false;

            if (data.status === 'success' && Array.isArray(data.data) && data.data.length > 0) {
                data.data.forEach(state => {
                    const option = document.createElement('option');
                    option.value = state.name;
                    option.textContent = state.name;
                    stateSelect.appendChild(option);
                });
                statesFound = true;
            } 
            else if (typeof data === 'object' && !Array.isArray(data) && Object.keys(data).length > 0) {
                for (const stateCode in data) {
                    const option = document.createElement('option');
                    option.value = data[stateCode]; 
                    option.textContent = data[stateCode];
                    stateSelect.appendChild(option);
                }
                statesFound = true;
            }

            if (statesFound) {
                stateSelect.disabled = false;
                if (currentUserLocation.state) {
                    stateSelect.value = currentUserLocation.state;
                }
            } else {
                stateSelect.innerHTML = '<option value="">No states found</option>';
            }
        } catch (error) {
            console.error('Error fetching states:', error);
            stateSelect.innerHTML = '<option value="">Could not load states</option>';
        }
    };

    if (countrySelect) {
        countrySelect.addEventListener('change', () => {
            currentUserLocation.state = '';
            populateStates(countrySelect.value);
        });
    }

    if (currentUserLocation.country) {
        populateStates(currentUserLocation.country);
    } else {
        stateSelect.innerHTML = '<option value="">Select a country first</option>';
        stateSelect.disabled = true;
    }
});
