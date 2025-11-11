<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header>
    <nav>
        <a href="index.php" class="logo">
            <img src="assets/logo.png" alt="Logo">
        </a>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="research.php">Research</a></li>
            <li><a href="member.php">Member</a></li>
            <li><a href="news.php">News</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                    <li><a href="admin/dashboard.php">Dashboard</a></li>
                <?php endif; ?>
                <li><a href="admin/logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
