<?php
// Enable Edge CDN caching with background stale-while-revalidate for fast page loads
header("Cache-Control: public, max-age=10, s-maxage=60, stale-while-revalidate=300");

require_once 'config/database.php';
require_once 'config/settings.php';

$conn = getDBConnection();

// Get page title and subtitle
$page_info = getPageTitle('home');

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
// Order by: Ketua Lab first, then others by name
$team = safeQueryAll($conn, "SELECT * FROM member 
                              ORDER BY 
                                CASE 
                                  WHEN LOWER(jabatan) LIKE '%ketua lab%' THEN 0 
                                  ELSE 1 
                                END,
                                nama");

// Fetch products
$products = safeQueryAll($conn, "SELECT * FROM produk ORDER BY id_produk");

// Research fields from fokus_penelitian table - always get fresh data
// Use prepared statement to ensure fresh data and prevent caching
try {
    $stmt = $conn->prepare("SELECT id_fp, title as judul, deskripsi, detail FROM fokus_penelitian ORDER BY id_fp");
    $stmt->execute();
    $riset = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $riset = [];
}

// Function to properly capitalize titles
function formatTitle($title) {
    if (empty($title)) return '';
    $title = trim($title);
    // Direct replacement for "information engineering" in any case variation
    $title = preg_replace('/\binformation\s+engineering\b/i', 'Information Engineering', $title);
    // If title contains "Information Engineering", preserve it and capitalize the rest
    if (stripos($title, 'Information Engineering') !== false) {
        // Split by "Information Engineering" and capitalize other parts
        $parts = preg_split('/\bInformation\s+Engineering\b/i', $title, -1, PREG_SPLIT_DELIM_CAPTURE);
        $result = '';
        foreach ($parts as $i => $part) {
            if ($i % 2 == 0) {
                // Regular text - capitalize normally
                $result .= ucwords(strtolower($part));
            } else {
                // This is "Information Engineering"
                $result .= 'Information Engineering';
            }
        }
        return $result;
    }
    // For other titles, use ucwords
    return ucwords(strtolower($title));
}

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

// Fetch videos
$videos = safeQueryAll($conn, "SELECT * FROM video ORDER BY created_at DESC");

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

require __DIR__ . '/views/index.php';
