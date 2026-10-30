<?php
require_once '../config/auth.php';
requireAdmin();

$conn = getDBConnection();

// Statistik
$stats = [];

// Total Artikel
try {
    $stmt = $conn->query("SELECT COUNT(*) as count FROM artikel");
    $stats['articles'] = $stmt->fetch()['count'] ?? 0;
} catch (PDOException $e) {
    $stats['articles'] = 0;
}

// Total Berita
try {
    $stmt = $conn->query("SELECT COUNT(*) as count FROM berita");
    $stats['news'] = $stmt->fetch()['count'] ?? 0;
} catch (PDOException $e) {
    $stats['news'] = 0;
}

// Total Member
try {
    $stmt = $conn->query("SELECT COUNT(*) as count FROM member");
    $stats['members'] = $stmt->fetch()['count'] ?? 0;
} catch (PDOException $e) {
    $stats['members'] = 0;
}

// Total Penelitian
try {
    $stmt = $conn->query("SELECT COUNT(*) as count FROM penelitian");
    $stats['penelitian'] = $stmt->fetch()['count'] ?? 0;
} catch (PDOException $e) {
    $stats['penelitian'] = 0;
}

// Total Visitors
try {
    $stmt = $conn->query("SELECT SUM(visit_count) as total FROM visitor");
    $stats['visitors'] = $stmt->fetch()['total'] ?? 0;
} catch (PDOException $e) {
    $stats['visitors'] = 0;
}

// Unread Messages
try {
    $stmt = $conn->query("SELECT COUNT(*) as count FROM buku_tamu WHERE is_read = false");
    $stats['unread_messages'] = $stmt->fetch()['count'] ?? 0;
} catch (PDOException $e) {
    $stats['unread_messages'] = 0;
}

// Recent News
try {
    $stmt = $conn->query("SELECT * FROM berita ORDER BY created_at DESC LIMIT 5");
    $recent_news = $stmt->fetchAll();
} catch (PDOException $e) {
    $recent_news = [];
}

// Ensure all stats have default values
if (!isset($stats['articles'])) $stats['articles'] = 0;
if (!isset($stats['news'])) $stats['news'] = 0;
if (!isset($stats['members'])) $stats['members'] = 0;
if (!isset($stats['penelitian'])) $stats['penelitian'] = 0;
if (!isset($stats['visitors'])) $stats['visitors'] = 0;
if (!isset($stats['unread_messages'])) $stats['unread_messages'] = 0;

require __DIR__ . '/../views/admin/dashboard.php';
