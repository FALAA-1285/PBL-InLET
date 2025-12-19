# PERSON 2: Frontend Developer - Research & Members Pages

## 📋 **TUGAS UTAMA**

### **1. Research Page (research.php)**
- Display research fields dengan detail
- Search dan filter functionality (kategori, tahun, dll)
- Pagination dengan page numbers
- Read more/less untuk deskripsi panjang
- Sorting options (by name, date, dll)
- Research progress cards dengan published articles
- YouTube video integration

### **2. Members Page (member.php)**
- Team members listing dengan filter
- Alphabet index navigation
- Search functionality dengan real-time filtering
- Member profile cards dengan foto
- Google Scholar integration dengan link
- Filter by role/position
- Pagination untuk member list

### **3. Styling**
- `css/style-research.css` - fully responsive
- `css/style-member.css` - fully responsive
- Responsive layouts untuk semua breakpoints
- Smooth transitions dan animations

---

## 📁 **FILE YANG DIKERJAKAN**

1. `research.php` - Halaman research dengan progress dan articles
2. `member.php` - Halaman members dengan search dan filter
3. `css/style-research.css` - Styling untuk research page
4. `css/style-member.css` - Styling untuk member page

---

## 💻 **KODE PROGRAM & PENJELASAN**

### **1. research.php**

#### **Bagian 1: Setup & Helper Functions**

```php
<?php
require_once 'config/database.php';
require_once 'config/settings.php';

$conn = getDBConnection();

// Get page title and subtitle
$page_info = getPageTitle('research');
```

**Penjelasan:**
- Setup koneksi database dan settings
- Ambil informasi halaman dari database

#### **Bagian 2: YouTube URL Processing Functions**

```php
function getYoutubeEmbedUrl($url)
{
    if (empty($url)) {
        return null;
    }

    $url = trim($url);
    $videoId = null;
    $startSeconds = null;

    // Extract YouTube video ID dari berbagai format
    if (preg_match('/youtu\.be\/([^\?\&]+)/', $url, $matches)) {
        $videoId = $matches[1];
    } elseif (preg_match('/youtube\.com\/shorts\/([^\?\&]+)/', $url, $matches)) {
        $videoId = $matches[1];
    } elseif (preg_match('/youtube\.com\/embed\/([^\?\&]+)/', $url, $matches)) {
        $videoId = $matches[1];
    } elseif (preg_match('/youtube\.com\/watch/', $url)) {
        $query = [];
        parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $query);
        if (!empty($query['v'])) {
            $videoId = $query['v'];
        }
        if (!empty($query['t'])) {
            $startSeconds = parseYoutubeTimecode($query['t']);
        } elseif (!empty($query['start'])) {
            $startSeconds = (int) $query['start'];
        }
    }

    if (!$videoId) {
        return null;
    }

    $videoId = preg_replace('/[^A-Za-z0-9_\-]/', '', $videoId);
    if ($videoId === '') {
        return null;
    }

    $embedUrl = 'https://www.youtube.com/embed/' . $videoId;
    if ($startSeconds !== null && $startSeconds > 0) {
        $embedUrl .= '?start=' . $startSeconds;
    }

    return $embedUrl;
}
```

**Penjelasan:**
- Extract video ID dari berbagai format URL YouTube (youtu.be, shorts, embed, watch)
- Parse query parameters untuk start time
- Sanitize video ID untuk keamanan
- Return embed URL dengan start time jika ada

```php
function parseYoutubeTimecode($value)
{
    if (preg_match('/^\d+$/', $value)) {
        return (int) $value;
    }

    if (preg_match('/((\d+)h)?((\d+)m)?((\d+)s)?/', $value, $matches)) {
        $hours = isset($matches[2]) ? (int) $matches[2] : 0;
        $minutes = isset($matches[4]) ? (int) $matches[4] : 0;
        $seconds = isset($matches[6]) ? (int) $matches[6] : 0;
        return ($hours * 3600) + ($minutes * 60) + $seconds;
    }

    return null;
}
```

**Penjelasan:**
- Parse timecode format seperti "1h30m15s" atau "90m" menjadi detik
- Support format: hours, minutes, seconds
- Return null jika format tidak valid

