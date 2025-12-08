<?php
require_once '../config/auth.php';
require_once '../config/upload.php';
requireAdmin();

$conn = getDBConnection();
$message = '';
$message_type = '';

// Create video table if it doesn't exist
try {
    $conn->exec("
        CREATE TABLE IF NOT EXISTS video (
            id_video SERIAL PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            href_link TEXT,
            created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
        )
    ");
    
    // Create sequence if it doesn't exist
    $conn->exec("
        CREATE SEQUENCE IF NOT EXISTS video_id_video_seq
        AS integer
        START WITH 1
        INCREMENT BY 1
        NO MINVALUE
        NO MAXVALUE
        CACHE 1
    ");
    
    // Set sequence owner
    $conn->exec("
        ALTER SEQUENCE video_id_video_seq OWNED BY video.id_video
    ");
    
    // Set default value for id_video
    $conn->exec("
        ALTER TABLE video ALTER COLUMN id_video SET DEFAULT nextval('video_id_video_seq')
    ");
} catch (PDOException $e) {
    // Table might already exist, continue
}

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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage News - CMS InLET</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <style>
        body {
            background: var(--light);
        }

        .admin-header {
            background: white;
            padding: 1.5rem 2rem;
            box-shadow: 0 2px 14px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            border-radius: 18px;
        }

        .admin-header-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
        }

        .admin-header h1 {
            color: var(--dark);
            font-size: 1.5rem;
            margin-bottom: 0.35rem;
        }

        .admin-header p {
            color: var(--gray);
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

        .form-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .form-section h2 {
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
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
        }

        .form-group textarea {
            min-height: 200px;
            resize: vertical;
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

        .data-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .data-section h2 {
            color: var(--primary);
            margin-bottom: 1.5rem;
        }

        .table-container {
            overflow-x: auto;
        }

        .thumbnail-cell {
            max-width: 120px;
        }

        .thumbnail-cell img {
            max-width: 100px;
            max-height: 60px;
            object-fit: cover;
            border-radius: 8px;
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

        .edit-form-section {
            display: none;
        }

        .edit-form-section.active {
            display: block;
        }

        #edit-video-form-section {
            display: none;
        }

        #edit-video-form-section.active {
            display: block;
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

        .pagination a:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
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

        .pagination-info {
            text-align: center;
            color: var(--gray);
            margin-top: 1rem;
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

        .tab-navigation {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .tab-btn {
            padding: 0.75rem 2rem;
            border: none;
            background: transparent;
            color: var(--gray);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .tab-btn:hover {
            color: var(--primary);
        }

        .tab-btn.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .tab-content {
            display: block;
        }
    </style>
</head>

<body>
    <?php $active_page = 'news';
    include __DIR__ . '/partials/sidebar.php'; ?>
    <main class="content">
        <div class="content-inner">
            <h1 class="text-primary mb-4"><i class="ri-newspaper-line"></i> Manage Members</h1>


            <div class="cms-content">
                <?php if ($message): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Tab Navigation -->
                <div class="tab-navigation" style="margin-bottom: 2rem; border-bottom: 2px solid #e2e8f0;">
                    <a href="?tab=news<?php echo isset($_GET['page']) ? '&page=' . intval($_GET['page']) : ''; ?>" 
                       class="tab-btn <?php echo $active_tab === 'news' ? 'active' : ''; ?>" 
                       id="tab-news">News</a>
                    <a href="?tab=video<?php echo isset($_GET['page']) ? '&page=' . intval($_GET['page']) : ''; ?>" 
                       class="tab-btn <?php echo $active_tab === 'video' ? 'active' : ''; ?>" 
                       id="tab-video">Video</a>
                </div>

                <!-- News Tab Content -->
                <div id="tab-content-news" class="tab-content" style="display: <?php echo $active_tab === 'news' ? 'block' : 'none'; ?>;">

                <!-- Edit Form Section (Hidden by default) -->
                <div id="edit-form-section" class="form-section edit-form-section">
                    <h2>Edit News</h2>
                    <form method="POST" action="" enctype="multipart/form-data" id="edit-news-form">
                        <input type="hidden" name="action" value="update_news">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="form-group">
                            <label>News Title *</label>
                            <input type="text" name="judul" id="edit_judul" required>
                        </div>
                        <div class="form-group">
                            <label>Content *</label>
                            <textarea name="konten" id="edit_konten" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Upload Gambar Thumbnail (File)</label>
                            <input type="file" name="gambar_file"
                                accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                            <small class="d-block mt-2 text-muted small">Maksimal 5MB. Format: JPG, PNG, GIF,
                                WEBP</small>
                        </div>
                        <div class="form-group">
                            <label>Or Enter Image URL</label>
                            <input type="text" name="gambar_thumbnail" id="edit_gambar_thumbnail"
                                placeholder="https://example.com/image.jpg">
                            <small class="d-block mt-2 text-muted small">If file upload is used, URL will be
                                ignored</small>
                        </div>
                        <div class="form-group">
                            <label>Tanggal *</label>
                            <input type="date" name="tanggal" id="edit_tanggal" required>
                        </div>
                        <button type="submit" class="btn-submit">Update News</button>
                        <button type="button" class="btn-cancel" onclick="cancelEdit()">Cancel</button>
                    </form>
                </div>

                <div class="form-section">
                    <h2>Add New News</h2>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add_news">
                        <div class="form-group">
                            <label>News Title *</label>
                            <input type="text" name="judul" required>
                        </div>
                        <div class="form-group">
                            <label>Content *</label>
                            <textarea name="konten" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Upload Gambar Thumbnail (File)</label>
                            <input type="file" name="gambar_file"
                                accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                            <small class="d-block mt-2 text-muted small">Maksimal 5MB. Format: JPG, PNG, GIF,
                                WEBP</small>
                        </div>
                        <div class="form-group">
                            <label>Or Enter Image URL</label>
                            <input type="text" name="gambar_thumbnail" placeholder="https://example.com/image.jpg">
                            <small class="d-block mt-2 text-muted small">If file upload is used, URL will be
                                ignored</small>
                        </div>
                        <div class="form-group">
                            <label>Tanggal *</label>
                            <input type="date" name="tanggal" required>
                        </div>
                        <button type="submit" class="btn-submit">Add News</button>
                    </form>
                </div>

                <div class="data-section">
                    <h2>News List (<?php echo count($news_list); ?>)</h2>
                    <?php if (empty($news_list)): ?>
                        <p class="text-center p-4 muted-gray">No news yet</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Thumbnail</th>
                                        <th>Title</th>
                                        <th>Content</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($news_list as $news): ?>
                                        <tr>
                                            <td><?php echo $news['id_berita']; ?></td>
                                            <td class="thumbnail-cell">
                                                <?php if ($news['gambar_thumbnail']): ?>
                                                    <img src="<?php echo htmlspecialchars($news['gambar_thumbnail']); ?>"
                                                        alt="<?php echo htmlspecialchars($news['judul']); ?>"
                                                        onerror="this.style.display='none'">
                                                <?php else: ?>
                                                    <span class="muted-gray">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($news['judul']); ?></td>
                                            <td><?php echo htmlspecialchars(substr($news['konten'], 0, 100)) . (strlen($news['konten']) > 100 ? '...' : ''); ?>
                                            </td>
                                            <td><?php echo date('d M Y', strtotime($news['created_at'])); ?></td>
                                            <td>
                                                <button type="button" class="btn-edit"
                                                    onclick="editNews(<?php echo htmlspecialchars(json_encode($news)); ?>)">
                                                    <i class="ri-edit-line"></i> Edit
                                                </button>
                                                <form method="POST" class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this news?');">
                                                    <input type="hidden" name="action" value="delete_news">
                                                    <input type="hidden" name="id" value="<?php echo $news['id_berita']; ?>">
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
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <?php 
                            $tab_param = $active_tab === 'news' ? '' : '&tab=' . $active_tab;
                            ?>
                            <div class="pagination">
                                <?php if ($current_page > 1): ?>
                                    <a href="?page=<?php echo $current_page - 1; ?><?php echo $tab_param; ?>">&laquo; Previous</a>
                                <?php else: ?>
                                    <span class="disabled">&laquo; Previous</span>
                                <?php endif; ?>

                                <?php
                                $start_page = max(1, $current_page - 2);
                                $end_page = min($total_pages, $current_page + 2);

                                if ($start_page > 1): ?>
                                    <a href="?page=1<?php echo $tab_param; ?>">1</a>
                                    <?php if ($start_page > 2): ?>
                                        <span>...</span>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <?php if ($i == $current_page): ?>
                                        <span class="active"><?php echo $i; ?></span>
                                    <?php else: ?>
                                        <a href="?page=<?php echo $i; ?><?php echo $tab_param; ?>"><?php echo $i; ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($end_page < $total_pages): ?>
                                    <?php if ($end_page < $total_pages - 1): ?>
                                        <span>...</span>
                                    <?php endif; ?>
                                    <a href="?page=<?php echo $total_pages; ?><?php echo $tab_param; ?>"><?php echo $total_pages; ?></a>
                                <?php endif; ?>

                                <?php if ($current_page < $total_pages): ?>
                                    <a href="?page=<?php echo $current_page + 1; ?><?php echo $tab_param; ?>">Next &raquo;</a>
                                <?php else: ?>
                                    <span class="disabled">Next &raquo;</span>
                                <?php endif; ?>
                            </div>
                            <div class="pagination-info">
                                Showing <?php echo ($offset + 1); ?> -
                                <?php echo min($offset + $items_per_page, $total_items); ?> of <?php echo $total_items; ?> news
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    </div>
                </div>

                <!-- Video Tab Content -->
                <div id="tab-content-video" class="tab-content" style="display: <?php echo $active_tab === 'video' ? 'block' : 'none'; ?>;">
                    <!-- Edit Video Form Section (Hidden by default) -->
                    <div id="edit-video-form-section" class="form-section edit-form-section">
                        <h2>Edit Video</h2>
                        <form method="POST" action="" enctype="multipart/form-data" id="edit-video-form">
                            <input type="hidden" name="action" value="update_video">
                            <input type="hidden" name="id" id="edit_video_id">
                            <div class="form-group">
                                <label>Video Title *</label>
                                <input type="text" name="title" id="edit_video_title" required>
                            </div>
                            <div class="form-group">
                                <label>Upload Video File</label>
                                <input type="file" name="video_file" accept="video/*" id="edit_video_file">
                                <small class="d-block mt-2 text-muted small">Maksimal 100MB. Format: MP4, AVI, MOV, WEBM, etc.</small>
                            </div>
                            <div class="form-group">
                                <label>Or Enter Video URL/Link</label>
                                <input type="text" name="href_link" id="edit_video_href_link"
                                    placeholder="https://example.com/video.mp4 or https://youtube.com/watch?v=...">
                                <small class="d-block mt-2 text-muted small">If file upload is used, URL will be ignored. Leave empty if uploading file.</small>
                            </div>
                            <button type="submit" class="btn-submit">Update Video</button>
                            <button type="button" class="btn-cancel" onclick="cancelEditVideo()">Cancel</button>
                        </form>
                    </div>

                    <div class="form-section">
                        <h2>Add New Video</h2>
                        <form method="POST" action="" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="add_video">
                            <div class="form-group">
                                <label>Video Title *</label>
                                <input type="text" name="title" required>
                            </div>
                            <div class="form-group">
                                <label>Upload Video File</label>
                                <input type="file" name="video_file" accept="video/*" id="add_video_file">
                                <small class="d-block mt-2 text-muted small">Maksimal 100MB. Format: MP4, AVI, MOV, WEBM, etc.</small>
                            </div>
                            <div class="form-group">
                                <label>Or Enter Video URL/Link</label>
                                <input type="text" name="href_link" id="add_video_url" placeholder="https://example.com/video.mp4 or https://youtube.com/watch?v=...">
                                <small class="d-block mt-2 text-muted small">If file upload is used, URL will be ignored. Leave empty if uploading file.</small>
                            </div>
                            <button type="submit" class="btn-submit">Add Video</button>
                        </form>
                    </div>

                    <div class="data-section">
                        <h2>Video List (<?php echo count($video_list); ?>)</h2>
                        <?php if (empty($video_list)): ?>
                            <p class="text-center p-4 muted-gray">No videos yet</p>
                        <?php else: ?>
                            <div class="table-container">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Title</th>
                                            <th>Link</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($video_list as $video): ?>
                                            <tr>
                                                <td><?php echo $video['id_video']; ?></td>
                                                <td><?php echo htmlspecialchars($video['title']); ?></td>
                                                <td>
                                                    <a href="<?php echo htmlspecialchars($video['href_link']); ?>" target="_blank" rel="noopener noreferrer" style="color: var(--primary); text-decoration: none;">
                                                        <?php echo htmlspecialchars(substr($video['href_link'], 0, 50)) . (strlen($video['href_link']) > 50 ? '...' : ''); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn-edit"
                                                        onclick="editVideo(<?php echo htmlspecialchars(json_encode($video)); ?>)">
                                                        <i class="ri-edit-line"></i> Edit
                                                    </button>
                                                    <form method="POST" class="d-inline"
                                                        onsubmit="return confirm('Are you sure you want to delete this video?');">
                                                        <input type="hidden" name="action" value="delete_video">
                                                        <input type="hidden" name="id" value="<?php echo $video['id_video']; ?>">
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
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function editNews(news) {
            // Populate edit form
            document.getElementById('edit_id').value = news.id_berita;
            document.getElementById('edit_judul').value = news.judul || '';
            document.getElementById('edit_konten').value = news.konten || '';
            document.getElementById('edit_gambar_thumbnail').value = news.gambar_thumbnail || '';
            
            // Set tanggal from created_at
            if (news.created_at) {
                // Convert timestamp to date format (YYYY-MM-DD)
                const date = new Date(news.created_at);
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                document.getElementById('edit_tanggal').value = `${year}-${month}-${day}`;
            } else {
                document.getElementById('edit_tanggal').value = '';
            }

            // Show edit form, hide add form
            document.getElementById('edit-form-section').classList.add('active');
            document.querySelector('.form-section:not(.edit-form-section)').style.display = 'none';

            // Scroll to edit form
            document.getElementById('edit-form-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function cancelEdit() {
            // Hide edit form, show add form
            document.getElementById('edit-form-section').classList.remove('active');
            document.querySelector('.form-section:not(.edit-form-section)').style.display = 'block';

            // Reset edit form
            document.getElementById('edit-news-form').reset();
        }

        // Tab switching is now handled by URL parameters, no JavaScript needed
        // But keep function for backward compatibility if needed
        function switchTab(tab) {
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            // Preserve page parameter if exists
            if (url.searchParams.has('page')) {
                // Keep page parameter
            } else {
                url.searchParams.delete('page');
            }
            window.location.href = url.toString();
        }

        function editVideo(video) {
            // Populate edit form
            document.getElementById('edit_video_id').value = video.id_video;
            document.getElementById('edit_video_title').value = video.title || '';
            document.getElementById('edit_video_href_link').value = video.href_link || '';

            // Show edit form, hide add form
            document.getElementById('edit-video-form-section').classList.add('active');
            document.querySelector('#tab-content-video .form-section:not(.edit-form-section)').style.display = 'none';

            // Scroll to edit form
            document.getElementById('edit-video-form-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function cancelEditVideo() {
            // Hide edit form, show add form
            document.getElementById('edit-video-form-section').classList.remove('active');
            document.querySelector('#tab-content-video .form-section:not(.edit-form-section)').style.display = 'block';

            // Reset edit form
            document.getElementById('edit-video-form').reset();
        }
    </script>
</body>

</html>