<?php

// ─── Database ────────────────────────────────────────────────────────────────

function getDBConnection() {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    // Adjust these constants or use direct values if you haven't defined them yet
    $host = defined('DB_HOST') ? DB_HOST : 'localhost';
    $name = defined('DB_NAME') ? DB_NAME : 'neres_store';
    $user = defined('DB_USER') ? DB_USER : 'root';
    $pass = defined('DB_PASS') ? DB_PASS : '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$name;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        die('<h2 style="font-family:sans-serif;color:#c00">Database connection failed: '
            . htmlspecialchars($e->getMessage()) . '</h2>');
    }

    return $pdo;
}

// ─── Output / Security ───────────────────────────────────────────────────────

function sanitize($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header('Location: ' . $url);
    exit();
}

// ─── Session / Flash ─────────────────────────────────────────────────────────

function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (!isset($_SESSION['flash'])) return null;
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

// ─── Admin Auth ───────────────────────────────────────────────────────────────

function isAdminLoggedIn() {
    return isset($_SESSION['admin']) && $_SESSION['admin'] === true;
}

function requireAdmin() {
    if (!isAdminLoggedIn()) {
        redirect('login.php');
    }
}

function authenticateAdmin(PDO $db, $username, $password) {
    $stmt = $db->prepare('SELECT * FROM admins WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        return $admin;
    }
    return null;
}

function loginAdmin($admin) {
    session_regenerate_id(true);
    $_SESSION['admin']          = true;
    $_SESSION['admin_id']       = $admin['id'];
    $_SESSION['admin_name']     = $admin['name'] ?? $admin['username'];
    $_SESSION['admin_username'] = $admin['username'];
}

function logoutAdmin() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

// ─── Activity Log ────────────────────────────────────────────────────────────

