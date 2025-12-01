<?php
require_once '../config/auth.php';
requireAdmin();

$conn = getDBConnection();
$message = '';
$message_type = '';

// Check if id_ruang column exists in peminjaman table
$hasRuangColumn = false;
try {
    $stmt = $conn->prepare("SELECT column_name FROM information_schema.columns WHERE table_name = 'peminjaman' AND column_name = 'id_ruang'");
    $stmt->execute();
    $hasRuangColumn = $stmt->fetch() !== false;
} catch (PDOException $e) {
    // If schema check fails, assume column doesn't exist
    $hasRuangColumn = false;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_peminjaman') {
        $id = $_POST['id'] ?? 0;
        $nama_peminjam = trim($_POST['nama_peminjam'] ?? '');
        $tanggal_pinjam = $_POST['tanggal_pinjam'] ?? '';
        $tanggal_kembali = $_POST['tanggal_kembali'] ?? null;
        $keterangan = trim($_POST['keterangan'] ?? '');
        $status = $_POST['status'] ?? 'dipinjam';

        if (empty($nama_peminjam) || empty($tanggal_pinjam)) {
            $message = 'Borrower name and borrow date are required!';
            $message_type = 'error';
        } else {
            try {
                $stmt = $conn->prepare("UPDATE peminjaman SET nama_peminjam = :nama_peminjam, tanggal_pinjam = :tanggal_pinjam, tanggal_kembali = :tanggal_kembali, keterangan = :keterangan, status = :status WHERE id_peminjaman = :id");
                $stmt->execute([
                    'id' => $id,
                    'nama_peminjam' => $nama_peminjam,
                    'tanggal_pinjam' => $tanggal_pinjam,
                    'tanggal_kembali' => $tanggal_kembali ?: null,
                    'keterangan' => $keterangan ?: null,
                    'status' => $status
                ]);
                $message = 'Borrowing record updated successfully!';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'delete_peminjaman') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $conn->prepare("DELETE FROM peminjaman WHERE id_peminjaman = :id");
            $stmt->execute(['id' => $id]);
            $message = 'Borrowing record deleted successfully!';
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    } elseif ($action === 'return_item') {
        $id = $_POST['id'] ?? 0;
        $tanggal_kembali = $_POST['tanggal_kembali'] ?? date('Y-m-d');
        try {
            $stmt = $conn->prepare("UPDATE peminjaman SET tanggal_kembali = :tanggal_kembali, status = 'dikembalikan' WHERE id_peminjaman = :id");
            $stmt->execute([
                'id' => $id,
                'tanggal_kembali' => $tanggal_kembali
            ]);
            $message = 'Item returned successfully!';
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$type_filter = $_GET['type'] ?? ''; // 'alat' or 'ruang'

// Build query based on filters
$query = "
    SELECT
        pj.id_peminjaman,
        pj.nama_peminjam,
        pj.tanggal_pinjam,
        pj.tanggal_kembali,
        pj.waktu_pinjam,
        pj.waktu_kembali,
        pj.keterangan,
        pj.status,
        pj.created_at,
        CASE
            WHEN pj.id_alat IS NOT NULL THEN 'alat'
            WHEN pj.id_ruang IS NOT NULL THEN 'ruang'
            ELSE 'unknown'
        END as type,
        COALESCE(alat.nama_alat, ruang.nama_ruang) as item_name,
        alat.deskripsi as alat_deskripsi
    FROM peminjaman pj
    LEFT JOIN alat_lab alat ON pj.id_alat = alat.id_alat_lab
    LEFT JOIN ruang_lab ruang ON pj.id_ruang = ruang.id_ruang_lab
    WHERE 1=1
";

$params = [];

if ($status_filter) {
    $query .= " AND pj.status = :status";
    $params['status'] = $status_filter;
}

if ($type_filter) {
    if ($type_filter === 'alat') {
        $query .= " AND pj.id_alat IS NOT NULL";
    } elseif ($type_filter === 'ruang' && $hasRuangColumn) {
        $query .= " AND pj.id_ruang IS NOT NULL";
    }
}

$query .= " ORDER BY pj.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$peminjaman_list = $stmt->fetchAll();

// Get edit data
$edit_peminjaman = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_query = "
        SELECT
            pj.*,
            'alat' as type,
            alat.nama_alat as item_name
        FROM peminjaman pj
        LEFT JOIN alat_lab alat ON pj.id_alat = alat.id_alat_lab
        WHERE pj.id_peminjaman = :id
    ";

    if ($hasRuangColumn) {
        $edit_query = "
            SELECT
                pj.*,
                CASE
                    WHEN pj.id_alat IS NOT NULL THEN 'alat'
                    WHEN pj.id_ruang IS NOT NULL THEN 'ruang'
                    ELSE 'unknown'
                END as type,
                COALESCE(alat.nama_alat, ruang.nama_ruang) as item_name
            FROM peminjaman pj
            LEFT JOIN alat_lab alat ON pj.id_alat = alat.id_alat_lab
            LEFT JOIN ruang_lab ruang ON pj.id_ruang = ruang.id_ruang_lab
            WHERE pj.id_peminjaman = :id
        ";
    }

    $stmt = $conn->prepare($edit_query);
    $stmt->execute(['id' => $edit_id]);
    $edit_peminjaman = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tool Loan Management - CMS InLET</title>
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

        .filters {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
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
        }

        .status-dipinjam {
            background: #fef3c7;
            color: #92400e;
        }

        .status-dikembalikan {
            background: #d1fae5;
            color: #065f46;
        }

        .type-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .type-alat {
            background: #dbeafe;
            color: #1e40af;
        }

        .type-ruang {
            background: #fce7f3;
            color: #be185d;
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

        .btn-return {
            background: #10b981;
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

        .btn-return:hover {
            background: #059669;
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

        .filter-btn {
            background: var(--primary);
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            margin-right: 0.5rem;
        }

        .filter-btn:hover {
            background: var(--primary-dark);
        }

        .clear-filter {
            background: #6b7280;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
        }

        .clear-filter:hover {
            background: #4b5563;
        }
    </style>
</head>

<body>
    <?php $active_page = 'peminjaman';
    include __DIR__ . '/partials/sidebar.php'; ?>

    <main class="content">
        <div class="content-inner">
            <div class="cms-content">
                <h1 class="text-primary mb-4"><i class="ri-exchange-line"></i> Borrowing Management</h1>

                <?php if ($message): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="filters">
                    <h3 class="text-primary mb-3">Filters</h3>
                    <form method="GET" class="d-inline">
                        <select name="status" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="dipinjam" <?php echo $status_filter === 'dipinjam' ? 'selected' : ''; ?>>
                                Borrowed</option>
                            <option value="dikembalikan" <?php echo $status_filter === 'dikembalikan' ? 'selected' : ''; ?>>Returned</option>
                        </select>
                        <select name="type" onchange="this.form.submit()">
                            <option value="">All Types</option>
                            <option value="alat" <?php echo $type_filter === 'alat' ? 'selected' : ''; ?>>Tools</option>
                            <option value="ruang" <?php echo $type_filter === 'ruang' ? 'selected' : ''; ?>>Rooms</option>
                        </select>
                        <?php if ($status_filter || $type_filter): ?>
                            <a href="peminjaman.php" class="clear-filter">Clear Filters</a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Edit Form -->
                <div class="form-section <?php echo $edit_peminjaman ? 'edit-form-section active' : ''; ?>">
                    <h2><?php echo $edit_peminjaman ? 'Edit Borrowing Record' : 'Add New Borrowing Record'; ?></h2>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_peminjaman">
                        <?php if ($edit_peminjaman): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_peminjaman['id_peminjaman']; ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="nama_peminjam">Borrower Name *</label>
                            <input type="text" id="nama_peminjam" name="nama_peminjam"
                                value="<?php echo htmlspecialchars($edit_peminjaman['nama_peminjam'] ?? ''); ?>"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="tanggal_pinjam">Borrow Date *</label>
                            <input type="date" id="tanggal_pinjam" name="tanggal_pinjam"
                                value="<?php echo htmlspecialchars($edit_peminjaman['tanggal_pinjam'] ?? ''); ?>"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="tanggal_kembali">Return Date</label>
                            <input type="date" id="tanggal_kembali" name="tanggal_kembali"
                                value="<?php echo htmlspecialchars($edit_peminjaman['tanggal_kembali'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="dipinjam" <?php echo ($edit_peminjaman['status'] ?? 'dipinjam') === 'dipinjam' ? 'selected' : ''; ?>>Borrowed</option>
                                <option value="dikembalikan" <?php echo ($edit_peminjaman['status'] ?? '') === 'dikembalikan' ? 'selected' : ''; ?>>Returned</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="keterangan">Notes</label>
                            <textarea id="keterangan" name="keterangan"
                                placeholder="Additional notes"><?php echo htmlspecialchars($edit_peminjaman['keterangan'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="btn-submit">
                            <?php echo $edit_peminjaman ? 'Update Record' : 'Add Record'; ?>
                        </button>
                        <?php if ($edit_peminjaman): ?>
                            <a href="peminjaman.php" class="btn-cancel">Cancel</a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Data List -->
                <div class="data-section">
                    <h2>Borrowing Records (<?php echo count($peminjaman_list); ?>)</h2>

                    <?php if (empty($peminjaman_list)): ?>
                        <p class="muted-gray">No borrowing records found.</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Type</th>
                                        <th>Item</th>
                                        <th>Borrower</th>
                                        <th>Borrow Date</th>
                                        <th>Return Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($peminjaman_list as $peminjaman): ?>
                                        <tr>
                                            <td><?php echo $peminjaman['id_peminjaman']; ?></td>
                                            <td>
                                                <span class="type-badge type-<?php echo $peminjaman['type']; ?>">
                                                    <?php echo ucfirst($peminjaman['type']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($peminjaman['item_name'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($peminjaman['nama_peminjam']); ?></td>
                                            <td><?php echo date('d M Y', strtotime($peminjaman['tanggal_pinjam'])); ?>
                                                <?php if ($peminjaman['waktu_pinjam']): ?>
                                                    <br><small><?php echo substr($peminjaman['waktu_pinjam'], 0, 5); ?> -
                                                        <?php echo substr($peminjaman['waktu_kembali'], 0, 5); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $peminjaman['tanggal_kembali'] ? date('d M Y', strtotime($peminjaman['tanggal_kembali'])) : '-'; ?>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?php echo $peminjaman['status']; ?>">
                                                    <?php echo ucfirst($peminjaman['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="?edit=<?php echo $peminjaman['id_peminjaman']; ?>" class="btn-edit">
                                                    <i class="ri-edit-line"></i> Edit
                                                </a>
                                                <?php if ($peminjaman['status'] === 'dipinjam'): ?>
                                                    <form method="POST" class="d-inline"
                                                        onsubmit="return confirm('Mark this item as returned?');">
                                                        <input type="hidden" name="action" value="return_item">
                                                        <input type="hidden" name="id"
                                                            value="<?php echo $peminjaman['id_peminjaman']; ?>">
                                                        <input type="hidden" name="tanggal_kembali"
                                                            value="<?php echo date('Y-m-d'); ?>">
                                                        <button type="submit" class="btn-return">
                                                            <i class="ri-check-line"></i> Return
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <form method="POST" class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this record?');">
                                                    <input type="hidden" name="action" value="delete_peminjaman">
                                                    <input type="hidden" name="id"
                                                        value="<?php echo $peminjaman['id_peminjaman']; ?>">
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