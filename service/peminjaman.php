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

require __DIR__ . '/../views/service/peminjaman.php';
