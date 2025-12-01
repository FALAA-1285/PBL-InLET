<?php
require_once '../config/auth.php';
requireAdmin();

$conn = getDBConnection();
$message = '';
$message_type = '';

// Pastikan views ada - buat jika belum ada
try {
    // View untuk melihat alat yang sedang dipinjam
    $view_dipinjam_sql = "CREATE OR REPLACE VIEW view_alat_dipinjam AS
        SELECT
            pj.id_peminjaman,
            pj.id_alat,
            alat.id_alat_lab,
            alat.nama_alat,
            alat.deskripsi,
            pj.nama_peminjam,
            pj.tanggal_pinjam,
            pj.tanggal_kembali,
            pj.keterangan,
            pj.status,
            pj.created_at
        FROM peminjaman pj
        JOIN alat_lab alat
            ON alat.id_alat_lab = pj.id_alat
        WHERE pj.status = 'dipinjam'";
    $conn->exec($view_dipinjam_sql);
} catch (PDOException $e) {
    // View mungkin sudah ada atau ada error, ignore
}

try {
    // View untuk melihat alat yang tersedia dengan informasi stok
    $view_tersedia_sql = "CREATE OR REPLACE VIEW view_alat_tersedia AS
        SELECT
            alat.id_alat_lab,
            alat.nama_alat,
            alat.deskripsi,
            alat.stock,
            COALESCE(pj.jumlah_dipinjam, 0) AS jumlah_dipinjam,
            (alat.stock - COALESCE(pj.jumlah_dipinjam, 0)) AS stok_tersedia
        FROM alat_lab alat
        LEFT JOIN (
            SELECT id_alat, COUNT(*) AS jumlah_dipinjam
            FROM peminjaman
            WHERE status = 'dipinjam'
            GROUP BY id_alat
        ) pj ON pj.id_alat = alat.id_alat_lab";
    $conn->exec($view_tersedia_sql);
} catch (PDOException $e) {
    // View mungkin sudah ada atau ada error, ignore
}

