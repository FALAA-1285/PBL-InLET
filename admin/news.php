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
                $stmt = $conn->prepare("INSERT INTO berita (judul, konten, gambar_thumbnail) VALUES (:judul, :konten, :gambar_thumbnail)");
                $stmt->execute([
                    'judul' => $judul,
                    'konten' => $konten,
                    'gambar_thumbnail' => $gambar_thumbnail ?: null
                ]);
                $message = 'Berita berhasil ditambahkan!';
                $message_type = 'success';
            } catch(PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'delete_news') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $conn->prepare("DELETE FROM berita WHERE id_berita = :id");
            $stmt->execute(['id' => $id]);
            $message = 'Berita berhasil dihapus!';
            $message_type = 'success';
        } catch(PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Get all news
$stmt = $conn->query("SELECT * FROM berita ORDER BY created_at DESC");
$news_list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola News - CMS InLET</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: var(--light);
        }
        .admin-header {
            background: white;
            padding: 1.5rem 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        .admin-header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-header h1 {
            color: var(--primary);
            font-size: 1.5rem;
        }
        .admin-nav {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        .admin-nav a {
            color: var(--dark);
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .admin-nav a:hover {
            background: var(--light);
            color: var(--primary);
        }
        .admin-nav .btn-logout {
            background: #ef4444;
            color: white;
        }
        .admin-nav .btn-logout:hover {
            background: #dc2626;
        }
        .cms-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem 4rem;
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
        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }
        .news-card {
            background: var(--light);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
        }
        .news-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        .news-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .news-card-content {
            padding: 1.5rem;
        }
        .news-card h3 {
            color: var(--primary);
            margin-bottom: 0.5rem;
        }
        .news-card p {
            color: var(--gray);
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        .news-card small {
            color: var(--gray);
            font-size: 0.85rem;
        }
        .news-card .actions {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
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
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="admin-header-content">
            <h1>Kelola News - CMS InLET</h1>
            <div class="admin-nav">
                <a href="dashboard.php">Dashboard</a>
                <a href="research.php">Research</a>
                <a href="member.php">Member</a>
                <a href="news.php">News</a>
                <a href="../index.php" target="_blank">View Site</a>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </div>

    <div class="cms-content">
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="form-section">
            <h2>Tambah Berita Baru</h2>
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_news">
                <div class="form-group">
                    <label>Judul Berita *</label>
                    <input type="text" name="judul" required>
                </div>
                <div class="form-group">
                    <label>Konten *</label>
                    <textarea name="konten" required></textarea>
                </div>
                <div class="form-group">
                    <label>Upload Gambar Thumbnail (File)</label>
                    <input type="file" name="gambar_file" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                    <small style="color: var(--gray); display: block; margin-top: 0.5rem;">Maksimal 5MB. Format: JPG, PNG, GIF, WEBP</small>
                </div>
                <div class="form-group">
                    <label>Atau Masukkan URL Gambar</label>
                    <input type="text" name="gambar_thumbnail" placeholder="https://example.com/image.jpg">
                    <small style="color: var(--gray); display: block; margin-top: 0.5rem;">Jika upload file, URL akan diabaikan</small>
                </div>
                <button type="submit" class="btn-submit">Tambah Berita</button>
            </form>
        </div>

        <div class="data-section">
            <h2>Daftar Berita</h2>
            <?php if (empty($news_list)): ?>
                <p style="color: var(--gray); text-align: center; padding: 2rem;">Belum ada berita</p>
            <?php else: ?>
                <div class="news-grid">
                    <?php foreach ($news_list as $news): ?>
                        <div class="news-card">
                            <?php if ($news['gambar_thumbnail']): ?>
                                <img src="<?php echo htmlspecialchars($news['gambar_thumbnail']); ?>" alt="<?php echo htmlspecialchars($news['judul']); ?>">
                            <?php else: ?>
                                <div style="height: 200px; background: var(--primary); display: flex; align-items: center; justify-content: center; color: white;">
                                    No Image
                                </div>
                            <?php endif; ?>
                            <div class="news-card-content">
                                <h3><?php echo htmlspecialchars($news['judul']); ?></h3>
                                <p><?php echo htmlspecialchars(substr($news['konten'], 0, 150)) . '...'; ?></p>
                                <small>Published: <?php echo date('d M Y', strtotime($news['created_at'])); ?></small>
                                <div class="actions">
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin hapus berita ini?');">
                                        <input type="hidden" name="action" value="delete_news">
                                        <input type="hidden" name="id" value="<?php echo $news['id_berita']; ?>">
                                        <button type="submit" class="btn-delete">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

