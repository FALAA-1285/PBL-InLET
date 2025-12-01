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

    if ($action === 'add_mitra') {
        $nama_institusi = trim($_POST['nama_institusi'] ?? '');
        $logo = $_POST['logo'] ?? ''; // URL input

        // Handle file upload
        if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImage($_FILES['logo_file'], 'mitra/');
            if ($uploadResult['success']) {
                $logo = $uploadResult['path'];
            } else {
                $message = $uploadResult['message'];
                $message_type = 'error';
            }
        }

        if (empty($message)) {
            if (empty($nama_institusi)) {
                $message = 'Nama institusi harus diisi!';
                $message_type = 'error';
            } else {
                try {
                    $stmt = $conn->prepare("INSERT INTO mitra (nama_institusi, logo) VALUES (:nama_institusi, :logo)");
                    $stmt->execute([
                        'nama_institusi' => $nama_institusi,
                        'logo' => $logo ?: null
                    ]);
                    $message = 'Mitra berhasil ditambahkan!';
                    $message_type = 'success';
                } catch (PDOException $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $message_type = 'error';
                }
            }
        }
    } elseif ($action === 'update_mitra') {
        $id = $_POST['id'] ?? 0;
        $nama_institusi = trim($_POST['nama_institusi'] ?? '');
        $logo = $_POST['logo'] ?? '';

        // Handle file upload
        if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImage($_FILES['logo_file'], 'mitra/');
            if ($uploadResult['success']) {
                $logo = $uploadResult['path'];
            } else {
                $message = $uploadResult['message'];
                $message_type = 'error';
            }
        }

        if (empty($message)) {
            if (empty($nama_institusi)) {
                $message = 'Nama institusi harus diisi!';
                $message_type = 'error';
            } else {
                try {
                    if ($logo) {
                        $stmt = $conn->prepare("UPDATE mitra SET nama_institusi = :nama_institusi, logo = :logo WHERE id_mitra = :id");
                        $stmt->execute([
                            'id' => $id,
                            'nama_institusi' => $nama_institusi,
                            'logo' => $logo
                        ]);
                    } else {
                        $stmt = $conn->prepare("UPDATE mitra SET nama_institusi = :nama_institusi WHERE id_mitra = :id");
                        $stmt->execute([
                            'id' => $id,
                            'nama_institusi' => $nama_institusi
                        ]);
                    }
                    $message = 'Mitra berhasil diupdate!';
                    $message_type = 'success';
                } catch (PDOException $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $message_type = 'error';
                }
            }
        }
    } elseif ($action === 'delete_mitra') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $conn->prepare("DELETE FROM mitra WHERE id_mitra = :id");
            $stmt->execute(['id' => $id]);
            $message = 'Mitra berhasil dihapus!';
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Get all mitra
$stmt = $conn->query("SELECT * FROM mitra ORDER BY nama_institusi");
$mitra_list = $stmt->fetchAll();

// Get mitra for edit
$edit_mitra = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM mitra WHERE id_mitra = :id");
    $stmt->execute(['id' => $edit_id]);
    $edit_mitra = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Partners - CMS InLET</title>
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

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
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

        .logo-cell {
            max-width: 120px;
        }

        .logo-cell img {
            max-width: 100px;
            max-height: 60px;
            object-fit: contain;
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
            text-decoration: none;
            display: inline-block;
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
    <?php $active_page = 'mitra';
    include __DIR__ . '/partials/sidebar.php'; ?>

    <main class="content">
        <div class="content-inner">
            <div class="cms-content">
                <h1 class="text-primary mb-4"><i class="ri-community-line"></i> Kelola Mitra Lab</h1>

                <?php if ($message): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Add/Edit Form -->
                <div class="form-section <?php echo $edit_mitra ? 'edit-form-section active' : ''; ?>">
                    <h2><?php echo $edit_mitra ? 'Edit Partner' : 'Add New Partner'; ?></h2>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action"
                            value="<?php echo $edit_mitra ? 'update_mitra' : 'add_mitra'; ?>">
                        <?php if ($edit_mitra): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_mitra['id_mitra']; ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="nama_institusi">Nama Institusi *</label>
                            <input type="text" id="nama_institusi" name="nama_institusi"
                                value="<?php echo htmlspecialchars($edit_mitra['nama_institusi'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="logo">Logo URL (optional)</label>
                            <input type="url" id="logo" name="logo"
                                value="<?php echo htmlspecialchars($edit_mitra['logo'] ?? ''); ?>"
                                placeholder="https://example.com/logo.png">
                        </div>

                        <div class="form-group">
                            <label for="logo_file">Atau Upload Logo</label>
                            <input type="file" id="logo_file" name="logo_file" accept="image/*">
                        </div>

                        <button type="submit" class="btn-submit">
                            <?php echo $edit_mitra ? 'Update Partner' : 'Add Partner'; ?>
                        </button>
                        <?php if ($edit_mitra): ?>
                            <a href="mitra.php" class="btn-cancel">Batal</a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Data List -->
                <div class="data-section">
                    <h2>Daftar Mitra (<?php echo count($mitra_list); ?>)</h2>

                    <?php if (empty($mitra_list)): ?>
                        <p class="muted-gray">No partners registered yet.</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Logo</th>
                                        <th>Nama Institusi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mitra_list as $mitra): ?>
                                        <tr>
                                            <td><?php echo $mitra['id_mitra']; ?></td>
                                            <td class="logo-cell">
                                                <?php if (!empty($mitra['logo'])): ?>
                                                    <img src="<?php echo htmlspecialchars($mitra['logo']); ?>"
                                                        alt="<?php echo htmlspecialchars($mitra['nama_institusi']); ?>"
                                                        onerror="this.style.display='none'">
                                                <?php else: ?>
                                                    <span class="muted-gray">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($mitra['nama_institusi']); ?></td>
                                            <td>
                                                <a href="?edit=<?php echo $mitra['id_mitra']; ?>" class="btn-edit">
                                                    <i class="ri-edit-line"></i> Edit
                                                </a>
                                                <form method="POST" class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this partner?');">
                                                    <input type="hidden" name="action" value="delete_mitra">
                                                    <input type="hidden" name="id" value="<?php echo $mitra['id_mitra']; ?>">
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
    </main>
</body>

</html>