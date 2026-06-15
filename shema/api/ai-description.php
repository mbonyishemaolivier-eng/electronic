<?php

header('Content-Type: application/json');

session_start();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/ai_recommendations.php';
require_once __DIR__ . '/../includes/activity.php';

if (!isAdminLoggedIn()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$db = getDBConnection();

$name = trim($input['name'] ?? '');
$categoryId = (int) ($input['category_id'] ?? 0);
$price = (float) ($input['price'] ?? 0);

if (empty($name) || $categoryId <= 0 || $price <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Name, category, and price are required to generate a description.']);
    exit;
}

$stmt = $db->prepare('SELECT name FROM categories WHERE id = ?');
$stmt->execute([$categoryId]);
$category = $stmt->fetch();

if (!$category) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid category']);
    exit;
}

$description = generateAIDescription($name, $category['name'], $price);
logActivity($db, 'ai_description', 'AI generated description for: ' . $name);

echo json_encode(['description' => $description]);
