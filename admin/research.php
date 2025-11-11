<?php
require_once '../config/auth.php';
requireAdmin();

$conn = getDBConnection();
$message = '';
$message_type = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_artikel') {
        $judul = $_POST['judul'] ?? '';
        $tahun = $_POST['tahun'] ?? null;
        $konten = $_POST['konten'] ?? '';
        
        try {
            $stmt = $conn->prepare("INSERT INTO artikel (judul, tahun, konten) VALUES (:judul, :tahun, :konten)");
            $stmt->execute(['judul' => $judul, 'tahun' => $tahun ?: null, 'konten' => $konten]);
            $message = 'Artikel berhasil ditambahkan!';
            $message_type = 'success';
        } catch(PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    } elseif ($action === 'add_progress') {
        $judul = $_POST['judul'] ?? '';
        $tahun = $_POST['tahun'] ?? null;
        $deskripsi = $_POST['deskripsi'] ?? '';
        $id_artikel = $_POST['id_artikel'] ?? null;
        $id_mhs = $_POST['id_mhs'] ?? null;
        $id_member = $_POST['id_member'] ?? null;
        
        try {
            $stmt = $conn->prepare("INSERT INTO progress (judul, tahun, deskripsi, id_artikel, id_mhs, id_member) VALUES (:judul, :tahun, :deskripsi, :id_artikel, :id_mhs, :id_member)");
            $stmt->execute([
                'judul' => $judul,
                'tahun' => $tahun ?: null,
                'deskripsi' => $deskripsi,
                'id_artikel' => $id_artikel ?: null,
                'id_mhs' => $id_mhs ?: null,
                'id_member' => $id_member ?: null
            ]);
            $message = 'Progress berhasil ditambahkan!';
            $message_type = 'success';
        } catch(PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    } elseif ($action === 'delete_artikel') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $conn->prepare("DELETE FROM artikel WHERE id_artikel = :id");
            $stmt->execute(['id' => $id]);
            $message = 'Artikel berhasil dihapus!';
            $message_type = 'success';
        } catch(PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    } elseif ($action === 'delete_progress') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $conn->prepare("DELETE FROM progress WHERE id_progress = :id");
            $stmt->execute(['id' => $id]);
            $message = 'Progress berhasil dihapus!';
            $message_type = 'success';
        } catch(PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Get all articles
$stmt = $conn->query("SELECT * FROM artikel ORDER BY tahun DESC, judul");
$artikels = $stmt->fetchAll();

// Get all progress
$stmt = $conn->query("SELECT p.*, a.judul as artikel_judul, m.nama as mahasiswa_nama, mem.nama as member_nama 
                      FROM progress p 
                      LEFT JOIN artikel a ON p.id_artikel = a.id_artikel 
                      LEFT JOIN mahasiswa m ON p.id_mhs = m.id_mhs 
                      LEFT JOIN member mem ON p.id_member = mem.id_member 
                      ORDER BY p.created_at DESC");
$progress_list = $stmt->fetchAll();

// Get dropdown options
$stmt = $conn->query("SELECT id_mhs, nama FROM mahasiswa ORDER BY nama");
$mahasiswa_list = $stmt->fetchAll();

$stmt = $conn->query("SELECT id_member, nama FROM member ORDER BY nama");
$member_list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Research - CMS InLET</title>
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
        .form-group select,
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
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
        }
        .form-group textarea {
            min-height: 150px;
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
        .tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid #e2e8f0;
        }
        .tab {
            padding: 1rem 2rem;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray);
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        .tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="admin-header-content">
            <h1>Kelola Research - CMS InLET</h1>
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

        <div class="tabs">
            <button class="tab active" onclick="showTab('artikel')">Artikel</button>
            <button class="tab" onclick="showTab('progress')">Progress</button>
        </div>

        <!-- Artikel Tab -->
        <div id="artikel-tab" class="tab-content active">
            <div class="form-section">
                <h2>Tambah Artikel Baru</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_artikel">
                    <div class="form-group">
                        <label>Judul Artikel</label>
                        <input type="text" name="judul" required>
                    </div>
                    <div class="form-group">
                        <label>Tahun</label>
                        <input type="number" name="tahun" min="2000" max="2099">
                    </div>
                    <div class="form-group">
                        <label>Konten</label>
                        <textarea name="konten" required></textarea>
                    </div>
                    <button type="submit" class="btn-submit">Tambah Artikel</button>
                </form>
            </div>

            <div class="data-section">
                <h2>Daftar Artikel</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Judul</th>
                            <th>Tahun</th>
                            <th>Konten</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($artikels)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--gray);">Belum ada artikel</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($artikels as $artikel): ?>
                                <tr>
                                    <td><?php echo $artikel['id_artikel']; ?></td>
                                    <td><?php echo htmlspecialchars($artikel['judul']); ?></td>
                                    <td><?php echo $artikel['tahun'] ?? '-'; ?></td>
                                    <td><?php echo htmlspecialchars(substr($artikel['konten'], 0, 50)) . '...'; ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin hapus artikel ini?');">
                                            <input type="hidden" name="action" value="delete_artikel">
                                            <input type="hidden" name="id" value="<?php echo $artikel['id_artikel']; ?>">
                                            <button type="submit" class="btn-delete">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Progress Tab -->
        <div id="progress-tab" class="tab-content">
            <div class="form-section">
                <h2>Tambah Progress Baru</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_progress">
                    <div class="form-group">
                        <label>Judul Progress</label>
                        <input type="text" name="judul" required>
                    </div>
                    <div class="form-group">
                        <label>Tahun</label>
                        <input type="number" name="tahun" min="2000" max="2099">
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Artikel (Opsional)</label>
                        <select name="id_artikel">
                            <option value="">-- Pilih Artikel --</option>
                            <?php foreach ($artikels as $artikel): ?>
                                <option value="<?php echo $artikel['id_artikel']; ?>">
                                    <?php echo htmlspecialchars($artikel['judul']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Mahasiswa (Opsional)</label>
                        <select name="id_mhs">
                            <option value="">-- Pilih Mahasiswa --</option>
                            <?php foreach ($mahasiswa_list as $mhs): ?>
                                <option value="<?php echo $mhs['id_mhs']; ?>">
                                    <?php echo htmlspecialchars($mhs['nama']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Member (Opsional)</label>
                        <select name="id_member">
                            <option value="">-- Pilih Member --</option>
                            <?php foreach ($member_list as $mem): ?>
                                <option value="<?php echo $mem['id_member']; ?>">
                                    <?php echo htmlspecialchars($mem['nama']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn-submit">Tambah Progress</button>
                </form>
            </div>

            <div class="data-section">
                <h2>Daftar Progress</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Judul</th>
                            <th>Tahun</th>
                            <th>Artikel</th>
                            <th>Mahasiswa</th>
                            <th>Member</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($progress_list)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--gray);">Belum ada progress</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($progress_list as $progress): ?>
                                <tr>
                                    <td><?php echo $progress['id_progress']; ?></td>
                                    <td><?php echo htmlspecialchars($progress['judul']); ?></td>
                                    <td><?php echo $progress['tahun'] ?? '-'; ?></td>
                                    <td><?php echo htmlspecialchars($progress['artikel_judul'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($progress['mahasiswa_nama'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($progress['member_nama'] ?? '-'); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin hapus progress ini?');">
                                            <input type="hidden" name="action" value="delete_progress">
                                            <input type="hidden" name="id" value="<?php echo $progress['id_progress']; ?>">
                                            <button type="submit" class="btn-delete">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.classList.add('active');
        }
    </script>
</body>
</html>

