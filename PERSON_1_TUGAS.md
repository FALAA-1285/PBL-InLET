# PERSON 1: Frontend Developer - Home Page & UI Components

## 📋 **TUGAS UTAMA**

### **1. Home Page (index.php)**
- Hero section dengan dynamic title/subtitle (dari database)
- Research section dengan read more/less functionality
- Research Fields section dengan pagination
- Video section dengan YouTube integration
- Products section dengan card layout
- Partners section dengan Swiper slider
- Team section dengan Swiper slider
- Gallery section dengan masonry layout & infinite scroll

### **2. UI Components & Styling**
- Header & Navigation (includes/header.php) - fully responsive
- Footer (includes/footer.php) - complete layout
- CSS styling (css/style-home.css) - responsive design
- Responsive design untuk mobile/tablet/desktop
- JavaScript untuk interaktifitas (swiper, masonry, read more, infinite scroll)

---

## 📁 **FILE YANG DIKERJAKAN**

1. `index.php` - Halaman utama website
2. `includes/header.php` - Komponen header/navigation
3. `includes/footer.php` - Komponen footer
4. `css/style-home.css` - Styling untuk halaman home

---

## 💻 **KODE PROGRAM & PENJELASAN**

### **1. index.php**

#### **Bagian 1: Setup & Konfigurasi**

```php
<?php
// Prevent caching to ensure fresh data
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

require_once 'config/database.php';
require_once 'config/settings.php';

$conn = getDBConnection();

// Get page title and subtitle
$page_info = getPageTitle('home');
```

**Penjelasan:**
- Header untuk mencegah caching agar data selalu fresh
- Mengambil koneksi database dan settings
- Mengambil informasi title dan subtitle halaman dari database

#### **Bagian 2: Helper Functions**

```php
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
```

**Penjelasan:**
- Fungsi untuk menjalankan query dengan error handling
- Mengembalikan array kosong jika terjadi error

```php
// Function to properly capitalize titles
function formatTitle($title) {
    if (empty($title)) return '';
    $title = trim($title);
    // Direct replacement for "information engineering" in any case variation
    $title = preg_replace('/\binformation\s+engineering\b/i', 'Information Engineering', $title);
    // ... (lanjutan logika formatting)
    return ucwords(strtolower($title));
}
```

**Penjelasan:**
- Memformat judul dengan benar
- Memastikan "Information Engineering" selalu ditulis dengan benar
- Menggunakan `ucwords()` untuk capitalize setiap kata

```php
// Initials helper
function getInitials($name)
{
    $words = preg_split('/\s+/', trim($name));
    $initials = '';
    foreach ($words as $word) {
        if ($word === '') continue;
        $initials .= mb_substr($word, 0, 1);
        if (mb_strlen($initials) >= 2) break;
    }
    return strtoupper($initials);
}
```

**Penjelasan:**
- Mengambil inisial dari nama (maksimal 2 huruf)
- Berguna untuk fallback jika foto member tidak ada

#### **Bagian 3: Fetch Data dari Database**

```php
// Fetch team
$team = safeQueryAll($conn, "SELECT * FROM member 
                              ORDER BY 
                                CASE 
                                  WHEN LOWER(jabatan) LIKE '%ketua lab%' THEN 0 
                                  ELSE 1 
                                END,
                                nama");

// Fetch products
$products = safeQueryAll($conn, "SELECT * FROM produk ORDER BY id_produk");

// Research fields from fokus_penelitian table
try {
    $stmt = $conn->prepare("SELECT id_fp, title as judul, deskripsi, detail FROM fokus_penelitian ORDER BY id_fp");
    $stmt->execute();
    $riset = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $riset = [];
}
```

**Penjelasan:**
- Mengambil data team dengan sorting: Ketua Lab di urutan pertama
- Mengambil data produk
- Mengambil data research fields dengan prepared statement untuk keamanan

#### **Bagian 4: Pagination untuk Research Fields**

```php
// Pagination for research fields
$perPage = 9;
$totalRF = count($riset);
$totalPagesRF = ($totalRF > 0) ? ceil($totalRF / $perPage) : 1;

// Current page
$pageRF = isset($_GET['rf']) ? (int) $_GET['rf'] : 1;
if ($pageRF < 1) $pageRF = 1;
if ($pageRF > $totalPagesRF) $pageRF = $totalPagesRF;

// Start index
$startRF = ($pageRF - 1) * $perPage;

// Paginated data
$research_fields_paginated = array_slice($riset, $startRF, $perPage);
```

