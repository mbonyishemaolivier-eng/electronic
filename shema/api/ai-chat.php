<?php

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/activity.php';
require_once __DIR__ . '/../includes/ai_assistant.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? $_POST['message'] ?? '');

if (strlen($message) > 500) {
    http_response_code(400);
    echo json_encode(['error' => 'Message too long']);
    exit;
}

$result = processAIChat($db, $message);

logActivity($db, 'ai_chat', 'AI query: ' . substr($message, 0, 100));

echo json_encode([
    'reply' => $result['reply'],
    'products' => formatAIProductCards($result['products']),
]);
