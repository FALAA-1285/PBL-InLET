<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/database.php';

// Login admin
function loginAdmin($username, $password) {
    $conn = getDBConnection();

    try {
        // Find admin by username
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

// Check if logged in
function isLoggedIn() {
    return isset($_SESSION['id_admin']) && isset($_SESSION['is_admin']);
}

// Check if admin
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit();
    }
}

// Require admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ../index.php');
        exit();
    }
}

// Logout
function logout() {
    session_unset();
    session_destroy();
    header('Location: ../login.php');
    exit();
}

// Get admin info
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
