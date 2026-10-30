<?php
/**
 * Variables provided by admin/ruang_lab.php
 *
 * @var string $message
 * @var string $message_type
 * @var mixed $edit_ruang
 * @var int $total_items
 * @var array $ruang_list
 * @var float $total_pages
 * @var int $current_page
 * @var int $offset
 * @var int $items_per_page
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Room Management - CMS InLET</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/admin/ruang_lab.css">
</head>

<body>
    <?php $active_page = 'ruang_lab';
    include __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <main class="content">
        <div class="content-inner">
            <div class="cms-content">
                <h1 class="text-primary mb-4"><i class="ri-building-line"></i> Manage Lab Rooms</h1>

                <?php if ($message): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Add/Edit Form -->
                <div class="form-section <?php echo $edit_ruang ? 'edit-form-section active' : ''; ?>">
                    <h2><?php echo $edit_ruang ? 'Edit Lab Room' : 'Add New Lab Room'; ?></h2>
                    <form method="POST">
                        <input type="hidden" name="action"
                            value="<?php echo $edit_ruang ? 'update_ruang' : 'add_ruang'; ?>">
                        <?php if ($edit_ruang): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_ruang['id_ruang_lab']; ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="nama_ruang">Room Name *</label>
                            <input type="text" id="nama_ruang" name="nama_ruang"
                                value="<?php echo htmlspecialchars($edit_ruang['nama_ruang'] ?? ''); ?>" required>
                        </div>

                        <?php if ($edit_ruang): ?>
                        <div class="form-group">
                            <label for="status">Status *</label>
                            <select id="status" name="status" required>
                                <option value="tersedia" <?php echo (($edit_ruang['status'] ?? 'tersedia') === 'tersedia') ? 'selected' : ''; ?>>Available</option>
                                <option value="maintenance" <?php echo (($edit_ruang['status'] ?? '') === 'maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                                <option value="tidak_tersedia" <?php echo (($edit_ruang['status'] ?? '') === 'tidak_tersedia') ? 'selected' : ''; ?>>Not Available</option>
                            </select>
                        </div>
                        <?php endif; ?>

                        <button type="submit" class="btn-submit">
                            <?php echo $edit_ruang ? 'Update Lab Room' : 'Add Lab Room'; ?>
                        </button>
                        <?php if ($edit_ruang): ?>
                            <a href="ruang_lab.php" class="btn-cancel">Cancel</a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Data List -->
                <div class="data-section">
                    <h2>Lab Rooms List (<?php echo $total_items; ?>)</h2>

                    <?php if (empty($ruang_list)): ?>
                        <p class="muted-gray">No lab rooms registered yet.</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Room Name</th>
                                        <th>Status</th>
                                        <th>Currently Borrowed</th>
                                        <th>Created At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ruang_list as $ruang): ?>
                                        <tr>
                                            <td><?php echo $ruang['id_ruang_lab']; ?></td>
                                            <td><?php echo htmlspecialchars($ruang['nama_ruang']); ?></td>
                                            <td>
                                                <?php 
                                                // Calculate status: if borrowed, show "Borrowed", otherwise show status from database
                                                $jumlah_dipinjam = (int)($ruang['jumlah_dipinjam'] ?? 0);
                                                $db_status = $ruang['status'] ?? 'tersedia';
                                                
                                                if ($jumlah_dipinjam > 0) {
                                                    // If there's active loan, show "Borrowed"
                                                    $status_class = 'status-dipinjam';
                                                    $status_text = 'Borrowed';
                                                } else {
                                                    // Otherwise, show status from database
                                                    $status_map = [
                                                        'tersedia' => ['class' => 'status-tersedia', 'text' => 'Available'],
                                                        'maintenance' => ['class' => 'status-maintenance', 'text' => 'Maintenance'],
                                                        'tidak_tersedia' => ['class' => 'status-tidak-tersedia', 'text' => 'Not Available']
                                                    ];
                                                    $status_info = $status_map[$db_status] ?? ['class' => 'status-tersedia', 'text' => 'Available'];
                                                    $status_class = $status_info['class'];
                                                    $status_text = $status_info['text'];
                                                }
                                                ?>
                                                <span class="status-badge <?php echo $status_class; ?>">
                                                    <?php echo $status_text; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($jumlah_dipinjam > 0): ?>
                                                    <span class="status-badge status-dipinjam">
                                                        <?php echo $jumlah_dipinjam; ?> active loan(s)
                                                    </span>
                                                <?php else: ?>
                                                    <span class="muted-gray">None</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('d M Y', strtotime($ruang['created_at'])); ?></td>
                                            <td>
                                                <a href="?edit=<?php echo $ruang['id_ruang_lab']; ?>" class="btn-edit">
                                                    <i class="ri-edit-line"></i> Edit
                                                </a>
                                                <form method="POST" class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this room?');">
                                                    <input type="hidden" name="action" value="delete_ruang">
                                                    <input type="hidden" name="id" value="<?php echo $ruang['id_ruang_lab']; ?>">
                                                    <button type="submit" class="btn-delete">
                                                        <i class="ri-delete-bin-line"></i> Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <?php if ($current_page > 1): ?>
                                    <a href="?page=<?php echo $current_page - 1; ?>">&laquo; Previous</a>
                                <?php else: ?>
                                    <span class="disabled">&laquo; Previous</span>
                                <?php endif; ?>
                                
                                <?php
                                $start_page = max(1, $current_page - 2);
                                $end_page = min($total_pages, $current_page + 2);
                                
                                if ($start_page > 1): ?>
                                    <a href="?page=1">1</a>
                                    <?php if ($start_page > 2): ?>
                                        <span>...</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <?php if ($i == $current_page): ?>
                                        <span class="active"><?php echo $i; ?></span>
                                    <?php else: ?>
                                        <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                
                                <?php if ($end_page < $total_pages): ?>
                                    <?php if ($end_page < $total_pages - 1): ?>
                                        <span>...</span>
                                    <?php endif; ?>
                                    <a href="?page=<?php echo $total_pages; ?>"><?php echo $total_pages; ?></a>
                                <?php endif; ?>
                                
                                <?php if ($current_page < $total_pages): ?>
                                    <a href="?page=<?php echo $current_page + 1; ?>">Next &raquo;</a>
                                <?php else: ?>
                                    <span class="disabled">Next &raquo;</span>
                                <?php endif; ?>
                            </div>
                            <div class="pagination-info">
                                Showing <?php echo ($offset + 1); ?> - <?php echo min($offset + $items_per_page, $total_items); ?> of <?php echo $total_items; ?> rooms
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</body>

</html>

