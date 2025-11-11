<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/database.php';

// Fungsi untuk login
function login($username, $password) {
    $conn = getDBConnection();
    
    try {
        // Cari user berdasarkan username
        $stmt = $conn->prepare("SELECT id_user, username, password_hash, role FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Set session
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Cek apakah user adalah admin
            $stmt = $conn->prepare("SELECT id_admin FROM admin WHERE id_user = :id_user");
            $stmt->execute(['id_user' => $user['id_user']]);
            $admin = $stmt->fetch();
            
            if ($admin) {
                $_SESSION['is_admin'] = true;
                $_SESSION['admin_id'] = $admin['id_admin'];
            } else {
                $_SESSION['is_admin'] = false;
            }
            
            // Cek apakah user adalah pengunjung
            $stmt = $conn->prepare("SELECT id_pengunjung FROM pengunjung WHERE id_user = :id_user");
            $stmt->execute(['id_user' => $user['id_user']]);
            $pengunjung = $stmt->fetch();
            
            if ($pengunjung) {
                $_SESSION['is_pengunjung'] = true;
                $_SESSION['pengunjung_id'] = $pengunjung['id_pengunjung'];
                
                // Update atau create visitor record
                updateVisitorCount($pengunjung['id_pengunjung']);
            } else {
                $_SESSION['is_pengunjung'] = false;
            }
            
            return true;
        }
        
        return false;
    } catch(PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

// Fungsi untuk update visitor count
function updateVisitorCount($id_pengunjung) {
    $conn = getDBConnection();
    
    try {
        // Cek apakah visitor record sudah ada
        $stmt = $conn->prepare("SELECT id_visitor, visit_count FROM visitor WHERE id_pengunjung = :id_pengunjung");
        $stmt->execute(['id_pengunjung' => $id_pengunjung]);
        $visitor = $stmt->fetch();
        
        if ($visitor) {
            // Update visit count dan last_visit
            $stmt = $conn->prepare("UPDATE visitor SET visit_count = visit_count + 1, last_visit = NOW() WHERE id_pengunjung = :id_pengunjung");
            $stmt->execute(['id_pengunjung' => $id_pengunjung]);
        } else {
            // Create new visitor record
            $stmt = $conn->prepare("INSERT INTO visitor (id_pengunjung, visit_count, last_visit, first_visit) VALUES (:id_pengunjung, 1, NOW(), NOW())");
            $stmt->execute(['id_pengunjung' => $id_pengunjung]);
        }
    } catch(PDOException $e) {
        error_log("Visitor update error: " . $e->getMessage());
    }
}

// Fungsi untuk cek apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk cek apakah user adalah admin
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

// Fungsi untuk cek apakah user adalah pengunjung
function isPengunjung() {
    return isset($_SESSION['is_pengunjung']) && $_SESSION['is_pengunjung'] === true;
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

// Fungsi untuk mendapatkan user info
function getUserInfo() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id_user' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['role'],
        'is_admin' => $_SESSION['is_admin'] ?? false,
        'is_pengunjung' => $_SESSION['is_pengunjung'] ?? false
    ];
}
?>

