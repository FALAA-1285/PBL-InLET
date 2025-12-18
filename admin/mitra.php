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
                    // Fix sequence if it's out of sync
                    try {
                        $max_id_stmt = $conn->query("SELECT COALESCE(MAX(id_mitra), 0) as max_id FROM mitra");
                        $max_id = $max_id_stmt->fetch()['max_id'];
                        $conn->exec("SELECT setval('mitra_id_mitra_seq', " . ($max_id + 1) . ", false)");
                    } catch (PDOException $seq_e) {
                        // Sequence might not exist or error, continue anyway
                    }
                    
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

// Pagination setup
$items_per_page = 10;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Get total count
try {
    $count_stmt = $conn->query("SELECT COUNT(*) FROM mitra");
    $total_items = $count_stmt->fetchColumn();
    $total_pages = ceil($total_items / $items_per_page);
} catch (PDOException $e) {
    $total_items = 0;
    $total_pages = 0;
}

// Get mitra with pagination
try {
    $stmt = $conn->prepare("SELECT * FROM mitra ORDER BY nama_institusi LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $mitra_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $mitra_list = [];
}

// Get mitra for edit
$edit_mitra = null;
if (isset($_GET['edit'])) {
    try {
        $edit_id = intval($_GET['edit']);
        $stmt = $conn->prepare("SELECT * FROM mitra WHERE id_mitra = :id");
        $stmt->execute(['id' => $edit_id]);
        $edit_mitra = $stmt->fetch();
    } catch (PDOException $e) {
        $edit_mitra = null;
    }
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
                <h1 class="text-primary mb-4"><i class="ri-community-line"></i> Manage Lab Partners</h1>

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
                            <label for="nama_institusi">Institution Name *</label>
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
                            <label for="logo_file">Or Upload Logo</label>
                            <input type="file" id="logo_file" name="logo_file" accept="image/*">
                        </div>

                        <button type="submit" class="btn-submit">
                            <?php echo $edit_mitra ? 'Update Partner' : 'Add Partner'; ?>
                        </button>
                        <?php if ($edit_mitra): ?>
                            <a href="mitra.php" class="btn-cancel">Cancel</a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Data List -->
                <div class="data-section">
                    <h2>Partner List (<?php echo $total_items; ?>)</h2>

                    <?php if (empty($mitra_list)): ?>
                        <p class="muted-gray">No partners registered yet.</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Logo</th>
                                        <th>Institution Name</th>
                                        <th>Action</th>
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
                                Showing <?php echo ($offset + 1); ?> - <?php echo min($offset + $items_per_page, $total_items); ?> of <?php echo $total_items; ?> partners
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html>