<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/auth.php';

$error = '';
$success = '';

// Jika sudah login, redirect
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $asal_institusi = trim($_POST['asal_institusi'] ?? '');

    // Validation
    if (empty($username) || empty($password) || empty($nama)) {
        $error = 'Username, password, and name are required!';
    } elseif ($password !== $password_confirm) {
        $error = 'Password and password confirmation do not match!';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long!';
    } else {
        $conn = getDBConnection();

        try {
            // Check if username already exists
            $stmt = $conn->prepare("SELECT id_user FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            if ($stmt->fetch()) {
                $error = 'Username already in use!';
            } else {
                // Hash password
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Create user
                $stmt = $conn->prepare("INSERT INTO users (username, password_hash, role) VALUES (:username, :password_hash, 'pengunjung') RETURNING id_user");
                $stmt->execute([
                    'username' => $username,
                    'password_hash' => $password_hash
                ]);
                $user_id = $stmt->fetchColumn();

                // Create pengunjung profile
                $stmt = $conn->prepare("INSERT INTO pengunjung (id_user, nama, email, asal_institusi) VALUES (:id_user, :nama, :email, :asal_institusi) RETURNING id_pengunjung");
                $stmt->execute([
                    'id_user' => $user_id,
                    'nama' => $nama,
                    'email' => $email ?: null,
                    'asal_institusi' => $asal_institusi ?: null
                ]);

                $pengunjung_id = $stmt->fetchColumn();

                // Create visitor record
                $stmt = $conn->prepare("INSERT INTO visitor (id_pengunjung, visit_count, first_visit) VALUES (:id_pengunjung, 0, NOW())");
                $stmt->execute(['id_pengunjung' => $pengunjung_id]);

                $success = 'Registration successful! Please login.';
            }
        } catch (PDOException $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

require __DIR__ . '/views/register.php';
