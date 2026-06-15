<?php
require_once 'includes/init.php';

if (isAdminLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password.';
    } else {
        $admin = authenticateAdmin($db, $username, $password);

        if ($admin) {
            loginAdmin($admin);
            logActivity($db, 'admin_login', 'Admin "' . $admin['username'] . '" logged in', 'admin', (int) $admin['id']);
            redirect('index.php');
        } else {
            logActivity($db, 'login_failed', 'Failed login attempt for username: ' . $username);
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <span class="logo-icon">⚡</span>
                <h1>neres<span class="logo-highlight">Store</span></h1>
                <p>Admin Panel Login</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-error"><?= sanitize($error) ?></div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autofocus
                           value="<?= sanitize($_POST['username'] ?? '') ?>" placeholder="Enter username">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter password">
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-block">Login to Admin</button>
            </form>

            <div class="login-footer">
                <a href="../index.php">← Back to Store</a>
            </div>
        </div>
    </div>
</body>
</html>
