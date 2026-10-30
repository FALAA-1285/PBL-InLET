<?php
/**
 * Variables provided by admin/mahasiswa.php
 *
 * @var string $message
 * @var string $message_type
 * @var mixed $edit_mahasiswa
 * @var int $total_items
 * @var array $mahasiswa_list
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
    <title>Manage Students - CMS InLET</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/admin/mahasiswa.css">
</head>

<body>
    <?php $active_page = 'mahasiswa';
    include __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <main class="content">
        <div class="content-inner">
            <div class="cms-content">
                <h1 class="text-primary mb-4"><i class="ri-graduation-cap-line"></i> Manage Students</h1>

                <?php if ($message): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Add/Edit Form -->
                <div class="form-section <?php echo $edit_mahasiswa ? 'edit-form-section active' : ''; ?>">
                    <h2><?php echo $edit_mahasiswa ? 'Edit Student' : 'Add New Student'; ?></h2>
                    <form method="POST">
                        <input type="hidden" name="action"
                            value="<?php echo $edit_mahasiswa ? 'update_mahasiswa' : 'add_mahasiswa'; ?>">
                        <?php if ($edit_mahasiswa): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_mahasiswa['nim'] ?? ''; ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="nim">NIM (Student ID) *</label>
                            <input type="text" id="nim" name="nim"
                                value="<?php echo htmlspecialchars($edit_mahasiswa['nim'] ?? ''); ?>" 
                                <?php echo $edit_mahasiswa ? 'readonly' : 'required'; ?>
                                placeholder="e.g., 225150200111001">
                            <?php if ($edit_mahasiswa): ?>
                                <small style="color: #6b7280; font-size: 0.875rem;">NIM cannot be changed</small>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="nama">Name *</label>
                            <input type="text" id="nama" name="nama"
                                value="<?php echo htmlspecialchars($edit_mahasiswa['nama'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="tahun">Year</label>
                            <input type="number" id="tahun" name="tahun"
                                value="<?php echo htmlspecialchars($edit_mahasiswa['tahun'] ?? ''); ?>" min="2000"
                                max="2100">
                        </div>

                        <div class="form-group">
                            <label for="status">Status *</label>
                            <select id="status" name="status" required>
                                <option value="regular" <?php echo (($edit_mahasiswa['status'] ?? 'regular') === 'regular') ? 'selected' : ''; ?>>Regular</option>
                                <option value="magang" <?php echo (($edit_mahasiswa['status'] ?? '') === 'magang') ? 'selected' : ''; ?>>Internship</option>
                                <option value="skripsi" <?php echo (($edit_mahasiswa['status'] ?? '') === 'skripsi') ? 'selected' : ''; ?>>Undergraduate Thesis</option>
                            </select>
                        </div>

                        <button type="submit" class="btn-submit">
                            <?php echo $edit_mahasiswa ? 'Update Student' : 'Add Student'; ?>
                        </button>
                        <?php if ($edit_mahasiswa): ?>
                            <a href="mahasiswa.php" class="btn-cancel">Cancel</a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Data List -->
                <div class="data-section">
                    <h2>Student List (<?php echo $total_items; ?>)</h2>

                    <?php if (empty($mahasiswa_list)): ?>
                        <p class="muted-gray">No students registered yet.</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>NIM</th>
                                        <th>Name</th>
                                        <th>Year</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mahasiswa_list as $mhs): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($mhs['nim'] ?? $mhs['id_mahasiswa'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($mhs['nama']); ?></td>
                                            <td><?php echo $mhs['tahun'] ?? '-'; ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $mhs['status']; ?>">
                                                    <?php
                                                    echo ucfirst($mhs['status']);
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="?edit=<?php echo $mhs['nim'] ?? ''; ?>" class="btn-edit">
                                                    <i class="ri-edit-line"></i> Edit
                                                </a>
                                                <form method="POST" class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this student?');">
                                                    <input type="hidden" name="action" value="delete_mahasiswa">
                                                    <input type="hidden" name="id" value="<?php echo $mhs['nim'] ?? ''; ?>">
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
                                Showing <?php echo ($offset + 1); ?> - <?php echo min($offset + $items_per_page, $total_items); ?> of <?php echo $total_items; ?> students
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</body>

</html>