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
            // Menggunakan stored procedure tambah_artikel
            $stmt = $conn->prepare("SELECT tambah_artikel(:judul, :tahun, :konten)");
            $stmt->execute([
                'judul' => $judul, 
                'tahun' => $tahun ?: null, 
                'konten' => $konten
            ]);
            $stmt->fetch(); // Execute function yang returns VOID
            $message = 'Artikel berhasil ditambahkan!';
            $message_type = 'success';
        } catch(PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    } elseif ($action === 'add_penelitian') {
        $judul = $_POST['judul'] ?? '';
        $tahun = $_POST['tahun'] ?? null;
        $deskripsi = $_POST['deskripsi'] ?? '';
        $id_artikel = $_POST['id_artikel'] ?? null;
        $id_mhs = $_POST['id_mhs'] ?? null;
        $id_member = $_POST['id_member'] ?? null;
        $id_produk = $_POST['id_produk'] ?? null;
        $id_mitra = $_POST['id_mitra'] ?? null;
        $tgl_mulai = $_POST['tgl_mulai'] ?? null;
        $tgl_selesai = $_POST['tgl_selesai'] ?? null;
        
        try {
            $stmt = $conn->prepare("INSERT INTO penelitian (judul, tahun, deskripsi, id_artikel, id_mhs, id_member, id_produk, id_mitra, tgl_mulai, tgl_selesai) VALUES (:judul, :tahun, :deskripsi, :id_artikel, :id_mhs, :id_member, :id_produk, :id_mitra, :tgl_mulai, :tgl_selesai)");
            $stmt->execute([
                'judul' => $judul,
                'tahun' => $tahun ?: null,
                'deskripsi' => $deskripsi ?: null,
                'id_artikel' => $id_artikel ?: null,
                'id_mhs' => $id_mhs ?: null,
                'id_member' => $id_member ?: null,
                'id_produk' => $id_produk ?: null,
                'id_mitra' => $id_mitra ?: null,
                'tgl_mulai' => $tgl_mulai ?: null,
                'tgl_selesai' => $tgl_selesai ?: null
            ]);
            $message = 'Penelitian berhasil ditambahkan!';
            $message_type = 'success';
        } catch(PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    } elseif ($action === 'update_artikel') {
        $id = $_POST['id'] ?? 0;
        $judul = $_POST['judul'] ?? '';
        $tahun = $_POST['tahun'] ?? null;
        $konten = $_POST['konten'] ?? '';
        
        try {
            $stmt = $conn->prepare("UPDATE artikel SET judul = :judul, tahun = :tahun, konten = :konten WHERE id_artikel = :id");
            $stmt->execute([
                'id' => $id,
                'judul' => $judul,
                'tahun' => $tahun ?: null,
                'konten' => $konten
            ]);
            $message = 'Artikel berhasil diupdate!';
            $message_type = 'success';
        } catch(PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    } elseif ($action === 'update_penelitian') {
        $id = $_POST['id'] ?? 0;
        $judul = $_POST['judul'] ?? '';
        $tahun = $_POST['tahun'] ?? null;
        $deskripsi = $_POST['deskripsi'] ?? '';
        $id_artikel = $_POST['id_artikel'] ?? null;
        $id_mhs = $_POST['id_mhs'] ?? null;
        $id_member = $_POST['id_member'] ?? null;
        $id_produk = $_POST['id_produk'] ?? null;
        $id_mitra = $_POST['id_mitra'] ?? null;
        $tgl_mulai = $_POST['tgl_mulai'] ?? null;
        $tgl_selesai = $_POST['tgl_selesai'] ?? null;
        
        try {
            $stmt = $conn->prepare("UPDATE penelitian SET judul = :judul, tahun = :tahun, deskripsi = :deskripsi, id_artikel = :id_artikel, id_mhs = :id_mhs, id_member = :id_member, id_produk = :id_produk, id_mitra = :id_mitra, tgl_mulai = :tgl_mulai, tgl_selesai = :tgl_selesai WHERE id_penelitian = :id");
            $stmt->execute([
                'id' => $id,
                'judul' => $judul,
                'tahun' => $tahun ?: null,
                'deskripsi' => $deskripsi ?: null,
                'id_artikel' => $id_artikel ?: null,
                'id_mhs' => $id_mhs ?: null,
                'id_member' => $id_member ?: null,
                'id_produk' => $id_produk ?: null,
                'id_mitra' => $id_mitra ?: null,
                'tgl_mulai' => $tgl_mulai ?: null,
                'tgl_selesai' => $tgl_selesai ?: null
            ]);
            $message = 'Penelitian berhasil diupdate!';
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
    } elseif ($action === 'delete_penelitian') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $conn->prepare("DELETE FROM penelitian WHERE id_penelitian = :id");
            $stmt->execute(['id' => $id]);
            $message = 'Penelitian berhasil dihapus!';
            $message_type = 'success';
        } catch(PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Pagination setup for articles
$items_per_page = 10;
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'artikel';

// Get page numbers - check if we're on the correct tab
$current_page_artikel = 1;
$current_page_progress = 1;

if ($current_tab === 'artikel' && isset($_GET['page'])) {
    $current_page_artikel = max(1, intval($_GET['page']));
} elseif (isset($_GET['page_artikel'])) {
    $current_page_artikel = max(1, intval($_GET['page_artikel']));
}

if ($current_tab === 'penelitian' && isset($_GET['page'])) {
    $current_page_progress = max(1, intval($_GET['page']));
} elseif (isset($_GET['page_penelitian'])) {
    $current_page_progress = max(1, intval($_GET['page_penelitian']));
}

// Get total count for articles
$stmt = $conn->query("SELECT COUNT(*) FROM artikel");
$total_items_artikel = $stmt->fetchColumn();
$total_pages_artikel = ceil($total_items_artikel / $items_per_page);
$offset_artikel = ($current_page_artikel - 1) * $items_per_page;

// Get articles with pagination (for display)
$stmt = $conn->prepare("SELECT * FROM artikel ORDER BY tahun DESC, judul LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset_artikel, PDO::PARAM_INT);
$stmt->execute();
$artikels = $stmt->fetchAll();

// Get all articles for dropdown (no pagination)
$stmt = $conn->query("SELECT id_artikel, judul FROM artikel ORDER BY judul");
$artikels_dropdown = $stmt->fetchAll();

// Get total count for penelitian
$stmt = $conn->query("SELECT COUNT(*) FROM penelitian");
$total_items_progress = $stmt->fetchColumn();
$total_pages_progress = ceil($total_items_progress / $items_per_page);
$offset_progress = ($current_page_progress - 1) * $items_per_page;

// Get penelitian with pagination
$stmt = $conn->prepare("SELECT p.*, a.judul as artikel_judul, m.nama as mahasiswa_nama, mem.nama as member_nama, pr.nama_produk, mt.nama_institusi as mitra_nama
                      FROM penelitian p 
                      LEFT JOIN artikel a ON p.id_artikel = a.id_artikel 
                      LEFT JOIN mahasiswa m ON p.id_mhs = m.id_mahasiswa 
                      LEFT JOIN member mem ON p.id_member = mem.id_member 
                      LEFT JOIN produk pr ON p.id_produk = pr.id_produk
                      LEFT JOIN mitra mt ON p.id_mitra = mt.id_mitra
                      ORDER BY p.created_at DESC
                      LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset_progress, PDO::PARAM_INT);
$stmt->execute();
$progress_list = $stmt->fetchAll();

// Get dropdown options
$stmt = $conn->query("SELECT id_mahasiswa, nama FROM mahasiswa ORDER BY nama");
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
            text-decoration: none;
            display: inline-block;
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
    <?php $active_page = 'research'; include __DIR__ . '/partials/sidebar.php'; ?>
    <main class="content">
        <div class="content-inner">
            <div class="admin-header">
                <div class="admin-header-content">
                    <div>
                        <p>Kelola artikel dan penelitian</p>
                        <h1>Research CMS InLET</h1>
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
            <a href="?tab=artikel&page=1" class="tab <?php echo ($current_tab === 'artikel') ? 'active' : ''; ?>">Artikel</a>
            <a href="?tab=penelitian&page=1" class="tab <?php echo ($current_tab === 'penelitian') ? 'active' : ''; ?>">Penelitian</a>
        </div>

        <!-- Artikel Tab -->
        <div id="artikel-tab" class="tab-content <?php echo ($current_tab === 'artikel') ? 'active' : ''; ?>">
            <!-- Edit Artikel Form (Hidden by default) -->
            <div id="edit-artikel-section" class="form-section edit-form-section">
                <h2>Edit Artikel</h2>
                <form method="POST" action="" id="edit-artikel-form">
                    <input type="hidden" name="action" value="update_artikel">
                    <input type="hidden" name="id" id="edit_artikel_id">
                    <div class="form-group">
                        <label>Judul Artikel</label>
                        <input type="text" name="judul" id="edit_artikel_judul" required>
                    </div>
                    <div class="form-group">
                        <label>Tahun</label>
                        <input type="number" name="tahun" id="edit_artikel_tahun" min="2000" max="2099">
                    </div>
                    <div class="form-group">
                        <label>Konten</label>
                        <textarea name="konten" id="edit_artikel_konten" required></textarea>
                    </div>
                    <button type="submit" class="btn-submit">Update Artikel</button>
                    <button type="button" class="btn-cancel" onclick="cancelEditArtikel()">Batal</button>
                </form>
            </div>

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
                <h2>Daftar Artikel (<?php echo count($artikels); ?>)</h2>
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
                                        <button type="button" class="btn-edit" onclick="editArtikel(<?php echo htmlspecialchars(json_encode($artikel)); ?>)">
                                            <i class="ri-edit-line"></i> Edit
                                        </button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin hapus artikel ini?');">
                                            <input type="hidden" name="action" value="delete_artikel">
                                            <input type="hidden" name="id" value="<?php echo $artikel['id_artikel']; ?>">
                                            <button type="submit" class="btn-delete">
                                                <i class="ri-delete-bin-line"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <!-- Pagination for Artikel -->
                <?php if ($total_pages_artikel > 1): ?>
                    <div class="pagination">
                        <?php if ($current_page_artikel > 1): ?>
                            <a href="?tab=artikel&page=<?php echo $current_page_artikel - 1; ?>">&laquo; Previous</a>
                        <?php else: ?>
                            <span class="disabled">&laquo; Previous</span>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $current_page_artikel - 2);
                        $end_page = min($total_pages_artikel, $current_page_artikel + 2);
                        
                        if ($start_page > 1): ?>
                            <a href="?tab=artikel&page=1">1</a>
                            <?php if ($start_page > 2): ?>
                                <span>...</span>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <?php if ($i == $current_page_artikel): ?>
                                <span class="active"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?tab=artikel&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($end_page < $total_pages_artikel): ?>
                            <?php if ($end_page < $total_pages_artikel - 1): ?>
                                <span>...</span>
                            <?php endif; ?>
                            <a href="?tab=artikel&page=<?php echo $total_pages_artikel; ?>"><?php echo $total_pages_artikel; ?></a>
                        <?php endif; ?>
                        
                        <?php if ($current_page_artikel < $total_pages_artikel): ?>
                            <a href="?tab=artikel&page=<?php echo $current_page_artikel + 1; ?>">Next &raquo;</a>
                        <?php else: ?>
                            <span class="disabled">Next &raquo;</span>
                        <?php endif; ?>
                    </div>
                    <div class="pagination-info">
                        Menampilkan <?php echo ($offset_artikel + 1); ?> - <?php echo min($offset_artikel + $items_per_page, $total_items_artikel); ?> dari <?php echo $total_items_artikel; ?> artikel
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Penelitian Tab -->
        <div id="penelitian-tab" class="tab-content <?php echo ($current_tab === 'penelitian') ? 'active' : ''; ?>">
            <!-- Edit Penelitian Form (Hidden by default) -->
            <div id="edit-penelitian-section" class="form-section edit-form-section">
                <h2>Edit Penelitian</h2>
                <form method="POST" action="" id="edit-penelitian-form">
                    <input type="hidden" name="action" value="update_penelitian">
                    <input type="hidden" name="id" id="edit_penelitian_id">
                    <div class="form-group">
                        <label>Judul Penelitian *</label>
                        <input type="text" name="judul" id="edit_penelitian_judul" required>
                    </div>
                    <div class="form-group">
                        <label>Tahun</label>
                        <input type="number" name="tahun" id="edit_penelitian_tahun" min="2000" max="2099">
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" id="edit_penelitian_deskripsi"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Artikel (Opsional)</label>
                        <select name="id_artikel" id="edit_penelitian_id_artikel">
                            <option value="">-- Pilih Artikel --</option>
                            <?php foreach ($artikels_dropdown as $artikel): ?>
                                <option value="<?php echo $artikel['id_artikel']; ?>">
                                    <?php echo htmlspecialchars($artikel['judul']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Mahasiswa (Opsional)</label>
                        <select name="id_mhs" id="edit_penelitian_id_mhs">
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
                        <select name="id_member" id="edit_penelitian_id_member">
                            <option value="">-- Pilih Member --</option>
                            <?php foreach ($member_list as $mem): ?>
                                <option value="<?php echo $mem['id_member']; ?>">
                                    <?php echo htmlspecialchars($mem['nama']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Produk (Opsional)</label>
                        <select name="id_produk" id="edit_penelitian_id_produk">
                            <option value="">-- Pilih Produk --</option>
                            <?php 
                            $produk_stmt = $conn->query("SELECT id_produk, nama_produk FROM produk ORDER BY nama_produk");
                            $produk_list = $produk_stmt->fetchAll();
                            foreach ($produk_list as $prod): ?>
                                <option value="<?php echo $prod['id_produk']; ?>">
                                    <?php echo htmlspecialchars($prod['nama_produk']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Mitra (Opsional)</label>
                        <select name="id_mitra" id="edit_penelitian_id_mitra">
                            <option value="">-- Pilih Mitra --</option>
                            <?php 
                            $mitra_stmt = $conn->query("SELECT id_mitra, nama_institusi FROM mitra ORDER BY nama_institusi");
                            $mitra_list = $mitra_stmt->fetchAll();
                            foreach ($mitra_list as $mit): ?>
                                <option value="<?php echo $mit['id_mitra']; ?>">
                                    <?php echo htmlspecialchars($mit['nama_institusi']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Mulai *</label>
                        <input type="date" name="tgl_mulai" id="edit_penelitian_tgl_mulai" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Selesai</label>
                        <input type="date" name="tgl_selesai" id="edit_penelitian_tgl_selesai">
                    </div>
                    <button type="submit" class="btn-submit">Update Penelitian</button>
                    <button type="button" class="btn-cancel" onclick="cancelEditPenelitian()">Batal</button>
                </form>
            </div>

            <div class="form-section">
                <h2>Tambah Penelitian Baru</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_penelitian">
                    <div class="form-group">
                        <label>Judul Penelitian *</label>
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
                            <?php foreach ($artikels_dropdown as $artikel): ?>
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
                    <div class="form-group">
                        <label>Produk (Opsional)</label>
                        <select name="id_produk">
                            <option value="">-- Pilih Produk --</option>
                            <?php 
                            if (!isset($produk_list)) {
                                $produk_stmt = $conn->query("SELECT id_produk, nama_produk FROM produk ORDER BY nama_produk");
                                $produk_list = $produk_stmt->fetchAll();
                            }
                            foreach ($produk_list as $prod): ?>
                                <option value="<?php echo $prod['id_produk']; ?>">
                                    <?php echo htmlspecialchars($prod['nama_produk']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Mitra (Opsional)</label>
                        <select name="id_mitra">
                            <option value="">-- Pilih Mitra --</option>
                            <?php 
                            if (!isset($mitra_list)) {
                                $mitra_stmt = $conn->query("SELECT id_mitra, nama_institusi FROM mitra ORDER BY nama_institusi");
                                $mitra_list = $mitra_stmt->fetchAll();
                            }
                            foreach ($mitra_list as $mit): ?>
                                <option value="<?php echo $mit['id_mitra']; ?>">
                                    <?php echo htmlspecialchars($mit['nama_institusi']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Mulai *</label>
                        <input type="date" name="tgl_mulai" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Selesai</label>
                        <input type="date" name="tgl_selesai">
                    </div>
                    <button type="submit" class="btn-submit">Tambah Penelitian</button>
                </form>
            </div>

            <div class="data-section">
                <h2>Daftar Penelitian (<?php echo count($progress_list); ?>)</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Judul</th>
                            <th>Tahun</th>
                            <th>Artikel</th>
                            <th>Mahasiswa</th>
                            <th>Member</th>
                            <th>Produk</th>
                            <th>Mitra</th>
                            <th>Tanggal Mulai</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($progress_list)): ?>
                            <tr>
                                <td colspan="10" style="text-align: center; color: var(--gray);">Belum ada penelitian</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($progress_list as $penelitian): ?>
                                <tr>
                                    <td><?php echo $penelitian['id_penelitian']; ?></td>
                                    <td><?php echo htmlspecialchars($penelitian['judul']); ?></td>
                                    <td><?php echo $penelitian['tahun'] ?? '-'; ?></td>
                                    <td><?php echo htmlspecialchars($penelitian['artikel_judul'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($penelitian['mahasiswa_nama'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($penelitian['member_nama'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($penelitian['nama_produk'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($penelitian['mitra_nama'] ?? '-'); ?></td>
                                    <td><?php echo $penelitian['tgl_mulai'] ? date('d M Y', strtotime($penelitian['tgl_mulai'])) : '-'; ?></td>
                                    <td>
                                        <button type="button" class="btn-edit" onclick="editPenelitian(<?php echo htmlspecialchars(json_encode($penelitian)); ?>)">
                                            <i class="ri-edit-line"></i> Edit
                                        </button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin hapus penelitian ini?');">
                                            <input type="hidden" name="action" value="delete_penelitian">
                                            <input type="hidden" name="id" value="<?php echo $penelitian['id_penelitian']; ?>">
                                            <button type="submit" class="btn-delete">
                                                <i class="ri-delete-bin-line"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <!-- Pagination for Penelitian -->
                <?php if ($total_pages_progress > 1): ?>
                    <div class="pagination">
                        <?php if ($current_page_progress > 1): ?>
                            <a href="?tab=penelitian&page=<?php echo $current_page_progress - 1; ?>">&laquo; Previous</a>
                        <?php else: ?>
                            <span class="disabled">&laquo; Previous</span>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $current_page_progress - 2);
                        $end_page = min($total_pages_progress, $current_page_progress + 2);
                        
                        if ($start_page > 1): ?>
                            <a href="?tab=penelitian&page=1">1</a>
                            <?php if ($start_page > 2): ?>
                                <span>...</span>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <?php if ($i == $current_page_progress): ?>
                                <span class="active"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?tab=penelitian&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($end_page < $total_pages_progress): ?>
                            <?php if ($end_page < $total_pages_progress - 1): ?>
                                <span>...</span>
                            <?php endif; ?>
                            <a href="?tab=penelitian&page=<?php echo $total_pages_progress; ?>"><?php echo $total_pages_progress; ?></a>
                        <?php endif; ?>
                        
                        <?php if ($current_page_progress < $total_pages_progress): ?>
                            <a href="?tab=penelitian&page=<?php echo $current_page_progress + 1; ?>">Next &raquo;</a>
                        <?php else: ?>
                            <span class="disabled">Next &raquo;</span>
                        <?php endif; ?>
                    </div>
                    <div class="pagination-info">
                        Menampilkan <?php echo ($offset_progress + 1); ?> - <?php echo min($offset_progress + $items_per_page, $total_items_progress); ?> dari <?php echo $total_items_progress; ?> penelitian
                    </div>
                <?php endif; ?>
            </div>
        </div>
        </div>
    </div>
    </main>

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
            
            // Redirect to first page of selected tab
            window.location.href = '?tab=' + tabName + '&page=1';
        }
        
        // Set active tab based on URL parameter
        window.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab') || 'artikel';
            
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            
            // Show selected tab
            document.getElementById(tab + '-tab').classList.add('active');
            const tabButtons = document.querySelectorAll('.tab');
            tabButtons.forEach(btn => {
                if (btn.textContent.toLowerCase().includes(tab)) {
                    btn.classList.add('active');
                }
            });
        });
        
        function editArtikel(artikel) {
            document.getElementById('edit_artikel_id').value = artikel.id_artikel;
            document.getElementById('edit_artikel_judul').value = artikel.judul || '';
            document.getElementById('edit_artikel_tahun').value = artikel.tahun || '';
            document.getElementById('edit_artikel_konten').value = artikel.konten || '';
            
            document.getElementById('edit-artikel-section').classList.add('active');
            document.querySelector('#artikel-tab .form-section:not(.edit-form-section)').style.display = 'none';
            
            document.getElementById('edit-artikel-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        
        function cancelEditArtikel() {
            document.getElementById('edit-artikel-section').classList.remove('active');
            document.querySelector('#artikel-tab .form-section:not(.edit-form-section)').style.display = 'block';
            document.getElementById('edit-artikel-form').reset();
        }
        
        function editPenelitian(penelitian) {
            document.getElementById('edit_penelitian_id').value = penelitian.id_penelitian;
            document.getElementById('edit_penelitian_judul').value = penelitian.judul || '';
            document.getElementById('edit_penelitian_tahun').value = penelitian.tahun || '';
            document.getElementById('edit_penelitian_deskripsi').value = penelitian.deskripsi || '';
            document.getElementById('edit_penelitian_id_artikel').value = penelitian.id_artikel || '';
            document.getElementById('edit_penelitian_id_mhs').value = penelitian.id_mhs || '';
            document.getElementById('edit_penelitian_id_member').value = penelitian.id_member || '';
            document.getElementById('edit_penelitian_id_produk').value = penelitian.id_produk || '';
            document.getElementById('edit_penelitian_id_mitra').value = penelitian.id_mitra || '';
            document.getElementById('edit_penelitian_tgl_mulai').value = penelitian.tgl_mulai || '';
            document.getElementById('edit_penelitian_tgl_selesai').value = penelitian.tgl_selesai || '';
            
            document.getElementById('edit-penelitian-section').classList.add('active');
            document.querySelector('#penelitian-tab .form-section:not(.edit-form-section)').style.display = 'none';
            
            document.getElementById('edit-penelitian-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        
        function cancelEditPenelitian() {
            document.getElementById('edit-penelitian-section').classList.remove('active');
            document.querySelector('#penelitian-tab .form-section:not(.edit-form-section)').style.display = 'block';
            document.getElementById('edit-penelitian-form').reset();
        }
    </script>
</body>
</html>

