<?php
// index.php (combined: page + AJAX endpoint for gallery)
require_once 'config/database.php';

$conn = getDBConnection();

// -------------------
// Get team members from database
// -------------------
$stmt = $conn->prepare("SELECT * 
                      FROM member 
                      ORDER BY nama");
$stmt->execute();
$team = $stmt->fetchAll();

// -------------------
// Dummy data for research
// -------------------
$riset = [];
for ($i = 1; $i <= 25; $i++) {
    $riset[] = [
        "judul" => "Research Field #$i",
        "deskripsi" => "A full description of research field number $i, explaining its focus and contributions."
    ];
}

// Get mitra from database
try {
    $mitra_stmt = $conn->query("SELECT * FROM mitra ORDER BY nama_institusi");
    $partners = $mitra_stmt->fetchAll();
    
    // If no mitra in database, use empty array
    if (empty($partners)) {
        $partners = [];
    }
} catch (PDOException $e) {
    // Fallback to empty array if table doesn't exist
    $partners = [];
}

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
        for ($i = 1; $i <= 100; $i++) {
            $w = rand(300, 450);
            $h = rand(250, 500);
            $all_gallery[] = ["gambar" => "https://picsum.photos/seed/$i/{$w}/{$h}", "judul" => ""];
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
    for ($i = 1; $i <= 100; $i++) {
        $w = rand(300, 450);
        $h = rand(250, 500);
        $all_gallery[] = ["img" => "https://picsum.photos/seed/$i/{$w}/{$h}"];
    }
}

// -------------------
// AJAX endpoint: load more gallery
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
// Pagination & slices
// -------------------
$research_limit = 9;
$rpage = isset($_GET["rpage"]) ? max(1, (int) $_GET["rpage"]) : 1;
$rstart = ($rpage - 1) * $research_limit;
$total_items = count($riset);
$total_pages = ceil($total_items / $research_limit);
$current_riset = array_slice($riset, $rstart, $research_limit);

// -------------------
// Pagination for gallery
// -------------------
$gallery_items_per_page = 12;
$gallery_page = isset($_GET['gpage']) ? max(1, (int) $_GET['gpage']) : 1;
$gallery_offset = ($gallery_page - 1) * $gallery_items_per_page;
$total_gallery_items = count($all_gallery);
$total_gallery_pages = ceil($total_gallery_items / $gallery_items_per_page);
$gallery_init = array_slice($all_gallery, $gallery_offset, $gallery_items_per_page);

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style-home.css">
</head>

<body>
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

        <!-- RESEARCH FIELDS -->
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
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($rpage > 1): ?>
                            <li class="page-item"><a class="page-link" href="?rpage=<?= $rpage - 1 ?>#bidang">Previous</a>
                            </li><?php endif; ?>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i == $rpage ? 'active' : '' ?>"><a class="page-link"
                                    href="?rpage=<?= $i ?>#bidang"><?= $i ?></a></li>
                        <?php endfor; ?>
                        <?php if ($rpage < $total_pages): ?>
                            <li class="page-item"><a class="page-link" href="?rpage=<?= $rpage + 1 ?>#bidang">Next</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
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
                    <?php if (empty($partners)): ?>
                        <div class="col-12 text-center">
                            <p class="text-muted">Belum ada mitra yang terdaftar.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($partners as $p): ?>
                            <div class="col-md-2 col-4 text-center">
                                <?php if (!empty($p['logo'])): ?>
                                    <img src="<?= htmlspecialchars($p['logo']) ?>"
                                         class="partner-logo img-fluid rounded shadow-sm" 
                                         alt="<?= htmlspecialchars($p['nama_institusi']) ?>"
                                         title="<?= htmlspecialchars($p['nama_institusi']) ?>"
                                         onerror="this.onerror=null; this.src='https://via.placeholder.com/200x100/cccccc/666666?text=' + encodeURIComponent('<?= htmlspecialchars($p['nama_institusi']) ?>');">
                                <?php else: ?>
                                    <div class="partner-logo img-fluid rounded shadow-sm d-flex align-items-center justify-content-center" 
                                         style="height: 100px; background: #f0f0f0; color: #666;">
                                        <?= htmlspecialchars($p['nama_institusi']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- TEAM -->
        <section class="py-5" id="team">
            <div class="container">
                <div class="section-title">
                    <h2>Expert Team</h2>
                    <p>The experts behind our innovations.</p>
                </div>
                <div class="swiper teamSwiper">
                    <div class="swiper-wrapper">
                        <?php 
                        // Get team members from database
                        if (!empty($team)): 
                            foreach ($team as $t): 
                                $has_photo = false;
                                $foto_url = '';
                                if (!empty($t['foto'])) {
                                    $foto_url = $t['foto'];
                                    if (!preg_match('/^https?:\/\//', $foto_url)) {
                                        if (strpos($foto_url, 'uploads/') !== 0) {
                                            $foto_url = 'uploads/' . ltrim($foto_url, '/');
                                        }
                                    }
                                    $has_photo = true;
                                }
                        ?>
                            <div class="swiper-slide">
                                <div class="member-card card-surface h-100 text-center">
                                    <div class="member-img-wrapper">
                                        <?php if ($has_photo): ?>
                                            <img src="<?php echo htmlspecialchars($foto_url); ?>" 
                                                 alt="<?php echo htmlspecialchars($t['nama']); ?>" 
                                                 class="member-img"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="member-initials" style="display: none;">
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
                        <?php 
                            endforeach; 
                        else:
                        ?>
                            <div class="swiper-slide">
                                <div class="member-card card-surface h-100 text-center">
                                    <div class="member-info">
                                        <p>Belum ada member yang terdaftar</p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
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

            // Disabled infinite scroll, using pagination instead
            // function loadMore() {
            //     if (isLoading || allLoaded) return;
            //     isLoading = true; document.getElementById('loader').style.display = 'block';
            //     fetch(`<?= basename($_SERVER['PHP_SELF']); ?>?action=load_gallery&page=${page}`)
            //         .then(r => r.json())
            //         .then(data => { if (!data || data.length === 0) { allLoaded = true; } else { appendItems(data); page++; } })
            //         .finally(() => { isLoading = false; document.getElementById('loader').style.display = 'none'; });
            // }

            // window.addEventListener('scroll', () => { if (window.innerHeight + window.scrollY > document.body.offsetHeight - 200) loadMore(); });
            window.addEventListener('resize', masonryLayout);

            // initial layout
            const imgs = container.querySelectorAll('img'); let loadedCount = 0;
            imgs.forEach(img => { if (img.complete) loadedCount++; else img.onload = img.onerror = () => { loadedCount++; if (loadedCount === imgs.length) masonryLayout(); } });
            if (loadedCount === imgs.length) masonryLayout();
        });
    </script>
</body>

</html>