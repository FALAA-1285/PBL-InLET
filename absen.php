<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/database.php';

$message = '';
$message_type = ''; // 'success' or 'error'

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = trim($_POST['nim'] ?? '');
    $status = $_POST['status'] ?? '';
    $tipe_absen = $_POST['tipe_absen'] ?? ''; // 'masuk' or 'keluar'
    
    if (empty($nim)) {
        $message = 'NIM harus diisi!';
        $message_type = 'error';
    } elseif (empty($status)) {
        $message = 'Status harus dipilih!';
        $message_type = 'error';
    } elseif (empty($tipe_absen)) {
        $message = 'Tipe absensi harus dipilih!';
        $message_type = 'error';
    } else {
        try {
            $conn = getDBConnection();
            
            // Buat tabel absensi jika belum ada
            try {
                $conn->exec("CREATE TABLE IF NOT EXISTS absensi (
                    id_absensi SERIAL PRIMARY KEY,
                    nim VARCHAR(50) NOT NULL,
                    status VARCHAR(20) NOT NULL,
                    tipe_absen VARCHAR(10) NOT NULL,
                    waktu_absen TIMESTAMP WITH TIME ZONE DEFAULT now(),
                    keterangan VARCHAR(500),
                    CONSTRAINT chk_absen_status CHECK (status IN ('magang','skripsi','regular')),
                    CONSTRAINT chk_absen_tipe CHECK (tipe_absen IN ('masuk','keluar'))
                )");
                
                // Buat index jika belum ada
                $conn->exec("CREATE INDEX IF NOT EXISTS idx_absensi_nim ON absensi(nim)");
                $conn->exec("CREATE INDEX IF NOT EXISTS idx_absensi_waktu ON absensi(waktu_absen)");
                $conn->exec("CREATE INDEX IF NOT EXISTS idx_absensi_tipe ON absensi(tipe_absen)");
            } catch (PDOException $e) {
                // Jika gagal membuat tabel, lanjutkan saja (mungkin sudah ada atau error lain)
            }
            
            // Validasi status
            if (!in_array($status, ['magang', 'skripsi', 'regular'])) {
                $message = 'Status tidak valid!';
                $message_type = 'error';
            } else {
                // Validasi tipe absen
                if (!in_array($tipe_absen, ['masuk', 'keluar'])) {
                    $message = 'Tipe absensi tidak valid!';
                    $message_type = 'error';
                } else {
                    // Cek apakah sudah ada absen masuk untuk hari ini (untuk absen keluar)
                    if ($tipe_absen === 'keluar') {
                        $today_start = date('Y-m-d 00:00:00');
                        $today_end = date('Y-m-d 23:59:59');
                        
                        $check_query = "SELECT id_absensi FROM absensi 
                                       WHERE nim = :nim 
                                       AND tipe_absen = 'masuk' 
                                       AND waktu_absen >= :start 
                                       AND waktu_absen <= :end 
                                       ORDER BY waktu_absen DESC 
                                       LIMIT 1";
                        $check_stmt = $conn->prepare($check_query);
                        $check_stmt->execute([
                            ':nim' => $nim,
                            ':start' => $today_start,
                            ':end' => $today_end
                        ]);
                        
                        $check_in = $check_stmt->fetch();
                        if (!$check_in) {
                            $message = 'Anda belum melakukan absen masuk hari ini!';
                            $message_type = 'error';
                        }
                    }
                    
                    // Jika tidak ada error, simpan absensi
                    if ($message_type !== 'error') {
                        // Cek apakah sudah ada absen masuk hari ini (untuk mencegah double masuk)
                        if ($tipe_absen === 'masuk') {
                            $today_start = date('Y-m-d 00:00:00');
                            $today_end = date('Y-m-d 23:59:59');
                            
                            $check_query = "SELECT id_absensi FROM absensi 
                                           WHERE nim = :nim 
                                           AND tipe_absen = 'masuk' 
                                           AND waktu_absen >= :start 
                                           AND waktu_absen <= :end 
                                           LIMIT 1";
                            $check_stmt = $conn->prepare($check_query);
                            $check_stmt->execute([
                                ':nim' => $nim,
                                ':start' => $today_start,
                                ':end' => $today_end
                            ]);
                            
                            $existing = $check_stmt->fetch();
                            if ($existing) {
                                $message = 'Anda sudah melakukan absen masuk hari ini!';
                                $message_type = 'error';
                            }
                        }
                        
                        // Cek apakah sudah ada absen keluar hari ini (untuk mencegah double keluar)
                        if ($tipe_absen === 'keluar' && $message_type !== 'error') {
                            $today_start = date('Y-m-d 00:00:00');
                            $today_end = date('Y-m-d 23:59:59');
                            
                            $check_query = "SELECT id_absensi FROM absensi 
                                           WHERE nim = :nim 
                                           AND tipe_absen = 'keluar' 
                                           AND waktu_absen >= :start 
                                           AND waktu_absen <= :end 
                                           LIMIT 1";
                            $check_stmt = $conn->prepare($check_query);
                            $check_stmt->execute([
                                ':nim' => $nim,
                                ':start' => $today_start,
                                ':end' => $today_end
                            ]);
                            
                            $existing = $check_stmt->fetch();
                            if ($existing) {
                                $message = 'Anda sudah melakukan absen keluar hari ini!';
                                $message_type = 'error';
                            }
                        }
                        
                        // Simpan absensi jika tidak ada error
                        if ($message_type !== 'error') {
                            $insert_query = "INSERT INTO absensi (nim, status, tipe_absen, waktu_absen) 
                                           VALUES (:nim, :status, :tipe_absen, now())";
                            $insert_stmt = $conn->prepare($insert_query);
                            $insert_stmt->execute([
                                ':nim' => $nim,
                                ':status' => $status,
                                ':tipe_absen' => $tipe_absen
                            ]);
                            
                            $tipe_text = ($tipe_absen === 'masuk') ? 'Masuk' : 'Keluar';
                            $status_text = '';
                            switch ($status) {
                                case 'magang':
                                    $status_text = 'Magang';
                                    break;
                                case 'skripsi':
                                    $status_text = 'Skripsi';
                                    break;
                                case 'regular':
                                    $status_text = 'Selain Magang dan Skripsi';
                                    break;
                            }
                            
                            $message = "Absen $tipe_text berhasil! NIM: $nim, Status: $status_text";
                            $message_type = 'success';
                            
                            // Clear form fields after successful submission
                            $_POST = [];
                        }
                    }
                }
            }
        } catch (PDOException $e) {
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
    <link rel="stylesheet" href="css/style-header.css">
    <link rel="stylesheet" href="css/style-footer.css">
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

        .form-group input[type="text"],
        .form-group select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .form-group input[type="text"]:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        }

        .radio-group {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .radio-option {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .radio-option:hover {
            border-color: var(--primary);
            background-color: rgba(13, 110, 253, 0.05);
        }

        .radio-option input[type="radio"] {
            margin-right: 0.75rem;
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .radio-option label {
            margin: 0;
            cursor: pointer;
            flex: 1;
            color: #333;
            font-weight: 400;
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
    <?php include 'includes/header.php'; ?>

    <div class="absen-container">
        <div class="absen-box">
            <h1>Form Absensi</h1>
            <p>Silakan isi data diri untuk melakukan absensi</p>

            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="absenForm">
                <div class="form-group">
                    <label for="nim">NIM <span class="required">*</span></label>
                    <input type="text" id="nim" name="nim" 
                           value="<?php echo htmlspecialchars($_POST['nim'] ?? ''); ?>" 
                           placeholder="Masukkan NIM" required autofocus>
                </div>

                <div class="form-group">
                    <label>Status <span class="required">*</span></label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" id="status_skripsi" name="status" value="skripsi" 
                                   <?php echo (isset($_POST['status']) && $_POST['status'] === 'skripsi') ? 'checked' : ''; ?> required>
                            <label for="status_skripsi">Skripsi</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" id="status_magang" name="status" value="magang" 
                                   <?php echo (isset($_POST['status']) && $_POST['status'] === 'magang') ? 'checked' : ''; ?>>
                            <label for="status_magang">Magang</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" id="status_regular" name="status" value="regular" 
                                   <?php echo (isset($_POST['status']) && $_POST['status'] === 'regular') ? 'checked' : ''; ?>>
                            <label for="status_regular">Selain Magang dan Skripsi</label>
                        </div>
                    </div>
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

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function setTipeAbsen(tipe) {
            document.getElementById('tipe_absen').value = tipe;
            
            // Validasi form
            const nim = document.getElementById('nim').value.trim();
            const status = document.querySelector('input[name="status"]:checked');
            
            if (!nim) {
                alert('NIM harus diisi!');
                document.getElementById('nim').focus();
                return false;
            }
            
            if (!status) {
                alert('Status harus dipilih!');
                return false;
            }
            
            // Submit form
            document.getElementById('absenForm').submit();
        }
    </script>
</body>

</html>

