<?php
require_once 'config/database.php';
require_once 'config/settings.php';

$conn = getDBConnection();

// Get page title and subtitle
$page_info = getPageTitle('research');

function getYoutubeEmbedUrl($url)
{
    if (empty($url)) {
        return null;
    }

    $url = trim($url);
    if ($url === '') {
        return null;   
    }

    $videoId = null;
    $startSeconds = null;

    // Extract YouTube video ID
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

// Function to extract URL from content
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

// Search by year
$search_year = isset($_GET['year']) ? trim($_GET['year']) : '';

// Articles pagination
$items_per_page = 9; // 9 items per page for grid layout
$current_page_artikel = isset($_GET['page_artikel']) ? max(1, intval($_GET['page_artikel'])) : 1;
$offset_artikel = ($current_page_artikel - 1) * $items_per_page;

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

// Get unique years
$stmt = $conn->query("SELECT DISTINCT tahun FROM artikel WHERE tahun IS NOT NULL ORDER BY tahun DESC");
$years_list = $stmt->fetchAll(PDO::FETCH_COLUMN);

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
    
    // Get all articles grouped by research for display in published articles table
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($page_info['title'] ?: 'Research - InLET'); ?></title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style-research.css">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main class="page-main">
        <section class="hero d-flex align-items-center" id="home">
            <div class="container text-center text-white">
                <h1 class="display-4 fw-bold"><?= htmlspecialchars($page_info['title'] ?: 'Research - Information And Learning Engineering Technology'); ?></h1>
                <?php if (!empty($page_info['subtitle'])): ?>
                    <p class="lead mt-3"><?= htmlspecialchars($page_info['subtitle']); ?></p>
                <?php else: ?>
                    <p class="lead mt-3">Pioneering advancements in Language and Educational Technology to shape the future of learning</p>
                <?php endif; ?>
            </div>
        </section>

        <?php if (!empty($progress_list)): ?>
            <section id="progress" class="research progress-white">
                <div class="research-container">
                    <div class="section-title">
                        <h2>Research Progress</h2>
                        <p>Latest updates on our research projects.</p>
                    </div>
                    <div class="research-progress-list">
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
                    </div>

                    <?php if ($total_pages_progress > 1): ?>
                        <nav aria-label="Progress pagination" class="mt-5">
                            <ul class="pagination justify-content-center pagination-gap">
                                <?php if ($current_page_progress > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="?page_progress=<?php echo $current_page_progress - 1; ?>#progress"
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
                                $start_page = max(1, $current_page_progress - 2);
                                $end_page = min($total_pages_progress, $current_page_progress + 2);

                                if ($start_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page_progress=1#progress">1</a>
                                    </li>
                                    <?php if ($start_page > 2): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <li class="page-item <?php echo ($i == $current_page_progress) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page_progress=<?php echo $i; ?>#progress"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($end_page < $total_pages_progress): ?>
                                    <?php if ($end_page < $total_pages_progress - 1): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="?page_progress=<?php echo $total_pages_progress; ?>#progress"><?php echo $total_pages_progress; ?></a>
                                    </li>
                                <?php endif; ?>

                                <?php if ($current_page_progress < $total_pages_progress): ?>
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="?page_progress=<?php echo $current_page_progress + 1; ?>#progress"
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
                            <div class="text-center mt-3 text-muted">
                                Showing <?php echo ($offset_progress + 1); ?> -
                                <?php echo min($offset_progress + $items_per_page, $total_items_progress); ?> of
                                <?php echo $total_items_progress; ?> progress updates
                            </div>
                        </nav>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <section class="research-cta">
            <div class="research-container text-center">
                <div class="section-title mb-3">
                    <h2>Join Our Mission in Innovation</h2>
                    <p>Interested in collaborating, becoming a research student, or getting more information?</p>
                </div>
                <a href="member.php" class="btn btn-primary">Contact Our Team</a>
            </div>
        </section>

        <?php include 'includes/footer.php'; ?>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            function toggleDetails(button) {
                const card = button.closest('.research-progress-card');
                const details = card.querySelector('.research-progress-details');
                const btnText = button.querySelector('.btn-text');
                
                if (details.style.display === 'none') {
                    details.style.display = 'block';
                    btnText.textContent = 'Hide details';
                } else {
                    details.style.display = 'none';
                    btnText.textContent = 'Show details';
                }
            }
            
            function toggleArticleDetails(button, researchId) {
                const content = document.getElementById('articles-' + researchId);
                const btnText = button.querySelector('.btn-text');
                const btnIcon = button.querySelector('.btn-icon');
                
                if (!content) return;
                
                if (content.style.display === 'none' || content.style.display === '') {
                    // Show details
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
                    
                    // Remove max-height after animation to allow natural height
                    setTimeout(() => {
                        content.style.maxHeight = 'none';
                        content.style.overflow = 'visible';
                    }, 300);
                } else {
                    // Hide details
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
        </script>
    </body>

</html>