**Penjelasan:**
- Setup pagination: 9 item per halaman
- Validasi halaman saat ini
- Mengambil data yang sesuai dengan halaman

#### **Bagian 5: Gallery dengan AJAX Endpoint**

```php
// ---------- GALLERY SOURCE ----------
$all_gallery = [];
try {
    $gallery_stmt = $conn->query("SELECT gambar, judul FROM gallery ORDER BY created_at DESC");
    $all_gallery = $gallery_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $all_gallery = [];
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

// Gallery initial load
$gallery_items_per_page = 12;
$total_gallery_items = count($all_gallery);
$gallery_init = array_slice($all_gallery, 0, $gallery_items_per_page);
```

**Penjelasan:**
- Mengambil semua data gallery dari database
- Endpoint AJAX untuk infinite scroll (mengembalikan JSON)
- Load awal: 12 item pertama

#### **Bagian 6: Hero Section (HTML)**

```php
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
```

**Penjelasan:**
- Hero section dengan background image
- Title dan subtitle dinamis dari database
- Fallback jika subtitle kosong

#### **Bagian 7: Research Section dengan Read More/Less**

```php
<?php foreach ($riset as $research_item): 
    $formatted_title = formatTitle($research_item['judul'] ?? '');
?>
    <div class="col-lg-6 col-md-12 mb-4">
        <div class="research-item-card card-surface h-100">
            <h3 class="research-item-title"><?= htmlspecialchars($formatted_title); ?></h3>
            <?php 
            $description = !empty($research_item['deskripsi']) ? $research_item['deskripsi'] : '...';
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
        </div>
    </div>
<?php endforeach; ?>
```

**Penjelasan:**
- Menampilkan research items dalam card layout
- Jika deskripsi > 150 karakter, tampilkan tombol "Read More"
- Menggunakan class `research-description-collapsed` untuk membatasi tinggi

#### **Bagian 8: Research Fields dengan Pagination**

```php
<?php foreach ($research_fields_paginated as $r): ?>
    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
        <div class="rf-card card-surface h-100">
            <h4 class="rf-card-title"><?= htmlspecialchars(formatTitle($r['judul'])) ?></h4>
            <div class="rf-card-content">
                <?php 
                $detail_text = $r['detail'] ?? $r['deskripsi'] ?? '';
                if (!empty($detail_text)) {
                    $lines = explode("\n", $detail_text);
                    $total_lines = count(array_filter($lines, function($l) { return !empty(trim($l)); }));
                    $needs_readmore = $total_lines > 5;
                ?>
                    <ul class="rf-card-list <?= $needs_readmore ? 'rf-content-collapsed' : '' ?>">
                        <?php
                        foreach ($lines as $line) {
                            $line = trim($line);
                            if (!empty($line)) {
                                if (strpos($line, '-') === 0) {
                                    $line = trim(substr($line, 1));
                                    echo '<li>' . htmlspecialchars($line) . '</li>';
                                } else {
                                    echo '<li class="no-bullet">' . htmlspecialchars($line) . '</li>';
                                }
                            }
                        }
                        ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
```

**Penjelasan:**
- Menampilkan research fields dalam grid 3 kolom
- Memproses detail text per baris
- Jika baris dimulai dengan "-", dijadikan bullet point
- Pagination ditampilkan di bawah

#### **Bagian 9: Video Section dengan Swiper**

```php
<?php 
// Filter only YouTube videos
$youtube_videos = array_filter($videos, function($v) {
    $url = strtolower($v['href_link'] ?? '');
    return strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false;
});

// Extract YouTube video ID
if (preg_match('/youtu\.be\/([^\?\&]+)/', $video_url, $matches)) {
    $video_id = $matches[1];
} elseif (preg_match('/youtube\.com\/watch\?v=([^\&\?]+)/', $video_url, $matches)) {
    $video_id = $matches[1];
}
?>
<div class="swiper videoSwiper">
    <div class="swiper-wrapper">
        <?php foreach ($videosForSlider as $v): ?>
            <div class="swiper-slide">
                <div class="video-slide-wrapper">
                    <iframe 
                        class="video-iframe"
                        src="https://www.youtube.com/embed/<?= $video_id ?>?enablejsapi=1" 
                        frameborder="0" 
                        allowfullscreen>
                    </iframe>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-pagination"></div>
</div>
```

