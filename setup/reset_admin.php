<?php
/**
 * Script untuk Reset dan Membuat Admin Baru
 * 
 * PERINGATAN: Hapus file ini setelah digunakan untuk keamanan!
 * File ini hanya untuk setup awal atau reset admin.
 */

require_once '../config/database.php';

$message = '';
$message_type = '';
$new_admin = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'reset') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $message = 'Username dan password harus diisi!';
            $message_type = 'error';
        } elseif (strlen($password) < 6) {
            $message = 'Password minimal 6 karakter!';
            $message_type = 'error';
        } elseif ($password !== $confirm_password) {
            $message = 'Password dan konfirmasi password tidak cocok!';
            $message_type = 'error';
        } else {
            try {
                $conn = getDBConnection();
                
                // Hash password
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                // Hapus semua admin yang ada
                $conn->exec("DELETE FROM admin");
                
                // Buat admin baru
                $stmt = $conn->prepare("INSERT INTO admin (username, password_hash, role) VALUES (:username, :password_hash, 'admin') RETURNING id_admin, username, role");
                $stmt->execute([
                    'username' => $username,
                    'password_hash' => $password_hash
                ]);
                
                $new_admin = $stmt->fetch();
                
                $message = 'Admin successfully reset and recreated!';
                $message_type = 'success';
            } catch(PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    }
}

// Get current admin count
try {
    $conn = getDBConnection();
    $stmt = $conn->query("SELECT COUNT(*) as count FROM admin");
    $admin_count = $stmt->fetch()['count'];
} catch(PDOException $e) {
    $admin_count = 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Admin - InLET</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --secondary: #6366f1;
            --primary-dark: #4338ca;
            --dark: #1e293b;
            --light: #f1f5f9;
            --gray: #64748b;
            --danger: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
        }

        .container {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .header .icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--danger), #f87171);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 10px 30px rgba(239, 68, 68, 0.3);
        }

        .header .icon i {
            font-size: 2rem;
            color: white;
        }

        .header h1 {
            color: var(--dark);
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: var(--gray);
            font-size: 0.95rem;
        }

        .warning-box {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-left: 4px solid #f59e0b;
            padding: 1.25rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }

        .warning-box h3 {
            color: #92400e;
            font-size: 1rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .warning-box p {
            color: #78350f;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .message {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
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

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .btn-reset {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--danger) 0%, #f87171 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.05rem;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
        }

        .info-box {
            background: var(--light);
            padding: 1.25rem;
            border-radius: 12px;
            margin-top: 1.5rem;
            border-left: 4px solid var(--primary);
        }

        .info-box h4 {
            color: var(--primary);
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
        }

        .info-box p {
            color: var(--gray);
            font-size: 0.85rem;
            line-height: 1.6;
        }

        .admin-info {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            padding: 1.25rem;
            border-radius: 12px;
            margin-top: 1.5rem;
            border-left: 4px solid var(--primary);
        }

        .admin-info h4 {
            color: var(--primary);
            font-size: 0.95rem;
            margin-bottom: 0.75rem;
        }

        .admin-info .info-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(79, 70, 229, 0.1);
        }

        .admin-info .info-item:last-child {
            border-bottom: none;
        }

        .admin-info .info-label {
            color: var(--gray);
            font-weight: 500;
        }

        .admin-info .info-value {
            color: var(--dark);
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">
                <i class="ri-shield-user-line"></i>
            </div>
            <h1>Reset Admin</h1>
            <p>Buat ulang akun admin baru</p>
        </div>

        <div class="warning-box">
            <h3>
                <i class="ri-error-warning-fill"></i>
                Peringatan!
            </h3>
            <p>
                Tindakan ini akan <strong>menghapus semua admin yang ada</strong> dan membuat admin baru. 
                Make sure you are certain before proceeding!
            </p>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <i class="ri-<?php echo $message_type === 'success' ? 'check' : 'error-warning'; ?>-fill"></i>
                <span><?php echo htmlspecialchars($message); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($new_admin): ?>
            <div class="admin-info">
                <h4><i class="ri-information-line"></i> Admin Successfully Created</h4>
                <div class="info-item">
                    <span class="info-label">Username:</span>
                    <span class="info-value"><?php echo htmlspecialchars($new_admin['username']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Role:</span>
                    <span class="info-value"><?php echo htmlspecialchars($new_admin['role']); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="action" value="reset">
            
            <div class="form-group">
                <label for="username">Username Baru *</label>
                <input type="text" id="username" name="username" 
                       placeholder="Masukkan username" 
                       required autofocus
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password Baru *</label>
                <input type="password" id="password" name="password" 
                       placeholder="Minimal 6 karakter" 
                       required minlength="6">
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" 
                       placeholder="Ulangi password" 
                       required minlength="6">
            </div>
            
            <button type="submit" class="btn-reset" 
                    onclick="return confirm('Are you sure you want to delete all admins and create a new admin? This action cannot be undone!');">
                <i class="ri-refresh-line"></i>
                <span>Reset & Buat Admin Baru</span>
            </button>
        </form>

        <div class="info-box">
            <h4><i class="ri-information-line"></i> Informasi</h4>
            <p>
                Saat ini ada <strong><?php echo $admin_count; ?> admin</strong> dalam database.
                Setelah reset, hanya akan ada 1 admin baru yang Anda buat.
            </p>
        </div>

        <div class="text-center mt-4 pt-4 border-top">
            <a href="../login.php" class="text-primary text-decoration-none fw-semibold">
                <i class="ri-arrow-left-line"></i> Kembali ke Login
            </a>
        </div>
    </div>
</body>
</html>

