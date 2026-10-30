<?php
/**
 * Variables provided by login.php
 *
 * @var mixed $error
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - InLET Polinema</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style-login.css">
</head>

<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <div class="logo">
                    <i class="ri-shield-user-line"></i>
                </div>
                <h1>Login Admin</h1>
                <p>Log in to InLET Polinema CMS</p>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <i class="ri-error-warning-fill"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <i class="ri-user-line"></i>
                        <input type="text" id="username" name="username" placeholder="Enter username" required autofocus
                            value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="ri-lock-password-line"></i>
                        <input type="password" id="password" name="password" placeholder="Enter password" required>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="ri-login-box-line"></i>
                    <span>Login</span>
                </button>
            </form>

            <div class="login-footer">
                <a href="index.php">
                    <i class="ri-arrow-left-line"></i> Back to Home
                </a>
            </div>
        </div>
    </div>
</body>

</html>