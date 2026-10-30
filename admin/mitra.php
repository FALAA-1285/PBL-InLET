<?php
require_once '../config/auth.php';
require_once '../config/upload.php';
requireAdmin();

$conn = getDBConnection();
$message = '';
$message_type = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_mitra') {
        $nama_institusi = trim($_POST['nama_institusi'] ?? '');
        $logo = $_POST['logo'] ?? ''; // URL input

        // Handle file upload
        if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImage($_FILES['logo_file'], 'mitra/');
            if ($uploadResult['success']) {
                $logo = $uploadResult['path'];
            } else {
                $message = $uploadResult['message'];
                $message_type = 'error';
            }
        }

        if (empty($message)) {
            if (empty($nama_institusi)) {
                $message = 'Nama institusi harus diisi!';
                $message_type = 'error';
            } else {
                try {
                    // Fix sequence if it's out of sync
                    try {
                        $max_id_stmt = $conn->query("SELECT COALESCE(MAX(id_mitra), 0) as max_id FROM mitra");
                        $max_id = $max_id_stmt->fetch()['max_id'];
                        $conn->exec("SELECT setval('mitra_id_mitra_seq', " . ($max_id + 1) . ", false)");
                    } catch (PDOException $seq_e) {
                        // Sequence might not exist or error, continue anyway
                    }
                    
                    $stmt = $conn->prepare("INSERT INTO mitra (nama_institusi, logo) VALUES (:nama_institusi, :logo)");
                    $stmt->execute([
                        'nama_institusi' => $nama_institusi,
                        'logo' => $logo ?: null
                    ]);
                    $message = 'Mitra berhasil ditambahkan!';
                    $message_type = 'success';
                } catch (PDOException $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $message_type = 'error';
                }
            }
        }
    } elseif ($action === 'update_mitra') {
        $id = $_POST['id'] ?? 0;
        $nama_institusi = trim($_POST['nama_institusi'] ?? '');
        $logo = $_POST['logo'] ?? '';

        // Handle file upload
        if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImage($_FILES['logo_file'], 'mitra/');
            if ($uploadResult['success']) {
                $logo = $uploadResult['path'];
            } else {
                $message = $uploadResult['message'];
                $message_type = 'error';
            }
        }

        if (empty($message)) {
            if (empty($nama_institusi)) {
                $message = 'Nama institusi harus diisi!';
                $message_type = 'error';
            } else {
                try {
                    if ($logo) {
                        $stmt = $conn->prepare("UPDATE mitra SET nama_institusi = :nama_institusi, logo = :logo WHERE id_mitra = :id");
                        $stmt->execute([
                            'id' => $id,
                            'nama_institusi' => $nama_institusi,
                            'logo' => $logo
                        ]);
                    } else {
                        $stmt = $conn->prepare("UPDATE mitra SET nama_institusi = :nama_institusi WHERE id_mitra = :id");
                        $stmt->execute([
                            'id' => $id,
                            'nama_institusi' => $nama_institusi
                        ]);
                    }
                    $message = 'Mitra berhasil diupdate!';
                    $message_type = 'success';
                } catch (PDOException $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $message_type = 'error';
                }
            }
        }
    } elseif ($action === 'delete_mitra') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $conn->prepare("DELETE FROM mitra WHERE id_mitra = :id");
            $stmt->execute(['id' => $id]);
            $message = 'Mitra berhasil dihapus!';
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
try {
    $count_stmt = $conn->query("SELECT COUNT(*) FROM mitra");
    $total_items = $count_stmt->fetchColumn();
    $total_pages = ceil($total_items / $items_per_page);
} catch (PDOException $e) {
    $total_items = 0;
    $total_pages = 0;
}

// Get mitra with pagination
try {
    $stmt = $conn->prepare("SELECT * FROM mitra ORDER BY nama_institusi LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $mitra_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $mitra_list = [];
}

// Get mitra for edit
$edit_mitra = null;
if (isset($_GET['edit'])) {
    try {
        $edit_id = intval($_GET['edit']);
        $stmt = $conn->prepare("SELECT * FROM mitra WHERE id_mitra = :id");
        $stmt->execute(['id' => $edit_id]);
        $edit_mitra = $stmt->fetch();
    } catch (PDOException $e) {
        $edit_mitra = null;
    }
}

require __DIR__ . '/../views/admin/mitra.php';
