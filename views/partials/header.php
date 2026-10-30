<?php

// Only start session if needed and headers have not yet been sent
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @session_start();
}
require_once __DIR__ . '/../../config/settings.php';

$current_page = basename($_SERVER['PHP_SELF']);
$is_service_page = str_contains($_SERVER['PHP_SELF'], '/service/');
$root_base = $is_service_page ? '../' : '';
$service_base = $is_service_page ? '' : 'service/';

// Get site logo dynamically
$site_logo = getSiteLogo();
// If logo is a relative path, prepend root_base
if (!empty($site_logo) && !preg_match('#^https?://#i', $site_logo)) {
    $site_logo = $root_base . ltrim($site_logo, '/');
}
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<header>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow-sm">
        <div class="container">
            <a href="<?= $root_base ?>index.php" class="navbar-brand">
                <img src="<?= htmlspecialchars($site_logo); ?>" alt="Logo" class="logo-img" onerror="this.src='<?= $root_base ?>assets/logo.png'">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page === 'index.php') ? 'active' : ''; ?>"
                            href="<?= $root_base ?>index.php">
                            Home
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page === 'research.php') ? 'active' : ''; ?>"
                            href="<?= $root_base ?>research.php">
                            Research
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page === 'member.php') ? 'active' : ''; ?>"
                            href="<?= $root_base ?>member.php">
                            Members
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page === 'news.php') ? 'active' : ''; ?>"
                            href="<?= $root_base ?>news.php">
                            News
                        </a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= (in_array($current_page, ['peminjaman.php', 'absen.php', 'buku_tamu.php', 'service.php', 'servise.php'])) ? 'active' : ''; ?>"
                            href="#" id="serviceDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Service
                        </a>

                        <ul class="dropdown-menu custom-dropdown shadow-sm border-0" aria-labelledby="serviceDropdown">
                            <li>
                                <a class="dropdown-item <?= ($current_page === 'peminjaman.php') ? 'active' : ''; ?>"
                                    href="<?= $service_base ?>peminjaman.php">
                                    Tool Loan
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item <?= ($current_page === 'absen.php') ? 'active' : ''; ?>"
                                    href="<?= $service_base ?>absen.php">
                                    Attendance
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item <?= ($current_page === 'buku_tamu.php') ? 'active' : ''; ?>"
                                    href="<?= $service_base ?>buku_tamu.php">
                                    Guestbook
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item ms-lg-1">
                        <a class="nav-link btn-admin <?= ($current_page === 'login.php') ? 'active' : ''; ?>"
                            href="<?= $root_base ?>login.php">
                            Admin
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>
