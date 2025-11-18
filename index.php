<?php
// Dummy
$riset = [];
for ($i = 1; $i <= 25; $i++) {
    $riset[] = [
        "judul" => "Bidang Riset ke-$i",
        "deskripsi" => "Deskripsi lengkap bidang riset nomor $i yang menjelaskan fokus penelitian dan kontribusinya."
    ];
}

$team = [
    ["nama" => "Dr. Andika Putra", "jabatan" => "Lead Research Engineer"],
    ["nama" => "Siti Rahma", "jabatan" => "AI Specialist"],
    ["nama" => "Bima Pratama", "jabatan" => "Software Architect"],
];

$partners = [
    ["logo" => "https://picsum.photos/200/100"],
    ["logo" => "https://picsum.photos/200/99"],
    ["logo" => "https://picsum.photos/200/98"],
];

$gallery = [];
for ($i = 1; $i <= 20; $i++) {
    $w = rand(300, 450);
    $h = rand(250, 500);
    $gallery[] = [
        "img" => "https://picsum.photos/$w/$h?random=$i"
    ];
}

// Pagination Riset
$limit = 9;
$page = isset($_GET["page"]) ? (int) $_GET["page"] : 1;
$start = ($page - 1) * $limit;

$total_items = count($riset);
$total_pages = ceil($total_items / $limit);

$current_riset = array_slice($riset, $start, $limit);

// Pagination GALERI
$gallery_limit = 12;
$gallery_page = isset($_GET["gpage"]) ? (int) $_GET["gpage"] : 1;
$gallery_start = ($gallery_page - 1) * $gallery_limit;

$gallery_total_items = count($gallery);
$gallery_total_pages = ceil($gallery_total_items / $gallery_limit);

$current_gallery = array_slice($gallery, $gallery_start, $gallery_limit);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Home - Information & Learning Engineering Technology</title>

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style-home.css">
    <link rel="stylesheet" href="css/style-header.css">
    <link rel="stylesheet" href="css/style-footer.css">
</head>

<body>
    <!-- HEADER -->
    <?php include 'includes/header.php'; ?>

    <!-- HERO -->
    <section class="hero d-flex align-items-center" id="home">
        <div class="container text-center text-white">
            <h1 class="display-4 fw-bold">InLET - Information And Learning Engineering Technology</h1>
            <p class="lead mt-3">Transforming the future of language learning through advanced engineering.</p>
        </div>
    </section>

    <!-- RISET -->
    <section class="py-5" id="riset">
        <div class="container">
            <div class="section-title">
                <h2>Riset Kami</h2>
                <p>Fokus penelitian InLET dalam pengembangan teknologi pembelajaran.</p>
            </div>
            <p class="text-center">Kami mengembangkan teknologi pembelajaran berbasis AI, sistem adaptif, hingga solusi
                digital modern.</p>
        </div>
    </section>

    <!-- BIDANG RISET (Pagination) -->
    <section class="py-5 bg-light" id="bidang">
        <div class="container">
            <div class="section-title">
                <h2>Bidang Riset</h2>
                <p>Daftar bidang riset</p>
            </div>

            <div class="row g-4">
                <?php foreach ($current_riset as $item): ?>
                    <div class="col-md-4">
                        <div class="p-4 bg-white shadow-sm rounded">
                            <h5 class="fw-bold text-primary"><?= $item["judul"] ?></h5>
                            <p><?= $item["deskripsi"] ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- PAGINATION -->
            <nav class="mt-4">
                <ul class="pagination justify-content-center">

                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page - 1 ?>#bidang">Prev</a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>#bidang"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?>#bidang">Next</a>
                        </li>
                    <?php endif; ?>

                </ul>
            </nav>

        </div>
    </section>

    <!-- TEAM -->
    <section class="py-5" id="team">
        <div class="container">
            <div class="section-title">
                <h2>Tim Ahli</h2>
                <p>Para pakar yang berada di belakang inovasi kami.</p>
            </div>

            <!-- Swiper Slider -->
            <div class="swiper teamSwiper">
                <div class="swiper-wrapper">

                    <?php foreach ($team as $t): ?>
                        <div class="swiper-slide">
                            <div class="p-4 bg-white shadow-sm rounded text-center">
                                <div class="rounded-circle bg-primary text-white d-inline-flex justify-content-center align-items-center mb-3"
                                    style="width:80px;height:80px;font-size:28px;">
                                    <?= substr($t["nama"], 0, 1) ?>
                                </div>
                                <h5 class="fw-bold"><?= $t["nama"] ?></h5>
                                <p class="text-primary"><?= $t["jabatan"] ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>

                <!-- Navigation -->
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>

            </div>
        </div>
    </section>


    <!-- PARTNER -->
    <section class="py-5 bg-light" id="partner">
        <div class="container">
            <div class="section-title">
                <h2>Partner Kami</h2>
                <p>Kolaborasi bersama institusi akademik dan industri.</p>
            </div>

            <div class="row justify-content-center g-4">
                <?php foreach ($partners as $p): ?>
                    <div class="col-md-2 col-4 text-center">
                        <img src="<?= $p["logo"] ?>" class="img-fluid rounded shadow-sm" alt="partner">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- GALLERY MASONRY -->
    <section class="py-5" id="gallery">
        <div class="container">
            <div class="section-title">
                <h2>Gallery</h2>
                <p>Inspirasi dan dokumentasi kegiatan InLET</p>
            </div>

            <div class="masonry">
                <?php foreach ($current_gallery as $g): ?>
                    <div class="masonry-item">
                        <img src="<?= $g["img"] ?>" alt="gallery">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <!-- PAGINATION GALLERY -->
    <nav class="mt-4">
        <ul class="pagination justify-content-center">

            <?php if ($gallery_page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?gpage=<?= $gallery_page - 1 ?>#gallery">Prev</a>
                </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $gallery_total_pages; $i++): ?>
                <li class="page-item <?= $i == $gallery_page ? 'active' : '' ?>">
                    <a class="page-link" href="?gpage=<?= $i ?>#gallery"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($gallery_page < $gallery_total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?gpage=<?= $gallery_page + 1 ?>#gallery">Next</a>
                </li>
            <?php endif; ?>

        </ul>
    </nav>


    <!-- FOOTER -->
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script>
        var swiper = new Swiper(".teamSwiper", {
            slidesPerView: 3,
            spaceBetween: 30,
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            breakpoints: {
                0: { slidesPerView: 1 },
                576: { slidesPerView: 2 },
                992: { slidesPerView: 3 }
            }
        });
    </script>

</body>

</html>