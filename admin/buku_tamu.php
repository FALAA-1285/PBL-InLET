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
            $message = 'Pesan ditandai sebagai sudah dibaca!';
            $message_type = 'success';
            $ajax_response = ['success' => true, 'status' => 'read'];
        } catch(PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
            $ajax_response = ['success' => false, 'message' => $e->getMessage()];
        }
    } elseif ($action === 'mark_unread') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $conn->prepare("UPDATE buku_tamu SET is_read = false WHERE id_buku_tamu = :id");
            $stmt->execute(['id' => $id]);
            $message = 'Pesan ditandai sebagai belum dibaca!';
            $message_type = 'success';
            $ajax_response = ['success' => true, 'status' => 'unread'];
        } catch(PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
            $ajax_response = ['success' => false, 'message' => $e->getMessage()];
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $conn->prepare("DELETE FROM buku_tamu WHERE id_buku_tamu = :id");
            $stmt->execute(['id' => $id]);
            $message = 'Pesan berhasil dihapus!';
            $message_type = 'success';
            $ajax_response = ['success' => true, 'status' => 'deleted'];
        } catch(PDOException $e) {
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

// Create table if not exists and update structure
try {
    // Try to alter table if exists to make pesan nullable
    try {
        $conn->exec("ALTER TABLE buku_tamu ALTER COLUMN pesan DROP NOT NULL");
    } catch (PDOException $e) {
        // Column might already be nullable or table doesn't exist yet
    }
    
    // Try to alter institusi and no_hp to NOT NULL if table exists
    try {
        $conn->exec("ALTER TABLE buku_tamu ALTER COLUMN institusi SET NOT NULL");
    } catch (PDOException $e) {
        // Column might already be NOT NULL or table doesn't exist yet
    }
    
    try {
        $conn->exec("ALTER TABLE buku_tamu ALTER COLUMN no_hp SET NOT NULL");
    } catch (PDOException $e) {
        // Column might already be NOT NULL or table doesn't exist yet
    }
    
    $conn->exec("CREATE TABLE IF NOT EXISTS buku_tamu (
        id_buku_tamu SERIAL PRIMARY KEY,
        nama VARCHAR(150) NOT NULL,
        email VARCHAR(150) NOT NULL,
        institusi VARCHAR(200) NOT NULL,
        no_hp VARCHAR(50) NOT NULL,
        pesan VARCHAR(2000),
        created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
        is_read BOOLEAN DEFAULT false,
        admin_response VARCHAR(2000)
    )");
    
    $conn->exec("CREATE INDEX IF NOT EXISTS idx_buku_tamu_created_at ON buku_tamu(created_at DESC)");
    $conn->exec("CREATE INDEX IF NOT EXISTS idx_buku_tamu_is_read ON buku_tamu(is_read)");
    $conn->exec("CREATE INDEX IF NOT EXISTS idx_buku_tamu_email ON buku_tamu(email)");
} catch (PDOException $e) {
    // Table might already exist
}

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
    <title>Buku Tamu - CMS InLET</title>
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
            color: var(--primary);
            font-size: 2rem;
            margin: 0;
        }

        .filter-tabs {
            display: flex;
            gap: 0.5rem;
            background: white;
            padding: 0.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
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

        .message-panel {
            margin-top: 0.6rem;
            display: none;
        }

        .message-panel.active {
            display: block;
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

        .btn-read { background: #10b981; }
        .btn-read:hover { background: #059669; }
        .btn-unread { background: #f59e0b; }
        .btn-unread:hover { background: #d97706; }
        .btn-delete { background: #ef4444; }
        .btn-delete:hover { background: #dc2626; }
        .btn-view { background: #3b82f6; }
        .btn-view:hover { background: #2563eb; }

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
            background: rgba(79,70,229,0.15);
            color: var(--primary);
        }

        .status-badge.read {
            background: rgba(16,185,129,0.15);
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
    <?php $active_page = 'buku_tamu'; include __DIR__ . '/partials/sidebar.php'; ?>
    
    <main class="content">
        <div class="content-inner">
            <div class="content-header">
                <h1><i class="ri-book-open-line"></i> Buku Tamu</h1>
                <div class="filter-tabs">
                    <a href="?filter=all" class="filter-tab <?= $filter === 'all' ? 'active' : ''; ?>">
                        Semua
                    </a>
                    <a href="?filter=unread" class="filter-tab <?= $filter === 'unread' ? 'active' : ''; ?>">
                        Belum Dibaca
                        <?php if ($unread_count > 0): ?>
                            <span class="badge-unread" id="unread-counter"><?= $unread_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="?filter=read" class="filter-tab <?= $filter === 'read' ? 'active' : ''; ?>">
                        Sudah Dibaca
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
                    <h3>Tidak ada pesan</h3>
                    <p>Belum ada pesan dari pengunjung.</p>
                </div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table class="messages-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Data Pengunjung</th>
                                <th>Pesan</th>
                                <th>Status & Waktu</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($messages as $index => $msg): ?>
                                <tr id="row-<?= $msg['id_buku_tamu']; ?>" data-is-read="<?= $msg['is_read'] ? '1' : '0'; ?>" class="<?= !$msg['is_read'] ? 'unread' : ''; ?>">
                                    <td><?= $offset + $index + 1; ?></td>
                                    <td>
                                        <div class="guest-info">
                                            <?= htmlspecialchars($msg['nama']); ?>
                                            <?php if (!$msg['is_read']): ?>
                                                <span class="badge-unread" id="badge-new-<?= $msg['id_buku_tamu']; ?>">Baru</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="guest-meta">
                                            <span><i class="ri-mail-line"></i> <?= htmlspecialchars($msg['email']); ?></span>
                                            <span><i class="ri-building-line"></i> <?= htmlspecialchars($msg['institusi'] ?? 'N/A'); ?></span>
                                            <span><i class="ri-phone-line"></i> <?= htmlspecialchars($msg['no_hp'] ?? 'N/A'); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($msg['pesan'])): ?>
                                            <button type="button"
                                                    class="btn-action btn-view"
                                                    onclick="toggleMessage(<?= $msg['id_buku_tamu']; ?>)">
                                                <i class="ri-eye-line"></i> Baca Pesan
                                            </button>
                                            <div id="message-<?= $msg['id_buku_tamu']; ?>" class="message-panel">
                                                <div class="message-text">
                                                    <?= nl2br(htmlspecialchars($msg['pesan'])); ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="message-text empty">
                                                <i class="ri-user-line"></i> Daftar hadir - Tidak ada pesan
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="status-cell">
                                        <span class="status-badge <?= $msg['is_read'] ? 'read' : 'unread'; ?>" id="status-badge-<?= $msg['id_buku_tamu']; ?>">
                                            <i class="<?= $msg['is_read'] ? 'ri-check-line' : 'ri-time-line'; ?>"></i>
                                            <span><?= $msg['is_read'] ? 'Sudah dibaca' : 'Belum dibaca'; ?></span>
                                        </span>
                                        <div class="status-time" id="status-time-<?= $msg['id_buku_tamu']; ?>">
                                            <?= date('d M Y, H:i', strtotime($msg['created_at'])); ?>
                                        </div>
                                    </td>
                                    <td class="actions-cell">
                                        <form method="POST" onsubmit="return confirm('Yakin hapus pesan ini?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $msg['id_buku_tamu']; ?>">
                                            <button type="submit" class="btn-action btn-delete">
                                                <i class="ri-delete-bin-line"></i> Hapus
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
                    <div class="pagination" style="display: flex; justify-content: center; align-items: center; gap: 0.5rem; margin-top: 2rem; padding: 1rem;">
                        <?php if ($current_page > 1): ?>
                            <a href="?page=<?= $current_page - 1; ?>&filter=<?= $filter; ?>" style="padding: 0.5rem 1rem; border: 1px solid #e2e8f0; border-radius: 8px; text-decoration: none; color: var(--dark); transition: all 0.3s;">&laquo; Previous</a>
                        <?php else: ?>
                            <span style="padding: 0.5rem 1rem; border: 1px solid #e2e8f0; border-radius: 8px; opacity: 0.5; cursor: not-allowed;">&laquo; Previous</span>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);
                        
                        if ($start_page > 1): ?>
                            <a href="?page=1&filter=<?= $filter; ?>" style="padding: 0.5rem 1rem; border: 1px solid #e2e8f0; border-radius: 8px; text-decoration: none; color: var(--dark);">1</a>
                            <?php if ($start_page > 2): ?>
                                <span>...</span>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <?php if ($i == $current_page): ?>
                                <span style="padding: 0.5rem 1rem; background: var(--primary); color: white; border-radius: 8px;"><?= $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?= $i; ?>&filter=<?= $filter; ?>" style="padding: 0.5rem 1rem; border: 1px solid #e2e8f0; border-radius: 8px; text-decoration: none; color: var(--dark); transition: all 0.3s;"><?= $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($end_page < $total_pages): ?>
                            <?php if ($end_page < $total_pages - 1): ?>
                                <span>...</span>
                            <?php endif; ?>
                            <a href="?page=<?= $total_pages; ?>&filter=<?= $filter; ?>" style="padding: 0.5rem 1rem; border: 1px solid #e2e8f0; border-radius: 8px; text-decoration: none; color: var(--dark);"><?= $total_pages; ?></a>
                        <?php endif; ?>
                        
                        <?php if ($current_page < $total_pages): ?>
                            <a href="?page=<?= $current_page + 1; ?>&filter=<?= $filter; ?>" style="padding: 0.5rem 1rem; border: 1px solid #e2e8f0; border-radius: 8px; text-decoration: none; color: var(--dark); transition: all 0.3s;">Next &raquo;</a>
                        <?php else: ?>
                            <span style="padding: 0.5rem 1rem; border: 1px solid #e2e8f0; border-radius: 8px; opacity: 0.5; cursor: not-allowed;">Next &raquo;</span>
                        <?php endif; ?>
                    </div>
                    <div style="text-align: center; color: var(--gray); margin-top: 1rem;">
                        Menampilkan <?= ($offset + 1); ?> - <?= min($offset + $items_per_page, $total_items); ?> dari <?= $total_items; ?> pesan
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function toggleMessage(id) {
            const panel = document.getElementById('message-' + id);
            if (!panel) {
                return;
            }
            panel.classList.toggle('active');

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
                    text.textContent = 'Sudah dibaca';
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