**Penjelasan:**
- Filter hanya video YouTube
- Extract video ID dari berbagai format URL YouTube
- Embed video menggunakan iframe
- Swiper untuk navigasi slider

#### **Bagian 10: Partners Section dengan Swiper**

```php
<?php 
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
                    <img src="<?= htmlspecialchars($p['logo']) ?>" 
                         class="partner-logo img-fluid rounded shadow-sm"
                         alt="<?= htmlspecialchars($p['nama_institusi']) ?>"
                         onerror="this.onerror=null; this.src='...';">
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
```

**Penjelasan:**
- Duplikasi partners jika kurang dari 6 untuk infinite loop
- Tampilkan logo atau placeholder text
- Error handling untuk gambar yang gagal load

#### **Bagian 11: Team Section dengan Swiper**

```php
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
                <?php if (!empty($t['google_scholar'])): ?>
                    <div class="member-scholar">
                        <a href="<?= htmlspecialchars($t['google_scholar']) ?>" target="_blank">
                            <img src="assets/google-scholar.png" alt="Google Scholar" width="28">
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
```

**Penjelasan:**
- Normalisasi path foto (menambahkan 'uploads/' jika perlu)
- Fallback ke initials jika foto tidak ada atau gagal load
- Tampilkan Google Scholar link jika ada

#### **Bagian 12: Gallery dengan Masonry Layout**

```php
<?php if (!empty($all_gallery)): ?>
    <div id="pinterest-grid" class="pinterest-grid">
        <?php foreach ($gallery_init as $g): ?>
            <div class="pin-item">
                <div class="pin-img-wrapper">
                    <img src="<?php echo htmlspecialchars($g['img']); ?>" 
                         alt="Gallery Image"
                         onerror="this.onerror=null; this.src='...';">
                    <div class="pin-overlay">
                        <h5 class="pin-title"><?php echo htmlspecialchars($g['judul'] ?: 'Image'); ?></h5>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div id="loader" class="text-center mt-3 d-none">Loading more...</div>
<?php endif; ?>
```

**Penjelasan:**
- Grid masonry untuk layout Pinterest-style
- Overlay dengan judul muncul saat hover
- Loader untuk infinite scroll

#### **Bagian 13: JavaScript - Swiper Initialization**

```javascript
// Init partner slider with centered slides - always loop
new Swiper(".partnersSwiper", {
    slidesPerView: 1,
    spaceBetween: 30,
    centeredSlides: true,
    loop: true,
    loopAdditionalSlides: 2,
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
        0: { slidesPerView: 1, spaceBetween: 20 },
        576: { slidesPerView: 2, spaceBetween: 25 },
        768: { slidesPerView: 3, spaceBetween: 30 },
        992: { slidesPerView: 4, spaceBetween: 35 },
        1200: { slidesPerView: 5, spaceBetween: 50 }
    }
});
```

**Penjelasan:**
- Konfigurasi Swiper untuk partners slider
- Responsive breakpoints untuk berbagai ukuran layar
- Autoplay dengan delay 3 detik
- Infinite loop dengan centered slides

#### **Bagian 14: JavaScript - Masonry Layout**

```javascript
function masonryLayout() {
    const items = Array.from(container.querySelectorAll(".pin-item"));
    const columns = getColumns();

    if (columns === 1) {
        // Single column: flow layout
        container.style.height = 'auto';
        items.forEach(i => {
            i.style.position = 'static';
            i.style.width = '100%';
            i.style.marginBottom = gap + 'px';
        });
        return;
    }

    // Multi-column: absolute positioning
    items.forEach(i => {
        i.style.position = 'absolute';
        i.style.marginBottom = '0';
    });

    const containerWidth = container.clientWidth;
    const colWidth = Math.floor((containerWidth - (columns - 1) * gap) / columns);
    const colHeights = Array(columns).fill(0);

    items.forEach(item => {
        item.style.width = colWidth + 'px';
        const minCol = colHeights.indexOf(Math.min(...colHeights));
        const x = minCol * (colWidth + gap);
        const y = colHeights[minCol];
        item.style.transform = `translate(${x}px, ${y}px)`;
        item.classList.add('show');

        const h = item.offsetHeight;
        colHeights[minCol] += h + gap;
    });

    container.style.height = Math.max(...colHeights) + 'px';
}
```

