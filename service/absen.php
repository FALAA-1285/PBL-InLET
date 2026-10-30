<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';
require_once '../config/settings.php';

$conn = getDBConnection();

// Get page title and subtitle
$page_info = getPageTitle('attendance');
$message = '';
$message_type = '';

// Search NIM
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'search_nim') {
    header('Content-Type: application/json');
    $search_term = trim($_GET['q'] ?? '');

    if (strlen($search_term) < 2) {
        echo json_encode([]);
        exit;
    }

    try {
        // Use id_mahasiswa instead of nim
        $stmt = $conn->prepare("SELECT id_mahasiswa as nim, nama, status FROM mahasiswa WHERE CAST(id_mahasiswa AS TEXT) ILIKE :search ORDER BY id_mahasiswa LIMIT 10");
        $stmt->execute([':search' => '%' . $search_term . '%']);
        $results = $stmt->fetchAll();

        $suggestions = [];
        foreach ($results as $row) {
            $suggestions[] = [
                'nim' => $row['nim'],
                'nama' => $row['nama'],
                'status' => $row['status']
            ];
        }

        echo json_encode($suggestions);
    } catch (PDOException $e) {
        echo json_encode([]);
    }
    exit;
}

// Validate NIM
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'validate_nim') {
    header('Content-Type: application/json');
    $nim = trim($_GET['nim'] ?? '');

    if (empty($nim)) {
        echo json_encode(['valid' => false, 'message' => 'Student ID cannot be empty']);
        exit;
    }

    try {
        // Use id_mahasiswa instead of nim
        $stmt = $conn->prepare("SELECT id_mahasiswa as nim, nama, status FROM mahasiswa WHERE id_mahasiswa = :nim LIMIT 1");
        $stmt->execute(['nim' => $nim]);
        $mahasiswa = $stmt->fetch();

        if ($mahasiswa) {
            echo json_encode([
                'valid' => true,
                'nama' => $mahasiswa['nama'],
                'status' => $mahasiswa['status']
            ]);
        } else {
            echo json_encode(['valid' => false, 'message' => 'Student ID is not registered']);
        }
    } catch (PDOException $e) {
        echo json_encode(['valid' => false, 'message' => 'An error occurred while validating Student ID']);
    }
    exit;
}

// Process form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = trim($_POST['nim'] ?? '');
    $status = $_POST['status'] ?? '';
    $tipe_absen = $_POST['tipe_absen'] ?? '';
    $keterangan = trim($_POST['keterangan'] ?? '');

    // Validate input
    if (empty($nim)) {
        $message = 'Student ID is required!';
        $message_type = 'error';
    } elseif (empty($status)) {
        $message = 'Status must be selected!';
        $message_type = 'error';
    } elseif (empty($tipe_absen)) {
        $message = 'Attendance type must be selected!';
        $message_type = 'error';
    } else {
        try {
            // Check student - use id_mahasiswa instead of nim
            $check_mhs = $conn->prepare("SELECT id_mahasiswa as nim, nama FROM mahasiswa WHERE id_mahasiswa = :nim LIMIT 1");
            $check_mhs->execute(['nim' => $nim]);
            $mahasiswa = $check_mhs->fetch();

            if (!$mahasiswa) {
                $message = 'Student ID not found in database!';
                $message_type = 'error';
            } else {
                // Menggunakan prosedur proc_update_absensi
                require_once __DIR__ . '/../config/procedures.php';
                
                $action = ($tipe_absen === 'masuk') ? 'checkin' : 'checkout';
                
                // Build keterangan
                $keterangan_full = 'Status: ' . ucfirst($status);
                if (!empty($keterangan)) {
                    $keterangan_full .= ' | ' . $keterangan;
                }
                
                $result = callUpdateAbsensi($mahasiswa['nim'], $action, $keterangan_full);
                
                if ($result['success']) {
                    if ($action === 'checkin') {
                        $message = 'Check-in successful! Welcome, ' . htmlspecialchars($mahasiswa['nama']) . '.';
                    } else {
                        $message = 'Check-out successful! Thank you, ' . htmlspecialchars($mahasiswa['nama']) . '.';
                    }
                    $message_type = 'success';
                } else {
                    $message = $result['message'];
                    $message_type = 'error';
                }
            }
        } catch (PDOException $e) {
            $message = 'Error: ' . htmlspecialchars($e->getMessage());
            $message_type = 'error';
        }
    }
}

require __DIR__ . '/../views/service/absen.php';
