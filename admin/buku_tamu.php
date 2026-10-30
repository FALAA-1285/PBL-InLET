<?php
require_once '../config/auth.php';
requireAdmin();

$conn = getDBConnection();

$message = '';
$message_type = '';
$ajax_response = null;

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $is_ajax = isset($_POST['ajax']) && $_POST['ajax'] === '1';

    if ($action === 'mark_read') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $conn->prepare("UPDATE buku_tamu SET is_read = true WHERE id_buku_tamu = :id");
            $stmt->execute(['id' => $id]);
            $message = 'Message marked as read successfully!';
            $message_type = 'success';
            $ajax_response = ['success' => true, 'status' => 'read'];
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
            $ajax_response = ['success' => false, 'message' => $e->getMessage()];
        }
    } elseif ($action === 'mark_unread') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $conn->prepare("UPDATE buku_tamu SET is_read = false WHERE id_buku_tamu = :id");
            $stmt->execute(['id' => $id]);
            $message = 'Message marked as unread successfully!';
            $message_type = 'success';
            $ajax_response = ['success' => true, 'status' => 'unread'];
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
            $ajax_response = ['success' => false, 'message' => $e->getMessage()];
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $conn->prepare("DELETE FROM buku_tamu WHERE id_buku_tamu = :id");
            $stmt->execute(['id' => $id]);
            $message = 'Message successfully deleted!';
            $message_type = 'success';
            $ajax_response = ['success' => true, 'status' => 'deleted'];
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
            $ajax_response = ['success' => false, 'message' => $e->getMessage()];
        }
    }

    if (!empty($is_ajax)) {
        header('Content-Type: application/json');
        echo json_encode($ajax_response ?? ['success' => false, 'message' => 'Unknown error']);
        exit;
    }
}

// Buku tamu table creation moved to inlet_pbl_clean.sql

// Pagination setup
$items_per_page = 10;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Filter
$filter = $_GET['filter'] ?? 'all'; // 'all', 'unread', 'read'

// Get total count
$count_query = "SELECT COUNT(*) FROM buku_tamu";
if ($filter === 'unread') {
    $count_query .= " WHERE is_read = false";
} elseif ($filter === 'read') {
    $count_query .= " WHERE is_read = true";
}
$stmt = $conn->query($count_query);
$total_items = $stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

// Get messages with pagination
$query = "SELECT * FROM buku_tamu";
if ($filter === 'unread') {
    $query .= " WHERE is_read = false";
} elseif ($filter === 'read') {
    $query .= " WHERE is_read = true";
}
$query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

$stmt = $conn->prepare($query);
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$messages = $stmt->fetchAll();

// Get unread count
$stmt = $conn->query("SELECT COUNT(*) FROM buku_tamu WHERE is_read = false");
$unread_count = $stmt->fetchColumn();

require __DIR__ . '/../views/admin/buku_tamu.php';
