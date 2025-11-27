<?php
require_once '../config/database.php';

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
            ON alat.id_alat = pj.id_alat
        WHERE pj.status = 'dipinjam'";
    $conn->exec($view_dipinjam_sql);
} catch(PDOException $e) {
    // View mungkin sudah ada atau ada error, ignore
}

try {
    // View untuk melihat alat yang tersedia dengan informasi stok
    $view_tersedia_sql = "CREATE OR REPLACE VIEW view_alat_tersedia AS
        SELECT 
            alat.id_alat,
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
        ) pj ON pj.id_alat = alat.id_alat";
    $conn->exec($view_tersedia_sql);
} catch(PDOException $e) {
    // View mungkin sudah ada atau ada error, ignore
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'pinjam') {
        $id_alat = intval($_POST['id_alat'] ?? 0);
        $nama_peminjam = trim($_POST['nama_peminjam'] ?? '');
        $tanggal_pinjam = $_POST['tanggal_pinjam'] ?? date('Y-m-d');
        $keterangan = trim($_POST['keterangan'] ?? '');
        
        if (empty($nama_peminjam) || $id_alat <= 0) {
            $message = 'Nama peminjam dan alat harus diisi!';
            $message_type = 'error';
        } else {
            try {
                // Check stock availability menggunakan view atau fallback ke query langsung
                try {
                    $check_stmt = $conn->prepare("SELECT stok_tersedia FROM view_alat_tersedia WHERE id_alat = :id");
                    $check_stmt->execute(['id' => $id_alat]);
                    $alat = $check_stmt->fetch();
                } catch(PDOException $e) {
                    // Fallback jika view error
                    $check_stmt = $conn->prepare("SELECT a.stock - COALESCE((SELECT COUNT(*) FROM peminjaman WHERE id_alat = a.id_alat AND status = 'dipinjam'), 0) as stok_tersedia
                        FROM alat_lab a WHERE a.id_alat = :id");
                    $check_stmt->execute(['id' => $id_alat]);
                    $alat = $check_stmt->fetch();
                }
                
                if (!$alat) {
                    $message = 'Alat tidak ditemukan!';
                    $message_type = 'error';
                } elseif ($alat['stok_tersedia'] <= 0) {
                    $message = 'Stock alat habis atau sedang dipinjam!';
                    $message_type = 'error';
                } else {
                    // Insert peminjaman
                    $stmt = $conn->prepare("INSERT INTO peminjaman (id_alat, nama_peminjam, tanggal_pinjam, status, keterangan) VALUES (:id_alat, :nama_peminjam, :tanggal_pinjam, 'dipinjam', :keterangan)");
                    $stmt->execute([
                        'id_alat' => $id_alat,
                        'nama_peminjam' => $nama_peminjam,
                        'tanggal_pinjam' => $tanggal_pinjam,
                        'keterangan' => $keterangan ?: null
                    ]);
                    
                    // Update stock (decrease)
                    $update_stmt = $conn->prepare("UPDATE alat_lab SET stock = stock - 1, updated_at = CURRENT_TIMESTAMP WHERE id_alat = :id");
                    $update_stmt->execute(['id' => $id_alat]);
                    
                    $message = 'Peminjaman berhasil!';
                    $message_type = 'success';
                }
            } catch(PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    } elseif ($action === 'kembali') {
        $id_peminjaman = intval($_POST['id_peminjaman'] ?? 0);
        $tanggal_kembali = $_POST['tanggal_kembali'] ?? date('Y-m-d');
        
        if ($id_peminjaman <= 0) {
            $message = 'ID peminjaman tidak valid!';
            $message_type = 'error';
        } else {
            try {
                // Get peminjaman info
                $get_stmt = $conn->prepare("SELECT id_alat FROM peminjaman WHERE id_peminjaman = :id AND status = 'dipinjam'");
                $get_stmt->execute(['id' => $id_peminjaman]);
                $peminjaman = $get_stmt->fetch();
                
                if (!$peminjaman) {
                    $message = 'Peminjaman tidak ditemukan atau sudah dikembalikan!';
                    $message_type = 'error';
                } else {
                    // Update peminjaman status
                    $stmt = $conn->prepare("UPDATE peminjaman SET tanggal_kembali = :tanggal_kembali, status = 'dikembalikan' WHERE id_peminjaman = :id");
                    $stmt->execute([
                        'id' => $id_peminjaman,
                        'tanggal_kembali' => $tanggal_kembali
                    ]);
                    
                    // Update stock (increase)
                    $update_stmt = $conn->prepare("UPDATE alat_lab SET stock = stock + 1, updated_at = CURRENT_TIMESTAMP WHERE id_alat = :id");
                    $update_stmt->execute(['id' => $peminjaman['id_alat']]);
                    
                    $message = 'Pengembalian berhasil!';
                    $message_type = 'success';
                }
            } catch(PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    }
}

// Get alat lab yang tersedia menggunakan view view_alat_tersedia
try {
    $alat_stmt = $conn->query("SELECT * FROM view_alat_tersedia WHERE stok_tersedia > 0 ORDER BY nama_alat");
    $alat_list = $alat_stmt->fetchAll();
} catch(PDOException $e) {
    // Fallback jika view error - query langsung dari tabel
    $alat_stmt = $conn->query("SELECT a.*, 
        (a.stock - COALESCE((SELECT COUNT(*) FROM peminjaman WHERE id_alat = a.id_alat AND status = 'dipinjam'), 0)) as stok_tersedia,
        COALESCE((SELECT COUNT(*) FROM peminjaman WHERE id_alat = a.id_alat AND status = 'dipinjam'), 0) as jumlah_dipinjam
        FROM alat_lab a 
        WHERE (a.stock - COALESCE((SELECT COUNT(*) FROM peminjaman WHERE id_alat = a.id_alat AND status = 'dipinjam'), 0)) > 0
        ORDER BY a.nama_alat");
    $alat_list = $alat_stmt->fetchAll();
}

// Get active peminjaman menggunakan view view_alat_dipinjam
try {
    $peminjaman_stmt = $conn->query("SELECT * FROM view_alat_dipinjam ORDER BY tanggal_pinjam DESC");
    $peminjaman_list = $peminjaman_stmt->fetchAll();
} catch(PDOException $e) {
    // Fallback jika view error - query langsung dari tabel
    $peminjaman_stmt = $conn->query("SELECT pj.*, alat.nama_alat, alat.deskripsi 
        FROM peminjaman pj
        JOIN alat_lab alat ON alat.id_alat = pj.id_alat
        WHERE pj.status = 'dipinjam'
        ORDER BY pj.tanggal_pinjam DESC");
    $peminjaman_list = $peminjaman_stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman Alat Lab - InLET</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style-peminjaman.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <main class="flex-grow-1" style="flex: 1 0 auto; min-height: 0; padding-top: 80px;">
        <section class="hero d-flex align-items-center" id="home">
            <div class="container text-center text-white">
                <h1 class="display-4 fw-bold">Lab Borrowing Dashboard</h1>
                <p class="lead mt-3">Easily manage room and equipment borrowing</p>
            </div>
        </section>

        <div class="container my-5">
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="section-title">
                <h2>Peminjaman Aktif</h2>
                <p>Daftar alat yang sedang dipinjam</p>
            </div>

            <div class="row g-4 mb-5">
                <?php if (empty($peminjaman_list)): ?>
                    <div class="col-12">
                        <div class="alert alert-info">Tidak ada peminjaman aktif saat ini.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($peminjaman_list as $p): ?>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($p['nama_alat']); ?></h5>
                                    <p class="card-text">
                                        <strong>Peminjam:</strong> <?php echo htmlspecialchars($p['nama_peminjam']); ?><br>
                                        <strong>Tanggal Pinjam:</strong> <?php echo date('d M Y', strtotime($p['tanggal_pinjam'])); ?><br>
                                        <?php if ($p['keterangan']): ?>
                                            <strong>Keterangan:</strong> <?php echo htmlspecialchars($p['keterangan']); ?>
                                        <?php endif; ?>
                                    </p>
                                    <form method="POST" onsubmit="return confirm('Yakin kembalikan alat ini?');">
                                        <input type="hidden" name="action" value="kembali">
                                        <input type="hidden" name="id_peminjaman" value="<?php echo $p['id_peminjaman']; ?>">
                                        <input type="hidden" name="tanggal_kembali" value="<?php echo date('Y-m-d'); ?>">
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="ri-check-line"></i> Kembalikan
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="section-title mt-5">
                <h2>Form Peminjaman</h2>
                <p>Isi form untuk meminjam alat lab</p>
            </div>

            <div class="card card-surface p-4 mb-5">
                <form method="POST" id="formPinjam">
                    <input type="hidden" name="action" value="pinjam">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Peminjam *</label>
                            <input type="text" class="form-control" name="nama_peminjam" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Alat Lab *</label>
                            <select class="form-select" name="id_alat" required>
                                <option value="">Pilih Alat Lab</option>
                                <?php foreach ($alat_list as $alat): ?>
                                    <option value="<?php echo $alat['id_alat']; ?>" 
                                            data-stock="<?php echo $alat['stok_tersedia']; ?>">
                                        <?php echo htmlspecialchars($alat['nama_alat']); ?> 
                                        (Tersedia: <?php echo $alat['stok_tersedia']; ?>, Total: <?php echo $alat['stock']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Pinjam *</label>
                            <input type="date" class="form-control" name="tanggal_pinjam" 
                                   value="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Keterangan</label>
                            <input type="text" class="form-control" name="keterangan" 
                                   placeholder="Keterangan peminjaman (opsional)">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Ajukan Peminjaman</button>
                </form>
            </div>

        </div>

    </main>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>