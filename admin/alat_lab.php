<?php
require_once '../config/auth.php';
requireAdmin();

$conn = getDBConnection();
$message = '';
$message_type = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_alat') {
        $nama_alat = trim($_POST['nama_alat'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $stock = intval($_POST['stock'] ?? 0);

        if (empty($nama_alat)) {
            $message = 'Tool name must be filled!';
            $message_type = 'error';
        } else {
            try {
                $stmt = $conn->prepare("INSERT INTO alat_lab (nama_alat, deskripsi, stock) VALUES (:nama_alat, :deskripsi, :stock)");
                $stmt->execute([
                    'nama_alat' => $nama_alat,
                    'deskripsi' => $deskripsi ?: null,
                    'stock' => $stock
                ]);
                $message = 'Lab tool successfully added!';
                $message_type = 'success';
                // Redirect to prevent resubmission
                header('Location: alat_lab.php?added=1');
                exit;
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'update_alat') {
        $id = $_POST['id'] ?? 0;
        $nama_alat = trim($_POST['nama_alat'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $stock = intval($_POST['stock'] ?? 0);

        if (empty($nama_alat)) {
            $message = 'Tool name must be filled!';
            $message_type = 'error';
        } else {
            try {
                $stmt = $conn->prepare("UPDATE alat_lab SET nama_alat = :nama_alat, deskripsi = :deskripsi, stock = :stock, updated_at = CURRENT_TIMESTAMP WHERE id_alat_lab = :id");
                $stmt->execute([
                    'id' => $id,
                    'nama_alat' => $nama_alat,
                    'deskripsi' => $deskripsi ?: null,
                    'stock' => $stock
                ]);
                // Redirect to prevent resubmission
                header('Location: alat_lab.php?updated=1');
                exit;
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'delete_alat') {
        $id = $_POST['id'] ?? 0;
        
        // Prevent deletion of Room Placeholder (id = 0) only
        if ($id == 0) {
            $message = 'Cannot delete Room Placeholder!';
            $message_type = 'error';
        } else {
            try {
                // Check if alat is being borrowed using direct query on peminjaman table
                $check_stmt = $conn->prepare("SELECT COUNT(*) FROM peminjaman WHERE id_alat = :id AND status = 'dipinjam'");
                $check_stmt->execute(['id' => $id]);
                $borrowed_count = $check_stmt->fetchColumn();

                if ($borrowed_count > 0) {
                    $message = 'Tool cannot be deleted because it is currently borrowed!';
                    $message_type = 'error';
                } else {
                    $stmt = $conn->prepare("DELETE FROM alat_lab WHERE id_alat_lab = :id");
                    $stmt->execute(['id' => $id]);
                    // Redirect to prevent resubmission
                    header('Location: alat_lab.php?deleted=1');
                    exit;
                }
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    }
}

// Check for success messages from redirect
if (isset($_GET['added'])) {
    $message = 'Lab tool successfully added!';
    $message_type = 'success';
} elseif (isset($_GET['updated'])) {
    $message = 'Lab tool successfully updated!';
    $message_type = 'success';
} elseif (isset($_GET['deleted'])) {
    $message = 'Lab tool successfully deleted!';
    $message_type = 'success';
}

// Pagination setup
$items_per_page = 10;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Get total count (exclude only Room Placeholder with id = 0)
try {
    $count_stmt = $conn->query("SELECT COUNT(*) FROM alat_lab WHERE id_alat_lab > 0");
    $total_items = $count_stmt->fetchColumn();
    $total_pages = ceil($total_items / $items_per_page);
} catch (PDOException $e) {
    $total_items = 0;
    $total_pages = 0;
    error_log("Error counting alat: " . $e->getMessage());
}

// Get alat lab dengan informasi stok tersedia - exclude only Room Placeholder (id = 0)
// Use direct query to alat_lab table and calculate stock manually
try {
    $stmt = $conn->prepare("SELECT 
        a.id_alat_lab,
        a.nama_alat,
        a.deskripsi,
        a.stock,
        COALESCE((SELECT COUNT(*) FROM peminjaman WHERE id_alat = a.id_alat_lab AND status = 'dipinjam'), 0) AS jumlah_dipinjam,
        (a.stock - COALESCE((SELECT COUNT(*) FROM peminjaman WHERE id_alat = a.id_alat_lab AND status = 'dipinjam'), 0)) AS stok_tersedia
    FROM alat_lab a
    WHERE a.id_alat_lab > 0
    ORDER BY a.nama_alat
    LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $alat_list = $stmt->fetchAll();
    
    // Debug: log if list is empty but total_items > 0
    if (empty($alat_list) && $total_items > 0) {
        error_log("Warning: alat_list is empty but total_items = $total_items");
    }
} catch (PDOException $e) {
    $alat_list = [];
    error_log("Error fetching alat list: " . $e->getMessage());
}

// Get alat for edit - perlu ambil dari tabel karena view tidak memiliki semua field untuk edit
// Exclude only Room Placeholder (id = 0) from edit
$edit_alat = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    // Prevent editing Room Placeholder only
    if ($edit_id == 0) {
        $edit_alat = null;
    } else {
        try {
            $stmt = $conn->prepare("SELECT * FROM alat_lab WHERE id_alat_lab = :id");
            $stmt->execute(['id' => $edit_id]);
            $edit_alat = $stmt->fetch();
        } catch (PDOException $e) {
            $edit_alat = null;
        }
    }
}

require __DIR__ . '/../views/admin/alat_lab.php';
