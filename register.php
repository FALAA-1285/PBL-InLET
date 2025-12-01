<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/auth.php';

$error = '';
$success = '';

// Jika sudah login, redirect
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $asal_institusi = trim($_POST['asal_institusi'] ?? '');
    
    // Validation
    if (empty($username) || empty($password) || empty($nama)) {
        $error = 'Username, password, and name are required!';
    } elseif ($password !== $password_confirm) {
        $error = 'Password and password confirmation do not match!';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long!';
    } else {
        $conn = getDBConnection();
        
        try {
            // Check if username already exists
            $stmt = $conn->prepare("SELECT id_user FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            if ($stmt->fetch()) {
                $error = 'Username already in use!';
            } else {
                // Hash password
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                // Create user
                $stmt = $conn->prepare("INSERT INTO users (username, password_hash, role) VALUES (:username, :password_hash, 'pengunjung') RETURNING id_user");
                $stmt->execute([
                    'username' => $username,
                    'password_hash' => $password_hash
                ]);
                $user_id = $stmt->fetchColumn();
                
                // Create pengunjung profile
                $stmt = $conn->prepare("INSERT INTO pengunjung (id_user, nama, email, asal_institusi) VALUES (:id_user, :nama, :email, :asal_institusi) RETURNING id_pengunjung");
                $stmt->execute([
                    'id_user' => $user_id,
                    'nama' => $nama,
                    'email' => $email ?: null,
                    'asal_institusi' => $asal_institusi ?: null
                ]);
                
                $pengunjung_id = $stmt->fetchColumn();
                
                // Create visitor record
                $stmt = $conn->prepare("INSERT INTO visitor (id_pengunjung, visit_count, first_visit) VALUES (:id_pengunjung, 0, NOW())");
                $stmt->execute(['id_pengunjung' => $pengunjung_id]);
                
                $success = 'Registration successful! Please login.';
            }
        } catch(PDOException $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - InLET</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
        }
        .register-box {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            width: 100%;
        }
        .register-box h1 {
            color: var(--primary);
            margin-bottom: 0.5rem;
            text-align: center;
        }
        .register-box p {
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
            color: var(--dark);
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
        }
        .error-message {
            background: #fee;
            color: #c33;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #c33;
        }
        .success-message {
            background: #d1fae5;
            color: #065f46;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #10b981;
        }
        .btn-register {
            width: 100%;
            padding: 0.75rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-register:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.4);
        }
        .register-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--gray);
        }
        .register-footer a {
            color: var(--primary);
            text-decoration: none;
        }
        .register-footer a:hover {
            text-decoration: underline;
        }
        .required {
            color: #ef4444;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-box">
            <h1>Registrasi Pengunjung</h1>
            <p>Daftar sebagai pengunjung untuk tracking kunjungan</p>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
                <div class="text-center mt-3">
                    <a href="login.php" class="btn-register d-inline-block text-decoration-none">Login Sekarang</a>
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">Username <span class="required">*</span></label>
                        <input type="text" id="username" name="username" required autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password <span class="required">*</span></label>
                        <input type="password" id="password" name="password" required minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <label for="password_confirm">Konfirmasi Password <span class="required">*</span></label>
                        <input type="password" id="password_confirm" name="password_confirm" required minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <label for="nama">Nama Lengkap <span class="required">*</span></label>
                        <input type="text" id="nama" name="nama" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email">
                    </div>
                    
                    <div class="form-group">
                        <label for="asal_institusi">Asal Institusi</label>
                        <input type="text" id="asal_institusi" name="asal_institusi">
                    </div>
                    
                    <button type="submit" class="btn-register">Daftar</button>
                </form>
            <?php endif; ?>
            
            <div class="register-footer">
                <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
            </div>
        </div>
    </div>
</body>
</html>

