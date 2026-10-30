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

// Fetch videos
$videos = [];
try {
    $video_stmt = $conn->query("SELECT * FROM video ORDER BY created_at DESC");
    $videos = $video_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $videos = [];
}

// ---------- GALLERY SOURCE ----------
// Gallery table creation moved to inlet_pbl_clean.sql
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

// ---------- NEWS count + fetch ----------


//$total_pages = ($items_per_page > 0 && $total_items > 0) ? ceil($total_items / $items_per_page) : 1;

// Gallery initial load
$gallery_items_per_page = 12;
$total_gallery_items = count($all_gallery);
$gallery_init = array_slice($all_gallery, 0, $gallery_items_per_page);

// Helper function untuk build pagination URL
function getPaginationUrl($page, $search = '')
{
    $params = ['page' => $page];
    if (!empty($search)) {
        $params['search'] = $search;
    }
    return 'news.php?' . http_build_query($params);
}

require __DIR__ . '/views/news.php';
