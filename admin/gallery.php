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
function startsWith($haystack, $needle) {
    if ($needle === '') return false;
    return substr($haystack, 0, strlen($needle)) === $needle;
}

function isLocalGalleryPath($path) {
    if (!$path) return false;
    $p = ltrim($path, '/');
    return startsWith($p, 'uploads/gallery/');
}

function cleanupGalleryFile($path) {
    if ($path && isLocalGalleryPath($path)) {
        deleteUploadedFile($path);
    }
}

function isValidImageReference($input) {
    if ($input === '' || $input === null) return false;
    if (filter_var($input, FILTER_VALIDATE_URL)) return true;
    $i = ltrim($input, '/');
    return startsWith($i, 'uploads/') || startsWith($i, 'assets/');
}

function getNewsThumbnail(PDO $conn, $id_berita) {
    if (!$id_berita) return null;
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
        $deskripsi = trim($_POST['deskripsi'] ?? '');
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
                $stmt = $conn->prepare("INSERT INTO gallery (id_berita, judul, deskripsi, gambar, created_at, updated_at) VALUES (:id_berita, :judul, :deskripsi, :gambar, NOW(), NOW())");
                $stmt->execute([
                    'id_berita' => $id_berita,
                    'judul' => $judul,
                    'deskripsi' => $deskripsi ?: null,
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
        $deskripsi = trim($_POST['deskripsi'] ?? '');
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
                $stmt = $conn->prepare("UPDATE gallery SET id_berita = :id_berita, judul = :judul, deskripsi = :deskripsi, gambar = :gambar, updated_at = NOW() WHERE id_gallery = :id");
                $stmt->execute([
                    'id' => $id,
                    'id_berita' => $id_berita,
                    'judul' => $judul,
                    'deskripsi' => $deskripsi ?: null,
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
$total_items = (int)$stmt->fetchColumn();
$total_pages = (int)ceil($total_items / max(1, $items_per_page));

$stmt = $conn->prepare("SELECT g.id_gallery, g.id_berita, g.judul, g.deskripsi, g.gambar, g.created_at, g.updated_at, b.judul AS berita_judul FROM gallery g LEFT JOIN berita b ON g.id_berita = b.id_berita ORDER BY g.created_at DESC, g.id_gallery DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$gallery_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// For front gallery display (public), fetch a set (used later)
$gallery_init_stmt = $conn->prepare("SELECT id_gallery AS id, judul, deskripsi, gambar AS img, id_berita, created_at FROM gallery ORDER BY created_at DESC LIMIT 60");
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
$total_gallery_pages = (int)ceil($total_gallery_items / max(1, $gallery_items_per_page));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <title>Kelola Gallery - CMS InLET</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        :root{
            --primary:#2563eb;
            --primary-dark:#1e40af;
            --light:#f8fafc;
            --dark:#0f172a;
            --gray:#6b7280;
        }
        body{ font-family:"Poppins",system-ui,-apple-system,Segoe UI,Roboto, 'Helvetica Neue', Arial; margin:0; background:var(--light); color:var(--dark); }
        .container{ max-width:1200px; margin:0 auto; padding:2rem; }
        .content-header{ display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:1.5rem;}
        .content-header h1{ margin:0; color:var(--primary); display:flex; align-items:center; gap:.75rem; font-size:1.6rem;}
        .form-section, .data-section { background:#fff; padding:1.5rem; border-radius:12px; box-shadow:0 6px 20px rgba(2,6,23,0.06); margin-bottom:1.5rem; }
        .form-group{ margin-bottom:1rem; }
        label{ display:block; font-weight:600; margin-bottom:.4rem; color:var(--dark); }
        input[type="text"], input[type="file"], textarea, select { width:100%; padding:.65rem; border:1px solid #e6edf9; border-radius:8px; font-size:0.95rem; }
        .btn{ display:inline-block; padding:.6rem .9rem; border-radius:8px; font-weight:600; cursor:pointer; border:none; }
        .btn-primary{ background:var(--primary); color:#fff; }
        .btn-ghost{ background:#eef2ff; color:var(--primary); border:1px solid rgba(37,99,235,0.08); }
        .btn-danger{ background:#ef4444; color:#fff; }
        .table-container{ overflow:auto; }
        table{ width:100%; border-collapse:collapse; min-width:800px; }
        th, td{ padding:.85rem; border-bottom:1px solid #f1f5f9; text-align:left; vertical-align:middle; }
        th{ background:#fafcff; font-weight:700; color:var(--dark); }
        .image-cell img{ width:140px; height:90px; object-fit:cover; border-radius:8px; border:1px solid #eef2ff; }
        .message{ padding:.9rem; border-radius:8px; margin-bottom:1rem; }
        .message.success{ background:#e6fffa; color:#065f46; border-left:4px solid #10b981; }
        .message.error{ background:#fff1f2; color:#991b1b; border-left:4px solid #ef4444; }
        .pagination { display:flex; gap:.5rem; justify-content:center; padding:1rem 0; }
        .pagination a, .pagination span { padding:.5rem .75rem; border-radius:8px; border:1px solid #eef2ff; text-decoration:none; color:var(--dark); }
        .pagination .active { background:var(--primary); color:#fff; border-color:var(--primary); }
        .pagination .disabled { opacity:.5; pointer-events:none; }

        /* Gallery (public) grid */
        #gallery { padding:2rem 0 3rem; }
        .pinterest-grid { position:relative; width:100%; }
        .pin-item { position:absolute; width:300px; transition:transform .25s ease, opacity .25s; opacity:0; }
        .pin-item.show { opacity:1; }
        .pin-img-wrapper { position:relative; border-radius:12px; overflow:hidden; box-shadow:0 8px 24px rgba(2,6,23,0.06); }
        .pin-img-wrapper img{ width:100%; height:auto; display:block; }
        .pin-overlay{ position:absolute; left:0; right:0; bottom:0; padding:1rem; background:linear-gradient(0deg, rgba(0,0,0,0.6), rgba(0,0,0,0.15)); color:#fff; }
        .pin-title{ margin:0; font-weight:700; font-size:1rem; }
        .pin-desc{ margin:.3rem 0 0; font-size:.9rem; color:rgba(255,255,255,0.9); }
        /* responsive */
        @media (max-width:768px){ .pin-item{ position:static; width:100% !important; margin-bottom:15px; } .pinterest-grid{ height:auto !important; } }
    </style>
</head>
<body>
    <?php $active_page = 'gallery'; include __DIR__ . '/partials/sidebar.php'; ?>

    <main class="container">
        <div class="content-header">
            <h1><i class="ri-image-line"></i> Gallery</h1>
            <div>
                <button class="btn btn-primary" onclick="document.getElementById('add-form-section').scrollIntoView({behavior:'smooth'});">Tambah Gambar</button>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo htmlspecialchars($message_type); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Edit Form (hidden by default) -->
        <div id="edit-form-section" class="form-section" style="display:none;">
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
                    <label>Deskripsi (opsional)</label>
                    <textarea name="deskripsi" id="edit_deskripsi" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Berita Terkait (Opsional)</label>
                    <select name="id_berita" id="edit_id_berita">
                        <option value="">-- Tidak dikaitkan --</option>
                        <?php foreach ($news_options as $news): ?>
                            <option value="<?php echo $news['id_berita']; ?>"><?php echo htmlspecialchars($news['judul']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Upload Gambar (File)</label>
                    <input type="file" name="gambar_file" accept="image/*">
                    <small style="display:block;color:var(--gray);">Jika diisi, unggahan ini akan menggantikan gambar lama.</small>
                </div>
                <div class="form-group">
                    <label>Atau Masukkan URL Gambar</label>
                    <input type="text" name="gambar_url" id="edit_gambar_url" placeholder="https://example.com/image.jpg">
                </div>
                <div style="display:flex;gap:.5rem;margin-top:.5rem;">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <button type="button" class="btn btn-ghost" onclick="cancelEdit()">Batal</button>
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
                    <label>Deskripsi (opsional)</label>
                    <textarea name="deskripsi" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Berita Terkait (Opsional)</label>
                    <select name="id_berita">
                        <option value="">-- Tidak dikaitkan --</option>
                        <?php foreach ($news_options as $news): ?>
                            <option value="<?php echo $news['id_berita']; ?>"><?php echo htmlspecialchars($news['judul']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small style="display:block;color:var(--gray);">Jika tidak mengupload/URL, gambar dapat mengambil thumbnail berita ini (jika ada).</small>
                </div>
                <div class="form-group">
                    <label>Upload Gambar (File)</label>
                    <input type="file" name="gambar_file" accept="image/*">
                </div>
                <div class="form-group">
                    <label>Atau Masukkan URL Gambar</label>
                    <input type="text" name="gambar_url" placeholder="https://example.com/image.jpg">
                </div>
                <div style="display:flex;gap:.5rem;">
                    <button type="submit" class="btn btn-primary">Tambah Gambar</button>
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
                                            <img src="<?php echo htmlspecialchars($item['gambar']); ?>" alt="<?php echo htmlspecialchars($item['judul']); ?>" onerror="this.style.display='none'">
                                        <?php else: ?>
                                            <span style="color:var(--gray);">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo !empty($item['created_at']) ? date('d M Y', strtotime($item['created_at'])) : '-'; ?></td>
                                    <td>
                                        <button class="btn btn-ghost" onclick='editGallery(<?php echo json_encode($item, JSON_HEX_APOS|JSON_HEX_QUOT); ?>)'><i class="ri-edit-line"></i> Edit</button>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin hapus gambar ini?');">
                                            <input type="hidden" name="action" value="delete_gallery">
                                            <input type="hidden" name="id" value="<?php echo $item['id_gallery']; ?>">
                                            <button type="submit" class="btn btn-danger"><i class="ri-delete-bin-line"></i> Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination" role="navigation" aria-label="Pagination">
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

                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <?php if ($i == $current_page): ?>
                                <span class="active"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
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
            <?php endif; ?>
        </div>

        <!-- PUBLIC GALLERY -->
        <section id="gallery">
            <div style="text-align:center; margin-bottom:1rem;">
                <h2>Gallery</h2>
                <p style="color:var(--gray);">Documentation of InLET</p>
            </div>

            <div id="pinterest-grid" class="pinterest-grid">
                <?php if (empty($gallery_init)): ?>
                    <div style="grid-column:1/-1; text-align:center; padding:3rem; color:var(--gray);">
                        <p style="font-size:1.1rem;">Belum ada gambar di gallery. Silakan tambahkan melalui halaman admin.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($gallery_init as $g): 
                        $rawImg = $g['img'] ?? '';
                        $imgSrc = ($rawImg !== null && trim($rawImg) !== '') ? htmlspecialchars($rawImg, ENT_QUOTES, 'UTF-8') : 'https://via.placeholder.com/400x300/cccccc/666666?text=Gallery';
                    ?>
                        <div class="pin-item">
                            <div class="pin-img-wrapper">
                                <img src="<?php echo $imgSrc; ?>" alt="<?php echo htmlspecialchars($g['judul'] ?? 'Gallery Image'); ?>" onerror="this.onerror=null; this.src='https://via.placeholder.com/400x300/cccccc/666666?text=Error+Loading+Image';">
                                <div class="pin-overlay">
                                    <h5 class="pin-title"><?php echo htmlspecialchars($g['judul'] ?? 'Gallery Image'); ?></h5>
                                    <?php if (!empty($g['deskripsi'])): ?><p class="pin-desc"><?php echo htmlspecialchars($g['deskripsi']); ?></p><?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

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
        document.getElementById('edit_deskripsi').value = data.deskripsi || '';
        if (document.getElementById('edit_id_berita')) {
            document.getElementById('edit_id_berita').value = data.id_berita || '';
        }
        if (document.getElementById('edit_gambar_url')) {
            document.getElementById('edit_gambar_url').value = data.gambar || '';
        }

        // show edit form
        document.getElementById('edit-form-section').style.display = 'block';
        document.getElementById('add-form-section').style.display = 'none';
        document.getElementById('edit-form-section').scrollIntoView({behavior:'smooth', block:'start'});
    }

    function cancelEdit() {
        var form = document.getElementById('edit-gallery-form');
        if (form) form.reset();
        document.getElementById('edit-form-section').style.display = 'none';
        document.getElementById('add-form-section').style.display = 'block';
    }

    // ----- Masonry layout for pinterest-grid -----
    document.addEventListener("DOMContentLoaded", function () {
        const container = document.getElementById("pinterest-grid");
        const gap = 15;

        if (!container) return;

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
                    i.classList.add('show');
                });
                return;
            }

            items.forEach(i => {
                i.style.position = 'absolute';
                i.style.marginBottom = '0';
            });

            const containerWidth = container.clientWidth || container.offsetWidth;
            const colWidth = Math.floor((containerWidth - (columns - 1) * gap) / columns);
            const colHeights = Array(columns).fill(0);

            items.forEach(item => {
                item.style.width = colWidth + 'px';

                // force reflow to ensure offsetHeight is accurate after width change
                const h = item.offsetHeight;

                const minCol = colHeights.indexOf(Math.min(...colHeights));
                const x = minCol * (colWidth + gap);
                const y = colHeights[minCol];

                item.style.transform = `translate(${x}px, ${y}px)`;
                item.classList.add('show');

                colHeights[minCol] += item.offsetHeight + gap;
            });

            container.style.height = Math.max(...colHeights) + 'px';
        }

        function initialLayout() {
            const imgs = Array.from(container.querySelectorAll('img'));
            if (imgs.length === 0) {
                masonryLayout();
                return;
            }

            let loaded = 0;
            imgs.forEach(img => {
                if (img.complete) {
                    loaded++;
                } else {
                    img.addEventListener('load', () => {
                        loaded++;
                        if (loaded === imgs.length) masonryLayout();
                    }, { once: true });
                    img.addEventListener('error', () => {
                        loaded++;
                        if (loaded === imgs.length) masonryLayout();
                    }, { once: true });
                }
            });

            if (loaded === imgs.length) masonryLayout();
        }

        initialLayout();
        window.addEventListener('resize', function () {
            clearTimeout(window.__masonryResizeTimer);
            window.__masonryResizeTimer = setTimeout(masonryLayout, 120);
        });
    });
    </script>
</body>
</html>