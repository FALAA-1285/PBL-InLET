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
        $nim = trim($_POST['nim'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $tahun = $_POST['tahun'] ?? null;
        $status = $_POST['status'] ?? 'regular';

        if (empty($nim)) {
            $message = 'NIM harus diisi!';
            $message_type = 'error';
        } elseif (empty($nama)) {
            $message = 'Nama harus diisi!';
            $message_type = 'error';
        } else {
            try {
                // Use id_mahasiswa instead of nim
                $stmt = $conn->prepare("INSERT INTO mahasiswa (id_mahasiswa, nama, tahun, status) VALUES (:id_mahasiswa, :nama, :tahun, :status)");
                $stmt->execute([
                    'id_mahasiswa' => $nim, // Use nim input value as id_mahasiswa
                    'nama' => $nama,
                    'tahun' => $tahun ?: null,
                    'status' => $status
                ]);
                $message = 'Student successfully added!';
                $message_type = 'success';
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $message = 'Student ID already exists!';
                    $message_type = 'error';
                } else {
                    $message = 'Error: ' . $e->getMessage();
                    $message_type = 'error';
                }
            }
        }
    } elseif ($action === 'update_mahasiswa') {
        $id = $_POST['id'] ?? 0;
        $nama = trim($_POST['nama'] ?? '');
        $tahun = $_POST['tahun'] ?? null;
        $status = $_POST['status'] ?? 'regular';

        if (empty($nama)) {
            $message = 'Nama harus diisi!';
            $message_type = 'error';
        } else {
            try {
                $stmt = $conn->prepare("UPDATE mahasiswa SET nama = :nama, tahun = :tahun, status = :status WHERE id_mahasiswa = :id");
                $stmt->execute([
                    'id' => $id,
                    'nama' => $nama,
                    'tahun' => $tahun ?: null,
                    'status' => $status
                ]);
                $message = 'Student successfully updated!';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'delete_mahasiswa') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $conn->prepare("DELETE FROM mahasiswa WHERE id_mahasiswa = :id");
            $stmt->execute(['id' => $id]);
            $message = 'Student successfully deleted!';
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
$count_stmt = $conn->query("SELECT COUNT(*) FROM mahasiswa");
$total_items = $count_stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

// Get mahasiswa with pagination - use id_mahasiswa as nim for compatibility
$stmt = $conn->prepare("SELECT id_mahasiswa as nim, nama, tahun, status, id_admin FROM mahasiswa ORDER BY nama LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$mahasiswa_list = $stmt->fetchAll();

// Get mahasiswa for edit
$edit_mahasiswa = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT id_mahasiswa as nim, nama, tahun, status, id_admin FROM mahasiswa WHERE id_mahasiswa = :id");
    $stmt->execute(['id' => $edit_id]);
    $edit_mahasiswa = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - CMS InLET</title>
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

        .form-group input[readonly] {
            background-color: #f1f5f9;
            cursor: not-allowed;
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
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            margin-left: 0.5rem;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }

        .btn-cancel:hover {
            background: #4b5563;
        }

        .d-inline {
            display: inline;
        }
    </style>
</head>

<body>
    <?php $active_page = 'mahasiswa';
    include __DIR__ . '/partials/sidebar.php'; ?>

    <main class="content">
        <div class="content-inner">
            <div class="cms-content">
                <h1 class="text-primary mb-4"><i class="ri-graduation-cap-line"></i> Manage Students</h1>

                <?php if ($message): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Add/Edit Form -->
                <div class="form-section <?php echo $edit_mahasiswa ? 'edit-form-section active' : ''; ?>">
                    <h2><?php echo $edit_mahasiswa ? 'Edit Student' : 'Add New Student'; ?></h2>
                    <form method="POST">
                        <input type="hidden" name="action"
                            value="<?php echo $edit_mahasiswa ? 'update_mahasiswa' : 'add_mahasiswa'; ?>">
                        <?php if ($edit_mahasiswa): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_mahasiswa['nim'] ?? ''; ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="nim">NIM (Student ID) *</label>
                            <input type="text" id="nim" name="nim"
                                value="<?php echo htmlspecialchars($edit_mahasiswa['nim'] ?? ''); ?>" 
                                <?php echo $edit_mahasiswa ? 'readonly' : 'required'; ?>
                                placeholder="e.g., 225150200111001">
                            <?php if ($edit_mahasiswa): ?>
                                <small style="color: #6b7280; font-size: 0.875rem;">NIM cannot be changed</small>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="nama">Name *</label>
                            <input type="text" id="nama" name="nama"
                                value="<?php echo htmlspecialchars($edit_mahasiswa['nama'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="tahun">Year</label>
                            <input type="number" id="tahun" name="tahun"
                                value="<?php echo htmlspecialchars($edit_mahasiswa['tahun'] ?? ''); ?>" min="2000"
                                max="2100">
                        </div>

                        <div class="form-group">
                            <label for="status">Status *</label>
                            <select id="status" name="status" required>
                                <option value="regular" <?php echo (($edit_mahasiswa['status'] ?? 'regular') === 'regular') ? 'selected' : ''; ?>>Regular</option>
                                <option value="magang" <?php echo (($edit_mahasiswa['status'] ?? '') === 'magang') ? 'selected' : ''; ?>>Internship</option>
                                <option value="skripsi" <?php echo (($edit_mahasiswa['status'] ?? '') === 'skripsi') ? 'selected' : ''; ?>>Undergraduate Thesis</option>
                            </select>
                        </div>

                        <button type="submit" class="btn-submit">
                            <?php echo $edit_mahasiswa ? 'Update Student' : 'Add Student'; ?>
                        </button>
                        <?php if ($edit_mahasiswa): ?>
                            <a href="mahasiswa.php" class="btn-cancel">Cancel</a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Data List -->
                <div class="data-section">
                    <h2>Student List (<?php echo $total_items; ?>)</h2>

                    <?php if (empty($mahasiswa_list)): ?>
                        <p class="muted-gray">No students registered yet.</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>NIM</th>
                                        <th>Name</th>
                                        <th>Year</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mahasiswa_list as $mhs): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($mhs['nim'] ?? $mhs['id_mahasiswa'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($mhs['nama']); ?></td>
                                            <td><?php echo $mhs['tahun'] ?? '-'; ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $mhs['status']; ?>">
                                                    <?php
                                                    echo ucfirst($mhs['status']);
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="?edit=<?php echo $mhs['nim'] ?? ''; ?>" class="btn-edit">
                                                    <i class="ri-edit-line"></i> Edit
                                                </a>
                                                <form method="POST" class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this student?');">
                                                    <input type="hidden" name="action" value="delete_mahasiswa">
                                                    <input type="hidden" name="id" value="<?php echo $mhs['nim'] ?? ''; ?>">
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
                                Showing <?php echo ($offset + 1); ?> - <?php echo min($offset + $items_per_page, $total_items); ?> of <?php echo $total_items; ?> students
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</body>

</html>