<?php
require_once '../config/database.php';
require_once '../config/settings.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn = getDBConnection();

// Get page title and subtitle
$page_info = getPageTitle('tool_loans');
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
        WHERE pj.status = 'dipinjam'
        AND (pj.keterangan IS NOT NULL AND pj.keterangan LIKE '%[APPROVED]%')";
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
        $tanggal_kembali = $_POST['tanggal_kembali'] ?? '';
        $jumlah = intval($_POST['jumlah'] ?? 1);
        $keterangan = trim($_POST['keterangan'] ?? '');

        if (empty($nama_peminjam) || $id_alat <= 0) {
            $message = 'Borrower name and tool are required!';
            $message_type = 'error';
        } elseif (empty($tanggal_kembali)) {
            $message = 'Return date is required!';
            $message_type = 'error';
        } elseif ($jumlah <= 0) {
            $message = 'Quantity must be greater than 0!';
            $message_type = 'error';
        } elseif ($tanggal_pinjam < date('Y-m-d')) {
            $message = 'Borrow date cannot be in the past!';
            $message_type = 'error';
        } elseif (empty($tanggal_kembali)) {
            $message = 'Return date is required!';
            $message_type = 'error';
        } elseif ($tanggal_kembali < date('Y-m-d')) {
            $message = 'Return date cannot be in the past!';
            $message_type = 'error';
        } elseif ($tanggal_kembali < $tanggal_pinjam) {
            $message = 'Return date must be on or after borrow date!';
            $message_type = 'error';
        } else {
            try {
                // Menggunakan view_alat_tersedia untuk cek stok
                $check_stmt = $conn->prepare("SELECT * FROM view_alat_tersedia WHERE id_alat_lab = :id");
                $check_stmt->execute(['id' => $id_alat]);
                $alat = $check_stmt->fetch();

                if (!$alat) {
                    $message = 'Tool not found!';
                    $message_type = 'error';
                } elseif ($alat['stok_tersedia'] < $jumlah) {
                    $message = 'Insufficient stock! Available: ' . $alat['stok_tersedia'] . ' unit(s), requested: ' . $jumlah . ' unit(s)';
                    $message_type = 'error';
                } else {
                    // Fix sequence if it's out of sync
                    try {
                        $max_id_stmt = $conn->query("SELECT COALESCE(MAX(id_peminjaman), 0) as max_id FROM peminjaman");
                        $max_id = $max_id_stmt->fetch()['max_id'];
                        $conn->exec("SELECT setval('peminjaman_id_peminjaman_seq', " . ($max_id + 1) . ", false)");
                    } catch (PDOException $seq_e) {
                        // Sequence might not exist or error, continue anyway
                    }
                    
                    // Insert multiple records untuk setiap unit yang dipinjam
                    // id_alat is NOT NULL, so we must provide it
                    $stmt = $conn->prepare("INSERT INTO peminjaman (id_alat, nama_peminjam, tanggal_pinjam, tanggal_kembali, status, keterangan) 
                        VALUES (:id_alat, :nama_peminjam, :tanggal_pinjam, :tanggal_kembali, 'dipinjam', :keterangan)");
                
                    try {
                        for ($i = 0; $i < $jumlah; $i++) {
                            $stmt->execute([
                                'id_alat' => $id_alat,
                                'nama_peminjam' => $nama_peminjam,
                                'tanggal_pinjam' => $tanggal_pinjam,
                                'tanggal_kembali' => $tanggal_kembali,
                                'keterangan' => $keterangan ?: null
                            ]);
                            
                            // Fix sequence after each insert to prevent duplicate key errors
                            if ($i < $jumlah - 1) {
                                try {
                                    $max_id_stmt = $conn->query("SELECT COALESCE(MAX(id_peminjaman), 0) as max_id FROM peminjaman");
                                    $max_id = $max_id_stmt->fetch()['max_id'];
                                    $conn->exec("SELECT setval('peminjaman_id_peminjaman_seq', " . ($max_id + 1) . ", false)");
                                } catch (PDOException $seq_e) {
                                    // Continue anyway
                                }
                            }
                        }

                        $message = 'Your request borrowing has been sent';
                        $message_type = 'success';
                    } catch (PDOException $insert_e) {
                        // Check if it's a duplicate key error
                        if (strpos($insert_e->getMessage(), 'duplicate key') !== false || strpos($insert_e->getMessage(), '23505') !== false) {
                            // Fix sequence and retry
                            try {
                                $max_id_stmt = $conn->query("SELECT COALESCE(MAX(id_peminjaman), 0) as max_id FROM peminjaman");
                                $max_id = $max_id_stmt->fetch()['max_id'];
                                $conn->exec("SELECT setval('peminjaman_id_peminjaman_seq', " . ($max_id + 1) . ", false)");
                                
                                // Retry remaining inserts
                                for ($i = 0; $i < $jumlah; $i++) {
                                    $stmt->execute([
                                        'id_alat' => $id_alat,
                                        'nama_peminjam' => $nama_peminjam,
                                        'tanggal_pinjam' => $tanggal_pinjam,
                                        'tanggal_kembali' => $tanggal_kembali,
                                        'keterangan' => $keterangan ?: null
                                    ]);
                                    
                                    if ($i < $jumlah - 1) {
                                        $max_id_stmt = $conn->query("SELECT COALESCE(MAX(id_peminjaman), 0) as max_id FROM peminjaman");
                                        $max_id = $max_id_stmt->fetch()['max_id'];
                                        $conn->exec("SELECT setval('peminjaman_id_peminjaman_seq', " . ($max_id + 1) . ", false)");
                                    }
                                }
                                
                                $message = 'Your request borrowing has been sent';
                                $message_type = 'success';
                            } catch (PDOException $retry_e) {
                                $message = 'Error: ' . $retry_e->getMessage();
                                $message_type = 'error';
                            }
                        } else {
                            $message = 'Error: ' . $insert_e->getMessage();
                            $message_type = 'error';
                        }
                    }
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
        } elseif ($tanggal_pinjam <= date('Y-m-d')) {
            $message = 'Room cannot be booked for today or past dates!';
            $message_type = 'error';
        } else {
            try {
                // Check if ruangan tersedia
                $check_stmt = $conn->prepare("SELECT status FROM ruang_lab WHERE id_ruang_lab = :id");
                $check_stmt->execute(['id' => $id_ruang]);
                $ruang = $check_stmt->fetch();

                if (!$ruang) {
                    $message = 'Room not found!';
                    $message_type = 'error';
                } elseif (strtolower($ruang['status']) === 'maintenance') {
                    $message = 'Lab room has maintenance';
                    $message_type = 'error';
                } elseif (strtolower($ruang['status']) !== 'tersedia') {
                    $message = 'Room not available';
                    $message_type = 'error';
                } else {
                    // Check if ada peminjaman di waktu yang sama (only check approved ones)
                    $conflict_stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM peminjaman
                        WHERE id_ruang = :id_ruang
                        AND status = 'dipinjam'
                        AND (keterangan IS NOT NULL AND keterangan LIKE '%[APPROVED]%')
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
                        $message = 'Room already booked at that time!';
                        $message_type = 'error';
                    } else {
                        // Ensure dummy alat with id = 0 exists for room borrowing
                        // This is needed because id_alat is NOT NULL with foreign key constraint
                        try {
                            $check_dummy = $conn->prepare("SELECT 1 FROM alat_lab WHERE id_alat_lab = 0");
                            $check_dummy->execute();
                            if (!$check_dummy->fetch()) {
                                // Create dummy alat if it doesn't exist
                                // Use explicit id = 0, bypassing sequence
                                $dummy_stmt = $conn->prepare("INSERT INTO alat_lab (id_alat_lab, nama_alat, deskripsi, stock) 
                                    VALUES (0, 'Room Placeholder', 'Dummy alat for room borrowing', 0)");
                                $dummy_stmt->execute();
                            }
                        } catch (PDOException $dummy_e) {
                            // If error (e.g., already exists), try to continue anyway
                            // Check if it's a duplicate key error, which means it already exists
                            if (strpos($dummy_e->getMessage(), 'duplicate key') === false && 
                                strpos($dummy_e->getMessage(), '23505') === false) {
                                // If it's not a duplicate key error, log it but continue
                            }
                        }
                        
                        // Fix sequence if it's out of sync
                        try {
                            $max_id_stmt = $conn->query("SELECT COALESCE(MAX(id_peminjaman), 0) as max_id FROM peminjaman");
                            $max_id = $max_id_stmt->fetch()['max_id'];
                            $conn->exec("SELECT setval('peminjaman_id_peminjaman_seq', " . ($max_id + 1) . ", false)");
                        } catch (PDOException $seq_e) {
                            // Sequence might not exist or error, continue anyway
                        }
                        
                        // Insert peminjaman
                        // id_alat is NOT NULL, so we use 0 as placeholder for room borrowing
                        $stmt = $conn->prepare("INSERT INTO peminjaman 
                            (id_alat, id_ruang, nama_peminjam, tanggal_pinjam, waktu_pinjam, waktu_kembali, status, keterangan) 
                            VALUES (0, :id_ruang, :nama_peminjam, :tanggal_pinjam, :waktu_pinjam, :waktu_kembali, 'dipinjam', :keterangan)");
                        $stmt->execute([
                            'id_ruang' => $id_ruang,
                            'nama_peminjam' => $nama_peminjam,
                            'tanggal_pinjam' => $tanggal_pinjam,
                            'waktu_pinjam' => $waktu_pinjam,
                            'waktu_kembali' => $waktu_kembali,
                            'keterangan' => $keterangan ?: null
                        ]);

                        $message = 'Your request borrowing has been sent';
                        $message_type = 'success';
                    }
                }
            } catch (PDOException $e) {
                // Check error type
                $error_msg = $e->getMessage();
                
                // Check if it's a foreign key violation (id_alat = 0 doesn't exist)
                if (strpos($error_msg, 'fk_peminjaman_alat') !== false || strpos($error_msg, '23503') !== false) {
                    // Ensure dummy alat exists and retry
                    try {
                        // Check if dummy exists first
                        $check_dummy = $conn->prepare("SELECT 1 FROM alat_lab WHERE id_alat_lab = 0");
                        $check_dummy->execute();
                        if (!$check_dummy->fetch()) {
                            // Create dummy alat if it doesn't exist
                            $dummy_stmt = $conn->prepare("INSERT INTO alat_lab (id_alat_lab, nama_alat, deskripsi, stock) 
                                VALUES (0, 'Room Placeholder', 'Dummy alat for room borrowing', 0)");
                            $dummy_stmt->execute();
                        }
                        
                        // Fix sequence
                        $max_id_stmt = $conn->query("SELECT COALESCE(MAX(id_peminjaman), 0) as max_id FROM peminjaman");
                        $max_id = $max_id_stmt->fetch()['max_id'];
                        $conn->exec("SELECT setval('peminjaman_id_peminjaman_seq', " . ($max_id + 1) . ", false)");
                        
                        // Retry insert
                        $stmt = $conn->prepare("INSERT INTO peminjaman 
                            (id_alat, id_ruang, nama_peminjam, tanggal_pinjam, waktu_pinjam, waktu_kembali, status, keterangan) 
                            VALUES (0, :id_ruang, :nama_peminjam, :tanggal_pinjam, :waktu_pinjam, :waktu_kembali, 'dipinjam', :keterangan)");
                        $stmt->execute([
                            'id_ruang' => $id_ruang,
                            'nama_peminjam' => $nama_peminjam,
                            'tanggal_pinjam' => $tanggal_pinjam,
                            'waktu_pinjam' => $waktu_pinjam,
                            'waktu_kembali' => $waktu_kembali,
                            'keterangan' => $keterangan ?: null
                        ]);
                        
                        $message = 'Your request borrowing has been sent';
                        $message_type = 'success';
                    } catch (PDOException $e2) {
                        // If dummy insert fails due to duplicate, it means it already exists, so retry peminjaman insert
                        if (strpos($e2->getMessage(), 'duplicate key') !== false || strpos($e2->getMessage(), '23505') !== false) {
                            try {
                                // Fix sequence
                                $max_id_stmt = $conn->query("SELECT COALESCE(MAX(id_peminjaman), 0) as max_id FROM peminjaman");
                                $max_id = $max_id_stmt->fetch()['max_id'];
                                $conn->exec("SELECT setval('peminjaman_id_peminjaman_seq', " . ($max_id + 1) . ", false)");
                                
                                // Retry insert
                                $stmt = $conn->prepare("INSERT INTO peminjaman 
                                    (id_alat, id_ruang, nama_peminjam, tanggal_pinjam, waktu_pinjam, waktu_kembali, status, keterangan) 
                                    VALUES (0, :id_ruang, :nama_peminjam, :tanggal_pinjam, :waktu_pinjam, :waktu_kembali, 'dipinjam', :keterangan)");
                                $stmt->execute([
                                    'id_ruang' => $id_ruang,
                                    'nama_peminjam' => $nama_peminjam,
                                    'tanggal_pinjam' => $tanggal_pinjam,
                                    'waktu_pinjam' => $waktu_pinjam,
                                    'waktu_kembali' => $waktu_kembali,
                                    'keterangan' => $keterangan ?: null
                                ]);
                                
                                $message = 'Your request borrowing has been sent';
                                $message_type = 'success';
                            } catch (PDOException $e3) {
                                $message = 'Error: ' . $e3->getMessage();
                                $message_type = 'error';
                            }
                        } else {
                            $message = 'Error: ' . $e2->getMessage();
                            $message_type = 'error';
                        }
                    }
                } elseif (strpos($error_msg, 'duplicate key') !== false || strpos($error_msg, '23505') !== false) {
                    // Fix sequence and try again
                    try {
                        $max_id_stmt = $conn->query("SELECT COALESCE(MAX(id_peminjaman), 0) as max_id FROM peminjaman");
                        $max_id = $max_id_stmt->fetch()['max_id'];
                        $conn->exec("SELECT setval('peminjaman_id_peminjaman_seq', " . ($max_id + 1) . ", false)");
                        
                        // Retry insert
                        $stmt = $conn->prepare("INSERT INTO peminjaman 
                            (id_alat, id_ruang, nama_peminjam, tanggal_pinjam, waktu_pinjam, waktu_kembali, status, keterangan) 
                            VALUES (0, :id_ruang, :nama_peminjam, :tanggal_pinjam, :waktu_pinjam, :waktu_kembali, 'dipinjam', :keterangan)");
                        $stmt->execute([
                            'id_ruang' => $id_ruang,
                            'nama_peminjam' => $nama_peminjam,
                            'tanggal_pinjam' => $tanggal_pinjam,
                            'waktu_pinjam' => $waktu_pinjam,
                            'waktu_kembali' => $waktu_kembali,
                            'keterangan' => $keterangan ?: null
                        ]);
                        
                        $message = 'Your request borrowing has been sent';
                        $message_type = 'success';
                    } catch (PDOException $e2) {
                        $message = 'Error: ' . $e2->getMessage();
                        $message_type = 'error';
                    }
                } else {
                    $message = 'Error: ' . $error_msg;
                    $message_type = 'error';
                }
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
                    // Menggunakan prosedur proc_return_peminjaman
                    require_once __DIR__ . '/../config/procedures.php';
                    $admin_id = $_SESSION['id_admin'] ?? null;
                    
                    $result = callReturnPeminjaman($id_peminjaman, $admin_id, 'baik', null);
                    
                    if ($result['success']) {
                        $message = 'Tool returned successfully!';
                        $message_type = 'success';
                    } else {
                        $message = $result['message'];
                        $message_type = 'error';
                    }
                }
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    }
}

// Get alat lab yang tersedia - menggunakan view_alat_tersedia
try {
    $alat_stmt = $conn->query("
        SELECT * FROM view_alat_tersedia
        WHERE stok_tersedia > 0
        ORDER BY nama_alat
    ");
    $alat_list = $alat_stmt->fetchAll();
} catch (PDOException $e) {
    $alat_list = [];
}

// Get all ruangan yang tersedia
try {
    $ruang_stmt = $conn->query("SELECT * FROM ruang_lab ORDER BY nama_ruang");
    $ruang_list_all = $ruang_stmt->fetchAll();
    $ruang_info = !empty($ruang_list_all) ? $ruang_list_all[0] : null; // Use first room as default
} catch (PDOException $e) {
    $ruang_list_all = [];
    $ruang_info = null;
}

// Get jadwal peminjaman ruangan (only approved active bookings for calendar)
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
        AND (pj.keterangan IS NOT NULL AND pj.keterangan LIKE '%[APPROVED]%')
        ORDER BY pj.tanggal_pinjam, pj.waktu_pinjam
    ");
    $jadwal_ruang = $jadwal_stmt->fetchAll();
    
    // Organize bookings by date for calendar display
    $bookings_by_date = [];
    foreach ($jadwal_ruang as $booking) {
        $date = $booking['tanggal_pinjam'];
        if (!isset($bookings_by_date[$date])) {
            $bookings_by_date[$date] = [];
        }
        $bookings_by_date[$date][] = $booking;
    }
} catch (PDOException $e) {
    $jadwal_ruang = [];
    $bookings_by_date = [];
}

// Get active peminjaman alat - menggunakan view_alat_dipinjam
try {
    $peminjaman_alat_stmt = $conn->query("
        SELECT * FROM view_alat_dipinjam
        WHERE id_alat IS NOT NULL
        ORDER BY tanggal_pinjam DESC
    ");
    $peminjaman_alat_list = $peminjaman_alat_stmt->fetchAll();
} catch (PDOException $e) {
    $peminjaman_alat_list = [];
}

// Get active peminjaman ruang - menggunakan view_ruang_dipinjam
try {
    $peminjaman_ruang_stmt = $conn->query("
        SELECT * FROM view_ruang_dipinjam
        ORDER BY tanggal_pinjam DESC, waktu_pinjam DESC
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
    <title><?= htmlspecialchars($page_info['title'] ?: 'Tool Loans - InLET'); ?></title>

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
                <h1 class="display-4 fw-bold"><?= htmlspecialchars($page_info['title'] ?: 'Lab Borrowing Dashboard'); ?></h1>
                <?php if (!empty($page_info['subtitle'])): ?>
                    <p class="lead mt-3"><?= htmlspecialchars($page_info['subtitle']); ?></p>
                <?php else: ?>
                    <p class="lead mt-3">Easily manage tool and room borrowing from the lab</p>
                <?php endif; ?>
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
            <?php
            // Get active tab from URL or default to 'alat'
            $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'alat';
            $is_alat_active = $active_tab === 'alat';
            $is_ruang_active = $active_tab === 'ruang';
            ?>
            <ul class="nav nav-tabs mb-4" id="borrowingTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo $is_alat_active ? 'active' : ''; ?>" id="alat-tab" data-bs-toggle="tab" data-bs-target="#alat"
                        type="button" role="tab">
                        Borrow Tools
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo $is_ruang_active ? 'active' : ''; ?>" id="ruang-tab" data-bs-toggle="tab" data-bs-target="#ruang" type="button"
                        role="tab">
                        BorrowRoom
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="borrowingTabContent">
                <!-- Tab Alat -->
                <div class="tab-pane fade <?php echo $is_alat_active ? 'show active' : ''; ?>" id="alat" role="tabpanel">
                    <div class="section-title">
                        <h2>Available Tools</h2>
                    </div>

                    <div class="row g-4 mb-5">
                        <?php if (empty($alat_list)): ?>
                            <div class="col-12">
                                <div class="alert alert-warning">No tools available for borrowing at this time.</div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($alat_list as $alat): ?>
                                <div class="col-md-4 col-lg-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($alat['nama_alat']); ?></h5>
                                            <?php if ($alat['deskripsi']): ?>
                                                <p class="card-text text-muted small">
                                                    <?php echo htmlspecialchars($alat['deskripsi']); ?>
                                                </p>
                                            <?php endif; ?>
                                            <div class="mt-3">
                                                <span class="badge bg-success">
                                                    Available: <?php echo $alat['stok_tersedia']; ?> unit(s)
                                                </span>
                                                <?php if ($alat['stock'] > 0): ?>
                                                    <br><small class="text-muted">
                                                        Total Stock: <?php echo $alat['stock']; ?> unit(s)
                                                    </small>
                                                <?php endif; ?>
                                            </div>
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
                                    <label class="form-label">Borrower Name *</label>
                                    <input type="text" class="form-control" name="nama_peminjam" 
                                        placeholder="Enter your name" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Select Tool *</label>
                                    <select class="form-select" name="id_alat" required>
                                        <option value="">Select Lab Tool</option>
                                        <?php foreach ($alat_list as $alat): ?>
                                            <option value="<?php echo $alat['id_alat_lab']; ?>">
                                                <?php echo htmlspecialchars($alat['nama_alat']); ?>
                                                (Available: <?php echo $alat['stok_tersedia']; ?> unit(s))
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Borrow Date *</label>
                                    <input type="date" class="form-control" name="tanggal_pinjam" id="tanggal_pinjam_alat"
                                        value="<?php echo date('Y-m-d'); ?>" 
                                        min="<?php echo date('Y-m-d'); ?>"
                                        required>
                                    <small class="text-muted d-block mt-1">Cannot select past dates</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Return Date *</label>
                                    <input type="date" class="form-control" name="tanggal_kembali" id="tanggal_kembali_alat"
                                        value="<?php echo date('Y-m-d'); ?>" 
                                        min="<?php echo date('Y-m-d'); ?>"
                                        required>
                                    <small class="text-muted d-block mt-1">Must be on or after borrow date</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Quantity *</label>
                                    <input type="number" class="form-control" name="jumlah" 
                                        min="1" value="1" required id="jumlahInput">
                                    <small class="text-muted" id="stockInfo">Available stock will be shown after selecting a tool</small>
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
                <div class="tab-pane fade <?php echo $is_ruang_active ? 'show active' : ''; ?>" id="ruang" role="tabpanel">
                    <?php if (!empty($ruang_list_all)): ?>
                        <div class="alert alert-info mb-4">
                            <h5 class="mb-2">📍 Available Rooms (<?php echo count($ruang_list_all); ?>)</h5>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($ruang_list_all as $ruang): ?>
                                    <span class="badge bg-<?php echo ($ruang['status'] ?? 'tersedia') === 'tersedia' ? 'success' : 'warning'; ?>">
                                        <?php echo htmlspecialchars($ruang['nama_ruang']); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="section-title">
                        <h2>Room Booking Calendar</h2>
                    </div>

                    <div class="card card-surface p-4 mb-5">
                        <div class="calendar-container" id="roomCalendarContainer">
                            <?php
                            // Get current month from URL or use current month (using normal date)
                            $current_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
                            $today = new DateTime();
                            $view_date = new DateTime($current_month . '-01');
                            
                            // Generate calendar for selected month
                            $start_date = clone $view_date;
                            $start_date->modify('first day of this month');
                            $end_date = clone $view_date;
                            $end_date->modify('last day of this month');
                            
                            // Set today to start of day for accurate comparison
                            $today->setTime(0, 0, 0);
                            
                            $current = clone $start_date;
                            $calendar_days = [];
                            while ($current <= $end_date) {
                                $current->setTime(0, 0, 0); // Set to start of day for comparison
                                $date_str = $current->format('Y-m-d');
                                $is_today = $date_str === $today->format('Y-m-d');
                                $is_past = $current < $today;
                                $is_today_or_past = $current <= $today;
                                
                                $calendar_days[] = [
                                    'date' => $date_str,
                                    'day' => $current->format('d'),
                                    'day_name' => $current->format('D'),
                                    'month' => $current->format('M'),
                                    'is_today' => $is_today,
                                    'is_past' => $is_past,
                                    'is_today_or_past' => $is_today_or_past,
                                    'bookings' => $bookings_by_date[$date_str] ?? []
                                ];
                                $current->modify('+1 day');
                            }
                            
                            // Group by week
                            $weeks = [];
                            $week = [];
                            $first_day = clone $start_date;
                            $first_day->modify('first day of this month');
                            $day_of_week = (int)$first_day->format('w'); // 0 = Sunday
                            
                            // Add empty cells for days before month starts
                            for ($i = 0; $i < $day_of_week; $i++) {
                                $week[] = null;
                            }
                            
                            foreach ($calendar_days as $day) {
                                $week[] = $day;
                                if (count($week) == 7) {
                                    $weeks[] = $week;
                                    $week = [];
                                }
                            }
                            
                            // Add remaining days
                            if (!empty($week)) {
                                while (count($week) < 7) {
                                    $week[] = null;
                                }
                                $weeks[] = $week;
                            }
                            
                            // Calculate prev/next month
                            $prev_month = clone $view_date;
                            $prev_month->modify('-1 month');
                            $next_month = clone $view_date;
                            $next_month->modify('+1 month');
                            ?>
                            
                            <div class="calendar-navigation mb-3">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="changeMonth('<?php echo $prev_month->format('Y-m'); ?>')">
                                    ← Previous
                                </button>
                                <h4 class="calendar-month-title mb-0">
                                    <?php echo $view_date->format('F Y'); ?>
                                </h4>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="changeMonth('<?php echo $next_month->format('Y-m'); ?>')">
                                    Next →
                                </button>
                            </div>
                            
                            <div class="calendar-grid">
                                <div class="calendar-header">
                                    <div class="calendar-day-header">Sun</div>
                                    <div class="calendar-day-header">Mon</div>
                                    <div class="calendar-day-header">Tue</div>
                                    <div class="calendar-day-header">Wed</div>
                                    <div class="calendar-day-header">Thu</div>
                                    <div class="calendar-day-header">Fri</div>
                                    <div class="calendar-day-header">Sat</div>
                                </div>
                                
                                <?php foreach ($weeks as $week): ?>
                                    <div class="calendar-week">
                                        <?php foreach ($week as $day): ?>
                                            <?php if ($day === null): ?>
                                                <div class="calendar-day empty"></div>
                                            <?php else: ?>
                                                <div class="calendar-day <?php echo $day['is_today'] ? 'today' : ''; ?> <?php echo $day['is_today_or_past'] ? 'past' : ''; ?> <?php echo !$day['is_today_or_past'] ? 'selectable' : ''; ?>" 
                                                     data-date="<?php echo $day['date']; ?>"
                                                     data-is-past="<?php echo $day['is_today_or_past'] ? 'true' : 'false'; ?>">
                                                    <div class="calendar-day-number">
                                                        <?php echo $day['day']; ?>
                                                        <!-- Today badge will be added by JavaScript -->
                                                    </div>
                                                    <div class="calendar-day-name"><?php echo $day['day_name']; ?></div>
                                                    <div class="calendar-bookings">
                                                        <?php if (!empty($day['bookings'])): ?>
                                                            <?php foreach ($day['bookings'] as $booking): ?>
                                                                <div class="booking-item" 
                                                                     title="<?php echo htmlspecialchars($booking['nama_peminjam']); ?> - <?php echo substr($booking['waktu_pinjam'], 0, 5); ?>-<?php echo substr($booking['waktu_kembali'], 0, 5); ?>">
                                                                    <small class="booking-time">
                                                                        <?php echo substr($booking['waktu_pinjam'], 0, 5); ?>-<?php echo substr($booking['waktu_kembali'], 0, 5); ?>
                                                                    </small>
                                                                    <small class="booking-name">
                                                                        <?php echo htmlspecialchars($booking['nama_peminjam']); ?>
                                                                    </small>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <div class="booking-empty">Available</div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <style>
                        .calendar-container {
                            width: 100%;
                        }
                        
                        .calendar-grid {
                            display: flex;
                            flex-direction: column;
                            gap: 0.5rem;
                        }
                        
                        .calendar-header {
                            display: grid;
                            grid-template-columns: repeat(7, 1fr);
                            gap: 0.5rem;
                            margin-bottom: 0.5rem;
                        }
                        
                        .calendar-day-header {
                            text-align: center;
                            font-weight: 600;
                            color: var(--primary, #4f46e5);
                            padding: 0.5rem;
                            background: #f8f9fa;
                            border-radius: 8px;
                        }
                        
                        .calendar-week {
                            display: grid;
                            grid-template-columns: repeat(7, 1fr);
                            gap: 0.5rem;
                        }
                        
                        .calendar-day {
                            border: 2px solid #e2e8f0;
                            border-radius: 12px;
                            padding: 0.75rem;
                            min-height: 80px;
                            background: white;
                            transition: all 0.3s;
                            position: relative;
                        }
                        
                        .calendar-day.empty {
                            border: none;
                            background: transparent;
                        }
                        
                        .calendar-day:hover:not(.empty):not(.past) {
                            border-color: var(--primary, #4f46e5);
                            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);
                            transform: translateY(-2px);
                        }
                        
                        .calendar-day.today {
                            border-color: var(--primary, #4f46e5);
                            background: linear-gradient(135deg, rgba(79, 70, 229, 0.05) 0%, rgba(79, 70, 229, 0.1) 100%);
                        }
                        
                        .calendar-day.past {
                            opacity: 0.5;
                            background: #f8f9fa;
                            cursor: not-allowed !important;
                            pointer-events: none;
                        }
                        
                        .calendar-day.selectable {
                            cursor: pointer;
                        }
                        
                        .calendar-day.selectable:hover {
                            background: #f0f9ff;
                        }
                        
                        .calendar-day.past:hover {
                            background: #f8f9fa !important;
                            transform: none !important;
                            box-shadow: none !important;
                        }
                        
                        .calendar-navigation {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            margin-bottom: 1rem;
                        }
                        
                        .calendar-month-title {
                            font-size: 1.5rem;
                            font-weight: 600;
                            color: var(--primary, #4f46e5);
                        }
                        
                        .calendar-day-number {
                            font-size: 1.1rem;
                            font-weight: 700;
                            color: var(--dark, #1e293b);
                            margin-bottom: 0.25rem;
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                        }
                        
                        .today-badge {
                            font-size: 0.65rem;
                            background: var(--primary, #4f46e5);
                            color: white;
                            padding: 0.15rem 0.4rem;
                            border-radius: 4px;
                            font-weight: 600;
                        }
                        
                        .calendar-day-name {
                            font-size: 0.75rem;
                            color: #64748b;
                            margin-bottom: 0.5rem;
                            text-transform: uppercase;
                            letter-spacing: 0.5px;
                        }
                        
                        .calendar-bookings {
                            display: flex;
                            flex-direction: column;
                            gap: 0.25rem;
                            max-height: 60px;
                            overflow-y: auto;
                        }
                        
                        .booking-item {
                            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
                            color: white;
                            padding: 0.35rem 0.5rem;
                            border-radius: 6px;
                            font-size: 0.7rem;
                            cursor: pointer;
                            transition: all 0.2s;
                        }
                        
                        .booking-item:hover {
                            transform: scale(1.05);
                            box-shadow: 0 2px 8px rgba(79, 70, 229, 0.3);
                        }
                        
                        .booking-time {
                            display: block;
                            font-weight: 600;
                            margin-bottom: 0.1rem;
                        }
                        
                        .booking-name {
                            display: block;
                            opacity: 0.9;
                            white-space: nowrap;
                            overflow: hidden;
                            text-overflow: ellipsis;
                        }
                        
                        .booking-empty {
                            font-size: 0.7rem;
                            color: #94a3b8;
                            text-align: center;
                            padding: 0.25rem;
                            font-style: italic;
                        }
                        
                        @media (max-width: 768px) {
                            .calendar-day {
                                min-height: 70px;
                                padding: 0.5rem;
                            }
                            
                            .calendar-day-number {
                                font-size: 0.9rem;
                            }
                            
                            .booking-item {
                                font-size: 0.65rem;
                                padding: 0.25rem 0.4rem;
                            }
                        }
                    </style>

                    <div class="section-title mt-5">
                        <h2>Room Borrowing Form</h2>
                    </div>

                    <div class="card card-surface p-4 mb-5">
                        <form method="POST" id="formPinjamRuang">
                            <input type="hidden" name="action" value="pinjam_ruang">

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Select Room *</label>
                                    <select class="form-select" name="id_ruang" required>
                                        <option value="">Select Room</option>
                                        <?php if (!empty($ruang_list_all)): ?>
                                            <?php foreach ($ruang_list_all as $ruang): ?>
                                                <option value="<?php echo $ruang['id_ruang_lab']; ?>">
                                                    <?php echo htmlspecialchars($ruang['nama_ruang']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Borrower Name *</label>
                                    <input type="text" class="form-control" name="nama_peminjam" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Borrow Date *</label>
                                    <input type="date" class="form-control" name="tanggal_pinjam" id="tanggal_pinjam_ruang"
                                        value="<?php echo date('Y-m-d'); ?>" 
                                        min="<?php echo date('Y-m-d'); ?>"
                                        required>
                                    <small class="text-muted d-block mt-1">Cannot book for today or past dates</small>
                                </div>

                                <div class="col-md-4">
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
                                Request Room Booking
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
    <script>
        // Update stock info when tool is selected
        const toolSelect = document.querySelector('select[name="id_alat"]');
        const jumlahInput = document.getElementById('jumlahInput');
        const stockInfo = document.getElementById('stockInfo');
        const alatList = <?php echo json_encode($alat_list); ?>;

        if (toolSelect && jumlahInput && stockInfo) {
            toolSelect.addEventListener('change', function() {
                const selectedId = parseInt(this.value);
                const selectedAlat = alatList.find(alat => alat.id_alat_lab == selectedId);
                
                if (selectedAlat) {
                    const availableStock = selectedAlat.stok_tersedia;
                    stockInfo.textContent = `Available: ${availableStock} unit(s)`;
                    stockInfo.className = 'text-success';
                    jumlahInput.setAttribute('max', availableStock);
                    
                    // Reset quantity if it exceeds available stock
                    if (parseInt(jumlahInput.value) > availableStock) {
                        jumlahInput.value = availableStock > 0 ? availableStock : 1;
                    }
                } else {
                    stockInfo.textContent = 'Available stock will be shown after selecting a tool';
                    stockInfo.className = 'text-muted';
                    jumlahInput.removeAttribute('max');
                }
            });

            // Validate quantity on input
            jumlahInput.addEventListener('input', function() {
                const selectedId = parseInt(toolSelect.value);
                if (selectedId) {
                    const selectedAlat = alatList.find(alat => alat.id_alat_lab == selectedId);
                    if (selectedAlat) {
                        const availableStock = selectedAlat.stok_tersedia;
                        const enteredQty = parseInt(this.value);
                        
                        if (enteredQty > availableStock) {
                            this.setCustomValidity(`Maximum ${availableStock} unit(s) available`);
                            stockInfo.textContent = `⚠️ Maximum ${availableStock} unit(s) available`;
                            stockInfo.className = 'text-danger';
                        } else if (enteredQty <= 0) {
                            this.setCustomValidity('Quantity must be greater than 0');
                            stockInfo.className = 'text-danger';
                        } else {
                            this.setCustomValidity('');
                            stockInfo.textContent = `Available: ${availableStock} unit(s)`;
                            stockInfo.className = 'text-success';
                        }
                    }
                }
            });

            // Date validation for tool borrowing with real-time validation
            function initDateValidation() {
                const tanggalPinjamAlat = document.getElementById('tanggal_pinjam_alat');
                const tanggalKembaliAlat = document.getElementById('tanggal_kembali_alat');
                
                if (!tanggalPinjamAlat || !tanggalKembaliAlat) {
                    // Elements not ready yet, try again
                    setTimeout(initDateValidation, 100);
                    return;
                }
                
                // Function to get today's date in YYYY-MM-DD format (using normal date, not from table)
                function getTodayDate() {
                    // Get current date using normal JavaScript Date (browser's local time)
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    const todayStr = today.toISOString().split('T')[0];
                    // Debug: log today's date
                    console.log('Today\'s date:', todayStr);
                    return todayStr;
                }
                
                // Update min attributes for borrow date and return date (from today onwards - cannot select past dates)
                function updateBorrowDateLimits() {
                    // Double check elements still exist
                    if (!tanggalPinjamAlat || !tanggalKembaliAlat) {
                        return;
                    }
                    
                    const currentToday = getTodayDate();
                    
                    // ALWAYS update (force update) - don't check if already set
                    // This ensures it's always current even if page was loaded yesterday
                    
                    // Borrow date: min = today (cannot select past dates)
                    // Method 1: Update property first
                    tanggalPinjamAlat.min = currentToday;
                    // Remove max attribute (no maximum limit)
                    tanggalPinjamAlat.removeAttribute('max');
                    
                    // Method 2: ALWAYS force update attribute HTML (remove then set)
                    tanggalPinjamAlat.removeAttribute('min');
                    tanggalPinjamAlat.setAttribute('min', currentToday);
                    
                    // Method 3: Also set via setAttribute directly (redundant but ensures update)
                    tanggalPinjamAlat.setAttribute('min', currentToday);
                    
                    // Debug: Verify what was set (check in console)
                    const actualMin = tanggalPinjamAlat.getAttribute('min');
                    if (actualMin !== currentToday) {
                        console.warn('WARNING: min attribute mismatch! Expected:', currentToday, 'Got:', actualMin);
                        // Force update again
                        tanggalPinjamAlat.removeAttribute('min');
                        tanggalPinjamAlat.setAttribute('min', currentToday);
                    } else {
                        console.log('✓ Borrow date min updated successfully to:', currentToday);
                    }
                    
                    // Update value if it's in the past (force to today) - ALWAYS CHECK
                    // This handles case where page was loaded yesterday and value is still old
                    if (!tanggalPinjamAlat.value || tanggalPinjamAlat.value < currentToday) {
                        if (tanggalPinjamAlat.value && tanggalPinjamAlat.value < currentToday) {
                            console.log('Updating past date value in updateBorrowDateLimits:', tanggalPinjamAlat.value, 'to:', currentToday);
                        }
                        tanggalPinjamAlat.value = currentToday;
                    }
                    
                    // Return date: min = today (cannot select past dates, but must be >= borrow date)
                    // Update return date min to today
                    tanggalKembaliAlat.min = currentToday;
                    tanggalKembaliAlat.removeAttribute('min');
                    tanggalKembaliAlat.setAttribute('min', currentToday);
                    tanggalKembaliAlat.setAttribute('min', currentToday); // Set twice to ensure
                    
                    // Also update return date min based on borrow date if borrow date is set
                    if (tanggalPinjamAlat.value && tanggalPinjamAlat.value >= currentToday) {
                        const borrowDate = tanggalPinjamAlat.value;
                        if (tanggalKembaliAlat.min !== borrowDate) {
                            tanggalKembaliAlat.min = borrowDate;
                            tanggalKembaliAlat.removeAttribute('min');
                            tanggalKembaliAlat.setAttribute('min', borrowDate);
                        }
                    }
                    
                    // Update return date value if it's in the past
                    if (tanggalKembaliAlat.value && tanggalKembaliAlat.value < currentToday) {
                        tanggalKembaliAlat.value = currentToday;
                    }
                }
                
                // CRITICAL: Set value and min FIRST before anything else
                const currentToday = getTodayDate();
                
                // Set borrow date value to today (if empty or past)
                // Note: HTML already has value="<?php echo date('Y-m-d'); ?>" but we still validate in JS
                if (!tanggalPinjamAlat.value || tanggalPinjamAlat.value < currentToday) {
                    tanggalPinjamAlat.value = currentToday;
                    console.log('Set borrow date value to:', currentToday);
                }
                
                // Set borrow date min to today
                tanggalPinjamAlat.min = currentToday;
                tanggalPinjamAlat.removeAttribute('min');
                tanggalPinjamAlat.setAttribute('min', currentToday);
                console.log('Set borrow date min to:', currentToday);
                
                // Set return date min to today (will be updated based on borrow date later)
                tanggalKembaliAlat.min = currentToday;
                tanggalKembaliAlat.removeAttribute('min');
                tanggalKembaliAlat.setAttribute('min', currentToday);
                console.log('Set return date min to:', currentToday);
                
                // Now run update function
                updateBorrowDateLimits();
                
                // Run again after short delays to ensure it sticks
                setTimeout(function() {
                    updateBorrowDateLimits();
                    const today = getTodayDate();
                    if (tanggalPinjamAlat.value && tanggalPinjamAlat.value < today) {
                        tanggalPinjamAlat.value = today;
                    }
                }, 10);
                setTimeout(function() {
                    updateBorrowDateLimits();
                    const today = getTodayDate();
                    if (tanggalPinjamAlat.value && tanggalPinjamAlat.value < today) {
                        tanggalPinjamAlat.value = today;
                    }
                }, 50);
                setTimeout(function() {
                    updateBorrowDateLimits();
                    const today = getTodayDate();
                    if (tanggalPinjamAlat.value && tanggalPinjamAlat.value < today) {
                        tanggalPinjamAlat.value = today;
                    }
                }, 100);
                setTimeout(function() {
                    updateBorrowDateLimits();
                    const today = getTodayDate();
                    if (tanggalPinjamAlat.value && tanggalPinjamAlat.value < today) {
                        tanggalPinjamAlat.value = today;
                    }
                }, 200);
                
                // Update limits every 200ms to ensure it's always current (very frequent)
                setInterval(updateBorrowDateLimits, 200);
                
                // Use MutationObserver to watch for value changes
                const observerBorrow = new MutationObserver(function(mutations) {
                    const currentToday = getTodayDate();
                    if (tanggalPinjamAlat.value && tanggalPinjamAlat.value < currentToday) {
                        console.warn('MutationObserver detected past date:', tanggalPinjamAlat.value);
                        tanggalPinjamAlat.value = currentToday;
                        tanggalPinjamAlat.removeAttribute('min');
                        tanggalPinjamAlat.setAttribute('min', currentToday);
                    }
                    // Also check min attribute
                    const actualMin = tanggalPinjamAlat.getAttribute('min');
                    if (actualMin && actualMin < currentToday) {
                        console.warn('MutationObserver detected old min (past):', actualMin);
                        tanggalPinjamAlat.removeAttribute('min');
                        tanggalPinjamAlat.setAttribute('min', currentToday);
                    }
                });
                
                // Observe the input element for attribute and value changes
                observerBorrow.observe(tanggalPinjamAlat, {
                    attributes: true,
                    attributeFilter: ['value', 'min', 'max'],
                    childList: false,
                    subtree: false
                });
                
                // Also watch for property changes using a proxy-like approach
                let lastValue = tanggalPinjamAlat.value;
                setInterval(function() {
                    const currentValue = tanggalPinjamAlat.value;
                    if (currentValue !== lastValue) {
                        lastValue = currentValue;
                        const currentToday = getTodayDate();
                        if (currentValue && currentValue < currentToday) {
                            console.warn('Value change detected - past date:', currentValue);
                            tanggalPinjamAlat.value = currentToday;
                            updateBorrowDateLimits();
                        }
                    }
                }, 100);
                
                // Update limits when date picker is about to open (focus event)
                tanggalPinjamAlat.addEventListener('focus', function() {
                    updateBorrowDateLimits();
                    // Force to today if in the past
                    const currentToday = getTodayDate();
                    if (this.value && this.value < currentToday) {
                        this.value = currentToday;
                    }
                    // Double-check min attribute is set correctly
                    setTimeout(function() {
                        const actualMin = tanggalPinjamAlat.getAttribute('min');
                        if (actualMin !== currentToday) {
                            tanggalPinjamAlat.removeAttribute('min');
                            tanggalPinjamAlat.setAttribute('min', currentToday);
                        }
                    }, 50);
                });
                
                // Update limits when date picker is clicked
                tanggalPinjamAlat.addEventListener('click', function() {
                    updateBorrowDateLimits();
                    const currentToday = getTodayDate();
                    if (this.value && this.value < currentToday) {
                        this.value = currentToday;
                    }
                    // Double-check min attribute is set correctly
                    setTimeout(function() {
                        const actualMin = tanggalPinjamAlat.getAttribute('min');
                        if (actualMin !== currentToday) {
                            tanggalPinjamAlat.removeAttribute('min');
                            tanggalPinjamAlat.setAttribute('min', currentToday);
                        }
                    }, 50);
                });
                
                // Additional: Intercept before date picker opens (mousedown)
                tanggalPinjamAlat.addEventListener('mousedown', function(e) {
                    updateBorrowDateLimits();
                    const currentToday = getTodayDate();
                    // Ensure min is set before picker opens
                    this.min = currentToday;
                    this.removeAttribute('min');
                    this.setAttribute('min', currentToday);
                });
                
                // Store function reference for use in other event handlers
                window.updateBorrowDateLimits = updateBorrowDateLimits;
                
                // Same for return date
                tanggalKembaliAlat.addEventListener('focus', function() {
                    updateBorrowDateLimits();
                    const currentToday = getTodayDate();
                    const borrowDate = tanggalPinjamAlat.value || currentToday;
                    if (this.value && this.value < currentToday) {
                        this.value = currentToday;
                    }
                });
                
                tanggalKembaliAlat.addEventListener('click', function() {
                    updateBorrowDateLimits();
                    const currentToday = getTodayDate();
                    const borrowDate = tanggalPinjamAlat.value || currentToday;
                    if (this.value && this.value < currentToday) {
                        this.value = currentToday;
                    }
                });
                
                // Prevent selecting past dates - input event (real-time)
                tanggalPinjamAlat.addEventListener('input', function() {
                    const currentToday = getTodayDate();
                    
                    // Cannot select past dates - reset to today if past date is selected
                    if (this.value && this.value < currentToday) {
                        console.warn('Past date detected:', this.value, 'Resetting to:', currentToday);
                        this.value = currentToday;
                        this.setCustomValidity('Cannot select past dates');
                        alert('Cannot select past dates. Date has been reset to today.');
                        // Force update min again
                        this.removeAttribute('min');
                        this.setAttribute('min', currentToday);
                    } else {
                        this.setCustomValidity('');
                    }
                    
                    // Update return date min based on borrow date
                    updateBorrowDateLimits();
                });
                
                // Also validate on keydown (when user types)
                tanggalPinjamAlat.addEventListener('keydown', function(e) {
                    // If user tries to type a date, validate immediately
                    setTimeout(function() {
                        const currentToday = getTodayDate();
                        if (tanggalPinjamAlat.value && tanggalPinjamAlat.value < currentToday) {
                            tanggalPinjamAlat.value = currentToday;
                            tanggalPinjamAlat.setCustomValidity('Cannot select past dates');
                        }
                    }, 10);
                });
                
                // Additional validation: check value periodically and on any interaction
                function validateBorrowDate() {
                    const currentToday = getTodayDate();
                    // Cannot select past dates
                    if (tanggalPinjamAlat.value && tanggalPinjamAlat.value < currentToday) {
                        console.warn('Past date detected in validation:', tanggalPinjamAlat.value, 'Resetting to:', currentToday);
                        tanggalPinjamAlat.value = currentToday;
                        tanggalPinjamAlat.setCustomValidity('Cannot select past dates');
                        // Force update min
                        tanggalPinjamAlat.removeAttribute('min');
                        tanggalPinjamAlat.setAttribute('min', currentToday);
                        updateBorrowDateLimits();
                        return false;
                    }
                    // Also verify min attribute is correct
                    const actualMin = tanggalPinjamAlat.getAttribute('min');
                    if (actualMin !== currentToday) {
                        console.warn('Min attribute incorrect:', actualMin, 'Expected:', currentToday);
                        tanggalPinjamAlat.removeAttribute('min');
                        tanggalPinjamAlat.setAttribute('min', currentToday);
                    }
                    return true;
                }
                
                // Check value periodically as backup (every 200ms - more frequent)
                setInterval(function() {
                    validateBorrowDate();
                }, 200);
                
                // Validate on any mouse/keyboard interaction
                ['mousedown', 'keydown', 'keyup', 'mouseup'].forEach(function(eventType) {
                    tanggalPinjamAlat.addEventListener(eventType, function() {
                        setTimeout(validateBorrowDate, 10);
                    });
                });
                
                // Prevent selecting past dates - change event
                tanggalPinjamAlat.addEventListener('change', function() {
                    const currentToday = getTodayDate();
                    const selectedDate = this.value;
                    
                    // Cannot select past dates - STRICT VALIDATION
                    if (selectedDate && selectedDate < currentToday) {
                        console.warn('Past date selected in change event:', selectedDate, 'Resetting to:', currentToday);
                        this.value = currentToday;
                        this.setCustomValidity('Cannot select past dates');
                        alert('Cannot select past dates. Date has been reset to today.');
                        // Force update min
                        this.removeAttribute('min');
                        this.setAttribute('min', currentToday);
                        updateBorrowDateLimits();
                        return;
                    } else {
                        this.setCustomValidity('');
                    }
                    
                    // Double-check min is still correct
                    const actualMin = this.getAttribute('min');
                    if (actualMin !== currentToday) {
                        this.removeAttribute('min');
                        this.setAttribute('min', currentToday);
                    }
                    
                    // Update return date min when borrow date changes
                    updateBorrowDateLimits();
                    
                    // If current return date is before borrow date, adjust it
                    if (tanggalKembaliAlat.value && tanggalKembaliAlat.value < this.value) {
                        tanggalKembaliAlat.value = this.value;
                    }
                });
                
                // Additional protection: validate on blur (when date picker closes)
                tanggalPinjamAlat.addEventListener('blur', function() {
                    const currentToday = getTodayDate();
                    if (this.value && this.value < currentToday) {
                        this.value = currentToday;
                        this.setCustomValidity('Cannot select past dates');
                        alert('Cannot select past dates. Date has been reset to today.');
                        updateBorrowDateLimits();
                    }
                });
                
                // Prevent selecting past dates on return date - input event (real-time)
                tanggalKembaliAlat.addEventListener('input', function() {
                    const currentToday = getTodayDate();
                    const borrowDate = tanggalPinjamAlat.value || currentToday;
                    
                    // Cannot select past dates
                    if (this.value && this.value < currentToday) {
                        this.value = currentToday;
                        this.setCustomValidity('Cannot select past dates');
                    } else if (this.value && this.value < borrowDate) {
                        // Return date must be on or after borrow date
                        this.value = borrowDate;
                        this.setCustomValidity('Return date must be on or after borrow date');
                    } else {
                        this.setCustomValidity('');
                    }
                });
                
                // Validate return date - change event
                tanggalKembaliAlat.addEventListener('change', function() {
                    const currentToday = getTodayDate();
                    const borrowDate = tanggalPinjamAlat.value || currentToday;
                    const returnDate = this.value;
                    
                    // Cannot select past dates
                    if (returnDate && returnDate < currentToday) {
                        this.setCustomValidity('Cannot select past dates');
                        this.value = currentToday;
                        alert('Cannot select past dates. Date has been reset to today.');
                    } else if (returnDate && returnDate < borrowDate) {
                        // Return date must be on or after borrow date
                        this.setCustomValidity('Return date must be on or after borrow date');
                        this.value = borrowDate;
                        alert('Return date must be on or after borrow date. Date has been adjusted.');
                    } else {
                        this.setCustomValidity('');
                    }
                });
            }
            
            // Initialize date validation when DOM is ready - MULTIPLE METHODS
            function runInitDateValidation() {
                // Try immediately
                initDateValidation();
                
                // Try after DOM ready
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', function() {
                        initDateValidation();
                        setTimeout(initDateValidation, 50);
                        setTimeout(initDateValidation, 100);
                        setTimeout(initDateValidation, 200);
                    });
                } else {
                    // DOM already ready
                    setTimeout(initDateValidation, 10);
                    setTimeout(initDateValidation, 50);
                    setTimeout(initDateValidation, 100);
                    setTimeout(initDateValidation, 200);
                }
            }
            
            // Run immediately
            runInitDateValidation();
            
            // Also run when window loads
            window.addEventListener('load', function() {
                setTimeout(initDateValidation, 100);
                setTimeout(initDateValidation, 300);
            });
            
            // Also run when tab is shown (Bootstrap tab event)
            const alatTab = document.getElementById('alat-tab');
            if (alatTab) {
                alatTab.addEventListener('shown.bs.tab', function() {
                    // Tab is now visible, ensure date validation is initialized
                    setTimeout(initDateValidation, 10);
                    setTimeout(initDateValidation, 50);
                    setTimeout(initDateValidation, 150);
                });
            }
            
            // Also run when tab content is visible (MutationObserver)
            const alatTabPane = document.getElementById('alat');
            if (alatTabPane) {
                const observer = new MutationObserver(function(mutations) {
                    // Check if tab is visible
                    if (alatTabPane.classList.contains('active') && alatTabPane.classList.contains('show')) {
                        setTimeout(initDateValidation, 10);
                        setTimeout(initDateValidation, 50);
                    }
                });
                
                observer.observe(alatTabPane, {
                    attributes: true,
                    attributeFilter: ['class']
                });
            }
            
            // Also run periodically as ultimate backup (every 2 seconds)
            setInterval(function() {
                const tanggalPinjamAlat = document.getElementById('tanggal_pinjam_alat');
                if (tanggalPinjamAlat) {
                    initDateValidation();
                }
            }, 2000);

            // Date validation for room borrowing
            const tanggalPinjamRuang = document.getElementById('tanggal_pinjam_ruang');
            
            if (tanggalPinjamRuang) {
                // Get today's date (normal date, not from table)
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const todayStr = today.toISOString().split('T')[0];
                const tomorrow = new Date(today);
                tomorrow.setDate(tomorrow.getDate() + 1);
                const tomorrowStr = tomorrow.toISOString().split('T')[0];
                
                // Prevent selecting past dates or today on input
                tanggalPinjamRuang.addEventListener('input', function() {
                    const selectedDate = this.value;
                    
                    if (selectedDate <= todayStr) {
                        this.value = tomorrowStr;
                        this.setCustomValidity('Room cannot be booked for today or past dates');
                    } else {
                        this.setCustomValidity('');
                    }
                });
                
                tanggalPinjamRuang.addEventListener('change', function() {
                    const selectedDate = this.value;
                    
                    if (selectedDate <= todayStr) {
                        this.setCustomValidity('Room cannot be booked for today or past dates');
                        this.value = tomorrowStr;
                    } else {
                        this.setCustomValidity('');
                    }
                });
            }

            // Calendar navigation function
            function changeMonth(month) {
                const url = new URL(window.location.href);
                url.searchParams.set('month', month);
                url.searchParams.set('tab', 'ruang'); // Keep room tab active
                window.location.href = url.toString();
            }
            
            // Function to ensure calendar is updated after page load/navigation
            function ensureCalendarUpdated() {
                // Wait for calendar to be rendered
                setTimeout(function() {
                    setupCalendarDays();
                }, 100);
                setTimeout(function() {
                    setupCalendarDays();
                }, 300);
                setTimeout(function() {
                    setupCalendarDays();
                }, 500);
                setTimeout(function() {
                    setupCalendarDays();
                }, 1000);
            }

            // Save active tab to URL when tab is clicked
            const tabButtons = document.querySelectorAll('#borrowingTabs button[data-bs-toggle="tab"]');
            tabButtons.forEach(button => {
                button.addEventListener('shown.bs.tab', function (e) {
                    const tabId = e.target.getAttribute('data-bs-target').substring(1); // Remove #
                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', tabId);
                    // Preserve month parameter if exists
                    window.history.replaceState({}, '', url.toString());
                });
            });

            // Select date from calendar with real-time validation (using normal date, not from table)
            function selectDate(date) {
                const tanggalPinjamRuang = document.getElementById('tanggal_pinjam_ruang');
                if (tanggalPinjamRuang) {
                    // Get real-time today's date (normal date, not from table)
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    const todayStr = today.toISOString().split('T')[0];
                    
                    const selectedDate = new Date(date + 'T00:00:00');
                    selectedDate.setHours(0, 0, 0, 0);
                    const tomorrow = new Date(today);
                    tomorrow.setDate(tomorrow.getDate() + 1);
                    const tomorrowStr = tomorrow.toISOString().split('T')[0];
                    
                    // Only allow dates after today (room cannot be booked for today or past dates)
                    if (date > todayStr) {
                        tanggalPinjamRuang.value = date;
                        tanggalPinjamRuang.setCustomValidity('');
                        // Scroll to form
                        tanggalPinjamRuang.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        // Highlight the input briefly
                        tanggalPinjamRuang.style.borderColor = '#4f46e5';
                        setTimeout(() => {
                            tanggalPinjamRuang.style.borderColor = '';
                        }, 1000);
                    } else {
                        // If somehow a past date or today is selected, set to tomorrow
                        tanggalPinjamRuang.value = tomorrowStr;
                        tanggalPinjamRuang.setCustomValidity('Room cannot be booked for today or past dates');
                        alert('Room cannot be booked for today or past dates. Please select a future date.');
                    }
                }
            }
            
            // Function to validate and setup calendar days with real-time validation
            // Using normal date (not from table, not WIB calculation)
            function setupCalendarDays() {
                const calendarDays = document.querySelectorAll('.calendar-day[data-date]');
                
                // Get current date using normal JavaScript Date (browser's local time)
                // ALWAYS get fresh date to ensure accuracy
                const now = new Date();
                const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                today.setHours(0, 0, 0, 0);
                
                // Format as YYYY-MM-DD for comparison
                const year = today.getFullYear();
                const month = String(today.getMonth() + 1).padStart(2, '0');
                const day = String(today.getDate()).padStart(2, '0');
                const todayStr = `${year}-${month}-${day}`;
                
                console.log('Today (real-time):', todayStr, 'Current time:', now.toLocaleString());
                
                calendarDays.forEach(function(dayElement) {
                    const dateStr = dayElement.getAttribute('data-date');
                    if (!dateStr) return;
                    
                    // Real-time validation: check if date is actually past or today
                    // Compare date strings directly (YYYY-MM-DD format)
                    const isToday = dateStr === todayStr;
                    
                    // For past/future comparison, convert to Date objects
                    const dayDate = new Date(dateStr + 'T00:00:00');
                    dayDate.setHours(0, 0, 0, 0);
                    const isActuallyPast = dayDate < today; // Past (not including today)
                    const isTodayOrPast = dayDate <= today; // Today or past
                    
                    // Remove any existing onclick attribute
                    dayElement.removeAttribute('onclick');
                    
                    // Update "Today" badge - only show on actual today (real-time)
                    // ALWAYS check and update badge, even if it already exists
                    // This ensures badge moves when day changes (e.g., from 7th to 8th)
                    const dayNumberDiv = dayElement.querySelector('.calendar-day-number');
                    if (dayNumberDiv) {
                        // Remove ALL existing "Today" badges first (to handle duplicates and old badges)
                        const allBadges = dayNumberDiv.querySelectorAll('.today-badge');
                        allBadges.forEach(badge => badge.remove());
                        
                        // Then add badge ONLY if it's actually today (real-time check)
                        // Double-check with fresh date comparison
                        if (isToday && dateStr === todayStr) {
                            const todayBadge = document.createElement('span');
                            todayBadge.className = 'today-badge';
                            todayBadge.textContent = 'Today';
                            dayNumberDiv.appendChild(todayBadge);
                            console.log('✓ Added "Today" badge to:', dateStr, '(Current date:', todayStr, ')');
                        } else {
                            // Ensure no badge exists for non-today dates
                            // (already removed above, but double-check)
                            const remainingBadges = dayNumberDiv.querySelectorAll('.today-badge');
                            remainingBadges.forEach(badge => badge.remove());
                        }
                    }
                    
                    // Update visual state based on real-time check
                    if (isToday) {
                        // Today: add today class, remove past class, but make it non-clickable
                        dayElement.classList.add('today');
                        dayElement.classList.remove('past');
                        dayElement.classList.remove('selectable');
                        dayElement.style.cursor = 'not-allowed';
                        dayElement.style.opacity = '0.7';
                        dayElement.style.pointerEvents = 'none';
                    } else if (isActuallyPast) {
                        // Past: add past class, remove today and selectable
                        dayElement.classList.remove('today');
                        dayElement.classList.add('past');
                        dayElement.classList.remove('selectable');
                        dayElement.style.cursor = 'not-allowed';
                        dayElement.style.opacity = '0.5';
                        dayElement.style.pointerEvents = 'none';
                    } else {
                        // Future: remove past and today, add selectable
                        dayElement.classList.remove('today');
                        dayElement.classList.remove('past');
                        dayElement.classList.add('selectable');
                        dayElement.style.cursor = 'pointer';
                        dayElement.style.opacity = '1';
                        dayElement.style.pointerEvents = 'auto';
                    }
                    
                    // Update data-is-past attribute
                    dayElement.setAttribute('data-is-past', isTodayOrPast ? 'true' : 'false');
                    
                    // Remove old event listeners by cloning
                    const newElement = dayElement.cloneNode(true);
                    dayElement.parentNode.replaceChild(newElement, dayElement);
                    
                    // Add click event listener with real-time validation (only for future dates)
                    if (!isTodayOrPast) {
                        newElement.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            // Double-check with real-time date (normal date, not from table)
                            const clickedDate = new Date(dateStr + 'T00:00:00');
                            clickedDate.setHours(0, 0, 0, 0);
                            const now = new Date();
                            now.setHours(0, 0, 0, 0);
                            
                            // Real-time validation: prevent clicking past dates or today
                            if (clickedDate <= now) {
                                alert('Cannot select past dates or today. Please select a future date.');
                                return false;
                            }
                            
                            // Only call selectDate if date is valid
                            selectDate(dateStr);
                        });
                    }
                });
            }
            
            // Setup calendar days when page loads
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    setupCalendarDays();
                    ensureCalendarUpdated();
                });
            } else {
                // Page already loaded, run immediately
                setupCalendarDays();
                ensureCalendarUpdated();
            }
            
            // Also run after short delays to ensure calendar is fully rendered
            setTimeout(setupCalendarDays, 100);
            setTimeout(setupCalendarDays, 300);
            setTimeout(setupCalendarDays, 500);
            setTimeout(setupCalendarDays, 1000);
            setTimeout(setupCalendarDays, 2000);
            
            // Update calendar every 10 seconds to ensure "Today" badge is always correct and moves when day changes
            setInterval(setupCalendarDays, 10000); // Every 10 seconds (very frequent to catch day changes)
            
            // Also update every minute as backup
            setInterval(setupCalendarDays, 60000); // Every 60 seconds
            
            // Also update when tab is shown (in case user switches tabs)
            const ruangTab = document.getElementById('ruang-tab');
            if (ruangTab) {
                ruangTab.addEventListener('shown.bs.tab', function() {
                    setTimeout(setupCalendarDays, 100);
                    setTimeout(setupCalendarDays, 300);
                    setTimeout(setupCalendarDays, 500);
                });
            }
            
            // Update when window gains focus (user comes back to tab)
            window.addEventListener('focus', function() {
                setupCalendarDays();
                setTimeout(setupCalendarDays, 100);
                setTimeout(setupCalendarDays, 500);
            });
            
            // Update when visibility changes (user switches tabs)
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) {
                    setupCalendarDays();
                    setTimeout(setupCalendarDays, 100);
                    setTimeout(setupCalendarDays, 500);
                }
            });

            // Prevent auto-scroll on page load/reload
            (function() {
                // Disable browser's automatic scroll restoration
                if ('scrollRestoration' in history) {
                    history.scrollRestoration = 'manual';
                }
                
                // Remove hash from URL if exists (prevents anchor scroll)
                if (window.location.hash) {
                    window.history.replaceState('', document.title, window.location.pathname + window.location.search);
                }
                
                // Scroll to top on load
                window.addEventListener('load', function() {
                    window.scrollTo(0, 0);
                    // Prevent auto-focus on inputs
                    if (document.activeElement && document.activeElement.tagName === 'INPUT') {
                        document.activeElement.blur();
                    }
                });

                // Also prevent scroll on DOMContentLoaded
                document.addEventListener('DOMContentLoaded', function() {
                    window.scrollTo(0, 0);
                });

                // Prevent scroll when tab is shown via Bootstrap
                const tabButtons = document.querySelectorAll('#borrowingTabs button[data-bs-toggle="tab"]');
                tabButtons.forEach(button => {
                    button.addEventListener('shown.bs.tab', function() {
                        // Scroll to top after tab is shown
                        setTimeout(() => {
                            window.scrollTo({ top: 0, behavior: 'instant' });
                        }, 50);
                    });
                });
            })();
        }
    </script>

</body>

</html>