<?php
require_once 'config/database.php';
require_once 'config/settings.php';

$conn = getDBConnection();

// Get page title and subtitle
$page_info = getPageTitle('news');

// Search setup
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Pagination setup untuk NEWS
$items_per_page = 9;
$current_page_news = isset($_GET['page_news']) ? max(1, intval($_GET['page_news'])) : 1;
$offset_news = ($current_page_news - 1) * $items_per_page;

// Get total count for news with search
if (!empty($search_query)) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM berita WHERE judul ILIKE :search");
    $stmt->execute([':search' => '%' . $search_query . '%']);
    $total_items_news = $stmt->fetchColumn();

    // Get news with pagination and search
    $stmt = $conn->prepare("SELECT * FROM berita WHERE judul ILIKE :search ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':search', '%' . $search_query . '%', PDO::PARAM_STR);
} else {
    $stmt = $conn->query("SELECT COUNT(*) FROM berita");
    $total_items_news = $stmt->fetchColumn();

    // Get articles with pagination
    $stmt = $conn->prepare("SELECT * FROM berita ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
}
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset_news, PDO::PARAM_INT);
$stmt->execute();
$news_list = $stmt->fetchAll();
$total_pages_news = ceil($total_items_news / $items_per_page);

// Fetch videos
$videos = [];
try {
    $video_stmt = $conn->query("SELECT * FROM video ORDER BY created_at DESC");
    $videos = $video_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $videos = [];
}

// ---------- GALLERY SOURCE ----------
// Gallery table creation moved to inlet_pbl_clean.sql
$all_gallery = [];
try {
    $gallery_stmt = $conn->query("SELECT gambar, judul FROM gallery ORDER BY created_at DESC");
    $all_gallery = $gallery_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $all_gallery = [];
}

if (empty($all_gallery)) {
    $all_gallery = [];
} else {
    $all_gallery = array_map(function ($item) {
        return ["img" => $item['gambar'], "judul" => $item['judul'] ?? ''];
    }, $all_gallery);
}

// ---------- AJAX endpoint for load_more gallery ----------
if (isset($_GET['action']) && $_GET['action'] === 'load_gallery') {
    $limit = 12;
    $gpage = isset($_GET['gpage']) ? max(1, (int) $_GET['gpage']) : 1;
    $start = ($gpage - 1) * $limit;
    $slice = array_slice($all_gallery, $start, $limit);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_values($slice));
    exit;
}

// ---------- NEWS count + fetch ----------


//$total_pages = ($items_per_page > 0 && $total_items > 0) ? ceil($total_items / $items_per_page) : 1;

// Gallery initial load
$gallery_items_per_page = 12;
$total_gallery_items = count($all_gallery);
$gallery_init = array_slice($all_gallery, 0, $gallery_items_per_page);

// Helper function untuk build pagination URL
function getPaginationUrl($page, $search = '')
{
    $params = ['page' => $page];
    if (!empty($search)) {
        $params['search'] = $search;
    }
    return 'news.php?' . http_build_query($params);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_info['title'] ?: 'News - InLET'); ?></title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@12/swiper-bundle.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <?php $news_css_version = file_exists(__DIR__ . '/css/style-news.css') ? filemtime(__DIR__ . '/css/style-news.css') : time(); ?>
    <link rel="stylesheet" href="css/style-news.css?v=<?= $news_css_version ?>">
</head>

