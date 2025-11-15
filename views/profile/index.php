<div class="profile-container">
    <h1 class="profile-title">My Profile</h1>

    <div id="notification-area"></div>

    <!-- User Information Card -->
    <div class="profile-card">
        
        <div class="profile-section-header">
            
            <h2 class="profile-section-title">User Information</h2>
            <button class="edit-btn" id="edit-profile-btn">Edit Profile</button>
        </div>
        <div class="profile-details">
            <div class="detail-item">
                <span class="detail-label">User ID</span>
                <span class="detail-value"><?php echo htmlspecialchars($user['auth_user_id'] ?? ''); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Name</span>
                <span class="detail-value" data-field="name"><?php echo htmlspecialchars($user['name'] ?? ''); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Email</span>
                <span class="detail-value"><?php echo htmlspecialchars($user['email'] ?? ''); ?></span>
            </div>
    
            <div class="detail-item">
                <span class="detail-label">Phone Number</span>
                <span class="detail-value" data-field="phone_number"><?php echo htmlspecialchars($user['phone_number'] ?? 'Not set'); ?></span>
            </div>
            
        </div>
        <p class="email-note">Email can only be updated from the authentication server.</p>
    </div>

    <!-- Address Details Card -->
    <div class="profile-card">
        <div class="profile-section-header">
            <h2 class="profile-section-title">Address Details</h2>
            <button class="edit-btn" id="edit-address-btn">Edit Address</button>
        </div>
        <div class="profile-details">
            <div class="detail-item">
                <span class="detail-label">Address Line 1</span>
                <span class="detail-value" data-field="address1"><?php echo htmlspecialchars($user['address1'] ?? 'Not set'); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Address Line 2</span>
                <span class="detail-value" data-field="address2"><?php echo htmlspecialchars($user['address2'] ?? 'Not set'); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Landmark</span>
                <span class="detail-value" data-field="landmark"><?php echo htmlspecialchars($user['landmark'] ?? 'Not set'); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">City</span>
                <span class="detail-value" data-field="city"><?php echo htmlspecialchars($user['city'] ?? 'Not set'); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">State</span>
                <span class="detail-value" data-field="state"><?php echo htmlspecialchars($user['state'] ?? 'Not set'); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Country</span>
                <span class="detail-value" data-field="country"><?php echo htmlspecialchars($user['country'] ?? 'Not set'); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Pincode</span>
                <span class="detail-value" data-field="pincode"><?php echo htmlspecialchars($user['pincode'] ?? 'Not set'); ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div id="profile-modal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Edit Profile</h2>
        <form id="profile-form">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="phone_number">Phone Number</label>
                <input type="tel" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>" required>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-cancel">Cancel</button>
                <button type="submit" class="btn-save">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Address Modal -->
<div id="address-modal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Edit Address</h2>
        <form id="address-form">
            <div class="form-group">
                <label for="country">Country</label>
                <select id="country" name="country" required>
                    <option value="">Select a country...</option>
                    <?php foreach ($supportedCountries as $countryName): ?>
                        <option value="<?php echo htmlspecialchars($countryName); ?>" <?php if ($user['country'] === $countryName) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($countryName); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="state">State</label>
                <select id="state" name="state" required></select>
            </div>
            <div class="form-group">
                <label for="city">City</label>
                <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="address1">Address Line 1</label>
                <input type="text" id="address1" name="address1" value="<?php echo htmlspecialchars($user['address1'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="address2">Address Line 2 (Optional)</label>
                <input type="text" id="address2" name="address2" value="<?php echo htmlspecialchars($user['address2'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="landmark">Landmark (Optional)</label>
                <input type="text" id="landmark" name="landmark" value="<?php echo htmlspecialchars($user['landmark'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="pincode">Pincode</label>
                <input type="text" id="pincode" name="pincode" value="<?php echo htmlspecialchars($user['pincode'] ?? ''); ?>" required>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-cancel">Cancel</button>
                <button type="submit" class="btn-save">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Pass API Key and current location to JS -->
<script>
    const currentUserLocation = {
        country: "<?php echo htmlspecialchars($user['country'] ?? ''); ?>",
        state: "<?php echo htmlspecialchars($user['state'] ?? ''); ?>",
        city: "<?php echo htmlspecialchars($user['city'] ?? ''); ?>"
    };
</script>
