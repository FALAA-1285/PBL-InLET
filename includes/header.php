<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/settings.php';

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

<link rel="stylesheet" href="<?= $root_base ?>css/inline-cleanup.css">

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
                        <a class="nav-link <?= ($current_page == 'index.php') ? 'active text-primary fw-bold' : ''; ?>"
                            href="<?= $root_base ?>index.php">
                            Home
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'research.php') ? 'active text-primary fw-bold' : ''; ?>"
                            href="<?= $root_base ?>research.php">
                            Research
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'member.php') ? 'active text-primary fw-bold' : ''; ?>"
                            href="<?= $root_base ?>member.php">
                            Members
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'news.php') ? 'active text-primary fw-bold' : ''; ?>"
                            href="<?= $root_base ?>news.php">
                            News
                        </a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= ($current_page == 'servise.php' || $current_page == 'peminjaman.php' || $current_page == 'absen.php' || $current_page == 'buku_tamu.php') ? 'active text-primary fw-bold' : 'fw-500'; ?>"
                            href="#" id="serviceDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Service
                        </a>

                        <ul class="dropdown-menu custom-dropdown shadow-sm border-0" aria-labelledby="serviceDropdown">
                            <li>
                                <a class="dropdown-item <?= ($current_page == 'peminjaman.php') ? 'active text-primary fw-bold' : ''; ?>"
                                    href="<?= $service_base ?>peminjaman.php">
                                    Tool Loan
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item <?= ($current_page == 'absen.php') ? 'active text-primary fw-bold' : ''; ?>"
                                    href="<?= $service_base ?>absen.php">
                                    Attendance
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item <?= ($current_page == 'buku_tamu.php') ? 'active text-primary fw-bold' : ''; ?>"
                                    href="<?= $service_base ?>buku_tamu.php">
                                    Guestbook
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item ms-lg-1">
                        <a class="nav-link btn-admin <?= ($current_page == 'login.php') ? 'active' : ''; ?>"
                            href="<?= $root_base ?>login.php">
                            Admin
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<style>
    html {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    html::-webkit-scrollbar {
        display: none;
    }

    body {
        overflow-y: scroll;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    body::-webkit-scrollbar {
        display: none;
    }

    .navbar {
        transition: all 0.3s ease-in-out;
        font-family: "Poppins", sans-serif;
        background-color: #ffffff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
    }

    .navbar-brand {
        font-weight: 700;
        color: #1f2937 !important;
    }

    .navbar-brand img {
        height: 40px;
        width: auto;
        object-fit: contain;
    }

    .logo-img {
        height: 40px;
        width: auto;
        object-fit: contain;
    }

    .navbar-nav .nav-link {
        color: #1f2937;
        font-weight: 500;
        padding: 0.7rem 1rem;
        font-size: 1rem;
        transition: all 0.25s ease;
        border-radius: 8px;
        margin: 0 0.1rem;
    }

    .navbar-nav .nav-link.fw-500 {
        font-weight: 500;
    }

    .navbar-nav .nav-link:hover {
        color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.04);
        transform: translateY(-1px);
    }

    .navbar-nav .nav-link.active {
        color: #0d6efd !important;
        font-weight: 700 !important;
        background-color: rgba(13, 110, 253, 0.08);
    }

    .fw-bold {
        font-weight: 700 !important;
    }

    .text-primary.fw-bold {
        color: #0d6efd !important;
        font-weight: 700 !important;
    }

    .dropdown-menu {
        background: #ffffff;
        border: 1px solid rgba(15, 23, 42, 0.06);
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.12);
        padding: 0.3rem 0;
        min-width: 12rem;
        border-radius: 12px;
        margin-top: 0.5rem;
        z-index: 1050;
    }

    .custom-dropdown {
        border-radius: 14px;
        padding: 0.6rem 0;
        min-width: 180px;
        animation: fadeSlideDown .25s ease;
    }

    .dropdown-item {
        color: #1f2937;
        padding: 0.55rem 1rem;
        background: transparent;
        font-size: 0.95rem;
        border-radius: 8px;
        transition: all 0.25s ease;
    }

    .dropdown-item:hover {
        background: rgba(13, 110, 253, 0.08);
        color: #0d6efd;
        padding-left: 1.2rem;
    }

    .dropdown-item.active,
    .dropdown-item.active:hover {
        background: rgba(13, 110, 253, 0.15);
        color: #0d6efd !important;
        font-weight: 700;
    }

    @keyframes fadeSlideDown {
        from {
            opacity: 0;
            transform: translateY(-6px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .navbar .dropdown-toggle::after {
        transition: 0.25s ease;
    }

    .navbar .dropdown.show .dropdown-toggle::after {
        transform: rotate(180deg);
    }

    .btn-admin {
        color: #ffffff !important;
        background-color: #0d6efd;
        border: 1.5px solid #0d6efd;
        padding: 0.7rem 1.2rem;
        font-weight: 500;
        font-size: 1rem;
        border-radius: 8px;
        transition: all 0.25s ease;
        margin-left: 0.25rem;
        position: relative;
        overflow: hidden;
        z-index: 1;
    }

    .btn-admin::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.1);
        transform: translateX(-100%);
        transition: transform 0.25s ease;
        z-index: -1;
    }

    .btn-admin:hover {
        color: #ffffff !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2);
    }

    .btn-admin:hover::before {
        transform: translateX(0);
    }

    .btn-admin:active::before {
        background-color: rgba(255, 255, 255, 0.15);
    }

    .btn-admin.active {
        color: #2a0492ff !important;
        background-color: #0b5ed7;
        border-color: #0b5ed7;
        font-weight: 600;
    }

    .btn-admin.active:hover {
        background-color: #0a58ca;
        border-color: #0a58ca;
    }

    .btn-admin.active::before {
        background-color: rgba(255, 255, 255, 0.2);
        transform: translateX(0);
    }

    @media (max-width: 992px) {
        .navbar-nav {
            text-align: center;
        }

        .nav-item {
            margin: 3px 0;
        }

        .navbar-nav .nav-link {
            margin: 2px 0;
            padding: 0.6rem 0.8rem;
        }

        .dropdown-menu {
            position: static;
            box-shadow: none;
            border: none;
            background: transparent;
            padding-left: 0.5rem;
            margin-top: 0;
        }

        .dropdown-item {
            padding-left: 1.25rem;
            border-radius: 0;
        }

        .btn-admin {
            margin-left: 0;
            margin-top: 0.5rem;
            margin-bottom: 0.5rem;
            display: inline-block;
            width: auto;
            max-width: 120px;
            margin-left: auto;
            margin-right: auto;
        }

        .ms-lg-1 {
            margin-left: 0 !important;
        }
    }

    .navbar-toggler {
        border: none;
    }

    .navbar-toggler:focus {
        box-shadow: none;
    }
</style>