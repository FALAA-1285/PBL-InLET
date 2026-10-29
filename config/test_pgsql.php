<?php
/**
 * Test PostgreSQL Connection
 * Restricted to development mode with APP_DEBUG enabled.
 */
require_once __DIR__ . '/database.php';

if (defined('APP_DEBUG') && !APP_DEBUG) {
    http_response_code(403);
    die("Access Denied: Diagnostics disabled in production.");
}

try {
    $conn = getDBConnection();
    echo "✅ Connection successful to " . htmlspecialchars(DB_NAME) . " on " . htmlspecialchars(DB_HOST) . "!";
} catch (PDOException $e) {
    echo "❌ Error: " . htmlspecialchars($e->getMessage());
}
?>
