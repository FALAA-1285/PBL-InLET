<?php
/**
 * Setup Script - Create Pengunjung User
 * 
 * Jalankan script ini untuk membuat user pengunjung
 * Setelah pengunjung dibuat, bisa login di login.php
 */

require_once '../config/database.php';

$conn = getDBConnection();

// Default pengunjung credentials (ubah sesuai kebutuhan!)
$pengunjung_username = 'pengunjung';
$pengunjung_password = 'pengunjung123'; // Ganti dengan password yang kuat!
$pengunjung_nama = 'Pengunjung Test';
$pengunjung_email = 'pengunjung@test.com';
$pengunjung_asal_institusi = 'Institusi Test';

try {
    // Check if pengunjung already exists
    $stmt = $conn->prepare("SELECT id_user FROM users WHERE username = :username");
    $stmt->execute(['username' => $pengunjung_username]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        echo "<h2>User pengunjung sudah ada!</h2>";
        echo "<p>Username: <strong>$pengunjung_username</strong></p>";
        echo "<p><a href='../login.php'>Login di sini</a></p>";
        exit();
    }
    
    // Hash password
    $password_hash = password_hash($pengunjung_password, PASSWORD_DEFAULT);
    
    // Create user
    $stmt = $conn->prepare("INSERT INTO users (username, password_hash, role) VALUES (:username, :password_hash, 'pengunjung')");
    $stmt->execute([
        'username' => $pengunjung_username,
        'password_hash' => $password_hash
    ]);
    $user_id = $conn->lastInsertId();
    
    // Create pengunjung profile
    $stmt = $conn->prepare("INSERT INTO pengunjung (id_user, nama, email, asal_institusi) VALUES (:id_user, :nama, :email, :asal_institusi)");
    $stmt->execute([
        'id_user' => $user_id,
        'nama' => $pengunjung_nama,
        'email' => $pengunjung_email,
        'asal_institusi' => $pengunjung_asal_institusi
    ]);
    
    $pengunjung_id = $conn->lastInsertId();
    
    // Create visitor record
    $stmt = $conn->prepare("INSERT INTO visitor (id_pengunjung, visit_count, first_visit) VALUES (:id_pengunjung, 0, NOW())");
    $stmt->execute(['id_pengunjung' => $pengunjung_id]);
    
    echo "<h2>User pengunjung berhasil dibuat!</h2>";
    echo "<p><strong>Username:</strong> $pengunjung_username</p>";
    echo "<p><strong>Password:</strong> $pengunjung_password</p>";
    echo "<p><strong>Nama:</strong> $pengunjung_nama</p>";
    echo "<p><strong>Email:</strong> $pengunjung_email</p>";
    echo "<p><strong>Asal Institusi:</strong> $pengunjung_asal_institusi</p>";
    echo "<hr>";
    echo "<p style='color: red;'><strong>PENTING:</strong> Ganti password setelah login pertama kali!</p>";
    echo "<p><a href='../login.php' style='display: inline-block; padding: 10px 20px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px;'>Login di sini</a></p>";
    echo "<hr>";
    echo "<p style='color: orange;'><strong>PERINGATAN:</strong> Hapus atau rename file ini setelah setup selesai!</p>";
    
} catch(PDOException $e) {
    echo "<h2 style='color: red;'>Error!</h2>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p>Pastikan database sudah diimport dan tabel sudah ada.</p>";
}
?>

