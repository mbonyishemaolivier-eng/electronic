<?php
require_once 'includes/init.php';

$pageTitle = 'Shopping Cart';
$cart = getCart();
$subtotal = getCartTotal();
$shipping = $subtotal > 0 ? SHIPPING_COST : 0;
$total = $subtotal + $shipping;

require_once 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Shopping Cart</h1>
        <p><?= getCartCount() ?> item<?= getCartCount() !== 1 ? 's' : '' ?> in your cart</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if (empty($cart)): ?>
        <div class="empty-state">
            <span class="empty-icon">🛒</span>
            <h2>Your cart is empty</h2>
            <p>Looks like you haven't added any products yet.</p>
            <a href="products.php" class="btn btn-primary btn-lg">Start Shopping</a>
        </div>
        <?php else: ?>
        <div class="cart-layout">
            <div class="cart-items">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart as $item): ?>
                        <tr>
                            <td class="cart-product">
                                <img src="<?= getProductImage($item['image']) ?>" alt="<?= sanitize($item['name']) ?>">
                                <span><?= sanitize($item['name']) ?></span>
                            </td>
                            <td><?= formatPrice((float) $item['price']) ?></td>
                            <td>
                                <form action="cart-action.php" method="POST" class="cart-qty-form">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                    <input type="hidden" name="redirect" value="cart.php">
                                    <div class="quantity-controls">
                                        <button type="button" class="qty-btn" data-action="decrease">−</button>
                                        <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>" class="qty-input">
                                        <button type="button" class="qty-btn" data-action="increase">+</button>
                                    </div>
                                </form>
                            </td>
                            <td class="cart-item-total"><?= formatPrice($item['price'] * $item['quantity']) ?></td>
                            <td>
                                <form action="cart-action.php" method="POST">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                    <input type="hidden" name="redirect" value="cart.php">
                                    <button type="submit" class="btn-remove" title="Remove item">✕</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="cart-actions">
                    <a href="products.php" class="btn btn-outline">← Continue Shopping</a>
                    <form action="cart-action.php" method="POST">
                        <input type="hidden" name="action" value="clear">
                        <input type="hidden" name="redirect" value="cart.php">
                        <button type="submit" class="btn btn-outline btn-danger">Clear Cart</button>
                    </form>
                </div>
            </div>

            <aside class="cart-summary">
                <h3>Order Summary</h3>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span><?= formatPrice($subtotal) ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span><?= formatPrice($shipping) ?></span>
                </div>
                <div class="summary-row summary-total">
                    <span>Total</span>
                    <span><?= formatPrice($total) ?></span>
                </div>
                <a href="checkout.php" class="btn btn-primary btn-lg btn-block">Proceed to Checkout</a>
            </aside>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
