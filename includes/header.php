<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<header class="main-header">
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow-sm">
        <div class="container">
            <a href="index.php" class="navbar-brand d-flex align-items-center gap-2">
                <img src="assets/logo.png" alt="InLET Logo" style="height: 42px;">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'index.php') ? 'active text-primary fw-bold' : ''; ?>" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'research.php') ? 'active text-primary fw-bold' : ''; ?>" href="research.php">Research</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'member.php') ? 'active text-primary fw-bold' : ''; ?>" href="member.php">Member</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'news.php') ? 'active text-primary fw-bold' : ''; ?>" href="news.php">News</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>