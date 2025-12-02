<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';
require_once '../config/settings.php';

$conn = getDBConnection();

// Get page title and subtitle
$page_info = getPageTitle('attendance');
$message = '';
$message_type = '';

// Search NIM
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'search_nim') {
    header('Content-Type: application/json');
    $search_term = trim($_GET['q'] ?? '');

    if (strlen($search_term) < 2) {
        echo json_encode([]);
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT nim as nim, nama, status FROM mahasiswa WHERE CAST(nim AS TEXT) ILIKE :search ORDER BY nim LIMIT 10");
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
    } catch (PDOException $e) {
        echo json_encode([]);
    }
    exit;
}

// Validate NIM
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'validate_nim') {
    header('Content-Type: application/json');
    $nim = trim($_GET['nim'] ?? '');

    if (empty($nim)) {
        echo json_encode(['valid' => false, 'message' => 'Student ID cannot be empty']);
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT nim as nim, nama, status FROM mahasiswa WHERE nim = :nim LIMIT 1");
        $stmt->execute(['nim' => $nim]);
        $mahasiswa = $stmt->fetch();

        if ($mahasiswa) {
            echo json_encode([
                'valid' => true,
                'nama' => $mahasiswa['nama'],
                'status' => $mahasiswa['status']
            ]);
        } else {
            echo json_encode(['valid' => false, 'message' => 'Student ID is not registered']);
        }
    } catch (PDOException $e) {
        echo json_encode(['valid' => false, 'message' => 'An error occurred while validating Student ID']);
    }
    exit;
}