<body>

    <?php include 'includes/header.php'; ?>

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
            <div class="container text-center">
                <div class="section-title">
                    <h2>Our News</h2>
                </div>
                <p>Read the latest blog posts about our research group and activities.</p>

                <!-- Search Box -->
                <div class="row justify-content-center mb-4">
                    <div class="col-md-6">
                        <form method="GET" action="news.php" class="d-flex gap-2">
                            <input type="text" name="search" class="form-control" placeholder="Search news by title..."
                                value="<?php echo htmlspecialchars($search_query); ?>">
                            <button type="submit" class="btn btn-primary">Search</button>
                            <?php if (!empty($search_query)): ?>
                                <a href="news.php" class="btn btn-secondary">Reset</a>
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
                <div class="section-title text-center mb-5">
                    <h2 class="fw-bold">Our Videos</h2>
                    <p class="text-muted">Watch our latest videos and content.</p>
                </div>
                <?php if (empty($videos)): ?>
                    <div class="text-center">
                        <div class="alert alert-light" role="alert">
                            <i class="fas fa-video fa-3x mb-3 text-muted"></i>
                            <p class="mb-0">No videos available yet.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="video-container-wrapper">
                        <div class="video-player-container">
                            <div class="video-wrapper" id="videoWrapper">
                                <button class="video-arrow arrow-left" id="prevVideo" title="Previous Video">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <?php 
                                $first_video = $videos[0];
                                $video_url = htmlspecialchars($first_video['href_link'] ?? '');
                                $video_title = htmlspecialchars($first_video['title'] ?? '');
                                ?>
                                <div class="video-player" id="videoPlayer">
                                    <?php if (!empty($video_url)): ?>
                                        <?php if (preg_match('/youtube\.com|youtu\.be/i', $video_url)): ?>
                                            <?php
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
                                            <?php if (!empty($video_id)): ?>
                                                <iframe 
                                                    id="videoFrame"
                                                    src="https://www.youtube.com/embed/<?= $video_id ?>?enablejsapi=1" 
                                                    frameborder="0" 
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                                    allowfullscreen
                                                    style="width: 100%; height: 100%; min-height: 400px; border-radius: 8px;">
                                                </iframe>
                                            <?php else: ?>
                                                <div class="video-placeholder">
                                                    <p>Invalid YouTube URL</p>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <video id="videoFrame" controls style="width: 100%; height: 100%; min-height: 400px; border-radius: 8px;">
                                                <source src="<?= $video_url ?>" type="video/mp4">
                                                Your browser does not support the video tag.
                                            </video>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="video-placeholder">
                                            <p>No video URL available</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <button class="video-arrow arrow-right" id="nextVideo" title="Next Video">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
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

    <?php include 'includes/footer.php'; ?>

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

        // Video Navigation with Auto-slide
        <?php if (!empty($videos)): ?>
        const videos = <?= json_encode($videos) ?>;
        let currentVideoIndex = 0;
        let autoSlideInterval = null;
        let autoSlideCount = 0;
        const maxAutoSlide = 5; // Auto-play 5 videos pertama
        const slideDuration = 10000; // 10 detik per video

        function loadVideo(index) {
            // Handle circular navigation
            if (index < 0) {
                index = videos.length - 1; // Loop to last video
            } else if (index >= videos.length) {
                index = 0; // Loop to first video
            }
            
            currentVideoIndex = index;
            const video = videos[index];
            const videoUrl = video.href_link || '';
            
            const videoPlayer = document.getElementById('videoPlayer');
            
            if (!videoUrl) {
                videoPlayer.innerHTML = '<div class="video-placeholder"><p>No video URL available</p></div>';
                return;
            }
            
            // Check if YouTube URL
            if (videoUrl.match(/youtube\.com|youtu\.be/i)) {
                let videoId = '';
                if (videoUrl.match(/youtu\.be\/([^\?\&]+)/)) {
                    videoId = videoUrl.match(/youtu\.be\/([^\?\&]+)/)[1];
                } else if (videoUrl.match(/youtube\.com\/watch\?v=([^\&\?]+)/)) {
                    videoId = videoUrl.match(/youtube\.com\/watch\?v=([^\&\?]+)/)[1];
                } else if (videoUrl.match(/youtube\.com\/embed\/([^\?\&]+)/)) {
                    videoId = videoUrl.match(/youtube\.com\/embed\/([^\?\&]+)/)[1];
                }
                
                if (videoId) {
                    videoPlayer.innerHTML = `<iframe 
                        id="videoFrame"
                        src="https://www.youtube.com/embed/${videoId}?enablejsapi=1&autoplay=1" 
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen
                        style="width: 100%; height: 100%; min-height: 400px; border-radius: 8px;">
                    </iframe>`;
                } else {
                    videoPlayer.innerHTML = '<div class="video-placeholder"><p>Invalid YouTube URL</p></div>';
                }
            } else {
                // Regular video file
                videoPlayer.innerHTML = `<video id="videoFrame" controls autoplay style="width: 100%; height: 100%; min-height: 400px; border-radius: 8px;">
                    <source src="${videoUrl}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>`;
            }
        }

        function nextVideo() {
            currentVideoIndex = (currentVideoIndex + 1) % videos.length; // Circular
            loadVideo(currentVideoIndex);
            autoSlideCount++;
            
            // Stop auto-slide after 5 videos
            if (autoSlideCount >= maxAutoSlide) {
                stopAutoSlide();
            }
        }

        function prevVideo() {
            currentVideoIndex = (currentVideoIndex - 1 + videos.length) % videos.length; // Circular
            loadVideo(currentVideoIndex);
            stopAutoSlide(); // Stop auto-slide when user manually navigates
        }

        function startAutoSlide() {
            stopAutoSlide(); // Clear any existing interval
            autoSlideCount = 0;
            autoSlideInterval = setInterval(function() {
                if (autoSlideCount < maxAutoSlide) {
                    nextVideo();
                } else {
                    stopAutoSlide();
                }
            }, slideDuration);
        }

        function stopAutoSlide() {
            if (autoSlideInterval) {
                clearInterval(autoSlideInterval);
                autoSlideInterval = null;
            }
        }

        // Initialize first video
        loadVideo(0);
        
        // Start auto-slide after a short delay
        setTimeout(function() {
            if (videos.length > 1) {
                startAutoSlide();
            }
        }, 2000); // Start after 2 seconds

        document.getElementById('prevVideo').addEventListener('click', function() {
            prevVideo();
        });

        document.getElementById('nextVideo').addEventListener('click', function() {
            nextVideo();
        });

        // Click navigation on video player
        const videoWrapper = document.getElementById('videoWrapper');
        videoWrapper.addEventListener('click', function(e) {
            // Don't trigger if clicking on arrow buttons
            if (e.target.closest('.video-arrow')) {
                return;
            }
            
            const rect = videoWrapper.getBoundingClientRect();
            const clickX = e.clientX - rect.left;
            const width = rect.width;
            const middle = width / 2;
            
            // Left click (left half) = previous video
            if (clickX < middle) {
                prevVideo();
            }
            // Right click (right half) = next video
            else {
                nextVideo();
            }
        });

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft') {
                prevVideo();
            } else if (e.key === 'ArrowRight') {
                nextVideo();
            }
        });
        <?php endif; ?>
    </script>
    <style>
        .video-container-wrapper {
            max-width: 900px;
            margin: 0 auto;
        }
        .video-player-container {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .video-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .video-wrapper::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 50%;
            height: 100%;
            z-index: 1;
            background: rgba(0,0,0,0);
            transition: background 0.3s;
        }
        .video-wrapper::after {
            content: '';
            position: absolute;
            right: 0;
            top: 0;
            width: 50%;
            height: 100%;
            z-index: 1;
            background: rgba(0,0,0,0);
            transition: background 0.3s;
        }
        .video-wrapper:hover::before {
            background: rgba(0,0,0,0.1);
        }
        .video-wrapper:hover::after {
            background: rgba(0,0,0,0.1);
        }
        .video-player {
            width: 100%;
            background: #000;
            border-radius: 8px;
            overflow: hidden;
        }
        .video-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            background: rgba(0, 0, 0, 0.6);
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            transition: all 0.3s;
            z-index: 10;
            padding: 0;
        }
        .video-arrow:hover {
            background: rgba(0, 0, 0, 0.8);
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 4px 12px rgba(0,0,0,0.4);
        }
        .video-arrow:active {
            transform: translateY(-50%) scale(0.95);
        }
        .arrow-left {
            left: 10px;
        }
        .arrow-right {
            right: 10px;
        }
        .video-placeholder {
            padding: 4rem;
            text-align: center;
            color: #999;
            background: #f5f5f5;
            min-height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>

</body>

</html>