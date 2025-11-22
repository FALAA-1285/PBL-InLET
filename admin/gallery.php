<?php
require_once '../config/auth.php';
require_once '../config/upload.php';
requireAdmin();

$conn = getDBConnection();
$message = '';
$message_type = '';

// Create gallery table if not exists
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS gallery (
        id_gallery SERIAL PRIMARY KEY,
        gambar VARCHAR(500) NOT NULL,
        judul VARCHAR(255),
        deskripsi VARCHAR(1000),
        urutan INTEGER DEFAULT 0,
        created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
        updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
    )");
    $conn->exec("CREATE INDEX IF NOT EXISTS idx_gallery_urutan ON gallery(urutan)");
    $conn->exec("CREATE INDEX IF NOT EXISTS idx_gallery_created ON gallery(created_at)");
} catch (PDOException $e) {
    // Table might already exist, continue
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_gallery') {
        $judul = $_POST['judul'] ?? '';
        $deskripsi = $_POST['deskripsi'] ?? '';
        $urutan = isset($_POST['urutan']) ? (int)$_POST['urutan'] : 0;
        $gambar = $_POST['gambar'] ?? ''; // URL input
        
        // Handle file upload
        if (isset($_FILES['gambar_file']) && $_FILES['gambar_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImage($_FILES['gambar_file'], 'gallery/');
            if ($uploadResult['success']) {
                $gambar = $uploadResult['path'];
            } else {
                $message = $uploadResult['message'];
                $message_type = 'error';
            }
        }
        
        if (empty($message)) {
            if (empty($gambar)) {
                $message = 'Gambar harus diisi!';
                $message_type = 'error';
            } else {
                try {
                    $stmt = $conn->prepare("INSERT INTO gallery (gambar, judul, deskripsi, urutan) VALUES (:gambar, :judul, :deskripsi, :urutan)");
                    $stmt->execute([
                        'gambar' => $gambar,
                        'judul' => $judul ?: null,
                        'deskripsi' => $deskripsi ?: null,
                        'urutan' => $urutan
                    ]);
                    $message = 'Gambar gallery berhasil ditambahkan!';
                    $message_type = 'success';
                } catch(PDOException $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $message_type = 'error';
                }
            }
        }
    } elseif ($action === 'update_gallery') {
        $id = $_POST['id'] ?? 0;
        $judul = $_POST['judul'] ?? '';
        $deskripsi = $_POST['deskripsi'] ?? '';
        $urutan = isset($_POST['urutan']) ? (int)$_POST['urutan'] : 0;
        $gambar = $_POST['gambar'] ?? '';
        
        // Handle file upload
        if (isset($_FILES['gambar_file']) && $_FILES['gambar_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImage($_FILES['gambar_file'], 'gallery/');
            if ($uploadResult['success']) {
                $gambar = $uploadResult['path'];
            } else {
                $message = $uploadResult['message'];
                $message_type = 'error';
            }
        }
        
        if (empty($message)) {
            try {
                if (!empty($gambar)) {
                    $stmt = $conn->prepare("UPDATE gallery SET gambar = :gambar, judul = :judul, deskripsi = :deskripsi, urutan = :urutan, updated_at = now() WHERE id_gallery = :id");
                    $stmt->execute([
                        'id' => $id,
                        'gambar' => $gambar,
                        'judul' => $judul ?: null,
                        'deskripsi' => $deskripsi ?: null,
                        'urutan' => $urutan
                    ]);
                } else {
                    $stmt = $conn->prepare("UPDATE gallery SET judul = :judul, deskripsi = :deskripsi, urutan = :urutan, updated_at = now() WHERE id_gallery = :id");
                    $stmt->execute([
                        'id' => $id,
                        'judul' => $judul ?: null,
                        'deskripsi' => $deskripsi ?: null,
                        'urutan' => $urutan
                    ]);
                }
                $message = 'Gambar gallery berhasil diupdate!';
                $message_type = 'success';
            } catch(PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'delete_gallery') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $conn->prepare("DELETE FROM gallery WHERE id_gallery = :id");
            $stmt->execute(['id' => $id]);
            $message = 'Gambar gallery berhasil dihapus!';
            $message_type = 'success';
        } catch(PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Pagination setup
$items_per_page = 12;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Get total count
try {
    $stmt = $conn->query("SELECT COUNT(*) FROM gallery");
    $total_items = $stmt->fetchColumn();
} catch (PDOException $e) {
    $total_items = 0;
}
$total_pages = ceil($total_items / $items_per_page);

// Get gallery items with pagination
try {
    $stmt = $conn->prepare("SELECT * FROM gallery ORDER BY urutan ASC, created_at DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $gallery_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $gallery_list = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery Management - CMS InLET</title>
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
        .admin-header h1 {
            color: var(--dark);
            font-size: 1.5rem;
            margin-bottom: 0.35rem;
        }
        .cms-content {
            max-width: 1200px;
            margin: 0 auto;
            padding-bottom: 4rem;
        }
        .message {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .form-section {
            background: white;
            padding: 2rem;
            border-radius: 18px;
            box-shadow: 0 2px 14px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }
        .form-section h2 {
            color: var(--dark);
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
        .form-group input[type="number"],
        .form-group input[type="file"],
        .form-group textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        .btn-submit {
            background: var(--primary);
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-submit:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 70, 229, 0.3);
        }
        .btn-cancel {
            background: #e2e8f0;
            color: var(--dark);
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            margin-left: 1rem;
            transition: all 0.3s ease;
        }
        .btn-cancel:hover {
            background: #cbd5e1;
        }
        .data-section {
            background: white;
            padding: 2rem;
            border-radius: 18px;
            box-shadow: 0 2px 14px rgba(0, 0, 0, 0.08);
        }
        .data-section h2 {
            color: var(--dark);
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
        }
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        .gallery-item {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            background: white;
            transition: all 0.3s ease;
        }
        .gallery-item:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            transform: translateY(-4px);
        }
        .gallery-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }
        .gallery-item-content {
            padding: 1rem;
        }
        .gallery-item h4 {
            margin: 0 0 0.5rem 0;
            font-size: 0.95rem;
            color: var(--dark);
            font-weight: 600;
        }
        .gallery-item p {
            margin: 0 0 0.75rem 0;
            font-size: 0.85rem;
            color: var(--gray);
        }
        .gallery-item-actions {
            display: flex;
            gap: 0.5rem;
        }
        .btn-edit {
            flex: 1;
            background: var(--primary);
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-edit:hover {
            background: var(--primary-dark);
        }
        .btn-delete {
            flex: 1;
            background: #ef4444;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-delete:hover {
            background: #dc2626;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            list-style: none;
            padding: 0;
            margin-top: 2rem;
        }
        .pagination .page-link {
            padding: 0.5rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            color: var(--dark);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .pagination .page-link:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        .pagination .page-link.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        .edit-form-section {
            background: #f8fafc;
            border: 2px solid var(--primary);
        }
    </style>
</head>
<body>
    <?php 
    $active_page = 'gallery';
    include 'partials/sidebar.php'; 
    ?>

    <div class="content">
        <div class="content-inner">
            <div class="admin-header">
                <h1>Gallery Management</h1>
                <p style="color: var(--gray); margin: 0;">Kelola gambar gallery untuk ditampilkan di website</p>
            </div>

            <div class="cms-content">
            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Edit Form Section (Hidden by default) -->
            <div id="edit-form-section" class="form-section edit-form-section" style="display: none;">
                <h2>Edit Gallery</h2>
                <form method="POST" action="" enctype="multipart/form-data" id="edit-gallery-form">
                    <input type="hidden" name="action" value="update_gallery">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label>Judul (Opsional)</label>
                        <input type="text" name="judul" id="edit_judul">
                    </div>
                    <div class="form-group">
                        <label>Deskripsi (Opsional)</label>
                        <textarea name="deskripsi" id="edit_deskripsi" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Upload Gambar Baru (File)</label>
                        <input type="file" name="gambar_file" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                        <small style="color: var(--gray); display: block; margin-top: 0.5rem;">Maksimal 5MB. Format: JPG, PNG, GIF, WEBP</small>
                    </div>
                    <div class="form-group">
                        <label>Atau Masukkan URL Gambar</label>
                        <input type="text" name="gambar" id="edit_gambar" placeholder="https://example.com/image.jpg">
                        <small style="color: var(--gray); display: block; margin-top: 0.5rem;">Jika upload file, URL akan diabaikan</small>
                    </div>
                    <div class="form-group">
                        <label>Urutan (0 = default)</label>
                        <input type="number" name="urutan" id="edit_urutan" value="0" min="0">
                    </div>
                    <button type="submit" class="btn-submit">Update Gallery</button>
                    <button type="button" class="btn-cancel" onclick="hideEditForm()">Batal</button>
                </form>
            </div>

            <div class="form-section">
                <h2>Tambah Gallery Baru</h2>
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_gallery">
                    <div class="form-group">
                        <label>Judul (Opsional)</label>
                        <input type="text" name="judul" placeholder="Judul gambar">
                    </div>
                    <div class="form-group">
                        <label>Deskripsi (Opsional)</label>
                        <textarea name="deskripsi" rows="3" placeholder="Deskripsi gambar"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Upload Gambar (File) <span style="color: #ef4444;">*</span></label>
                        <input type="file" name="gambar_file" id="gambar_file" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                        <small style="color: var(--gray); display: block; margin-top: 0.5rem;">Maksimal 5MB. Format: JPG, PNG, GIF, WEBP</small>
                    </div>
                    <div class="form-group">
                        <label>Atau Masukkan URL Gambar <span style="color: #ef4444;">*</span></label>
                        <input type="url" name="gambar" id="gambar_url" placeholder="https://example.com/image.jpg">
                        <small style="color: var(--gray); display: block; margin-top: 0.5rem;">Jika upload file, URL akan diabaikan. Minimal harus mengisi salah satu (File atau URL)</small>
                    </div>
                    <div class="form-group">
                        <label>Urutan (0 = default)</label>
                        <input type="number" name="urutan" value="0" min="0">
                    </div>
                    <button type="submit" class="btn-submit" onclick="return validateGalleryForm()">Tambah Gallery</button>
                </form>
            </div>

            <div class="data-section">
                <h2>Daftar Gallery (<?php echo $total_items; ?> gambar)</h2>
                <?php if (empty($gallery_list)): ?>
                    <p>Belum ada gambar gallery.</p>
                <?php else: ?>
                    <div class="gallery-grid">
                        <?php foreach ($gallery_list as $item): ?>
                            <div class="gallery-item">
                                <img src="<?php echo htmlspecialchars($item['gambar']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['judul'] ?? 'Gallery'); ?>" 
                                     onerror="this.src='https://via.placeholder.com/400x300/cccccc/666666?text=Gallery'">
                                <div class="gallery-item-content">
                                    <?php if ($item['judul']): ?>
                                        <h4><?php echo htmlspecialchars($item['judul']); ?></h4>
                                    <?php endif; ?>
                                    <p>Urutan: <?php echo $item['urutan']; ?></p>
                                    <div class="gallery-item-actions">
                                        <button onclick="editGallery(<?php echo htmlspecialchars(json_encode($item)); ?>)" class="btn-edit">Edit</button>
                                        <form method="POST" action="" style="flex: 1; margin: 0;" onsubmit="return confirm('Yakin ingin menghapus gambar ini?');">
                                            <input type="hidden" name="action" value="delete_gallery">
                                            <input type="hidden" name="id" value="<?php echo $item['id_gallery']; ?>">
                                            <button type="submit" class="btn-delete" style="width: 100%;">Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Gallery pagination">
                            <ul class="pagination">
                                <?php if ($current_page > 1): ?>
                                    <li><a href="?page=<?php echo $current_page - 1; ?>" class="page-link">Previous</a></li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li><a href="?page=<?php echo $i; ?>" class="page-link <?php echo ($i == $current_page) ? 'active' : ''; ?>"><?php echo $i; ?></a></li>
                                <?php endfor; ?>
                                
                                <?php if ($current_page < $total_pages): ?>
                                    <li><a href="?page=<?php echo $current_page + 1; ?>" class="page-link">Next</a></li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            </div>
        </div>
    </div>

    <script>
        function validateGalleryForm() {
            const fileInput = document.getElementById('gambar_file');
            const urlInput = document.getElementById('gambar_url');
            
            if (!fileInput.files.length && !urlInput.value.trim()) {
                alert('Harap pilih file gambar atau masukkan URL gambar!');
                return false;
            }
            
            return true;
        }
        
        function editGallery(item) {
            document.getElementById('edit_id').value = item.id_gallery;
            document.getElementById('edit_judul').value = item.judul || '';
            document.getElementById('edit_deskripsi').value = item.deskripsi || '';
            document.getElementById('edit_gambar').value = item.gambar || '';
            document.getElementById('edit_urutan').value = item.urutan || 0;
            document.getElementById('edit-form-section').style.display = 'block';
            document.getElementById('edit-form-section').scrollIntoView({ behavior: 'smooth' });
        }
        
        function hideEditForm() {
            document.getElementById('edit-form-section').style.display = 'none';
        }
        
        // Auto-hide message after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const messages = document.querySelectorAll('.message');
            messages.forEach(function(msg) {
                setTimeout(function() {
                    msg.style.transition = 'opacity 0.5s';
                    msg.style.opacity = '0';
                    setTimeout(function() {
                        msg.style.display = 'none';
                    }, 500);
                }, 5000);
            });
        });
    </script>
</body>
</html>

