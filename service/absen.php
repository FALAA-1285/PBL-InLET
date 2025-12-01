<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';

$conn = getDBConnection();
$message = '';
$message_type = '';

// Handle AJAX request for NIM search
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'search_nim') {
    header('Content-Type: application/json');
    $search_term = trim($_GET['q'] ?? '');
    
    if (strlen($search_term) < 2) {
        echo json_encode([]);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("SELECT nim, nama, status FROM mahasiswa WHERE nim ILIKE :search ORDER BY nim LIMIT 10");
        $stmt->execute([':search' => '%' . $search_term . '%']);
        $results = $stmt->fetchAll();
        
        $suggestions = [];
        foreach ($results as $row) {
            $suggestions[] = [
                'nim' => $row['nim'],
                'nama' => $row['nama'],
                'status' => $row['status']
            ];
        }
        
        echo json_encode($suggestions);
    } catch(PDOException $e) {
        echo json_encode([]);
    }
    exit;
}

// Handle AJAX request for NIM validation
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'validate_nim') {
    header('Content-Type: application/json');
    $nim = trim($_GET['nim'] ?? '');
    
    if (empty($nim)) {
        echo json_encode(['valid' => false, 'message' => 'NIM tidak boleh kosong']);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("SELECT id_mhs, nama, status FROM mahasiswa WHERE nim = :nim LIMIT 1");
        $stmt->execute(['nim' => $nim]);
        $mahasiswa = $stmt->fetch();
        
        if ($mahasiswa) {
            echo json_encode([
                'valid' => true,
                'nama' => $mahasiswa['nama'],
                'status' => $mahasiswa['status']
            ]);
        } else {
            echo json_encode(['valid' => false, 'message' => 'NIM tidak terdaftar']);
        }
    } catch(PDOException $e) {
        echo json_encode(['valid' => false, 'message' => 'Terjadi kesalahan saat memvalidasi NIM']);
    }
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = trim($_POST['nim'] ?? '');
    $status = $_POST['status'] ?? '';
    $tipe_absen = $_POST['tipe_absen'] ?? ''; // 'masuk' or 'keluar'
    $keterangan = trim($_POST['keterangan'] ?? '');
    
    // Validation
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
            // Check if mahasiswa exists with this NIM
            $check_mhs = $conn->prepare("SELECT id_mhs, nama FROM mahasiswa WHERE nim = :nim LIMIT 1");
            $check_mhs->execute(['nim' => $nim]);
            $mahasiswa = $check_mhs->fetch();
            
            if (!$mahasiswa) {
                $message = 'NIM tidak ditemukan dalam database!';
                $message_type = 'error';
            } else {
                $today = date('Y-m-d');
                
                if ($tipe_absen === 'masuk') {
                    // Check if already checked in today
                    $check_stmt = $conn->prepare("SELECT id_absensi FROM absensi 
                                                 WHERE id_mhs = :id_mhs 
                                                 AND tanggal = :tanggal 
                                                 AND waktu_datang IS NOT NULL");
                    $check_stmt->execute([
                        'id_mhs' => $mahasiswa['id_mhs'],
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
                            'id_mhs' => $mahasiswa['id_mhs'],
                            'tanggal' => $today
                        ]);
                        $existing = $check_stmt->fetch();
                        
                        // Prepare keterangan with status
                        $keterangan_full = 'Status: ' . ucfirst($status);
                        if (!empty($keterangan)) {
                            $keterangan_full .= ' | ' . $keterangan;
                        }
                        
                        if ($existing) {
                            // Update existing record
                            $stmt = $conn->prepare("UPDATE absensi 
                                                   SET waktu_datang = CURRENT_TIMESTAMP, 
                                                       keterangan = :keterangan 
                                                   WHERE id_absensi = :id");
                            $stmt->execute([
                                'id' => $existing['id_absensi'],
                                'keterangan' => $keterangan_full
                            ]);
                        } else {
                            // Insert new record
                            $stmt = $conn->prepare("INSERT INTO absensi (id_mhs, tanggal, waktu_datang, keterangan) 
                                                   VALUES (:id_mhs, :tanggal, CURRENT_TIMESTAMP, :keterangan)");
                            $stmt->execute([
                                'id_mhs' => $mahasiswa['id_mhs'],
                                'tanggal' => $today,
                                'keterangan' => $keterangan_full
                            ]);
                        }
                        
                        $message = 'Absen masuk berhasil! Selamat datang, ' . htmlspecialchars($mahasiswa['nama']) . '.';
                        $message_type = 'success';
                    }
                } elseif ($tipe_absen === 'keluar') {
                    // Check if checked in today
                    $check_stmt = $conn->prepare("SELECT id_absensi FROM absensi 
                                                 WHERE id_mhs = :id_mhs 
                                                 AND tanggal = :tanggal 
                                                 AND waktu_datang IS NOT NULL");
                    $check_stmt->execute([
                        'id_mhs' => $mahasiswa['id_mhs'],
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
                            // Prepare keterangan with status if not already set
                            $keterangan_full = $keterangan;
                            if (!empty($keterangan)) {
                                $keterangan_full = 'Status: ' . ucfirst($status) . ' | ' . $keterangan;
                            }
                            
                            // Update record with checkout time
                            $stmt = $conn->prepare("UPDATE absensi 
                                                   SET waktu_pulang = CURRENT_TIMESTAMP,
                                                       keterangan = COALESCE(:keterangan, keterangan)
                                                   WHERE id_absensi = :id");
                            $stmt->execute([
                                'id' => $existing['id_absensi'],
                                'keterangan' => $keterangan_full ?: null
                            ]);
                            
                            $message = 'Absen keluar berhasil! Terima kasih, ' . htmlspecialchars($mahasiswa['nama']) . '.';
                            $message_type = 'success';
                        }
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
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style-absensi.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <main>
        <section class="hero d-flex align-items-center" id="home">
            <div class="container text-center text-white">
                <h1 class="display-4 fw-bold">Form Absensi</h1>
                <p class="lead mt-3">Silakan isi form untuk melakukan absensi masuk atau keluar</p>
            </div>
        </section>

        <div class="container my-5">
            <div class="card-surface">
                <?php if ($message): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <i class="<?php echo $message_type === 'success' ? 'ri-checkbox-circle-fill' : 'ri-error-warning-fill'; ?>"></i>
                        <span><?php echo $message; ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="absenForm">
                    <div class="mb-4">
                        <label for="nim" class="form-label">
                            <i class="ri-user-line"></i> NIM <span class="required">*</span>
                        </label>
                        <div class="input-group-icon">
                            <input type="text" 
                                   class="form-control" 
                                   id="nim" 
                                   name="nim" 
                                   placeholder="Masukkan atau cari NIM"
                                   value="<?php echo htmlspecialchars($_POST['nim'] ?? ''); ?>"
                                   autocomplete="off"
                                   required>
                            <div id="nim-suggestions" class="nim-suggestions"></div>
                            <div id="nim-validation" class="nim-validation"></div>
                        </div>
                        <small id="nim-help" class="text-muted">Ketik minimal 2 karakter untuk mencari NIM</small>
                    </div>

                    <div class="mb-4">
                        <label for="status" class="form-label">
                            <i class="ri-book-open-line"></i> Status <span class="required">*</span>
                        </label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="">Pilih Status</option>
                            <option value="skripsi" <?php echo (isset($_POST['status']) && $_POST['status'] === 'skripsi') ? 'selected' : ''; ?>>Skripsi</option>
                            <option value="magang" <?php echo (isset($_POST['status']) && $_POST['status'] === 'magang') ? 'selected' : ''; ?>>Magang</option>
                            <option value="lainnya" <?php echo (isset($_POST['status']) && $_POST['status'] === 'lainnya') ? 'selected' : ''; ?>>Lainnya</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="keterangan" class="form-label">
                            <i class="ri-file-text-line"></i> Keterangan (Opsional)
                        </label>
                        <textarea class="form-control" 
                                  id="keterangan" 
                                  name="keterangan" 
                                  rows="3" 
                                  placeholder="Keterangan absensi (opsional)"><?php echo htmlspecialchars($_POST['keterangan'] ?? ''); ?></textarea>
                    </div>

                    <input type="hidden" name="tipe_absen" id="tipe_absen" value="">

                    <div class="btn-group-absensi">
                        <button type="button" class="btn-absen btn-masuk" onclick="setTipeAbsen('masuk')">
                            <i class="ri-login-box-line"></i> Absen Masuk
                        </button>
                        <button type="button" class="btn-absen btn-keluar" onclick="setTipeAbsen('keluar')">
                            <i class="ri-logout-box-line"></i> Absen Keluar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let nimValid = false;
        let searchTimeout = null;
        let selectedNim = null;

        const nimInput = document.getElementById('nim');
        const nimSuggestions = document.getElementById('nim-suggestions');
        const nimValidation = document.getElementById('nim-validation');
        const nimHelp = document.getElementById('nim-help');

        // Search NIM as user types
        nimInput.addEventListener('input', function() {
            const value = this.value.trim();
            nimValid = false;
            selectedNim = null;
            
            // Clear previous timeout
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }

            // Hide suggestions and validation
            nimSuggestions.innerHTML = '';
            nimSuggestions.style.display = 'none';
            nimValidation.innerHTML = '';
            nimValidation.className = 'nim-validation';

            if (value.length < 2) {
                nimHelp.textContent = 'Ketik minimal 2 karakter untuk mencari NIM';
                return;
            }

            // Debounce search
            searchTimeout = setTimeout(() => {
                searchNIM(value);
            }, 300);
        });

        // Validate NIM when user stops typing
        nimInput.addEventListener('blur', function() {
            const value = this.value.trim();
            if (value.length > 0 && !nimValid) {
                validateNIM(value);
            }
        });

        // Search NIM function
        function searchNIM(query) {
            if (query.length < 2) return;

            fetch(`?action=search_nim&q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        displaySuggestions(data);
                    } else {
                        nimSuggestions.innerHTML = '<div class="suggestion-item no-results">Tidak ada NIM yang ditemukan</div>';
                        nimSuggestions.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        // Display suggestions
        function displaySuggestions(suggestions) {
            nimSuggestions.innerHTML = '';
            suggestions.forEach(item => {
                const div = document.createElement('div');
                div.className = 'suggestion-item';
                div.innerHTML = `
                    <strong>${item.nim}</strong>
                    <span>${item.nama}</span>
                    <small>${item.status ? item.status.charAt(0).toUpperCase() + item.status.slice(1) : ''}</small>
                `;
                div.addEventListener('click', () => {
                    selectNIM(item.nim, item.nama);
                });
                nimSuggestions.appendChild(div);
            });
            nimSuggestions.style.display = 'block';
        }

        // Select NIM from suggestions
        function selectNIM(nim, nama) {
            nimInput.value = nim;
            selectedNim = nim;
            nimSuggestions.style.display = 'none';
            validateNIM(nim);
        }

        // Validate NIM
        function validateNIM(nim) {
            if (!nim || nim.length === 0) {
                nimValid = false;
                return;
            }

            fetch(`?action=validate_nim&nim=${encodeURIComponent(nim)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.valid) {
                        nimValid = true;
                        nimValidation.innerHTML = `
                            <i class="ri-checkbox-circle-fill"></i>
                            <span>NIM terdaftar: <strong>${data.nama}</strong></span>
                        `;
                        nimValidation.className = 'nim-validation valid';
                        nimHelp.textContent = `NIM terdaftar atas nama: ${data.nama}`;
                    } else {
                        nimValid = false;
                        nimValidation.innerHTML = `
                            <i class="ri-error-warning-fill"></i>
                            <span>${data.message}</span>
                        `;
                        nimValidation.className = 'nim-validation invalid';
                        nimHelp.textContent = data.message;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    nimValid = false;
                });
        }

        // Close suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!nimInput.contains(e.target) && !nimSuggestions.contains(e.target)) {
                nimSuggestions.style.display = 'none';
            }
        });

        // Form submission validation
        function setTipeAbsen(tipe) {
            const nim = document.getElementById('nim').value.trim();
            const status = document.getElementById('status').value;
            
            if (!nim) {
                alert('NIM harus diisi!');
                document.getElementById('nim').focus();
                return false;
            }

            if (!status) {
                alert('Status harus dipilih!');
                document.getElementById('status').focus();
                return false;
            }

            // Validate NIM synchronously before submission
            if (!nimValid || selectedNim !== nim) {
                // Show loading state
                nimValidation.innerHTML = '<i class="ri-loader-4-line"></i> <span>Memvalidasi NIM...</span>';
                nimValidation.className = 'nim-validation';
                
                // Validate NIM
                fetch(`?action=validate_nim&nim=${encodeURIComponent(nim)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.valid) {
                            nimValid = true;
                            selectedNim = nim;
                            document.getElementById('tipe_absen').value = tipe;
                            document.getElementById('absenForm').submit();
                        } else {
                            nimValid = false;
                            nimValidation.innerHTML = `
                                <i class="ri-error-warning-fill"></i>
                                <span>${data.message}</span>
                            `;
                            nimValidation.className = 'nim-validation invalid';
                            alert('NIM tidak terdaftar! Silakan periksa kembali NIM Anda.');
                            document.getElementById('nim').focus();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat memvalidasi NIM. Silakan coba lagi.');
                    });
                
                return false;
            }
            
            // If NIM is already valid, submit directly
            document.getElementById('tipe_absen').value = tipe;
            document.getElementById('absenForm').submit();
        }
    </script>
</body>

</html>
