<?php
require_once 'config/database.php';
require_once 'config/settings.php';

$conn = getDBConnection();

// Get page title and subtitle
$page_info = getPageTitle('member');

// Search and filter setup
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$letter_filter = isset($_GET['letter']) ? strtoupper(trim($_GET['letter'])) : '';

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

// Fetch all members on one page
// Order by: Ketua Lab first, then others by name
$query_sql = "SELECT * FROM member $where_sql
              ORDER BY
                CASE
                  WHEN LOWER(jabatan) LIKE '%ketua lab%' THEN 0
                  ELSE 1
                END,
                nama";
$stmt = $conn->prepare($query_sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$members = $stmt->fetchAll();

// Get first letters for index
$stmt = $conn->query("SELECT DISTINCT UPPER(SUBSTRING(nama FROM 1 FOR 1)) as first_letter FROM member WHERE nama IS NOT NULL AND nama != '' ORDER BY first_letter");
$available_letters = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get initials
function getInitials($name)
{
    $words = explode(' ', $name);
    $initials = '';
    foreach ($words as $word) {
        if (!empty($word)) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
    }
    return substr($initials, 0, 2);
}

require __DIR__ . '/views/member.php';
