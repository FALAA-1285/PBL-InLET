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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Gallery - CMS InLET</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <style>
        body {
            background: var(--light);
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .content-header h1 {
            color: var(--primary);
            font-size: 2rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .content-header h1 i {
            background: rgba(99, 102, 241, 0.12);
            color: var(--primary);
            padding: 0.5rem;
            border-radius: 12px;
            font-size: 1.75rem;
        }

        .cms-content {
            max-width: 1200px;
            margin: 0 auto;
            padding-bottom: 4rem;
        }

        .message {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .message.success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .message.error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .form-section,
        .data-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .form-section h2,
        .data-section h2 {
            color: var(--primary);
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark);
            font-weight: 500;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
        }

        .btn-submit {
            background: var(--primary);
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-cancel {
            background: #6b7280;
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-left: 1rem;
        }

        .btn-cancel:hover {
            background: #4b5563;
        }

        .btn-edit {
            background: #3b82f6;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
            margin-right: 0.5rem;
        }

        .btn-edit:hover {
            background: #2563eb;
        }

        .btn-delete {
            background: #ef4444;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .btn-delete:hover {
            background: #dc2626;
        }

        .table-container {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .data-table th {
            background: var(--light);
            color: var(--dark);
            font-weight: 600;
        }

        .data-table tr:hover {
            background: var(--light);
        }

        .image-cell img {
            max-width: 150px;
            max-height: 100px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
        }

        .image-cell {
            min-width: 160px;
        }

        .help-text {
            color: var(--gray);
            font-size: 0.85rem;
            display: block;
            margin-top: 0.35rem;
        }

        .edit-form-section {
            display: none;
        }

        .edit-form-section.active {
            display: block;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
            padding: 1rem;
        }

        .pagination a,
        .pagination span {
            padding: 0.5rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            text-decoration: none;
            color: var(--dark);
            transition: all 0.3s;
        }

        .pagination .active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .pagination .disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        .pagination a:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .pagination-info {
            text-align: center;
            color: var(--gray);
            margin-top: 1rem;
        }
    </style>
</head>

<body>
    <?php $active_page = 'gallery';
    include __DIR__ . '/partials/sidebar.php'; ?>
    <main class="content">
        <div class="content-inner">
            <div class="content-header">
                <h1><i class="ri-image-line"></i> Gallery</h1>
            </div>

            <div class="cms-content">
                <?php if ($message): ?>
                    <div class="message <?php echo htmlspecialchars($message_type); ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <div id="edit-form-section" class="form-section edit-form-section">
                    <h2>Edit Gallery Image</h2>
                    <form method="POST" enctype="multipart/form-data" id="edit-gallery-form">
                        <input type="hidden" name="action" value="update_gallery">
                        <input type="hidden" name="id" id="edit_id">
                        <input type="hidden" name="current_image" id="edit_current_image">
                        <div class="form-group">
                            <label>Title *</label>
                            <input type="text" name="title" id="edit_title" required>
                        </div>
                        <div class="form-group">
                            <label>Related News (Optional)</label>
                            <select name="id_news" id="edit_id_news">
                                <option value="">-- Not Related --</option>
                                <?php foreach ($news_options as $news): ?>
                                    <option value="<?php echo $news['id_berita']; ?>">
                                        <?php echo htmlspecialchars($news['judul']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="help-text">If no file or URL is uploaded, image will use news thumbnail.</span>
                        </div>
                        <div class="form-group">
                            <label>Upload Image File</label>
                            <input type="file" name="image_file"
                                accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                            <span class="help-text">Max 5MB. If uploaded, this will override news/URL selection.</span>
                        </div>
                        <div class="form-group">
                            <label>Or Enter Image URL</label>
                            <input type="text" name="image_url" id="edit_image_url"
                                placeholder="https://example.com/image.jpg">
                            <span class="help-text">Optional. Will override news image if filled.</span>
                        </div>
                        <button type="submit" class="btn-submit">Save Changes</button>
                        <button type="button" class="btn-cancel" onclick="cancelEdit()">Cancel</button>
                    </form>
                </div>

                <div class="form-section" id="add-form-section">
                    <h2>Add New Image</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add_gallery">
                        <div class="form-group">
                            <label>Title *</label>
                            <input type="text" name="title" required placeholder="Image title">
                        </div>
                        <div class="form-group">
                            <label>Related News (Optional)</label>
                            <select name="id_news">
                                <option value="">-- Not Related --</option>
                                <?php foreach ($news_options as $news): ?>
                                    <option value="<?php echo $news['id_berita']; ?>">
                                        <?php echo htmlspecialchars($news['judul']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="help-text">If no file/URL uploaded, image will use this news thumbnail.</span>
                        </div>
                        <div class="form-group">
                            <label>Upload Image File</label>
                            <input type="file" name="image_file"
                                accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                            <span class="help-text">Max 5MB. Overrides URL and news image.</span>
                        </div>
                        <div class="form-group">
                            <label>Or Enter Image URL</label>
                            <input type="text" name="image_url" placeholder="https://example.com/image.jpg">
                            <span class="help-text">One option must be filled: select news, upload file, or enter
                                URL.</span>
                        </div>
                        <button type="submit" class="btn-submit">Add Image</button>
                    </form>
                </div>

                <div class="data-section">
                    <h2>Gallery List (<?php echo count($gallery_items); ?>)</h2>
                    <?php if (empty($gallery_items)): ?>
                        <p class="text-center p-4 muted-gray">No gallery data yet.</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Related News</th>
                                        <th>Image</th>
                                        <th>Created</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($gallery_items as $item): ?>
                                        <tr>
                                            <td><?php echo $item['id_gallery']; ?></td>
                                            <td><?php echo htmlspecialchars($item['judul']); ?></td>
                                            <td><?php echo htmlspecialchars($item['news_title'] ?? '-'); ?></td>
                                            <td class="image-cell">
                                                <?php if (!empty($item['gambar'])): ?>
                                                    <img src="<?php echo htmlspecialchars($item['gambar']); ?>"
                                                        alt="<?php echo htmlspecialchars($item['judul']); ?>"
                                                        onerror="this.style.display='none'">
                                                <?php else: ?>
                                                    <span class="muted-gray">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $created = $item['created_at'] ?? null;
                                                echo $created ? date('d M Y', strtotime($created)) : '-';
                                                ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn-edit"
                                                    onclick="editGallery(<?php echo htmlspecialchars(json_encode($item)); ?>)">
                                                    <i class="ri-edit-line"></i> Edit
                                                </button>
                                                <form method="POST" class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this image?');">
                                                    <input type="hidden" name="action" value="delete_gallery">
                                                    <input type="hidden" name="id" value="<?php echo $item['id_gallery']; ?>">
                                                    <button type="submit" class="btn-delete">
                                                        <i class="ri-delete-bin-line"></i> Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($current_page > 1): ?>
                                <a href="?page=<?php echo $current_page - 1; ?>">&laquo; Previous</a>
                            <?php else: ?>
                                <span class="disabled">&laquo; Previous</span>
                            <?php endif; ?>

                            <?php
                            $start_page = max(1, $current_page - 2);
                            $end_page = min($total_pages, $current_page + 2);
                            if ($start_page > 1): ?>
                                <a href="?page=1">1</a>
                                <?php if ($start_page > 2): ?><span>...</span><?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <a href="?page=<?php echo $i; ?>"
                                    class="<?php echo $i == $current_page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                            <?php endfor; ?>

                            <?php if ($end_page < $total_pages): ?>
                                <?php if ($end_page < $total_pages - 1): ?><span>...</span><?php endif; ?>
                                <a href="?page=<?php echo $total_pages; ?>"><?php echo $total_pages; ?></a>
                            <?php endif; ?>

                            <?php if ($current_page < $total_pages): ?>
                                <a href="?page=<?php echo $current_page + 1; ?>">Next &raquo;</a>
                            <?php else: ?>
                                <span class="disabled">Next &raquo;</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($total_pages > 1): ?>
                        <div class="pagination-info">
                            Page <?php echo $current_page; ?> of <?php echo $total_pages; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        function editGallery(item) {
            document.getElementById('edit_id').value = item.id_gallery;
            document.getElementById('edit_title').value = item.judul;
            document.getElementById('edit_id_news').value = item.id_berita || '';
            document.getElementById('edit_image_url').value = '';
            document.getElementById('edit_current_image').value = item.gambar || '';

            document.getElementById('add-form-section').style.display = 'none';
            document.getElementById('edit-form-section').classList.add('active');
            document.getElementById('edit-form-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function cancelEdit() {
            document.getElementById('edit-gallery-form').reset();
            document.getElementById('edit-form-section').classList.remove('active');
            document.getElementById('add-form-section').style.display = 'block';
        }
    </script>
</body>

</html>