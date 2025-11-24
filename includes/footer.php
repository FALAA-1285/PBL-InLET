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
        /* ==========================================
   PAGE LAYOUT - FORCE FOOTER TO BOTTOM
   ========================================== */
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

        /* Pastikan main mengambil semua ruang yang tersisa */
        main {
            flex: 1 0 auto;
            min-height: 0;
        }

        /* Footer akan tetap di bawah */
        footer {
            flex-shrink: 0;
            margin-top: auto;
            width: 100%;
            position: relative;
        }

        /* ==========================================
   HERO SECTION
   ========================================== */
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 350px;
            position: relative;
            overflow: hidden;
            padding: 80px 0;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><rect width="100" height="100" fill="rgba(255,255,255,0.05)"/><circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="2"/></svg>');
            opacity: 0.3;
        }

        .hero .container {
            position: relative;
            z-index: 1;
        }

        /* ==========================================
   TEAM SECTION
   ========================================== */
        .team-section {
            padding: 80px 0 100px;
            background: #f8f9fa;
        }

        .section-title h2 {
            color: #2c3e50;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        /* ==========================================
   MEMBER CARDS
   ========================================== */
        .member-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            border: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
        }

        .member-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }

        .member-img-wrapper {
            width: 100%;
            height: 280px;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .member-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .member-card:hover .member-img {
            transform: scale(1.05);
        }

        .member-initials {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.5rem;
            font-weight: 700;
            color: white;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .member-info {
            padding: 24px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .member-name {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .member-role {
            color: #667eea;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .member-desc {
            color: #6c757d;
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 0;
            flex-grow: 1;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .member-footer {
            padding: 0 24px 24px;
            margin-top: auto;
        }

        .btn-email {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 12px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-email:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-email i {
            font-size: 1rem;
        }

        /* ==========================================
   PAGINATION
   ========================================== */
        .pagination-modern {
            margin-bottom: 0;
        }

        .pagination-modern .page-link {
            border: 2px solid #e9ecef;
            color: #667eea;
            margin: 0 4px;
            border-radius: 8px;
            padding: 10px 16px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .pagination-modern .page-link:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
            transform: translateY(-2px);
        }

        .pagination-modern .page-item.active .page-link {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
            color: white;
        }

        .pagination-modern .page-item.disabled .page-link {
            background: #f8f9fa;
            border-color: #e9ecef;
            color: #adb5bd;
        }

        /* ==========================================
   RESPONSIVE DESIGN
   ========================================== */
        @media (max-width: 1200px) {
            .member-img-wrapper {
                height: 260px;
            }
        }

        @media (max-width: 992px) {
            .hero {
                min-height: 300px;
                padding: 60px 0;
            }

            .section-title h2 {
                font-size: 2rem;
            }

            .member-img-wrapper {
                height: 240px;
            }

            .team-section {
                padding: 60px 0 80px;
            }
        }

        @media (max-width: 768px) {
            .team-section {
                padding: 50px 0 60px;
            }

            .hero {
                min-height: 280px;
                padding: 50px 0;
            }

            .hero .display-4 {
                font-size: 2rem;
            }

            .member-img-wrapper {
                height: 280px;
            }

            .member-name {
                font-size: 1.15rem;
            }
        }

        @media (max-width: 576px) {
            .team-section {
                padding: 40px 0 50px;
            }

            .hero {
                padding: 40px 0;
            }

            .member-img-wrapper {
                height: 320px;
            }

            .pagination-modern .page-link {
                padding: 8px 12px;
                font-size: 0.9rem;
            }
        }

        /* ==========================================
   UTILITIES - Bootstrap Helper Classes
   ========================================== */
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

        /* ----------------------------------
   Clean footer styles
   ---------------------------------- */
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