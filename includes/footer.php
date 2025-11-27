<footer class="footer bg-dark text-white py-5 mt-5"
    style="width: 100%; display: block; position: relative; z-index: 1;">
    <div class="container-fluid px-5">
        <div class="row align-items-center gy-3">

            <!-- Logo dan Deskripsi -->
            <div class="col-lg-4 col-md-6 text-center text-md-start">
                <a href="#" class="d-inline-flex align-items-center mb-2">
                    <img src="assets/logoPutih.png" alt="Logo" width="120" class="me-1">
                </a>
                <p class="text-secondary small mb-0">Information and Learning Engineering Technology</p>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-4 col-md-6 text-center">
                <ul class="list-inline mb-2 footer-links">
                    <li class="list-inline-item mx-2"><a href="index.php">Home</a></li>
                    <li class="list-inline-item mx-2"><a href="research.php">Research</a></li>
                    <li class="list-inline-item mx-2"><a href="member.php">Member</a></li>
                    <li class="list-inline-item mx-2"><a href="news.php">News</a></li>
                </ul>
            </div>

            <!-- Kontak -->
            <div class="col-lg-4 col-md-12 text-center text-md-end small text-secondary">
                <p class="mb-1"><i class="bi bi-envelope-fill me-2"></i>info@inlet.edu</p>
                <p class="mb-1"><i class="bi bi-phone-fill me-2"></i>(+62) 823 328 645</p>
                <p class="mb-0"><i class="bi bi-geo-alt-fill me-2"></i>Malang, East Java</p>
            </div>
        </div>

        <hr class="border-secondary my-3">

        <!-- Copyright -->
        <div class="text-center small text-secondary">
            &copy; 2025 InLET - Information and Learning Engineering Technology
        </div>
    </div>
    <style>
        html {
            height: 100%;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
            padding: 0;
        }

        main {
            flex: 1 0 auto;
            min-height: 0;
        }

        footer {
            flex-shrink: 0;
            margin-top: auto;
            width: 100%;
            position: relative;
        }

        .min-vh-100 {
            min-height: 100vh !important;
        }

        .flex-grow-1 {
            flex-grow: 1 !important;
        }

        .d-flex {
            display: flex !important;
        }

        .flex-column {
            flex-direction: column !important;
        }

        .site-footer {
            background: #0d1720;
            color: #cfd8e3;
        }

        .site-footer a {
            color: #cbd5e1;
            text-decoration: none;
            transition: color 160ms ease-in-out, opacity 160ms ease-in-out;
        }

        .site-footer a:hover {
            color: #ffffff;
            text-decoration: none;
        }

        .site-footer .footer-tagline {
            color: #9aa4b2;
        }

        .site-footer .footer-links a {
            color: #aeb8c3;
            font-weight: 600;
            padding: 4px 6px;
        }

        .site-footer .footer-links .list-inline-item {
            display: inline-block;
        }

        .site-footer .socials a {
            font-size: 1.05rem;
            opacity: 0.92;
        }

        .site-footer hr {
            border-color: rgba(255, 255, 255, 0.06);
        }

        @media (max-width: 576px) {
            .site-footer .footer-links {
                margin-top: 8px;
            }

            .site-footer .d-flex {
                gap: 8px;
            }
        }
    </style>