<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';

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
    } elseif (empty($institusi)) {
        $message = 'Institusi harus diisi!';
        $message_type = 'error';
    } elseif (empty($no_hp)) {
        $message = 'Nomor HP harus diisi!';
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
                    institusi VARCHAR(200) NOT NULL,
                    no_hp VARCHAR(50) NOT NULL,
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
                ':institusi' => $institusi,
                ':no_hp' => $no_hp,
                ':pesan' => $pesan ?: null
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
    <link rel="stylesheet" href="../css/style-buku-tamu.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <main>
        <section class="hero d-flex align-items-center" id="home">
            <div class="container text-center text-white">
                <h1 class="display-4 fw-bold">Buku Tamu</h1>
                <p class="lead mt-3">Berikan saran, kritik, atau pesan untuk Lab InLET</p>
            </div>
        </section>

        <div class="container my-5">
            <div class="card-surface">
                <?php if ($message): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <i class="<?php echo $message_type === 'success' ? 'ri-checkbox-circle-fill' : 'ri-error-warning-fill'; ?>"></i>
                        <span><?php echo htmlspecialchars($message); ?></span>
                    </div>
                <?php endif; ?>

                <div class="info-box">
                    <h5><i class="ri-information-line"></i> Informasi</h5>
                    <p>Form ini dapat digunakan untuk <strong>daftar hadir pengunjung</strong> atau mengirim <strong>pesan/saran/kritik</strong> untuk Lab InLET. Isi semua field yang wajib (nama, institusi, email, nomor HP) dan tambahkan pesan jika ingin menyampaikan sesuatu. Data akan langsung diterima oleh admin Lab InLET.</p>
                </div>

                <form method="POST" action="" id="guestbookForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nama" class="form-label">
                                <i class="ri-user-line"></i> Nama <span class="required">*</span>
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
                                <i class="ri-mail-line"></i> Email <span class="required">*</span>
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
                                <i class="ri-building-line"></i> Institusi <span class="required">*</span>
                            </label>
                            <div class="input-group-icon">
                                <i class="ri-building-line"></i>
                                <input type="text" 
                                       class="form-control" 
                                       id="institusi" 
                                       name="institusi" 
                                       placeholder="Nama institusi/universitas"
                                       value="<?php echo htmlspecialchars($_POST['institusi'] ?? ''); ?>"
                                       required>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="no_hp" class="form-label">
                                <i class="ri-phone-line"></i> No. HP <span class="required">*</span>
                            </label>
                            <div class="input-group-icon">
                                <i class="ri-phone-line"></i>
                                <input type="tel" 
                                       class="form-control" 
                                       id="no_hp" 
                                       name="no_hp" 
                                       placeholder="08xxxxxxxxxx"
                                       value="<?php echo htmlspecialchars($_POST['no_hp'] ?? ''); ?>"
                                       required>
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
    </main>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Character counter for message
        const pesanTextarea = document.getElementById('pesan');
        const maxLength = 2000;
        
        if (pesanTextarea) {
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
        }

        // Form validation
        document.getElementById('guestbookForm').addEventListener('submit', function(e) {
            const nama = document.getElementById('nama').value.trim();
            const email = document.getElementById('email').value.trim();
            const institusi = document.getElementById('institusi').value.trim();
            const no_hp = document.getElementById('no_hp').value.trim();
            const pesan = document.getElementById('pesan').value.trim();
            
            if (!nama || !email || !institusi || !no_hp) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang wajib (nama, email, institusi, nomor HP)!');
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