```php
function extractUrlFromContent($content)
{
    if (empty($content)) {
        return null;
    }
    
    // Pattern to match URLs (http, https, www)
    $pattern = '/(https?:\/\/[^\s]+|www\.[^\s]+)/i';
    
    if (preg_match($pattern, $content, $matches)) {
        $url = trim($matches[1]);
        // Add http:// if URL starts with www.
        if (preg_match('/^www\./i', $url)) {
            $url = 'http://' . $url;
        }
        return $url;
    }
    
    return null;
}
```

**Penjelasan:**
- Extract URL dari konten artikel
- Support http, https, dan www
- Auto-add http:// untuk www URLs

#### **Bagian 3: Search & Pagination Setup**

```php
// Search by year
$search_year = isset($_GET['year']) ? trim($_GET['year']) : '';

// Articles pagination
$items_per_page = 9; // 9 items per page for grid layout
$current_page_artikel = isset($_GET['page_artikel']) ? max(1, intval($_GET['page_artikel'])) : 1;
$offset_artikel = ($current_page_artikel - 1) * $items_per_page;
```

**Penjelasan:**
- Setup search parameter untuk filter by year
- Pagination: 9 items per halaman untuk grid layout
- Validasi halaman saat ini

#### **Bagian 4: Fetch Articles dengan Search**

```php
// Count articles with search
if (!empty($search_year) && is_numeric($search_year)) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM artikel WHERE tahun = :year");
    $stmt->execute([':year' => (int) $search_year]);
    $total_items_artikel = $stmt->fetchColumn();

    // Fetch articles with pagination and search
    $stmt = $conn->prepare("SELECT * FROM artikel WHERE tahun = :year ORDER BY tahun DESC, judul LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':year', (int) $search_year, PDO::PARAM_INT);
} else {
    $stmt = $conn->query("SELECT COUNT(*) FROM artikel");
    $total_items_artikel = $stmt->fetchColumn();

    // Fetch articles with pagination
    $stmt = $conn->prepare("SELECT * FROM artikel ORDER BY tahun DESC, judul LIMIT :limit OFFSET :offset");
}
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset_artikel, PDO::PARAM_INT);
$stmt->execute();
$artikels = $stmt->fetchAll();
$total_pages_artikel = ceil($total_items_artikel / $items_per_page);
```

**Penjelasan:**
- Conditional query berdasarkan search year
- Menggunakan prepared statements untuk keamanan
- Sorting: tahun DESC, kemudian judul ASC
- Calculate total pages untuk pagination

#### **Bagian 5: Fetch Research Progress**

```php
// Progress pagination - using penelitian (research) table
$current_page_progress = isset($_GET['page_progress']) ? max(1, intval($_GET['page_progress'])) : 1;
$offset_progress = ($current_page_progress - 1) * $items_per_page;

// Count progress items from penelitian
try {
    $stmt = $conn->query("SELECT COUNT(*) FROM penelitian");
    $total_items_progress = $stmt->fetchColumn();
} catch (PDOException $e) {
    $total_items_progress = 0;
}
$total_pages_progress = ceil($total_items_progress / $items_per_page);

// Fetch progress with pagination from penelitian
try {
    $stmt = $conn->prepare("SELECT p.*
                      FROM penelitian p
                      ORDER BY p.created_at DESC, p.tgl_mulai DESC
                      LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset_progress, PDO::PARAM_INT);
    $stmt->execute();
    $progress_list = $stmt->fetchAll();
    
    // Check if id_penelitian column exists in artikel table
    try {
        $check_col = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'artikel' AND column_name = 'id_penelitian'");
        $has_id_penelitian = $check_col->rowCount() > 0;
    } catch (PDOException $e) {
        $has_id_penelitian = false;
    }
    
    // Get all articles grouped by research
    if ($has_id_penelitian) {
        $stmt_articles = $conn->query("SELECT a.*, p.id_penelitian, p.judul as penelitian_judul 
                                       FROM artikel a 
                                       LEFT JOIN penelitian p ON a.id_penelitian = p.id_penelitian 
                                       ORDER BY p.id_penelitian, a.tahun DESC, a.judul");
    } else {
        // Fallback if column doesn't exist yet
        $stmt_articles = $conn->query("SELECT a.*, NULL as id_penelitian, NULL as penelitian_judul 
                                       FROM artikel a 
                                       ORDER BY a.tahun DESC, a.judul");
    }
    $all_articles = $stmt_articles->fetchAll();
    
    // Group articles by research
    $articles_by_research = [];
    foreach ($all_articles as $article) {
        $research_id = $article['id_penelitian'] ?? 'unassigned';
        if (!isset($articles_by_research[$research_id])) {
            $articles_by_research[$research_id] = [];
        }
        $articles_by_research[$research_id][] = $article;
    }
} catch (PDOException $e) {
    $progress_list = [];
    $all_articles = [];
    $articles_by_research = [];
}
```

