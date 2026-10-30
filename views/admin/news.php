<?php
/**
 * Variables provided by admin/news.php
 *
 * @var string $message
 * @var string $message_type
 * @var string $active_tab
 * @var array $news_list
 * @var float $total_pages
 * @var int $current_page
 * @var int $offset
 * @var int $items_per_page
 * @var int $total_items
 * @var array $video_list
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage News - CMS InLET</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/admin/news.css">
</head>

<body>
    <?php $active_page = 'news';
    include __DIR__ . '/../partials/admin_sidebar.php'; ?>
    <main class="content">
        <div class="content-inner">
            <h1 class="text-primary mb-4"><i class="ri-newspaper-line"></i> Manage News</h1>


            <div class="cms-content">
                <?php if ($message): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Tab Navigation -->
                <div class="tab-navigation" style="margin-bottom: 2rem; border-bottom: 2px solid #e2e8f0;">
                    <a href="?tab=news<?php echo isset($_GET['page']) ? '&page=' . intval($_GET['page']) : ''; ?>" 
                       class="tab-btn <?php echo $active_tab === 'news' ? 'active' : ''; ?>" 
                       id="tab-news">News</a>
                    <a href="?tab=video<?php echo isset($_GET['page']) ? '&page=' . intval($_GET['page']) : ''; ?>" 
                       class="tab-btn <?php echo $active_tab === 'video' ? 'active' : ''; ?>" 
                       id="tab-video">Video</a>
                </div>

                <!-- News Tab Content -->
                <div id="tab-content-news" class="tab-content" style="display: <?php echo $active_tab === 'news' ? 'block' : 'none'; ?>;">

                <!-- Edit Form Section (Hidden by default) -->
                <div id="edit-form-section" class="form-section edit-form-section">
                    <h2>Edit News</h2>
                    <form method="POST" action="" enctype="multipart/form-data" id="edit-news-form">
                        <input type="hidden" name="action" value="update_news">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="form-group">
                            <label>News Title *</label>
                            <input type="text" name="judul" id="edit_judul" required>
                        </div>
                        <div class="form-group">
                            <label>Content *</label>
                            <textarea name="konten" id="edit_konten" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Upload Thumbnail Image (File)</label>
                            <input type="file" name="gambar_file"
                                accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                            <small class="d-block mt-2 text-muted small">Maximum 5MB. Format: JPG, PNG, GIF,
                                WEBP</small>
                        </div>
                        <div class="form-group">
                            <label>Or Enter Image URL</label>
                            <input type="text" name="gambar_thumbnail" id="edit_gambar_thumbnail"
                                placeholder="https://example.com/image.jpg">
                            <small class="d-block mt-2 text-muted small">If file upload is used, URL will be
                                ignored</small>
                        </div>
                        <div class="form-group">
                            <label>Date *</label>
                            <input type="date" name="tanggal" id="edit_tanggal" required>
                        </div>
                        <button type="submit" class="btn-submit">Update News</button>
                        <button type="button" class="btn-cancel" onclick="cancelEdit()">Cancel</button>
                    </form>
                </div>

                <div class="form-section">
                    <h2>Add New News</h2>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add_news">
                        <div class="form-group">
                            <label>News Title *</label>
                            <input type="text" name="judul" required>
                        </div>
                        <div class="form-group">
                            <label>Content *</label>
                            <textarea name="konten" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Upload Thumbnail Image (File)</label>
                            <input type="file" name="gambar_file"
                                accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                            <small class="d-block mt-2 text-muted small">Maximum 5MB. Format: JPG, PNG, GIF,
                                WEBP</small>
                        </div>
                        <div class="form-group">
                            <label>Or Enter Image URL</label>
                            <input type="text" name="gambar_thumbnail" placeholder="https://example.com/image.jpg">
                            <small class="d-block mt-2 text-muted small">If file upload is used, URL will be
                                ignored</small>
                        </div>
                        <div class="form-group">
                            <label>Date *</label>
                            <input type="date" name="tanggal" required>
                        </div>
                        <button type="submit" class="btn-submit">Add News</button>
                    </form>
                </div>

                <div class="data-section">
                    <h2>News List (<?php echo count($news_list); ?>)</h2>
                    <?php if (empty($news_list)): ?>
                        <p class="text-center p-4 muted-gray">No news yet</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Thumbnail</th>
                                        <th>Title</th>
                                        <th>Content</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($news_list as $news): ?>
                                        <tr>
                                            <td><?php echo $news['id_berita']; ?></td>
                                            <td class="thumbnail-cell">
                                                <?php if ($news['gambar_thumbnail']): ?>
                                                    <img src="<?php echo htmlspecialchars($news['gambar_thumbnail']); ?>"
                                                        alt="<?php echo htmlspecialchars($news['judul']); ?>"
                                                        onerror="this.style.display='none'">
                                                <?php else: ?>
                                                    <span class="muted-gray">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($news['judul']); ?></td>
                                            <td><?php echo htmlspecialchars(substr($news['konten'], 0, 100)) . (strlen($news['konten']) > 100 ? '...' : ''); ?>
                                            </td>
                                            <td><?php echo date('d M Y', strtotime($news['created_at'])); ?></td>
                                            <td>
                                                <button type="button" class="btn-edit"
                                                    onclick="editNews(<?php echo htmlspecialchars(json_encode($news)); ?>)">
                                                    <i class="ri-edit-line"></i> Edit
                                                </button>
                                                <form method="POST" class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this news?');">
                                                    <input type="hidden" name="action" value="delete_news">
                                                    <input type="hidden" name="id" value="<?php echo $news['id_berita']; ?>">
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
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <?php 
                            $tab_param = $active_tab === 'news' ? '' : '&tab=' . $active_tab;
                            ?>
                            <div class="pagination">
                                <?php if ($current_page > 1): ?>
                                    <a href="?page=<?php echo $current_page - 1; ?><?php echo $tab_param; ?>">&laquo; Previous</a>
                                <?php else: ?>
                                    <span class="disabled">&laquo; Previous</span>
                                <?php endif; ?>

                                <?php
                                $start_page = max(1, $current_page - 2);
                                $end_page = min($total_pages, $current_page + 2);

                                if ($start_page > 1): ?>
                                    <a href="?page=1<?php echo $tab_param; ?>">1</a>
                                    <?php if ($start_page > 2): ?>
                                        <span>...</span>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <?php if ($i == $current_page): ?>
                                        <span class="active"><?php echo $i; ?></span>
                                    <?php else: ?>
                                        <a href="?page=<?php echo $i; ?><?php echo $tab_param; ?>"><?php echo $i; ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($end_page < $total_pages): ?>
                                    <?php if ($end_page < $total_pages - 1): ?>
                                        <span>...</span>
                                    <?php endif; ?>
                                    <a href="?page=<?php echo $total_pages; ?><?php echo $tab_param; ?>"><?php echo $total_pages; ?></a>
                                <?php endif; ?>

                                <?php if ($current_page < $total_pages): ?>
                                    <a href="?page=<?php echo $current_page + 1; ?><?php echo $tab_param; ?>">Next &raquo;</a>
                                <?php else: ?>
                                    <span class="disabled">Next &raquo;</span>
                                <?php endif; ?>
                            </div>
                            <div class="pagination-info">
                                Showing <?php echo ($offset + 1); ?> -
                                <?php echo min($offset + $items_per_page, $total_items); ?> of <?php echo $total_items; ?> news
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    </div>
                </div>

                <!-- Video Tab Content -->
                <div id="tab-content-video" class="tab-content" style="display: <?php echo $active_tab === 'video' ? 'block' : 'none'; ?>;">
                    <!-- Edit Video Form Section (Hidden by default) -->
                    <div id="edit-video-form-section" class="form-section edit-form-section">
                        <h2>Edit Video</h2>
                        <form method="POST" action="" enctype="multipart/form-data" id="edit-video-form">
                            <input type="hidden" name="action" value="update_video">
                            <input type="hidden" name="id" id="edit_video_id">
                            <div class="form-group">
                                <label>Video Title *</label>
                                <input type="text" name="title" id="edit_video_title" required>
                            </div>
                            <div class="form-group">
                                <label>Upload Video File</label>
                                <input type="file" name="video_file" accept="video/*" id="edit_video_file">
                                <small class="d-block mt-2 text-muted small">Maksimal 100MB. Format: MP4, AVI, MOV, WEBM, etc.</small>
                            </div>
                            <div class="form-group">
                                <label>Or Enter Video URL/Link</label>
                                <input type="text" name="href_link" id="edit_video_href_link"
                                    placeholder="https://example.com/video.mp4 or https://youtube.com/watch?v=...">
                                <small class="d-block mt-2 text-muted small">If file upload is used, URL will be ignored. Leave empty if uploading file.</small>
                            </div>
                            <button type="submit" class="btn-submit">Update Video</button>
                            <button type="button" class="btn-cancel" onclick="cancelEditVideo()">Cancel</button>
                        </form>
                    </div>

                    <div class="form-section">
                        <h2>Add New Video</h2>
                        <form method="POST" action="" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="add_video">
                            <div class="form-group">
                                <label>Video Title *</label>
                                <input type="text" name="title" required>
                            </div>
                            <div class="form-group">
                                <label>Upload Video File</label>
                                <input type="file" name="video_file" accept="video/*" id="add_video_file">
                                <small class="d-block mt-2 text-muted small">Maksimal 100MB. Format: MP4, AVI, MOV, WEBM, etc.</small>
                            </div>
                            <div class="form-group">
                                <label>Or Enter Video URL/Link</label>
                                <input type="text" name="href_link" id="add_video_url" placeholder="https://example.com/video.mp4 or https://youtube.com/watch?v=...">
                                <small class="d-block mt-2 text-muted small">If file upload is used, URL will be ignored. Leave empty if uploading file.</small>
                            </div>
                            <button type="submit" class="btn-submit">Add Video</button>
                        </form>
                    </div>

                    <div class="data-section">
                        <h2>Video List (<?php echo count($video_list); ?>)</h2>
                        <?php if (empty($video_list)): ?>
                            <p class="text-center p-4 muted-gray">No videos yet</p>
                        <?php else: ?>
                            <div class="table-container">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Title</th>
                                            <th>Link</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($video_list as $video): ?>
                                            <tr>
                                                <td><?php echo $video['id_video']; ?></td>
                                                <td><?php echo htmlspecialchars($video['title']); ?></td>
                                                <td>
                                                    <a href="<?php echo htmlspecialchars($video['href_link']); ?>" target="_blank" rel="noopener noreferrer" style="color: var(--primary); text-decoration: none;">
                                                        <?php echo htmlspecialchars(substr($video['href_link'], 0, 50)) . (strlen($video['href_link']) > 50 ? '...' : ''); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn-edit"
                                                        onclick="editVideo(<?php echo htmlspecialchars(json_encode($video)); ?>)">
                                                        <i class="ri-edit-line"></i> Edit
                                                    </button>
                                                    <form method="POST" class="d-inline"
                                                        onsubmit="return confirm('Are you sure you want to delete this video?');">
                                                        <input type="hidden" name="action" value="delete_video">
                                                        <input type="hidden" name="id" value="<?php echo $video['id_video']; ?>">
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
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function editNews(news) {
            // Populate edit form
            document.getElementById('edit_id').value = news.id_berita;
            document.getElementById('edit_judul').value = news.judul || '';
            document.getElementById('edit_konten').value = news.konten || '';
            document.getElementById('edit_gambar_thumbnail').value = news.gambar_thumbnail || '';
            
            // Set tanggal from created_at
            if (news.created_at) {
                // Convert timestamp to date format (YYYY-MM-DD)
                const date = new Date(news.created_at);
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                document.getElementById('edit_tanggal').value = `${year}-${month}-${day}`;
            } else {
                document.getElementById('edit_tanggal').value = '';
            }

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
            document.getElementById('edit-news-form').reset();
        }

        // Tab switching is now handled by URL parameters, no JavaScript needed
        // But keep function for backward compatibility if needed
        function switchTab(tab) {
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            // Preserve page parameter if exists
            if (url.searchParams.has('page')) {
                // Keep page parameter
            } else {
                url.searchParams.delete('page');
            }
            window.location.href = url.toString();
        }

        function editVideo(video) {
            // Populate edit form
            document.getElementById('edit_video_id').value = video.id_video;
            document.getElementById('edit_video_title').value = video.title || '';
            document.getElementById('edit_video_href_link').value = video.href_link || '';

            // Show edit form, hide add form
            document.getElementById('edit-video-form-section').classList.add('active');
            document.querySelector('#tab-content-video .form-section:not(.edit-form-section)').style.display = 'none';

            // Scroll to edit form
            document.getElementById('edit-video-form-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function cancelEditVideo() {
            // Hide edit form, show add form
            document.getElementById('edit-video-form-section').classList.remove('active');
            document.querySelector('#tab-content-video .form-section:not(.edit-form-section)').style.display = 'block';

            // Reset edit form
            document.getElementById('edit-video-form').reset();
        }
    </script>
</body>

</html>