<?php
/**
 * Variables provided by admin/alat_lab.php
 *
 * @var string $message
 * @var string $message_type
 * @var mixed $edit_alat
 * @var int $total_items
 * @var array $alat_list
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
    <title>Manage Lab Tools - CMS InLET</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/admin/alat_lab.css">
</head>

<body>
    <?php $active_page = 'alat_lab';
    include __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <main class="content">
        <div class="content-inner">
            <div class="cms-content">
                <h1 class="text-primary mb-4"><i class="ri-tools-line"></i> Manage Lab Tools</h1>

                <?php if ($message): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Add/Edit Form -->
                <div class="form-section <?php echo $edit_alat ? 'edit-form-section active' : ''; ?>">
                    <h2><?php echo $edit_alat ? 'Edit Lab Tool' : 'Add New Lab Tool'; ?></h2>
                    <form method="POST">
                        <input type="hidden" name="action"
                            value="<?php echo $edit_alat ? 'update_alat' : 'add_alat'; ?>">
                        <?php if ($edit_alat): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_alat['id_alat_lab']; ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="nama_alat">Tool Name *</label>
                            <input type="text" id="nama_alat" name="nama_alat"
                                value="<?php echo htmlspecialchars($edit_alat['nama_alat'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="deskripsi">Description</label>
                            <textarea id="deskripsi" name="deskripsi"
                                placeholder="Lab tool description"><?php echo htmlspecialchars($edit_alat['deskripsi'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="stock">Stock *</label>
                            <input type="number" id="stock" name="stock"
                                value="<?php echo htmlspecialchars($edit_alat['stock'] ?? 0); ?>" min="0" required>
                        </div>

                        <button type="submit" class="btn-submit">
                            <?php echo $edit_alat ? 'Update Lab Tool' : 'Add Lab Tool'; ?>
                        </button>
                        <?php if ($edit_alat): ?>
                            <a href="alat_lab.php" class="btn-cancel">Cancel</a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Data List -->
                <div class="data-section">
                    <h2>Lab Tools List (<?php echo $total_items; ?>)</h2>

                    <?php if (empty($alat_list)): ?>
                        <p class="muted-gray">No lab tools registered yet.</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tool Name</th>
                                        <th>Description</th>
                                        <th>Total Stock</th>
                                        <th>Available Stock</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($alat_list as $alat): ?>
                                        <tr>
                                            <td><?php echo $alat['id_alat_lab']; ?></td>
                                            <td><?php echo htmlspecialchars($alat['nama_alat']); ?></td>
                                            <td><?php echo htmlspecialchars($alat['deskripsi'] ?? '-'); ?></td>
                                            <td><?php echo $alat['stock']; ?> unit</td>
                                            <td>
                                                <span class="stock-badge <?php
                                                $stok_tersedia = $alat['stok_tersedia'] ?? 0;
                                                echo $stok_tersedia > 5 ? 'stock-available' : ($stok_tersedia > 0 ? 'stock-low' : 'stock-empty');
                                                ?>">
                                                    <?php echo $stok_tersedia; ?> units available
                                                    <?php if (isset($alat['jumlah_dipinjam']) && $alat['jumlah_dipinjam'] > 0): ?>
                                                        <br><small class="small text-muted">(<?php echo $alat['jumlah_dipinjam']; ?>
                                                            borrowed)</small>
                                                    <?php endif; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="?edit=<?php echo $alat['id_alat_lab']; ?>" class="btn-edit">
                                                    <i class="ri-edit-line"></i> Edit
                                                </a>
                                                <form method="POST" class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this tool?');">
                                                    <input type="hidden" name="action" value="delete_alat">
                                                    <input type="hidden" name="id" value="<?php echo $alat['id_alat_lab']; ?>">
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
                                Showing <?php echo ($offset + 1); ?> - <?php echo min($offset + $items_per_page, $total_items); ?> of <?php echo $total_items; ?> tools
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</body>

</html>