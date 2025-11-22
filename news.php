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

// Create gallery table if not exists
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS gallery (
        id_gallery SERIAL PRIMARY KEY,
        gambar VARCHAR(500) NOT NULL,
        judul VARCHAR(255),
        deskripsi VARCHAR(1000),
        urutan INTEGER DEFAULT 0,
        created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
        updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
    )");
    $conn->exec("CREATE INDEX IF NOT EXISTS idx_gallery_urutan ON gallery(urutan)");
    $conn->exec("CREATE INDEX IF NOT EXISTS idx_gallery_created ON gallery(created_at)");
} catch (PDOException $e) {
    // Table might already exist, continue
}

// Get gallery from database
try {
    $gallery_stmt = $conn->query("SELECT gambar, judul FROM gallery ORDER BY urutan ASC, created_at DESC");
    $all_gallery = $gallery_stmt->fetchAll();
    
    // If no gallery in database, use dummy data as fallback
    if (empty($all_gallery)) {
        for ($i = 1; $i <= 80; $i++) {
            $w = rand(320, 460);
            $h = rand(240, 500);
            $all_gallery[] = ["gambar" => "https://picsum.photos/seed/news{$i}/{$w}/{$h}", "judul" => ""];
        }
    } else {
        // Rename 'gambar' to 'img' for compatibility
        $all_gallery = array_map(function($item) {
            return ["img" => $item['gambar'], "judul" => $item['judul'] ?? ''];
        }, $all_gallery);
    }
} catch (PDOException $e) {
    // Fallback to dummy data if table doesn't exist
    $all_gallery = [];
    for ($i = 1; $i <= 80; $i++) {
        $w = rand(320, 460);
        $h = rand(240, 500);
        $all_gallery[] = ["img" => "https://picsum.photos/seed/news{$i}/{$w}/{$h}"];
    }
}

// AJAX endpoint to load more gallery items (disabled, using pagination)
// if (isset($_GET['action']) && $_GET['action'] === 'load_gallery') {
//     $limit = 12;
//     $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
//     $start = ($page - 1) * $limit;
//     $slice = array_slice($all_gallery, $start, $limit);
//     header('Content-Type: application/json; charset=utf-8');
//     echo json_encode(array_values($slice));
//     exit;
// }

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
                            Menampilkan <?php echo $total_items; ?> hasil untuk "<?php echo htmlspecialchars($search_query); ?>"
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
                                <a class="page-link" href="<?= $page_url ?>page=<?php echo $current_page - 1; ?>" aria-label="Previous">
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
                            <li class="page-item"><a class="page-link" href="<?= $page_url ?>page=<?= $total_pages ?>"><?= $total_pages ?></a></li>
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
                <?php if ($total_gallery_pages > 1): ?>
                    <nav aria-label="Gallery pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($gallery_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?gpage=<?= $gallery_page - 1 ?>#gallery" aria-label="Previous">
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
                                    <a class="page-link" href="?gpage=1#gallery">1</a>
                                </li>
                                <?php if ($start_page > 2): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?= $i == $gallery_page ? 'active' : '' ?>">
                                    <a class="page-link" href="?gpage=<?= $i ?>#gallery"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($end_page < $total_gallery_pages): ?>
                                <?php if ($end_page < $total_gallery_pages - 1): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" href="?gpage=<?= $total_gallery_pages ?>#gallery"><?= $total_gallery_pages ?></a>
                                </li>
                            <?php endif; ?>
                            
                            <?php if ($gallery_page < $total_gallery_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?gpage=<?= $gallery_page + 1 ?>#gallery" aria-label="Next">
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
                            Menampilkan <?= ($gallery_offset + 1) ?> - <?= min($gallery_offset + $gallery_items_per_page, $total_gallery_items) ?> dari <?= $total_gallery_items ?> gambar
                        </div>
                    </nav>
                <?php endif; ?>
                <div id="loader" class="text-center mt-3" style="display:none;">Loading more...</div>
            </div>
        </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function handleImageError(img, originalSrc) {
        let retries = parseInt(img.getAttribute('data-retries') || '0', 10);
        if (retries < 2) {
            img.setAttribute('data-retries', (retries + 1).toString());
            setTimeout(() => {
                img.src = originalSrc + (originalSrc.includes('?') ? '&' : '?') + 'retry=' + Date.now();
            }, 500);
        } else {
            img.onerror = null;
            img.src = 'https://via.placeholder.com/400x300/cccccc/666666?text=Gallery+Image';
        }
    }
    </script>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const container = document.getElementById("pinterest-grid");
        const loader = document.getElementById("loader");
        let page = 2;
        let isLoading = false;
        let allLoaded = false;
        const gap = 15;

        const calcColumns = () => {
            if (window.innerWidth < 576) return 1;
            if (window.innerWidth < 768) return 2;
            return 3;
        };

        function masonryLayout() {
            const items = Array.from(container.querySelectorAll(".pin-item"));
            const columns = calcColumns();

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

            items.forEach(item => item.style.position = 'absolute');
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

        function appendItems(itemsData) {
            if (!itemsData || itemsData.length === 0) return;

            itemsData.forEach(data => {
                const item = document.createElement('div');
                item.className = 'pin-item';
                const image = new Image();
                const originalSrc = data.img;
                image.src = originalSrc;
                image.alt = 'gallery image';
                image.loading = 'lazy';
                item.appendChild(image);
                container.appendChild(item);

                image.onload = () => {
                    requestAnimationFrame(() => {
                        item.classList.add('show');
                        masonryLayout();
                    });
                };

                let retryCount = 0;
                image.onerror = () => {
                    retryCount++;
                    if (retryCount <= 3) {
                        setTimeout(() => {
                            const separator = originalSrc.includes('?') ? '&' : '?';
                            image.src = originalSrc + separator + 'retry=' + Date.now() + '&attempt=' + retryCount;
                        }, 250 * retryCount);
                    } else {
                        image.onerror = null;
                        image.src = 'https://via.placeholder.com/400x300/cccccc/666666?text=Gallery+Image';
                        image.onload = () => {
                            requestAnimationFrame(() => {
                                item.classList.add('show');
                                masonryLayout();
                            });
                        };
                    }
                };

                if (image.complete && image.naturalHeight !== 0) {
                    image.onload();
                }
            });
        }

        function loadMore() {
            if (isLoading || allLoaded) return;
            isLoading = true;
            loader.style.display = 'block';

            fetch(location.pathname + '?action=load_gallery&page=' + page)
                .then(res => res.json())
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
                .catch(() => {
                    isLoading = false;
                    loader.style.display = 'none';
                });
        }

        function initialLayout() {
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

        initialLayout();

        // Disabled infinite scroll, using pagination instead
        // let scrollTimer = null;
        // window.addEventListener('scroll', () => {
        //     if (scrollTimer) clearTimeout(scrollTimer);
        //     scrollTimer = setTimeout(() => {
        //         if ((window.innerHeight + window.scrollY) >= (document.body.offsetHeight - 200)) {
        //             loadMore();
        //         }
        //     }, 120);
        // });

        let resizeTimer = null;
        window.addEventListener('resize', () => {
            if (resizeTimer) clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => masonryLayout(), 120);
        });

        const observer = new MutationObserver(() => masonryLayout());
        observer.observe(container, { childList: true });
    });
    </script>
</body>

</html>