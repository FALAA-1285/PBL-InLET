<?php
require_once '../config/auth.php';
requireAdmin();

$conn = getDBConnection();
$message = '';
$message_type = '';

// Get current admin ID and validate it exists in admin table
$admin_id = $_SESSION['id_admin'] ?? null;
if ($admin_id) {
    try {
        $check_admin = $conn->prepare("SELECT id_admin FROM admin WHERE id_admin = :id_admin LIMIT 1");
        $check_admin->execute(['id_admin' => $admin_id]);
        if (!$check_admin->fetch()) {
            // Admin ID doesn't exist, set to null
            $admin_id = null;
        }
    } catch (PDOException $e) {
        // If error checking, set to null to be safe
        $admin_id = null;
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_ruang') {
        $nama_ruang = trim($_POST['nama_ruang'] ?? '');

        if (empty($nama_ruang)) {
            $message = 'Nama ruang harus diisi!';
            $message_type = 'error';
        } else {
            try {
                if ($admin_id) {
                    $stmt = $conn->prepare("INSERT INTO ruang_lab (nama_ruang, id_admin) VALUES (:nama_ruang, :id_admin)");
                    $stmt->execute([
                        'nama_ruang' => $nama_ruang,
                        'id_admin' => $admin_id
                    ]);
                } else {
                    // If no valid admin ID, insert without id_admin (will be NULL)
                    $stmt = $conn->prepare("INSERT INTO ruang_lab (nama_ruang) VALUES (:nama_ruang)");
                    $stmt->execute([
                        'nama_ruang' => $nama_ruang
                    ]);
                }
                $message = 'Lab room successfully added!';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'update_ruang') {
        $id = $_POST['id'] ?? 0;
        $nama_ruang = trim($_POST['nama_ruang'] ?? '');
        $status = trim($_POST['status'] ?? 'tersedia');

        if (empty($nama_ruang)) {
            $message = 'Nama ruang harus diisi!';
            $message_type = 'error';
        } else {
            try {
                $stmt = $conn->prepare("UPDATE ruang_lab SET nama_ruang = :nama_ruang, status = :status WHERE id_ruang_lab = :id");
                $stmt->execute([
                    'id' => $id,
                    'nama_ruang' => $nama_ruang,
                    'status' => $status
                ]);
                // Redirect to add form after successful update
                header('Location: ruang_lab.php?updated=1');
                exit;
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'delete_ruang') {
        $id = $_POST['id'] ?? 0;
        try {
            // Check if room is being borrowed
            $check_stmt = $conn->prepare("SELECT COUNT(*) FROM peminjaman WHERE id_ruang = :id AND status = 'dipinjam'");
            $check_stmt->execute(['id' => $id]);
            $borrowed_count = $check_stmt->fetchColumn();

            if ($borrowed_count > 0) {
                $message = 'Room cannot be deleted because it is currently borrowed!';
                $message_type = 'error';
            } else {
                $stmt = $conn->prepare("DELETE FROM ruang_lab WHERE id_ruang_lab = :id");
                $stmt->execute(['id' => $id]);
                $message = 'Lab room successfully deleted!';
                $message_type = 'success';
            }
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Check if redirected from update
if (isset($_GET['updated']) && $_GET['updated'] == '1') {
    $message = 'Lab room successfully updated!';
    $message_type = 'success';
}

// Pagination setup
$items_per_page = 10;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Get total count
$count_stmt = $conn->query("SELECT COUNT(*) FROM ruang_lab");
$total_items = $count_stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

// Get ruang lab with pagination
$stmt = $conn->prepare("SELECT r.*, 
    (SELECT COUNT(*) FROM peminjaman WHERE id_ruang = r.id_ruang_lab AND status = 'dipinjam') as jumlah_dipinjam
    FROM ruang_lab r 
    ORDER BY nama_ruang
    LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$ruang_list = $stmt->fetchAll();

// Get ruang for edit
$edit_ruang = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM ruang_lab WHERE id_ruang_lab = :id");
    $stmt->execute(['id' => $edit_id]);
    $edit_ruang = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Room Management - CMS InLET</title>
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

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .status-tersedia {
            background: #d1fae5;
            color: #065f46;
        }

        .status-maintenance {
            background: #fef3c7;
            color: #92400e;
        }

        .status-tidak-tersedia {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-dipinjam {
            background: #fef3c7;
            color: #92400e;
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
    </style>
</head>

<body>
    <?php $active_page = 'ruang_lab';
    include __DIR__ . '/partials/sidebar.php'; ?>

    <main class="content">
        <div class="content-inner">
            <div class="cms-content">
                <h1 class="text-primary mb-4"><i class="ri-building-line"></i> Manage Lab Rooms</h1>

                <?php if ($message): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Add/Edit Form -->
                <div class="form-section <?php echo $edit_ruang ? 'edit-form-section active' : ''; ?>">
                    <h2><?php echo $edit_ruang ? 'Edit Lab Room' : 'Add New Lab Room'; ?></h2>
                    <form method="POST">
                        <input type="hidden" name="action"
                            value="<?php echo $edit_ruang ? 'update_ruang' : 'add_ruang'; ?>">
                        <?php if ($edit_ruang): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_ruang['id_ruang_lab']; ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="nama_ruang">Room Name *</label>
                            <input type="text" id="nama_ruang" name="nama_ruang"
                                value="<?php echo htmlspecialchars($edit_ruang['nama_ruang'] ?? ''); ?>" required>
                        </div>

                        <?php if ($edit_ruang): ?>
                        <div class="form-group">
                            <label for="status">Status *</label>
                            <select id="status" name="status" required>
                                <option value="tersedia" <?php echo (($edit_ruang['status'] ?? 'tersedia') === 'tersedia') ? 'selected' : ''; ?>>Available</option>
                                <option value="maintenance" <?php echo (($edit_ruang['status'] ?? '') === 'maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                                <option value="tidak_tersedia" <?php echo (($edit_ruang['status'] ?? '') === 'tidak_tersedia') ? 'selected' : ''; ?>>Not Available</option>
                            </select>
                        </div>
                        <?php endif; ?>

                        <button type="submit" class="btn-submit">
                            <?php echo $edit_ruang ? 'Update Lab Room' : 'Add Lab Room'; ?>
                        </button>
                        <?php if ($edit_ruang): ?>
                            <a href="ruang_lab.php" class="btn-cancel">Cancel</a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Data List -->
                <div class="data-section">
                    <h2>Lab Rooms List (<?php echo $total_items; ?>)</h2>

                    <?php if (empty($ruang_list)): ?>
                        <p class="muted-gray">No lab rooms registered yet.</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Room Name</th>
                                        <th>Status</th>
                                        <th>Currently Borrowed</th>
                                        <th>Created At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ruang_list as $ruang): ?>
                                        <tr>
                                            <td><?php echo $ruang['id_ruang_lab']; ?></td>
                                            <td><?php echo htmlspecialchars($ruang['nama_ruang']); ?></td>
                                            <td>
                                                <?php 
                                                // Calculate status: if borrowed, show "Borrowed", otherwise show status from database
                                                $jumlah_dipinjam = (int)($ruang['jumlah_dipinjam'] ?? 0);
                                                $db_status = $ruang['status'] ?? 'tersedia';
                                                
                                                if ($jumlah_dipinjam > 0) {
                                                    // If there's active loan, show "Borrowed"
                                                    $status_class = 'status-dipinjam';
                                                    $status_text = 'Borrowed';
                                                } else {
                                                    // Otherwise, show status from database
                                                    $status_map = [
                                                        'tersedia' => ['class' => 'status-tersedia', 'text' => 'Available'],
                                                        'maintenance' => ['class' => 'status-maintenance', 'text' => 'Maintenance'],
                                                        'tidak_tersedia' => ['class' => 'status-tidak-tersedia', 'text' => 'Not Available']
                                                    ];
                                                    $status_info = $status_map[$db_status] ?? ['class' => 'status-tersedia', 'text' => 'Available'];
                                                    $status_class = $status_info['class'];
                                                    $status_text = $status_info['text'];
                                                }
                                                ?>
                                                <span class="status-badge <?php echo $status_class; ?>">
                                                    <?php echo $status_text; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($jumlah_dipinjam > 0): ?>
                                                    <span class="status-badge status-dipinjam">
                                                        <?php echo $jumlah_dipinjam; ?> active loan(s)
                                                    </span>
                                                <?php else: ?>
                                                    <span class="muted-gray">None</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('d M Y', strtotime($ruang['created_at'])); ?></td>
                                            <td>
                                                <a href="?edit=<?php echo $ruang['id_ruang_lab']; ?>" class="btn-edit">
                                                    <i class="ri-edit-line"></i> Edit
                                                </a>
                                                <form method="POST" class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this room?');">
                                                    <input type="hidden" name="action" value="delete_ruang">
                                                    <input type="hidden" name="id" value="<?php echo $ruang['id_ruang_lab']; ?>">
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
                                Showing <?php echo ($offset + 1); ?> - <?php echo min($offset + $items_per_page, $total_items); ?> of <?php echo $total_items; ?> rooms
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</body>

</html>

