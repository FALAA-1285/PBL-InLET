<?php
require_once '../config/auth.php';
requireAdmin();

$conn = getDBConnection();

$message = '';
$message_type = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'mark_read') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $conn->prepare("UPDATE buku_tamu SET is_read = true WHERE id_buku_tamu = :id");
            $stmt->execute(['id' => $id]);
            $message = 'Pesan ditandai sebagai sudah dibaca!';
            $message_type = 'success';
        } catch(PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    } elseif ($action === 'mark_unread') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $conn->prepare("UPDATE buku_tamu SET is_read = false WHERE id_buku_tamu = :id");
            $stmt->execute(['id' => $id]);
            $message = 'Pesan ditandai sebagai belum dibaca!';
            $message_type = 'success';
        } catch(PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    } elseif ($action === 'add_response') {
        $id = $_POST['id'] ?? 0;
        $response = trim($_POST['response'] ?? '');
        try {
            $stmt = $conn->prepare("UPDATE buku_tamu SET admin_response = :response WHERE id_buku_tamu = :id");
            $stmt->execute([
                'id' => $id,
                'response' => $response ?: null
            ]);
            $message = 'Respon berhasil disimpan!';
            $message_type = 'success';
        } catch(PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        try {
            $stmt = $conn->prepare("DELETE FROM buku_tamu WHERE id_buku_tamu = :id");
            $stmt->execute(['id' => $id]);
            $message = 'Pesan berhasil dihapus!';
            $message_type = 'success';
        } catch(PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Create table if not exists
try {
                // Try to alter table if exists to make pesan nullable
                try {
                    $conn->exec("ALTER TABLE buku_tamu ALTER COLUMN pesan DROP NOT NULL");
                } catch (PDOException $e) {
                    // Column might already be nullable or table doesn't exist yet
                }
                
                $conn->exec("CREATE TABLE IF NOT EXISTS buku_tamu (
        id_buku_tamu SERIAL PRIMARY KEY,
        nama VARCHAR(150) NOT NULL,
        email VARCHAR(150) NOT NULL,
        institusi VARCHAR(200),
        no_hp VARCHAR(50),
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

        .message-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.07);
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }

        .message-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }

        .message-card.unread {
            border-left-color: var(--primary);
            background: linear-gradient(to right, rgba(79, 70, 229, 0.05), white);
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .message-info {
            flex: 1;
        }

        .message-name {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }

        .message-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            color: var(--gray);
            font-size: 0.9rem;
        }

        .message-meta i {
            margin-right: 0.25rem;
        }

        .message-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-read {
            background: #10b981;
            color: white;
        }

        .btn-read:hover {
            background: #059669;
        }

        .btn-unread {
            background: #f59e0b;
            color: white;
        }

        .btn-unread:hover {
            background: #d97706;
        }

        .btn-delete {
            background: #ef4444;
            color: white;
        }

        .btn-delete:hover {
            background: #dc2626;
        }

        .btn-response {
            background: var(--primary);
            color: white;
        }

        .btn-response:hover {
            background: var(--primary-dark);
        }

        .message-content {
            background: var(--light);
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            color: var(--dark);
            line-height: 1.6;
        }

        .admin-response {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            padding: 1rem;
            border-radius: 12px;
            margin-top: 1rem;
            border-left: 4px solid var(--primary);
        }

        .admin-response h5 {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .response-form {
            margin-top: 1rem;
        }

        .response-form textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-family: inherit;
            resize: vertical;
            min-height: 100px;
            margin-bottom: 0.5rem;
        }

        .response-form textarea:focus {
            outline: none;
            border-color: var(--primary);
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
                            <span class="badge-unread"><?= $unread_count; ?></span>
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
                <?php foreach ($messages as $msg): ?>
                    <div class="message-card <?= !$msg['is_read'] ? 'unread' : ''; ?>">
                        <div class="message-header">
                            <div class="message-info">
                                <div class="message-name">
                                    <?= htmlspecialchars($msg['nama']); ?>
                                    <?php if (!$msg['is_read']): ?>
                                        <span class="badge-unread">Baru</span>
                                    <?php endif; ?>
                                </div>
                                <div class="message-meta">
                                    <span><i class="ri-mail-line"></i> <?= htmlspecialchars($msg['email']); ?></span>
                                    <?php if ($msg['institusi']): ?>
                                        <span><i class="ri-building-line"></i> <?= htmlspecialchars($msg['institusi']); ?></span>
                                    <?php endif; ?>
                                    <?php if ($msg['no_hp']): ?>
                                        <span><i class="ri-phone-line"></i> <?= htmlspecialchars($msg['no_hp']); ?></span>
                                    <?php endif; ?>
                                    <span><i class="ri-time-line"></i> <?= date('d M Y, H:i', strtotime($msg['created_at'])); ?></span>
                                </div>
                            </div>
                            <div class="message-actions">
                                <?php if (!$msg['is_read']): ?>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Tandai pesan ini sebagai sudah dibaca?');">
                                        <input type="hidden" name="action" value="mark_read">
                                        <input type="hidden" name="id" value="<?= $msg['id_buku_tamu']; ?>">
                                        <button type="submit" class="btn-action btn-read">
                                            <i class="ri-check-line"></i> Tandai Dibaca
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="mark_unread">
                                        <input type="hidden" name="id" value="<?= $msg['id_buku_tamu']; ?>">
                                        <button type="submit" class="btn-action btn-unread">
                                            <i class="ri-close-line"></i> Tandai Belum Dibaca
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin hapus pesan ini?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $msg['id_buku_tamu']; ?>">
                                    <button type="submit" class="btn-action btn-delete">
                                        <i class="ri-delete-bin-line"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <?php if (!empty($msg['pesan'])): ?>
                            <div class="message-content">
                                <?= nl2br(htmlspecialchars($msg['pesan'])); ?>
                            </div>
                        <?php else: ?>
                            <div class="message-content" style="background: #f1f5f9; color: #64748b; font-style: italic;">
                                <i class="ri-user-line"></i> Daftar hadir - Tidak ada pesan
                            </div>
                        <?php endif; ?>

                        <?php if ($msg['admin_response']): ?>
                            <div class="admin-response">
                                <h5><i class="ri-admin-line"></i> Respon Admin</h5>
                                <p><?= nl2br(htmlspecialchars($msg['admin_response'])); ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="response-form" id="response-form-<?= $msg['id_buku_tamu']; ?>" style="display: none;">
                            <form method="POST">
                                <input type="hidden" name="action" value="add_response">
                                <input type="hidden" name="id" value="<?= $msg['id_buku_tamu']; ?>">
                                <textarea name="response" placeholder="Tulis respon untuk pengunjung..."><?= htmlspecialchars($msg['admin_response'] ?? ''); ?></textarea>
                                <button type="submit" class="btn-action btn-response">
                                    <i class="ri-send-plane-fill"></i> Simpan Respon
                                </button>
                                <button type="button" class="btn-action" onclick="document.getElementById('response-form-<?= $msg['id_buku_tamu']; ?>').style.display='none'" style="background: #6b7280; color: white;">
                                    <i class="ri-close-line"></i> Batal
                                </button>
                            </form>
                        </div>

                        <button type="button" class="btn-action btn-response" onclick="toggleResponseForm(<?= $msg['id_buku_tamu']; ?>)">
                            <i class="ri-reply-line"></i> <?= $msg['admin_response'] ? 'Edit Respon' : 'Tambah Respon'; ?>
                        </button>
                    </div>
                <?php endforeach; ?>

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
        function toggleResponseForm(id) {
            const form = document.getElementById('response-form-' + id);
            if (form.style.display === 'none') {
                form.style.display = 'block';
            } else {
                form.style.display = 'none';
            }
        }
    </script>
</body>
</html>

