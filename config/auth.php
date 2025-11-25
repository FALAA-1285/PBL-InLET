<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/database.php';

// Fungsi untuk login admin (langsung dari tabel admin)
function loginAdmin($username, $password) {
    $conn = getDBConnection();
    
    try {
        // Cari admin berdasarkan username
        $stmt = $conn->prepare("SELECT id_admin, username, password_hash, role FROM admin WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password_hash'])) {
            // Set session
            $_SESSION['id_admin'] = $admin['id_admin'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['role'] = $admin['role'];
            $_SESSION['is_admin'] = true;
            
            return true;
        }
        
        return false;
    } catch(PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

// Fungsi untuk cek apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['id_admin']) && isset($_SESSION['is_admin']);
}

// Fungsi untuk cek apakah user adalah admin
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

// Fungsi untuk require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit();
    }
}

// Fungsi untuk require admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ../index.php');
        exit();
    }
}

// Fungsi untuk logout
function logout() {
    session_unset();
    session_destroy();
    header('Location: ../login.php');
    exit();
}

// Fungsi untuk mendapatkan admin info
function getAdminInfo() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id_admin' => $_SESSION['id_admin'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['role'] ?? 'admin'
    ];
}
?>