**Penjelasan:**
- Algoritma masonry layout manual (tanpa library)
- Single column: flow layout biasa
- Multi-column: absolute positioning dengan perhitungan kolom terpendek
- Menunggu gambar load sebelum layout

#### **Bagian 15: JavaScript - Infinite Scroll Gallery**

```javascript
function loadMore() {
    if (isLoading || allLoaded) return;
    isLoading = true;
    const loader = document.getElementById("loader");
    if (loader) loader.style.display = "block";

    fetch('index.php?action=load_gallery&gpage=' + gpage)
        .then(r => r.json())
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

window.addEventListener('scroll', () => {
    if ((window.innerHeight + window.scrollY) >= (document.body.offsetHeight - 200)) {
        loadMore();
    }
});
```

**Penjelasan:**
- Load lebih banyak item saat user scroll mendekati bottom
- Menggunakan Fetch API untuk AJAX request
- Flag `isLoading` untuk mencegah multiple requests
- Flag `allLoaded` untuk stop loading jika sudah habis

#### **Bagian 16: JavaScript - Read More/Less Functionality**

```javascript
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
        
        setTimeout(() => {
            description.style.maxHeight = 'none';
        }, 300);
    } else {
        // Collapse
        description.style.maxHeight = description.scrollHeight + 'px';
        description.offsetHeight; // Force reflow
        description.style.maxHeight = '5.1em';
        description.classList.add('research-description-collapsed');
        readmoreText.style.display = 'inline';
        readlessText.style.display = 'none';
    }
}
```

**Penjelasan:**
- Toggle expand/collapse untuk deskripsi
- Animasi smooth dengan `maxHeight` transition
- Force reflow untuk memastikan animasi berjalan

---

### **2. includes/header.php**

#### **Bagian 1: Setup & Path Detection**

```php
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/settings.php';

$current_page = basename($_SERVER['PHP_SELF']);
$is_service_page = str_contains($_SERVER['PHP_SELF'], '/service/');
$root_base = $is_service_page ? '../' : '';
$service_base = $is_service_page ? '' : 'service/';

// Get site logo dynamically
$site_logo = getSiteLogo();
if (!empty($site_logo) && !preg_match('#^https?://#i', $site_logo)) {
    $site_logo = $root_base . ltrim($site_logo, '/');
}
?>
```

**Penjelasan:**
- Deteksi halaman saat ini untuk active link
- Deteksi apakah di folder service untuk path yang benar
- Ambil logo dari database dengan fallback

#### **Bagian 2: Navigation HTML**

```php
<header>
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow-sm">
        <div class="container">
            <a href="<?= $root_base ?>index.php" class="navbar-brand">
                <img src="<?= htmlspecialchars($site_logo); ?>" 
                     alt="Logo" 
                     class="logo-img" 
                     onerror="this.src='<?= $root_base ?>assets/logo.png'">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'index.php') ? 'active text-primary fw-bold' : ''; ?>"
                           href="<?= $root_base ?>index.php">Home</a>
                    </li>
                    <!-- ... menu lainnya ... -->
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="serviceDropdown" role="button" data-bs-toggle="dropdown">
                            Service
                        </a>
                        <ul class="dropdown-menu custom-dropdown shadow-sm border-0">
                            <li><a class="dropdown-item" href="<?= $service_base ?>peminjaman.php">Tool Loan</a></li>
                            <li><a class="dropdown-item" href="<?= $service_base ?>absen.php">Attendance</a></li>
                            <li><a class="dropdown-item" href="<?= $service_base ?>buku_tamu.php">Guestbook</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>
```

**Penjelasan:**
- Bootstrap navbar dengan fixed-top
- Active link detection berdasarkan `$current_page`
- Dropdown menu untuk Service
- Responsive dengan collapse di mobile

#### **Bagian 3: CSS Styling untuk Navbar**

