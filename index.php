<?php
// index.php (combined: page + AJAX endpoint for gallery)

// -------------------
// Dummy data
// -------------------
$riset = [];
for ($i = 1; $i <= 25; $i++) {
    $riset[] = [
        "judul" => "Research Field #$i",
        "deskripsi" => "A full description of research field number $i, explaining its focus and contributions."
    ];
}

$team = [
    [
        "nama" => "Dr. Andika Putra",
        "jabatan" => "Lead Research Engineer",
        "deskripsi" => "Memimpin roadmap riset strategis dan kolaborasi lintas disiplin."
    ],
    [
        "nama" => "Siti Rahma",
        "jabatan" => "AI Specialist",
        "deskripsi" => "Mengembangkan model AI untuk solusi pembelajaran adaptif."
    ],
    [
        "nama" => "Bima Pratama",
        "jabatan" => "Software Architect",
        "deskripsi" => "Merancang arsitektur platform pembelajaran berperforma tinggi."
    ],
];

$partners = [
    ["logo" => "https://picsum.photos/200/100"],
    ["logo" => "https://picsum.photos/201/100"],
    ["logo" => "https://picsum.photos/202/100"],
    ["logo" => "https://picsum.photos/203/100"],
    ["logo" => "https://picsum.photos/204/100"],
    ["logo" => "https://picsum.photos/205/100"],
];

// Full gallery dataset (dummy 100 images)
$all_gallery = [];
for ($i = 1; $i <= 100; $i++) {
    $w = rand(300, 450);
    $h = rand(250, 500);
    // Use seed for stable images across requests
    $all_gallery[] = [
        "img" => "https://picsum.photos/seed/" . $i . "/{$w}/{$h}"
    ];
}

// -------------------
// AJAX endpoint: load more gallery
// Usage: index.php?action=load_gallery&page=N
// -------------------
if (isset($_GET['action']) && $_GET['action'] === 'load_gallery') {
    $limit = 12;
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $start = ($page - 1) * $limit;
    $data = array_slice($all_gallery, $start, $limit);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_values($data));
    exit;
}

// -------------------
// Page rendering variables
// -------------------

// Research pagination (use distinct param rpage)
$research_limit = 9;
$rpage = isset($_GET["rpage"]) ? max(1, (int) $_GET["rpage"]) : 1;
$rstart = ($rpage - 1) * $research_limit;

$total_items = count($riset);
$total_pages = ceil($total_items / $research_limit);

$current_riset = array_slice($riset, $rstart, $research_limit);

// Pagination GALERI
$gallery_limit = 12;
$gallery_page = isset($_GET["gpage"]) ? (int) $_GET["gpage"] : 1;
$gallery_start = ($gallery_page - 1) * $gallery_limit;

// use $all_gallery (the real dataset)
$gallery_total_items = count($all_gallery);
$gallery_total_pages = ceil($gallery_total_items / $gallery_limit);

$current_gallery = array_slice($all_gallery, $gallery_start, $gallery_limit);

// initial server-side items for page 1 (used in the HTML render)
$gallery_init = array_slice($all_gallery, 0, $gallery_limit);

