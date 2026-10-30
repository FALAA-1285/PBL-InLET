<?php
require_once '../config/auth.php';
requireAdmin();

$conn = getDBConnection();
$message = '';
$message_type = '';

// Get current admin ID and validate it exists in admin table
$admin_id = $_SESSION['id_admin'] ?? null;
if ($admin_id) {
    try {
        $check_admin = $conn->prepare("SELECT id_admin FROM admin WHERE id_admin = :id_admin LIMIT 1");
        $check_admin->execute(['id_admin' => $admin_id]);
        if (!$check_admin->fetch()) {
            // Admin ID doesn't exist, set to null
            $admin_id = null;
        }
    } catch (PDOException $e) {
        // If error checking, set to null to be safe
        $admin_id = null;
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_ruang') {
        $nama_ruang = trim($_POST['nama_ruang'] ?? '');

        if (empty($nama_ruang)) {
            $message = 'Nama ruang harus diisi!';
            $message_type = 'error';
        } else {
            try {
                if ($admin_id) {
                    $stmt = $conn->prepare("INSERT INTO ruang_lab (nama_ruang, id_admin) VALUES (:nama_ruang, :id_admin)");
                    $stmt->execute([
                        'nama_ruang' => $nama_ruang,
                        'id_admin' => $admin_id
                    ]);
                } else {
                    // If no valid admin ID, insert without id_admin (will be NULL)
                    $stmt = $conn->prepare("INSERT INTO ruang_lab (nama_ruang) VALUES (:nama_ruang)");
                    $stmt->execute([
                        'nama_ruang' => $nama_ruang
                    ]);
                }
                $message = 'Lab room successfully added!';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'update_ruang') {
        $id = $_POST['id'] ?? 0;
        $nama_ruang = trim($_POST['nama_ruang'] ?? '');
        $status = trim($_POST['status'] ?? 'tersedia');

        if (empty($nama_ruang)) {
            $message = 'Nama ruang harus diisi!';
            $message_type = 'error';
        } else {
            try {
                $stmt = $conn->prepare("UPDATE ruang_lab SET nama_ruang = :nama_ruang, status = :status WHERE id_ruang_lab = :id");
                $stmt->execute([
                    'id' => $id,
                    'nama_ruang' => $nama_ruang,
                    'status' => $status
                ]);
                // Redirect to add form after successful update
                header('Location: ruang_lab.php?updated=1');
                exit;
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'delete_ruang') {
        $id = $_POST['id'] ?? 0;
        try {
            // Check if room is being borrowed
            $check_stmt = $conn->prepare("SELECT COUNT(*) FROM peminjaman WHERE id_ruang = :id AND status = 'dipinjam'");
            $check_stmt->execute(['id' => $id]);
            $borrowed_count = $check_stmt->fetchColumn();

            if ($borrowed_count > 0) {
                $message = 'Room cannot be deleted because it is currently borrowed!';
                $message_type = 'error';
            } else {
                $stmt = $conn->prepare("DELETE FROM ruang_lab WHERE id_ruang_lab = :id");
                $stmt->execute(['id' => $id]);
                $message = 'Lab room successfully deleted!';
                $message_type = 'success';
            }
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Check if redirected from update
if (isset($_GET['updated']) && $_GET['updated'] == '1') {
    $message = 'Lab room successfully updated!';
    $message_type = 'success';
}

// Pagination setup
$items_per_page = 10;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Get total count
try {
    $count_stmt = $conn->query("SELECT COUNT(*) FROM ruang_lab");
    $total_items = $count_stmt->fetchColumn();
    $total_pages = ceil($total_items / $items_per_page);
} catch (PDOException $e) {
    $total_items = 0;
    $total_pages = 0;
}

// Get ruang lab with pagination
try {
    $stmt = $conn->prepare("SELECT r.*, 
        (SELECT COUNT(*) FROM peminjaman WHERE id_ruang = r.id_ruang_lab AND status = 'dipinjam') as jumlah_dipinjam
        FROM ruang_lab r 
        ORDER BY nama_ruang
        LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $ruang_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $ruang_list = [];
}

// Get ruang for edit
$edit_ruang = null;
if (isset($_GET['edit'])) {
    try {
        $edit_id = intval($_GET['edit']);
        $stmt = $conn->prepare("SELECT * FROM ruang_lab WHERE id_ruang_lab = :id");
        $stmt->execute(['id' => $edit_id]);
        $edit_ruang = $stmt->fetch();
    } catch (PDOException $e) {
        $edit_ruang = null;
    }
}

require __DIR__ . '/../views/admin/ruang_lab.php';
