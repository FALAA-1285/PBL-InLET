<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/auth.php';

$error = '';

// Jika sudah login sebagai admin, redirect ke dashboard
if (isLoggedIn() && isAdmin()) {
    header('Location: admin/dashboard.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username and password are required!';
    } else {
        if (loginAdmin($username, $password)) {
            header('Location: admin/dashboard.php');
            exit();
        } else {
            $error = 'Invalid username or password!';
        }
    }
}

require __DIR__ . '/views/login.php';
