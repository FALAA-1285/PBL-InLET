<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
$is_service_page = str_contains($_SERVER['PHP_SELF'], '/service/');
$root_base = $is_service_page ? '../' : '';
$service_base = $is_service_page ? '' : 'service/';
?>

<header>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow-sm">
        <div class="container">

            <a href="<?= $root_base ?>index.php" class="navbar-brand">
                <img src="<?= $root_base ?>assets/logo.png" alt="Logo" style="height: 40px;">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">

                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'index.php') ? 'active text-primary fw-bold' : ''; ?>"
                            href="<?= $root_base ?>index.php">Home</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'research.php') ? 'active text-primary fw-bold' : ''; ?>"
                            href="<?= $root_base ?>research.php">Research</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'member.php') ? 'active text-primary fw-bold' : ''; ?>"
                            href="<?= $root_base ?>member.php">Member</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'news.php') ? 'active text-primary fw-bold' : ''; ?>"
                            href="<?= $root_base ?>news.php">News</a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= ($current_page == 'servise.php' || $current_page == 'peminjaman.php' || $current_page == 'absen.php' || $current_page == 'buku_tamu.php') ? 'active text-primary fw-bold' : ''; ?>"
                            href="#" id="serviceDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Service
                        </a>

                        <ul class="dropdown-menu custom-dropdown shadow-sm border-0" aria-labelledby="serviceDropdown">
                            <li>
                                <a class="dropdown-item <?= ($current_page == 'peminjaman.php') ? 'active text-primary fw-bold' : ''; ?>"
                                    href="<?= $service_base ?>peminjaman.php">Peminjaman</a>
                            </li>

                            <li>
                                <a class="dropdown-item <?= ($current_page == 'absen.php') ? 'active text-primary fw-bold' : ''; ?>"
                                    href="<?= $service_base ?>absen.php">Absensi</a>
                            </li>

                            <li>
                                <a class="dropdown-item <?= ($current_page == 'buku_tamu.php') ? 'active text-primary fw-bold' : ''; ?>"
                                    href="<?= $service_base ?>buku_tamu.php">Buku Tamu</a>
                            </li>
                        </ul>
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
        padding-top: 80px;
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
    }

    .logo-img {
        height: 40px;
        width: auto;
        object-fit: contain;
    }

    .navbar-nav .nav-link {
        color: #1f2937;
        font-weight: 500;
        padding: 0.7rem 1.1rem;
        font-size: 1rem;
        transition: all 0.25s ease;
    }

    .navbar-nav .nav-link:hover {
        color: #0d6efd;
        transform: translateY(-1px);
    }

    .navbar-nav .nav-link.active {
        color: #0d6efd !important;
        font-weight: 600;
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
        font-weight: 600;
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

    @media (max-width: 992px) {
        .navbar .dropdown-menu {
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

        .navbar-nav {
            text-align: center;
        }

        .nav-item {
            margin: 5px 0;
        }
    }

    .btn {
        font-weight: 500;
        transition: all 0.2s ease-in-out;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    .navbar-toggler {
        border: none;
    }

    .navbar-toggler:focus {
        box-shadow: none;
    }
</style>