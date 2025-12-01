<?php
require_once '../config/database.php';

$conn = getDBConnection();
$message = '';
$message_type = '';

// Create database views
try {
    $view_dipinjam_sql = "CREATE OR REPLACE VIEW view_alat_dipinjam AS
        SELECT
            pj.id_peminjaman,
            pj.id_alat,
            alat.nama_alat,
            alat.deskripsi,
            pj.nama_peminjam,
            pj.tanggal_pinjam,
            pj.tanggal_kembali,
            pj.keterangan,
            pj.status,
            pj.created_at,
            pj.id_ruang
        FROM peminjaman pj
        LEFT JOIN alat_lab alat ON alat.id_alat_lab = pj.id_alat
        WHERE pj.status = 'dipinjam'";
    $conn->exec($view_dipinjam_sql);
} catch (PDOException $e) {
}

try {
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
            WHERE status = 'dipinjam' AND id_alat IS NOT NULL
            GROUP BY id_alat
        ) pj ON pj.id_alat = alat.id_alat_lab";
    $conn->exec($view_tersedia_sql);
} catch (PDOException $e) {
}

try {
    $view_ruang_dipinjam_sql = "CREATE OR REPLACE VIEW view_ruang_dipinjam AS
        SELECT
            pj.id_peminjaman,
            pj.id_ruang,
            r.nama_ruang,
            r.status as status_ruang,
            pj.nama_peminjam,
            pj.tanggal_pinjam,
            pj.tanggal_kembali,
            pj.waktu_pinjam,
            pj.waktu_kembali,
            pj.keterangan,
            pj.status,
            pj.created_at
        FROM peminjaman pj
        JOIN ruang_lab r ON r.id_ruang_lab = pj.id_ruang
        WHERE pj.status = 'dipinjam'";
    $conn->exec($view_ruang_dipinjam_sql);
} catch (PDOException $e) {
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'pinjam_alat') {
        $id_alat = intval($_POST['id_alat'] ?? 0);
        $nama_peminjam = trim($_POST['nama_peminjam'] ?? '');
        $tanggal_pinjam = $_POST['tanggal_pinjam'] ?? date('Y-m-d');
        $keterangan = trim($_POST['keterangan'] ?? '');

        if (empty($nama_peminjam) || $id_alat <= 0) {
            $message = 'Borrower name and tool are required!';
            ;
            $message_type = 'error';
        } else {
            try {
                // Check stock availability
                $check_stmt = $conn->prepare("SELECT stock,
                    (SELECT COUNT(*) FROM peminjaman WHERE id_alat = :id AND status = 'dipinjam') as dipinjam
                    FROM alat_lab WHERE id_alat_lab = :id");
                $check_stmt->execute(['id' => $id_alat]);
                $alat = $check_stmt->fetch();

                if (!$alat) {
                    $message = 'Tool not found!';
                    ;
                    $message_type = 'error';
                } elseif (($alat['stock'] - $alat['dipinjam']) <= 0) {
                    $message = 'Tool stock is out or all units are currently borrowed!';
                    ;
                    $message_type = 'error';
                } else {
                    // Insert peminjaman
                    $stmt = $conn->prepare("INSERT INTO peminjaman (id_alat, nama_peminjam, tanggal_pinjam, status, keterangan) 
                        VALUES (:id_alat, :nama_peminjam, :tanggal_pinjam, 'dipinjam', :keterangan)");
                    $stmt->execute([
                        'id_alat' => $id_alat,
                        'nama_peminjam' => $nama_peminjam,
                        'tanggal_pinjam' => $tanggal_pinjam,
                        'keterangan' => $keterangan ?: null
                    ]);

                    $message = 'Tool loan successful!';
                    $message_type = 'success';
                }
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'pinjam_ruang') {
        $id_ruang = intval($_POST['id_ruang'] ?? 0);
        $nama_peminjam = trim($_POST['nama_peminjam'] ?? '');
        $tanggal_pinjam = $_POST['tanggal_pinjam'] ?? date('Y-m-d');
        $waktu_pinjam = $_POST['waktu_pinjam'] ?? '';
        $waktu_kembali = $_POST['waktu_kembali'] ?? '';
        $keterangan = trim($_POST['keterangan'] ?? '');

        if (empty($nama_peminjam) || $id_ruang <= 0 || empty($waktu_pinjam) || empty($waktu_kembali)) {
            $message = 'All fields are required!';
            $message_type = 'error';
        } else {
            try {
                // Check if ruangan tersedia
                $check_stmt = $conn->prepare("SELECT status FROM ruang_lab WHERE id_ruang = :id");
                $check_stmt->execute(['id' => $id_ruang]);
                $ruang = $check_stmt->fetch();

                if (!$ruang) {
                    $message = 'Room not found!';
                    $message_type = 'error';
                } else {
                    // Check if ada peminjaman di waktu yang sama
                    $conflict_stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM peminjaman
                        WHERE id_ruang = :id_ruang
                        AND status = 'dipinjam'
                        AND tanggal_pinjam = :tanggal
                        AND :waktu_pinjam < waktu_kembali
                        AND :waktu_kembali > waktu_pinjam");
                    $conflict_stmt->execute([
                        'id_ruang' => $id_ruang,
                        'tanggal' => $tanggal_pinjam,
                        'waktu_pinjam' => $waktu_pinjam,
                        'waktu_kembali' => $waktu_kembali
                    ]);
                    $conflict = $conflict_stmt->fetch();

                    if ($conflict['cnt'] > 0) {
                        $message = 'Ruangan sudah dipinjam pada waktu tersebut!';
                        $message_type = 'error';
                    } else {
                        // Insert peminjaman
                        $stmt = $conn->prepare("INSERT INTO peminjaman 
                            (id_ruang, nama_peminjam, tanggal_pinjam, waktu_pinjam, waktu_kembali, status, keterangan) 
                            VALUES (:id_ruang, :nama_peminjam, :tanggal_pinjam, :waktu_pinjam, :waktu_kembali, 'dipinjam', :keterangan)");
                        $stmt->execute([
                            'id_ruang' => $id_ruang,
                            'nama_peminjam' => $nama_peminjam,
                            'tanggal_pinjam' => $tanggal_pinjam,
                            'waktu_pinjam' => $waktu_pinjam,
                            'waktu_kembali' => $waktu_kembali,
                            'keterangan' => $keterangan ?: null
                        ]);

                        $message = 'Room booking successful!';
                        $message_type = 'success';
                    }
                }
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'kembali') {
        $id_peminjaman = intval($_POST['id_peminjaman'] ?? 0);
        $tanggal_kembali = $_POST['tanggal_kembali'] ?? date('Y-m-d');

        if ($id_peminjaman <= 0) {
            $message = 'Invalid loan ID!';
            $message_type = 'error';
        } else {
            try {
                // Get peminjaman info
                $get_stmt = $conn->prepare("SELECT id_alat, id_ruang FROM peminjaman WHERE id_peminjaman = :id AND status = 'dipinjam'");
                $get_stmt->execute(['id' => $id_peminjaman]);
                $peminjaman = $get_stmt->fetch();

                if (!$peminjaman) {
                    $message = 'Loan record not found or already returned!';
                    $message_type = 'error';
                } else {
                    // Update peminjaman status
                    $stmt = $conn->prepare("UPDATE peminjaman SET tanggal_kembali = :tanggal_kembali, status = 'dikembalikan' WHERE id_peminjaman = :id");
                    $stmt->execute([
                        'id' => $id_peminjaman,
                        'tanggal_kembali' => $tanggal_kembali
                    ]);

                    $message = 'Tool returned successfully!';
                    $message_type = 'success';
                }
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    }
}

// Get alat lab yang tersedia
try {
    $alat_stmt = $conn->query("
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
            WHERE status = 'dipinjam' AND id_alat IS NOT NULL
            GROUP BY id_alat
        ) pj ON pj.id_alat = alat.id_alat_lab
        WHERE (alat.stock - COALESCE(pj.jumlah_dipinjam, 0)) > 0
        ORDER BY alat.nama_alat
    ");
    $alat_list = $alat_stmt->fetchAll();
} catch (PDOException $e) {
    $alat_list = [];
}

// Get ruangan yang tersedia (hanya 1 ruangan)
try {
    $ruang_stmt = $conn->query("SELECT * FROM ruang_lab LIMIT 1");
    $ruang_info = $ruang_stmt->fetch();
} catch (PDOException $e) {
    $ruang_info = null;
}

// Get jadwal peminjaman ruangan hari ini dan besok
try {
    $jadwal_stmt = $conn->query("
        SELECT 
            pj.id_peminjaman,
            pj.nama_peminjam,
            pj.tanggal_pinjam,
            pj.waktu_pinjam,
            pj.waktu_kembali,
            pj.keterangan,
            pj.status
        FROM peminjaman pj
        WHERE pj.id_ruang IS NOT NULL 
        AND pj.status = 'dipinjam'
        AND pj.tanggal_pinjam >= CURDATE()
        ORDER BY pj.tanggal_pinjam, pj.waktu_pinjam
    ");
    $jadwal_ruang = $jadwal_stmt->fetchAll();
} catch (PDOException $e) {
    $jadwal_ruang = [];
}

// Get active peminjaman alat
try {
    $peminjaman_alat_stmt = $conn->query("
        SELECT
            pj.id_peminjaman,
            pj.id_alat,
            alat.nama_alat,
            alat.deskripsi,
            pj.nama_peminjam,
            pj.tanggal_pinjam,
            pj.keterangan,
            pj.status
        FROM peminjaman pj
        JOIN alat_lab alat ON alat.id_alat_lab = pj.id_alat
        WHERE pj.status = 'dipinjam' AND pj.id_alat IS NOT NULL
        ORDER BY pj.tanggal_pinjam DESC
    ");
    $peminjaman_alat_list = $peminjaman_alat_stmt->fetchAll();
} catch (PDOException $e) {
    $peminjaman_alat_list = [];
}

// Get active peminjaman ruang
try {
    $peminjaman_ruang_stmt = $conn->query("
        SELECT
            pj.id_peminjaman,
            pj.id_ruang,
            r.nama_ruang,
            pj.nama_peminjam,
            pj.tanggal_pinjam,
            pj.waktu_pinjam,
            pj.waktu_kembali,
            pj.keterangan,
            pj.status
        FROM peminjaman pj
        JOIN ruang_lab r ON r.id_ruang_lab = pj.id_ruang
        WHERE pj.status = 'dipinjam' AND pj.id_ruang IS NOT NULL
        ORDER BY pj.tanggal_pinjam DESC, pj.waktu_pinjam DESC
    ");
    $peminjaman_ruang_list = $peminjaman_ruang_stmt->fetchAll();
} catch (PDOException $e) {
    $peminjaman_ruang_list = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tool & Room Borrowing - InLET</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style-peminjaman.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <main class="page-main">
        <section class="hero d-flex align-items-center" id="home">
            <div class="container text-center text-white">
                <h1 class="display-4 fw-bold">Lab Borrowing Dashboard</h1>
                <p class="lead mt-3">Easily manage tool and room borrowing from the lab</p>
            </div>
        </section>

        <div class="container my-5">
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show"
                    role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Nav Tabs -->
            <ul class="nav nav-tabs mb-4" id="borrowingTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="alat-tab" data-bs-toggle="tab" data-bs-target="#alat"
                        type="button" role="tab">
                        Borrow Tools
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="ruang-tab" data-bs-toggle="tab" data-bs-target="#ruang" type="button"
                        role="tab">
                        Borrow Room
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="borrowingTabContent">
                <!-- Tab Alat -->
                <div class="tab-pane fade show active" id="alat" role="tabpanel">
                    <div class="section-title">
                        <h2>Tools Currently Borrowed</h2>
                    </div>

                    <div class="row g-4 mb-5">
                        <?php if (empty($peminjaman_alat_list)): ?>
                            <div class="col-12">
                                <div class="alert alert-info">No active tool borrowing at this time.</div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($peminjaman_alat_list as $p): ?>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($p['nama_alat']); ?></h5>
                                            <p class="card-text">
                                                <strong>Borrower:</strong>
                                                <?php echo htmlspecialchars($p['nama_peminjam']); ?><br>
                                                <strong>Borrow Date:</strong>
                                                <?php echo date('d M Y', strtotime($p['tanggal_pinjam'])); ?><br>
                                                <?php if ($p['keterangan']): ?>
                                                    <strong>Notes:</strong> <?php echo htmlspecialchars($p['keterangan']); ?>
                                                <?php endif; ?>
                                            </p>
                                            <form method="POST"
                                                onsubmit="return confirm('Are you sure you want to return this tool?');">
                                                <input type="hidden" name="action" value="kembali">
                                                <input type="hidden" name="id_peminjaman"
                                                    value="<?php echo $p['id_peminjaman']; ?>">
                                                <input type="hidden" name="tanggal_kembali"
                                                    value="<?php echo date('Y-m-d'); ?>">
                                                <button type="submit" class="btn btn-success btn-sm">Return</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="section-title mt-5">
                        <h2>Tool Borrowing Form</h2>
                    </div>

                    <div class="card card-surface p-4 mb-5">
                        <form method="POST" id="formPinjamAlat">
                            <input type="hidden" name="action" value="pinjam_alat">

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Borrow Time *</label>
                                    <input type="text" class="form-control" name="nama_peminjam" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Select Room *</label>
                                    <select class="form-select" name="id_alat" required>
                                        <option value="">Select Lab Tool</option>
                                        <?php foreach ($alat_list as $alat): ?>
                                            <option value="<?php echo $alat['id_alat_lab']; ?>">
                                                <?php echo htmlspecialchars($alat['nama_alat']); ?>
                                                (Tersedia: <?php echo $alat['stok_tersedia']; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Borrow Date *</label>
                                    <input type="date" class="form-control" name="tanggal_pinjam"
                                        value="<?php echo date('Y-m-d'); ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Notes</label>
                                    <input type="text" class="form-control" name="keterangan"
                                        placeholder="Notes (optional)">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Borrow Tool</button>
                        </form>
                    </div>
                </div>

                <!-- Tab Ruang -->
                <div class="tab-pane fade" id="ruang" role="tabpanel">
                    <?php if ($ruang_info): ?>
                        <div class="alert alert-info mb-4">
                            <h5 class="mb-2">📍 <?php echo htmlspecialchars($ruang_info['nama_ruang']); ?></h5>
                            <span
                                class="badge bg-<?php echo $ruang_info['status'] === 'tersedia' ? 'success' : 'warning'; ?>">
                                <?php echo htmlspecialchars($ruang_info['status']); ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <div class="section-title">
                        <h2>Room Borrowing Schedule</h2>
                    </div>

                    <?php if (empty($jadwal_ruang)): ?>
                        <div class="alert alert-success mb-5">
                            <i class="bi bi-check-circle"></i> No room bookings yet. Room is available!
                        </div>
                    <?php else: ?>
                        <div class="row g-3 mb-5">
                            <?php
                            $current_date = '';
                            foreach ($jadwal_ruang as $p):
                                $pinjam_date = date('Y-m-d', strtotime($p['tanggal_pinjam']));
                                if ($pinjam_date !== $current_date):
                                    if ($current_date !== '')
                                        echo '</div></div>';
                                    $current_date = $pinjam_date;
                                    ?>
                                    <div class="col-12">
                                        <h5 class="text-primary mb-3">
                                            <?php echo date('l, d F Y', strtotime($p['tanggal_pinjam'])); ?>
                                        </h5>
                                        <div class="row g-3">
                                        <?php endif; ?>
                                        <div class="col-md-6">
                                            <div class="card border-start border-primary border-3">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="mb-0"><?php echo htmlspecialchars($p['nama_peminjam']); ?>
                                                        </h6>
                                                        <span class="badge bg-primary">
                                                            <?php echo substr($p['waktu_pinjam'], 0, 5); ?> -
                                                            <?php echo substr($p['waktu_kembali'], 0, 5); ?>
                                                        </span>
                                                    </div>
                                                    <?php if ($p['keterangan']): ?>
                                                        <p class="text-muted small mb-2">
                                                            <?php echo htmlspecialchars($p['keterangan']); ?></p>
                                                    <?php endif; ?>
                                                    <form method="POST" class="d-inline"
                                                        onsubmit="return confirm('Are you sure you want to return this room?');">
                                                        <input type="hidden" name="action" value="kembali">
                                                        <input type="hidden" name="id_peminjaman"
                                                            value="<?php echo $p['id_peminjaman']; ?>">
                                                        <input type="hidden" name="tanggal_kembali"
                                                            value="<?php echo date('Y-m-d'); ?>">
                                                        <button type="submit" class="btn btn-success btn-sm">
                                                            ✓ Return
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="section-title mt-5">
                        <h2>Room Borrowing Form</h2>
                    </div>

                    <div class="card card-surface p-4 mb-5">
                        <form method="POST" id="formPinjamRuang">
                            <input type="hidden" name="action" value="pinjam_ruang">
                            <?php if ($ruang_info): ?>
                                <input type="hidden" name="id_ruang" value="<?php echo $ruang_info['id_ruang']; ?>">
                            <?php endif; ?>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">Borrower Name *</label>
                                    <input type="text" class="form-control" name="nama_peminjam" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Borrow Date *</label>
                                    <input type="date" class="form-control" name="tanggal_pinjam"
                                        value="<?php echo date('Y-m-d'); ?>" min="<?php echo date('Y-m-d'); ?>"
                                        required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Borrow Time *</label>
                                    <input type="time" class="form-control" name="waktu_pinjam" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Return Time *</label>
                                    <input type="time" class="form-control" name="waktu_kembali" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Purpose/Notes</label>
                                <textarea class="form-control" name="keterangan" rows="2"
                                    placeholder="Example: Meeting, practicum, etc. (optional)"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                Request Room Borrowing
                            </button>
                        </form>
                    </div>

                    <!-- Info Box -->
                    <div class="alert alert-warning">
                        <strong>Important:</strong> Please ensure to return the room on time to avoid interfering with
                        other room bookings.
                    </div>
                </div>
            </div>

        </div>

    </main>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>