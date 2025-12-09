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

// Progress pagination
$current_page_progress = isset($_GET['page_progress']) ? max(1, intval($_GET['page_progress'])) : 1;
$offset_progress = ($current_page_progress - 1) * $items_per_page;

// Count progress items
$stmt = $conn->query("SELECT COUNT(*) FROM penelitian");
$total_items_progress = $stmt->fetchColumn();
$total_pages_progress = ceil($total_items_progress / $items_per_page);

// Fetch progress with pagination
$stmt = $conn->prepare("SELECT p.*, a.judul as artikel_judul, m.nama as mahasiswa_nama, mem.nama as member_nama
                      FROM penelitian p
                      LEFT JOIN artikel a ON p.id_artikel = a.id_artikel
                      LEFT JOIN mahasiswa m ON p.id_mhs = m.id_mahasiswa
                      LEFT JOIN member mem ON p.id_member = mem.id_member
                      ORDER BY p.created_at DESC
                      LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset_progress, PDO::PARAM_INT);
$stmt->execute();
$progress_list = $stmt->fetchAll();
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

        <section class="research" id="focus-areas">
            <div class="research-container">
                <div class="section-title">
                    <h2>Core Research Focus Areas</h2>
                    <p>A deep dive into the six pillars of our innovation.</p>
                </div>

                <div class="row justify-content-center mb-4">
                    <div class="col-md-6">
                        <form method="GET" action="" class="d-flex gap-2">
                            <select name="year" class="form-select">
                                <option value="">Search by year...</option>
                                <?php foreach ($years_list as $year): ?>
                                    <option value="<?= $year ?>" <?= $search_year == $year ? 'selected' : '' ?>>
                                        <?= $year ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-primary">Search</button>
                            <?php if (!empty($search_year)): ?>
                                <a href="research.php" class="btn btn-secondary">Reset</a>
                            <?php endif; ?>
                        </form>
                        <?php if (!empty($search_year)): ?>
                            <p class="mt-2 text-muted">
                                Showing <?php echo $total_items_artikel; ?> articles for year
                                <?php echo htmlspecialchars($search_year); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (empty($artikels)): ?>
                    <div class="empty-data-alert" role="alert">
                        <i class="fas fa-book fa-3x mb-3 text-muted"></i>
                        <p class="mb-1">No research articles published yet.</p>
                        <p class="small text-muted">Please log in as an admin to add articles via the CMS.</p>
                    </div>
                <?php else: ?>
                    <div class="row g-4 justify-content-center">
                        <?php foreach ($artikels as $artikel): ?>
                            <?php
                            // Extract URL from konten
                            $url = extractUrlFromContent($artikel['konten'] ?? '');
                            ?>
                            <div class="col-xl-4 col-md-6">
                                <div class="card-surface research-card h-100">
                                    <div>
                                        <?php if ($url): ?>
                                            <h4>
                                                <a href="<?php echo htmlspecialchars($url); ?>" target="_blank" rel="noopener noreferrer" style="text-decoration: none; color: inherit;">
                                                    <?php echo htmlspecialchars($artikel['judul']); ?>
                                                </a>
                                            </h4>
                                        <?php else: ?>
                                            <h4><?php echo htmlspecialchars($artikel['judul']); ?></h4>
                                        <?php endif; ?>
                                        <?php if ($artikel['tahun']): ?>
                                            <div class="research-meta">Year: <?php echo $artikel['tahun']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <!-- Konten tidak ditampilkan, hanya digunakan untuk extract URL -->
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($total_pages_artikel > 1): ?>
                    <nav aria-label="Articles pagination" class="mt-5">
                        <ul class="pagination justify-content-center pagination-gap">
                            <?php
                            $page_url = "?";
                            if (!empty($search_year)) {
                                $page_url .= "year=" . urlencode($search_year) . "&";
                            }
                            ?>

                            <?php if ($current_page_artikel > 1): ?>
                                <li class="page-item">
                                    <a class="page-link"
                                        href="<?= $page_url ?>page_artikel=<?php echo $current_page_artikel - 1; ?>#focus-areas"
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
                            $start_page = max(1, $current_page_artikel - 2);
                            $end_page = min($total_pages_artikel, $current_page_artikel + 2);

                            if ($start_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $page_url ?>page_artikel=1#focus-areas">1</a>
                                </li>
                                <?php if ($start_page > 2): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?php echo ($i == $current_page_artikel) ? 'active' : ''; ?>">
                                    <a class="page-link"
                                        href="<?= $page_url ?>page_artikel=<?php echo $i; ?>#focus-areas"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($end_page < $total_pages_artikel): ?>
                                <?php if ($end_page < $total_pages_artikel - 1): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link"
                                        href="<?= $page_url ?>page_artikel=<?php echo $total_pages_artikel; ?>#focus-areas"><?php echo $total_pages_artikel; ?></a>
                                </li>
                            <?php endif; ?>

                            <?php if ($current_page_artikel < $total_pages_artikel): ?>
                                <li class="page-item">
                                    <a class="page-link"
                                        href="<?= $page_url ?>page_artikel=<?php echo $current_page_artikel + 1; ?>#focus-areas"
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

        <?php if (!empty($progress_list)): ?>
            <section id="progress" class="research progress-white">
                <div class="research-container">
                    <div class="section-title">
                        <h2>Research Progress</h2>
                        <p>Latest updates on our research projects.</p>
                    </div>
                    <div class="row g-4 justify-content-center">
                        <?php foreach ($progress_list as $progress): ?>
                            <div class="col-xl-4 col-md-6">
                                <div class="card-surface research-card h-100">
                                    <div>
                                        <h4><?php echo htmlspecialchars($progress['judul']); ?></h4>
                                        <?php if ($progress['tahun']): ?>
                                            <div class="research-meta">Year: <?php echo $progress['tahun']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($progress['deskripsi']): ?>
                                        <p><?php echo htmlspecialchars($progress['deskripsi']); ?></p>
                                    <?php endif; ?>
                                    <?php $embedUrl = getYoutubeEmbedUrl($progress['video_url'] ?? ''); ?>
                                    <?php if ($embedUrl): ?>
                                        <div class="research-video">
                                            <div class="video-wrapper">
                                                <iframe src="<?php echo htmlspecialchars($embedUrl); ?>"
                                                    title="YouTube video player" frameborder="0"
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                                    allowfullscreen loading="lazy"></iframe>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($progress['artikel_judul'] || $progress['mahasiswa_nama'] || $progress['member_nama']): ?>
                                        <div class="mt-2 pt-3 border-top small text-muted">
                                            <?php if ($progress['artikel_judul']): ?>
                                                <p><strong>Article:</strong> <?php echo htmlspecialchars($progress['artikel_judul']); ?>
                                                </p>
                                            <?php endif; ?>
                                            <?php if ($progress['mahasiswa_nama']): ?>
                                                <p><strong>Student:</strong>
                                                    <?php echo htmlspecialchars($progress['mahasiswa_nama']); ?></p>
                                            <?php endif; ?>
                                            <?php if ($progress['member_nama']): ?>
                                                <p><strong>Member:</strong> <?php echo htmlspecialchars($progress['member_nama']); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
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
</body>

</html>