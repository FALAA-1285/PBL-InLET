<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
?>

<header>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow-sm">
        <div class="container">

            <!-- Logo -->
            <a href="index.php" class="navbar-brand">
                <img src="assets/logo.png" alt="Logo" style="height: 40px;">
            </a>

            <!-- Toggler (Mobile) -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Menu -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">

                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'index.php') ? 'active text-primary fw-bold' : ''; ?>"
                            href="index.php">Home</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'research.php') ? 'active text-primary fw-bold' : ''; ?>"
                            href="research.php">Research</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'member.php') ? 'active text-primary fw-bold' : ''; ?>"
                            href="member.php">Member</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'news.php') ? 'active text-primary fw-bold' : ''; ?>"
                            href="news.php">News</a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= ($current_page == 'servise.php' || $current_page == 'peminjaman.php') ? 'active text-primary fw-bold' : ''; ?>"
                            href="#" id="serviceDropdown" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            Service
                        </a>
                        <ul class="dropdown-menu custom-dropdown" aria-labelledby="serviceDropdown">
                            <li><a class="dropdown-item <?= ($current_page == 'peminjaman.php') ? 'active text-primary fw-bold' : ''; ?>"
                                    href="peminjaman.php">Peminjaman</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>