// Admin ID no longer needed for alat_lab operations

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_alat') {
        $nama_alat = trim($_POST['nama_alat'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $stock = intval($_POST['stock'] ?? 0);

        if (empty($nama_alat)) {
            $message = 'Nama alat harus diisi!';
            $message_type = 'error';
        } else {
            try {
                $stmt = $conn->prepare("INSERT INTO alat_lab (nama_alat, deskripsi, stock) VALUES (:nama_alat, :deskripsi, :stock)");
                $stmt->execute([
                    'nama_alat' => $nama_alat,
                    'deskripsi' => $deskripsi ?: null,
                    'stock' => $stock
                ]);
                $message = 'Lab tool successfully added!';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'update_alat') {
        $id = $_POST['id'] ?? 0;
        $nama_alat = trim($_POST['nama_alat'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $stock = intval($_POST['stock'] ?? 0);

        if (empty($nama_alat)) {
            $message = 'Nama alat harus diisi!';
            $message_type = 'error';
        } else {
            try {
                $stmt = $conn->prepare("UPDATE alat_lab SET nama_alat = :nama_alat, deskripsi = :deskripsi, stock = :stock, updated_at = CURRENT_TIMESTAMP WHERE id_alat_lab = :id");
                $stmt->execute([
                    'id' => $id,
                    'nama_alat' => $nama_alat,
                    'deskripsi' => $deskripsi ?: null,
                    'stock' => $stock
                ]);
                $message = 'Lab tool successfully updated!';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'delete_alat') {
        $id = $_POST['id'] ?? 0;
        try {
            // Check if alat is being borrowed using direct query on peminjaman table
            $check_stmt = $conn->prepare("SELECT COUNT(*) FROM peminjaman WHERE id_alat = :id AND status = 'dipinjam'");
            $check_stmt->execute(['id' => $id]);
            $borrowed_count = $check_stmt->fetchColumn();

            if ($borrowed_count > 0) {
                $message = 'Tool cannot be deleted because it is currently borrowed!';
                $message_type = 'error';
            } else {
                $stmt = $conn->prepare("DELETE FROM alat_lab WHERE id_alat_lab = :id");
                $stmt->execute(['id' => $id]);
                $message = 'Lab tool successfully deleted!';
                $message_type = 'success';
            }
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Get all alat lab dengan informasi stok tersedia
$stmt = $conn->query("SELECT
    alat.id_alat_lab,
    alat.nama_alat,
    alat.deskripsi,
    alat.stock,
    COALESCE(pj.jumlah_dipinjam, 0) AS jumlah_dipinjam,
    (alat.stock - COALESCE(pj.jumlah_dipinjam, 0)) AS stok_tersedia
FROM alat_lab alat
LEFT JOIN (
    SELECT id_alat, COUNT(*) AS jumlah_dipinjam
    FROM peminjaman
    WHERE status = 'dipinjam'
    GROUP BY id_alat
) pj ON pj.id_alat = alat.id_alat_lab
ORDER BY nama_alat");
$alat_list = $stmt->fetchAll();

// Get alat for edit - perlu ambil dari tabel karena view tidak memiliki semua field untuk edit
$edit_alat = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM alat_lab WHERE id_alat_lab = :id");
    $stmt->execute(['id' => $edit_id]);
    $edit_alat = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Tools Management - CMS InLET</title>
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

        .stock-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .stock-available {
            background: #d1fae5;
            color: #065f46;
        }

        .stock-low {
            background: #fef3c7;
            color: #92400e;
        }

        .stock-empty {
            background: #fee2e2;
            color: #991b1b;
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
    <?php $active_page = 'alat_lab';
    include __DIR__ . '/partials/sidebar.php'; ?>

    <main class="content">
        <div class="content-inner">
            <div class="cms-content">
                <h1 class="text-primary mb-4"><i class="ri-tools-line"></i> Manage Lab Tools</h1>

                <?php if ($message): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Add/Edit Form -->
                <div class="form-section <?php echo $edit_alat ? 'edit-form-section active' : ''; ?>">
                    <h2><?php echo $edit_alat ? 'Edit Lab Tool' : 'Add New Lab Tool'; ?></h2>
                    <form method="POST">
                        <input type="hidden" name="action"
                            value="<?php echo $edit_alat ? 'update_alat' : 'add_alat'; ?>">
                        <?php if ($edit_alat): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_alat['id_alat_lab']; ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="nama_alat">Tool Name *</label>
                            <input type="text" id="nama_alat" name="nama_alat"
                                value="<?php echo htmlspecialchars($edit_alat['nama_alat'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="deskripsi">Description</label>
                            <textarea id="deskripsi" name="deskripsi"
                                placeholder="Lab tool description"><?php echo htmlspecialchars($edit_alat['deskripsi'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="stock">Stock *</label>
                            <input type="number" id="stock" name="stock"
                                value="<?php echo htmlspecialchars($edit_alat['stock'] ?? 0); ?>" min="0" required>
                        </div>

                        <button type="submit" class="btn-submit">
                            <?php echo $edit_alat ? 'Update Lab Tool' : 'Add Lab Tool'; ?>
                        </button>
                        <?php if ($edit_alat): ?>
                            <a href="alat_lab.php" class="btn-cancel">Cancel</a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Data List -->
                <div class="data-section">
                    <h2>Lab Tools List (<?php echo count($alat_list); ?>)</h2>

                    <?php if (empty($alat_list)): ?>
                        <p class="muted-gray">No lab tools registered yet.</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tool Name</th>
                                        <th>Description</th>
                                        <th>Total Stock</th>
                                        <th>Available Stock</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($alat_list as $alat): ?>
                                        <tr>
                                            <td><?php echo $alat['id_alat_lab']; ?></td>
                                            <td><?php echo htmlspecialchars($alat['nama_alat']); ?></td>
                                            <td><?php echo htmlspecialchars($alat['deskripsi'] ?? '-'); ?></td>
                                            <td><?php echo $alat['stock']; ?> unit</td>
                                            <td>
                                                <span class="stock-badge <?php
                                                $stok_tersedia = $alat['stok_tersedia'] ?? 0;
                                                echo $stok_tersedia > 5 ? 'stock-available' : ($stok_tersedia > 0 ? 'stock-low' : 'stock-empty');
                                                ?>">
                                                    <?php echo $stok_tersedia; ?> units available
                                                    <?php if (isset($alat['jumlah_dipinjam']) && $alat['jumlah_dipinjam'] > 0): ?>
                                                        <br><small class="small text-muted">(<?php echo $alat['jumlah_dipinjam']; ?>
                                                            borrowed)</small>
                                                    <?php endif; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="?edit=<?php echo $alat['id_alat_lab']; ?>" class="btn-edit">
                                                    <i class="ri-edit-line"></i> Edit
                                                </a>
                                                <form method="POST" class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this tool?');">
                                                    <input type="hidden" name="action" value="delete_alat">
                                                    <input type="hidden" name="id" value="<?php echo $alat['id_alat_lab']; ?>">
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