**Penjelasan:**
- Pagination terpisah untuk research progress
- Dynamic column detection untuk kompatibilitas
- Group articles by research ID untuk display di progress card
- Error handling yang robust

#### **Bagian 6: Research Progress Card HTML**

```php
<?php foreach ($progress_list as $progress): ?>
    <div class="research-progress-card">
        <div class="research-progress-header">
            <div class="research-progress-title-section">
                <h3 class="research-progress-title"><?php echo htmlspecialchars($progress['judul'] ?? 'Research Project'); ?></h3>
                <p class="research-progress-subtitle"><?php echo htmlspecialchars(strtoupper($progress['judul'] ?? 'RESEARCH PROJECT')); ?></p>
            </div>
            <div class="research-progress-period">
                <?php 
                $tgl_mulai = $progress['tgl_mulai'] ?? null;
                $tgl_selesai = $progress['tgl_selesai'] ?? null;
                
                if ($tgl_mulai) {
                    $date_mulai = date('F Y', strtotime($tgl_mulai));
                    if ($tgl_selesai) {
                        $date_selesai = date('F Y', strtotime($tgl_selesai));
                        $period_text = $date_mulai . ' - ' . $date_selesai;
                    } else {
                        $period_text = $date_mulai . ' - Present';
                    }
                } else {
                    $period_text = 'January 2021 - Present';
                }
                ?>
                <span class="period-text"><?php echo htmlspecialchars($period_text); ?></span>
            </div>
        </div>
        
        <div class="research-progress-description">
            <p><?php echo htmlspecialchars($progress['deskripsi'] ?? 'Research description not available.'); ?></p>
        </div>
        
        <?php if (!empty($progress['video_url'])): ?>
            <?php $embedUrl = getYoutubeEmbedUrl($progress['video_url']); ?>
            <?php if ($embedUrl): ?>
                <div class="research-progress-video">
                    <div class="video-wrapper">
                        <iframe src="<?php echo htmlspecialchars($embedUrl); ?>"
                            title="YouTube video player" frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen loading="lazy"></iframe>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <?php 
        // Get articles for this specific research
        $research_articles = [];
        if (isset($articles_by_research[$progress['id_penelitian']])) {
            $research_articles = $articles_by_research[$progress['id_penelitian']];
        }
        ?>
        <?php if (!empty($research_articles)): ?>
            <div class="published-articles-section">
                <div class="published-articles-header">
                    <h4 class="published-articles-title">Published article :</h4>
                    <button class="btn-show-details" onclick="toggleArticleDetails(this, '<?php echo $progress['id_penelitian']; ?>')">
                        <span class="btn-text">Show Details</span>
                        <i class="fas fa-chevron-down btn-icon"></i>
                    </button>
                </div>
                <div class="published-articles-content" id="articles-<?php echo $progress['id_penelitian']; ?>" style="display: none;">
                    <table class="published-articles-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Title</th>
                                <th>Year</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $article_no = 1;
                            foreach ($research_articles as $artikel): 
                                $article_url = extractUrlFromContent($artikel['konten'] ?? '');
                            ?>
                                <tr>
                                    <td><?php echo $article_no++; ?></td>
                                    <td>
                                        <?php if ($article_url): ?>
                                            <a href="<?php echo htmlspecialchars($article_url); ?>" target="_blank" rel="noopener noreferrer" class="article-link">
                                                <?php echo htmlspecialchars($artikel['judul']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="article-title"><?php echo htmlspecialchars($artikel['judul']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="article-year"><?php echo htmlspecialchars($artikel['tahun'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
```

**Penjelasan:**
- Card layout dengan header, description, video, dan published articles
- Format period: "Month Year - Month Year" atau "Month Year - Present"
- YouTube video embed dengan lazy loading
- Toggle untuk show/hide published articles table
- Extract URL dari konten untuk link artikel

