<?php

function initCart(): void
{
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

function getCart(): array
{
    initCart();
    return $_SESSION['cart'];
}

function getCartCount(): int
{
    $cart = getCart();
    $count = 0;
    foreach ($cart as $item) {
        $count += $item['quantity'];
    }
    return $count;
}

function getCartTotal(): float
{
    $cart = getCart();
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

function addToCart(array $product, int $quantity = 1): void
{
    initCart();
    $id = $product['id'];

    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$id] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => (float) $product['price'],
            'image' => $product['image'],
            'quantity' => $quantity,
            'stock' => $product['stock'],
        ];
    }

    if ($_SESSION['cart'][$id]['quantity'] > $product['stock']) {
        $_SESSION['cart'][$id]['quantity'] = $product['stock'];
    }
}

function updateCartItem(int $productId, int $quantity): bool
{
    initCart();

    if (!isset($_SESSION['cart'][$productId])) {
        return false;
    }

    if ($quantity <= 0) {
        unset($_SESSION['cart'][$productId]);
        return true;
    }

    if ($quantity > $_SESSION['cart'][$productId]['stock']) {
        $quantity = $_SESSION['cart'][$productId]['stock'];
    }

    $_SESSION['cart'][$productId]['quantity'] = $quantity;
    return true;
}

function removeFromCart(int $productId): void
{
    initCart();
    unset($_SESSION['cart'][$productId]);
}

function clearCart(): void
{
    $_SESSION['cart'] = [];
}
