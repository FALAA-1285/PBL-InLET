<?php
require_once '../config/auth.php';
require_once '../config/upload.php';
requireAdmin();

$conn = getDBConnection();
$message = '';
$message_type = '';

function getNewsThumbnail(PDO $conn, $id_berita) {
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

function startsWith($haystack, $needle) {
    return $needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
}

function isLocalGalleryPath($path) {
    return startsWith($path, 'uploads/gallery/');
}

function cleanupGalleryFile($path) {
    if ($path && isLocalGalleryPath($path)) {
        deleteUploadedFile($path);
    }
}

function isValidImageReference($input) {
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
        $judul = trim($_POST['judul'] ?? '');
        $gambar_url = trim($_POST['gambar_url'] ?? '');
        $id_berita_input = intval($_POST['id_berita'] ?? 0);
        $id_berita = $id_berita_input > 0 ? $id_berita_input : null;
        $final_image = '';

        if (isset($_FILES['gambar_file']) && $_FILES['gambar_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImage($_FILES['gambar_file'], 'gallery/');
            if ($uploadResult['success']) {
                $final_image = $uploadResult['path'];
            } else {
                $message = $uploadResult['message'];
                $message_type = 'error';
            }
        } elseif ($gambar_url !== '') {
            if (isValidImageReference($gambar_url)) {
                $final_image = $gambar_url;
            } else {
                $message = 'URL gambar tidak valid.';
                $message_type = 'error';
            }
        } elseif ($id_berita) {
            $news_thumbnail = getNewsThumbnail($conn, $id_berita);
            if ($news_thumbnail) {
                $final_image = $news_thumbnail;
            } else {
                $message = 'Berita yang dipilih belum memiliki gambar thumbnail.';
                $message_type = 'error';
            }
        } else {
            $message = 'Silakan pilih berita terkait atau upload/masukkan URL gambar.';
            $message_type = 'error';
        }

        if (empty($judul)) {
            $message = 'Judul wajib diisi.';
            $message_type = 'error';
        }

        if (empty($message)) {
            try {
                $stmt = $conn->prepare("INSERT INTO gallery (id_berita, judul, gambar, created_at, updated_at) VALUES (:id_berita, :judul, :gambar, NOW(), NOW())");
                $stmt->execute([
                    'id_berita' => $id_berita,
                    'judul' => $judul,
                    'gambar' => $final_image ?: null,
                ]);
                $message = 'Gambar gallery berhasil ditambahkan!';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'update_gallery') {
        $id = intval($_POST['id'] ?? 0);
        $judul = trim($_POST['judul'] ?? '');
        $gambar_url = trim($_POST['gambar_url'] ?? '');
        $id_berita_input = intval($_POST['id_berita'] ?? 0);
        $id_berita = $id_berita_input > 0 ? $id_berita_input : null;
        $current_gambar = trim($_POST['current_gambar'] ?? '');
        $final_image = $current_gambar;

        if (isset($_FILES['gambar_file']) && $_FILES['gambar_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImage($_FILES['gambar_file'], 'gallery/');
            if ($uploadResult['success']) {
                $final_image = $uploadResult['path'];
                if ($current_gambar && $final_image !== $current_gambar) {
                    cleanupGalleryFile($current_gambar);
                }
            } else {
                $message = $uploadResult['message'];
                $message_type = 'error';
            }
        } elseif ($gambar_url !== '') {
            if (isValidImageReference($gambar_url)) {
                if ($current_gambar && isLocalGalleryPath($current_gambar) && $current_gambar !== $gambar_url) {
                    cleanupGalleryFile($current_gambar);
                }
                $final_image = $gambar_url;
            } else {
                $message = 'URL gambar tidak valid.';
                $message_type = 'error';
            }
        } elseif ($id_berita) {
            $news_thumbnail = getNewsThumbnail($conn, $id_berita);
            if ($news_thumbnail) {
                if ($current_gambar && isLocalGalleryPath($current_gambar) && $current_gambar !== $news_thumbnail) {
                    cleanupGalleryFile($current_gambar);
                }
                $final_image = $news_thumbnail;
            } else {
                $message = 'Berita yang dipilih belum memiliki gambar thumbnail.';
                $message_type = 'error';
            }
        } elseif (empty($current_gambar)) {
            $message = 'Silakan pilih berita terkait atau upload/masukkan URL gambar.';
            $message_type = 'error';
        }

        if (empty($judul)) {
            $message = 'Judul wajib diisi.';
            $message_type = 'error';
        }

        if (empty($message)) {
            try {
                $stmt = $conn->prepare("UPDATE gallery SET id_berita = :id_berita, judul = :judul, gambar = :gambar, updated_at = NOW() WHERE id_gallery = :id");
                $stmt->execute([
                    'id' => $id,
                    'id_berita' => $id_berita,
                    'judul' => $judul,
                    'gambar' => $final_image,
                ]);
                $message = 'Data gallery berhasil diperbarui!';
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

            $message = 'Data gallery berhasil dihapus.';
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
$total_items = (int)$stmt->fetchColumn();
$total_pages = (int)ceil($total_items / max(1, $items_per_page));

// Fetch gallery entries
$stmt = $conn->prepare("SELECT g.id_gallery, g.id_berita, g.judul, g.gambar, g.created_at, g.updated_at, b.judul AS berita_judul FROM gallery g LEFT JOIN berita b ON g.id_berita = b.id_berita ORDER BY g.created_at DESC, g.id_gallery DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$gallery_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch berita options for optional relation
$news_stmt = $conn->query("SELECT id_berita, judul FROM berita ORDER BY created_at DESC, id_berita DESC");
$news_options = $news_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Gallery - CMS InLET</title>
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
    <?php $active_page = 'gallery'; include __DIR__ . '/partials/sidebar.php'; ?>
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
                <h2>Edit Gambar Gallery</h2>
                <form method="POST" enctype="multipart/form-data" id="edit-gallery-form">
                    <input type="hidden" name="action" value="update_gallery">
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="current_gambar" id="edit_current_gambar">
                    <div class="form-group">
                        <label>Judul *</label>
                        <input type="text" name="judul" id="edit_judul" required>
                    </div>
                    <div class="form-group">
                        <label>Berita Terkait (Opsional)</label>
                        <select name="id_berita" id="edit_id_berita">
                            <option value="">-- Tidak dikaitkan --</option>
                            <?php foreach ($news_options as $news): ?>
                                <option value="<?php echo $news['id_berita']; ?>"><?php echo htmlspecialchars($news['judul']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="help-text">Jika tidak upload atau URL, gambar akan mengikuti thumbnail berita.</span>
                    </div>
                    <div class="form-group">
                        <label>Upload Gambar (File)</label>
                        <input type="file" name="gambar_file" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                        <span class="help-text">Maksimal 5MB. Jika diisi, unggahan ini akan mengabaikan pilihan berita/URL.</span>
                    </div>
                    <div class="form-group">
                        <label>Atau Masukkan URL Gambar</label>
                        <input type="text" name="gambar_url" id="edit_gambar_url" placeholder="https://example.com/image.jpg">
                        <span class="help-text">Opsional. Akan mengabaikan gambar berita bila diisi.</span>
                    </div>
                    <button type="submit" class="btn-submit">Simpan Perubahan</button>
                    <button type="button" class="btn-cancel" onclick="cancelEdit()">Batal</button>
                </form>
            </div>

            <div class="form-section" id="add-form-section">
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
                                <option value="<?php echo $news['id_berita']; ?>"><?php echo htmlspecialchars($news['judul']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="help-text">Jika tidak upload/URL, gambar akan mengambil thumbnail berita ini.</span>
                    </div>
                    <div class="form-group">
                        <label>Upload Gambar (File)</label>
                        <input type="file" name="gambar_file" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                        <span class="help-text">Maksimal 5MB. Menggantikan URL maupun gambar berita.</span>
                    </div>
                    <div class="form-group">
                        <label>Atau Masukkan URL Gambar</label>
                        <input type="text" name="gambar_url" placeholder="https://example.com/image.jpg">
                        <span class="help-text">Salah satu opsi harus terisi: pilih berita, upload file, atau isi URL.</span>
                    </div>
                    <button type="submit" class="btn-submit">Tambah Gambar</button>
                </form>
            </div>

            <div class="data-section">
                <h2>Daftar Gallery (<?php echo count($gallery_items); ?>)</h2>
                <?php if (empty($gallery_items)): ?>
                    <p style="color: var(--gray); text-align: center; padding: 2rem;">Belum ada data gallery.</p>
                <?php else: ?>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Judul</th>
                                    <th>Berita Terkait</th>
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
                                                <img src="<?php echo htmlspecialchars($item['gambar']); ?>" alt="<?php echo htmlspecialchars($item['judul']); ?>" onerror="this.style.display='none'">
                                            <?php else: ?>
                                                <span style="color: var(--gray);">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                                $created = $item['created_at'] ?? null;
                                                echo $created ? date('d M Y', strtotime($created)) : '-';
                                            ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn-edit" onclick="editGallery(<?php echo htmlspecialchars(json_encode($item)); ?>)">
                                                <i class="ri-edit-line"></i> Edit
                                            </button>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin hapus gambar ini?');">
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
                <?php endif; ?>

                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($current_page > 1): ?>
                            <a href="?page=<?php echo $current_page - 1; ?>">&laquo; Prev</a>
                        <?php else: ?>
                            <span class="disabled">&laquo; Prev</span>
                        <?php endif; ?>

                        <?php
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);
                        if ($start_page > 1): ?>
                            <a href="?page=1">1</a>
                            <?php if ($start_page > 2): ?><span>...</span><?php endif; ?>
                        <?php endif; ?>

            </div>
        </section>

        <!-- GALLERY -->
        <section class="py-5" id="gallery">
            <div class="container">
                <div class="section-title text-center mb-4">
                    <h2>Gallery</h2>
                    <p>Documentation of InLET</p>
                </div>
                <div id="pinterest-grid" class="pinterest-grid">
                    <?php if (empty($gallery_init)): ?>
                        <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: var(--gray);">
                            <p style="font-size: 1.1rem;">Belum ada gambar di gallery. Silakan tambahkan melalui halaman
                                admin.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($gallery_init as $g): ?>
                            <?php
                            $rawImg = $g['img'] ?? '';
                            $imgSrc = ($rawImg !== null && trim($rawImg) !== '')
                                ? htmlspecialchars($rawImg, ENT_QUOTES, 'UTF-8')
                                : 'https://via.placeholder.com/400x300/cccccc/666666?text=Gallery';
                            ?>
                            <div class="pin-item">
                                <div class="pin-img-wrapper">
                                    <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($g['judul'] ?? 'Gallery Image') ?>"
                                        onerror="this.onerror=null; this.src='https://via.placeholder.com/400x300/cccccc/666666?text=Error+Loading+Image';">
                                    <div class="pin-overlay">
                                        <h5 class="pin-title"><?= htmlspecialchars($g['judul'] ?? 'Gallery Image') ?></h5>
                                        <?php if (!empty($g['deskripsi'])): ?>
                                            <p class="pin-desc"><?= htmlspecialchars($g['deskripsi']) ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($g['berita_judul'])): ?>
                                            <small class="pin-berita" style="display: block; margin-top: 0.5rem; opacity: 0.8;">
                                                Dari: <?= htmlspecialchars($g['berita_judul']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php if ($total_gallery_pages > 1): ?>
                    <nav aria-label="Gallery pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($gallery_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link"
                                        href="?gpage=<?= $gallery_page - 1 ?><?= !empty($search_query) ? '&search=' . urlencode($search_query) : '' ?>#gallery"
                                        aria-label="Previous">
                                        <span aria-hidden="true">&laquo; Previous</span>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link">&laquo; Previous</span>
                                </li>
                            <?php endif; ?>

                            <?php
                            $start_page = max(1, $gallery_page - 2);
                            $end_page = min($total_gallery_pages, $gallery_page + 2);

                            if ($start_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link"
                                        href="?gpage=1<?= !empty($search_query) ? '&search=' . urlencode($search_query) : '' ?>#gallery">1</a>
                                </li>
                                <?php if ($start_page > 2): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endfor; ?>

                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?= $i == $gallery_page ? 'active' : '' ?>">
                                    <a class="page-link"
                                        href="?gpage=<?= $i ?><?= !empty($search_query) ? '&search=' . urlencode($search_query) : '' ?>#gallery"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($end_page < $total_gallery_pages): ?>
                                <?php if ($end_page < $total_gallery_pages - 1): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link"
                                        href="?gpage=<?= $total_gallery_pages ?><?= !empty($search_query) ? '&search=' . urlencode($search_query) : '' ?>#gallery"><?= $total_gallery_pages ?></a>
                                </li>
                            <?php endif; ?>

                            <?php if ($gallery_page < $total_gallery_pages): ?>
                                <li class="page-item">
                                    <a class="page-link"
                                        href="?gpage=<?= $gallery_page + 1 ?><?= !empty($search_query) ? '&search=' . urlencode($search_query) : '' ?>#gallery"
                                        aria-label="Next">
                                        <span aria-hidden="true">Next &raquo;</span>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link">Next &raquo;</span>
                                </li>
                            <?php endif; ?>
                        </ul>
                        <div class="text-center mt-3" style="color: var(--gray);">
                            Menampilkan <?= ($gallery_offset + 1) ?> -
                            <?= min($gallery_offset + $gallery_items_per_page, $total_gallery_items) ?> dari
                            <?= $total_gallery_items ?> gambar
                        </div>
                    </nav>
                <?php elseif (empty($all_gallery)): ?>
                    <div class="text-center mt-3" style="color: var(--gray);">
                        Belum ada gambar di gallery
                    </div>
                <?php endif; ?>
            </div>
            </div>
        </div>
    </main>

    <script>
        // Swiper init
        new Swiper(".teamSwiper", { slidesPerView: 3, spaceBetween: 30, navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" }, breakpoints: { 0: { slidesPerView: 1 }, 576: { slidesPerView: 2 }, 992: { slidesPerView: 3 } } });

        // Masonry Layout
        document.addEventListener("DOMContentLoaded", function () {
            const container = document.getElementById("pinterest-grid");
            const gap = 15;

            function getColumns() {
                if (window.innerWidth < 576) return 1;
                if (window.innerWidth < 768) return 2;
                return 3;
            }

            function masonryLayout() {
                const items = Array.from(container.querySelectorAll(".pin-item"));
                const columns = getColumns();

                if (columns === 1) {
                    container.style.height = 'auto';
                    items.forEach(i => {
                        i.style.position = '';
                        i.style.transform = '';
                        i.style.width = '100%';
                    });
                    return;
                }

                items.forEach(i => i.style.position = 'absolute');
                const colWidth = (container.offsetWidth - (columns - 1) * gap) / columns;
                const colHeights = Array(columns).fill(0);

                items.forEach(item => {
                    item.style.width = colWidth + 'px';
                    const minCol = colHeights.indexOf(Math.min(...colHeights));
                    const x = minCol * (colWidth + gap);
                    const y = colHeights[minCol];
                    item.style.transform = `translate(${x}px,${y}px)`;
                    item.classList.add('show');
                    colHeights[minCol] += item.offsetHeight + gap;
                });

                container.style.height = Math.max(...colHeights) + 'px';
            }

            function initialLayout() {
                const imgs = container.querySelectorAll('img');
                let loaded = 0;

                imgs.forEach(img => {
                    if (img.complete) {
                        loaded++;
                    } else {
                        img.addEventListener('load', () => {
                            loaded++;
                            if (loaded === imgs.length) masonryLayout();
                        });
                        img.addEventListener('error', () => {
                            loaded++;
                            if (loaded === imgs.length) masonryLayout();
                        });
                    }
                });

                if (loaded === imgs.length) masonryLayout();
            }
            document.getElementById('edit-form-section').classList.add('active');
            document.getElementById('add-form-section').style.display = 'none';
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