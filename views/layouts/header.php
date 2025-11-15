<?php
// Include the session helper and User model
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Cart.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in and get their name
$is_logged_in = is_user_logged_in();
$user_name = null;
$cart_item_count = 0;
if ($is_logged_in) {
    $pdo = DBConnection::get();
    if (isset($pdo)) {
        $userModel = new User($pdo);
        $user_name = $userModel->getUserNameByAuthId($_SESSION['user_id']);
        $cartModel = new Cart($pdo);
        $cart_item_count = $cartModel->getCartItemCount($_SESSION['user_id']);
    }
}

// Get the current path to determine the active page
$current_path = strtok($_SERVER['REQUEST_URI'], '?');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - dkydivyansh.com</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Workbench&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Handjet:wght@300&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Flex:opsz,wght@8..144,100..1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="/public/css/style.css?v=<?php echo time(); ?>">

    <!-- Page-specific stylesheets -->
    <?php if (isset($extra_styles)): ?>
        <?php foreach ($extra_styles as $style): ?>
            <link rel="stylesheet" href="<?php echo $style; ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Custom styles for user greeting -->
    <style>
        .user-greeting {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 150px;
            font-family: "Handjet", sans-serif;
            font-size: 1.5rem;
            color: #ffffff;
            font-weight: 700;
        }

        .mobile-user-greeting {
            font-weight: 800;
            padding: 1rem 2rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-family: "Handjet", sans-serif;
            font-size: 1.5rem;
        }

        /* New style to hide the greeting on mobile */
        @media (max-width: 1023px) {
            .user-greeting.mobile-hidden {
                display: none;
            }
        }
    </style>

    <!-- External JS Libraries -->
    <script src="https://unpkg.com/lenis@1.3.8/dist/lenis.min.js"></script>
    <script type="module">
        import * as ogl from 'https://unpkg.com/ogl';
        window.ogl = ogl;
    </script>
        <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

    <!-- 2. ADD CORRECTED LOADER STYLES -->
    <style>
        #lottie-loader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #0a0a0a; /* Use a solid background to prevent content flashing */
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 1; /* Start fully visible */
            visibility: visible;
            transition: opacity 0.4s ease, visibility 0.4s ease; /* Add a smooth fade-out transition */
        }

        #lottie-loader-overlay.hidden {
            opacity: 0;
            visibility: hidden;
        }
    </style>
</head>

<body>
    <div id="lottie-loader-overlay">
        <lottie-player 
            src="https://lottie.host/2c6e762d-fa54-469d-a295-1a488687d544/1FsdIXLWbB.json" 
            background="transparent" 
            speed="2" 
            style="width: 500px; height: 500px;" 
            loop 
            autoplay>
        </lottie-player>
    </div>
    <nav class="navbar">
        <div class="navbar-content">
            <a href="/" class="logo">SHOP - dkydivyansh</a>

            <nav class="desktop-menu">
                <a href="/" class="nav-item <?php echo ($current_path === '/') ? 'active' : ''; ?>">Home</a>
                <div class="nav-item <?php echo (strpos($current_path, '/category') === 0) ? 'active' : ''; ?>">
                    Categories
                    <div class="submenu">
                        <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $category): ?>
                                <a href="/category/<?php echo htmlspecialchars(urlencode($category['name'])); ?>"><?php echo htmlspecialchars($category['name']); ?></a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($is_logged_in): ?>
                    <a href="/profile" class="nav-item <?php echo ($current_path === '/profile') ? 'active' : ''; ?>">Profile</a>
                    <a href="/orders" class="nav-item <?php echo (strpos($current_path, '/orders') === 0) ? 'active' : ''; ?>">Orders</a>
                    <a href="/auth/logout" class="nav-item">Logout</a>
                <?php else: ?>
                    <a href="/login" class="nav-item <?php echo ($current_path === '/login') ? 'active' : ''; ?>">Login</a>
                    <a href="/register" class="nav-item <?php echo ($current_path === '/register') ? 'active' : ''; ?>">Register</a>
                <?php endif; ?>
            </nav>

            <div class="header-right-icons">
                <?php if ($is_logged_in && $user_name): ?>
                    <span class="nav-item user-greeting mobile-hidden">Hello, <?php echo htmlspecialchars($user_name); ?></span>
                <?php endif; ?>
                <a href="/cart" class="cart-icon-wrapper">
                    <span class="material-symbols-outlined cart-icon">shopping_cart</span>
                    <?php if ($is_logged_in && $cart_item_count > 0): ?>
                        <span class="cart-item-count"><?php echo $cart_item_count; ?></span>
                    <?php endif; ?>
                </a>
                <span class="material-symbols-outlined search-icon">search</span>
                <button class="mobile-menu-toggle material-symbols-outlined">menu</button>
            </div>
        </div>
    </nav>
   <script>
        document.addEventListener('DOMContentLoaded', function() {
            const lottieLoader = document.getElementById('lottie-loader-overlay');
            let hideLoaderTimeout;

            const hideLoader = () => {
                if (lottieLoader) {
                    lottieLoader.classList.add('hidden');
                    if (hideLoaderTimeout) {
                        clearTimeout(hideLoaderTimeout);
                    }
                    // Remove the click listener in case it was added
                    lottieLoader.removeEventListener('click', hideLoader);
                }
            };

            // --- Page Load Logic ---
            // 1. A promise that resolves after a minimum display time of 1 second
            const minimumDisplayTime = new Promise(resolve => setTimeout(resolve, 3000));

            // 2. A promise that resolves when the page content is fully loaded
            const pageContentLoaded = new Promise(resolve => {
                // Using 'load' ensures all assets like images are ready
                window.addEventListener('load', resolve, { once: true });
            });

            // 3. Wait for both the minimum time and the page to fully load
            Promise.all([minimumDisplayTime, pageContentLoaded]).then(() => {
                hideLoader();
            });

            // 4. Set a 5-second timeout to allow manual closing if loading is stuck
            hideLoaderTimeout = setTimeout(() => {
                if (lottieLoader && !lottieLoader.classList.contains('hidden')) {
                    // If the loader is still visible after 5 seconds, allow user to click it away
                    lottieLoader.addEventListener('click', hideLoader, { once: true });
                }
            }, 5000);

            // --- Page Navigation Logic ---
            document.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    const target = this.getAttribute('target');

                    // Only trigger for internal, same-tab navigation
                    if (href && href !== '#' && !href.startsWith('javascript:') && target !== '_blank') {
                        e.preventDefault(); 
                        
                        if (lottieLoader) {
                            lottieLoader.classList.remove('hidden');
                        }
                        
                        setTimeout(() => {
                            window.location.href = href;
                        }, 150);
                    }
                });
            });
        });
    </script>
    <!-- Mobile Menu Structure -->
    <div class="menu-backdrop"></div>
    <div class="mobile-menu">
        <button class="mobile-menu-close material-symbols-outlined">close</button>

        <?php if ($is_logged_in && $user_name): ?>
            <div class="mobile-user-greeting">Hello, <?php echo htmlspecialchars($user_name); ?></div>
        <?php endif; ?>

        <a href="/" class="mobile-menu-item">Home</a>
        <div class="mobile-menu-item">
            Categories
            <div class="mobile-submenu">
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $category): ?>
                        <a href="/category/<?php echo htmlspecialchars(urlencode($category['name'])); ?>"><?php echo htmlspecialchars($category['name']); ?></a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($is_logged_in): ?>
            <a href="/profile" class="mobile-menu-item">Profile</a>
            <a href="/orders" class="mobile-menu-item">Orders</a>
            <a href="/auth/logout" class="mobile-menu-item">Logout</a>
        <?php else: ?>
            <a href="/login" class="mobile-menu-item">Login</a>
            <a href="/register" class="mobile-menu-item">Register</a>
        <?php endif; ?>
    </div>
    <div id="loader-overlay" class="loader-overlay" style="display: none;">
    <div class="loader-spinner"></div>
