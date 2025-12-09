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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Guestbook - CMS InLET</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <style>
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .content-header h1 {
            color: #000000;
            font-size: 2rem;
            margin: 0;
        }

        .content-header h1 i {
            color: #000000;
        }

        .filter-tabs {
            display: flex;
            gap: 0.5rem;
            background: white;
            padding: 0.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .filter-tab {
            padding: 0.5rem 1.25rem;
            border: none;
            background: transparent;
            color: var(--gray);
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .filter-tab:hover {
            background: var(--light);
            color: var(--primary);
        }

        .filter-tab.active {
            background: var(--primary);
            color: white;
        }

        .badge-unread {
            background: #ef4444;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }

        .table-wrapper {
            background: white;
            border-radius: 18px;
            padding: 0;
            box-shadow: 0 10px 35px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .messages-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 900px;
        }

        .messages-table thead th {
            background: var(--light);
            padding: 1.1rem;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--gray);
        }

        .messages-table tbody td {
            padding: 1.1rem;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
            background: white;
        }

        .messages-table tbody tr.unread {
            background: rgba(79, 70, 229, 0.04);
        }

        .messages-table tbody tr:hover td {
            background: rgba(79, 70, 229, 0.03);
        }

        .messages-table tbody tr:last-child td {
            border-bottom: none;
        }

        .message-text {
            background: var(--light);
            padding: 0.75rem;
            border-radius: 10px;
            color: var(--dark);
            line-height: 1.5;
            white-space: pre-line;
        }

        .message-text.empty {
            background: #f1f5f9;
            color: var(--gray);
            font-style: italic;
        }

        .guest-info {
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .guest-meta {
            margin-top: 0.35rem;
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
            color: var(--gray);
            font-size: 0.9rem;
        }

        .guest-meta span i {
            margin-right: 0.35rem;
        }

        .actions-cell {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            min-width: 180px;
        }

        .actions-cell form {
            margin: 0;
        }


        .btn-action {
            padding: 0.45rem 0.85rem;
            border: none;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            color: white;
        }

        .btn-read {
            background: #10b981;
        }

        .btn-read:hover {
            background: #059669;
        }

        .btn-unread {
            background: #f59e0b;
        }

        .btn-unread:hover {
            background: #d97706;
        }

        .btn-delete {
            background: #ef4444;
        }

        .btn-delete:hover {
            background: #dc2626;
        }

        .btn-view {
            background: #3b82f6;
        }

        .btn-view:hover {
            background: #2563eb;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-badge.unread {
            background: rgba(79, 70, 229, 0.15);
            color: var(--primary);
        }

        .status-badge.read {
            background: rgba(16, 185, 129, 0.15);
            color: #047857;
        }

        .status-cell {
            min-width: 200px;
        }

        .status-time {
            font-size: 0.85rem;
            color: var(--gray);
            margin-top: 0.4rem;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .message {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .message.success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .message.error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
    </style>
</head>

<body>
    <?php $active_page = 'buku_tamu';
    include __DIR__ . '/partials/sidebar.php'; ?>

    <main class="content">
        <div class="content-inner">
            <div class="content-header">
                <h1><i class="ri-book-open-line"></i> Guestbook</h1>
                <div class="filter-tabs">
                    <a href="?filter=all" class="filter-tab <?= $filter === 'all' ? 'active' : ''; ?>">
                        All
                    </a>
                    <a href="?filter=unread" class="filter-tab <?= $filter === 'unread' ? 'active' : ''; ?>">
                        Unread
                        <?php if ($unread_count > 0): ?>
                            <span class="badge-unread" id="unread-counter"><?= $unread_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="?filter=read" class="filter-tab <?= $filter === 'read' ? 'active' : ''; ?>">
                        Read
                    </a>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($messages)): ?>
                <div class="empty-state">
                    <i class="ri-inbox-line"></i>
                    <h3>No messages</h3>
                    <p>No messages from visitors yet.</p>
                </div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table class="messages-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Visitor Information</th>
                                <th>Message</th>
                                <th>Status & Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($messages as $index => $msg): ?>
                                <tr id="row-<?= $msg['id_buku_tamu']; ?>" data-is-read="<?= $msg['is_read'] ? '1' : '0'; ?>"
                                    class="<?= !$msg['is_read'] ? 'unread' : ''; ?>">
                                    <td><?= $offset + $index + 1; ?></td>
                                    <td>
                                        <div class="guest-info">
                                            <?= htmlspecialchars($msg['nama']); ?>
                                            <?php if (!$msg['is_read']): ?>
                                                <span class="badge-unread" id="badge-new-<?= $msg['id_buku_tamu']; ?>">New</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="guest-meta">
                                            <span><i class="ri-mail-line"></i> <?= htmlspecialchars($msg['email']); ?></span>
                                            <span><i class="ri-building-line"></i>
                                                <?= htmlspecialchars($msg['institusi'] ?? 'N/A'); ?></span>
                                            <span><i class="ri-phone-line"></i>
                                                <?= htmlspecialchars($msg['no_hp'] ?? 'N/A'); ?></span>
                                        </div>
                                        <div id="message-display-<?= $msg['id_buku_tamu']; ?>" style="display: none; margin-top: 0.75rem;">
                                            <div class="message-text">
                                                <?= nl2br(htmlspecialchars($msg['pesan'])); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($msg['pesan'])): ?>
                                            <button type="button" class="btn-action btn-view" id="view-btn-<?= $msg['id_buku_tamu']; ?>"
                                                onclick="toggleMessage(<?= $msg['id_buku_tamu']; ?>)">
                                                <i class="ri-eye-line"></i> View Message
                                            </button>
                                        <?php else: ?>
                                            <div class="message-text empty">
                                                <i class="ri-user-line"></i> Attendance only - No message
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="status-cell">
                                        <span class="status-badge <?= $msg['is_read'] ? 'read' : 'unread'; ?>"
                                            id="status-badge-<?= $msg['id_buku_tamu']; ?>">
                                            <i class="<?= $msg['is_read'] ? 'ri-check-line' : 'ri-time-line'; ?>"></i>
                                            <span><?= $msg['is_read'] ? 'Read' : 'Unread'; ?></span>
                                        </span>
                                        <div class="status-time" id="status-time-<?= $msg['id_buku_tamu']; ?>">
                                            <?= date('d M Y, H:i', strtotime($msg['created_at'])); ?>
                                        </div>
                                    </td>
                                    <td class="actions-cell">
                                        <form method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this message?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $msg['id_buku_tamu']; ?>">
                                            <button type="submit" class="btn-action btn-delete">
                                                <i class="ri-delete-bin-line"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination d-flex justify-content-center align-items-center gap-2 mt-4 p-3">
                        <?php if ($current_page > 1): ?>
                            <a href="?page=<?= $current_page - 1; ?>&filter=<?= $filter; ?>" class="page-pill">&laquo; Previous</a>
                        <?php else: ?>
                            <span class="page-pill disabled">&laquo; Previous</span>
                        <?php endif; ?>

                        <?php
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);

                        if ($start_page > 1): ?>
                            <a href="?page=1&filter=<?= $filter; ?>" class="page-pill">1</a>
                            <?php if ($start_page > 2): ?>
                                <span>...</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <?php if ($i == $current_page): ?>
                                <span class="page-pill active"><?= $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?= $i; ?>&filter=<?= $filter; ?>" class="page-pill"><?= $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($end_page < $total_pages): ?>
                            <?php if ($end_page < $total_pages - 1): ?>
                                <span>...</span>
                            <?php endif; ?>
                            <a href="?page=<?= $total_pages; ?>&filter=<?= $filter; ?>" class="page-pill"><?= $total_pages; ?></a>
                        <?php endif; ?>

                        <?php if ($current_page < $total_pages): ?>
                            <a href="?page=<?= $current_page + 1; ?>&filter=<?= $filter; ?>" class="page-pill">Next &raquo;</a>
                        <?php else: ?>
                            <span class="page-pill disabled">Next &raquo;</span>
                        <?php endif; ?>
                    </div>
                    <div class="text-center muted-gray mt-3">
                        Showing <?= ($offset + 1); ?> - <?= min($offset + $items_per_page, $total_items); ?> of
                        <?= $total_items; ?> messages
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function toggleMessage(id) {
            const messageDisplay = document.getElementById('message-display-' + id);
            const button = document.getElementById('view-btn-' + id);
            
            if (!messageDisplay || !button) {
                return;
            }

            // Toggle message display
            if (messageDisplay.style.display === 'none') {
                // Show message
                messageDisplay.style.display = 'block';
                button.innerHTML = '<i class="ri-eye-off-line"></i> Hide Message';
            } else {
                // Hide message
                messageDisplay.style.display = 'none';
                button.innerHTML = '<i class="ri-eye-line"></i> View Message';
            }

            // Mark as read if unread
            const row = document.getElementById('row-' + id);
            if (row && row.dataset.isRead === '0') {
                markMessageAsRead(id, row);
            }
        }

        function markMessageAsRead(id, row) {
            const formData = new FormData();
            formData.append('action', 'mark_read');
            formData.append('id', id);
            formData.append('ajax', '1');

            fetch('buku_tamu.php', {
                method: 'POST',
                body: formData
            }).then(response => response.json())
                .then(data => {
                    if (data.success) {
                        row.dataset.isRead = '1';
                        row.classList.remove('unread');
                        updateStatusBadge(id);
                        removeNewBadge(id);
                        decrementUnreadCounter();
                    }
                }).catch(error => console.error(error));
        }

        function updateStatusBadge(id) {
            const badge = document.getElementById('status-badge-' + id);
            if (badge) {
                badge.classList.remove('unread');
                badge.classList.add('read');
                const icon = badge.querySelector('i');
                if (icon) {
                    icon.className = 'ri-check-line';
                }
                const text = badge.querySelector('span');
                if (text) {
                    text.textContent = 'Read';
                }
            }
        }

        function removeNewBadge(id) {
            const badge = document.getElementById('badge-new-' + id);
            if (badge) {
                badge.remove();
            }
        }

        function decrementUnreadCounter() {
            const counter = document.getElementById('unread-counter');
            if (!counter) {
                return;
            }
            const value = parseInt(counter.textContent, 10) || 0;
            if (value <= 1) {
                counter.remove();
            } else {
                counter.textContent = value - 1;
            }
        }
    </script>
</body>

</html>