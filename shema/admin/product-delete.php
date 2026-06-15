<?php
require_once 'includes/init.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('products.php');
}

$productId = (int) ($_POST['id'] ?? 0);
$product = getProductById($db, $productId);

if (!$product) {
    setFlash('error', 'Product not found.');
    redirect('products.php');
}

if (deleteProduct($db, $productId)) {
    $imagePath = __DIR__ . '/../assets/images/products/' . $product['image'];
    if ($product['image'] !== 'default.jpg' && file_exists($imagePath)) {
        @unlink($imagePath);
    }

    logActivity($db, 'product_deleted', 'Deleted product: ' . $product['name'], 'product', $productId);
    setFlash('success', 'Product "' . $product['name'] . '" deleted successfully.');
} else {
    setFlash('error', 'Failed to delete product.');
}

redirect('products.php');
