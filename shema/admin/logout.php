<?php
require_once 'includes/init.php';

if (isAdminLoggedIn()) {
    logActivity($db, 'admin_logout', 'Admin "' . $_SESSION['admin_username'] . '" logged out', 'admin', (int) $_SESSION['admin_id']);
}

logoutAdmin();
redirect('login.php');
