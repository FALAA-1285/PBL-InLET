<?php
/**
 * Variables provided by admin/mitra.php
 *
 * @var string $message
 * @var string $message_type
 * @var mixed $edit_mitra
 * @var int $total_items
 * @var array $mitra_list
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
    <title>Manage Partners - CMS InLET</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/admin/mitra.css">
</head>

<body>
    <?php $active_page = 'mitra';
    include __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <main class="content">
        <div class="content-inner">
            <div class="cms-content">
                <h1 class="text-primary mb-4"><i class="ri-community-line"></i> Manage Lab Partners</h1>

                <?php if ($message): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Add/Edit Form -->
                <div class="form-section <?php echo $edit_mitra ? 'edit-form-section active' : ''; ?>">
                    <h2><?php echo $edit_mitra ? 'Edit Partner' : 'Add New Partner'; ?></h2>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action"
                            value="<?php echo $edit_mitra ? 'update_mitra' : 'add_mitra'; ?>">
                        <?php if ($edit_mitra): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_mitra['id_mitra']; ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="nama_institusi">Institution Name *</label>
                            <input type="text" id="nama_institusi" name="nama_institusi"
                                value="<?php echo htmlspecialchars($edit_mitra['nama_institusi'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="logo">Logo URL (optional)</label>
                            <input type="url" id="logo" name="logo"
                                value="<?php echo htmlspecialchars($edit_mitra['logo'] ?? ''); ?>"
                                placeholder="https://example.com/logo.png">
                        </div>

                        <div class="form-group">
                            <label for="logo_file">Or Upload Logo</label>
                            <input type="file" id="logo_file" name="logo_file" accept="image/*">
                        </div>

                        <button type="submit" class="btn-submit">
                            <?php echo $edit_mitra ? 'Update Partner' : 'Add Partner'; ?>
                        </button>
                        <?php if ($edit_mitra): ?>
                            <a href="mitra.php" class="btn-cancel">Cancel</a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Data List -->
                <div class="data-section">
                    <h2>Partner List (<?php echo $total_items; ?>)</h2>

                    <?php if (empty($mitra_list)): ?>
                        <p class="muted-gray">No partners registered yet.</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Logo</th>
                                        <th>Institution Name</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mitra_list as $mitra): ?>
                                        <tr>
                                            <td><?php echo $mitra['id_mitra']; ?></td>
                                            <td class="logo-cell">
                                                <?php if (!empty($mitra['logo'])): ?>
                                                    <img src="<?php echo htmlspecialchars($mitra['logo']); ?>"
                                                        alt="<?php echo htmlspecialchars($mitra['nama_institusi']); ?>"
                                                        onerror="this.style.display='none'">
                                                <?php else: ?>
                                                    <span class="muted-gray">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($mitra['nama_institusi']); ?></td>
                                            <td>
                                                <a href="?edit=<?php echo $mitra['id_mitra']; ?>" class="btn-edit">
                                                    <i class="ri-edit-line"></i> Edit
                                                </a>
                                                <form method="POST" class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this partner?');">
                                                    <input type="hidden" name="action" value="delete_mitra">
                                                    <input type="hidden" name="id" value="<?php echo $mitra['id_mitra']; ?>">
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
                                Showing <?php echo ($offset + 1); ?> - <?php echo min($offset + $items_per_page, $total_items); ?> of <?php echo $total_items; ?> partners
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html>