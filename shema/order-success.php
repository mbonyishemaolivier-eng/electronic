<?php
require_once 'includes/init.php';

$orderNumber = $_GET['order'] ?? ($_SESSION['last_order'] ?? '');

if (empty($orderNumber)) {
    redirect('index.php');
}

$order = getOrderByNumber($db, $orderNumber);

if (!$order) {
    setFlash('error', 'Order not found.');
    redirect('index.php');
}

$pageTitle = 'Order Confirmed';
unset($_SESSION['last_order']);

require_once 'includes/header.php';
?>

<section class="section order-success-section">
    <div class="container">
        <div class="order-success">
            <div class="success-icon">✓</div>
            <h1>Order Confirmed!</h1>
            <p class="success-message">Thank you for shopping at neresStore. Your order has been placed successfully.</p>

            <div class="order-details-card">
                <div class="order-details-header">
                    <div>
                        <span class="order-label">Order Number</span>
                        <strong class="order-number"><?= sanitize($order['order_number']) ?></strong>
                    </div>
                    <div>
                        <span class="order-label">Date</span>
                        <strong><?= date('F j, Y g:i A', strtotime($order['created_at'])) ?></strong>
                    </div>
                    <div>
                        <span class="order-label">Status</span>
                        <span class="status-badge status-<?= sanitize($order['status']) ?>"><?= ucfirst(sanitize($order['status'])) ?></span>
                    </div>
                </div>

                <div class="order-details-body">
                    <div class="order-section">
                        <h3>Customer Information</h3>
                        <p><strong><?= sanitize($order['full_name']) ?></strong></p>
                        <p><?= sanitize($order['email']) ?></p>
                        <p><?= sanitize($order['phone']) ?></p>
                        <p><?= sanitize($order['address']) ?>, <?= sanitize($order['city']) ?></p>
                    </div>

                    <div class="order-section">
                        <h3>Order Items</h3>
                        <table class="order-items-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order['items'] as $item): ?>
                                <tr>
                                    <td><?= sanitize($item['product_name']) ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td><?= formatPrice((float) $item['unit_price']) ?></td>
                                    <td><?= formatPrice((float) $item['total_price']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="order-totals">
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span><?= formatPrice((float) $order['subtotal']) ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span><?= formatPrice((float) $order['shipping']) ?></span>
                        </div>
                        <div class="summary-row summary-total">
                            <span>Total Paid</span>
                            <span><?= formatPrice((float) $order['total']) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="success-actions">
                <a href="products.php" class="btn btn-primary btn-lg">Continue Shopping</a>
                <a href="index.php" class="btn btn-outline btn-lg">Back to Home</a>
            </div>

            <p class="delivery-note">📦 Your order will be delivered within 2-5 business days. We'll contact you at <?= sanitize($order['phone']) ?> for confirmation.</p>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
