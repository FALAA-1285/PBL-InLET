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
                $stmt = $conn->prepare("INSERT INTO member (nama, email, jabatan, foto) VALUES (:nama, :email, :jabatan, :foto)");
                $stmt->execute([
                    'nama' => $nama,
                    'email' => $email ?: null,
                    'jabatan' => $jabatan ?: null,
                    'foto' => $foto ?: null
                ]);
                $member_id = $conn->lastInsertId();
                
                // Add profile if provided
                $alamat = $_POST['alamat'] ?? '';
                $no_tlp = $_POST['no_tlp'] ?? '';
                $deskripsi = $_POST['deskripsi'] ?? '';
                
                if ($alamat || $no_tlp || $deskripsi) {
                    $stmt = $conn->prepare("INSERT INTO profil_member (id_member, alamat, no_tlp, deskripsi) VALUES (:id_member, :alamat, :no_tlp, :deskripsi)");
                    $stmt->execute([
                        'id_member' => $member_id,
                        'alamat' => $alamat ?: null,
                        'no_tlp' => $no_tlp ?: null,
                        'deskripsi' => $deskripsi ?: null
                    ]);
                }
                
                $message = 'Member berhasil ditambahkan!';
                $message_type = 'success';
            } catch(PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'delete_member') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $conn->prepare("DELETE FROM member WHERE id_member = :id");
            $stmt->execute(['id' => $id]);
            $message = 'Member berhasil dihapus!';
            $message_type = 'success';
        } catch(PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Get all members with profiles
$stmt = $conn->query("SELECT m.*, pm.alamat, pm.no_tlp, pm.deskripsi 
                      FROM member m 
                      LEFT JOIN profil_member pm ON m.id_member = pm.id_member 
                      ORDER BY m.nama");
$members = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Member - CMS InLET</title>
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
            min-height: 100px;
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
        .member-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .member-card {
            background: var(--light);
            padding: 1.5rem;
            border-radius: 15px;
            border-left: 4px solid var(--primary);
            transition: all 0.3s;
        }
        .member-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .member-card h3 {
            color: var(--primary);
            margin-bottom: 0.5rem;
        }
        .member-card p {
            color: var(--gray);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        .member-card .actions {
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
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="admin-header-content">
            <h1>Kelola Member - CMS InLET</h1>
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
            <h2>Tambah Member Baru</h2>
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_member">
                <div class="form-group">
                    <label>Nama *</label>
                    <input type="text" name="nama" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email">
                </div>
                <div class="form-group">
                    <label>Jabatan</label>
                    <input type="text" name="jabatan">
                </div>
                <div class="form-group">
                    <label>Upload Foto (File)</label>
                    <input type="file" name="foto_file" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                    <small style="color: var(--gray); display: block; margin-top: 0.5rem;">Maksimal 5MB. Format: JPG, PNG, GIF, WEBP</small>
                </div>
                <div class="form-group">
                    <label>Atau Masukkan URL Foto</label>
                    <input type="text" name="foto" placeholder="https://example.com/foto.jpg">
                    <small style="color: var(--gray); display: block; margin-top: 0.5rem;">Jika upload file, URL akan diabaikan</small>
                </div>
                <h3 style="color: var(--primary); margin-top: 2rem; margin-bottom: 1rem;">Profil Detail (Opsional)</h3>
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="alamat"></textarea>
                </div>
                <div class="form-group">
                    <label>No. Telepon</label>
                    <input type="text" name="no_tlp">
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi"></textarea>
                </div>
                <button type="submit" class="btn-submit">Tambah Member</button>
            </form>
        </div>

        <div class="data-section">
            <h2>Daftar Member</h2>
            <?php if (empty($members)): ?>
                <p style="color: var(--gray); text-align: center; padding: 2rem;">Belum ada member</p>
            <?php else: ?>
                <div class="member-grid">
                    <?php foreach ($members as $member): ?>
                        <div class="member-card">
                            <h3><?php echo htmlspecialchars($member['nama']); ?></h3>
                            <?php if ($member['jabatan']): ?>
                                <p><strong>Jabatan:</strong> <?php echo htmlspecialchars($member['jabatan']); ?></p>
                            <?php endif; ?>
                            <?php if ($member['email']): ?>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($member['email']); ?></p>
                            <?php endif; ?>
                            <?php if ($member['alamat']): ?>
                                <p><strong>Alamat:</strong> <?php echo htmlspecialchars($member['alamat']); ?></p>
                            <?php endif; ?>
                            <?php if ($member['no_tlp']): ?>
                                <p><strong>No. Telp:</strong> <?php echo htmlspecialchars($member['no_tlp']); ?></p>
                            <?php endif; ?>
                            <?php if ($member['deskripsi']): ?>
                                <p><strong>Deskripsi:</strong> <?php echo htmlspecialchars(substr($member['deskripsi'], 0, 100)) . '...'; ?></p>
                            <?php endif; ?>
                            <div class="actions">
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin hapus member ini?');">
                                    <input type="hidden" name="action" value="delete_member">
                                    <input type="hidden" name="id" value="<?php echo $member['id_member']; ?>">
                                    <button type="submit" class="btn-delete">Hapus</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

