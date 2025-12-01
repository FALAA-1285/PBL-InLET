<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';

$message = '';
$message_type = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $institusi = trim($_POST['institusi'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $pesan = trim($_POST['pesan'] ?? '');

    // Validation
    if (empty($nama)) {
        $message = 'Name is required!';
        $message_type = 'error';
    } elseif (empty($email)) {
        $message = 'Email is required!';
        $message_type = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email format!';
        $message_type = 'error';
    } elseif (empty($institusi)) {
        $message = 'Institution is required!';
        $message_type = 'error';
    } elseif (empty($no_hp)) {
        $message = 'Phone number is required!';
        $message_type = 'error';
    } else {
        try {
            $conn = getDBConnection();

            // Buku tamu table creation moved to inlet_pbl_clean.sql

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
                $message = 'Thank you! Your message has been sent. We will respond shortly.';
            } else {
                $message = 'Thank you! Your attendance has been recorded.';
            }
            $message_type = 'success';

            // Clear form data after successful submission
            $_POST = [];
        } catch (PDOException $e) {
            $message = 'An error occurred while saving your message. Please try again.';
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
    <title>Guestbook - InLET Polinema</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style-buku-tamu.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <main>
        <section class="hero d-flex align-items-center" id="home">
            <div class="container text-center text-white">
                <h1 class="display-4 fw-bold">Guestbook</h1>
                <p class="lead mt-3">Share your feedback, suggestions, or messages for Lab InLET</p>
            </div>
        </section>

        <div class="container my-5">
            <div class="card-surface">
                <?php if ($message): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <i
                            class="<?php echo $message_type === 'success' ? 'ri-checkbox-circle-fill' : 'ri-error-warning-fill'; ?>"></i>
                        <span><?php echo htmlspecialchars($message); ?></span>
                    </div>
                <?php endif; ?>

                <div class="info-box">
                    <h5><i class="ri-information-line"></i> Information</h5>
                    <p>This form can be used to <strong>register visitor attendance</strong> or send
                        <strong>messages/feedback/suggestions</strong> to Lab InLET. Fill in all required fields (name,
                        institution, email, phone number) and add a message if you want to share something. Data will be
                        received directly by Lab InLET admin.</p>
                </div>

                <form method="POST" action="" id="guestbookForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nama" class="form-label">
                                <i class="ri-user-line"></i> Name <span class="required">*</span>
                            </label>
                            <div class="input-group-icon">
                                <i class="ri-user-line"></i>
                                <input type="text" class="form-control" id="nama" name="nama"
                                    placeholder="Enter your full name"
                                    value="<?php echo htmlspecialchars($_POST['nama'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">
                                <i class="ri-mail-line"></i> Email <span class="required">*</span>
                            </label>
                            <div class="input-group-icon">
                                <i class="ri-mail-line"></i>
                                <input type="email" class="form-control" id="email" name="email"
                                    placeholder="nama@email.com"
                                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="institusi" class="form-label">
                                <i class="ri-building-line"></i> Institution <span class="required">*</span>
                            </label>
                            <div class="input-group-icon">
                                <i class="ri-building-line"></i>
                                <input type="text" class="form-control" id="institusi" name="institusi"
                                    placeholder="Institution/University name"
                                    value="<?php echo htmlspecialchars($_POST['institusi'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="no_hp" class="form-label">
                                <i class="ri-phone-line"></i> Phone Number <span class="required">*</span>
                            </label>
                            <div class="input-group-icon">
                                <i class="ri-phone-line"></i>
                                <input type="tel" class="form-control" id="no_hp" name="no_hp"
                                    placeholder="08xxxxxxxxxx"
                                    value="<?php echo htmlspecialchars($_POST['no_hp'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="pesan" class="form-label">
                            <i class="ri-message-3-line"></i> Message (Optional)
                        </label>
                        <textarea class="form-control" id="pesan" name="pesan" rows="5"
                            placeholder="Write your message, suggestions, or feedback here (optional)..."><?php echo htmlspecialchars($_POST['pesan'] ?? ''); ?></textarea>
                        <small class="text-muted">Maximum 2000 characters. Leave blank if only registering
                            attendance.</small>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="ri-send-plane-fill"></i> Send Message
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
            pesanTextarea.addEventListener('input', function () {
                const length = this.value.length;
                const small = this.nextElementSibling;

                if (length > maxLength) {
                    this.value = this.value.substring(0, maxLength);
                    small.textContent = 'Maximum 2000 characters (exceeded)';
                    small.style.color = '#ef4444';
                } else {
                    small.textContent = `${length}/2000 characters`;
                    small.style.color = length > maxLength * 0.9 ? '#f59e0b' : '#64748b';
                }
            });
        }

        // Form validation
        document.getElementById('guestbookForm').addEventListener('submit', function (e) {
            const nama = document.getElementById('nama').value.trim();
            const email = document.getElementById('email').value.trim();
            const institusi = document.getElementById('institusi').value.trim();
            const no_hp = document.getElementById('no_hp').value.trim();
            const pesan = document.getElementById('pesan').value.trim();

            if (!nama || !email || !institusi || !no_hp) {
                e.preventDefault();
                alert('Please fill in all required fields (name, email, institution, phone number)!');
                return false;
            }

            if (pesan.length > maxLength) {
                e.preventDefault();
                alert('Message is too long! Maximum 2000 characters.');
                return false;
            }
        });
    </script>
</body>

</html>