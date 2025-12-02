<?php
require_once __DIR__ . '/../config/settings.php';

$is_service_page = str_contains($_SERVER['PHP_SELF'], '/service/');
$root_base = $is_service_page ? '../' : '';

// Get footer settings dynamically
$footer_settings = getFooterSettings();
$contact_info = getContactInfo();

// Get footer logo
$footer_logo = $footer_settings['logo'];
if (!empty($footer_logo) && !preg_match('#^https?://#i', $footer_logo)) {
    $footer_logo = $root_base . ltrim($footer_logo, '/');
} else {
    $footer_logo = $root_base . 'assets/logoPutih.png'; // Default fallback
}
?>
<footer class="footer bg-dark text-white py-5 mt-5">
    <div class="container-fluid px-5">
        <div class="row align-items-center gy-4">

            <div class="col-lg-4 col-md-6 text-center text-md-start">
                <a href="#" class="d-inline-flex align-items-center mb-3">
                    <img src="<?= htmlspecialchars($footer_logo); ?>" alt="InLET Logo" width="130" class="me-2" onerror="this.src='<?= $root_base ?>assets/logoPutih.png'">
                </a>
                <?php if (!empty($footer_settings['title'])): ?>
                    <p class="text-light-soft mb-0"><?= htmlspecialchars($footer_settings['title']); ?></p>
                <?php else: ?>
                    <p class="text-light-soft mb-0">Information and Learning Engineering Technology</p>
                    <p class="text-light-soft mb-0">State Polytechnic of Malang</p>
                <?php endif; ?>
            </div>

            <div class="col-lg-4 col-md-6 text-center">
                <div class="footer-menu-section">
                    <ul class="list-inline mb-3 footer-links">
                        <li class="list-inline-item mx-3"><a href="<?= $root_base ?>index.php">Home</a></li>
                        <li class="list-inline-item mx-3"><a href="<?= $root_base ?>research.php">Research</a></li>
                        <li class="list-inline-item mx-3"><a href="<?= $root_base ?>member.php">Members</a></li>
                        <li class="list-inline-item mx-3"><a href="<?= $root_base ?>news.php">News</a></li>
                    </ul>
                    <ul class="list-inline mb-0 footer-links">
                        <li class="list-inline-item mx-3"><a href="<?= $root_base ?>service/peminjaman.php">Tool
                                Loan</a></li>
                        <li class="list-inline-item mx-3"><a href="<?= $root_base ?>service/absen.php">Attendance</a>
                        </li>
                        <li class="list-inline-item mx-3"><a href="<?= $root_base ?>service/buku_tamu.php">Guestbook</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-4 col-md-12 text-center text-md-end">
                <?php if (!empty($contact_info['email'])): ?>
                    <p class="mb-2 contact-info"><i class="bi bi-envelope-fill me-2"></i><?= htmlspecialchars($contact_info['email']); ?></p>
                <?php endif; ?>
                <?php if (!empty($contact_info['phone'])): ?>
                    <p class="mb-2 contact-info"><i class="bi bi-phone-fill me-2"></i><?= htmlspecialchars($contact_info['phone']); ?></p>
                <?php endif; ?>
                <?php if (!empty($contact_info['address'])): ?>
                    <p class="mb-0 contact-info"><i class="bi bi-geo-alt-fill me-2"></i><?= htmlspecialchars($contact_info['address']); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <hr class="border-light-soft my-4">

        <div class="text-center">
            <?php if (!empty($footer_settings['copyright'])): ?>
                <p class="text-light-soft mb-0"><?= htmlspecialchars($footer_settings['copyright']); ?></p>
            <?php else: ?>
                <p class="text-light-soft mb-0">&copy; <?= date('Y'); ?> InLET - Information and Learning Engineering Technology</p>
            <?php endif; ?>
        </div>
    </div>
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
            padding: 0;
        }

        main {
            flex: 1;
        }

        .footer {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            margin-top: auto;
            width: 100%;
        }

        .text-light-soft {
            color: #94a3b8;
            font-size: 0.95rem;
        }

        .footer-links {
            margin-bottom: 0;
        }

        .footer-links .list-inline-item a {
            color: #cbd5e1;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            padding: 0.4rem 0.6rem;
            border-radius: 6px;
            transition: all 0.2s ease;
            position: relative;
        }

        .footer-links .list-inline-item a:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.08);
            transform: translateY(-1px);
        }

        .footer-links .list-inline-item a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .footer-links .list-inline-item a:hover::after {
            width: 80%;
        }

        .contact-info {
            color: #94a3b8;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.5rem;
        }

        .contact-info i {
            color: #3b82f6;
            font-size: 1rem;
        }

        .border-light-soft {
            border-color: rgba(148, 163, 184, 0.15) !important;
        }

        @media (max-width: 992px) {
            .container-fluid {
                padding-left: 2rem;
                padding-right: 2rem;
            }

            .footer-links .list-inline-item {
                margin: 0.3rem 0.5rem;
            }
        }

        @media (max-width: 768px) {
            .footer {
                padding: 3rem 0;
            }

            .container-fluid {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }

            .footer-links .list-inline-item {
                margin: 0.2rem 0.4rem;
                display: inline-block;
            }

            .footer-links .list-inline-item a {
                padding: 0.3rem 0.5rem;
                font-size: 0.9rem;
            }

            .text-center.text-md-start,
            .text-center.text-md-end {
                text-align: center !important;
            }

            .contact-info {
                justify-content: center;
            }

            .row.align-items-center {
                text-align: center;
            }

            .col-lg-4 {
                margin-bottom: 1.5rem;
            }

            .col-lg-4:last-child {
                margin-bottom: 0;
            }
        }

        @media (max-width: 576px) {
            .container-fluid {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .footer {
                padding: 2.5rem 0;
            }

            .footer-links .list-inline-item {
                display: block;
                margin: 0.4rem 0;
            }

            .footer-links .list-inline-item a {
                display: inline-block;
                padding: 0.4rem 0.8rem;
            }

            .contact-info {
                font-size: 0.9rem;
            }

            .text-light-soft {
                font-size: 0.9rem;
            }
        }
    </style>
</footer>