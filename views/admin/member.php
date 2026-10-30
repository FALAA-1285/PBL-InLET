<?php
/**
 * Variables provided by admin/member.php
 *
 * @var string $message
 * @var string $message_type
 * @var int $total_items
 * @var array $members
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
    <title>Manage Members - CMS InLET</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/admin/member.css">
</head>

<body>
    <?php $active_page = 'member';
    include __DIR__ . '/../partials/admin_sidebar.php'; ?>
    <main class="content">
        <div class="content-inner">
            <h1 class="text-primary mb-4"><i class="ri-team-line"></i> Manage Members</h1>

            <div class="cms-content">
                <?php if ($message): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Edit Form Section (Hidden by default) -->
                <div id="edit-form-section" class="form-section edit-form-section">
                    <h2>Edit Member</h2>
                    <form method="POST" action="" enctype="multipart/form-data" id="edit-member-form">
                        <input type="hidden" name="action" value="update_member">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="form-group">
                            <label>Name *</label>
                            <input type="text" name="nama" id="edit_nama" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" id="edit_email">
                        </div>
                        <div class="form-group">
                            <label>Position</label>
                            <input type="text" name="jabatan" id="edit_jabatan">
                        </div>
                        <div class="form-group">
                            <label>Area of Expertise</label>
                            <input type="text" name="bidang_keahlian" id="edit_bidang_keahlian"
                                placeholder="Area of expertise (optional)">
                        </div>
                        <div class="form-group">
                            <label>Upload Photo (File)</label>
                            <input type="file" name="foto_file"
                                accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                            <small class="d-block mt-2 text-muted small">Maximum 5MB. Format: JPG, PNG, GIF,
                                WEBP</small>
                        </div>
                        <div class="form-group">
                            <label>Or Enter Photo URL</label>
                            <input type="text" name="foto" id="edit_foto" placeholder="https://example.com/foto.jpg">
                            <small class="d-block mt-2 text-muted small">If file upload is used, URL will be
                                ignored</small>
                        </div>
                        <h3 class="text-primary mt-4 mb-3">Detailed Profile (Optional)</h3>
                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="alamat" id="edit_alamat"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="no_tlp" id="edit_no_tlp">
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="deskripsi" id="edit_deskripsi"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Google Scholar URL</label>
                            <input type="url" name="google_scholar" id="edit_google_scholar" placeholder="https://scholar.google.com/citations?user=...">
                            <small class="d-block mt-2 text-muted small">Link to Google Scholar profile</small>
                        </div>
                        <button type="submit" class="btn-submit">Update Member</button>
                        <button type="button" class="btn-cancel" onclick="cancelEdit()">Cancel</button>
                    </form>
                </div>

                <div class="form-section">
                    <h2>Add New Member</h2>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add_member">
                        <div class="form-group">
                            <label>Name *</label>
                            <input type="text" name="nama" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email">
                        </div>
                        <div class="form-group">
                            <label>Position</label>
                            <input type="text" name="jabatan">
                        </div>
                        <div class="form-group">
                            <label>Area of Expertise</label>
                            <input type="text" name="bidang_keahlian" placeholder="Area of expertise (optional)">
                        </div>
                        <div class="form-group">
                            <label>Upload Photo (File)</label>
                            <input type="file" name="foto_file"
                                accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                            <small class="d-block mt-2 text-muted small">Max 5MB. Format: JPG, PNG, GIF, WEBP</small>
                        </div>
                        <div class="form-group">
                            <label>Or Enter Photo URL</label>
                            <input type="text" name="foto" placeholder="https://example.com/foto.jpg">
                            <small class="d-block mt-2 text-muted small">If file upload is used, URL will be
                                ignored</small>
                        </div>
                        <h3 class="text-primary mt-4 mb-3">Detailed Profile (Optional)</h3>
                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="alamat"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="no_tlp">
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="deskripsi"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Google Scholar URL</label>
                            <input type="url" name="google_scholar" placeholder="https://scholar.google.com/citations?user=...">
                            <small class="d-block mt-2 text-muted small">Link to Google Scholar profile</small>
                        </div>
                        <button type="submit" class="btn-submit">Add Member</button>
                    </form>
                </div>

                <div class="data-section">
                    <h2>Member List (<?php echo $total_items; ?>)</h2>
                    <?php if (empty($members)): ?>
                        <p class="text-center p-4 muted-gray">No members yet</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Position</th>
                                        <th>Phone</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($members as $member): ?>
                                        <tr>
                                            <td><?php echo $member['id_member']; ?></td>
                                            <td><?php echo htmlspecialchars($member['nama']); ?></td>
                                            <td><?php echo htmlspecialchars($member['email'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($member['jabatan'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($member['notlp'] ?? '-'); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button type="button" class="btn-edit"
                                                        onclick="editMember(<?php echo htmlspecialchars(json_encode($member)); ?>)">
                                                        <i class="ri-edit-line"></i> Edit
                                                    </button>
                                                    <form method="POST" class="d-inline"
                                                        onsubmit="return confirm('Are you sure you want to delete this member?');">
                                                        <input type="hidden" name="action" value="delete_member">
                                                        <input type="hidden" name="id" value="<?php echo $member['id_member']; ?>">
                                                        <button type="submit" class="btn-delete">
                                                            <i class="ri-delete-bin-line"></i> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <!-- Pagination -->
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
                            Showing <?php echo ($offset + 1); ?> -
                            <?php echo min($offset + $items_per_page, $total_items); ?> of <?php echo $total_items; ?>
                            members (Page <?php echo $current_page; ?> of <?php echo $total_pages; ?>)
                        </div>
                    <?php endif; ?>
                </div>
            </div>
    </main>

    <script>
        function editMember(member) {
            // Populate edit form
            document.getElementById('edit_id').value = member.id_member;
            document.getElementById('edit_nama').value = member.nama || '';
            document.getElementById('edit_email').value = member.email || '';
            document.getElementById('edit_jabatan').value = member.jabatan || '';
            document.getElementById('edit_bidang_keahlian').value = member.bidang_keahlian || '';
            document.getElementById('edit_foto').value = member.foto || '';
            document.getElementById('edit_alamat').value = member.alamat || '';
            document.getElementById('edit_no_tlp').value = member.notlp || '';
            document.getElementById('edit_deskripsi').value = member.deskripsi || '';
            document.getElementById('edit_google_scholar').value = member.google_scholar || '';

            // Show edit form, hide add form
            document.getElementById('edit-form-section').classList.add('active');
            document.querySelector('.form-section:not(.edit-form-section)').style.display = 'none';

            // Scroll to edit form
            document.getElementById('edit-form-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function cancelEdit() {
            // Hide edit form, show add form
            document.getElementById('edit-form-section').classList.remove('active');
            document.querySelector('.form-section:not(.edit-form-section)').style.display = 'block';

            // Reset edit form
            document.getElementById('edit-member-form').reset();
        }
    </script>
</body>

</html>