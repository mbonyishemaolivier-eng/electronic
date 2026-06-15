<?php
require_once 'includes/init.php';
require_once 'includes/activity.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$action = $_POST['action'] ?? '';
$productId = (int) ($_POST['product_id'] ?? 0);
$quantity = max(1, (int) ($_POST['quantity'] ?? 1));
$redirectUrl = $_POST['redirect'] ?? 'cart.php';

switch ($action) {
    case 'add':
        $product = getProductById($db, $productId);
        if ($product && $product['stock'] > 0) {
            addToCart($product, $quantity);
            logActivity($db, 'cart_add', 'Added "' . $product['name'] . '" (x' . $quantity . ') to cart', 'product', $productId);
            setFlash('success', $product['name'] . ' added to cart!');
        } else {
            setFlash('error', 'Product unavailable or out of stock.');
        }
        break;

    case 'update':
        if (updateCartItem($productId, $quantity)) {
            logActivity($db, 'cart_update', 'Updated cart item #' . $productId . ' to qty ' . $quantity, 'product', $productId);
            setFlash('success', 'Cart updated successfully.');
        } else {
            setFlash('error', 'Could not update cart item.');
        }
        break;

    case 'remove':
        removeFromCart($productId);
        logActivity($db, 'cart_remove', 'Removed product #' . $productId . ' from cart', 'product', $productId);
        setFlash('success', 'Item removed from cart.');
        break;

    case 'clear':
        clearCart();
        logActivity($db, 'cart_clear', 'Shopping cart cleared');
        setFlash('success', 'Cart cleared.');
        break;

    default:
        setFlash('error', 'Invalid action.');
}

redirect($redirectUrl);