function logActivity(PDO $db, $actionType, $description, $entityType = null, $entityId = null) {
    try {
        $stmt = $db->prepare('
            INSERT INTO activity_log (action_type, description, entity_type, entity_id, ip_address, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ');
        $stmt->execute([
            $actionType,
            $description,
            $entityType,
            $entityId,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    } catch (PDOException $e) {
        // Silently fail — logging should never break the app
    }
}

function getRecentActivities(PDO $db, $limit = 10) {
    try {
        $stmt = $db->prepare('
            SELECT * FROM activity_log
            ORDER BY created_at DESC
            LIMIT ?
        ');
        $stmt->bindValue(1, (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

function getActivityStats(PDO $db) {
    $stats = ['by_type' => [], 'daily' => []];
    try {
        $stmt = $db->query('
            SELECT action_type, COUNT(*) AS count
            FROM activity_log
            GROUP BY action_type
            ORDER BY count DESC
        ');
        $stats['by_type'] = $stmt->fetchAll();

        $stmt = $db->query('
            SELECT DATE(created_at) AS day, COUNT(*) AS count
            FROM activity_log
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE(created_at)
            ORDER BY day ASC
        ');
        $stats['daily'] = $stmt->fetchAll();
    } catch (PDOException $e) {
        // return empty stats
    }
    return $stats;
}

// ─── Dashboard Stats ─────────────────────────────────────────────────────────

function getDashboardStats(PDO $db) {
    $stats = [
        'total_products'   => 0,
        'total_orders'     => 0,
        'total_customers'  => 0,
        'total_revenue'    => 0,
        'orders_today'     => 0,
        'orders_week'      => 0,
        'orders_month'     => 0,
        'revenue_month'    => 0,
        'low_stock'        => 0,
        'ai_chats'         => 0,
        'ai_descriptions'  => 0,
        'orders_by_status' => [],
        'top_products'     => [],
        'recent_orders'    => [],
    ];

    try {
        $stats['total_products']  = (int) $db->query('SELECT COUNT(*) FROM products')->fetchColumn();
        $stats['total_orders']    = (int) $db->query('SELECT COUNT(*) FROM orders')->fetchColumn();
        $stats['total_customers'] = (int) $db->query('SELECT COUNT(*) FROM customers')->fetchColumn();
        $stats['total_revenue']   = (float) $db->query('SELECT COALESCE(SUM(total),0) FROM orders')->fetchColumn();

        $stats['orders_today'] = (int) $db->query(
            "SELECT COUNT(*) FROM orders WHERE DATE(created_at)=CURDATE()")->fetchColumn();
        $stats['orders_week']  = (int) $db->query(
            "SELECT COUNT(*) FROM orders WHERE created_at >= DATE_SUB(NOW(),INTERVAL 7 DAY)")->fetchColumn();
        $stats['orders_month'] = (int) $db->query(
            "SELECT COUNT(*) FROM orders WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")->fetchColumn();
        $stats['revenue_month'] = (float) $db->query(
            "SELECT COALESCE(SUM(total),0) FROM orders WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")->fetchColumn();

        $stats['low_stock'] = (int) $db->query(
            "SELECT COUNT(*) FROM products WHERE stock <= 5")->fetchColumn();

        // AI stats (silently skip if table doesn't exist)
        try {
            $stats['ai_chats']        = (int) $db->query("SELECT COUNT(*) FROM ai_chat_logs")->fetchColumn();
            $stats['ai_descriptions'] = (int) $db->query(
                "SELECT COUNT(*) FROM activity_log WHERE action_type='ai_description'")->fetchColumn();
        } catch (PDOException $e) {}

        $stmt = $db->query('SELECT status, COUNT(*) AS count FROM orders GROUP BY status');
        $stats['orders_by_status'] = $stmt->fetchAll();

        $stmt = $db->query('
            SELECT p.name AS product_name, SUM(oi.quantity) AS total_sold,
                   SUM(oi.quantity * oi.price) AS revenue
            FROM order_items oi
            JOIN products p ON p.id = oi.product_id
            GROUP BY oi.product_id
            ORDER BY total_sold DESC
            LIMIT 5
        ');
        $stats['top_products'] = $stmt->fetchAll();

        $stmt = $db->query('
            SELECT o.order_number, c.full_name, o.total, o.status
            FROM orders o
            JOIN customers c ON c.id = o.customer_id
            ORDER BY o.created_at DESC
            LIMIT 5
        ');
        $stats['recent_orders'] = $stmt->fetchAll();

    } catch (PDOException $e) {
        // return whatever we have so far
    }

    return $stats;
}

// ─── Products ────────────────────────────────────────────────────────────────

function getAllProductsAdmin(PDO $db) {
    try {
        $stmt = $db->query('
            SELECT p.*, c.name AS category_name
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            ORDER BY p.created_at DESC
        ');
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

function getProductById(PDO $db, $id) {
    $stmt = $db->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([(int) $id]);
    return $stmt->fetch() ?: null;
}

function createProduct(PDO $db, array $data) {
    $stmt = $db->prepare('
        INSERT INTO products (category_id, name, slug, description, price, image, stock, featured, created_at)
        VALUES (:category_id, :name, :slug, :description, :price, :image, :stock, :featured, NOW())
    ');
    $stmt->execute($data);
    return (int) $db->lastInsertId();
}

function updateProduct(PDO $db, $id, array $data) {
    $data['id'] = (int) $id;
    $stmt = $db->prepare('
        UPDATE products
        SET category_id = :category_id,
            name        = :name,
            slug        = :slug,
            description = :description,
            price       = :price,
            image       = :image,
            stock       = :stock,
            featured    = :featured
        WHERE id = :id
    ');
    return $stmt->execute($data);
}

function deleteProduct(PDO $db, $id) {
    $stmt = $db->prepare('DELETE FROM products WHERE id = ?');
    return $stmt->execute([(int) $id]);
}

function generateSlug($string) {
    $slug = strtolower(trim($string));
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    return trim($slug, '-');
}

function uniqueProductSlug(PDO $db, $slug, $excludeId = null) {
    $base  = $slug;
    $count = 1;
    while (true) {
        if ($excludeId) {
            $stmt = $db->prepare('SELECT id FROM products WHERE slug = ? AND id != ?');
            $stmt->execute([$slug, $excludeId]);
        } else {
            $stmt = $db->prepare('SELECT id FROM products WHERE slug = ?');
            $stmt->execute([$slug]);
        }
        if (!$stmt->fetch()) break;
        $slug = $base . '-' . $count++;
    }
    return $slug;
}

function getProductImage($filename) {
    $path = 'assets/images/products/' . $filename;
    if ($filename && $filename !== 'default.jpg' && file_exists(__DIR__ . '/../' . $path)) {
        return $path;
    }
    return 'assets/images/products/default.jpg';
}

function uploadProductImage(array $file) {
    $maxSize  = 5 * 1024 * 1024;
    $allowed  = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $ext_map  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Upload error code: ' . $file['error']);
    }
    if ($file['size'] > $maxSize) {
        throw new Exception('Image must be under 5 MB.');
    }

    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, $allowed, true)) {
        throw new Exception('Only JPG, PNG, WEBP, and GIF images are allowed.');
    }

    $ext      = $ext_map[$mime];
    $filename = uniqid('prod_', true) . '.' . $ext;
    $dest     = __DIR__ . '/../assets/images/products/' . $filename;

    if (!is_dir(dirname($dest))) {
        mkdir(dirname($dest), 0755, true);
    }
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new Exception('Failed to save image.');
    }

    return $filename;
}

// ─── Categories ──────────────────────────────────────────────────────────────

function getCategories(PDO $db) {
    try {
        return $db->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// ─── Formatting ──────────────────────────────────────────────────────────────

function formatPrice($amount) {
    return 'RWF ' . number_format((float) $amount, 0, '.', ',');
}