```css
.navbar {
    transition: all 0.3s ease-in-out;
    font-family: "Poppins", sans-serif;
    background-color: #ffffff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
}

.navbar-nav .nav-link {
    color: #1f2937;
    font-weight: 500;
    padding: 0.7rem 1rem;
    font-size: 1rem;
    transition: all 0.25s ease;
    border-radius: 8px;
    margin: 0 0.1rem;
}

.navbar-nav .nav-link:hover {
    color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.04);
    transform: translateY(-1px);
}

.navbar-nav .nav-link.active {
    color: #0d6efd !important;
    font-weight: 700 !important;
    background-color: rgba(13, 110, 253, 0.08);
}
```

**Penjelasan:**
- Styling modern dengan hover effects
- Active state dengan background highlight
- Smooth transitions

---

### **3. includes/footer.php**

#### **Bagian 1: Setup & Data Fetching**

```php
<?php
require_once __DIR__ . '/../config/settings.php';

$is_service_page = str_contains($_SERVER['PHP_SELF'], '/service/');
$root_base = $is_service_page ? '../' : '';

// Get footer settings dynamically
$footer_settings = getFooterSettings();
$contact_info = getContactInfo();

// Get footer logo
$footer_logo = $footer_settings['logo'];
if (!empty($footer_logo) && !preg_match('#^https?://#i', $footer_logo)) {
    $footer_logo = $root_base . ltrim($footer_logo, '/');
} else {
    $footer_logo = $root_base . 'assets/logoPutih.png'; // Default fallback
}
?>
```

**Penjelasan:**
- Ambil settings footer dan contact info dari database
- Normalisasi path logo dengan fallback

#### **Bagian 2: Footer HTML Structure**

```php
<footer class="footer bg-dark text-white py-5 mt-5">
    <div class="container-fluid px-5">
        <div class="row align-items-center gy-4">
            <div class="col-lg-4 col-md-6 text-center text-md-start">
                <a href="#" class="d-inline-flex align-items-center mb-3">
                    <img src="<?= htmlspecialchars($footer_logo); ?>" alt="InLET Logo" width="130">
                </a>
                <?php if (!empty($footer_settings['title'])): ?>
                    <p class="text-light-soft mb-0"><?= htmlspecialchars($footer_settings['title']); ?></p>
                <?php else: ?>
                    <p class="text-light-soft mb-0">Information and Learning Engineering Technology</p>
                <?php endif; ?>
            </div>

            <div class="col-lg-4 col-md-6 text-center">
                <ul class="list-inline mb-3 footer-links">
                    <li class="list-inline-item mx-3"><a href="<?= $root_base ?>index.php">Home</a></li>
                    <li class="list-inline-item mx-3"><a href="<?= $root_base ?>research.php">Research</a></li>
                    <!-- ... menu lainnya ... -->
                </ul>
            </div>

            <div class="col-lg-4 col-md-12 text-center text-md-end">
                <?php if (!empty($contact_info['email'])): ?>
                    <p class="mb-2 contact-info">
                        <i class="bi bi-envelope-fill me-2"></i><?= htmlspecialchars($contact_info['email']); ?>
                    </p>
                <?php endif; ?>
                <!-- ... contact info lainnya ... -->
            </div>
        </div>

        <hr class="border-light-soft my-4">

        <div class="text-center">
            <?php if (!empty($footer_settings['copyright'])): ?>
                <p class="text-light-soft mb-0"><?= htmlspecialchars($footer_settings['copyright']); ?></p>
            <?php else: ?>
                <p class="text-light-soft mb-0">&copy; <?= date('Y'); ?> InLET - Information and Learning Engineering Technology</p>
            <?php endif; ?>
        </div>
    </div>
</footer>
```

**Penjelasan:**
- 3 kolom layout: Logo & Title, Menu Links, Contact Info
- Copyright dinamis dari database atau default
- Responsive dengan text alignment berbeda di mobile

---

### **4. css/style-home.css**

#### **Bagian 1: Base Styles & Hero Section**

```css
body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

main {
    flex: 1;
    padding-top: 80px; /* Space for fixed navbar */
    margin-top: 0;
}

.hero {
    min-height: 50vh;
    background-image: url("../assets/Teknik-Polinema.jpg");
    background-position: center center;
    background-repeat: no-repeat;
    background-size: cover;
    background-color: #0b1220;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.hero::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.35); /* Dark overlay */
}
```

**Penjelasan:**
- Flexbox layout untuk footer sticky
- Hero dengan background image dan overlay gelap
- Padding top untuk fixed navbar

#### **Bagian 2: Research Section Styling**

