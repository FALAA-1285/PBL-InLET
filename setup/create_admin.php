<?php
/**
 * Setup Script - Create Admin User
 * 
 * Jalankan script ini sekali untuk membuat admin user pertama
 * Setelah admin dibuat, hapus atau rename file ini untuk keamanan
 */

require_once '../config/database.php';

$conn = getDBConnection();
// Default admin credentials (ubah setelah setup!)
$admin_username = 'admin';
$admin_password = 'admin123'; // Ganti dengan password yang kuat!
$admin_nama = 'Administrator';
$admin_email = 'admin@inlet.edu';

try {
    // Check if admin already exists
    $stmt = $conn->prepare("SELECT id_admin FROM admin WHERE username = :username");
    $stmt->execute(['username' => $admin_username]);
    $existing = $stmt->fetch();

    if ($existing) {
        echo "Admin user sudah ada! Username: $admin_username<br>";
        echo "<a href='../login.php'>Login di sini</a>";
        exit();
    }

    // Hash password
    $password_hash = password_hash($admin_password, PASSWORD_DEFAULT);

    // Create user
    $stmt = $conn->prepare("INSERT INTO admin (username, password_hash, role) VALUES (:username, :password_hash, 'admin') RETURNING id_user");
    $stmt->execute([
        'username' => $admin_username,
        'password_hash' => $password_hash
    ]);
    $user_id = $stmt->fetchColumn();

    // Create admin profile
    $stmt = $conn->prepare("INSERT INTO admin (id_admin, nama, email) VALUES (:id_admin, :nama, :email)");
    $stmt->execute([
        'id_user' => $user_id,
        'nama' => $admin_nama,
        'email' => $admin_email
    ]);

    echo "<h2>Admin successfully created!</h2>";
    echo "<p><strong>Username:</strong> $admin_username</p>";
    echo "<p><strong>Password:</strong> $admin_password</p>";
    echo "<p style='color: red;'><strong>PENTING:</strong> Ganti password setelah login pertama kali!</p>";
    echo "<p><a href='../login.php'>Login di sini</a></p>";
    echo "<p style='color: orange;'><strong>PERINGATAN:</strong> Hapus atau rename file ini setelah setup selesai!</p>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>