<?php
/**
 * Variables provided by admin/gallery.php
 *
 * @var string $message
 * @var string $message_type
 * @var array $news_options
 * @var array $gallery_items
 * @var int $total_pages
 * @var int $current_page
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Gallery - CMS InLET</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/admin/gallery.css">
</head>

<body>
    <?php $active_page = 'gallery';
    include __DIR__ . '/../partials/admin_sidebar.php'; ?>
    <main class="content">
        <div class="content-inner">
            <div class="content-header">
                <h1><i class="ri-image-line"></i> Gallery</h1>
            </div>

            <div class="cms-content">
                <?php if ($message): ?>
                    <div class="message <?php echo htmlspecialchars($message_type); ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <div id="edit-form-section" class="form-section edit-form-section">
                    <h2>Edit Gallery Image</h2>
                    <form method="POST" enctype="multipart/form-data" id="edit-gallery-form">
                        <input type="hidden" name="action" value="update_gallery">
                        <input type="hidden" name="id" id="edit_id">
                        <input type="hidden" name="current_image" id="edit_current_image">
                        <div class="form-group">
                            <label>Title *</label>
                            <input type="text" name="title" id="edit_title" required>
                        </div>
                        <div class="form-group">
                            <label>Related News (Optional)</label>
                            <select name="id_news" id="edit_id_news">
                                <option value="">-- Not Related --</option>
                                <?php foreach ($news_options as $news): ?>
                                    <option value="<?php echo $news['id_berita']; ?>">
                                        <?php echo htmlspecialchars($news['judul']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="help-text">If no file or URL is uploaded, image will use news thumbnail.</span>
                        </div>
                        <div class="form-group">
                            <label>Upload Image File</label>
                            <input type="file" name="image_file"
                                accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                            <span class="help-text">Max 5MB. If uploaded, this will override news/URL selection.</span>
                        </div>
                        <div class="form-group">
                            <label>Or Enter Image URL</label>
                            <input type="text" name="image_url" id="edit_image_url"
                                placeholder="https://example.com/image.jpg">
                            <span class="help-text">Optional. Will override news image if filled.</span>
                        </div>
                        <button type="submit" class="btn-submit">Save Changes</button>
                        <button type="button" class="btn-cancel" onclick="cancelEdit()">Cancel</button>
                    </form>
                </div>

                <div class="form-section" id="add-form-section">
                    <h2>Add New Image</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add_gallery">
                        <div class="form-group">
                            <label>Title *</label>
                            <input type="text" name="title" required placeholder="Image title">
                        </div>
                        <div class="form-group">
                            <label>Related News (Optional)</label>
                            <select name="id_news">
                                <option value="">-- Not Related --</option>
                                <?php foreach ($news_options as $news): ?>
                                    <option value="<?php echo $news['id_berita']; ?>">
                                        <?php echo htmlspecialchars($news['judul']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="help-text">If no file/URL uploaded, image will use this news thumbnail.</span>
                        </div>
                        <div class="form-group">
                            <label>Upload Image File</label>
                            <input type="file" name="image_file"
                                accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                            <span class="help-text">Max 5MB. Overrides URL and news image.</span>
                        </div>
                        <div class="form-group">
                            <label>Or Enter Image URL</label>
                            <input type="text" name="image_url" placeholder="https://example.com/image.jpg">
                            <span class="help-text">One option must be filled: select news, upload file, or enter
                                URL.</span>
                        </div>
                        <button type="submit" class="btn-submit">Add Image</button>
                    </form>
                </div>

                <div class="data-section">
                    <h2>Gallery List (<?php echo count($gallery_items); ?>)</h2>
                    <?php if (empty($gallery_items)): ?>
                        <p class="text-center p-4 muted-gray">No gallery data yet.</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Related News</th>
                                        <th>Image</th>
                                        <th>Created</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($gallery_items as $item): ?>
                                        <tr>
                                            <td><?php echo $item['id_gallery']; ?></td>
                                            <td><?php echo htmlspecialchars($item['judul']); ?></td>
                                            <td><?php echo htmlspecialchars($item['news_title'] ?? '-'); ?></td>
                                            <td class="image-cell">
                                                <?php if (!empty($item['gambar'])): ?>
                                                    <img src="<?php echo htmlspecialchars($item['gambar']); ?>"
                                                        alt="<?php echo htmlspecialchars($item['judul']); ?>"
                                                        onerror="this.style.display='none'">
                                                <?php else: ?>
                                                    <span class="muted-gray">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $created = $item['created_at'] ?? null;
                                                echo $created ? date('d M Y', strtotime($created)) : '-';
                                                ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn-edit"
                                                    onclick="editGallery(<?php echo htmlspecialchars(json_encode($item)); ?>)">
                                                    <i class="ri-edit-line"></i> Edit
                                                </button>
                                                <form method="POST" class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this image?');">
                                                    <input type="hidden" name="action" value="delete_gallery">
                                                    <input type="hidden" name="id" value="<?php echo $item['id_gallery']; ?>">
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
                    <?php endif; ?>

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
                                <?php if ($start_page > 2): ?><span>...</span><?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <a href="?page=<?php echo $i; ?>"
                                    class="<?php echo $i == $current_page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                            <?php endfor; ?>

                            <?php if ($end_page < $total_pages): ?>
                                <?php if ($end_page < $total_pages - 1): ?><span>...</span><?php endif; ?>
                                <a href="?page=<?php echo $total_pages; ?>"><?php echo $total_pages; ?></a>
                            <?php endif; ?>

                            <?php if ($current_page < $total_pages): ?>
                                <a href="?page=<?php echo $current_page + 1; ?>">Next &raquo;</a>
                            <?php else: ?>
                                <span class="disabled">Next &raquo;</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($total_pages > 1): ?>
                        <div class="pagination-info">
                            Page <?php echo $current_page; ?> of <?php echo $total_pages; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        function editGallery(item) {
            document.getElementById('edit_id').value = item.id_gallery;
            document.getElementById('edit_title').value = item.judul;
            document.getElementById('edit_id_news').value = item.id_berita || '';
            document.getElementById('edit_image_url').value = '';
            document.getElementById('edit_current_image').value = item.gambar || '';

            document.getElementById('add-form-section').style.display = 'none';
            document.getElementById('edit-form-section').classList.add('active');
            document.getElementById('edit-form-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function cancelEdit() {
            document.getElementById('edit-gallery-form').reset();
            document.getElementById('edit-form-section').classList.remove('active');
            document.getElementById('add-form-section').style.display = 'block';
        }
    </script>
</body>

</html>