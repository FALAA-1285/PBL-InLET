<?php
/**
 * Test Database Joins & Views
 * Diagnostic file for development use only.
 */

require_once __DIR__ . '/config/database.php';

if (defined('APP_DEBUG') && !APP_DEBUG) {
    http_response_code(403);
    die("Access Denied: Diagnostics disabled in production.");
}

echo "<h2>Testing Database Joins & Views</h2>";
echo "<hr>";

try {
    $conn = getDBConnection();

    // 1. Test view_alat_dipinjam
    echo "<h3>1. Test view_alat_dipinjam:</h3>";
    $stmt = $conn->query("SELECT COUNT(*) as count FROM view_alat_dipinjam");
    $row = $stmt->fetch();
    echo "<p>Total alat sedang dipinjam: " . htmlspecialchars($row['count']) . "</p>";

    // 2. Test view_alat_tersedia
    echo "<h3>2. Test view_alat_tersedia:</h3>";
    $stmt = $conn->query("SELECT id_alat_lab, nama_alat, stock, stok_tersedia FROM view_alat_tersedia LIMIT 5");
    $alat_list = $stmt->fetchAll();
    echo "<ul>";
    foreach ($alat_list as $alat) {
        echo "<li>" . htmlspecialchars($alat['nama_alat']) . " (Stok: " . htmlspecialchars($alat['stock']) . ", Tersedia: " . htmlspecialchars($alat['stok_tersedia']) . ")</li>";
    }
    echo "</ul>";

    // 3. Test view_ruang_dipinjam
    echo "<h3>3. Test view_ruang_dipinjam:</h3>";
    $stmt = $conn->query("SELECT COUNT(*) as count FROM view_ruang_dipinjam");
    $row = $stmt->fetch();
    echo "<p>Total ruang sedang dipinjam: " . htmlspecialchars($row['count']) . "</p>";

    echo "<p style='color: green;'>✅ Semua view berhasil diakses!</p>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Kembali ke Homepage</a></p>";
?>
