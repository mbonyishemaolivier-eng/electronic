<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' | ' : '' ?>Admin - <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="icon" href="../assets/images/favicon.svg" type="image/svg+xml">
</head>
<body class="admin-body">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-brand">
            <a href="index.php">
                <span class="logo-icon">⚡</span>
                <span>neres<span class="logo-highlight">Store</span></span>
            </a>
            <small>Admin Panel</small>
        </div>
        <nav class="admin-nav">
            <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">
                <span class="nav-icon">📊</span> Dashboard
            </a>
            <a href="products.php" class="<?= in_array(basename($_SERVER['PHP_SELF']), ['products.php', 'product-create.php', 'product-edit.php']) ? 'active' : '' ?>">
                <span class="nav-icon">📦</span> Products
            </a>
            <a href="activities.php" class="<?= basename($_SERVER['PHP_SELF']) === 'activities.php' ? 'active' : '' ?>">
                <span class="nav-icon">📋</span> Activity Log
            </a>
            <a href="../index.php" target="_blank">
                <span class="nav-icon">🌐</span> View Store
            </a>
            <a href="logout.php" class="nav-logout">
                <span class="nav-icon">🚪</span> Logout
            </a>
        </nav>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <button class="admin-menu-toggle" id="adminMenuToggle" aria-label="Toggle menu">☰</button>
            <div class="admin-topbar-info">
                <span>Welcome, <strong><?= sanitize($_SESSION['admin_name'] ?? 'Admin') ?></strong></span>
            </div>
        </header>

        <div class="admin-content">
            <?php $flash = getFlash(); if ($flash): ?>
            <div class="alert alert-<?= sanitize($flash['type']) ?>">
                <?= sanitize($flash['message']) ?>
            </div>
            <?php endif; ?>
