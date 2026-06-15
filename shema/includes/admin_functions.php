<?php

require_once __DIR__ . '/activity.php';

function getDashboardStats(PDO $db): array
{
    $stats = [];

    $stats['total_products'] = (int) $db->query('SELECT COUNT(*) FROM products')->fetchColumn();
    $stats['total_orders'] = (int) $db->query('SELECT COUNT(*) FROM orders')->fetchColumn();
    $stats['total_customers'] = (int) $db->query('SELECT COUNT(*) FROM customers')->fetchColumn();
    $stats['total_revenue'] = (float) $db->query('SELECT COALESCE(SUM(total), 0) FROM orders')->fetchColumn();

    $stats['orders_today'] = (int) $db->query(
        "SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()"
    )->fetchColumn();

    $stats['orders_week'] = (int) $db->query(
        "SELECT COUNT(*) FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)"
    )->fetchColumn();

    $stats['orders_month'] = (int) $db->query(
        "SELECT COUNT(*) FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"
    )->fetchColumn();

    $stats['revenue_month'] = (float) $db->query(
        "SELECT COALESCE(SUM(total), 0) FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"
    )->fetchColumn();

    $stats['low_stock'] = (int) $db->query(
        'SELECT COUNT(*) FROM products WHERE stock <= 5'
    )->fetchColumn();

    $stats['ai_chats'] = (int) $db->query(
        "SELECT COUNT(*) FROM activity_logs WHERE action_type = 'ai_chat'"
    )->fetchColumn();

    $stats['ai_descriptions'] = (int) $db->query(
        "SELECT COUNT(*) FROM activity_logs WHERE action_type = 'ai_description'"
    )->fetchColumn();

    $stmt = $db->query(
        "SELECT status, COUNT(*) AS count FROM orders GROUP BY status"
    );
    $stats['orders_by_status'] = $stmt->fetchAll();

    $stmt = $db->query(
        "SELECT o.order_number, o.total, o.status, o.created_at, c.full_name
         FROM orders o
         JOIN customers c ON o.customer_id = c.id
         ORDER BY o.created_at DESC LIMIT 5"
    );
    $stats['recent_orders'] = $stmt->fetchAll();

    $stmt = $db->query(
        "SELECT oi.product_name, SUM(oi.quantity) AS total_sold, SUM(oi.total_price) AS revenue
         FROM order_items oi
         GROUP BY oi.product_id, oi.product_name
         ORDER BY total_sold DESC LIMIT 5"
    );
    $stats['top_products'] = $stmt->fetchAll();

    return $stats;
}

function getAllProductsAdmin(PDO $db): array
{
    $stmt = $db->query(
        'SELECT p.*, c.name AS category_name
         FROM products p
         JOIN categories c ON p.category_id = c.id
         ORDER BY p.created_at DESC'
    );
    return $stmt->fetchAll();
}

function generateSlug(string $text): string
{
    $slug = strtolower(trim($text));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    return trim($slug, '-');
}

function uploadProductImage(array $file): ?string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed, true)) {
        throw new Exception('Invalid image type. Use JPG, PNG, WEBP, or GIF.');
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('Image must be smaller than 5MB.');
    }

    $ext = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        default => 'jpg',
    };

    $filename = 'product-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destination = __DIR__ . '/../assets/images/products/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception('Failed to upload image.');
    }

    return $filename;
}

function createProduct(PDO $db, array $data): int
{
    $stmt = $db->prepare(
        'INSERT INTO products (category_id, name, slug, description, price, image, stock, featured)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $data['category_id'],
        $data['name'],
        $data['slug'],
        $data['description'],
        $data['price'],
        $data['image'],
        $data['stock'],
        $data['featured'],
    ]);
    return (int) $db->lastInsertId();
}

function updateProduct(PDO $db, int $id, array $data): bool
{
    $stmt = $db->prepare(
        'UPDATE products SET category_id = ?, name = ?, slug = ?, description = ?,
         price = ?, image = ?, stock = ?, featured = ? WHERE id = ?'
    );
    return $stmt->execute([
        $data['category_id'],
        $data['name'],
        $data['slug'],
        $data['description'],
        $data['price'],
        $data['image'],
        $data['stock'],
        $data['featured'],
        $id,
    ]);
}

function deleteProduct(PDO $db, int $id): bool
{
    $stmt = $db->prepare('DELETE FROM products WHERE id = ?');
    return $stmt->execute([$id]);
}

function uniqueProductSlug(PDO $db, string $slug, ?int $excludeId = null): string
{
    $baseSlug = $slug;
    $counter = 1;

    while (true) {
        $sql = 'SELECT id FROM products WHERE slug = ?';
        $params = [$slug];

        if ($excludeId) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        if (!$stmt->fetch()) {
            return $slug;
        }

        $slug = $baseSlug . '-' . $counter;
        $counter++;
    }
}
