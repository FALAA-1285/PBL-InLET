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

require __DIR__ . '/views/research.php';
