# PERSON 3: Frontend Developer - News Page & Gallery

## 📋 **TUGAS UTAMA**

### **1. News Page (news.php)**
- News listing dengan pagination
- News detail page dengan full content
- Search functionality
- Category filter (jika ada)
- Date sorting (newest/oldest)
- Related news section

### **2. Gallery Enhancement**
- Gallery display dengan lightbox/modal
- Image upload preview (jika ada)
- Image optimization & lazy loading
- Gallery categories/filtering
- Smooth image transitions
- Masonry layout dengan infinite scroll

### **3. Styling**
- `css/style-news.css` - fully responsive
- Gallery styling dengan lightbox
- Responsive image handling

---

## 📁 **FILE YANG DIKERJAKAN**

1. `news.php` - Halaman news dengan search, pagination, videos, dan gallery
2. `css/style-news.css` - Styling untuk news page

---

## 💻 **KODE PROGRAM & PENJELASAN**

### **1. news.php**

#### **Bagian 1: Setup & Search**

```php
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
```

**Penjelasan:**
- Setup koneksi database dan settings
- Ambil search query dari GET parameter
- Pagination: 9 items per halaman untuk grid layout

#### **Bagian 2: Fetch News dengan Search**

```php
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
```

**Penjelasan:**
- Conditional query berdasarkan search query
- ILIKE untuk case-insensitive search di PostgreSQL
- Sorting: newest first (created_at DESC)
- Calculate total pages untuk pagination

#### **Bagian 3: Fetch Videos & Gallery**

```php
// Fetch videos
$videos = [];
try {
    $video_stmt = $conn->query("SELECT * FROM video ORDER BY created_at DESC");
    $videos = $video_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $videos = [];
}

// ---------- GALLERY SOURCE ----------
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
```

**Penjelasan:**
- Fetch videos dengan error handling
- Fetch gallery dengan error handling
- Transform gallery data ke format yang konsisten
- Sort by created_at DESC (newest first)

#### **Bagian 4: AJAX Endpoint untuk Gallery Infinite Scroll**

```php
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
- Endpoint AJAX untuk load more gallery items
- Return JSON response dengan 12 items per request
- Initial load: 12 items pertama

#### **Bagian 5: Helper Function untuk Pagination URL**

```php
// Helper function untuk build pagination URL
function getPaginationUrl($page, $search = '')
{
    $params = ['page' => $page];
    if (!empty($search)) {
        $params['search'] = $search;
    }
    return 'news.php?' . http_build_query($params);
}
```

**Penjelasan:**
- Build pagination URL dengan preserve search query
- Menggunakan `http_build_query()` untuk URL encoding

#### **Bagian 6: Search Box HTML**

```php
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
```

**Penjelasan:**
- Search form dengan GET method
- Preserve search query di input value
- Reset button muncul jika ada search query
- Display search results count

#### **Bagian 7: News Grid HTML**

```php
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
                        <img src="<?php echo htmlspecialchars($news['gambar_thumbnail']); ?>" 
                             class="card-img-top"
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
```

**Penjelasan:**
- Responsive grid: 3 col desktop, 2 col tablet, 1 col mobile
- Thumbnail image dengan fallback "No Image"
- Format date: "Month Day, Year"
- Truncate konten ke 150 karakter dengan "..."
- Empty state dengan pesan berbeda untuk search vs no data

#### **Bagian 8: Pagination dengan Preserve Search**

```php
<?php if ($total_pages_news > 1): ?>
    <nav aria-label="News pagination" class="mt-5">
        <ul class="pagination justify-content-center pagination-gap">
            <?php
            $page_url = "?";
            if (!empty($search_query)) {
                $page_url .= "search=" . urlencode($search_query) . "&";
            }
            ?>
            
            <!-- Pagination links dengan preserve search -->
        </ul>
    </nav>
<?php endif; ?>
```

**Penjelasan:**
- Preserve search query di semua pagination links
- URL encoding untuk keamanan
- Standard pagination dengan ellipsis

#### **Bagian 9: Video Section dengan Swiper**

```php
<?php 
// Filter only YouTube videos
$youtube_videos = array_filter($videos, function($v) {
    $url = strtolower($v['href_link'] ?? '');
    return strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false;
});
$youtube_videos = array_values($youtube_videos); // Re-index array

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
- Extract video ID dari berbagai format URL
- Embed dengan iframe
- Swiper untuk navigasi slider

#### **Bagian 10: Gallery dengan Masonry Layout**

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
- Masonry grid layout
- Overlay dengan judul muncul saat hover
- Error handling untuk gambar yang gagal load
- Loader untuk infinite scroll

#### **Bagian 11: JavaScript - Masonry Layout & Infinite Scroll**

```javascript
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

    // Wait for images to load before layout
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

    // Resize handler dengan debounce
    let resizeTimer = null;
    window.addEventListener("resize", function () {
        if (resizeTimer) clearTimeout(resizeTimer);
        resizeTimer = setTimeout(masonryLayout, 120);
    });

    // Infinite scroll dengan debounce
    let scrollTimer = null;
    window.addEventListener('scroll', () => {
        if (scrollTimer) clearTimeout(scrollTimer);
        scrollTimer = setTimeout(() => {
            if ((window.innerHeight + window.scrollY) >= (document.body.offsetHeight - 200)) {
                loadMore();
            }
        }, 120);
    });
});
```

