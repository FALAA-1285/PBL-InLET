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
    ["logo" => "https://picsum.photos/200/100?random=1"],
    ["logo" => "https://picsum.photos/201/100?random=2"],
    ["logo" => "https://picsum.photos/202/100?random=3"],
    ["logo" => "https://picsum.photos/203/100?random=4"],
    ["logo" => "https://picsum.photos/204/100?random=5"],
    ["logo" => "https://picsum.photos/205/100?random=6"],
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
    $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
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

function getInitials($name)
{
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
</head>

<body>
    <!-- HEADER -->
    <?php include 'includes/header.php'; ?>

    <main>
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
                <p class="text-center">We develop AI-based learning technology, adaptive systems, and modern digital
                    solutions.</p>
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
                        <img src="<?= htmlspecialchars($p["logo"]) ?>" 
                             class="partner-logo img-fluid rounded shadow-sm" 
                             alt="partner"
                             onerror="this.onerror=null; this.src='https://via.placeholder.com/200x100/cccccc/666666?text=Partner';">
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
                        <img src="<?= htmlspecialchars($g['img']) ?>" 
                             alt="gallery" 
                             onerror="handleImageError(this, '<?= htmlspecialchars($g['img']) ?>');">
                    </div>
                <?php endforeach; ?>
            </div>

                <div id="loader" aria-hidden="true"></div>
            </div>
        </section>
    </main>

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

    <!-- Image Error Handler (Global) -->
    <script>
    // Handle image error with retry - must be global for onerror attribute
    function handleImageError(img, originalSrc) {
        let retries = parseInt(img.getAttribute('data-retries') || '0');
        if (retries < 2) {
            img.setAttribute('data-retries', (retries + 1).toString());
            // Retry with a small delay and cache buster
            setTimeout(() => {
                img.src = originalSrc + (originalSrc.includes('?') ? '&' : '?') + 'retry=' + Date.now();
            }, 500);
        } else {
            // Final fallback after retries
            img.onerror = null;
            img.src = 'https://via.placeholder.com/400x300/cccccc/666666?text=Gallery+Image';
        }
    }
    </script>

    <!-- Masonry + Infinite Scroll (combined & fixed) -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const container = document.getElementById("pinterest-grid");
            const gap = 15;
            let page = 2;
            let allLoaded = false;

            function getColumns() {
                if (window.innerWidth < 576) return 1;
                if (window.innerWidth < 768) return 2;
                return 3;
            }

            function masonryLayout() {
                const items = Array.from(container.querySelectorAll(".pin-item"));
                const columns = getColumns();

                if (columns === 1) {
                    container.style.height = 'auto';
                    items.forEach(item => {
                        item.style.position = '';
                        item.style.transform = '';
                        item.style.width = '100%';
                    });
                    return;
                }

                items.forEach(item => item.style.position = 'absolute');
                const containerWidth = container.offsetWidth;
                const colWidth = (containerWidth - (columns - 1) * gap) / columns;
                const colHeights = new Array(columns).fill(0);

                items.forEach(item => {
                    item.style.width = colWidth + 'px';
                    const minColIndex = colHeights.indexOf(Math.min(...colHeights));
                    const x = minColIndex * (colWidth + gap);
                    const y = colHeights[minColIndex];
                    item.style.transform = `translate(${x}px, ${y}px)`;
                    colHeights[minColIndex] += item.offsetHeight + gap;
                    item.classList.add('show'); // fade-in effect
                });

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
                const originalSrc = imgObj.img;
                image.src = originalSrc;
                image.alt = 'gallery image';
                image.style.display = 'block';
                image.loading = 'lazy';
                let retryCount = 0;

                // Always add item to DOM immediately, even if image is still loading
                item.appendChild(image);
                container.appendChild(item);

                image.onload = function () {
                    // allow CSS transition to run & then layout
                    requestAnimationFrame(() => {
                        item.classList.add('show');
                        masonryLayout();
                    });
                };

                image.onerror = function () {
                    retryCount++;
                    if (retryCount <= 3) {
                        // Retry loading the image with exponential backoff
                        setTimeout(() => {
                            const separator = originalSrc.includes('?') ? '&' : '?';
                            image.src = originalSrc + separator + 'retry=' + Date.now() + '&attempt=' + retryCount;
                        }, 300 * retryCount); // 300ms, 600ms, 900ms
                    } else {
                        // Final fallback after retries
                        image.onerror = null;
                        image.src = 'https://via.placeholder.com/400x300/cccccc/666666?text=Gallery+Image';
                        image.onload = function() {
                            requestAnimationFrame(() => {
                                item.classList.add('show');
                                masonryLayout();
                            });
                        };
                    }
                };

                // If image already loaded (cached), trigger onload
                if (image.complete && image.naturalHeight !== 0) {
                    image.onload();
                }
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
                masonryLayout();
            }

            function loadMore() {
                if (allLoaded) return;
                fetch(`${location.pathname}?action=load_gallery&page=${page}`)
                    .then(res => res.json())
                    .then(data => {
                        if (!Array.isArray(data) || data.length === 0) {
                            allLoaded = true;
                        } else {
                            appendItems(data);
                            page++;
                        }
                    })
                    .catch(err => console.error(err));
            }

            window.addEventListener('scroll', function () {
                if ((window.innerHeight + window.scrollY) >= (document.body.offsetHeight - 200)) {
                    loadMore();
                }
            });

            window.addEventListener('resize', masonryLayout);

            // Layout awal
            const images = container.querySelectorAll('img');
            let loadedCount = 0;
            images.forEach(img => {
                if (img.complete) loadedCount++;
                else {
                    img.addEventListener('load', () => { loadedCount++; if (loadedCount === images.length) masonryLayout(); });
                    img.addEventListener('error', () => { loadedCount++; if (loadedCount === images.length) masonryLayout(); });
                }
            });
            if (loadedCount === images.length) masonryLayout();
        });
    </script>
</body>

</html>