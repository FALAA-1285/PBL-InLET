<?php
require_once '../config/auth.php';
require_once '../config/upload.php';
requireAdmin();

$conn = getDBConnection();
$message = '';
$message_type = '';

// Ensure google_scholar column exists in member table
try {
    $check_col = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'member' AND column_name = 'google_scholar'");
    if ($check_col->rowCount() == 0) {
        $conn->exec("ALTER TABLE member ADD COLUMN google_scholar TEXT");
    }
} catch (PDOException $e) {
    // Column might already exist or error, continue anyway
    error_log("Note: Could not add google_scholar column: " . $e->getMessage());
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_member') {
        $nama = $_POST['nama'] ?? '';
        $email = $_POST['email'] ?? '';
        $jabatan = $_POST['jabatan'] ?? '';
        $foto = $_POST['foto'] ?? ''; // URL input

        // Handle file upload
        if (isset($_FILES['foto_file']) && $_FILES['foto_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImage($_FILES['foto_file'], 'members/');
            if ($uploadResult['success']) {
                $foto = $uploadResult['path'];
            } else {
                $message = $uploadResult['message'];
                $message_type = 'error';
            }
        }

        if (empty($message)) {
            try {
                $alamat = $_POST['alamat'] ?? '';
                $no_tlp = $_POST['no_tlp'] ?? '';
                $deskripsi = $_POST['deskripsi'] ?? '';
                $bidang_keahlian = $_POST['bidang_keahlian'] ?? '';
                $google_scholar = $_POST['google_scholar'] ?? '';
                $admin_id = $_SESSION['id_admin'] ?? null;

                $stmt = $conn->prepare("INSERT INTO member (nama, email, jabatan, foto, bidang_keahlian, alamat, notlp, deskripsi, google_scholar) VALUES (:nama, :email, :jabatan, :foto, :keahlian, :alamat, :notlp, :deskripsi, :google_scholar)");
                $stmt->execute([
                    'nama' => $nama,
                    'email' => $email ?: null,
                    'jabatan' => $jabatan ?: null,
                    'foto' => $foto ?: null,
                    'keahlian' => $bidang_keahlian ?: null,
                    'alamat' => $alamat ?: null,
                    'notlp' => $no_tlp ?: null,
                    'deskripsi' => $deskripsi ?: null,
                    'google_scholar' => $google_scholar ?: null
                ]);

                $message = 'Member successfully added!';
                $message_type = 'success';
                
                // Redirect to prevent resubmission
                header('Location: member.php?added=1');
                exit;
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'update_member') {
        $id = $_POST['id'] ?? 0;
        $nama = $_POST['nama'] ?? '';
        $email = $_POST['email'] ?? '';
        $jabatan = $_POST['jabatan'] ?? '';
        $foto = $_POST['foto'] ?? '';

        // Handle file upload
        if (isset($_FILES['foto_file']) && $_FILES['foto_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImage($_FILES['foto_file'], 'members/');
            if ($uploadResult['success']) {
                $foto = $uploadResult['path'];
            } else {
                $message = $uploadResult['message'];
                $message_type = 'error';
            }
        }

        if (empty($message)) {
            try {
                $alamat = $_POST['alamat'] ?? '';
                $no_tlp = $_POST['no_tlp'] ?? '';
                $deskripsi = $_POST['deskripsi'] ?? '';

                $bidang_keahlian = $_POST['bidang_keahlian'] ?? '';
                $google_scholar = $_POST['google_scholar'] ?? '';
                
                $stmt = $conn->prepare("UPDATE member SET nama = :nama, email = :email, jabatan = :jabatan, foto = :foto, bidang_keahlian = :keahlian, alamat = :alamat, notlp = :notlp, deskripsi = :deskripsi, google_scholar = :google_scholar WHERE id_member = :id");
                $stmt->execute([
                    'id' => $id,
                    'nama' => $nama,
                    'email' => $email ?: null,
                    'jabatan' => $jabatan ?: null,
                    'foto' => $foto ?: null,
                    'keahlian' => $bidang_keahlian ?: null,
                    'alamat' => $alamat ?: null,
                    'notlp' => $no_tlp ?: null,
                    'deskripsi' => $deskripsi ?: null,
                    'google_scholar' => $google_scholar ?: null
                ]);

                $message = 'Member successfully updated!';
                $message_type = 'success';
                
                // Redirect to prevent resubmission
                $page = isset($_GET['page']) ? '&page=' . intval($_GET['page']) : '';
                header('Location: member.php?updated=1' . $page);
                exit;
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'delete_member') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $conn->prepare("DELETE FROM member WHERE id_member = :id");
            $stmt->execute(['id' => $id]);
            $message = 'Member successfully deleted!';
            $message_type = 'success';
            
            // Redirect to prevent resubmission
            $page = isset($_GET['page']) ? '&page=' . intval($_GET['page']) : '';
            header('Location: member.php?deleted=1' . $page);
            exit;
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Check for success messages from redirect
if (isset($_GET['updated'])) {
    $message = 'Member successfully updated!';
    $message_type = 'success';
} elseif (isset($_GET['deleted'])) {
    $message = 'Member successfully deleted!';
    $message_type = 'success';
} elseif (isset($_GET['added'])) {
    $message = 'Member successfully added!';
    $message_type = 'success';
}

// Pagination setup
$items_per_page = 10;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Get total count
$stmt = $conn->query("SELECT COUNT(*) FROM member");
$total_items = $stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

// Get members with pagination (all fields are already in member table)
// Order by: Ketua Lab first, then others by name
$stmt = $conn->prepare("SELECT * 
                      FROM member 
                      ORDER BY 
                        CASE 
                          WHEN LOWER(jabatan) LIKE '%ketua lab%' THEN 0 
                          ELSE 1 
                        END,
                        nama
                      LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$members = $stmt->fetchAll();

require __DIR__ . '/../views/admin/member.php';
