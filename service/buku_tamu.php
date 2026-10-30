<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';
require_once '../config/settings.php';

// Get page title and subtitle
$page_info = getPageTitle('guestbook');

$message = '';
$message_type = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $institusi = trim($_POST['institusi'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $pesan = trim($_POST['pesan'] ?? '');

    // Validation
    if (empty($nama)) {
        $message = 'Name is required!';
        $message_type = 'error';
    } elseif (empty($email)) {
        $message = 'Email is required!';
        $message_type = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email format!';
        $message_type = 'error';
    } elseif (empty($institusi)) {
        $message = 'Institution is required!';
        $message_type = 'error';
    } elseif (empty($no_hp)) {
        $message = 'Phone number is required!';
        $message_type = 'error';
    } else {
        try {
            $conn = getDBConnection();

            // Buku tamu table creation moved to inlet_pbl_clean.sql

            // Insert data
            $stmt = $conn->prepare("INSERT INTO buku_tamu (nama, email, institusi, no_hp, pesan) 
                                   VALUES (:nama, :email, :institusi, :no_hp, :pesan)");
            $stmt->execute([
                ':nama' => $nama,
                ':email' => $email,
                ':institusi' => $institusi,
                ':no_hp' => $no_hp,
                ':pesan' => $pesan ?: null
            ]);

            if (!empty($pesan)) {
                $message = 'Thank you! Your message has been sent. We will respond shortly.';
            } else {
                $message = 'Thank you! Your attendance has been recorded.';
            }
            $message_type = 'success';

            // Clear form data after successful submission
            $_POST = [];
        } catch (PDOException $e) {
            $message = 'An error occurred while saving your message. Please try again.';
            $message_type = 'error';
        }
    }
}

require __DIR__ . '/../views/service/buku_tamu.php';