#### **Bagian 7: JavaScript - Toggle Article Details**

```javascript
function toggleArticleDetails(button, researchId) {
    const content = document.getElementById('articles-' + researchId);
    const btnText = button.querySelector('.btn-text');
    const btnIcon = button.querySelector('.btn-icon');
    
    if (!content) return;
    
    if (content.style.display === 'none' || content.style.display === '') {
        // Show details dengan animasi
        content.style.display = 'block';
        content.style.maxHeight = '0';
        content.style.opacity = '0';
        content.style.overflow = 'hidden';
        content.style.transition = 'max-height 0.3s ease, opacity 0.3s ease';
        
        // Force reflow
        content.offsetHeight;
        
        // Animate to full height
        content.style.maxHeight = content.scrollHeight + 'px';
        content.style.opacity = '1';
        
        btnText.textContent = 'Hide Details';
        btnIcon.classList.remove('fa-chevron-down');
        btnIcon.classList.add('fa-chevron-up');
        button.classList.add('active');
        
        // Remove max-height after animation
        setTimeout(() => {
            content.style.maxHeight = 'none';
            content.style.overflow = 'visible';
        }, 300);
    } else {
        // Hide details dengan animasi
        content.style.maxHeight = content.scrollHeight + 'px';
        content.style.overflow = 'hidden';
        
        // Force reflow
        content.offsetHeight;
        
        // Animate to collapsed
        content.style.maxHeight = '0';
        content.style.opacity = '0';
        
        btnText.textContent = 'Show Details';
        btnIcon.classList.remove('fa-chevron-up');
        btnIcon.classList.add('fa-chevron-down');
        button.classList.remove('active');
        
        // Hide after animation
        setTimeout(() => {
            content.style.display = 'none';
        }, 300);
    }
}
```

**Penjelasan:**
- Smooth expand/collapse animation dengan max-height dan opacity
- Toggle icon chevron up/down
- Force reflow untuk memastikan animasi berjalan
- Remove max-height setelah animasi untuk natural height

---

### **2. member.php**

#### **Bagian 1: Setup & Search/Filter**

```php
<?php
require_once 'config/database.php';
require_once 'config/settings.php';

$conn = getDBConnection();

// Get page title and subtitle
$page_info = getPageTitle('member');

// Search and filter setup
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$letter_filter = isset($_GET['letter']) ? strtoupper(trim($_GET['letter'])) : '';

// Pagination setup
$items_per_page = 8; // 8 items per page for grid layout
$current_page_members = isset($_GET['page_members']) ? max(1, intval($_GET['page_members'])) : 1;
$offset_members = ($current_page_members - 1) * $items_per_page;
```

**Penjelasan:**
- Setup search query dan letter filter
- Pagination: 8 items per halaman untuk grid layout

#### **Bagian 2: Build WHERE Clause Dinamis**

```php
// Build WHERE clause
$where_clauses = [];
$params = [];

if (!empty($search_query)) {
    $where_clauses[] = "nama ILIKE :search";
    $params[':search'] = '%' . $search_query . '%';
}

if (!empty($letter_filter) && strlen($letter_filter) == 1 && ctype_alpha($letter_filter)) {
    $where_clauses[] = "UPPER(SUBSTRING(nama FROM 1 FOR 1)) = :letter";
    $params[':letter'] = $letter_filter;
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';
```

**Penjelasan:**
- Build WHERE clause dinamis berdasarkan search dan filter
- ILIKE untuk case-insensitive search
- Validasi letter filter (harus 1 karakter alphabet)
- Combine multiple conditions dengan AND

#### **Bagian 3: Count & Fetch Members**

```php
// Count total items
$count_sql = "SELECT COUNT(*) FROM member $where_sql";
$stmt = $conn->prepare($count_sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$total_items_members = (int) $stmt->fetchColumn();
$total_pages_members = max(1, ($items_per_page > 0 && $total_items_members > 0) ? (int) ceil($total_items_members / $items_per_page) : 1);
$current_page_members = min(max(1, $current_page_members), $total_pages_members);
$offset_members = ($current_page_members - 1) * $items_per_page;

// Fetch members with search and pagination
// Order by: Ketua Lab first, then others by name
$query_sql = "SELECT * FROM member $where_sql 
              ORDER BY 
                CASE 
                  WHEN LOWER(jabatan) LIKE '%ketua lab%' THEN 0 
                  ELSE 1 
                END,
                nama 
              LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($query_sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset_members, PDO::PARAM_INT);
$stmt->execute();
$members = $stmt->fetchAll();
```