// Process form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = trim($_POST['nim'] ?? '');
    $status = $_POST['status'] ?? '';
    $tipe_absen = $_POST['tipe_absen'] ?? '';
    $keterangan = trim($_POST['keterangan'] ?? '');

    // Validate input
    if (empty($nim)) {
        $message = 'Student ID is required!';
        $message_type = 'error';
    } elseif (empty($status)) {
        $message = 'Status must be selected!';
        $message_type = 'error';
    } elseif (empty($tipe_absen)) {
        $message = 'Attendance type must be selected!';
        $message_type = 'error';
    } else {
        try {
            // Check student
            $check_mhs = $conn->prepare("SELECT nim as nim, nama FROM mahasiswa WHERE nim = :nim LIMIT 1");
            $check_mhs->execute(['nim' => $nim]);
            $mahasiswa = $check_mhs->fetch();

            if (!$mahasiswa) {
                $message = 'Student ID not found in database!';
                $message_type = 'error';
            } else {
                $today = date('Y-m-d');

                if ($tipe_absen === 'masuk') {
                    // Check check-in status
                    $check_stmt = $conn->prepare("SELECT id_absensi FROM absensi 
                                                 WHERE nim = :nim 
                                                 AND tanggal = :tanggal 
                                                 AND waktu_datang IS NOT NULL");
                    $check_stmt->execute([
                        'nim' => $mahasiswa['nim'],
                        'tanggal' => $today
                    ]);

                    if ($check_stmt->fetch()) {
                        $message = 'You have already checked in today!';
                        $message_type = 'error';
                    } else {
                        // Check record
                        $check_stmt = $conn->prepare("SELECT id_absensi FROM absensi 
                                                     WHERE nim = :nim 
                                                     AND tanggal = :tanggal");
                        $check_stmt->execute([
                            'nim' => $mahasiswa['nim'],
                            'tanggal' => $today
                        ]);
                        $existing = $check_stmt->fetch();

                        // Build note
                        $keterangan_full = 'Status: ' . ucfirst($status);
                        if (!empty($keterangan)) {
                            $keterangan_full .= ' | ' . $keterangan;
                        }

                        if ($existing) {
                            // Update record
                            $stmt = $conn->prepare("UPDATE absensi
                                                   SET waktu_datang = CURRENT_TIMESTAMP, 
                                                       keterangan = :keterangan 
                                                   WHERE id_absensi = :id");
                            $stmt->execute([
                                'id' => $existing['id_absensi'],
                                'keterangan' => $keterangan_full
                            ]);
                        } else {
                            // Insert record
                            $stmt = $conn->prepare("INSERT INTO absensi (nim, tanggal, waktu_datang, keterangan)
                                                   VALUES (:nim, :tanggal, CURRENT_TIMESTAMP, :keterangan)");
                            $stmt->execute([
                                'nim' => $mahasiswa['nim'],
                                'tanggal' => $today,
                                'keterangan' => $keterangan_full
                            ]);
                        }

                        $message = 'Check-in successful! Welcome, ' . htmlspecialchars($mahasiswa['nama']) . '.';
                        $message_type = 'success';
                    }
                } elseif ($tipe_absen === 'keluar') {
                    // Check check-in status
                    $check_stmt = $conn->prepare("SELECT id_absensi FROM absensi
                                                 WHERE nim = :nim 
                                                 AND tanggal = :tanggal 
                                                 AND waktu_datang IS NOT NULL");
                    $check_stmt->execute([
                        'nim' => $mahasiswa['nim'],
                        'tanggal' => $today
                    ]);

                    $existing = $check_stmt->fetch();
                    if (!$existing) {
                        $message = 'You have not checked in today!';
                        $message_type = 'error';
                    } else {
                        // Check checkout status
                        $check_stmt = $conn->prepare("SELECT id_absensi FROM absensi
                                                     WHERE id_absensi = :id 
                                                     AND waktu_pulang IS NOT NULL");
                        $check_stmt->execute(['id' => $existing['id_absensi']]);

                        if ($check_stmt->fetch()) {
                            $message = 'You have already checked out today!';
                            $message_type = 'error';
                        } else {
                            // Prepare note
                            $keterangan_full = $keterangan;
                            if (!empty($keterangan)) {
                                $keterangan_full = 'Status: ' . ucfirst($status) . ' | ' . $keterangan;
                            }

                            // Update checkout
                            $stmt = $conn->prepare("UPDATE absensi 
                                                   SET waktu_pulang = CURRENT_TIMESTAMP,
                                                       keterangan = COALESCE(:keterangan, keterangan)
                                                   WHERE id_absensi = :id");
                            $stmt->execute([
                                'id' => $existing['id_absensi'],
                                'keterangan' => $keterangan_full ?: null
                            ]);

                            $message = 'Check-out successful! Thank you, ' . htmlspecialchars($mahasiswa['nama']) . '.';
                            $message_type = 'success';
                        }
                    }
                }
            }
        } catch (PDOException $e) {
            $message = 'Error: ' . htmlspecialchars($e->getMessage());
            $message_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_info['title'] ?: 'Attendance - InLET'); ?></title>
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
                <h1 class="display-4 fw-bold"><?= htmlspecialchars($page_info['title'] ?: 'Attendance Form'); ?></h1>
                <?php if (!empty($page_info['subtitle'])): ?>
                    <p class="lead mt-3"><?= htmlspecialchars($page_info['subtitle']); ?></p>
                <?php else: ?>
                    <p class="lead mt-3">Information And Learning Engineering Technology</p>
                <?php endif; ?>
            </div>
        </section>

        <div class="container my-5">
            <div class="card-surface">
                <?php if ($message): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <i
                            class="<?php echo $message_type === 'success' ? 'ri-checkbox-circle-fill' : 'ri-error-warning-fill'; ?>"></i>
                        <span><?php echo $message; ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="absenForm">
                    <div class="mb-4">
                        <label for="nim" class="form-label">
                            <i class="ri-user-line"></i> Student ID <span class="required">*</span>
                        </label>
                        <div class="input-group-icon">
                            <input type="text" class="form-control" id="nim" name="nim"
                                placeholder="Enter or search Student ID"
                                value="<?php echo htmlspecialchars($_POST['nim'] ?? ''); ?>" autocomplete="off"
                                required>
                            <div id="nim-suggestions" class="nim-suggestions"></div>
                            <div id="nim-validation" class="nim-validation"></div>
                        </div>
                        <small id="nim-help" class="text-muted">Type at least 2 characters to search for Student
                            ID</small>
                    </div>

                    <div class="mb-4">
                        <label for="status" class="form-label">
                            <i class="ri-book-open-line"></i> Status <span class="required">*</span>
                        </label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="">Select Status</option>
                            <option value="lainnya" <?php echo (isset($_POST['status']) && $_POST['status'] === 'lainnya') ? 'selected' : ''; ?>>Regular</option>
                            <option value="magang" <?php echo (isset($_POST['status']) && $_POST['status'] === 'magang') ? 'selected' : ''; ?>>Internship</option>
                            <option value="skripsi" <?php echo (isset($_POST['status']) && $_POST['status'] === 'skripsi') ? 'selected' : ''; ?>>Undergraduate Thesis</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="keterangan" class="form-label">
                            <i class="ri-file-text-line"></i> Additional Notes (Optional)
                        </label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="3"
                            placeholder="Additional notes (optional)"><?php echo htmlspecialchars($_POST['keterangan'] ?? ''); ?></textarea>
                    </div>

                    <input type="hidden" name="tipe_absen" id="tipe_absen" value="">

                    <!-- Divider -->
                    <div class="btn-divider my-4">
                        <span>Select Attendance Type</span>
                    </div>

                    <!-- Button group -->
                    <div class="btn-group-absensi">
                        <button type="button" class="btn-attendance btn-check-in" onclick="setAttendanceType('masuk')">
                            <i class="ri-login-box-line btn-icon"></i>
                            <div>
                                <div class="btn-text">Check In</div>
                            </div>
                        </button>

                        <button type="button" class="btn-attendance btn-check-out"
                            onclick="setAttendanceType('keluar')">
                            <i class="ri-logout-box-line btn-icon"></i>
                            <div>
                                <div class="btn-text">Check Out</div>
                            </div>
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

        // Handle input
        nimInput.addEventListener('input', function () {
            const value = this.value.trim();
            nimValid = false;
            selectedNim = null;

            // Clear timeout
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }

            // Hide elements
            nimSuggestions.innerHTML = '';
            nimSuggestions.style.display = 'none';
            nimValidation.innerHTML = '';
            nimValidation.className = 'nim-validation';

            if (value.length < 2) {
                nimHelp.textContent = 'Type at least 2 characters to search for Student ID';
                return;
            }

            // Debounce
            searchTimeout = setTimeout(() => {
                searchStudentID(value);
            }, 300);
        });

        // Validate on blur
        nimInput.addEventListener('blur', function () {
            const value = this.value.trim();
            if (value.length > 0 && !nimValid) {
                validateStudentID(value);
            }
        });

        // Search function
        function searchStudentID(query) {
            if (query.length < 2) return;

            fetch(`?action=search_nim&q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        displaySuggestions(data);
                    } else {
                        nimSuggestions.innerHTML = '<div class="suggestion-item no-results">No Student ID found</div>';
                        nimSuggestions.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        // Show suggestions
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
                    selectStudentID(item.nim, item.nama);
                });
                nimSuggestions.appendChild(div);
            });
            nimSuggestions.style.display = 'block';
        }

        // Select suggestion
        function selectStudentID(nim, nama) {
            nimInput.value = nim;
            selectedNim = nim;
            nimSuggestions.style.display = 'none';
            validateStudentID(nim);
        }

        // Validate ID
        function validateStudentID(nim) {
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
                            <span>Student ID registered: <strong>${data.nama}</strong></span>
                        `;
                        nimValidation.className = 'nim-validation valid';
                        nimHelp.textContent = `Student ID registered under: ${data.nama}`;
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

        // Close on click outside
        document.addEventListener('click', function (e) {
            if (!nimInput.contains(e.target) && !nimSuggestions.contains(e.target)) {
                nimSuggestions.style.display = 'none';
            }
        });

        // Validate form
        function setAttendanceType(type) {
            const nim = document.getElementById('nim').value.trim();
            const status = document.getElementById('status').value;

            if (!nim) {
                alert('Student ID is required!');
                document.getElementById('nim').focus();
                return false;
            }

            if (!status) {
                alert('Status must be selected!');
                document.getElementById('status').focus();
                return false;
            }

            // Sync validate
            if (!nimValid || selectedNim !== nim) {
                // Show loading
                nimValidation.innerHTML = '<i class="ri-loader-4-line"></i> <span>Validating Student ID...</span>';
                nimValidation.className = 'nim-validation';

                // Validate Student ID
                fetch(`?action=validate_nim&nim=${encodeURIComponent(nim)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.valid) {
                            nimValid = true;
                            selectedNim = nim;
                            document.getElementById('tipe_absen').value = type;
                            document.getElementById('absenForm').submit();
                        } else {
                            nimValid = false;
                            nimValidation.innerHTML = `
                                <i class="ri-error-warning-fill"></i>
                                <span>${data.message}</span>
                            `;
                            nimValidation.className = 'nim-validation invalid';
                            alert('Student ID not registered! Please double-check your Student ID.');
                            document.getElementById('nim').focus();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while validating Student ID. Please try again.');
                    });

                return false;
            }

            // Submit if valid
            document.getElementById('tipe_absen').value = type;
            document.getElementById('absenForm').submit();
        }

        // Add button feedback
        document.querySelectorAll('.btn-attendance').forEach(button => {
            button.addEventListener('mousedown', function () {
                this.style.transform = 'translateY(-1px) scale(0.99)';
            });

            button.addEventListener('mouseup', function () {
                this.style.transform = 'translateY(-4px) scale(1.02)';
            });

            button.addEventListener('mouseleave', function () {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    </script>
</body>

</html>