<?php
require_once 'config/database.php';

$conn = getDBConnection();

// Search setup
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Pagination setup
$items_per_page = 9; // 9 items per page for 3 columns grid
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Get total count with search
if (!empty($search_query)) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM berita WHERE judul ILIKE :search OR konten ILIKE :search");
    $stmt->execute([':search' => '%' . $search_query . '%']);
    $total_items = $stmt->fetchColumn();

    // Get news with pagination and search
    $stmt = $conn->prepare("SELECT * FROM berita WHERE judul ILIKE :search OR konten ILIKE :search ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':search', '%' . $search_query . '%', PDO::PARAM_STR);
} else {
    $stmt = $conn->query("SELECT COUNT(*) FROM berita");
    $total_items = $stmt->fetchColumn();

    // Get news with pagination
    $stmt = $conn->prepare("SELECT * FROM berita ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
}
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$news_list = $stmt->fetchAll();
$total_pages = ceil($total_items / $items_per_page);

 => "https://picsum.photos/seed/news{$i}/{$w}/{$h}", 
                "judul" => "Gallery Image {$i}",
                "deskripsi" => ""
            ];
        }
    } else {
        // Rename 'gambar' to 'img' for compatibility with existing JavaScript
        $all_gallery = array_map(function ($item) {
            return [
                "img" => $item['gambar'], 
                "judul" => $item['judul'] ?? 'Gallery Image',
                "deskripsi" => $item['deskripsi'] ?? ''
            ];
        }, $all_gallery);
    }
} catch (PDOException $e) {
    // Fallback to dummy data if query fails
    error_log("Gallery query error: " . $e->getMessage());
    $all_gallery = [];
    for ($i = 1; $i <= 80; $i++) {
        $w = rand(320, 460);
        $h = rand(240, 500);
        $all_gallery[] = [
            "img" => "https://picsum.photos/seed/news{$i}/{$w}/{$h}",
            "judul" => "Gallery Image {$i}",
            "deskripsi" => ""
        ];
    }
}

// Pagination for gallery
$gallery_items_per_page = 12;
$gallery_page = isset($_GET['gpage']) ? max(1, (int) $_GET['gpage']) : 1;
$gallery_offset = ($gallery_page - 1) * $gallery_items_per_page;
$total_gallery_items = count($all_gallery);
$total_gallery_pages = ceil($total_gallery_items / $gallery_items_per_page);
$gallery_init = array_slice($all_gallery, $gallery_offset, $gallery_items_per_page);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News - Information And Learning Engineering Technology</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style-news.css">
</head>

