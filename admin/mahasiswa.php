<?php
require_once '../config/auth.php';
requireAdmin();

$conn = getDBConnection();
$message = '';
$message_type = '';

// Get current admin ID
$admin_id = $_SESSION['id_admin'] ?? null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_mahasiswa') {
        $nama = trim($_POST['nama'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $tahun = $_POST['tahun'] ?? null;
        $status = $_POST['status'] ?? 'regular';
        
        if (empty($nama)) {
            $message = 'Nama harus diisi!';
            $message_type = 'error';
        } else {
            try {
                $stmt = $conn->prepare("INSERT INTO mahasiswa (nama, title, tahun, status, id_admin) VALUES (:nama, :title, :tahun, :status, :id_admin)");
                $stmt->execute([
                    'nama' => $nama,
                    'title' => $title ?: null,
                    'tahun' => $tahun ?: null,
                    'status' => $status,
                    'id_admin' => $admin_id
                ]);
                $message = 'Mahasiswa berhasil ditambahkan!';
                $message_type = 'success';
            } catch(PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'update_mahasiswa') {
        $id = $_POST['id'] ?? 0;
        $nama = trim($_POST['nama'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $tahun = $_POST['tahun'] ?? null;
        $status = $_POST['status'] ?? 'regular';
        
        if (empty($nama)) {
            $message = 'Nama harus diisi!';
            $message_type = 'error';
        } else {
            try {
                $stmt = $conn->prepare("UPDATE mahasiswa SET nama = :nama, title = :title, tahun = :tahun, status = :status WHERE id_mahasiswa = :id");
                $stmt->execute([
                    'id' => $id,
                    'nama' => $nama,
                    'title' => $title ?: null,
                    'tahun' => $tahun ?: null,
                    'status' => $status
                ]);
                $message = 'Mahasiswa berhasil diupdate!';
                $message_type = 'success';
            } catch(PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'delete_mahasiswa') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $conn->prepare("DELETE FROM mahasiswa WHERE id_mahasiswa = :id");
            $stmt->execute(['id' => $id]);
            $message = 'Mahasiswa berhasil dihapus!';
            $message_type = 'success';
        } catch(PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Get all mahasiswa
$stmt = $conn->query("SELECT * FROM mahasiswa ORDER BY nama");
$mahasiswa_list = $stmt->fetchAll();

// Get mahasiswa for edit
$edit_mahasiswa = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM mahasiswa WHERE id_mahasiswa = :id");
    $stmt->execute(['id' => $edit_id]);
    $edit_mahasiswa = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mahasiswa - CMS InLET</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <style>
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
        th, td {
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
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-magang {
            background: #dbeafe;
            color: #1e40af;
        }
        .badge-skripsi {
            background: #fce7f3;
            color: #9f1239;
        }
        .badge-regular {
            background: #f1f5f9;
            color: #475569;
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
            text-decoration: none;
            display: inline-block;
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
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            margin-left: 0.5rem;
        }
        .btn-cancel:hover {
            background: #4b5563;
        }
    </style>
</head>
<body>
    <?php $active_page = 'mahasiswa'; include __DIR__ . '/partials/sidebar.php'; ?>
    
    <main class="content">
        <div class="content-inner">
            <div class="cms-content">
                <h1 style="color: var(--primary); margin-bottom: 2rem;"><i class="ri-graduation-cap-line"></i> Kelola Mahasiswa</h1>

                <?php if ($message): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Add/Edit Form -->
                <div class="form-section <?php echo $edit_mahasiswa ? 'edit-form-section active' : ''; ?>">
                    <h2><?php echo $edit_mahasiswa ? 'Edit Mahasiswa' : 'Tambah Mahasiswa Baru'; ?></h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="<?php echo $edit_mahasiswa ? 'update_mahasiswa' : 'add_mahasiswa'; ?>">
                        <?php if ($edit_mahasiswa): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_mahasiswa['id_mahasiswa']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="nama">Nama *</label>
                            <input type="text" id="nama" name="nama" 
                                   value="<?php echo htmlspecialchars($edit_mahasiswa['nama'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="title">Title/Judul</label>
                            <input type="text" id="title" name="title" 
                                   value="<?php echo htmlspecialchars($edit_mahasiswa['title'] ?? ''); ?>" 
                                   placeholder="Judul penelitian/skripsi">
                        </div>
                        
                        <div class="form-group">
                            <label for="tahun">Tahun</label>
                            <input type="number" id="tahun" name="tahun" 
                                   value="<?php echo htmlspecialchars($edit_mahasiswa['tahun'] ?? ''); ?>" 
                                   min="2000" max="2100">
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Status *</label>
                            <select id="status" name="status" required>
                                <option value="regular" <?php echo (($edit_mahasiswa['status'] ?? 'regular') === 'regular') ? 'selected' : ''; ?>>Regular</option>
                                <option value="magang" <?php echo (($edit_mahasiswa['status'] ?? '') === 'magang') ? 'selected' : ''; ?>>Magang</option>
                                <option value="skripsi" <?php echo (($edit_mahasiswa['status'] ?? '') === 'skripsi') ? 'selected' : ''; ?>>Skripsi</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn-submit">
                            <?php echo $edit_mahasiswa ? 'Update Mahasiswa' : 'Tambah Mahasiswa'; ?>
                        </button>
                        <?php if ($edit_mahasiswa): ?>
                            <a href="mahasiswa.php" class="btn-cancel">Batal</a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Data List -->
                <div class="data-section">
                    <h2>Daftar Mahasiswa (<?php echo count($mahasiswa_list); ?>)</h2>
                    
                    <?php if (empty($mahasiswa_list)): ?>
                        <p style="color: var(--gray);">Belum ada mahasiswa yang terdaftar.</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama</th>
                                        <th>Title</th>
                                        <th>Tahun</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mahasiswa_list as $mhs): ?>
                                        <tr>
                                            <td><?php echo $mhs['id_mahasiswa']; ?></td>
                                            <td><?php echo htmlspecialchars($mhs['nama']); ?></td>
                                            <td><?php echo htmlspecialchars($mhs['title'] ?? '-'); ?></td>
                                            <td><?php echo $mhs['tahun'] ?? '-'; ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $mhs['status']; ?>">
                                                    <?php 
                                                    echo ucfirst($mhs['status']); 
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="?edit=<?php echo $mhs['id_mahasiswa']; ?>" class="btn-edit">
                                                    <i class="ri-edit-line"></i> Edit
                                                </a>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin hapus mahasiswa ini?');">
                                                    <input type="hidden" name="action" value="delete_mahasiswa">
                                                    <input type="hidden" name="id" value="<?php echo $mhs['id_mahasiswa']; ?>">
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
                </div>
            </div>
        </div>
    </main>
</body>
</html>

