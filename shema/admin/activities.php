<?php
require_once 'includes/init.php';
requireAdmin();

$pageTitle = 'Activity Log';
$activities = getRecentActivities($db, 100);
$activityStats = getActivityStats($db);

require_once 'includes/header.php';
?>

<div class="admin-page-header">
    <h1>Activity Log</h1>
    <p>All store and admin activities</p>
</div>

<?php if (!empty($activityStats['by_type'])): ?>
<div class="admin-card">
    <div class="admin-card-header"><h2>Activity Summary</h2></div>
    <div class="admin-card-body">
        <div class="activity-summary-grid">
            <?php foreach ($activityStats['by_type'] as $type): ?>
            <div class="activity-summary-item">
                <span class="activity-type type-<?= sanitize($type['action_type']) ?>">
                    <?= sanitize(str_replace('_', ' ', $type['action_type'])) ?>
                </span>
                <strong><?= $type['count'] ?></strong>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="admin-card">
    <div class="admin-card-body">
        <?php if (empty($activities)): ?>
        <p class="text-muted">No activity recorded yet.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activities as $activity): ?>
                    <tr>
                        <td><?= date('M j, Y g:i A', strtotime($activity['created_at'])) ?></td>
                        <td>
                            <span class="activity-type type-<?= sanitize($activity['action_type']) ?>">
                                <?= sanitize(str_replace('_', ' ', $activity['action_type'])) ?>
                            </span>
                        </td>
                        <td><?= sanitize($activity['description']) ?></td>
                        <td><code><?= sanitize($activity['ip_address'] ?? '-') ?></code></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
