<?php

function getClientIp(): string
{
    return $_SERVER['HTTP_X_FORWARDED_FOR']
        ?? $_SERVER['HTTP_CLIENT_IP']
        ?? $_SERVER['REMOTE_ADDR']
        ?? 'unknown';
}

function logActivity(PDO $db, string $actionType, string $description, ?string $entityType = null, ?int $entityId = null): void
{
    try {
        $stmt = $db->prepare(
            'INSERT INTO activity_logs (action_type, description, entity_type, entity_id, ip_address, user_agent)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $actionType,
            $description,
            $entityType,
            $entityId,
            getClientIp(),
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]);
    } catch (Exception $e) {
        error_log('Activity log failed: ' . $e->getMessage());
    }
}

function getRecentActivities(PDO $db, int $limit = 20): array
{
    $stmt = $db->prepare('SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT ?');
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getActivityStats(PDO $db): array
{
    $stats = [];

    $stmt = $db->query(
        "SELECT action_type, COUNT(*) AS count
         FROM activity_logs
         GROUP BY action_type
         ORDER BY count DESC"
    );
    $stats['by_type'] = $stmt->fetchAll();

    $stmt = $db->query(
        "SELECT DATE(created_at) AS day, COUNT(*) AS count
         FROM activity_logs
         WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
         GROUP BY DATE(created_at)
         ORDER BY day ASC"
    );
    $stats['daily'] = $stmt->fetchAll();

    return $stats;
}
