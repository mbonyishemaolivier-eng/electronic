<?php

function isAdminLoggedIn(): bool
{
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireAdmin(): void
{
    if (!isAdminLoggedIn()) {
        setFlash('error', 'Please login to access the admin panel.');
        redirect('login.php');
    }
}

function getAdmin(PDO $db, int $id): ?array
{
    $stmt = $db->prepare('SELECT id, username, full_name, created_at FROM admins WHERE id = ?');
    $stmt->execute([$id]);
    $result = $stmt->fetch();
    return $result ?: null;
}

function authenticateAdmin(PDO $db, string $username, string $password): ?array
{
    $stmt = $db->prepare('SELECT * FROM admins WHERE username = ?');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        return $admin;
    }

    return null;
}

function loginAdmin(array $admin): void
{
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_name'] = $admin['full_name'];
}

function logoutAdmin(): void
{
    unset($_SESSION['admin_id'], $_SESSION['admin_username'], $_SESSION['admin_name']);
}
