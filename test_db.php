<?php
/**
 * Test Database Connection
 * 
 * File ini untuk test koneksi database
 * Hapus file ini setelah setup selesai untuk keamanan
 */

require_once 'config/database.php';

echo "<h2>Testing Database Connection</h2>";
echo "<hr>";

try {
    $conn = getDBConnection();
    echo "<p style='color: green;'>✅ <strong>Database connection successful!</strong></p>";

    // Test query
    $stmt = $conn->query("SELECT version()");
    $version = $stmt->fetch();
    echo "<p><strong>PostgreSQL Version:</strong> " . $version['version'] . "</p>";

    // Check database name
    $stmt = $conn->query("SELECT current_database()");
    $db_name = $stmt->fetch();
    echo "<p><strong>Database Name:</strong> " . $db_name['current_database'] . "</p>";

    // Check if tables exist
    $stmt = $conn->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name");
    $tables = $stmt->fetchAll();

    echo "<hr>";
    echo "<h3>Daftar Tabel di Database:</h3>";

    if (empty($tables)) {
        echo "<p style='color: orange;'>⚠️ <strong>Belum ada tabel!</strong> Import schema database terlebih dahulu.</p>";
        echo "<p>Jalankan: <code>psql -U postgres -d inlet_pbl -f database/schema.sql</code></p>";
    } else {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>" . $table['table_name'] . "</li>";
        }
        echo "</ul>";
        echo "<p style='color: green;'>✅ <strong>Total " . count($tables) . " tabel ditemukan</strong></p>";
    }

    // Check if users table has data
    try {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
        $user_count = $stmt->fetch();
        echo "<hr>";
        echo "<h3>Status Setup:</h3>";
        echo "<p><strong>Total Users:</strong> " . $user_count['count'] . "</p>";

        if ($user_count['count'] > 0) {
            $stmt = $conn->query("SELECT username, role FROM users");
            $users = $stmt->fetchAll();
            echo "<ul>";
            foreach ($users as $user) {
                echo "<li>Username: <strong>" . $user['username'] . "</strong> (Role: " . $user['role'] . ")</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: orange;'>⚠️ <strong>Belum ada user!</strong> Buat admin user terlebih dahulu.</p>";
            echo "<p>Akses: <a href='setup/create_admin.php'>setup/create_admin.php</a></p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Error checking users: " . $e->getMessage() . "</p>";
    }

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ <strong>Database connection failed!</strong></p>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<hr>";
    echo "<h3>Kemungkinan Penyebab:</h3>";
    echo "<ul>";
    echo "<li>PostgreSQL tidak running</li>";
    echo "<li>Database belum dibuat</li>";
    echo "<li>Username/password salah di config/database.php</li>";
    echo "<li>Port PostgreSQL tidak sesuai</li>";
    echo "</ul>";
    echo "<hr>";
    echo "<h3>Solusi:</h3>";
    echo "<ol>";
    echo "<li>Pastikan PostgreSQL running di Laragon</li>";
    echo "<li>Buat database: <code>CREATE DATABASE inlet_pbl;</code></li>";
    echo "<li>Cek konfigurasi di <code>config/database.php</code></li>";
    echo "<li>Pastikan password PostgreSQL benar (saat ini: 828)</li>";
    echo "</ol>";
}

echo "<hr>";
echo "<p><strong>PENTING:</strong> Hapus file ini setelah setup selesai untuk keamanan!</p>";
echo "<p><a href='index.php'>Kembali ke Homepage</a> | <a href='login.php'>Login</a></p>";
?>