<body>

    <!-- HEADER -->
    <?php include 'includes/header.php'; ?>

    <!-- HERO -->
    <main class="flex-grow-1" style="flex: 1 0 auto; min-height: 0;">
        <section class="hero d-flex align-items-center" id="home">
            <div class="container text-center text-white">
                <h1>News - Information And Learning Engineering Technology</h1>
                <p>Stay updated with our latest publications, activities, and breakthroughs.</p>
            </div>
        </section>

        <section id="news" class="research" style="padding: 6rem 0;">
            <div class="container text-center">
                <div class="section-title">
                    <h2 style="font-size: 2.5rem;">Our News</h2>
                    <p style="font-size: 1.1rem;">Read the latest blog posts about our research group and activities.
                    </p>
                </div>

                <!-- Search Box -->
                <div class="row justify-content-center mb-4">
                    <div class="col-md-6">
                        <form method="GET" action="" class="d-flex gap-2">
                            <input type="text" name="search" class="form-control"
                                placeholder="Cari berita berdasarkan judul..."
                                value="<?php echo htmlspecialchars($search_query); ?>">
                            <button type="submit" class="btn btn-primary">Cari</button>
                            <?php if (!empty($search_query)): ?>
                                <a href="news.php" class="btn btn-secondary">Reset</a>
                            <?php endif; ?>
                        </form>
                        <?php if (!empty($search_query)): ?>
                            <p class="mt-2 text-muted">
                                Menampilkan <?php echo $total_items; ?> hasil untuk
                                "<?php echo htmlspecialchars($search_query); ?>"
                            </p>
                        <?php endif; ?>
                    </div>
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
                            <?php
                            $page_url = "?";
                            if (!empty($search_query)) {
                                $page_url .= "search=" . urlencode($search_query) . "&";
                            }
                            ?>

                            <?php if ($current_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $page_url ?>page=<?php echo $current_page - 1; ?>"
                                        aria-label="Previous">
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
                                <li class="page-item"><a class="page-link" href="<?= $page_url ?>page=1">1</a></li>
                                <?php if ($start_page > 2): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= $page_url ?>page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($end_page < $total_pages): ?>
                                <?php if ($end_page < $total_pages - 1): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                                <li class="page-item"><a class="page-link"
                                        href="<?= $page_url ?>page=<?= $total_pages ?>"><?= $total_pages ?></a></li>
                            <?php endif; ?>

                            <!-- NEXT -->
                            <?php if ($current_page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $page_url ?>page=<?= $current_page + 1 ?>" aria-label="Next">
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
                    <?php if (empty($gallery_init)): ?>
                        <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: var(--gray);">
                            <p style="font-size: 1.1rem;">Belum ada gambar di gallery. Silakan tambahkan melalui halaman admin.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($gallery_init as $g): ?>
                            <?php
                            $rawImg = $g['img'] ?? '';
                            $imgSrc = ($rawImg !== null && trim($rawImg) !== '')
                                ? htmlspecialchars($rawImg, ENT_QUOTES, 'UTF-8')
                                : 'https://via.placeholder.com/400x300/cccccc/666666?text=Gallery';
                            ?>
                            <div class="pin-item">
                                <div class="pin-img-wrapper">
                                    <img src="<?= $imgSrc ?>" 
                                         alt="<?= htmlspecialchars($g['judul'] ?? 'Gallery Image') ?>"
                                         onerror="this.onerror=null; this.src='https://via.placeholder.com/400x300/cccccc/666666?text=Error+Loading+Image';">
                                    <div class="pin-overlay">
                                        <h5 class="pin-title"><?= htmlspecialchars($g['judul'] ?? 'Gallery Image') ?></h5>
                                        <?php if (!empty($g['deskripsi'])): ?>
                                            <p class="pin-desc"><?= htmlspecialchars($g['deskripsi']) ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($g['berita_judul'])): ?>
                                            <small class="pin-berita" style="display: block; margin-top: 0.5rem; opacity: 0.8;">
                                                Dari: <?= htmlspecialchars($g['berita_judul']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php if ($total_gallery_pages > 1): ?>
                    <nav aria-label="Gallery pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($gallery_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?gpage=<?= $gallery_page - 1 ?><?= !empty($search_query) ? '&search=' . urlencode($search_query) : '' ?>#gallery" aria-label="Previous">
                                        <span aria-hidden="true">&laquo; Previous</span>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link">&laquo; Previous</span>
                                </li>
                            <?php endif; ?>

                            <?php
                            $start_page = max(1, $gallery_page - 2);
                            $end_page = min($total_gallery_pages, $gallery_page + 2);

                            if ($start_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?gpage=1<?= !empty($search_query) ? '&search=' . urlencode($search_query) : '' ?>#gallery">1</a>
                                </li>
                                <?php if ($start_page > 2): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?= $i == $gallery_page ? 'active' : '' ?>">
                                    <a class="page-link" href="?gpage=<?= $i ?><?= !empty($search_query) ? '&search=' . urlencode($search_query) : '' ?>#gallery"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($end_page < $total_gallery_pages): ?>
                                <?php if ($end_page < $total_gallery_pages - 1): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link"
                                        href="?gpage=<?= $total_gallery_pages ?><?= !empty($search_query) ? '&search=' . urlencode($search_query) : '' ?>#gallery"><?= $total_gallery_pages ?></a>
                                </li>
                            <?php endif; ?>

                            <?php if ($gallery_page < $total_gallery_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?gpage=<?= $gallery_page + 1 ?><?= !empty($search_query) ? '&search=' . urlencode($search_query) : '' ?>#gallery" aria-label="Next">
                                        <span aria-hidden="true">Next &raquo;</span>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link">Next &raquo;</span>
                                </li>
                            <?php endif; ?>
                        </ul>
                        <div class="text-center mt-3" style="color: var(--gray);">
                            Menampilkan <?= ($gallery_offset + 1) ?> -
                            <?= min($gallery_offset + $gallery_items_per_page, $total_gallery_items) ?> dari
                            <?= $total_gallery_items ?> gambar
                        </div>
                    </nav>
                <?php elseif (empty($all_gallery)): ?>
                    <div class="text-center mt-3" style="color: var(--gray);">
                        Belum ada gambar di gallery
                    </div>
                <?php endif; ?>
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

        // Masonry Layout
        document.addEventListener("DOMContentLoaded", function () {
            const container = document.getElementById("pinterest-grid");
            const gap = 15;

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
                    items.forEach(i => { 
                        i.style.position = ''; 
                        i.style.transform = ''; 
                        i.style.width = '100%'; 
                    }); 
                    return; 
                }
                
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

            function initialLayout() {
                const imgs = container.querySelectorAll('img');
                let loaded = 0;
                
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

            initialLayout();
            window.addEventListener('resize', masonryLayout);
        });
    </script>

</body>

</html>