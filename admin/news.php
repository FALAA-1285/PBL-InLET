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

    if ($action === 'add_news') {
        $judul = $_POST['judul'] ?? '';
        $konten = $_POST['konten'] ?? '';
        $gambar_thumbnail = $_POST['gambar_thumbnail'] ?? ''; // URL input
        $tanggal = $_POST['tanggal'] ?? ''; // Date input

        // Handle file upload
        if (isset($_FILES['gambar_file']) && $_FILES['gambar_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImage($_FILES['gambar_file'], 'news/');
            if ($uploadResult['success']) {
                $gambar_thumbnail = $uploadResult['path'];
            } else {
                $message = $uploadResult['message'];
                $message_type = 'error';
            }
        }

        if (empty($message)) {
            try {
                // Try to alter gambar_thumbnail column to TEXT if it's still VARCHAR(255)
                try {
                    $conn->exec("ALTER TABLE berita ALTER COLUMN gambar_thumbnail TYPE TEXT");
                } catch (PDOException $e) {
                    // Column might already be TEXT or error, continue anyway
                }
                
                // Prepare created_at value
                $created_at = null;
                if (!empty($tanggal)) {
                    // Convert date to timestamp format
                    $created_at = date('Y-m-d H:i:s', strtotime($tanggal));
                }
                
                $stmt = $conn->prepare("INSERT INTO berita (judul, konten, gambar_thumbnail, created_at) VALUES (:judul, :konten, :gambar_thumbnail, :created_at)");
                $stmt->execute([
                    'judul' => $judul,
                    'konten' => $konten,
                    'gambar_thumbnail' => $gambar_thumbnail ?: null,
                    'created_at' => $created_at
                ]);
                $message = 'News successfully added!';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'update_news') {
        $id = $_POST['id'] ?? 0;
        $judul = $_POST['judul'] ?? '';
        $konten = $_POST['konten'] ?? '';
        $gambar_thumbnail = $_POST['gambar_thumbnail'] ?? '';
        $tanggal = $_POST['tanggal'] ?? ''; // Date input

        // Handle file upload
        if (isset($_FILES['gambar_file']) && $_FILES['gambar_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImage($_FILES['gambar_file'], 'news/');
            if ($uploadResult['success']) {
                $gambar_thumbnail = $uploadResult['path'];
            } else {
                $message = $uploadResult['message'];
                $message_type = 'error';
            }
        }

        if (empty($message)) {
            try {
                // Try to alter gambar_thumbnail column to TEXT if it's still VARCHAR(255)
                try {
                    $conn->exec("ALTER TABLE berita ALTER COLUMN gambar_thumbnail TYPE TEXT");
                } catch (PDOException $e) {
                    // Column might already be TEXT or error, continue anyway
                }
                
                // Prepare created_at value
                $created_at = null;
                if (!empty($tanggal)) {
                    // Convert date to timestamp format
                    $created_at = date('Y-m-d H:i:s', strtotime($tanggal));
                }
                
                $stmt = $conn->prepare("UPDATE berita SET judul = :judul, konten = :konten, gambar_thumbnail = :gambar_thumbnail, created_at = :created_at WHERE id_berita = :id");
                $stmt->execute([
                    'id' => $id,
                    'judul' => $judul,
                    'konten' => $konten,
                    'gambar_thumbnail' => $gambar_thumbnail ?: null,
                    'created_at' => $created_at
                ]);
                $message = 'News successfully updated!';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'delete_news') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $conn->prepare("DELETE FROM berita WHERE id_berita = :id");
            $stmt->execute(['id' => $id]);
            $message = 'News successfully deleted!';
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    } elseif ($action === 'add_video') {
        $title = $_POST['title'] ?? '';
        $href_link = $_POST['href_link'] ?? '';

        // Handle file upload
        if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
            // Check file size (max 100MB for video)
            $maxVideoSize = 100 * 1024 * 1024; // 100MB
            if ($_FILES['video_file']['size'] > $maxVideoSize) {
                $message = 'Video file is too large. Maximum 100MB.';
                $message_type = 'error';
            } else {
                // Upload video file using UPLOAD_DIR constant
                $uploadDir = UPLOAD_DIR . 'videos/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // Generate unique filename
                $extension = pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION);
                $fileName = uniqid('video_', true) . '.' . $extension;
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['video_file']['tmp_name'], $targetPath)) {
                    $href_link = UPLOAD_URL . 'videos/' . $fileName;
                } else {
                    $message = 'Error uploading video file. Please check folder permissions.';
                    $message_type = 'error';
                }
            }
        }

        if (empty($message)) {
            if (empty($title)) {
                $message = 'Title is required!';
                $message_type = 'error';
            } elseif (empty($href_link)) {
                $message = 'Video link or file is required!';
                $message_type = 'error';
            } else {
                try {
                    $stmt = $conn->prepare("INSERT INTO video (title, href_link) VALUES (:title, :href_link)");
                    $stmt->execute([
                        'title' => $title,
                        'href_link' => $href_link
                    ]);
                    $message = 'Video successfully added!';
                    $message_type = 'success';
                } catch (PDOException $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $message_type = 'error';
                }
            }
        }
    } elseif ($action === 'update_video') {
        $id = $_POST['id'] ?? 0;
        $title = $_POST['title'] ?? '';
        $href_link = $_POST['href_link'] ?? '';

        // Handle file upload
        if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
            // Check file size (max 100MB for video)
            $maxVideoSize = 100 * 1024 * 1024; // 100MB
            if ($_FILES['video_file']['size'] > $maxVideoSize) {
                $message = 'Video file is too large. Maximum 100MB.';
                $message_type = 'error';
            } else {
                // Upload video file using UPLOAD_DIR constant
                $uploadDir = UPLOAD_DIR . 'videos/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // Generate unique filename
                $extension = pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION);
                $fileName = uniqid('video_', true) . '.' . $extension;
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['video_file']['tmp_name'], $targetPath)) {
                    $href_link = UPLOAD_URL . 'videos/' . $fileName;
                } else {
                    $message = 'Error uploading video file. Please check folder permissions.';
                    $message_type = 'error';
                }
            }
        }

        if (empty($message)) {
            if (empty($title)) {
                $message = 'Title is required!';
                $message_type = 'error';
            } elseif (empty($href_link)) {
                $message = 'Video link or file is required!';
                $message_type = 'error';
            } else {
                try {
                    $stmt = $conn->prepare("UPDATE video SET title = :title, href_link = :href_link WHERE id_video = :id");
                    $stmt->execute([
                        'id' => $id,
                        'title' => $title,
                        'href_link' => $href_link
                    ]);
                    $message = 'Video successfully updated!';
                    $message_type = 'success';
                } catch (PDOException $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $message_type = 'error';
                }
            }
        }
    } elseif ($action === 'delete_video') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $conn->prepare("DELETE FROM video WHERE id_video = :id");
            $stmt->execute(['id' => $id]);
            $message = 'Video successfully deleted!';
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
$stmt = $conn->query("SELECT COUNT(*) FROM berita");
$total_items = $stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

// Get news with pagination
$stmt = $conn->prepare("SELECT * FROM berita ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$news_list = $stmt->fetchAll();

// Get videos
$video_stmt = $conn->query("SELECT * FROM video ORDER BY created_at DESC");
$video_list = $video_stmt->fetchAll();

// Get video for edit
$edit_video = null;
if (isset($_GET['edit_video'])) {
    $edit_id = intval($_GET['edit_video']);
    $stmt = $conn->prepare("SELECT * FROM video WHERE id_video = :id");
    $stmt->execute(['id' => $edit_id]);
    $edit_video = $stmt->fetch();
}

// Get active tab from URL
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'news';
if (!in_array($active_tab, ['news', 'video'])) {
    $active_tab = 'news';
}

require __DIR__ . '/../views/admin/news.php';