**Penjelasan:**
- Masonry layout algorithm manual (tanpa library)
- Responsive columns: 1 mobile, 2 tablet, 3 desktop
- Infinite scroll dengan debounce untuk performance
- Wait for images load sebelum layout
- Append items dinamis dengan error handling
- Resize handler dengan debounce

#### **Bagian 12: Video Swiper Initialization**

```javascript
// Video Swiper
new Swiper(".videoSwiper", {
    slidesPerView: 1,
    spaceBetween: 30,
    centeredSlides: true,
    loop: true,
    loopAdditionalSlides: 2,
    autoplay: false, // No autoplay for videos
    navigation: { 
        nextEl: ".videoSwiper .swiper-button-next", 
        prevEl: ".videoSwiper .swiper-button-prev" 
    },
    pagination: {
        el: ".videoSwiper .swiper-pagination",
        clickable: true
    },
    breakpoints: {
        0: { slidesPerView: 1, spaceBetween: 20 },
        768: { slidesPerView: 1, spaceBetween: 30 }
    }
});
```

**Penjelasan:**
- Swiper untuk video slider
- No autoplay (user mungkin sedang menonton)
- Infinite loop dengan centered slides
- Responsive breakpoints

---

### **2. css/style-news.css**

#### **Bagian 1: News Card Styling**

```css
.feature-card {
    background: #ffffff;
    border-radius: 18px;
    padding: 2rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    transition: .25s ease;
    cursor: pointer;
}

.feature-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
}

.feature-card h3 {
    color: var(--primary);
    font-weight: 700;
    font-size: 1.3rem;
}

.feature-card p {
    color: var(--gray);
    margin-top: .5rem;
    line-height: 1.5;
}

.feature-card img,
.feature-card .no-image {
    height: 220px;
    object-fit: cover;
    width: 100%;
    display: block;
}
```

**Penjelasan:**
- Card dengan rounded corners dan shadow
- Hover effect dengan lift dan shadow increase
- Fixed height untuk thumbnail (220px)
- Object-fit cover untuk maintain aspect ratio

#### **Bagian 2: Gallery Masonry Styling**

```css
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

.pin-img-wrapper {
    overflow: hidden;
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
    opacity: 0 !important;
    transition: opacity 0.3s ease;
    pointer-events: none;
}

.pin-item:hover .pin-overlay {
    opacity: 1 !important;
}
```

**Penjelasan:**
- Masonry item dengan hover effects
- Image zoom on hover (scale 1.1)
- Gradient overlay dengan fade-in on hover
- Smooth transitions

#### **Bagian 3: Search Box Styling**

```css
#news input[type="text"] {
    border-radius: 12px;
    padding: .75rem 1rem;
    border: 1px solid #ddd;
}

#news button {
    border-radius: 12px;
    padding: .75rem 1.1rem;
}
```

**Penjelasan:**
- Rounded input dan button
- Consistent padding dan border radius

#### **Bagian 4: Pagination Styling**

```css
.pagination-modern {
    gap: 0.35rem;
}

.pagination-modern .page-link {
    border: none;
    border-radius: 18px;
    padding: 0.65rem 1.3rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    color: #475569;
    background: #fff;
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
    transition: all 0.2s ease;
}

.pagination-modern .page-link:hover {
    color: var(--primary-color);
    transform: translateY(-1px);
}

.pagination-modern .page-item.active .page-link {
    background: var(--primary-color);
    color: #fff;
    box-shadow: 0 15px 30px rgba(13, 110, 253, 0.35);
}
```

**Penjelasan:**
- Modern pagination dengan rounded pills
- Hover effect dengan lift
- Active state dengan primary color
- Shadow effects untuk depth

---

## ✅ **DELIVERABLES**

1. ✅ News page dengan full CRUD display
2. ✅ Gallery dengan masonry layout & infinite scroll
3. ✅ Image optimization & lazy loading
4. ✅ Responsive design untuk semua halaman
5. ✅ Search functionality dengan preserve di pagination
6. ✅ Video section dengan Swiper
7. ✅ Smooth transitions dan animations
8. ✅ Error handling untuk images dan data kosong

---

## 🎯 **FITUR UTAMA YANG DIIMPLEMENTASIKAN**

1. **Search Functionality**: Search news by title dengan case-insensitive
2. **Pagination**: Preserve search query di pagination links
3. **Masonry Layout**: Pinterest-style gallery dengan infinite scroll
4. **Image Lazy Loading**: Wait for images load sebelum layout
5. **YouTube Integration**: Extract dan embed video dari berbagai format
6. **Responsive Design**: Mobile-first dengan breakpoints
7. **Error Handling**: Fallback untuk gambar, data kosong, dll
8. **Performance**: Debouncing untuk scroll dan resize events
9. **Smooth Animations**: Hover effects, transitions, masonry layout
10. **Empty States**: Different messages untuk search vs no data

---

## 📝 **CATATAN PENTING**

- Menggunakan ILIKE untuk case-insensitive search di PostgreSQL
- Preserve search query di semua pagination links
- Masonry layout algorithm manual tanpa library eksternal
- Debouncing untuk scroll dan resize untuk performance
- Wait for images load sebelum layout untuk akurasi
- Error handling yang robust untuk semua edge cases
- CSS cache busting dengan filemtime version


