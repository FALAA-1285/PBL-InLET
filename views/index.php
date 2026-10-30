<?php
/**
 * Variables provided by index.php
 *
 * @var array $page_info
 * @var array $riset
 * @var \PDO $conn
 * @var array $research_fields_paginated
 * @var float $totalPagesRF
 * @var int $pageRF
 * @var array $videos
 * @var mixed $matches
 * @var array $products
 * @var array $partners
 * @var array $team
 * @var array $all_gallery
 * @var array $gallery_init
 */
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style-home.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="assets/css/site.css?v=<?= filemtime(__DIR__ . '/../assets/css/site.css') ?>">
</head>

<body>
    <?php include __DIR__ . '/partials/header.php'; ?>
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
        <section class="py-5 bg-light" id="riset">
            <div class="container">
                <div class="section-title">
                    <h2>Research areas</h2>
                    <p>InLET's name is its map: information and learning, each studied as engineering and as technology.</p>
                </div>
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
                
                // Place the four pillars on an Information/Learning x Engineering/Technology grid
                // when every title follows that pattern; otherwise fall back to a plain grid.
                $pillar_cells = [];
                foreach ($riset as $research_item) {
                    if (preg_match('/^\s*(information|learning)\s+(engineering|technology)\s*$/i', $research_item['judul'] ?? '', $m)) {
                        $pillar_cells[strtolower($m[1]) . '-' . strtolower($m[2])] = true;
                    }
                }
                $use_matrix = count($riset) === 4 && count($pillar_cells) === 4;

                if (!empty($riset)): ?>
                    <div class="<?= $use_matrix ? 'pillar-matrix' : 'row' ?>">
                        <?php if ($use_matrix): ?>
                            <div aria-hidden="true"></div>
                            <div class="pillar-axis pillar-axis-col" style="grid-column: 2; grid-row: 1;">Information</div>
                            <div class="pillar-axis pillar-axis-col" style="grid-column: 3; grid-row: 1;">Learning</div>
                            <div class="pillar-axis pillar-axis-row" style="grid-column: 1; grid-row: 2;">Engineering</div>
                            <div class="pillar-axis pillar-axis-row" style="grid-column: 1; grid-row: 3;">Technology</div>
                        <?php endif; ?>
                        <?php foreach ($riset as $research_item):
                            $formatted_title = formatTitle($research_item['judul'] ?? '');
                            $cell_style = '';
                            if ($use_matrix) {
                                preg_match('/(information|learning)\s+(engineering|technology)/i', $research_item['judul'], $m);
                                $cell_style = sprintf('grid-column: %d; grid-row: %d;',
                                    strtolower($m[1]) === 'information' ? 2 : 3,
                                    strtolower($m[2]) === 'engineering' ? 2 : 3);
                            }
                        ?>
                            <div class="<?= $use_matrix ? 'pillar-cell' : 'col-lg-6 col-md-12 mb-4' ?>"<?= $cell_style ? ' style="' . $cell_style . '"' : '' ?>>
                                <div class="research-item-card card-surface h-100">
                                    <h3 class="research-item-title"><?= htmlspecialchars($formatted_title); ?></h3>
                                    <?php 
                                    $description = !empty($research_item['deskripsi']) ? $research_item['deskripsi'] : 'This research pillar focuses on information systems engineering and data-driven decision making. Subdomains such as E-Government, Decision Support Systems, and Civic Technology are selected for their relevance to industry and government needs in building transparent, efficient, and ethical digital systems.';
                                    $description_length = strlen($description);
                                    $needs_readmore = $description_length > 150;
                                    ?>
                                    <div class="research-description-wrapper">
                                        <p class="research-item-description <?= $needs_readmore ? 'research-description-collapsed' : '' ?>">
                                            <?= htmlspecialchars($description); ?>
                                        </p>
                                        <?php if ($needs_readmore): ?>
                                            <button class="btn-readmore" onclick="toggleResearchDescription(this)">
                                                <span class="readmore-text">Read More</span>
                                                <span class="readless-text" style="display: none;">Read Less</span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <?php
                                    $topics = [];
                                    if ($use_matrix) {
                                        foreach (explode("\n", $research_item['detail'] ?? '') as $topic) {
                                            $topic = trim(ltrim(trim($topic), '-'));
                                            if ($topic !== '') $topics[] = $topic;
                                        }
                                    }
                                    ?>
                                    <?php if ($topics): ?>
                                        <ul class="pillar-topics">
                                            <?php foreach ($topics as $topic): ?>
                                                <li><?= htmlspecialchars($topic); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-data-alert" role="alert">
                        <i class="fas fa-flask fa-3x mb-3 text-muted"></i>
                        <p class="mb-0">InLET's research focuses on developing learning technology.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <?php if (!$use_matrix): // topics are shown inside the research matrix ?>
        <section id="research-fields" class="py-5">
            <div class="container">
                <div class="section-title">
                    <h2>Topics by area</h2>
                    <p>The specific topics each research area covers.</p>
                </div>

                <div class="row">
                    <?php if (!empty($research_fields_paginated)): ?>
                        <?php foreach ($research_fields_paginated as $r): ?>
                            <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                                <div class="rf-card card-surface h-100">
                                    <h4 class="rf-card-title">
                                        <?= htmlspecialchars(formatTitle($r['judul'])) ?>
                                    </h4>
                                    <div class="rf-card-content">
                                        <?php 
                                        $detail_text = $r['detail'] ?? $r['deskripsi'] ?? '';
                                        if (!empty($detail_text)) {
                                            // Split by newlines and process each line
                                            $lines = explode("\n", $detail_text);
                                            $total_lines = count(array_filter($lines, function($l) { return !empty(trim($l)); }));
                                            $needs_readmore = $total_lines > 5;
                                            ?>
                                            <div class="rf-content-wrapper">
                                                <ul class="rf-card-list <?= $needs_readmore ? 'rf-content-collapsed' : '' ?>">
                                                    <?php
                                                    foreach ($lines as $line) {
                                                        $line = trim($line);
                                                        if (!empty($line)) {
                                                            // If line starts with "-", remove it and make it a bullet point
                                                            if (strpos($line, '-') === 0) {
                                                                $line = trim(substr($line, 1));
                                                                echo '<li>' . htmlspecialchars($line) . '</li>';
                                                            } else {
                                                                // Regular line without bullet
                                                                echo '<li class="no-bullet">' . htmlspecialchars($line) . '</li>';
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                </ul>
                                                <?php if ($needs_readmore): ?>
                                                    <button class="btn-readmore" onclick="toggleResearchField(this)">
                                                        <span class="readmore-text">Read More</span>
                                                        <span class="readless-text" style="display: none;">Read Less</span>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="empty-data-alert" role="alert">
                                <i class="fas fa-flask fa-3x mb-3 text-muted"></i>
                                <p class="mb-0">No Research Fields available.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPagesRF > 1): ?>
                    <nav aria-label="RF Pagination" class="mt-5">
                        <ul class="pagination pagination-modern justify-content-center">
                            <!-- Previous -->
                            <li class="page-item <?= ($pageRF <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?rf=<?= $pageRF - 1 ?>#research-fields" aria-label="Previous">
                                    <span aria-hidden="true">&laquo; Previous</span>
                                </a>
                            </li>

                            <!-- Number -->
                            <?php 
                            $start_page = max(1, $pageRF - 2);
                            $end_page = min($totalPagesRF, $pageRF + 2);
                            
                            if ($start_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?rf=1#research-fields">1</a>
                                </li>
                                <?php if ($start_page > 2): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?= ($i == $pageRF) ? 'active' : '' ?>">
                                    <a class="page-link" href="?rf=<?= $i ?>#research-fields"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($end_page < $totalPagesRF): ?>
                                <?php if ($end_page < $totalPagesRF - 1): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" href="?rf=<?= $totalPagesRF ?>#research-fields"><?= $totalPagesRF ?></a>
                                </li>
                            <?php endif; ?>

                            <!-- Next -->
                            <li class="page-item <?= ($pageRF >= $totalPagesRF) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?rf=<?= $pageRF + 1 ?>#research-fields" aria-label="Next">
                                    <span aria-hidden="true">Next &raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Video section -->
        <section class="py-5" id="video">
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

        <!-- Our Product section -->
        <section class="py-5" id="product">
            <div class="container">
                <div class="section-title">
                    <h2>Products</h2>
                    <p>Applications and equipment from InLET's projects.</p>
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
                                        <?php endif; ?>
                                        <?php if (!empty($product['try'])): ?>
                                            <div class="mt-3">
                                                <a href="<?= htmlspecialchars($product['try']) ?>" target="_blank" class="btn btn-primary">
                                                    <i class="fas fa-external-link-alt me-2"></i>Try Now
                                                </a>
                                            </div>
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
                    <h2>Partners</h2>
                    <p>Universities and companies InLET collaborates with.</p>
                </div>
                <?php if (empty($partners)): ?>
                    <div class="text-center">
                        <div class="alert alert-light" role="alert">
                            <i class="fas fa-handshake fa-3x mb-3 text-muted"></i>
                            <p class="mb-0">No partners registered yet.</p>
                        </div>
                    </div>
                <?php else: 
                    // Duplicate partners if less than 6 to ensure smooth infinite loop
                    $partnersForSlider = $partners;
                    if (count($partners) < 6) {
                        $partnersForSlider = array_merge($partners, $partners, $partners);
                    }
                ?>
                    <div class="swiper partnersSwiper">
                        <div class="swiper-wrapper">
                            <?php foreach ($partnersForSlider as $p): ?>
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
                        <div class="swiper-pagination"></div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Team section -->
        <section class="py-5" id="team">
            <div class="container">
                <div class="section-title">
                    <h2>Team</h2>
                    <p>Lecturers and researchers at InLET.</p>
                </div>
                <?php if (!empty($team)):
                    $teamForSlider = $team;
                    if (count($team) < 5) {
                        $teamForSlider = array_merge($team, $team, $team);
                    }
                ?>
                    <div class="swiper teamSwiper">
                        <div class="swiper-wrapper">
                            <?php foreach ($teamForSlider as $t):
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
                                <div class="swiper-slide">
                                    <div class="member-card card-surface h-100 text-center">
                                        <div class="member-img-wrapper">
                                            <?php if (!empty($foto_url)): ?>
                                                <img src="<?= htmlspecialchars($foto_url) ?>"
                                                     alt="<?= htmlspecialchars($t['nama']) ?>"
                                                     class="member-img"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div class="member-initials d-none">
                                                    <?= htmlspecialchars(getInitials($t['nama'])) ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="member-initials">
                                                    <?= htmlspecialchars(getInitials($t['nama'])) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="member-info text-center">
                                            <h3 class="member-name"><?= htmlspecialchars($t['nama']) ?></h3>
                                            <div class="member-role"><?= htmlspecialchars($t['jabatan'] ?: 'Member') ?></div>
                                            <?php if (!empty($t['email'])): ?>
                                                <div class="member-email">
                                                    <?= htmlspecialchars($t['email']) ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($t['google_scholar'])): ?>
                                                <div class="member-scholar">
                                                    <a href="<?= htmlspecialchars($t['google_scholar']) ?>" target="_blank" title="Google Scholar">
                                                        <img src="assets/google-scholar.png" alt="Google Scholar" width="28">
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($t['deskripsi'])): ?>
                                                <p class="member-desc" style="display:none;">
                                                    <?= htmlspecialchars($t['deskripsi']) ?>
                                                </p>
                                                <a href="javascript:void(0)" class="member-toggle">
                                                    More Info
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-pagination"></div>
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

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script>
        // Init partner slider with centered slides - always loop
        new Swiper(".partnersSwiper", {
            slidesPerView: 1,
            spaceBetween: 30,
            centeredSlides: true,
            loop: true, // Always loop for infinite effect
            loopAdditionalSlides: 2, // Add extra slides for smooth looping
            autoplay: { 
                delay: 3000, 
                disableOnInteraction: false 
            },
            navigation: { 
                nextEl: ".partnersSwiper .swiper-button-next", 
                prevEl: ".partnersSwiper .swiper-button-prev" 
            },
            pagination: {
                el: ".partnersSwiper .swiper-pagination",
                clickable: true
            },
            breakpoints: { 
                0: { 
                    slidesPerView: 1, 
                    spaceBetween: 20,
                    centeredSlides: true
                }, 
                576: { 
                    slidesPerView: 2, 
                    spaceBetween: 25,
                    centeredSlides: true
                }, 
                768: { 
                    slidesPerView: 3, 
                    spaceBetween: 30,
                    centeredSlides: true
                }, 
                992: { 
                    slidesPerView: 4, 
                    spaceBetween: 35,
                    centeredSlides: true
                }, 
                1200: { 
                    slidesPerView: 5, 
                    spaceBetween: 50,
                    centeredSlides: true
                } 
            }
        });

        // Init team slider with centered slides - always loop
        new Swiper(".teamSwiper", {
            slidesPerView: 1,
            spaceBetween: 30,
            centeredSlides: true,
            loop: true, // Always loop for infinite effect
            loopAdditionalSlides: 2, // Add extra slides for smooth looping
            autoplay: { 
                delay: 3000, 
                disableOnInteraction: false 
            },
            navigation: { 
                nextEl: ".teamSwiper .swiper-button-next", 
                prevEl: ".teamSwiper .swiper-button-prev" 
            },
            pagination: {
                el: ".teamSwiper .swiper-pagination",
                clickable: true
            },
            breakpoints: {
                0: { 
                    slidesPerView: 1, 
                    spaceBetween: 20,
                    centeredSlides: true
                },
                576: { 
                    slidesPerView: 2, 
                    spaceBetween: 25,
                    centeredSlides: true
                },
                768: { 
                    slidesPerView: 3, 
                    spaceBetween: 30,
                    centeredSlides: true
                },
                992: { 
                    slidesPerView: 4, 
                    spaceBetween: 30,
                    centeredSlides: true
                },
                1200: { 
                    slidesPerView: 4, 
                    spaceBetween: 30,
                    centeredSlides: true
                }
            }
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
        
        // Read More functionality for Research
        function toggleResearchDescription(btn) {
            const wrapper = btn.closest('.research-description-wrapper');
            const description = wrapper.querySelector('.research-item-description');
            const readmoreText = btn.querySelector('.readmore-text');
            const readlessText = btn.querySelector('.readless-text');
            
            if (description.classList.contains('research-description-collapsed')) {
                // Expand
                description.style.maxHeight = description.scrollHeight + 'px';
                description.classList.remove('research-description-collapsed');
                readmoreText.style.display = 'none';
                readlessText.style.display = 'inline';
                
                // After animation, remove max-height to allow natural height
                setTimeout(() => {
                    description.style.maxHeight = 'none';
                }, 300);
            } else {
                // Collapse
                description.style.maxHeight = description.scrollHeight + 'px';
                // Force reflow
                description.offsetHeight;
                description.style.maxHeight = '5.1em';
                description.classList.add('research-description-collapsed');
                readmoreText.style.display = 'inline';
                readlessText.style.display = 'none';
            }
        }
        
        // Read More functionality for Research Fields
        function toggleResearchField(btn) {
            const wrapper = btn.closest('.rf-content-wrapper');
            const content = wrapper.querySelector('.rf-card-list');
            const readmoreText = btn.querySelector('.readmore-text');
            const readlessText = btn.querySelector('.readless-text');
            
            if (content.classList.contains('rf-content-collapsed')) {
                // Expand
                content.style.maxHeight = content.scrollHeight + 'px';
                content.classList.remove('rf-content-collapsed');
                readmoreText.style.display = 'none';
                readlessText.style.display = 'inline';
                
                // After animation, remove max-height to allow natural height
                setTimeout(() => {
                    content.style.maxHeight = 'none';
                }, 300);
            } else {
                // Collapse
                content.style.maxHeight = content.scrollHeight + 'px';
                // Force reflow
                content.offsetHeight;
                content.style.maxHeight = '180px';
                content.classList.add('rf-content-collapsed');
                readmoreText.style.display = 'inline';
                readlessText.style.display = 'none';
            }
        }
    </script>
    <link rel="stylesheet" href="assets/css/style-home-extra.css">

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".member-toggle").forEach(function (btn) {
                btn.addEventListener("click", function () {
                    const desc = btn.previousElementSibling;
                    if (!desc) return;

                    if (desc.style.display === "block") {
                        desc.style.display = "none";
                        btn.textContent = "More Info";
                    } else {
                        desc.style.display = "block";
                        btn.textContent = "Less Info";
                    }
                });
            });
        });

        // Gallery Masonry Layout and Infinite Scroll
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

                fetch('index.php?action=load_gallery&gpage=' + gpage)
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
    </script>
</body>

</html>