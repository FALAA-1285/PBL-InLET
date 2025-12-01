<?php
require_once 'config/database.php';

$conn = getDBConnection();

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

// Research fields (placeholder)
$riset = [];

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
    <title>Home - Information & Learning Engineering Technology</title>
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
                <h1 class="display-4 fw-bold">InLET - Information And Learning Engineering Technology</h1>
                <p class="lead mt-3">Transforming the future of language learning through advanced engineering.</p>
            </div>
        </section>

        <!-- Research section -->
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

        <section id="research-fields" class="py-5">
            <div class="container">
                <h2 class="section-title mb-4 text-center">Research Fields</h2>

                <div class="row">
                    <?php if (!empty($research_fields_paginated)): ?>
                        <?php foreach ($research_fields_paginated as $r): ?>
                            <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                                <div class="rf-card p-4 shadow-sm rounded h-100">
                                    <h4 class="fw-bold mb-2 text-center">
                                        <?= htmlspecialchars($r['judul']) ?>
                                    </h4>
                                    <p class="text-muted text-center mb-0">
                                        <?= htmlspecialchars($r['deskripsi']) ?>
                                    </p>
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

        <!-- Partners section -->
        <section class="py-5 bg-light" id="partner">
            <div class="container">
                <div class="section-title">
                    <h2>Our Partners</h2>
                    <p>Collaboration with academic and industry institutions.</p>
                </div>
                <div class="row justify-content-center g-4">
                    <?php if (empty($partners)): ?>
                        <div class="col-12">
                            <div class="empty-data-alert" role="alert">
                                <i class="fas fa-handshake fa-3x mb-3 text-muted"></i>
                                <p class="mb-0">No partners registered yet.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($partners as $p): ?>
                            <div class="col-md-2 col-4 text-center">
                                <?php if (!empty($p['logo'])): ?>
                                    <img src="<?= htmlspecialchars($p['logo']) ?>" class="partner-logo img-fluid rounded shadow-sm"
                                        alt="<?= htmlspecialchars($p['nama_institusi']) ?>"
                                        title="<?= htmlspecialchars($p['nama_institusi']) ?>"
                                        onerror="this.onerror=null; this.src='https://via.placeholder.com/200x100/cccccc/666666?text=' + encodeURIComponent('<?= addslashes(htmlspecialchars($p['nama_institusi'])) ?>');">
                                <?php else: ?>
                                    <div
                                        class="partner-logo img-fluid rounded shadow-sm d-flex align-items-center justify-content-center partner-placeholder">
                                        <?= htmlspecialchars($p['nama_institusi']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Team section -->
        <section class="py-5" id="team">
            <div class="container">
                <div class="section-title">
                    <h2>Expert Team</h2>
                    <p>The experts behind our innovations.</p>
                </div>
                <?php if (!empty($team)): ?>
                    <div class="swiper teamSwiper">
                        <div class="swiper-wrapper">
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
                                <div class="swiper-slide">
                                    <div class="member-card card-surface h-100 text-center">
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
                                                <p class="member-desc"><?= htmlspecialchars($t["deskripsi"]) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
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
        // Init swiper
        new Swiper(".teamSwiper", {
            slidesPerView: 3,
            spaceBetween: 30,
            navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" },
            breakpoints: { 0: { slidesPerView: 1 }, 576: { slidesPerView: 2 }, 992: { slidesPerView: 3 } }
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
    </script>
</body>

</html>