```css
.research-item-card {
    background: var(--card-bg);
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.05);
    border-left: 4px solid var(--primary-color);
}

.research-item-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
}

.research-description-collapsed {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    max-height: 5.1em;
    transition: max-height 0.3s ease;
}
```

**Penjelasan:**
- Card dengan border-left accent
- Hover effect dengan transform dan shadow
- CSS line-clamp untuk truncate text

#### **Bagian 3: Swiper Styling**

```css
.partnersSwiper {
    padding: 2rem 0;
    overflow: visible;
}

.partnersSwiper .swiper-slide {
    height: 180px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.3s ease;
}

.partnersSwiper .swiper-slide:hover {
    transform: scale(1.05);
}

.partner-logo {
    max-width: 250px;
    max-height: 120px;
    width: auto;
    height: auto;
    object-fit: contain;
    filter: grayscale(20%);
    transition: filter 0.3s ease;
}

.partner-logo:hover {
    filter: grayscale(0%);
}
```

**Penjelasan:**
- Swiper dengan centered slides
- Hover effect dengan scale dan grayscale removal
- Responsive sizing

#### **Bagian 4: Masonry Gallery Styling**

```css
.pinterest-grid {
    position: relative;
}

.pin-item {
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    transition: .25s ease;
}

.pin-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
}

.pin-img-wrapper img {
    width: 100%;
    display: block;
    border-radius: 16px;
    transition: transform 0.3s ease;
}

.pin-img-wrapper:hover img {
    transform: scale(1.1);
}

.pin-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.55), transparent);
    padding: 1rem;
    color: #fff;
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
}

.pin-item:hover .pin-overlay {
    opacity: 1;
}
```

**Penjelasan:**
- Masonry grid dengan absolute positioning
- Hover effect dengan image zoom dan overlay
- Gradient overlay untuk readability

#### **Bagian 5: Responsive Design**

```css
@media (max-width: 768px) {
    .hero {
        min-height: 40vh;
    }

    .hero h1 {
        font-size: 1.6rem;
    }

    .research-item-title {
        font-size: 1.25rem;
    }
    
    .rf-card {
        padding: 1.5rem;
    }
}

@media (max-width: 576px) {
    .hero {
        min-height: 35vh;
    }

    .hero h1 {
        font-size: 1.4rem;
    }
}
```

**Penjelasan:**
- Breakpoints untuk tablet dan mobile
- Adjust font sizes dan spacing
- Maintain readability di semua devices

---

## ✅ **DELIVERABLES**

1. ✅ Home page yang fully functional dan responsive
2. ✅ UI components yang reusable (header & footer)
3. ✅ Smooth animations dan transitions
4. ✅ Gallery dengan masonry layout & infinite scroll
5. ✅ Swiper sliders untuk partners, team, dan videos
6. ✅ Read more/less functionality untuk research descriptions
7. ✅ Pagination untuk research fields
8. ✅ YouTube video integration
9. ✅ Responsive design untuk semua breakpoints

---

## 🎯 **FITUR UTAMA YANG DIIMPLEMENTASIKAN**

1. **Dynamic Content dari Database**: Semua konten (title, subtitle, research, products, partners, team, gallery) diambil dari database
2. **Masonry Layout**: Gallery dengan layout Pinterest-style yang responsif
3. **Infinite Scroll**: Load lebih banyak gallery items saat scroll
4. **Swiper Integration**: Slider untuk partners, team, dan videos dengan autoplay
5. **Read More/Less**: Toggle untuk deskripsi panjang dengan animasi smooth
6. **Pagination**: Pagination untuk research fields dengan page numbers
7. **YouTube Integration**: Extract dan embed video YouTube dari berbagai format URL
8. **Responsive Design**: Mobile-first approach dengan breakpoints untuk semua devices
9. **Error Handling**: Fallback untuk gambar yang gagal load, data kosong, dll
10. **Performance**: Lazy loading, debouncing untuk scroll/resize events

---

## 📝 **CATATAN PENTING**

- Semua path relatif disesuaikan berdasarkan lokasi file (root vs service folder)
- Menggunakan prepared statements untuk keamanan SQL
- XSS protection dengan `htmlspecialchars()`
- Error handling yang robust untuk semua database queries
- CSS variables untuk konsistensi warna dan spacing
- JavaScript modular dan terorganisir dengan baik

