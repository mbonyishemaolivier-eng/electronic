<?php

// Start session only if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load app config (DB credentials, APP_NAME, etc.) if it exists
$configPath = __DIR__ . '/../includes/config.php';
if (file_exists($configPath)) {
    require_once $configPath;
}

// Load all shared helper functions
require_once __DIR__ . '/../includes/functions.php';

// Open a database connection — available as $db everywhere
$db = getDBConnection();