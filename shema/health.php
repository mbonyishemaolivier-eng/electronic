<?php
header('Content-Type: application/json');

$status = ['status' => 'ok', 'app' => 'neresStore', 'timestamp' => date('c')];

try {
    require_once __DIR__ . '/config/database.php';
    $pdo = getDBConnection();
    $pdo->query('SELECT 1');
    $status['database'] = 'connected';
} catch (Exception $e) {
    http_response_code(503);
    $status['status'] = 'error';
    $status['database'] = 'disconnected';
}

echo json_encode($status);
