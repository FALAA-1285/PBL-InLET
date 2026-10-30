<?php
require_once '../config/auth.php';
require_once '../config/upload.php';
requireAdmin();

$conn = getDBConnection();
$message = '';
$message_type = '';

function getNewsThumbnail(PDO $conn, $id_berita)
{
    if (!$id_berita) {
        return null;
    }

    $stmt = $conn->prepare("SELECT gambar_thumbnail FROM berita WHERE id_berita = :id");
    $stmt->execute(['id' => $id_berita]);
    $news = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($news && !empty($news['gambar_thumbnail'])) {
        return $news['gambar_thumbnail'];
    }
    return null;
}

function startsWith($haystack, $needle)
{
    return $needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
}

function isLocalGalleryPath($path)
{
    return startsWith($path, 'uploads/gallery/');
}

function cleanupGalleryFile($path)
{
    if ($path && isLocalGalleryPath($path)) {
        deleteUploadedFile($path);
    }
}

function isValidImageReference($input)
{
    if ($input === '') {
        return false;
    }
    if (filter_var($input, FILTER_VALIDATE_URL)) {
        return true;
    }
    return startsWith($input, 'uploads/') || startsWith($input, '/uploads/') || startsWith($input, 'assets/') || startsWith($input, '/assets/');
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_gallery') {
        $title = trim($_POST['title'] ?? '');
        $image_url = trim($_POST['image_url'] ?? '');
        $id_news_input = intval($_POST['id_news'] ?? 0);
        $id_news = $id_news_input > 0 ? $id_news_input : null;
        $final_image = '';

        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImage($_FILES['image_file'], 'gallery/');
            if ($uploadResult['success']) {
                $final_image = $uploadResult['path'];
            } else {
                $message = $uploadResult['message'];
                $message_type = 'error';
            }
        } elseif ($image_url !== '') {
            if (isValidImageReference($image_url)) {
                $final_image = $image_url;
            } else {
                $message = 'Invalid image URL.';
                $message_type = 'error';
            }
        } elseif ($id_news) {
            $news_thumbnail = getNewsThumbnail($conn, $id_news);
            if ($news_thumbnail) {
                $final_image = $news_thumbnail;
            } else {
                $message = 'The selected news has no thumbnail image yet.';
                $message_type = 'error';
            }
        } else {
            $message = 'Please select related news or upload/enter an image URL.';
            $message_type = 'error';
        }

        if (empty($title)) {
            $message = 'Title is required.';
            $message_type = 'error';
        }

        if (empty($message)) {
            try {
                // Try to alter gambar column to TEXT if it's still VARCHAR
                try {
                    $conn->exec("ALTER TABLE gallery ALTER COLUMN gambar TYPE TEXT");
                } catch (PDOException $e) {
                    // Column might already be TEXT or error, continue anyway
                }
                
                $stmt = $conn->prepare("INSERT INTO gallery (id_berita, judul, gambar, created_at, updated_at) VALUES (:id_news, :title, :image, NOW(), NOW())");
                $stmt->execute([
                    'id_news' => $id_news,
                    'title' => $title,
                    'image' => $final_image ?: null,
                ]);
                $message = 'Gallery image added successfully!';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'update_gallery') {
        $id = intval($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $image_url = trim($_POST['image_url'] ?? '');
        $id_news_input = intval($_POST['id_news'] ?? 0);
        $id_news = $id_news_input > 0 ? $id_news_input : null;
        $current_image = trim($_POST['current_image'] ?? '');
        $final_image = $current_image;

        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImage($_FILES['image_file'], 'gallery/');
            if ($uploadResult['success']) {
                $final_image = $uploadResult['path'];
                if ($current_image && $final_image !== $current_image) {
                    cleanupGalleryFile($current_image);
                }
            } else {
                $message = $uploadResult['message'];
                $message_type = 'error';
            }
        } elseif ($image_url !== '') {
            if (isValidImageReference($image_url)) {
                if ($current_image && isLocalGalleryPath($current_image) && $current_image !== $image_url) {
                    cleanupGalleryFile($current_image);
                }
                $final_image = $image_url;
            } else {
                $message = 'Invalid image URL.';
                $message_type = 'error';
            }
        } elseif ($id_news) {
            $news_thumbnail = getNewsThumbnail($conn, $id_news);
            if ($news_thumbnail) {
                if ($current_image && isLocalGalleryPath($current_image) && $current_image !== $news_thumbnail) {
                    cleanupGalleryFile($current_image);
                }
                $final_image = $news_thumbnail;
            } else {
                $message = 'The selected news has no thumbnail image yet.';
                $message_type = 'error';
            }
        } elseif (empty($current_image)) {
            $message = 'Please select related news or upload/enter an image URL.';
            $message_type = 'error';
        }

        if (empty($title)) {
            $message = 'Title is required.';
            $message_type = 'error';
        }

        if (empty($message)) {
            try {
                // Try to alter gambar column to TEXT if it's still VARCHAR
                try {
                    $conn->exec("ALTER TABLE gallery ALTER COLUMN gambar TYPE TEXT");
                } catch (PDOException $e) {
                    // Column might already be TEXT or error, continue anyway
                }
                
                $stmt = $conn->prepare("UPDATE gallery SET id_berita = :id_news, judul = :title, gambar = :image, updated_at = NOW() WHERE id_gallery = :id");
                $stmt->execute([
                    'id' => $id,
                    'id_news' => $id_news,
                    'title' => $title,
                    'image' => $final_image,
                ]);
                $message = 'Gallery data updated successfully!';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'delete_gallery') {
        $id = intval($_POST['id'] ?? 0);
        try {
            $stmt = $conn->prepare("SELECT gambar FROM gallery WHERE id_gallery = :id");
            $stmt->execute(['id' => $id]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $conn->prepare("DELETE FROM gallery WHERE id_gallery = :id");
            $stmt->execute(['id' => $id]);

            if ($existing && isset($existing['gambar'])) {
                cleanupGalleryFile($existing['gambar']);
            }

            $message = 'Gallery data deleted successfully.';
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
$stmt = $conn->query("SELECT COUNT(*) FROM gallery");
$total_items = (int) $stmt->fetchColumn();
$total_pages = (int) ceil($total_items / max(1, $items_per_page));

// Fetch gallery entries
$stmt = $conn->prepare("SELECT g.id_gallery, g.id_berita, g.judul, g.gambar, g.created_at, g.updated_at, b.judul AS news_title FROM gallery g LEFT JOIN berita b ON g.id_berita = b.id_berita ORDER BY g.created_at DESC, g.id_gallery DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$gallery_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch news options for optional relation
$news_stmt = $conn->query("SELECT id_berita, judul FROM berita ORDER BY created_at DESC, id_berita DESC");
$news_options = $news_stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/../views/admin/gallery.php';
