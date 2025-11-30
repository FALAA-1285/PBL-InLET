<?php
// gallery_admin.php
require_once '../config/auth.php';
requireAdmin();
require_once '../config/database.php';
require_once '../config/upload.php';

$conn = getDBConnection();
$message = '';
$message_type = '';

// ---------- Utilities ----------
function startsWith($haystack, $needle)
{
    if ($needle === '')
        return false;
    return substr($haystack, 0, strlen($needle)) === $needle;
}

function isLocalGalleryPath($path)
{
    if (!$path)
        return false;
    $p = ltrim($path, '/');
    return startsWith($p, 'uploads/gallery/');
}

function cleanupGalleryFile($path)
{
    if ($path && isLocalGalleryPath($path)) {
        deleteUploadedFile($path);
    }
}

function isValidImageReference($input)
{
    if ($input === '' || $input === null)
        return false;
    if (filter_var($input, FILTER_VALIDATE_URL))
        return true;
    $i = ltrim($input, '/');
    return startsWith($i, 'uploads/') || startsWith($i, 'assets/');
}

function getNewsThumbnail(PDO $conn, $id_berita)
{
    if (!$id_berita)
        return null;
    $stmt = $conn->prepare("SELECT gambar_thumbnail FROM berita WHERE id_berita = :id");
    $stmt->execute(['id' => $id_berita]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return ($row && !empty($row['gambar_thumbnail'])) ? $row['gambar_thumbnail'] : null;
}

// ---------- Handle POST actions (add, update, delete) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ---------- ADD ----------
    if ($action === 'add_gallery') {
        $judul = trim($_POST['judul'] ?? '');
        $gambar_url = trim($_POST['gambar_url'] ?? '');
        $id_berita_input = intval($_POST['id_berita'] ?? 0);
        $id_berita = $id_berita_input > 0 ? $id_berita_input : null;

        $final_image = null;

        // validation
        if ($judul === '') {
            $message = 'Judul wajib diisi.';
            $message_type = 'error';
        }

        // file upload has highest priority
        if (empty($message) && isset($_FILES['gambar_file']) && $_FILES['gambar_file']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadImage($_FILES['gambar_file'], 'gallery/');
            if ($upload['success']) {
                $final_image = $upload['path'];
            } else {
                $message = $upload['message'];
                $message_type = 'error';
            }
        }

        // URL input
        if (empty($message) && !$final_image && $gambar_url !== '') {
            if (!isValidImageReference($gambar_url)) {
                $message = 'URL gambar tidak valid.';
                $message_type = 'error';
            } else {
                $final_image = $gambar_url;
            }
        }

        // thumbnail dari berita
        if (empty($message) && !$final_image && $id_berita) {
            $thumb = getNewsThumbnail($conn, $id_berita);
            if ($thumb) {
                $final_image = $thumb;
            } else {
                $message = 'Berita yang dipilih belum memiliki gambar thumbnail.';
                $message_type = 'error';
            }
        }

        if (empty($message) && !$final_image) {
            $message = 'Silakan pilih berita terkait atau upload/masukkan URL gambar.';
            $message_type = 'error';
        }

        if (empty($message)) {
            try {
                $stmt = $conn->prepare("INSERT INTO gallery (id_berita, judul, gambar, created_at, updated_at) VALUES (:id_berita, :judul, :gambar, NOW(), NOW())");
                $stmt->execute([
                    'id_berita' => $id_berita,
                    'judul' => $judul,
                    'gambar' => $final_image
                ]);
                $message = 'Gambar gallery berhasil ditambahkan!';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    }

    // ---------- UPDATE ----------
    if ($action === 'update_gallery') {
        $id = intval($_POST['id'] ?? 0);
        $judul = trim($_POST['judul'] ?? '');
        $gambar_url = trim($_POST['gambar_url'] ?? '');
        $id_berita_input = intval($_POST['id_berita'] ?? 0);
        $id_berita = $id_berita_input > 0 ? $id_berita_input : null;
        $current_gambar = trim($_POST['current_gambar'] ?? '');
        $final_image = $current_gambar;

        if ($judul === '') {
            $message = 'Judul wajib diisi.';
            $message_type = 'error';
        }

        // file upload
        if (empty($message) && isset($_FILES['gambar_file']) && $_FILES['gambar_file']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadImage($_FILES['gambar_file'], 'gallery/');
            if ($upload['success']) {
                $final_image = $upload['path'];
                if ($current_gambar && $final_image !== $current_gambar) {
                    cleanupGalleryFile($current_gambar);
                }
            } else {
                $message = $upload['message'];
                $message_type = 'error';
            }
        }

        // url image
        if (empty($message) && !$final_image && $gambar_url !== '') {
            if (!isValidImageReference($gambar_url)) {
                $message = 'URL gambar tidak valid.';
                $message_type = 'error';
            } else {
                if ($current_gambar && isLocalGalleryPath($current_gambar) && $current_gambar !== $gambar_url) {
                    cleanupGalleryFile($current_gambar);
                }
                $final_image = $gambar_url;
            }
        }

        // berita thumbnail
        if (empty($message) && !$final_image && $id_berita) {
            $thumb = getNewsThumbnail($conn, $id_berita);
            if ($thumb) {
                if ($current_gambar && isLocalGalleryPath($current_gambar) && $current_gambar !== $thumb) {
                    cleanupGalleryFile($current_gambar);
                }
                $final_image = $thumb;
            } else {
                $message = 'Berita yang dipilih belum memiliki gambar thumbnail.';
                $message_type = 'error';
            }
        }

        // if user cleared current and didn't supply new
        if (empty($message) && empty($final_image)) {
            $message = 'Silakan pilih berita terkait atau upload/masukkan URL gambar.';
            $message_type = 'error';
        }

        if (empty($message)) {
            try {
                $stmt = $conn->prepare("UPDATE gallery SET id_berita = :id_berita, judul = :judul, gambar = :gambar, updated_at = NOW() WHERE id_gallery = :id");
                $stmt->execute([
                    'id' => $id,
                    'id_berita' => $id_berita,
                    'judul' => $judul,
                    'gambar' => $final_image
                ]);
                $message = 'Data gallery berhasil diperbarui!';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    }

    // ---------- DELETE ----------
    if ($action === 'delete_gallery') {
        $id = intval($_POST['id'] ?? 0);
        try {
            $stmt = $conn->prepare("SELECT gambar FROM gallery WHERE id_gallery = :id");
            $stmt->execute(['id' => $id]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $conn->prepare("DELETE FROM gallery WHERE id_gallery = :id");
            $stmt->execute(['id' => $id]);

            if ($existing && !empty($existing['gambar'])) {
                cleanupGalleryFile($existing['gambar']);
            }

            $message = 'Data gallery berhasil dihapus.';
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
    // after POST, reload to avoid resubmission (optional)
    if (!headers_sent()) {
        // keep message via session? For simplicity we just continue (page will render message)
    }
}

// ---------- Pagination & Fetch for admin table ----------
$items_per_page = 10;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

$stmt = $conn->query("SELECT COUNT(*) FROM gallery");
$total_items = (int) $stmt->fetchColumn();
$total_pages = (int) ceil($total_items / max(1, $items_per_page));

$stmt = $conn->prepare("SELECT g.id_gallery, g.id_berita, g.judul, g.gambar, g.created_at, g.updated_at, b.judul AS berita_judul FROM gallery g LEFT JOIN berita b ON g.id_berita = b.id_berita ORDER BY g.created_at DESC, g.id_gallery DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$gallery_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// For front gallery display (public), fetch a set (used later)
$gallery_init_stmt = $conn->prepare("SELECT id_gallery AS id, judul, gambar AS img, id_berita, created_at FROM gallery ORDER BY created_at DESC LIMIT 60");
$gallery_init_stmt->execute();
$gallery_init = $gallery_init_stmt->fetchAll(PDO::FETCH_ASSOC);

// fetch berita options
$news_stmt = $conn->query("SELECT id_berita, judul FROM berita ORDER BY created_at DESC, id_berita DESC");
$news_options = $news_stmt->fetchAll(PDO::FETCH_ASSOC);

// variables for front pagination (gallery) if needed later
$gallery_items_per_page = 12;
$gallery_page = isset($_GET['gpage']) ? max(1, intval($_GET['gpage'])) : 1;
$gallery_offset = ($gallery_page - 1) * $gallery_items_per_page;
$total_gallery_items = count($gallery_init); // using initial fetch size
$total_gallery_pages = (int) ceil($total_gallery_items / max(1, $gallery_items_per_page));
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <title>Kelola Gallery - CMS InLET</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
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
            align-items: center;
            gap: 1rem;
        }

        .admin-header h1 {
            color: var(--dark);
            font-size: 1.5rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
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
            font-size: 1.3rem;
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

        .form-group input[type="text"],
        .form-group input[type="file"],
        .form-group textarea,
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
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-group small {
            display: block;
            margin-top: 0.5rem;
            color: var(--gray);
            font-size: 0.875rem;
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

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: var(--light);
            color: var(--primary);
            font-weight: 600;
        }

        tr:hover {
            background: var(--light);
        }

        .image-cell {
            text-align: center;
        }

        .image-cell img {
            width: 120px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e2e8f0;
            transition: transform 0.3s;
        }

        .image-cell img:hover {
            transform: scale(1.05);
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

        .btn-primary-header {
            background: var(--primary);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary-header:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
    </style>
</head>

<body>
    <?php $active_page = 'gallery';
    include __DIR__ . '/partials/sidebar.php'; ?>

    <main class="content">
        <div class="content-inner">
            <h1 style="color: var(--primary); margin-bottom: 2rem;"><i class="ri-image-line"></i> Kelola Mitra Lab</h1>


            <div class="cms-content">

                <?php if ($message): ?>
                    <div class="message <?php echo htmlspecialchars($message_type); ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Edit Form (hidden by default) -->
                <div id="edit-form-section" class="form-section edit-form-section">
                    <h2>Edit Gambar Gallery</h2>
                    <form method="POST" enctype="multipart/form-data" id="edit-gallery-form">
                        <input type="hidden" name="action" value="update_gallery">
                        <input type="hidden" name="id" id="edit_id">
                        <input type="hidden" name="current_gambar" id="edit_current_gambar">
                        <div class="form-group">
                            <label>Judul *</label>
                            <input type="text" name="judul" id="edit_judul" required placeholder="Judul gambar">
                        </div>
                        <div class="form-group">
                            <label>Berita Terkait (Opsional)</label>
                            <select name="id_berita" id="edit_id_berita">
                                <option value="">-- Tidak dikaitkan --</option>
                                <?php foreach ($news_options as $news): ?>
                                    <option value="<?php echo $news['id_berita']; ?>">
                                        <?php echo htmlspecialchars($news['judul']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small>Jika tidak mengupload/URL, gambar dapat mengambil thumbnail berita ini (jika
                                ada).</small>
                        </div>
                        <div class="form-group">
                            <label>Upload Gambar (File)</label>
                            <input type="file" name="gambar_file" accept="image/*">
                            <small>Jika diisi, unggahan ini akan menggantikan gambar lama.</small>
                        </div>
                        <div class="form-group">
                            <label>Atau Masukkan URL Gambar</label>
                            <input type="text" name="gambar_url" id="edit_gambar_url"
                                placeholder="https://example.com/image.jpg">
                        </div>
                        <div>
                            <button type="submit" class="btn-submit">Simpan Perubahan</button>
                            <button type="button" class="btn-cancel" onclick="cancelEdit()">Batal</button>
                        </div>
                    </form>
                </div>

                <!-- Add Form -->
                <div id="add-form-section" class="form-section">
                    <h2>Tambah Gambar Baru</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add_gallery">
                        <div class="form-group">
                            <label>Judul *</label>
                            <input type="text" name="judul" required placeholder="Judul gambar">
                        </div>
                        <div class="form-group">
                            <label>Berita Terkait (Opsional)</label>
                            <select name="id_berita">
                                <option value="">-- Tidak dikaitkan --</option>
                                <?php foreach ($news_options as $news): ?>
                                    <option value="<?php echo $news['id_berita']; ?>">
                                        <?php echo htmlspecialchars($news['judul']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small style="display:block;color:var(--gray);">Jika tidak mengupload/URL, gambar dapat
                                mengambil thumbnail berita ini (jika ada).</small>
                        </div>
                        <div class="form-group">
                            <label>Upload Gambar (File)</label>
                            <input type="file" name="gambar_file" accept="image/*">
                        </div>
                        <div class="form-group">
                            <label>Atau Masukkan URL Gambar</label>
                            <input type="text" name="gambar_url" placeholder="https://example.com/image.jpg">
                        </div>
                        <div>
                            <button type="submit" class="btn-submit">Tambah Gambar</button>
                        </div>
                    </form>
                </div>

                <!-- Data Table -->
                <div class="data-section">
                    <h2>Daftar Gallery (<?php echo count($gallery_items); ?>)</h2>
                    <?php if (empty($gallery_items)): ?>
                        <p style="color:var(--gray); text-align:center; padding:1rem;">Belum ada data gallery.</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Judul</th>
                                        <th>Berita</th>
                                        <th>Gambar</th>
                                        <th>Dibuat</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($gallery_items as $item): ?>
                                        <tr>
                                            <td><?php echo $item['id_gallery']; ?></td>
                                            <td><?php echo htmlspecialchars($item['judul']); ?></td>
                                            <td><?php echo htmlspecialchars($item['berita_judul'] ?? '-'); ?></td>
                                            <td class="image-cell">
                                                <?php if (!empty($item['gambar'])): ?>
                                                    <img src="<?php echo htmlspecialchars($item['gambar']); ?>"
                                                        alt="<?php echo htmlspecialchars($item['judul']); ?>"
                                                        onerror="this.style.display='none'">
                                                <?php else: ?>
                                                    <span style="color:var(--gray);">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo !empty($item['created_at']) ? date('d M Y', strtotime($item['created_at'])) : '-'; ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn-edit"
                                                    onclick='editGallery(<?php echo json_encode($item, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                                    <i class="ri-edit-line"></i> Edit
                                                </button>
                                                <form method="POST" style="display:inline;"
                                                    onsubmit="return confirm('Yakin hapus gambar ini?');">
                                                    <input type="hidden" name="action" value="delete_gallery">
                                                    <input type="hidden" name="id" value="<?php echo $item['id_gallery']; ?>">
                                                    <button type="submit" class="btn-delete">
                                                        <i class="ri-delete-bin-line"></i> Hapus
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
                                    <?php if ($start_page > 2): ?>
                                        <span>...</span>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <?php if ($i == $current_page): ?>
                                        <span class="active"><?php echo $i; ?></span>
                                    <?php else: ?>
                                        <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($end_page < $total_pages): ?>
                                    <?php if ($end_page < $total_pages - 1): ?>
                                        <span>...</span>
                                    <?php endif; ?>
                                    <a href="?page=<?php echo $total_pages; ?>"><?php echo $total_pages; ?></a>
                                <?php endif; ?>

                                <?php if ($current_page < $total_pages): ?>
                                    <a href="?page=<?php echo $current_page + 1; ?>">Next &raquo;</a>
                                <?php else: ?>
                                    <span class="disabled">Next &raquo;</span>
                                <?php endif; ?>
                            </div>
                            <div class="pagination-info">
                                Menampilkan <?php echo ($offset + 1); ?> -
                                <?php echo min($offset + $items_per_page, $total_items); ?> dari <?php echo $total_items; ?>
                                gambar
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </main>

    <script>
        // ----- Edit gallery helper -----
        function editGallery(item) {
            // item passed as object
            var data = (typeof item === 'string') ? JSON.parse(item) : item;

            // populate form
            document.getElementById('edit_id').value = data.id_gallery || data.id || '';
            document.getElementById('edit_current_gambar').value = data.gambar || '';
            document.getElementById('edit_judul').value = data.judul || '';
            if (document.getElementById('edit_id_berita')) {
                document.getElementById('edit_id_berita').value = data.id_berita || '';
            }
            if (document.getElementById('edit_gambar_url')) {
                document.getElementById('edit_gambar_url').value = data.gambar || '';
            }

            // show edit form
            document.getElementById('edit-form-section').classList.add('active');
            document.getElementById('add-form-section').style.display = 'none';
            document.getElementById('edit-form-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function cancelEdit() {
            var form = document.getElementById('edit-gallery-form');
            if (form) form.reset();
            document.getElementById('edit-form-section').classList.remove('active');
            document.getElementById('add-form-section').style.display = 'block';
        }
    </script>
</body>

</html>