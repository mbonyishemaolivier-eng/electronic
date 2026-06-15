    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="index.php" class="logo">
                        <span class="logo-icon">⚡</span>
                        <span class="logo-text">neres<span class="logo-highlight">Store</span></span>
                    </a>
                    <p><?= APP_TAGLINE ?>. Quality electronics delivered across Rwanda.</p>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="products.php">All Products</a></li>
                        <li><a href="cart.php">Shopping Cart</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Categories</h4>
                    <ul>
                        <?php foreach (getCategories($db) as $cat): ?>
                        <li><a href="products.php?category=<?= sanitize($cat['slug']) ?>"><?= sanitize($cat['name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h4>Contact Us</h4>
                    <p>📍 Kigali, Rwanda</p>
                    <p>📞 +250 791 591 773</p>
                    <p>✉️ info@neresstore.rw</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <?= APP_NAME ?>. EWA408510 E-Commerce Project — UNILAK.</p>
            </div>
        </div>
    </footer>

    <?php
    if (strpos($_SERVER['PHP_SELF'] ?? '', '/admin/') === false) {
        require_once __DIR__ . '/ai_widget.php';
        renderAIChatWidget();
    }
    ?>

    <script src="assets/js/main.js"></script>
</body>
</html>
