<?php
// Prevent caching to ensure fresh data
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

require_once 'config/database.php';
require_once 'config/settings.php';

$conn = getDBConnection();

// Get page title and subtitle
$page_info = getPageTitle('home');

// Safe query helper
function safeQueryAll($conn, $sql)
{
    try {
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

// Fetch team
$team = safeQueryAll($conn, "SELECT * FROM member ORDER BY nama");

// Fetch products
$products = safeQueryAll($conn, "SELECT * FROM produk ORDER BY id_produk");

// Research fields from fokus_penelitian table - always get fresh data
// Use prepared statement to ensure fresh data and prevent caching
try {
    $stmt = $conn->prepare("SELECT id_fp, title as judul, deskripsi, detail FROM fokus_penelitian ORDER BY id_fp");
    $stmt->execute();
    $riset = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $riset = [];
}

// Function to properly capitalize titles
function formatTitle($title) {
    if (empty($title)) return '';
    $title = trim($title);
    // Direct replacement for "information engineering" in any case variation
    $title = preg_replace('/\binformation\s+engineering\b/i', 'Information Engineering', $title);
    // If title contains "Information Engineering", preserve it and capitalize the rest
    if (stripos($title, 'Information Engineering') !== false) {
        // Split by "Information Engineering" and capitalize other parts
        $parts = preg_split('/\bInformation\s+Engineering\b/i', $title, -1, PREG_SPLIT_DELIM_CAPTURE);
        $result = '';
        foreach ($parts as $i => $part) {
            if ($i % 2 == 0) {
                // Regular text - capitalize normally
                $result .= ucwords(strtolower($part));
            } else {
                // This is "Information Engineering"
                $result .= 'Information Engineering';
            }
        }
        return $result;
    }
    // For other titles, use ucwords
    return ucwords(strtolower($title));
}

// Pagination for research fields
$perPage = 9;
$totalRF = count($riset);
$totalPagesRF = ($totalRF > 0) ? ceil($totalRF / $perPage) : 1;

// Current page
$pageRF = isset($_GET['rf']) ? (int) $_GET['rf'] : 1;
if ($pageRF < 1)
    $pageRF = 1;
if ($pageRF > $totalPagesRF)
    $pageRF = $totalPagesRF;

// Start index
$startRF = ($pageRF - 1) * $perPage;

// Paginated data
$research_fields_paginated = array_slice($riset, $startRF, $perPage);

// Fetch partners
$partners = safeQueryAll($conn, "SELECT * FROM mitra ORDER BY nama_institusi");

// Fetch videos
$videos = safeQueryAll($conn, "SELECT * FROM video ORDER BY created_at DESC");

// Fetch gallery
$raw_gallery = safeQueryAll($conn, "SELECT gambar, judul FROM gallery ORDER BY created_at DESC");

$all_gallery = [];
if (!empty($raw_gallery)) {
    foreach ($raw_gallery as $row) {
        $img = trim($row['gambar'] ?? '');
        // keep absolute URLs as-is; if path looks relative, make it relative to 'uploads/'
        if ($img !== '' && !preg_match('#^https?://#i', $img)) {
            // if it's already contains uploads/ keep it; else prepend uploads/
            if (strpos($img, 'uploads/') !== 0 && strpos($img, './uploads/') !== 0) {
                $img = 'uploads/' . ltrim($img, '/');
            }
        }
        $all_gallery[] = [
            'img' => $img ?: null,
            'judul' => $row['judul'] ?? ''
        ];
    }
}

// If no gallery data, leave empty
if (empty($all_gallery)) {
    $all_gallery = [];
}

// Initials helper
function getInitials($name)
{
    $words = preg_split('/\s+/', trim($name));
    $initials = '';
    foreach ($words as $word) {
        if ($word === '')
            continue;
        $initials .= mb_substr($word, 0, 1);
        if (mb_strlen($initials) >= 2)
            break;
    }
    return strtoupper($initials);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?= htmlspecialchars($page_info['title'] ?: 'Home - InLET'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/style-home.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>
    <main>
        <section class="hero d-flex align-items-center" id="home">
            <div class="container text-center text-white">
                <h1 class="display-4 fw-bold"><?= htmlspecialchars($page_info['title'] ?: 'InLET - Information And Learning Engineering Technology'); ?></h1>
                <?php if (!empty($page_info['subtitle'])): ?>
                    <p class="lead mt-3"><?= htmlspecialchars($page_info['subtitle']); ?></p>
                <?php else: ?>
                    <p class="lead mt-3">Transforming the future of language learning through advanced engineering.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- Research section -->
        <section class="py-5" id="riset">
            <div class="container">
                <div class="section-title">
                    <h2>Our Research</h2>
                    <?php 
                    // Re-query to ensure fresh data (in case of updates)
                    if (empty($riset)) {
                        try {
                            $stmt = $conn->prepare("SELECT id_fp, title as judul, deskripsi, detail FROM fokus_penelitian ORDER BY id_fp");
                            $stmt->execute();
                            $riset = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        } catch (PDOException $e) {
                            $riset = [];
                        }
                    }
                    
                    if (!empty($riset)): 
                        // Display all research items
                        foreach ($riset as $research_item): 
                            $formatted_title = formatTitle($research_item['judul'] ?? '');
                    ?>
                        <div style="margin-bottom: 2rem;">
                            <h3 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;"><?= htmlspecialchars($formatted_title); ?></h3>
                            <?php if (!empty($research_item['deskripsi'])): ?>
                                <p class="text-center"><?= htmlspecialchars($research_item['deskripsi']); ?></p>
                            <?php else: ?>
                                <p class="text-center">Pilar ini berfokus pada rekayasa sistem informasi dan pengambilan keputusan berbasis data. Subdomain seperti E-Government, Decision Support Systems, dan Civic Technology dipilih karena relevan dengan kebutuhan industri dan pemerintahan dalam membangun sistem digital yang transparan, efisien, dan etis. Pilar ini mendukung pengembangan solusi teknologi untuk tata kelola publik, manajemen pengetahuan, dan sistem informasi yang patuh terhadap regulasi.</p>
                            <?php endif; ?>
                        </div>
                    <?php 
                        endforeach;
                    else: ?>
                        <p>InLET's research focuses on developing learning technology.</p>
                        <p class="text-center">Pilar ini berfokus pada rekayasa sistem informasi dan pengambilan keputusan berbasis data. Subdomain seperti E-Government, Decision Support Systems, dan Civic Technology dipilih karena relevan dengan kebutuhan industri dan pemerintahan dalam membangun sistem digital yang transparan, efisien, dan etis. Pilar ini mendukung pengembangan solusi teknologi untuk tata kelola publik, manajemen pengetahuan, dan sistem informasi yang patuh terhadap regulasi.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section id="research-fields" class="py-5">
            <div class="container">
                <h2 class="section-title mb-4 text-center">Research Fields</h2>

                <div class="row">
                    <?php if (!empty($research_fields_paginated)): ?>
                        <?php foreach ($research_fields_paginated as $r): ?>
                            <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                                <div class="rf-card p-4 shadow-sm rounded h-100">
                                    <h4 class="fw-bold mb-2 text-center">
                                        <?= htmlspecialchars(formatTitle($r['judul'])) ?>
                                    </h4>
                                    <div class="text-muted text-center">
                                        <?php 
                                        $detail_text = $r['detail'] ?? $r['deskripsi'] ?? '';
                                        if (!empty($detail_text)) {
                                            // Split by newlines and process each line
                                            $lines = explode("\n", $detail_text);
                                            echo '<ul class="list-unstyled mb-0" style="text-align: left; padding-left: 1.5rem;">';
                                            foreach ($lines as $line) {
                                                $line = trim($line);
                                                if (!empty($line)) {
                                                    // If line starts with "-", remove it and make it a bullet point
                                                    if (strpos($line, '-') === 0) {
                                                        $line = trim(substr($line, 1));
                                                        echo '<li style="list-style-type: disc; margin-bottom: 0.5rem;">' . htmlspecialchars($line) . '</li>';
                                                    } else {
                                                        // Regular line without bullet
                                                        echo '<li style="list-style-type: none; margin-bottom: 0.5rem;">' . htmlspecialchars($line) . '</li>';
                                                    }
                                                }
                                            }
                                            echo '</ul>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-data-alert" role="alert">
                            <i class="fas fa-flask fa-3x mb-3 text-muted"></i>
                            <p class="mb-0">No Research Fields available.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPagesRF > 1): ?>
                    <nav aria-label="RF Pagination" class="mt-4">
                        <ul class="pagination justify-content-center">

                            <!-- Previous -->
                            <li class="page-item <?= ($pageRF <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?rf=<?= $pageRF - 1 ?>">Previous</a>
                            </li>

                            <!-- Number -->
                            <?php for ($i = 1; $i <= $totalPagesRF; $i++): ?>
                                <li class="page-item <?= ($i == $pageRF) ? 'active' : '' ?>">
                                    <a class="page-link" href="?rf=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next -->
                            <li class="page-item <?= ($pageRF >= $totalPagesRF) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?rf=<?= $pageRF + 1 ?>">Next</a>
                            </li>

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
                    <div class="divider"></div>
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

        <!-- Our Product section -->
        <section class="py-5" id="product">
            <div class="container">
                <div class="section-title text-center mb-5">
                    <h2 class="fw-bold">Our Products</h2>
                    <p class="text-muted">Innovative solutions developed by InLET.</p>
                    <div class="divider"></div>
                </div>
                <?php if (empty($products)): ?>
                    <div class="text-center">
                        <div class="alert alert-light" role="alert">
                            <i class="fas fa-box fa-3x mb-3 text-muted"></i>
                            <p class="mb-0">No products available yet.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($products as $product): ?>
                            <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                                <div class="card h-100 shadow-sm">
                                    <?php if (!empty($product['gambar'])): ?>
                                        <img src="<?= htmlspecialchars($product['gambar']); ?>" 
                                             class="card-img-top" 
                                             alt="<?= htmlspecialchars($product['nama_produk']); ?>"
                                             style="height: 200px; object-fit: cover;"
                                             onerror="this.style.display='none'">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title fw-bold"><?= htmlspecialchars($product['nama_produk']); ?></h5>
                                        <?php if (!empty($product['deskripsi'])): ?>
                                            <p class="card-text text-muted"><?= nl2br(htmlspecialchars($product['deskripsi'])); ?></p>
                                        <?php else: ?>
                                            <p class="card-text text-muted">No description available.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Partners section - SLIDER -->
        <section class="py-5 bg-light" id="partner">
            <div class="container">
                <div class="section-title">
                    <h2>Our Partners</h2>
                    <p>Collaboration with academic and industry institutions.</p>
                </div>
                <?php if (empty($partners)): ?>
                    <div class="text-center">
                        <div class="alert alert-light" role="alert">
                            <i class="fas fa-handshake fa-3x mb-3 text-muted"></i>
                            <p class="mb-0">No partners registered yet.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="swiper partnersSwiper">
                        <div class="swiper-wrapper">
                            <?php foreach ($partners as $p): ?>
                                <div class="swiper-slide d-flex justify-content-center align-items-center">
                                    <?php if (!empty($p['logo'])): ?>
                                        <img src="<?= htmlspecialchars($p['logo']) ?>" class="partner-logo img-fluid rounded shadow-sm"
                                            alt="<?= htmlspecialchars($p['nama_institusi']) ?>"
                                            title="<?= htmlspecialchars($p['nama_institusi']) ?>"
                                            onerror="this.onerror=null; this.src='https://via.placeholder.com/200x100/cccccc/666666?text=' + encodeURIComponent('<?= addslashes(htmlspecialchars($p['nama_institusi'])) ?>');">
                                    <?php else: ?>
                                        <div class="partner-logo partner-placeholder">
                                            <?= htmlspecialchars($p['nama_institusi']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Team section -->
        <section class="py-5" id="team">
            <div class="container">
                <div class="section-title text-center mb-5">
                    <h2 class="fw-bold">Expert Team</h2>
                    <p class="text-muted">The brilliant minds behind our research.</p>
                    <div class="divider"></div>
                </div>
                <?php if (!empty($team)): ?>
                    <div class="row g-4">
                        <?php foreach ($team as $t):
                            $foto_url = '';
                            if (!empty($t['foto'])) {
                                $foto_url = $t['foto'];
                                if (!preg_match('/^https?:\/\//i', $foto_url)) {
                                    if (strpos($foto_url, 'uploads/') !== 0) {
                                        $foto_url = 'uploads/' . ltrim($foto_url, '/');
                                    }
                                }
                            }
                            ?>
                            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                                <div class="member-card card-surface h-100">
                                    <div class="member-img-wrapper">
                                        <?php if (!empty($foto_url)): ?>
                                            <img src="<?= htmlspecialchars($foto_url) ?>"
                                                alt="<?= htmlspecialchars($t['nama']) ?>" class="member-img"
                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="member-initials d-none">
                                                <?= htmlspecialchars(getInitials($t["nama"])) ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="member-initials">
                                                <?= htmlspecialchars(getInitials($t["nama"])) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="member-info">
                                        <h3 class="member-name"><?= htmlspecialchars($t["nama"]) ?></h3>
                                        <div class="member-role"><?= htmlspecialchars($t["jabatan"] ?: 'Member') ?></div>
                                        <?php if (!empty($t["deskripsi"])): ?>
                                            <p class="member-desc" title="<?= htmlspecialchars($t["deskripsi"]) ?>"><?= htmlspecialchars($t["deskripsi"]) ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($t['email'])): ?>
                                        <div class="member-footer">
                                            <a href="mailto:<?php echo htmlspecialchars($t['email']); ?>" class="btn-email">
                                                <i class="fas fa-envelope me-2"></i>Contact via Email
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-data-alert" role="alert">
                        <i class="fas fa-users fa-3x mb-3 text-muted"></i>
                        <p class="mb-0">No members registered yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Gallery section -->
        <section class="py-5" id="gallery">
            <div class="container">
                <div class="section-title text-center mb-4">
                    <h2>Gallery</h2>
                    <p>Documentation of InLET</p>
                </div>

                <!-- Pinterest-like grid -->
                <?php if (!empty($all_gallery)): ?>
                    <div id="pinterest-grid" class="pinterest-grid">
                        <?php foreach ($all_gallery as $g):
                            $img_src = $g['img'] ?? null;
                            // if empty or null, use placeholder
                            if (empty($img_src)) {
                                $img_src = "https://via.placeholder.com/400x300/cccccc/666666?text=Gallery";
                            }
                            // ensure safe attributes
                            $judul = $g['judul'] ?? '';
                            ?>
                            <div class="pin-item">
                                <div class="pin-img-wrapper">
                                    <img src="<?= htmlspecialchars($img_src) ?>"
                                        alt="<?= htmlspecialchars($judul ?: 'Gallery Image') ?>"
                                        onerror="this.onerror=null; this.src='https://via.placeholder.com/400x300/cccccc/666666?text=Gallery';">
                                    <div class="pin-overlay">
                                        <h5 class="pin-title"><?= htmlspecialchars($judul ?: 'Image') ?></h5>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
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

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script>
        // Init partner slider
        new Swiper(".partnersSwiper", {
            slidesPerView: 5,
            spaceBetween: 50,
            autoplay: { delay: 3000, disableOnInteraction: false },
            navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" },
            breakpoints: { 0: { slidesPerView: 1, spaceBetween: 15 }, 576: { slidesPerView: 2, spaceBetween: 25 }, 768: { slidesPerView: 3, spaceBetween: 35 }, 992: { slidesPerView: 4, spaceBetween: 45 }, 1200: { slidesPerView: 5, spaceBetween: 50 } }
        });

        // Masonry layout
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

                // Reset for single column (flow layout)
                if (columns === 1) {
                    container.style.height = 'auto';
                    items.forEach(i => {
                        i.style.position = 'static';
                        i.style.transform = '';
                        i.style.width = '100%';
                        i.style.marginBottom = gap + 'px';
                    });
                    return;
                }

                // absolute positioning layout
                items.forEach(i => {
                    i.style.position = 'absolute';
                    i.style.marginBottom = '0';
                });

                const containerWidth = container.clientWidth;
                const colWidth = Math.floor((containerWidth - (columns - 1) * gap) / columns);
                const colHeights = Array(columns).fill(0);

                items.forEach(item => {
                    item.style.width = colWidth + 'px';
                    // ensure image is displayed block so offsetHeight is proper
                    const rect = item.getBoundingClientRect();
                    // choose shortest column
                    const minCol = colHeights.indexOf(Math.min(...colHeights));
                    const x = minCol * (colWidth + gap);
                    const y = colHeights[minCol];
                    item.style.transform = `translate(${x}px, ${y}px)`;
                    item.classList.add('show');

                    // compute item's full height including margins
                    const h = item.offsetHeight;
                    colHeights[minCol] += h + gap;
                });

                container.style.height = Math.max(...colHeights) + 'px';
            }

            // Wait for images to load
            const imgs = Array.from(container.querySelectorAll('img'));
            if (imgs.length === 0) {
                masonryLayout();
            } else {
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

            // Resize handler
            let resizeTimeout = null;
            window.addEventListener('resize', function () {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(masonryLayout, 150);
            });
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