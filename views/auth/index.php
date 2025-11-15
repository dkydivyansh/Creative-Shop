<div id="background-container">
    <!-- This is where a background animation could be placed -->
</div>

<div class="auth-container">
    <?php if ($formType === 'login'): ?>
        <!-- Login Card -->
        <div class="auth-card">
    <img src="https://dkydivyansh.com/wp-content/uploads/2025/08/D-2.png" alt="icon" class="auth-card-icon" onerror="this.style.display='none'"/>
    <h2>Login</h2>

    <?php 
        // Check for an error message from the callback
        if (isset($_SESSION['auth_error'])): 
    ?>
        <div class="auth-error-box">
            <?php 
                echo htmlspecialchars($_SESSION['auth_error']); 
                // Clear the error from the session so it doesn't show again
                unset($_SESSION['auth_error']);
            ?>
        </div>
    <?php endif; ?>

    <p>Welcome back! Sign in to access your account, view your orders, and manage your profile.</p>
    <a href="#" class="auth-button">
        <span>Login with dkydivyansh.com</span>
        <span class="material-symbols-outlined arrow">arrow_forward</span>
    </a>
</div>
    <?php else: ?>
        <!-- Register Card -->
        <div class="auth-card">
            <img src="https://dkydivyansh.com/wp-content/uploads/2025/08/D-2.png" alt="icon" class="auth-card-icon" onerror="this.style.display='none'"/>
            <h2>Register</h2>
            <p>New here? Create an account to start shopping, save your favorite items, and enjoy a seamless checkout experience.</p>
            <a href="#" class="auth-button">
                <span>Register with dkydivyansh.com</span>
                <span class="material-symbols-outlined arrow">arrow_forward</span>
            </a>
        </div>
    <?php endif; ?>
</div>