**Penjelasan:**
- Count dengan WHERE clause yang sama
- Validasi halaman saat ini (min 1, max total_pages)
- Sorting: Ketua Lab di urutan pertama, kemudian alphabetically
- Bind parameters untuk keamanan

#### **Bagian 4: Get Available Letters untuk Alphabet Index**

```php
// Get first letters for index
$stmt = $conn->query("SELECT DISTINCT UPPER(SUBSTRING(nama FROM 1 FOR 1)) as first_letter FROM member WHERE nama IS NOT NULL AND nama != '' ORDER BY first_letter");
$available_letters = $stmt->fetchAll(PDO::FETCH_COLUMN);
```

**Penjelasan:**
- Ambil huruf pertama yang tersedia untuk alphabet navigation
- DISTINCT untuk unique letters
- ORDER BY untuk sorting alphabetically

#### **Bagian 5: Member Card HTML**

```php
<?php foreach ($members as $member): ?>
    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
        <div class="member-card card-surface h-100">
            <?php
            // Handle image
            $has_photo = false;
            $foto_url = '';
            if (!empty($member['foto'])) {
                $foto_url = $member['foto'];
                // If not HTTP URL, assume local file
                if (!preg_match('/^https?:\/\//', $foto_url)) {
                    // Add uploads/ prefix if missing
                    if (strpos($foto_url, 'uploads/') !== 0) {
                        $foto_url = 'uploads/' . ltrim($foto_url, '/');
                    }
                }
                $has_photo = true;
            }
            ?>

            <div class="member-img-wrapper">
                <?php if ($has_photo): ?>
                    <img src="<?php echo htmlspecialchars($foto_url); ?>"
                        alt="<?php echo htmlspecialchars($member['nama']); ?>" 
                        class="member-img"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="member-initials d-none">
                        <?php echo getInitials($member['nama']); ?>
                    </div>
                <?php else: ?>
                    <div class="member-initials">
                        <?php echo getInitials($member['nama']); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="member-info text-center">
                <h3 class="member-name"><?php echo htmlspecialchars($member['nama']); ?></h3>
                <div class="member-role"><?php echo htmlspecialchars($member['jabatan'] ?: 'Member'); ?></div>
                <?php if (!empty($member['email'])): ?>
                    <div class="member-email">
                        <?php echo htmlspecialchars($member['email']); ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($member['google_scholar'])): ?>
                    <div class="member-scholar">
                        <a href="<?php echo htmlspecialchars($member['google_scholar']); ?>" target="_blank" title="Google Scholar">
                            <img src="assets/google-scholar.png" alt="Google Scholar" width="28">
                        </a>
                    </div>
                <?php endif; ?>
                <?php if (!empty($member['deskripsi'])): ?>
                    <p class="member-desc" style="display:none;">
                        <?php echo htmlspecialchars($member['deskripsi']); ?>
                    </p>
                    <a href="javascript:void(0)" class="member-toggle">
                        More Info
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
```

**Penjelasan:**
- Responsive grid: 1 col mobile, 2 col tablet, 3-4 col desktop
- Normalisasi path foto (tambah 'uploads/' jika perlu)
- Fallback ke initials jika foto tidak ada atau gagal load
- Toggle untuk show/hide deskripsi
- Google Scholar link dengan icon

#### **Bagian 6: Pagination dengan Preserve Search/Filter**

```php
<?php if ($total_pages_members > 1): ?>
    <nav aria-label="Members pagination" class="mt-5">
        <ul class="pagination justify-content-center pagination-gap">
            <?php
            $page_url = "?";
            if (!empty($search_query)) {
                $page_url .= "search=" . urlencode($search_query) . "&";
            }
            if (!empty($letter_filter)) {
                $page_url .= "letter=" . urlencode($letter_filter) . "&";
            }
            ?>
            
            <!-- Pagination links dengan $page_url -->
        </ul>
    </nav>
<?php endif; ?>
```

**Penjelasan:**
- Preserve search query dan letter filter di pagination links
- URL encoding untuk keamanan
- Build URL parameter dinamis

