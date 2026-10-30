<?php
/**
 * Variables provided by admin/buku_tamu.php
 *
 * @var string $filter
 * @var int $unread_count
 * @var string $message
 * @var string $message_type
 * @var array $messages
 * @var int $offset
 * @var float $total_pages
 * @var int $current_page
 * @var int $items_per_page
 * @var int $total_items
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Guestbook - CMS InLET</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/admin/buku_tamu.css">
</head>

<body>
    <?php $active_page = 'buku_tamu';
    include __DIR__ . '/../partials/admin_sidebar.php'; ?>

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