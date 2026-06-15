<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';

function formatPrice(float $price): string
{
    return number_format($price, 0, '.', ',') . ' ' . CURRENCY;
}

function sanitize(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function generateOrderNumber(): string
{
    return 'NS-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

function getCategories(PDO $db): array
{
    $stmt = $db->query('SELECT * FROM categories ORDER BY name');
    return $stmt->fetchAll();
}

function getCategoryBySlug(PDO $db, string $slug): ?array
{
    $stmt = $db->prepare('SELECT * FROM categories WHERE slug = ?');
    $stmt->execute([$slug]);
    $result = $stmt->fetch();
    return $result ?: null;
}

function getProducts(PDO $db, ?int $categoryId = null, ?string $search = null, int $limit = 0): array
{
    $sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.stock > 0';
    $params = [];

    if ($categoryId) {
        $sql .= ' AND p.category_id = ?';
        $params[] = $categoryId;
    }

    if ($search) {
        $sql .= ' AND (p.name LIKE ? OR p.description LIKE ?)';
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $sql .= ' ORDER BY p.featured DESC, p.name ASC';

    if ($limit > 0) {
        $sql .= ' LIMIT ' . (int) $limit;
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getProductById(PDO $db, int $id): ?array
{
    $stmt = $db->prepare(
        'SELECT p.*, c.name AS category_name, c.slug AS category_slug
         FROM products p
         JOIN categories c ON p.category_id = c.id
         WHERE p.id = ?'
    );
    $stmt->execute([$id]);
    $result = $stmt->fetch();
    return $result ?: null;
}

function getFeaturedProducts(PDO $db, int $limit = 6): array
{
    return getProducts($db, null, null, $limit);
}

function getProductImage(string $image): string
{
    $path = 'admin/assets/images/products/' . $image;
    if (file_exists(__DIR__ . '/../' . $path)) {
        return $path;
    }
    return 'admin/assets/images/placeholder.svg';
}

function createCustomer(PDO $db, array $data): int
{
    $stmt = $db->prepare(
        'INSERT INTO customers (full_name, email, phone, address, city)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $data['full_name'],
        $data['email'],
        $data['phone'],
        $data['address'],
        $data['city'],
    ]);
    return (int) $db->lastInsertId();
}

function createOrder(PDO $db, int $customerId, array $cart, float $subtotal, float $shipping, float $total, ?string $notes = null): array
{
    $orderNumber = generateOrderNumber();

    $db->beginTransaction();

    try {
        $stmt = $db->prepare(
            'INSERT INTO orders (order_number, customer_id, subtotal, shipping, total, notes)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$orderNumber, $customerId, $subtotal, $shipping, $total, $notes]);
        $orderId = (int) $db->lastInsertId();

        $itemStmt = $db->prepare(
            'INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, total_price)
             VALUES (?, ?, ?, ?, ?, ?)'
        );

        $stockStmt = $db->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');

        foreach ($cart as $item) {
            $itemTotal = $item['price'] * $item['quantity'];
            $itemStmt->execute([
                $orderId,
                $item['id'],
                $item['name'],
                $item['quantity'],
                $item['price'],
                $itemTotal,
            ]);

            $stockStmt->execute([$item['quantity'], $item['id'], $item['quantity']]);
            if ($stockStmt->rowCount() === 0) {
                throw new Exception('Insufficient stock for ' . $item['name']);
            }
        }

        $db->commit();

        return [
            'id' => $orderId,
            'order_number' => $orderNumber,
        ];
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

function getOrderByNumber(PDO $db, string $orderNumber): ?array
{
    $stmt = $db->prepare(
        'SELECT o.*, c.full_name, c.email, c.phone, c.address, c.city
         FROM orders o
         JOIN customers c ON o.customer_id = c.id
         WHERE o.order_number = ?'
    );
    $stmt->execute([$orderNumber]);
    $order = $stmt->fetch();

    if (!$order) {
        return null;
    }

    $itemStmt = $db->prepare('SELECT * FROM order_items WHERE order_id = ?');
    $itemStmt->execute([$order['id']]);
    $order['items'] = $itemStmt->fetchAll();

    return $order;
}
