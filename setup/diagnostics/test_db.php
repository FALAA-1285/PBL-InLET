<?php
/**
 * Test Database Connection
 * Diagnostic file for development use only.
 */

require_once __DIR__ . '/../../config/database.php';

if (defined('APP_DEBUG') && !APP_DEBUG) {
    http_response_code(403);
    die("Access Denied: Diagnostics disabled in production.");
}

echo "<h2>Testing Database Connection</h2>";
echo "<hr>";

try {
    $conn = getDBConnection();
    echo "<p style='color: green;'>✅ <strong>Database connection successful!</strong></p>";

    // Test query
    $stmt = $conn->query("SELECT version()");
    $version = $stmt->fetch();
    echo "<p><strong>PostgreSQL Version:</strong> " . htmlspecialchars($version['version']) . "</p>";

    // Check database name
    $stmt = $conn->query("SELECT current_database()");
    $db_name = $stmt->fetch();
    echo "<p><strong>Database Name:</strong> " . htmlspecialchars($db_name['current_database']) . "</p>";

    // Check if tables exist
    $stmt = $conn->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name");
    $tables = $stmt->fetchAll();

    echo "<hr>";
    echo "<h3>Daftar Tabel di Database:</h3>";

    if (empty($tables)) {
        echo "<p style='color: orange;'>⚠️ <strong>Belum ada tabel!</strong> Import schema database terlebih dahulu.</p>";
    } else {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>" . htmlspecialchars($table['table_name']) . "</li>";
        }
        echo "</ul>";
        echo "<p style='color: green;'>✅ <strong>Total " . count($tables) . " tabel ditemukan</strong></p>";
    }

    // Check admin accounts
    try {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM admin");
        $admin_count = $stmt->fetch();
        echo "<hr>";
        echo "<h3>Status Setup Admin:</h3>";
        echo "<p><strong>Total Admin:</strong> " . $admin_count['count'] . "</p>";

        if ($admin_count['count'] > 0) {
            $stmt = $conn->query("SELECT username, role FROM admin");
            $admins = $stmt->fetchAll();
            echo "<ul>";
            foreach ($admins as $adm) {
                echo "<li>Username: <strong>" . htmlspecialchars($adm['username']) . "</strong> (Role: " . htmlspecialchars($adm['role']) . ")</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: orange;'>⚠️ <strong>Belum ada admin!</strong></p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Error checking admin: " . htmlspecialchars($e->getMessage()) . "</p>";
    }

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ <strong>Database connection failed!</strong></p>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Kembali ke Homepage</a> | <a href='login.php'>Login</a></p>";
?>
