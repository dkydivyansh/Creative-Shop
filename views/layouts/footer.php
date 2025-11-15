</main> <!-- This closes the <main> tag opened in header.php -->

<style>
    .site-footer {
        background-color: #111111;
        color: #888888;
        padding: 2rem 1rem;
        border-top: 1px solid #222;
        font-size: 0.9rem;
    }
    .footer-container {
        max-width: 1400px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1.5rem;
    }
    .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 1rem 2rem;
    }
    .footer-links a {
        color: #888888;
        text-decoration: none;
        transition: color 0.2s;
    }
    .footer-links a:hover {
        color: #ffffff;
        text-decoration: underline;
    }
    .footer-copyright {
        text-align: center;
    }
</style>

<footer class="site-footer">
    <div class="footer-container">
        <ul class="footer-links">
            <li><a href="https://legal.dkydivyansh.com/Terms-and-Conditions" target="_blank" rel="noopener noreferrer">Terms & Conditions</a></li>
            <li><a href="http://legal.dkydivyansh.com/Privacy-Policy" target="_blank" rel="noopener noreferrer">Privacy Policy</a></li>
            <li><a href="https://legal.dkydivyansh.com/Shipping-Delivery" target="_blank" rel="noopener noreferrer">Shipping & Delivery</a></li>
            <li><a href="https://legal.dkydivyansh.com/Cancellation-Refund" target="_blank" rel="noopener noreferrer">Cancellation & Refund</a></li>
            <li><a href="https://legal.dkydivyansh.com/Pricing-Policy" target="_blank" rel="noopener noreferrer">Pricing Policy</a></li>
            <li><a href="https://legal.dkydivyansh.com/" target="_blank" rel="noopener noreferrer">Legal Home</a></li>
             <li><a href="https://dkydivyansh.com/contact/" target="_blank" rel="noopener noreferrer">Contact Me</a></li>
        </ul>
        <div class="footer-copyright">
            &copy; <?php echo date('Y'); ?> dkydivyansh.com All Rights Reserved.
        </div>
    </div>
</footer>

<script src="/public/js/main.js?v=<?php echo time(); ?>"></script>
<?php if (isset($extra_scripts) && is_array($extra_scripts)): ?>
    <?php foreach ($extra_scripts as $script): ?>
        <script src="<?php echo htmlspecialchars($script); ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