</div>

    <!-- Search Popup Structure -->
    <div class="search-popup">
        <div class="search-box">
            <span class="material-symbols-outlined">search</span>
            <input type="text" class="search-input" placeholder="Search products...">
        </div>
    </div>
<a href="https://donate.dkydivyansh.com/" target="_blank" rel="noopener noreferrer" id="dky-support-button">
        <img src="https://dkydivyansh.com/wp-content/uploads/2025/09/heart.png" alt="Support Icon" class="dky-support-icon">
        <span class="dky-support-text">Support My Projects</span>
    </a>

    <style>
        #dky-support-button {
            position: fixed;
            bottom: 50px;
            right: 10px;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: rgb(59 59 59 / 20%);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 50px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            text-decoration: none;
            overflow: hidden;
            opacity: 0;
            visibility: hidden;
            transform: translateX(120%);
            transition: all 0.5s cubic-bezier(0.25, 1, 0.5, 1);
        }

        #dky-support-button.dky-support-show {
            opacity: 1;
            visibility: visible;
            transform: translateX(0);
        }

        #dky-support-button .dky-support-icon {
            height: 28px;
            width: 28px;
            fill: #e91e63;
            flex-shrink: 0;
            transition: fill 0.3s ease;
        }

        #dky-support-button .dky-support-text {
            color: #333;
            font-weight: 600;
            font-size: 1rem;
            white-space: nowrap;
            opacity: 0;
            max-width: 0;
            margin-left: 0;
            transition: max-width 0.4s ease, opacity 0.3s ease, margin-left 0.4s ease;
        }
		 #dky-support-button .dky-support-icon {
            height: 20px;
            width: 20px;
            flex-shrink: 0;
            transition: transform 0.3s ease; /* Added a subtle hover effect for the image */
        }

        #dky-support-button .dky-support-text {
            color: #333;
            font-weight: 600;
            font-size: 1rem;
            white-space: nowrap;
            opacity: 0;
            max-width: 0;
            margin-left: 0;
            transition: max-width 0.4s ease, opacity 0.3s ease, margin-left 0.4s ease;
        }

        /* Hover Effects */
        #dky-support-button:hover {
            width: 260px;
            background-color: #e91e63;
        }

        #dky-support-button:hover .dky-support-icon {
            fill: #fff;
        }

        #dky-support-button:hover .dky-support-text {
            opacity: 1;
            max-width: 200px;
            margin-left: 12px;
            color: #fff;
        }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const supportButton = document.getElementById('dky-support-button');
        if (!supportButton) return;

        const scrollThreshold = 20; 

        const toggleButtonVisibility = () => {
            if (window.scrollY > scrollThreshold) {
                supportButton.classList.add('dky-support-show');
            } else {
                supportButton.classList.remove('dky-support-show');
            }
        };

        window.addEventListener('scroll', toggleButtonVisibility);
        toggleButtonVisibility(); // Check on page load
    });
    </script>
    <main> <!-- Main content starts here -->
    
    