function getInitials($name) {
    $words = preg_split('/\s+/', trim($name));
    $initials = '';
    foreach ($words as $word) {
        if ($word === '') {
            continue;
        }
        $initials .= mb_substr($word, 0, 1);
        if (mb_strlen($initials) >= 2) {
            break;
        }
    }
    return strtoupper($initials);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home - Information & Learning Engineering Technology</title>

    <!-- CSS libs -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style-home.css">
    <link rel="stylesheet" href="css/style-header.css">
    <link rel="stylesheet" href="css/style-footer.css">
    <link rel="stylesheet" href="css/style-member.css">
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

    <!-- RESEARCH -->
    <section class="py-5" id="riset">
        <div class="container">
            <div class="section-title">
                <h2>Our Research</h2>
                <p>InLET's research focuses on developing learning technology.</p>
            </div>
            <p class="text-center">We develop AI-based learning technology, adaptive systems, and modern digital solutions.</p>
        </div>
    </section>

    <!-- RESEARCH FIELDS WITH PAGINATION -->
    <section class="py-5 bg-light" id="bidang">
        <div class="container">
            <div class="section-title">
                <h2>Research Fields</h2>
                <p>List of research fields</p>
            </div>

            <div class="row g-4">
                <?php foreach ($current_riset as $item): ?>
                    <div class="col-md-4">
                        <div class="p-4 bg-white shadow-sm rounded">
                            <h5 class="fw-bold text-primary"><?= htmlspecialchars($item["judul"]) ?></h5>
                            <p><?= htmlspecialchars($item["deskripsi"]) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- PAGINATION -->
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($rpage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?rpage=<?= $rpage - 1 ?>#bidang">Previous</a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i == $rpage ? 'active' : '' ?>">
                            <a class="page-link" href="?rpage=<?= $i ?>#bidang"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($rpage < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?rpage=<?= $rpage + 1 ?>#bidang">Next</a>
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
                <h2>Expert Team</h2>
                <p>The experts behind our innovations.</p>
            </div>

            <!-- Swiper Slider -->
            <div class="swiper teamSwiper">
                <div class="swiper-wrapper">
                    <?php foreach ($team as $t): ?>
                        <div class="swiper-slide">
                            <div class="member-card card-surface h-100 text-center">
                                <div class="member-img-wrapper">
                                    <div class="member-initials">
                                        <?= htmlspecialchars(getInitials($t["nama"])) ?>
                                    </div>
                                </div>
                                <div class="member-info">
                                    <h3 class="member-name"><?= htmlspecialchars($t["nama"]) ?></h3>
                                    <div class="member-role"><?= htmlspecialchars($t["jabatan"]) ?></div>
                                    <?php if (!empty($t["deskripsi"])): ?>
                                        <p class="member-desc"><?= htmlspecialchars($t["deskripsi"]) ?></p>
                                    <?php endif; ?>
                                </div>
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

    <!-- PARTNERS -->
    <section class="py-5 bg-light" id="partner">
        <div class="container">
            <div class="section-title">
                <h2>Our Partners</h2>
                <p>Collaboration with academic and industry institutions.</p>
            </div>

            <div class="row justify-content-center g-4">
                <?php foreach ($partners as $p): ?>
                    <div class="col-md-2 col-4 text-center">
                        <img src="<?= htmlspecialchars($p["logo"]) ?>" class="partner-logo img-fluid rounded shadow-sm" alt="partner">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- GALLERY -->
    <section class="py-5" id="gallery">
        <div class="container">
            <div class="section-title">
                <h2>Gallery</h2>
                <p>Documentation of InLET</p>
            </div>

            <div id="pinterest-grid" class="pinterest-grid">
                <!-- initial server-side items (page 1) -->
                <?php foreach ($gallery_init as $g): ?>
                    <div class="pin-item show">
                        <img src="<?= htmlspecialchars($g['img']) ?>" alt="gallery">
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="loader" aria-hidden="true"></div>
        </div>
    </section>

    <!-- FOOTER -->
    <?php include 'includes/footer.php'; ?>

    <!-- JS libs -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <!-- Page scripts -->
    <script>
    // Swiper init (unchanged)
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

    <!-- Masonry + Infinite Scroll (combined & fixed) -->
    <script>
    document.addEventListener("DOMContentLoaded", function () {

        const container = document.getElementById("pinterest-grid");
        const loader = document.getElementById("loader");

        let page = 2; // since we already rendered page 1 server-side
        let isLoading = false;
        let allLoaded = false;
        const gap = 15; // must match visual spacing

        // Calculate columns based on viewport
        function calcColumns() {
            if (window.innerWidth < 576) return 1;
            if (window.innerWidth < 768) return 2;
            return 3;
        }

        // Layout function: waterfall algorithm
        function masonryLayout() {
            const items = Array.from(container.querySelectorAll(".pin-item"));
            const columns = calcColumns();

            // If 1 column, let normal document flow do the job
            if (columns === 1) {
                container.style.height = 'auto';
                items.forEach(item => {
                    item.style.position = '';
                    item.style.transform = '';
                    item.style.left = '';
                    item.style.top = '';
                });
                return;
            }

            // Ensure absolute positioning for waterfall
            items.forEach(item => {
                item.style.position = 'absolute';
            });

            const colHeights = new Array(columns).fill(0);

            items.forEach(item => {
                const minCol = colHeights.indexOf(Math.min(...colHeights));
                const itemWidth = item.offsetWidth;
                const x = minCol * (itemWidth + gap);
                const y = colHeights[minCol];

                item.style.transform = `translate(${x}px, ${y}px)`;
                colHeights[minCol] += item.offsetHeight + gap;
            });

            container.style.height = Math.max(...colHeights) + "px";
        }

        // Append new items (array of {img: url})
        function appendItems(itemsData) {
            if (!itemsData || itemsData.length === 0) return;

            itemsData.forEach(imgObj => {
                const item = document.createElement('div');
                item.className = 'pin-item';
                // create image element
                const image = new Image();
                image.src = imgObj.img;
                image.alt = 'gallery image';
                image.style.display = 'block';

                image.onload = function () {
                    item.appendChild(image);
                    container.appendChild(item);
                    // allow CSS transition to run & then layout
                    requestAnimationFrame(() => {
                        item.classList.add('show');
                        masonryLayout();
                    });
                };

                image.onerror = function () {
                    // still append to avoid blocking flow
                    item.textContent = 'Image failed to load';
                    container.appendChild(item);
                    requestAnimationFrame(() => {
                        item.classList.add('show');
                        masonryLayout();
                    });
                };
            });
        }

        // Load more via AJAX from the same file (action=load_gallery)
        function loadMore() {
            if (isLoading || allLoaded) return;
            isLoading = true;
            loader.style.display = 'block';

            fetch(location.pathname + '?action=load_gallery&page=' + page)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (!Array.isArray(data) || data.length === 0) {
                        allLoaded = true;
                        loader.style.display = 'none';
                        isLoading = false;
                        return;
                    }

                    appendItems(data);
                    page++;
                    isLoading = false;
                    loader.style.display = 'none';
                })
                .catch(err => {
                    console.error('Failed to load gallery:', err);
                    isLoading = false;
                    loader.style.display = 'none';
                });
        }

        // Initial layout after images already in DOM load
        function initialLayoutWhenImagesReady() {
            const imgs = container.querySelectorAll('img');
            let loaded = 0;
            if (imgs.length === 0) {
                masonryLayout();
                return;
            }
            imgs.forEach(img => {
                if (img.complete) {
                    loaded++;
                } else {
                    img.addEventListener('load', () => {
                        loaded++;
                        if (loaded === imgs.length) masonryLayout();
                    });
                    img.addEventListener('error', () => {
                        loaded++;
                        if (loaded === imgs.length) masonryLayout();
                    });
                }
            });
            if (loaded === imgs.length) masonryLayout();
        }

        // Initial call (we already inserted page 1 server-side)
        initialLayoutWhenImagesReady();

        // Infinite scroll with small throttle
        let scrollTimer = null;
        window.addEventListener('scroll', function () {
            if (scrollTimer) clearTimeout(scrollTimer);
            scrollTimer = setTimeout(() => {
                if (isLoading || allLoaded) return;
                // near bottom
                if ((window.innerHeight + window.scrollY) >= (document.body.offsetHeight - 200)) {
                    loadMore();
                }
            }, 120);
        });

        // Re-layout on resize (debounced)
        let resizeTimer = null;
        window.addEventListener('resize', function () {
            if (resizeTimer) clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                masonryLayout();
            }, 120);
        });

        // Observe DOM changes, re-layout after new children inserted
        const mo = new MutationObserver(() => {
            masonryLayout();
        });
        mo.observe(container, { childList: true });

    });
    </script>
</body>
</html>
