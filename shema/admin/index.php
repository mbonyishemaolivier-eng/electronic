<?php
require_once 'includes/init.php';
requireAdmin();

$pageTitle = 'Dashboard';
$stats = getDashboardStats($db);
$activities = getRecentActivities($db, 10);
$activityStats = getActivityStats($db);

require_once 'includes/header.php';
?>

<div class="admin-page-header">
    <h1>Dashboard</h1>
    <p>Store overview and statistics</p>
</div>

<div class="stats-grid">
    <div class="stat-card stat-primary">
        <div class="stat-icon">📦</div>
        <div class="stat-info">
            <span class="stat-value"><?= $stats['total_products'] ?></span>
            <span class="stat-label">Total Products</span>
        </div>
    </div>
    <div class="stat-card stat-success">
        <div class="stat-icon">🛒</div>
        <div class="stat-info">
            <span class="stat-value"><?= $stats['total_orders'] ?></span>
            <span class="stat-label">Total Orders</span>
        </div>
    </div>
    <div class="stat-card stat-info">
        <div class="stat-icon">👥</div>
        <div class="stat-info">
            <span class="stat-value"><?= $stats['total_customers'] ?></span>
            <span class="stat-label">Customers</span>
        </div>
    </div>
    <div class="stat-card stat-warning">
        <div class="stat-icon">💰</div>
        <div class="stat-info">
            <span class="stat-value"><?= formatPrice($stats['total_revenue']) ?></span>
            <span class="stat-label">Total Revenue</span>
        </div>
    </div>
</div>

<div class="stats-grid stats-grid-sm">
    <div class="stat-card-mini">
        <span class="stat-value"><?= $stats['orders_today'] ?></span>
        <span class="stat-label">Orders Today</span>
    </div>
    <div class="stat-card-mini">
        <span class="stat-value"><?= $stats['orders_week'] ?></span>
        <span class="stat-label">This Week</span>
    </div>
    <div class="stat-card-mini">
        <span class="stat-value"><?= $stats['orders_month'] ?></span>
        <span class="stat-label">This Month</span>
    </div>
    <div class="stat-card-mini">
        <span class="stat-value"><?= formatPrice($stats['revenue_month']) ?></span>
        <span class="stat-label">Monthly Revenue</span>
    </div>
    <div class="stat-card-mini stat-danger">
        <span class="stat-value"><?= $stats['low_stock'] ?></span>
        <span class="stat-label">Low Stock Items</span>
    </div>
    <div class="stat-card-mini" style="border-left: 3px solid #7c3aed;">
        <span class="stat-value"><?= $stats['ai_chats'] ?></span>
        <span class="stat-label">🤖 AI Chats</span>
    </div>
    <div class="stat-card-mini" style="border-left: 3px solid #7c3aed;">
        <span class="stat-value"><?= $stats['ai_descriptions'] ?></span>
        <span class="stat-label">🤖 AI Descriptions</span>
    </div>
</div>

<div class="admin-grid-2">
    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Orders by Status</h2>
        </div>
        <div class="admin-card-body">
            <?php if (empty($stats['orders_by_status'])): ?>
            <p class="text-muted">No orders yet.</p>
            <?php else: ?>
            <div class="status-bars">
                <?php
                $maxCount = max(array_column($stats['orders_by_status'], 'count')) ?: 1;
                foreach ($stats['orders_by_status'] as $status):
                    $percent = ($status['count'] / $maxCount) * 100;
                ?>
                <div class="status-bar-row">
                    <span class="status-label"><?= ucfirst(sanitize($status['status'])) ?></span>
                    <div class="status-bar-track">
                        <div class="status-bar-fill status-<?= sanitize($status['status']) ?>" style="width: <?= $percent ?>%"></div>
                    </div>
                    <span class="status-count"><?= $status['count'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Top Selling Products</h2>
        </div>
        <div class="admin-card-body">
            <?php if (empty($stats['top_products'])): ?>
            <p class="text-muted">No sales data yet.</p>
            <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr><th>Product</th><th>Sold</th><th>Revenue</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['top_products'] as $product): ?>
                    <tr>
                        <td><?= sanitize($product['product_name']) ?></td>
                        <td><?= $product['total_sold'] ?></td>
                        <td><?= formatPrice((float) $product['revenue']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="admin-grid-2">
    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Recent Orders</h2>
            <a href="products.php" class="btn btn-sm btn-outline">Manage Products</a>
        </div>
        <div class="admin-card-body">
            <?php if (empty($stats['recent_orders'])): ?>
            <p class="text-muted">No orders yet.</p>
            <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['recent_orders'] as $order): ?>
                    <tr>
                        <td><strong><?= sanitize($order['order_number']) ?></strong></td>
                        <td><?= sanitize($order['full_name']) ?></td>
                        <td><?= formatPrice((float) $order['total']) ?></td>
                        <td><span class="badge badge-<?= sanitize($order['status']) ?>"><?= ucfirst(sanitize($order['status'])) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Recent Activity</h2>
            <a href="activities.php" class="btn btn-sm btn-outline">View All</a>
        </div>
        <div class="admin-card-body">
            <?php if (empty($activities)): ?>
            <p class="text-muted">No activity recorded yet.</p>
            <?php else: ?>
            <ul class="activity-list">
                <?php foreach ($activities as $activity): ?>
                <li class="activity-item">
                    <span class="activity-type type-<?= sanitize($activity['action_type']) ?>">
                        <?= sanitize(str_replace('_', ' ', $activity['action_type'])) ?>
                    </span>
                    <p><?= sanitize($activity['description']) ?></p>
                    <small><?= date('M j, Y g:i A', strtotime($activity['created_at'])) ?></small>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($activityStats['daily'])): ?>
<div class="admin-card">
    <div class="admin-card-header">
        <h2>Activity (Last 7 Days)</h2>
    </div>
    <div class="admin-card-body">
        <div class="activity-chart">
            <?php
            $maxDaily = max(array_column($activityStats['daily'], 'count')) ?: 1;
            foreach ($activityStats['daily'] as $day):
                $height = ($day['count'] / $maxDaily) * 100;
            ?>
            <div class="chart-bar-col">
                <div class="chart-bar" style="height: <?= max($height, 5) ?>%" title="<?= $day['count'] ?> activities"></div>
                <span class="chart-label"><?= date('D', strtotime($day['day'])) ?></span>
                <span class="chart-value"><?= $day['count'] ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
