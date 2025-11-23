<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/database.php';

$message = '';
$message_type = ''; // 'success' or 'error'

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $institusi = trim($_POST['institusi'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $pesan = trim($_POST['pesan'] ?? '');
    
    // Validation
    if (empty($nama)) {
        $message = 'Nama harus diisi!';
        $message_type = 'error';
    } elseif (empty($email)) {
        $message = 'Email harus diisi!';
        $message_type = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Format email tidak valid!';
        $message_type = 'error';
    } else {
        try {
            $conn = getDBConnection();
            
            // Create table if not exists
            try {
                // Try to alter table if exists to make pesan nullable
                try {
                    $conn->exec("ALTER TABLE buku_tamu ALTER COLUMN pesan DROP NOT NULL");
                } catch (PDOException $e) {
                    // Column might already be nullable or table doesn't exist yet
                }
                
                $conn->exec("CREATE TABLE IF NOT EXISTS buku_tamu (
                    id_buku_tamu SERIAL PRIMARY KEY,
                    nama VARCHAR(150) NOT NULL,
                    email VARCHAR(150) NOT NULL,
                    institusi VARCHAR(200),
                    no_hp VARCHAR(50),
                    pesan VARCHAR(2000),
                    created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
                    is_read BOOLEAN DEFAULT false,
                    admin_response VARCHAR(2000)
                )");
                
                // Create indexes
                $conn->exec("CREATE INDEX IF NOT EXISTS idx_buku_tamu_created_at ON buku_tamu(created_at DESC)");
                $conn->exec("CREATE INDEX IF NOT EXISTS idx_buku_tamu_is_read ON buku_tamu(is_read)");
                $conn->exec("CREATE INDEX IF NOT EXISTS idx_buku_tamu_email ON buku_tamu(email)");
            } catch (PDOException $e) {
                // Table might already exist, continue
            }
            
            // Insert data
            $stmt = $conn->prepare("INSERT INTO buku_tamu (nama, email, institusi, no_hp, pesan) 
                                   VALUES (:nama, :email, :institusi, :no_hp, :pesan)");
            $stmt->execute([
                ':nama' => $nama,
                ':email' => $email,
                ':institusi' => $institusi ?: null,
                ':no_hp' => $no_hp ?: null,
                ':pesan' => $pesan
            ]);
            
            if (!empty($pesan)) {
                $message = 'Terima kasih! Pesan Anda telah terkirim. Kami akan segera menindaklanjuti.';
            } else {
                $message = 'Terima kasih! Data kehadiran Anda telah tercatat.';
            }
            $message_type = 'success';
            
            // Clear form data after successful submission
            $_POST = [];
        } catch (PDOException $e) {
            $message = 'Terjadi kesalahan saat menyimpan pesan. Silakan coba lagi.';
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
    <title>Buku Tamu - InLET Polinema</title>
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-attachment: fixed;
            min-height: 100vh;
            padding-top: 130px !important;
        }

        .guestbook-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 1rem 1rem 3rem;
            position: relative;
            z-index: 1;
        }

        .guestbook-header {
            text-align: center;
            color: white;
            margin-bottom: 1.5rem;
            padding: 0.5rem 0 1rem;
            position: relative;
            z-index: 1;
        }

        .guestbook-header h1 {
            font-size: 2.75rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .guestbook-header h1 i {
            font-size: 3rem;
            filter: drop-shadow(0 4px 15px rgba(0, 0, 0, 0.2));
        }

        .guestbook-header p {
            font-size: 1.15rem;
            opacity: 0.95;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .guestbook-card {
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.4);
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .guestbook-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
        }

        .form-label {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-label .required {
            color: #ef4444;
        }

        .form-control,
        .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            padding: 0.875rem 1.25rem;
            font-size: 1rem;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
            outline: none;
            transform: translateY(-1px);
        }

        .form-control:hover {
            border-color: #cbd5e1;
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
            line-height: 1.6;
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 14px;
            padding: 1rem 2.5rem;
            font-size: 1.15rem;
            font-weight: 700;
            transition: all 0.3s;
            width: 100%;
            margin-top: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.5);
            color: white;
        }

        .btn-submit:active {
            transform: translateY(-1px);
        }

        .btn-submit i {
            font-size: 1.3rem;
        }

        .message {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .message.success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .message.error {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .message i {
            font-size: 1.25rem;
        }

        .info-box {
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
            border-left: 5px solid #667eea;
            border-radius: 16px;
            padding: 1.25rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.15);
        }

        .info-box h5 {
            color: #4338ca;
            font-weight: 700;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.1rem;
        }

        .info-box h5 i {
            font-size: 1.3rem;
        }

        .info-box p {
            color: #4c1d95;
            margin: 0;
            font-size: 1rem;
            line-height: 1.6;
        }

        .input-group-icon {
            position: relative;
        }

        .input-group-icon i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            z-index: 10;
        }

        .input-group-icon .form-control {
            padding-left: 2.75rem;
        }

        @media (max-width: 768px) {
            body {
                padding-top: 110px;
            }

            .guestbook-container {
                padding: 0.75rem 1rem 2rem;
            }

            .guestbook-header {
                margin-bottom: 1.25rem;
                padding: 0.5rem 0 0.75rem;
            }

            .guestbook-header h1 {
                font-size: 2rem;
                margin-bottom: 0.5rem;
            }

            .guestbook-header h1 i {
                font-size: 2.25rem;
            }

            .guestbook-header p {
                font-size: 0.95rem;
            }

            .guestbook-card {
                padding: 1.75rem 1.25rem;
                border-radius: 20px;
            }

            .info-box {
                padding: 1rem;
                margin-bottom: 1.5rem;
            }
        }

        .form-floating {
            margin-bottom: 1.25rem;
        }

        .form-floating > label {
            color: #64748b;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="guestbook-container">
        <div class="guestbook-header">
            <h1><i class="ri-book-open-line"></i> Buku Tamu</h1>
            <p>Berikan saran, kritik, atau pesan untuk Lab InLET</p>
        </div>

        <div class="guestbook-card">
            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>">
                    <i class="<?php echo $message_type === 'success' ? 'ri-checkbox-circle-fill' : 'ri-error-warning-fill'; ?>"></i>
                    <span><?php echo htmlspecialchars($message); ?></span>
                </div>
            <?php endif; ?>

            <div class="info-box">
                <h5><i class="ri-information-line"></i> Informasi</h5>
                <p>Form ini dapat digunakan untuk <strong>daftar hadir pengunjung</strong> atau mengirim <strong>pesan/saran/kritik</strong> untuk Lab InLET. Isi minimal nama dan email untuk daftar hadir, atau tambahkan pesan jika ingin menyampaikan sesuatu. Data akan langsung diterima oleh admin Lab InLET.</p>
            </div>

            <form method="POST" action="" id="guestbookForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nama" class="form-label">
                            Nama <span class="required">*</span>
                        </label>
                        <div class="input-group-icon">
                            <i class="ri-user-line"></i>
                            <input type="text" 
                                   class="form-control" 
                                   id="nama" 
                                   name="nama" 
                                   placeholder="Masukkan nama lengkap"
                                   value="<?php echo htmlspecialchars($_POST['nama'] ?? ''); ?>"
                                   required>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">
                            Email <span class="required">*</span>
                        </label>
                        <div class="input-group-icon">
                            <i class="ri-mail-line"></i>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   placeholder="nama@email.com"
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                   required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="institusi" class="form-label">
                            <i class="ri-building-line"></i> Institusi
                        </label>
                        <div class="input-group-icon">
                            <i class="ri-building-line"></i>
                            <input type="text" 
                                   class="form-control" 
                                   id="institusi" 
                                   name="institusi" 
                                   placeholder="Nama institusi/universitas"
                                   value="<?php echo htmlspecialchars($_POST['institusi'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="no_hp" class="form-label">
                            <i class="ri-phone-line"></i> No. HP
                        </label>
                        <div class="input-group-icon">
                            <i class="ri-phone-line"></i>
                            <input type="tel" 
                                   class="form-control" 
                                   id="no_hp" 
                                   name="no_hp" 
                                   placeholder="08xxxxxxxxxx"
                                   value="<?php echo htmlspecialchars($_POST['no_hp'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="pesan" class="form-label">
                        <i class="ri-message-3-line"></i> Pesan (Opsional)
                    </label>
                    <textarea class="form-control" 
                              id="pesan" 
                              name="pesan" 
                              rows="5" 
                              placeholder="Tuliskan pesan, saran, atau kritik Anda di sini (opsional)..."><?php echo htmlspecialchars($_POST['pesan'] ?? ''); ?></textarea>
                    <small class="text-muted">Maksimal 2000 karakter. Kosongkan jika hanya untuk daftar hadir.</small>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="ri-send-plane-fill"></i> Kirim Pesan
                </button>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Character counter for message
        const pesanTextarea = document.getElementById('pesan');
        const maxLength = 2000;
        
        pesanTextarea.addEventListener('input', function() {
            const length = this.value.length;
            const small = this.nextElementSibling;
            
            if (length > maxLength) {
                this.value = this.value.substring(0, maxLength);
                small.textContent = 'Maksimal 2000 karakter (terlampaui)';
                small.style.color = '#ef4444';
            } else {
                small.textContent = `${length}/${maxLength} karakter`;
                small.style.color = length > maxLength * 0.9 ? '#f59e0b' : '#64748b';
            }
        });

        // Form validation
        document.getElementById('guestbookForm').addEventListener('submit', function(e) {
            const nama = document.getElementById('nama').value.trim();
            const email = document.getElementById('email').value.trim();
            const pesan = document.getElementById('pesan').value.trim();
            
            if (!nama || !email) {
                e.preventDefault();
                alert('Mohon lengkapi nama dan email!');
                return false;
            }
            
            if (pesan.length > maxLength) {
                e.preventDefault();
                alert('Pesan terlalu panjang! Maksimal 2000 karakter.');
                return false;
            }
        });
    </script>
</body>

</html>

