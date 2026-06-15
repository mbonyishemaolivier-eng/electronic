<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' | ' : '' ?><?= APP_NAME ?> - Electronics Store</title>
    <meta name="description" content="<?= APP_TAGLINE ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="assets/images/favicon.svg" type="image/svg+xml">
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <a href="index.php" class="logo">
                <span class="logo-icon">⚡</span>
                <span class="logo-text">neres<span class="logo-highlight">Store</span></span>
            </a>

            <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <nav class="main-nav" id="mainNav">
                <ul>
                    <li><a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">Home</a></li>
                    <li><a href="products.php" class="<?= basename($_SERVER['PHP_SELF']) === 'products.php' ? 'active' : '' ?>">Products</a></li>
                    <li class="nav-dropdown">
                        <a href="products.php">Categories</a>
                        <ul class="dropdown-menu">
                            <?php foreach (getCategories($db) as $cat): ?>
                            <li><a href="products.php?category=<?= sanitize($cat['slug']) ?>"><?= sanitize($cat['name']) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li><a href="cart.php" class="cart-link <?= basename($_SERVER['PHP_SELF']) === 'cart.php' ? 'active' : '' ?>">
                        Cart
                        <?php if (getCartCount() > 0): ?>
                        <span class="cart-badge"><?= getCartCount() ?></span>
                        <?php endif; ?>
                    </a></li>
                </ul>
            </nav>

            <form class="search-form" action="products.php" method="GET">
                <input type="search" name="search" placeholder="Search products..." value="<?= sanitize($_GET['search'] ?? '') ?>">
                <button type="submit" aria-label="Search">🔍</button>
            </form>
        </div>
    </header>

    <main class="main-content">
        <?php $flash = getFlash(); if ($flash): ?>
        <div class="container">
            <div class="alert alert-<?= sanitize($flash['type']) ?>">
                <?= sanitize($flash['message']) ?>
            </div>
        </div>
        <?php endif; ?>
