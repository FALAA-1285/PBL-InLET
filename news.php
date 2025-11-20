<?php
require_once 'config/database.php';

$conn = getDBConnection();

// Pagination setup
$items_per_page = 9; // 9 items per page for 3 columns grid
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Get total count
$stmt = $conn->query("SELECT COUNT(*) FROM berita");
$total_items = $stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

// Get news with pagination
$stmt = $conn->prepare("SELECT * FROM berita ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$news_list = $stmt->fetchAll();

// Gallery dataset (reusable dummy images)
$all_gallery = [];
for ($i = 1; $i <= 80; $i++) {
    $w = rand(320, 460);
    $h = rand(240, 500);
    $all_gallery[] = [
        "img" => "https://picsum.photos/seed/news{$i}/{$w}/{$h}"
    ];
}

// AJAX endpoint to load more gallery items
if (isset($_GET['action']) && $_GET['action'] === 'load_gallery') {
    $limit = 12;
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $start = ($page - 1) * $limit;
    $slice = array_slice($all_gallery, $start, $limit);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_values($slice));
    exit;
}

$gallery_limit = 12;
$gallery_init = array_slice($all_gallery, 0, $gallery_limit);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News - Information And Learning Engineering Technology</title>

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
            <h1 class="display-4 fw-bold">News - Information And Learning Engineering Technology</h1>
            <p>Stay updated with our latest publications, activities, and breakthroughs.</p>
        </div>
    </section>

    <section id="news" class="research" style="padding: 6rem 0;">
        <div class="container text-center">
            <div class="section-title">
                <h2 style="font-size: 2.5rem;">Our News</h2>
                <p style="font-size: 1.1rem;">Read the latest blog posts about our research group and activities.</p>
            </div>
            <div class="row g-4 justify-content-center">

                <?php if (empty($news_list)): ?>
                    <div class="col-12">
                        <p style="color: var(--gray); padding: 3rem;">No news articles have been published.</p>
                    </div>
                <?php else: ?>

                    <?php foreach ($news_list as $news): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="feature-card h-100 border-0 shadow-sm transition-hover"
                                style="border-radius: 12px; overflow: hidden; padding: 0;">
                                <?php if ($news['gambar_thumbnail']): ?>
                                    <img src="<?php echo htmlspecialchars($news['gambar_thumbnail']); ?>" class="card-img-top"
                                        alt="<?php echo htmlspecialchars($news['judul']); ?>"
                                        style="height: 220px; object-fit: cover;">
                                <?php else: ?>
                                    <div
                                        style="height: 220px; background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem;">
                                        No Image
                                    </div>
                                <?php endif; ?>

                                <div class="card-body text-start" style="padding: 1.5rem 2.5rem;">
                                    <small class="text-muted">Published on
                                        <?php echo date('F d, Y', strtotime($news['created_at'])); ?>
                                    </small>

                                    <h3 class="mt-2" style="font-weight: 600;">
                                        <?php echo htmlspecialchars($news['judul']); ?>
                                    </h3>

                                    <p class="card-text">
                                        <?php echo htmlspecialchars(substr($news['konten'], 0, 150)) . '...'; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                <?php endif; ?>
            </div>

            <!-- Pagination news -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="News pagination" style="margin-top: 3rem;">
                    <ul class="pagination justify-content-center" style="gap: 0.5rem;">

                        <!-- PREVIOUS -->
                        <?php if ($current_page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $current_page - 1; ?>" aria-label="Previous">
                                    &laquo; Previous
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link">&laquo; Previous</span>
                            </li>
                        <?php endif; ?>

                        <!-- PAGE NUMBERS -->
                        <?php
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);

                        if ($start_page > 1): ?>
                            <li class="page-item"><a class="page-link" href="?page=1">1</a></li>
                            <?php if ($start_page > 2): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($end_page < $total_pages): ?>
                            <?php if ($end_page < $total_pages - 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <li class="page-item"><a class="page-link" href="?page=<?= $total_pages ?>"><?= $total_pages ?></a></li>
                        <?php endif; ?>

                        <!-- NEXT -->
                        <?php if ($current_page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $current_page + 1 ?>" aria-label="Next">
                                    Next &raquo;
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled"><span class="page-link">Next &raquo;</span></li>
                        <?php endif; ?>
                    </ul>

                    <div class="text-center mt-3" style="color: var(--gray);">
                        Showing <?php echo ($offset + 1); ?> –
                        <?php echo min($offset + $items_per_page, $total_items); ?>
                        of <?php echo $total_items; ?> news articles
                    </div>
                </nav>
            <?php endif; ?>

        </div>
    </section>

    <!-- GALLERY -->
        <section class="py-5" id="gallery">
            <div class="container">
                <div class="section-title text-center mb-4">
                    <h2>Gallery</h2>
                    <p>Documentation of InLET</p>
                </div>
                <div id="pinterest-grid" class="pinterest-grid">
                    <?php foreach ($gallery_init as $g): ?>
                        <div class="pin-item">
                            <div class="pin-img-wrapper">
                                <img src="<?= htmlspecialchars($g['img']) ?>" alt="Gallery Image"
                                    onerror="this.onerror=null; this.src='https://via.placeholder.com/400x300/cccccc/666666?text=Gallery';">
                                <div class="pin-overlay">
                                    <h5 class="pin-title"><?= htmlspecialchars($g['judul'] ?? 'Image') ?></h5>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div id="loader" class="text-center mt-3" style="display:none;">Loading more...</div>
            </div>
        </section>

    </main>
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        // Swiper init
        new Swiper(".teamSwiper", { slidesPerView: 3, spaceBetween: 30, navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" }, breakpoints: { 0: { slidesPerView: 1 }, 576: { slidesPerView: 2 }, 992: { slidesPerView: 3 } } });

        // Masonry + Infinite Scroll
        document.addEventListener("DOMContentLoaded", function () {
            const container = document.getElementById("pinterest-grid");
            const gap = 15; let page = 2; let allLoaded = false; let isLoading = false;

            function getColumns() { if (window.innerWidth < 576) return 1; if (window.innerWidth < 768) return 2; return 3; }
            function masonryLayout() {
                const items = Array.from(container.querySelectorAll(".pin-item"));
                const columns = getColumns();
                if (columns === 1) { container.style.height = 'auto'; items.forEach(i => { i.style.position = ''; i.style.transform = ''; i.style.width = '100%'; }); return; }
                items.forEach(i => i.style.position = 'absolute');
                const colWidth = (container.offsetWidth - (columns - 1) * gap) / columns;
                const colHeights = Array(columns).fill(0);
                items.forEach(item => {
                    item.style.width = colWidth + 'px';
                    const minCol = colHeights.indexOf(Math.min(...colHeights));
                    const x = minCol * (colWidth + gap);
                    const y = colHeights[minCol];
                    item.style.transform = `translate(${x}px,${y}px)`;
                    item.classList.add('show');
                    colHeights[minCol] += item.offsetHeight + gap;
                });
                container.style.height = Math.max(...colHeights) + 'px';
            }

            function appendItems(data) {
                if (!data || data.length === 0) return;
                data.forEach(g => {
                    const item = document.createElement('div'); item.className = 'pin-item';
                    const wrapper = document.createElement('div'); wrapper.className = 'pin-img-wrapper';
                    const img = document.createElement('img'); img.src = g.img; img.alt = g.judul || 'Gallery Image';
                    img.onerror = function () { this.src = 'https://via.placeholder.com/400x300/cccccc/666666?text=Gallery'; };
                    const overlay = document.createElement('div'); overlay.className = 'pin-overlay';
                    const title = document.createElement('h5'); title.className = 'pin-title'; title.textContent = g.judul || 'Image'; overlay.appendChild(title);
                    wrapper.appendChild(img); wrapper.appendChild(overlay); item.appendChild(wrapper); container.appendChild(item);
                    img.onload = masonryLayout;
                });
            }

            function loadMore() {
                if (isLoading || allLoaded) return;
                isLoading = true; document.getElementById('loader').style.display = 'block';
                fetch(`<?= basename($_SERVER['PHP_SELF']); ?>?action=load_gallery&page=${page}`)
                    .then(r => r.json())
                    .then(data => { if (!data || data.length === 0) { allLoaded = true; } else { appendItems(data); page++; } })
                    .finally(() => { isLoading = false; document.getElementById('loader').style.display = 'none'; });
            }

            window.addEventListener('scroll', () => { if (window.innerHeight + window.scrollY > document.body.offsetHeight - 200) loadMore(); });
            window.addEventListener('resize', masonryLayout);

            // initial layout
            const imgs = container.querySelectorAll('img'); let loadedCount = 0;
            imgs.forEach(img => { if (img.complete) loadedCount++; else img.onload = img.onerror = () => { loadedCount++; if (loadedCount === imgs.length) masonryLayout(); } });
            if (loadedCount === imgs.length) masonryLayout();
        });
    </script>
    
</body>

</html>