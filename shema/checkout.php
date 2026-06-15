<?php
require_once 'includes/init.php';
require_once 'includes/activity.php';

$pageTitle = 'Checkout';
$cart = getCart();

if (empty($cart)) {
    setFlash('error', 'Your cart is empty. Add products before checkout.');
    redirect('products.php');
}

$subtotal = getCartTotal();
$shipping = SHIPPING_COST;
$total = $subtotal + $shipping;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? 'Kigali');
    $notes = trim($_POST['notes'] ?? '');

    if (empty($fullName)) $errors[] = 'Full name is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (empty($phone)) $errors[] = 'Phone number is required.';
    if (empty($address)) $errors[] = 'Delivery address is required.';
    if (empty($city)) $errors[] = 'City is required.';

    if (empty($errors)) {
        try {
            $customerId = createCustomer($db, [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'city' => $city,
            ]);

            $order = createOrder($db, $customerId, $cart, $subtotal, $shipping, $total, $notes ?: null);
            logActivity($db, 'order_placed', 'Order ' . $order['order_number'] . ' placed by ' . $fullName . ' (' . formatPrice($total) . ')', 'order', $order['id']);
            clearCart();

            $_SESSION['last_order'] = $order['order_number'];
            redirect('order-success.php?order=' . urlencode($order['order_number']));
        } catch (Exception $e) {
            $errors[] = 'Order failed: ' . $e->getMessage();
        }
    }
}

require_once 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1>Checkout</h1>
        <p>Complete your order details below</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                <li><?= sanitize($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="checkout-layout">
            <div class="checkout-form-section">
                <h2>Customer Details</h2>
                <form method="POST" class="checkout-form">
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" required
                               value="<?= sanitize($_POST['full_name'] ?? '') ?>" placeholder="Jean Baptiste">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" required
                                   value="<?= sanitize($_POST['email'] ?? '') ?>" placeholder="you@email.com">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" required
                                   value="<?= sanitize($_POST['phone'] ?? '') ?>" placeholder="+250 7XX XXX XXX">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address">Delivery Address *</label>
                        <textarea id="address" name="address" required rows="3"
                                  placeholder="Street, district, landmark..."><?= sanitize($_POST['address'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="city">City *</label>
                        <select id="city" name="city" required>
                            <?php
                            $cities = ['Kigali', 'Huye', 'Musanze', 'Rubavu', 'Muhanga', 'Nyagatare', 'Rwamagana'];
                            $selectedCity = $_POST['city'] ?? 'Kigali';
                            foreach ($cities as $c):
                            ?>
                            <option value="<?= $c ?>" <?= $selectedCity === $c ? 'selected' : '' ?>><?= $c ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="notes">Order Notes (optional)</label>
                        <textarea id="notes" name="notes" rows="2"
                                  placeholder="Special delivery instructions..."><?= sanitize($_POST['notes'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block">Place Order</button>
                </form>
            </div>

            <aside class="checkout-summary">
                <h2>Order Summary</h2>
                <div class="checkout-items">
                    <?php foreach ($cart as $item): ?>
                    <div class="checkout-item">
                        <img src="<?= getProductImage($item['image']) ?>" alt="<?= sanitize($item['name']) ?>">
                        <div class="checkout-item-info">
                            <span class="checkout-item-name"><?= sanitize($item['name']) ?></span>
                            <span class="checkout-item-qty">Qty: <?= $item['quantity'] ?></span>
                        </div>
                        <span class="checkout-item-price"><?= formatPrice($item['price'] * $item['quantity']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-divider"></div>

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
            </aside>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
