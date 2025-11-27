<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';

$conn = getDBConnection();
$message = '';
$message_type = '';

// Get all mahasiswa for dropdown
$mhs_stmt = $conn->query("SELECT id_mhs, nama, status FROM mahasiswa ORDER BY nama");
$mahasiswa_list = $mhs_stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_mhs = intval($_POST['id_mhs'] ?? 0);
    $tipe_absen = $_POST['tipe_absen'] ?? ''; // 'masuk' or 'keluar'
    $keterangan = trim($_POST['keterangan'] ?? '');
    
    if ($id_mhs <= 0) {
        $message = 'Mahasiswa harus dipilih!';
        $message_type = 'error';
    } elseif (empty($tipe_absen)) {
        $message = 'Tipe absensi harus dipilih!';
        $message_type = 'error';
    } else {
        try {
            $today = date('Y-m-d');
            
            if ($tipe_absen === 'masuk') {
                // Check if already checked in today
                $check_stmt = $conn->prepare("SELECT id_absensi FROM absensi 
                                             WHERE id_mhs = :id_mhs 
                                             AND tanggal = :tanggal 
                                             AND waktu_datang IS NOT NULL");
                $check_stmt->execute([
                    'id_mhs' => $id_mhs,
                    'tanggal' => $today
                ]);
                
                if ($check_stmt->fetch()) {
                    $message = 'Anda sudah melakukan absen masuk hari ini!';
                    $message_type = 'error';
                } else {
                    // Check if record exists for today
                    $check_stmt = $conn->prepare("SELECT id_absensi FROM absensi 
                                                 WHERE id_mhs = :id_mhs 
                                                 AND tanggal = :tanggal");
                    $check_stmt->execute([
                        'id_mhs' => $id_mhs,
                        'tanggal' => $today
                    ]);
                    $existing = $check_stmt->fetch();
                    
                    if ($existing) {
                        // Update existing record
                        $stmt = $conn->prepare("UPDATE absensi 
                                               SET waktu_datang = CURRENT_TIMESTAMP, 
                                                   keterangan = :keterangan 
                                               WHERE id_absensi = :id");
                        $stmt->execute([
                            'id' => $existing['id_absensi'],
                            'keterangan' => $keterangan ?: null
                        ]);
                    } else {
                        // Insert new record
                        $stmt = $conn->prepare("INSERT INTO absensi (id_mhs, tanggal, waktu_datang, keterangan) 
                                               VALUES (:id_mhs, :tanggal, CURRENT_TIMESTAMP, :keterangan)");
                        $stmt->execute([
                            'id_mhs' => $id_mhs,
                            'tanggal' => $today,
                            'keterangan' => $keterangan ?: null
                        ]);
                    }
                    
                    $message = 'Absen masuk berhasil!';
                    $message_type = 'success';
                }
            } elseif ($tipe_absen === 'keluar') {
                // Check if checked in today
                $check_stmt = $conn->prepare("SELECT id_absensi FROM absensi 
                                             WHERE id_mhs = :id_mhs 
                                             AND tanggal = :tanggal 
                                             AND waktu_datang IS NOT NULL");
                $check_stmt->execute([
                    'id_mhs' => $id_mhs,
                    'tanggal' => $today
                ]);
                
                $existing = $check_stmt->fetch();
                if (!$existing) {
                    $message = 'Anda belum melakukan absen masuk hari ini!';
                    $message_type = 'error';
                } else {
                    // Check if already checked out
                    $check_stmt = $conn->prepare("SELECT id_absensi FROM absensi 
                                                 WHERE id_absensi = :id 
                                                 AND waktu_pulang IS NOT NULL");
                    $check_stmt->execute(['id' => $existing['id_absensi']]);
                    
                    if ($check_stmt->fetch()) {
                        $message = 'Anda sudah melakukan absen keluar hari ini!';
                        $message_type = 'error';
                    } else {
                        // Update record with checkout time
                        $stmt = $conn->prepare("UPDATE absensi 
                                               SET waktu_pulang = CURRENT_TIMESTAMP,
                                                   keterangan = COALESCE(:keterangan, keterangan)
                                               WHERE id_absensi = :id");
                        $stmt->execute([
                            'id' => $existing['id_absensi'],
                            'keterangan' => $keterangan ?: null
                        ]);
                        
                        $message = 'Absen keluar berhasil!';
                        $message_type = 'success';
                    }
                }
            }
        } catch(PDOException $e) {
            $message = 'Terjadi kesalahan: ' . htmlspecialchars($e->getMessage());
            $message_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Absensi - InLET</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0d6efd;
            --gray: #6c757d;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        .absen-container {
            min-height: calc(100vh - 80px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            margin-top: 80px;
        }

        .absen-box {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
        }

        .absen-box h1 {
            color: var(--primary);
            margin-bottom: 0.5rem;
            text-align: center;
            font-size: 2rem;
            font-weight: 700;
        }

        .absen-box p {
            color: var(--gray);
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        }

        .form-group textarea {
            min-height: 80px;
            resize: vertical;
        }

        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn-absen {
            flex: 1;
            padding: 1rem;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .btn-masuk {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-masuk:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-keluar {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .btn-keluar:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(245, 87, 108, 0.4);
        }

        .message {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .required {
            color: #dc3545;
        }

        @media (max-width: 576px) {
            .absen-box {
                padding: 2rem 1.5rem;
            }

            .btn-group {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <div class="absen-container">
        <div class="absen-box">
            <h1>Form Absensi</h1>
            <p>Silakan pilih mahasiswa dan lakukan absensi</p>

            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="absenForm">
                <div class="form-group">
                    <label for="id_mhs">Mahasiswa <span class="required">*</span></label>
                    <select id="id_mhs" name="id_mhs" required>
                        <option value="">Pilih Mahasiswa</option>
                        <?php foreach ($mahasiswa_list as $mhs): ?>
                            <option value="<?php echo $mhs['id_mhs']; ?>">
                                <?php echo htmlspecialchars($mhs['nama']); ?> 
                                (<?php echo ucfirst($mhs['status']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="keterangan">Keterangan (opsional)</label>
                    <textarea id="keterangan" name="keterangan" 
                              placeholder="Keterangan absensi (opsional)"></textarea>
                </div>

                <input type="hidden" name="tipe_absen" id="tipe_absen" value="">

                <div class="btn-group">
                    <button type="button" class="btn-absen btn-masuk" onclick="setTipeAbsen('masuk')">
                        Absen Masuk
                    </button>
                    <button type="button" class="btn-absen btn-keluar" onclick="setTipeAbsen('keluar')">
                        Absen Keluar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function setTipeAbsen(tipe) {
            document.getElementById('tipe_absen').value = tipe;
            
            // Validasi form
            const id_mhs = document.getElementById('id_mhs').value;
            
            if (!id_mhs) {
                alert('Mahasiswa harus dipilih!');
                document.getElementById('id_mhs').focus();
                return false;
            }
            
            // Submit form
            document.getElementById('absenForm').submit();
        }
    </script>
</body>

</html>
