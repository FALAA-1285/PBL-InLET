<?php
require_once '../config/auth.php';
requireAdmin();

$conn = getDBConnection();
$message = '';
$message_type = '';

// Get current admin ID
$admin_id = $_SESSION['id_admin'] ?? null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_mahasiswa') {
        $nim = trim($_POST['nim'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $tahun = $_POST['tahun'] ?? null;
        $status = $_POST['status'] ?? 'regular';

        if (empty($nim)) {
            $message = 'NIM harus diisi!';
            $message_type = 'error';
        } elseif (empty($nama)) {
            $message = 'Nama harus diisi!';
            $message_type = 'error';
        } else {
            try {
                // Use id_mahasiswa instead of nim
                $stmt = $conn->prepare("INSERT INTO mahasiswa (id_mahasiswa, nama, tahun, status) VALUES (:id_mahasiswa, :nama, :tahun, :status)");
                $stmt->execute([
                    'id_mahasiswa' => $nim, // Use nim input value as id_mahasiswa
                    'nama' => $nama,
                    'tahun' => $tahun ?: null,
                    'status' => $status
                ]);
                $message = 'Student successfully added!';
                $message_type = 'success';
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $message = 'Student ID already exists!';
                    $message_type = 'error';
                } else {
                    $message = 'Error: ' . $e->getMessage();
                    $message_type = 'error';
                }
            }
        }
    } elseif ($action === 'update_mahasiswa') {
        $id = $_POST['id'] ?? 0;
        $nama = trim($_POST['nama'] ?? '');
        $tahun = $_POST['tahun'] ?? null;
        $status = $_POST['status'] ?? 'regular';

        if (empty($nama)) {
            $message = 'Nama harus diisi!';
            $message_type = 'error';
        } else {
            try {
                $stmt = $conn->prepare("UPDATE mahasiswa SET nama = :nama, tahun = :tahun, status = :status WHERE id_mahasiswa = :id");
                $stmt->execute([
                    'id' => $id,
                    'nama' => $nama,
                    'tahun' => $tahun ?: null,
                    'status' => $status
                ]);
                $message = 'Student successfully updated!';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'delete_mahasiswa') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $conn->prepare("DELETE FROM mahasiswa WHERE id_mahasiswa = :id");
            $stmt->execute(['id' => $id]);
            $message = 'Student successfully deleted!';
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Pagination setup
$items_per_page = 10;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Get total count
$count_stmt = $conn->query("SELECT COUNT(*) FROM mahasiswa");
$total_items = $count_stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

// Get mahasiswa with pagination - use id_mahasiswa as nim for compatibility
try {
    $stmt = $conn->prepare("SELECT id_mahasiswa as nim, nama, tahun, status, id_admin FROM mahasiswa ORDER BY nama LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $mahasiswa_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $mahasiswa_list = [];
}

// Get mahasiswa for edit
$edit_mahasiswa = null;
if (isset($_GET['edit'])) {
    try {
        $edit_id = intval($_GET['edit']);
        $stmt = $conn->prepare("SELECT id_mahasiswa as nim, nama, tahun, status, id_admin FROM mahasiswa WHERE id_mahasiswa = :id");
        $stmt->execute(['id' => $edit_id]);
        $edit_mahasiswa = $stmt->fetch();
    } catch (PDOException $e) {
        $edit_mahasiswa = null;
    }
}

require __DIR__ . '/../views/admin/mahasiswa.php';