#### **Bagian 7: JavaScript - Toggle Member Description**

```javascript
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
```

**Penjelasan:**
- Toggle show/hide deskripsi member
- Change button text sesuai state
- Simple toggle tanpa animasi

---

### **3. css/style-research.css**

#### **Bagian 1: Research Progress Card Styling**

```css
.research-progress-list {
    display: flex;
    flex-direction: column;
    gap: 2rem;
    max-width: 1200px;
    margin: 0 auto;
}

.research-progress-card {
    background: var(--card-bg);
    border-radius: 16px;
    padding: 2.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.05);
    margin-bottom: 2rem;
}

.research-progress-card:hover {
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
    transform: translateY(-2px);
}

.research-progress-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}
```

**Penjelasan:**
- Flexbox column layout dengan gap
- Card dengan shadow dan border radius
- Hover effect dengan transform dan shadow
- Responsive header dengan flex-wrap

#### **Bagian 2: Published Articles Table Styling**

```css
.published-articles-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 0.5rem;
    border-radius: 8px;
    overflow: hidden;
}

.published-articles-table thead {
    background: var(--light);
}

.published-articles-table th {
    padding: 0.75rem 1rem;
    text-align: left;
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--gray);
    border-bottom: 2px solid #e2e8f0;
}

.published-articles-table tbody tr:hover {
    background: var(--light);
    transition: background 0.2s ease;
}

.article-link {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s ease;
}

.article-link:hover {
    color: var(--primary-dark);
    text-decoration: underline;
}
```

**Penjelasan:**
- Modern table design dengan rounded corners
- Hover effect pada rows
- Link styling dengan hover underline

---

### **4. css/style-member.css**

#### **Bagian 1: Member Card Styling**

```css
.member-card {
    background: var(--card-bg);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    height: 100%;
    border: none;
    display: flex;
    flex-direction: column;
    position: relative;
}

.member-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: var(--gradient-accent);
}

.member-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--hover-shadow);
}
```

**Penjelasan:**
- Card dengan gradient accent bar di atas
- Hover effect dengan lift dan shadow
- Flexbox column untuk layout

#### **Bagian 2: Member Image & Initials**

```css
.member-img-wrapper {
    width: 120px;
    height: 120px;
    margin: 2rem auto 1rem;
    position: relative;
}

.member-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    border: 4px solid #fff;
    box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    background-color: #eee;
}

.member-initials {
    width: 100%;
    height: 100%;
    background: var(--gradient-accent);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: bold;
    border: 4px solid #fff;
    box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    user-select: none;
}
```

**Penjelasan:**
- Circular image dengan border dan shadow
- Fallback initials dengan gradient background
- Centered dengan flexbox

---

## ✅ **DELIVERABLES**

1. ✅ Research page dengan search & filter yang functional
2. ✅ Members page dengan alphabet navigation
3. ✅ Responsive design untuk kedua halaman
4. ✅ Interactive filtering dan search
5. ✅ Pagination dengan preserve search/filter
6. ✅ YouTube video integration dengan timecode support
7. ✅ Published articles table dengan toggle
8. ✅ Member cards dengan Google Scholar integration
9. ✅ Smooth animations dan transitions

---

## 🎯 **FITUR UTAMA YANG DIIMPLEMENTASIKAN**

1. **Search & Filter**: Search by name, filter by alphabet letter
2. **Pagination**: Separate pagination untuk articles dan progress
3. **YouTube Integration**: Extract video ID dari berbagai format URL
4. **Timecode Support**: Parse dan embed YouTube dengan start time
5. **Published Articles**: Group articles by research dengan toggle table
6. **URL Extraction**: Extract URL dari konten untuk article links
7. **Alphabet Navigation**: Filter members by first letter
8. **Responsive Design**: Mobile-first dengan breakpoints
9. **Error Handling**: Fallback untuk foto, data kosong, dll
10. **Performance**: Lazy loading untuk YouTube videos

---

## 📝 **CATATAN PENTING**

- Menggunakan ILIKE untuk case-insensitive search di PostgreSQL
- Dynamic WHERE clause building untuk fleksibilitas filter
- Prepared statements untuk semua database queries
- XSS protection dengan `htmlspecialchars()`
- URL encoding untuk query parameters
- Responsive grid dengan Bootstrap classes
- Smooth animations dengan CSS transitions


