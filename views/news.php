<?php
/**
 * Variables provided by news.php
 *
 * @var array $page_info
 * @var string $search_query
 * @var int $total_items_news
 * @var array $news_list
 * @var float $total_pages_news
 * @var int $current_page_news
 * @var array $videos
 * @var mixed $matches
 * @var array $all_gallery
 * @var array $gallery_init
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_info['title'] ?: 'News - InLET'); ?></title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <?php $news_css_version = file_exists(__DIR__ . '/../assets/css/style-news.css') ? filemtime(__DIR__ . '/../assets/css/style-news.css') : time(); ?>
    <link rel="stylesheet" href="assets/css/style-news.css?v=<?= $news_css_version ?>">
    <link rel="stylesheet" href="assets/css/site.css?v=<?= filemtime(__DIR__ . '/../assets/css/site.css') ?>">
</head>

<body>

    <?php include __DIR__ . '/partials/header.php'; ?>

    <main>
        <section class="hero d-flex align-items-center" id="home">
            <div class="container text-center text-white">
                <h1 class="display-4 fw-bold"><?= htmlspecialchars($page_info['title'] ?: 'News - Information And Learning Engineering Technology'); ?></h1>
                <?php if (!empty($page_info['subtitle'])): ?>
                    <p class="lead mt-3"><?= htmlspecialchars($page_info['subtitle']); ?></p>
                <?php else: ?>
                    <p class="lead mt-3">Stay updated with our latest publications, activities, and breakthroughs.</p>
                <?php endif; ?>
            </div>
        </section>

        <section id="news" class="research">
            <div class="container">
                <div class="section-title">
                    <h2>News</h2>
                    <p>Visits, conferences, awards and other activities from the lab.</p>
                </div>

                <!-- Search Box -->
                <div class="row justify-content-center mb-4">
                    <div class="col-md-7 col-lg-5">
                        <form method="GET" action="news.php" class="d-flex gap-2">
                            <input type="text" name="search" class="form-control" placeholder="Search by title"
                                value="<?php echo htmlspecialchars($search_query); ?>">
                            <button type="submit" class="btn btn-primary">Search</button>
                            <?php if (!empty($search_query)): ?>
                                <a href="news.php" class="btn-quiet">Clear search</a>
                            <?php endif; ?>
                        </form>

                        <?php if (!empty($search_query)): ?>
                            <p class="mt-2 text-muted">
                                Showing <?php echo $total_items_news; ?> results for
                                "<?php echo htmlspecialchars($search_query); ?>"
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- News Grid -->
                <div class="row g-4 justify-content-center">
                    <?php if (empty($news_list)): ?>
                        <div class="col-12">
                            <div class="empty-data-alert" role="alert">
                                <i class="fas fa-newspaper fa-3x mb-3 text-muted"></i>
                                <p class="mb-0">
                                    <?php if (!empty($search_query)): ?>
                                        No results found for "<?php echo htmlspecialchars($search_query); ?>".
                                    <?php else: ?>
                                        No news articles have been published.
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($news_list as $news): ?>
                            <div class="col-lg-4 col-md-6">
                                <div class="feature-card h-100 border-0 shadow-sm transition-hover card-rounded">
                                    <?php if (!empty($news['gambar_thumbnail'])): ?>
                                        <img src="<?php echo htmlspecialchars($news['gambar_thumbnail']); ?>" class="card-img-top"
                                            alt="<?php echo htmlspecialchars($news['judul']); ?>">
                                    <?php else: ?>
                                        <div class="no-image">
                                            No Image
                                        </div>
                                    <?php endif; ?>

                                    <div class="card-body text-start">
                                        <small class="text-muted">Published on
                                            <?php echo htmlspecialchars(date('F d, Y', strtotime($news['created_at']))); ?>
                                        </small>

                                        <h3 class="mt-2 fw-bold">
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

                <!-- Custom Pagination -->
                <?php if ($total_pages_news > 1): ?>
                    <nav aria-label="News pagination" class="mt-5">
                        <ul class="pagination justify-content-center pagination-gap">
                            <?php
                            $page_url = "?";
                            if (!empty($search_query)) {
                                $page_url .= "search=" . urlencode($search_query) . "&";
                            }
                            ?>

                            <?php if ($current_page_news > 1): ?>
                                <li class="page-item">
                                    <a class="page-link"
                                        href="<?= $page_url ?>page_news=<?php echo $current_page_news - 1; ?>#focus-areas"
                                        aria-label="Previous">
                                        <span aria-hidden="true">&laquo; Previous</span>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link" aria-hidden="true">&laquo; Previous</span>
                                </li>
                            <?php endif; ?>

                            <?php
                            $start_page = max(1, $current_page_news - 2);
                            $end_page = min($total_pages_news, $current_page_news + 2);

                            if ($start_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $page_url ?>page_news=1#focus-areas">1</a>
                                </li>
                                <?php if ($start_page > 2): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?php echo ($i == $current_page_news) ? 'active' : ''; ?>">
                                    <a class="page-link"
                                        href="<?= $page_url ?>page_news=<?php echo $i; ?>#focus-areas"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($end_page < $total_pages_news): ?>
                                <?php if ($end_page < $total_pages_news - 1): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link"
                                        href="<?= $page_url ?>page_news=<?php echo $total_pages_news; ?>#focus-areas"><?php echo $total_pages_news; ?></a>
                                </li>
                            <?php endif; ?>

                            <?php if ($current_page_news < $total_pages_news): ?>
                                <li class="page-item">
                                    <a class="page-link"
                                        href="<?= $page_url ?>page_news=<?php echo $current_page_news + 1; ?>#focus-areas"
                                        aria-label="Next">
                                        <span aria-hidden="true">Next &raquo;</span>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link" aria-hidden="true">Next &raquo;</span>
                                </li>
                            <?php endif; ?>
                        </ul>

                    </nav>
                <?php endif; ?>

            </div>
        </section>

        <!-- Video section -->
        <section class="py-5 bg-light" id="video">
            <div class="container">
                <div class="section-title">
                    <h2>Videos</h2>
                    <p>Recorded talks and lab activities.</p>
                </div>
                <?php 
                // Filter only YouTube videos
                $youtube_videos = array_filter($videos, function($v) {
                    $url = strtolower($v['href_link'] ?? '');
                    return strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false;
                });
                $youtube_videos = array_values($youtube_videos); // Re-index array
                
                if (empty($youtube_videos)): ?>
                    <div class="text-center">
                        <div class="alert alert-light" role="alert">
                            <i class="fas fa-video fa-3x mb-3 text-muted"></i>
                            <p class="mb-0">No YouTube videos available yet.</p>
                        </div>
                    </div>
                <?php else:
                    // Duplicate videos for smooth looping if less than 3
                    $videosForSlider = $youtube_videos;
                    if (count($youtube_videos) < 3) {
                        $videosForSlider = array_merge($youtube_videos, $youtube_videos, $youtube_videos);
                    }
                ?>
                    <div class="swiper videoSwiper">
                        <div class="swiper-wrapper">
                            <?php foreach ($videosForSlider as $v): 
                                $video_url = htmlspecialchars($v['href_link'] ?? '');
                                
                                // Extract YouTube video ID
                                $video_id = '';
                                if (preg_match('/youtu\.be\/([^\?\&]+)/', $video_url, $matches)) {
                                    $video_id = $matches[1];
                                } elseif (preg_match('/youtube\.com\/watch\?v=([^\&\?]+)/', $video_url, $matches)) {
                                    $video_id = $matches[1];
                                } elseif (preg_match('/youtube\.com\/embed\/([^\?\&]+)/', $video_url, $matches)) {
                                    $video_id = $matches[1];
                                }
                            ?>
                                <div class="swiper-slide">
                                    <div class="video-slide-wrapper">
                                        <?php if (!empty($video_id)): ?>
                                            <iframe 
                                                class="video-iframe"
                                                src="https://www.youtube.com/embed/<?= $video_id ?>?enablejsapi=1" 
                                                frameborder="0" 
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                                allowfullscreen>
                                            </iframe>
                                        <?php else: ?>
                                            <div class="video-placeholder">
                                                <p>Invalid YouTube URL</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-pagination"></div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- GALLERY -->
        <section class="py-5" id="gallery">
            <div class="container">
                <div class="section-title">
                    <h2>Gallery</h2>
                    <p>Photos from lab events, visits and discussions.</p>
                </div>

                <?php if (!empty($all_gallery)): ?>
                    <div id="pinterest-grid" class="pinterest-grid">
                        <?php foreach ($gallery_init as $g): ?>
                            <div class="pin-item">
                                <div class="pin-img-wrapper">
                                    <img src="<?php echo htmlspecialchars($g['img']); ?>" alt="Gallery Image"
                                        onerror="this.onerror=null; this.src='https://via.placeholder.com/400x300/cccccc/666666?text=Gallery';">
                                    <div class="pin-overlay">
                                        <h5 class="pin-title"><?php echo htmlspecialchars($g['judul'] ?: 'Image'); ?></h5>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div id="loader" class="text-center mt-3 d-none">Loading more...</div>
                <?php else: ?>
                    <div class="empty-data-alert" role="alert">
                        <i class="fas fa-images fa-3x mb-3 text-muted"></i>
                        <p class="mb-0">No gallery images available.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/partials/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.js"></script>

    <script>
        // Init swiper
        new Swiper(".teamSwiper", {
            slidesPerView: 3,
            spaceBetween: 30,
            navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" },
            breakpoints: { 0: { slidesPerView: 1 }, 576: { slidesPerView: 2 }, 992: { slidesPerView: 3 } }
        });

        document.addEventListener("DOMContentLoaded", function () {
            const container = document.getElementById("pinterest-grid");
            if (!container) return;
            const gap = 15;
            let gpage = 2; // Gallery page counter
            let allLoaded = false;
            let isLoading = false;

            function getColumns() {
                if (window.innerWidth < 576) return 1;
                if (window.innerWidth < 768) return 2;
                return 3;
            }

            function masonryLayout() {
                const items = Array.from(container.querySelectorAll(".pin-item"));
                const columns = getColumns();

                if (columns === 1) {
                    container.style.height = "auto";
                    items.forEach(i => {
                        i.style.position = "";
                        i.style.transform = "";
                        i.style.width = "100%";
                    });
                    return;
                }

                items.forEach(i => {
                    i.style.position = "absolute";
                    i.style.transition = "transform 300ms ease";
                });

                const colWidth = (container.offsetWidth - (columns - 1) * gap) / columns;
                const colHeights = Array(columns).fill(0);

                items.forEach(item => {
                    item.style.width = Math.floor(colWidth) + "px";
                    const minCol = colHeights.indexOf(Math.min(...colHeights));
                    const x = minCol * (colWidth + gap);
                    const y = colHeights[minCol];
                    item.style.transform = `translate(${x}px, ${y}px)`;
                    item.classList.add("show");
                    colHeights[minCol] += item.offsetHeight + gap;
                });

                container.style.height = Math.max(...colHeights) + "px";
            }

            function appendItems(data) {
                if (!data || !data.length) return;
                data.forEach(g => {
                    const item = document.createElement("div");
                    item.className = "pin-item";
                    const wrapper = document.createElement("div");
                    wrapper.className = "pin-img-wrapper";

                    const img = document.createElement("img");
                    img.src = g.img;
                    img.alt = g.judul || "Gallery Image";
                    img.onerror = function () {
                        this.onerror = null;
                        this.src = "https://via.placeholder.com/400x300/cccccc/666666?text=Gallery";
                    };

                    const overlay = document.createElement("div");
                    overlay.className = "pin-overlay";
                    const title = document.createElement("h5");
                    title.className = "pin-title";
                    title.textContent = g.judul || "Image";
                    overlay.appendChild(title);

                    wrapper.appendChild(img);
                    wrapper.appendChild(overlay);
                    item.appendChild(wrapper);
                    container.appendChild(item);

                    img.onload = img.onerror = function () {
                        setTimeout(masonryLayout, 40);
                    };
                });

                setTimeout(masonryLayout, 60);
            }

            function loadMore() {
                if (isLoading || allLoaded) return;
                isLoading = true;
                const loader = document.getElementById("loader");
                if (loader) loader.style.display = "block";

                fetch('news.php?action=load_gallery&gpage=' + gpage)
                    .then(r => {
                        if (!r.ok) throw new Error("Network response was not ok");
                        return r.json();
                    })
                    .then(data => {
                        if (!data || data.length === 0) {
                            allLoaded = true;
                        } else {
                            appendItems(data);
                            gpage++;
                        }
                    })
                    .catch(err => {
                        console.warn("Failed to load more gallery items:", err);
                        allLoaded = true;
                    })
                    .finally(() => {
                        isLoading = false;
                        if (loader) loader.style.display = "none";
                    });
            }

            (function initImageLoadAndLayout() {
                const imgs = Array.from(container.querySelectorAll("img"));
                if (!imgs.length) {
                    masonryLayout();
                    return;
                }

                let loadedCount = 0;
                imgs.forEach(img => {
                    if (img.complete) {
                        loadedCount++;
                    } else {
                        img.addEventListener("load", () => {
                            loadedCount++;
                            if (loadedCount === imgs.length) masonryLayout();
                        }, { once: true });
                        img.addEventListener("error", () => {
                            loadedCount++;
                            if (loadedCount === imgs.length) masonryLayout();
                        }, { once: true });
                    }
                });

                if (loadedCount === imgs.length) {
                    setTimeout(masonryLayout, 40);
                }
            })();

            let resizeTimer = null;
            window.addEventListener("resize", function () {
                if (resizeTimer) clearTimeout(resizeTimer);
                resizeTimer = setTimeout(masonryLayout, 120);
            });

            let scrollTimer = null;
            window.addEventListener('scroll', () => {
                if (scrollTimer) clearTimeout(scrollTimer);
                scrollTimer = setTimeout(() => {
                    if ((window.innerHeight + window.scrollY) >= (document.body.offsetHeight - 200)) {
                        loadMore();
                    }
                }, 120);
            });

            window.__galleryLoadMore = loadMore;
        });

        // Video Swiper
        new Swiper(".videoSwiper", {
            slidesPerView: 1,
            spaceBetween: 30,
            centeredSlides: true,
            loop: true, // Always loop for infinite effect
            loopAdditionalSlides: 2, // Add extra slides for smooth looping
            autoplay: false, // No autoplay for videos (user might be watching)
            navigation: { 
                nextEl: ".videoSwiper .swiper-button-next", 
                prevEl: ".videoSwiper .swiper-button-prev" 
            },
            pagination: {
                el: ".videoSwiper .swiper-pagination",
                clickable: true
            },
            breakpoints: {
                0: { 
                    slidesPerView: 1, 
                    spaceBetween: 20,
                    centeredSlides: true
                },
                768: { 
                    slidesPerView: 1, 
                    spaceBetween: 30,
                    centeredSlides: true
                }
            }
        });
    </script>
    <link rel="stylesheet" href="assets/css/style-news-extra.css">

</body>

</html>