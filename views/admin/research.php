<?php
/**
 * Variables provided by admin/research.php
 *
 * @var string $message
 * @var string $message_type
 * @var string $current_tab
 * @var array $penelitian_dropdown
 * @var array $mahasiswa_list
 * @var array $member_list
 * @var \PDO $conn
 * @var array $artikels
 * @var float $total_pages_artikel
 * @var int $current_page_artikel
 * @var int $offset_artikel
 * @var int $items_per_page
 * @var int $total_items_artikel
 * @var array $progress_list
 * @var float $total_pages_progress
 * @var int $current_page_progress
 * @var int $offset_progress
 * @var int $total_items_progress
 * @var array $fokus_penelitian_list
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Research - CMS InLET</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/admin/research.css">
</head>

<body>
    <?php $active_page = 'research';
    include __DIR__ . '/../partials/admin_sidebar.php'; ?>
    <main class="content">
        <div class="content-inner">
            <h1 class="text-primary mb-4"><i class="ri-flask-line"></i> Manage Research</h1>

            <div class="cms-content">
                <?php 
                // Show message from URL parameter first (for redirects), then from $message variable
                if (isset($_GET['added']) && $_GET['added'] == 1): ?>
                    <div class="message success">
                        Research detail successfully added!
                    </div>
                <?php elseif (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
                    <div class="message success">
                        Research detail successfully updated!
                    </div>
                <?php elseif (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
                    <div class="message success">
                        Research detail successfully deleted!   
                    </div>
                <?php elseif ($message): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <div class="tabs">
                    <a href="?tab=artikel&page=1"
                        class="tab <?php echo ($current_tab === 'artikel') ? 'active' : ''; ?>">Article</a>
                    <a href="?tab=penelitian&page=1"
                        class="tab <?php echo ($current_tab === 'penelitian') ? 'active' : ''; ?>">Research</a>
                    <a href="?tab=research_detail"
                        class="tab <?php echo ($current_tab === 'research_detail') ? 'active' : ''; ?>">Research Detail</a>
                    <a href="?tab=product"
                        class="tab <?php echo ($current_tab === 'product') ? 'active' : ''; ?>">Product</a>
                </div>

                <!-- Artikel Tab -->
                <div id="artikel-tab" class="tab-content <?php echo ($current_tab === 'artikel') ? 'active' : ''; ?>">
                    <!-- Edit Artikel Form (Hidden by default) -->
                    <div id="edit-artikel-section" class="form-section edit-form-section">
                        <h2>Edit Article</h2>
                        <form method="POST" action="" id="edit-artikel-form">
                            <input type="hidden" name="action" value="update_artikel">
                            <input type="hidden" name="id" id="edit_artikel_id">
                            <div class="form-group">
                                <label>Article Title</label>
                                <input type="text" name="judul" id="edit_artikel_judul" required>
                            </div>
                            <div class="form-group">
                                <label>Year</label>
                                <input type="number" name="tahun" id="edit_artikel_tahun" min="2000" max="2099">
                            </div>
                            <div class="form-group">
                                <label>Content</label>
                                <textarea name="konten" id="edit_artikel_konten" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Research (Optional)</label>
                                <select name="id_penelitian" id="edit_artikel_id_penelitian">
                                    <option value="">-- Select Research --</option>
                                    <?php foreach ($penelitian_dropdown as $penelitian): ?>
                                        <option value="<?php echo $penelitian['id_penelitian']; ?>">
                                            <?php echo htmlspecialchars($penelitian['judul']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Student (Optional)</label>
                                <select name="nim" id="edit_artikel_nim">
                                    <option value="">-- Select Student --</option>
                                    <?php foreach ($mahasiswa_list as $mhs): ?>
                                        <option value="<?php echo $mhs['nim']; ?>">
                                            <?php echo htmlspecialchars($mhs['nama']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Member (Optional)</label>
                                <select name="id_member" id="edit_artikel_id_member">
                                    <option value="">-- Select Member --</option>
                                    <?php foreach ($member_list as $mem): ?>
                                        <option value="<?php echo $mem['id_member']; ?>">
                                            <?php echo htmlspecialchars($mem['nama']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Product (Optional)</label>
                                <select name="id_produk" id="edit_artikel_id_produk">
                                    <option value="">-- Select Product --</option>
                                    <?php
                                    $produk_stmt = $conn->query("SELECT id_produk, nama_produk FROM produk ORDER BY nama_produk");
                                    $produk_list = $produk_stmt->fetchAll();
                                    foreach ($produk_list as $prod): ?>
                                        <option value="<?php echo $prod['id_produk']; ?>">
                                            <?php echo htmlspecialchars($prod['nama_produk']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Partner (Optional)</label>
                                <select name="id_mitra" id="edit_artikel_id_mitra">
                                    <option value="">-- Select Partner --</option>
                                    <?php
                                    $mitra_stmt = $conn->query("SELECT id_mitra, nama_institusi FROM mitra ORDER BY nama_institusi");
                                    $mitra_list = $mitra_stmt->fetchAll();
                                    foreach ($mitra_list as $mit): ?>
                                        <option value="<?php echo $mit['id_mitra']; ?>">
                                            <?php echo htmlspecialchars($mit['nama_institusi']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn-submit">Update Article</button>
                            <button type="button" class="btn-cancel" onclick="cancelEditArtikel()">Cancel</button>
                        </form>
                    </div>

                    <div class="form-section">
                        <h2>Add New Article</h2>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="add_artikel">
                            <div class="form-group">
                                <label>Article Title</label>
                                <input type="text" name="judul" required>
                            </div>
                            <div class="form-group">
                                <label>Year</label>
                                <input type="number" name="tahun" min="2000" max="2099">
                            </div>
                            <div class="form-group">
                                <label>Content</label>
                                <textarea name="konten" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Research (Optional)</label>
                                <select name="id_penelitian">
                                    <option value="">-- Select Research --</option>
                                    <?php foreach ($penelitian_dropdown as $penelitian): ?>
                                        <option value="<?php echo $penelitian['id_penelitian']; ?>">
                                            <?php echo htmlspecialchars($penelitian['judul']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Student (Optional)</label>
                                <select name="nim">
                                    <option value="">-- Select Student --</option>
                                    <?php foreach ($mahasiswa_list as $mhs): ?>
                                        <option value="<?php echo $mhs['nim']; ?>">
                                            <?php echo htmlspecialchars($mhs['nama']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Member (Optional)</label>
                                <select name="id_member">
                                    <option value="">-- Select Member --</option>
                                    <?php foreach ($member_list as $mem): ?>
                                        <option value="<?php echo $mem['id_member']; ?>">
                                            <?php echo htmlspecialchars($mem['nama']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Product (Optional)</label>
                                <select name="id_produk">
                                    <option value="">-- Select Product --</option>
                                    <?php
                                    if (!isset($produk_list)) {
                                        $produk_stmt = $conn->query("SELECT id_produk, nama_produk FROM produk ORDER BY nama_produk");
                                        $produk_list = $produk_stmt->fetchAll();
                                    }
                                    foreach ($produk_list as $prod): ?>
                                        <option value="<?php echo $prod['id_produk']; ?>">
                                            <?php echo htmlspecialchars($prod['nama_produk']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Partner (Optional)</label>
                                <select name="id_mitra">
                                    <option value="">-- Select Partner --</option>
                                    <?php
                                    if (!isset($mitra_list)) {
                                        $mitra_stmt = $conn->query("SELECT id_mitra, nama_institusi FROM mitra ORDER BY nama_institusi");
                                        $mitra_list = $mitra_stmt->fetchAll();
                                    }
                                    foreach ($mitra_list as $mit): ?>
                                        <option value="<?php echo $mit['id_mitra']; ?>">
                                            <?php echo htmlspecialchars($mit['nama_institusi']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn-submit">Add Article</button>
                        </form>
                    </div>

                    <div class="data-section">
                        <h2>Article List (<?php echo count($artikels); ?>)</h2>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Year</th>
                                    <th>Research</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($artikels)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center muted-gray">No articles yet</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($artikels as $artikel): ?>
                                        <tr>
                                            <td><?php echo $artikel['id_artikel']; ?></td>
                                            <td><?php echo htmlspecialchars($artikel['judul']); ?></td>
                                            <td><?php echo $artikel['tahun'] ?? '-'; ?></td>
                                            <td><?php echo htmlspecialchars($artikel['penelitian_judul'] ?? '-'); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                <button type="button" class="btn-edit"
                                                    onclick="editArtikel(<?php echo htmlspecialchars(json_encode($artikel)); ?>)">
                                                    <i class="ri-edit-line"></i> Edit
                                                </button>
                                                <form method="POST" class="d-inline"
                                                        onsubmit="return confirm('Are you sure you want to delete this article?');">
                                                    <input type="hidden" name="action" value="delete_artikel">
                                                    <input type="hidden" name="id"
                                                        value="<?php echo $artikel['id_artikel']; ?>">
                                                    <button type="submit" class="btn-delete">
                                                            <i class="ri-delete-bin-line"></i> Delete
                                                    </button>
                                                </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <!-- Pagination for Artikel -->
                        <?php if ($total_pages_artikel > 1): ?>
                            <div class="pagination">
                                <?php if ($current_page_artikel > 1): ?>
                                    <a href="?tab=artikel&page=<?php echo $current_page_artikel - 1; ?>">&laquo; Previous</a>
                                <?php else: ?>
                                    <span class="disabled">&laquo; Previous</span>
                                <?php endif; ?>

                                <?php
                                $start_page = max(1, $current_page_artikel - 2);
                                $end_page = min($total_pages_artikel, $current_page_artikel + 2);

                                if ($start_page > 1): ?>
                                    <a href="?tab=artikel&page=1">1</a>
                                    <?php if ($start_page > 2): ?>
                                        <span>...</span>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <?php if ($i == $current_page_artikel): ?>
                                        <span class="active"><?php echo $i; ?></span>
                                    <?php else: ?>
                                        <a href="?tab=artikel&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($end_page < $total_pages_artikel): ?>
                                    <?php if ($end_page < $total_pages_artikel - 1): ?>
                                        <span>...</span>
                                    <?php endif; ?>
                                    <a
                                        href="?tab=artikel&page=<?php echo $total_pages_artikel; ?>"><?php echo $total_pages_artikel; ?></a>
                                <?php endif; ?>

                                <?php if ($current_page_artikel < $total_pages_artikel): ?>
                                    <a href="?tab=artikel&page=<?php echo $current_page_artikel + 1; ?>">Next &raquo;</a>
                                <?php else: ?>
                                    <span class="disabled">Next &raquo;</span>
                                <?php endif; ?>
                            </div>
                            <div class="pagination-info">
                                Showing <?php echo ($offset_artikel + 1); ?> -
                                <?php echo min($offset_artikel + $items_per_page, $total_items_artikel); ?> of
                                <?php echo $total_items_artikel; ?> articles
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Penelitian Tab -->
                <div id="penelitian-tab"
                    class="tab-content <?php echo ($current_tab === 'penelitian') ? 'active' : ''; ?>">
                    <!-- Edit Penelitian Form (Hidden by default) -->
                    <div id="edit-penelitian-section" class="form-section edit-form-section">
                        <h2>Edit Research</h2>
                        <form method="POST" action="" id="edit-penelitian-form">
                            <input type="hidden" name="action" value="update_penelitian">
                            <input type="hidden" name="id" id="edit_penelitian_id">
                            <div class="form-group">
                                <label>Research Title *</label>
                                <input type="text" name="judul" id="edit_penelitian_judul" required>
                            </div>
                            <div class="form-group">
                                <label>Year</label>
                                <input type="number" name="tahun" id="edit_penelitian_tahun" min="2000" max="2099">
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="deskripsi" id="edit_penelitian_deskripsi"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Start Date *</label>
                                <input type="date" name="tgl_mulai" id="edit_penelitian_tgl_mulai" required>
                            </div>
                            <div class="form-group">
                                <label>End Date</label>
                                <input type="date" name="tgl_selesai" id="edit_penelitian_tgl_selesai">
                            </div>
                            <button type="submit" class="btn-submit">Update Research</button>
                            <button type="button" class="btn-cancel" onclick="cancelEditPenelitian()">Cancel</button>
                        </form>
                    </div>

                    <div class="form-section">
                        <h2>Add New Research</h2>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="add_penelitian">
                            <div class="form-group">
                                <label>Research Title *</label>
                                <input type="text" name="judul" required>
                            </div>
                            <div class="form-group">
                                <label>Year</label>
                                <input type="number" name="tahun" min="2000" max="2099">
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="deskripsi"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Start Date *</label>
                                <input type="date" name="tgl_mulai" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>End Date</label>
                                <input type="date" name="tgl_selesai">
                            </div>
                            <button type="submit" class="btn-submit">Add Research</button>
                        </form>
                    </div>

                    <div class="data-section">
                        <h2>Research List (<?php echo count($progress_list); ?>)</h2>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Year</th>
                                    <th>Start Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($progress_list)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center muted-gray">No research yet</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($progress_list as $penelitian): ?>
                                        <tr>
                                            <td><?php echo $penelitian['id_penelitian']; ?></td>
                                            <td><?php echo htmlspecialchars($penelitian['judul']); ?></td>
                                            <td><?php echo $penelitian['tahun'] ?? '-'; ?></td>
                                            <td><?php echo $penelitian['tgl_mulai'] ? date('d M Y', strtotime($penelitian['tgl_mulai'])) : '-'; ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                <button type="button" class="btn-edit"
                                                    onclick="editPenelitian(<?php echo htmlspecialchars(json_encode($penelitian)); ?>)">
                                                    <i class="ri-edit-line"></i> Edit
                                                </button>
                                                <form method="POST" class="d-inline"
                                                        onsubmit="return confirm('Are you sure you want to delete this research?');">
                                                    <input type="hidden" name="action" value="delete_penelitian">
                                                    <input type="hidden" name="id"
                                                        value="<?php echo $penelitian['id_penelitian']; ?>">
                                                    <button type="submit" class="btn-delete">
                                                            <i class="ri-delete-bin-line"></i> Delete
                                                    </button>
                                                </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <!-- Pagination for Penelitian -->
                        <?php if ($total_pages_progress > 1): ?>
                            <div class="pagination">
                                <?php if ($current_page_progress > 1): ?>
                                    <a href="?tab=penelitian&page=<?php echo $current_page_progress - 1; ?>">&laquo;
                                        Previous</a>
                                <?php else: ?>
                                    <span class="disabled">&laquo; Previous</span>
                                <?php endif; ?>

                                <?php
                                $start_page = max(1, $current_page_progress - 2);
                                $end_page = min($total_pages_progress, $current_page_progress + 2);

                                if ($start_page > 1): ?>
                                    <a href="?tab=penelitian&page=1">1</a>
                                    <?php if ($start_page > 2): ?>
                                        <span>...</span>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <?php if ($i == $current_page_progress): ?>
                                        <span class="active"><?php echo $i; ?></span>
                                    <?php else: ?>
                                        <a href="?tab=penelitian&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($end_page < $total_pages_progress): ?>
                                    <?php if ($end_page < $total_pages_progress - 1): ?>
                                        <span>...</span>
                                    <?php endif; ?>
                                    <a
                                        href="?tab=penelitian&page=<?php echo $total_pages_progress; ?>"><?php echo $total_pages_progress; ?></a>
                                <?php endif; ?>

                                <?php if ($current_page_progress < $total_pages_progress): ?>
                                    <a href="?tab=penelitian&page=<?php echo $current_page_progress + 1; ?>">Next &raquo;</a>
                                <?php else: ?>
                                    <span class="disabled">Next &raquo;</span>
                                <?php endif; ?>
                            </div>
                            <div class="pagination-info">
                                Showing <?php echo ($offset_progress + 1); ?> -
                                <?php echo min($offset_progress + $items_per_page, $total_items_progress); ?> of
                                <?php echo $total_items_progress; ?> research
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Research Detail Tab -->
                <div id="research_detail-tab"
                    class="tab-content <?php echo ($current_tab === 'research_detail') ? 'active' : ''; ?>">
                    <!-- Edit Research Detail Form (Hidden by default) -->
                    <div id="edit-research-detail-section" class="form-section edit-form-section">
                        <h2>Edit Research Detail</h2>
                        <form method="POST" action="" id="edit-research-detail-form">
                            <input type="hidden" name="action" value="update_fokus_penelitian">
                            <input type="hidden" name="id" id="edit_research_detail_id">
                            <div class="form-group">
                                <label>Title * (Max 200 characters)</label>
                                <input type="text" name="title" id="edit_research_detail_title" maxlength="200" required>
                                <small class="text-muted">Used for "Our Research" section</small>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="deskripsi" id="edit_research_detail_deskripsi" rows="3"></textarea>
                                <small class="text-muted">Used for "Our Research" section</small>
                            </div>
                            <div class="form-group">
                                <label>Detail</label>
                                <textarea name="detail" id="edit_research_detail_detail" rows="12" style="min-height: 200px;"></textarea>
                                <small class="text-muted">Used for "Research Fields" section. Use "-" at the start of each line for bullet points.</small>
                                <div class="char-counter" id="edit_detail_counter" style="text-align: right; color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">
                                    <span id="edit_detail_count">0</span> characters
                                </div>
                            </div>
                            <button type="submit" class="btn-submit">Update Research Detail</button>
                            <button type="button" class="btn-cancel" onclick="cancelEditResearchDetail()">Cancel</button>
                        </form>
                    </div>

                    <div class="form-section">
                        <h2>Add New Research Detail</h2>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="add_fokus_penelitian">
                            <div class="form-group">
                                <label>Title * (Max 200 characters)</label>
                                <input type="text" name="title" maxlength="200" required>
                                <small class="text-muted">Used for "Our Research" section</small>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="deskripsi" rows="3"></textarea>
                                <small class="text-muted">Used for "Our Research" section</small>
                            </div>
                            <div class="form-group">
                                <label>Detail</label>
                                <textarea name="detail" id="add_research_detail_detail" rows="12" style="min-height: 200px;"></textarea>
                                <small class="text-muted">Used for "Research Fields" section. Use "-" at the start of each line for bullet points.</small>
                                <div class="char-counter" id="add_detail_counter" style="text-align: right; color: #64748b; font-size: 0.875rem; margin-top: 0.25rem;">
                                    <span id="add_detail_count">0</span> characters
                                </div>
                            </div>
                            <button type="submit" class="btn-submit">Add Research Detail</button>
                        </form>
                    </div>

                    <div class="data-section">
                        <h2>Research Detail List (<?php echo count($fokus_penelitian_list); ?>)</h2>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Detail</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($fokus_penelitian_list)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center muted-gray">No research detail yet</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($fokus_penelitian_list as $fp): ?>
                                        <tr>
                                            <td><?php echo $fp['id_fp']; ?></td>
                                            <td><?php echo htmlspecialchars($fp['title']); ?></td>
                                            <td><?php echo htmlspecialchars($fp['deskripsi'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($fp['detail'] ?? '-'); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button type="button" class="btn-edit"
                                                        onclick="editResearchDetail(<?php echo htmlspecialchars(json_encode($fp)); ?>)">
                                                        <i class="ri-edit-line"></i> Edit
                                                    </button>
                                                    <a href="?tab=research_detail&action=delete_fokus_penelitian&id=<?php echo $fp['id_fp']; ?>"
                                                        class="btn-delete"
                                                        onclick="return confirm('Are you sure you want to delete this research detail?');">
                                                        <i class="ri-delete-bin-line"></i> Delete
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Product Tab -->
                <div id="product-tab"
                    class="tab-content <?php echo ($current_tab === 'product') ? 'active' : ''; ?>">
                    <?php if (isset($_GET['added']) && $_GET['added'] == 1): ?>
                        <div class="message success">
                            Product successfully added!
                        </div>
                    <?php elseif (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
                        <div class="message success">
                            Product successfully updated!
                        </div>
                    <?php elseif (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
                        <div class="message success">
                            Product successfully deleted!
                        </div>
                    <?php elseif ($message && $current_tab === 'product'): ?>
                        <div class="message <?php echo $message_type; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Edit Product Form (Hidden by default) -->
                    <div id="edit-product-section" class="form-section edit-form-section">
                        <h2>Edit Product</h2>
                        <form method="POST" id="edit-product-form" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update_produk">
                            <input type="hidden" name="id" id="edit_product_id">
                            <input type="hidden" name="current_gambar" id="edit_product_current_gambar">
                            <div class="form-group">
                                <label for="edit_product_nama">Product Name *</label>
                                <input type="text" id="edit_product_nama" name="nama_produk" required
                                    maxlength="255">
                            </div>
                            <div class="form-group">
                                <label for="edit_product_deskripsi">Description</label>
                                <textarea id="edit_product_deskripsi" name="deskripsi" rows="5"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="edit_product_gambar_file">Upload Product Image (File)</label>
                                <input type="file" id="edit_product_gambar_file" name="gambar_file"
                                    accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                <small class="d-block mt-2 text-muted small">Max 5MB. Format: JPG, PNG, GIF, WEBP</small>
                            </div>
                            <div class="form-group">
                                <label for="edit_product_gambar">Or Enter Image URL</label>
                                <input type="text" id="edit_product_gambar" name="gambar"
                                    placeholder="https://example.com/image.jpg">
                                <small class="d-block mt-2 text-muted small">If file upload is used, URL will be ignored</small>
                            </div>
                            <div class="form-group">
                                <label for="edit_product_try">Try URL (Optional)</label>
                                <input type="text" id="edit_product_try" name="try"
                                    placeholder="https://example.com/try-now">
                                <small class="d-block mt-2 text-muted small">URL untuk tombol "Try Now" pada produk</small>
                            </div>
                            <div id="edit_product_image_preview" class="mb-3" style="display: none;">
                                <label>Current Image:</label>
                                <div>
                                    <img id="edit_product_image_preview_img" src="" alt="Current image" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                                </div>
                            </div>
                            <button type="submit" class="btn-submit">Update Product</button>
                            <button type="button" class="btn-cancel" onclick="cancelEditProduct()">Cancel</button>
                        </form>
                    </div>

                    <!-- Add Product Form -->
                    <div class="form-section">
                        <h2>Add New Product</h2>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="add_produk">
                            <div class="form-group">
                                <label for="product_nama">Product Name *</label>
                                <input type="text" id="product_nama" name="nama_produk" required maxlength="255">
                            </div>
                            <div class="form-group">
                                <label for="product_deskripsi">Description</label>
                                <textarea id="product_deskripsi" name="deskripsi" rows="5"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="product_gambar_file">Upload Product Image (File)</label>
                                <input type="file" id="product_gambar_file" name="gambar_file"
                                    accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                <small class="d-block mt-2 text-muted small">Max 5MB. Format: JPG, PNG, GIF, WEBP</small>
                            </div>
                            <div class="form-group">
                                <label for="product_gambar">Or Enter Image URL</label>
                                <input type="text" id="product_gambar" name="gambar"
                                    placeholder="https://example.com/image.jpg">
                                <small class="d-block mt-2 text-muted small">If file upload is used, URL will be ignored</small>
                            </div>
                            <div class="form-group">
                                <label for="product_try">Try URL (Optional)</label>
                                <input type="text" id="product_try" name="try"
                                    placeholder="https://example.com/try-now">
                                <small class="d-block mt-2 text-muted small">URL untuk tombol "Try Now" pada produk</small>
                            </div>
                            <button type="submit" class="btn-submit">Add Product</button>
                        </form>
                    </div>

                    <div class="data-section">
                        <h2>Product List (<?php echo count($produk_list); ?>)</h2>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Product Name</th>
                                    <th>Description</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($produk_list)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center muted-gray">No products yet</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($produk_list as $produk): ?>
                                        <tr>
                                            <td><?php echo $produk['id_produk']; ?></td>
                                            <td>
                                                <?php if (!empty($produk['gambar'])): ?>
                                                    <img src="<?php echo htmlspecialchars($produk['gambar']); ?>" 
                                                         alt="<?php echo htmlspecialchars($produk['nama_produk']); ?>"
                                                         style="max-width: 100px; max-height: 100px; object-fit: contain; border-radius: 8px;"
                                                         onerror="this.style.display='none'">
                                                <?php else: ?>
                                                    <span class="muted-gray">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($produk['nama_produk']); ?></td>
                                            <td>
                                                <?php if (!empty($produk['deskripsi'])): ?>
                                                    <?php 
                                                    $deskripsi = htmlspecialchars($produk['deskripsi']);
                                                    // Truncate long descriptions
                                                    if (strlen($deskripsi) > 100) {
                                                        echo substr($deskripsi, 0, 100) . '...';
                                                    } else {
                                                        echo $deskripsi;
                                                    }
                                                    ?>
                                                <?php else: ?>
                                                    <span class="muted-gray">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button type="button" class="btn-edit"
                                                        onclick="editProduct(<?php 
                                                            // Convert null to empty string to prevent "undefined" in JavaScript
                                                            $produk_json = [
                                                                'id_produk' => $produk['id_produk'] ?? 0,
                                                                'nama_produk' => $produk['nama_produk'] ?? '',
                                                                'deskripsi' => $produk['deskripsi'] ?? '',
                                                                'gambar' => $produk['gambar'] ?? '',
                                                                'try' => $produk['try'] ?? ''
                                                            ];
                                                            echo htmlspecialchars(json_encode($produk_json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
                                                        ?>)">
                                                        <i class="ri-edit-line"></i> Edit
                                                    </button>
                                                    <form method="POST" class="d-inline"
                                                        onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                        <input type="hidden" name="action" value="delete_produk">
                                                        <input type="hidden" name="id" value="<?php echo $produk['id_produk']; ?>">
                                                        <button type="submit" class="btn-delete">
                                                            <i class="ri-delete-bin-line"></i> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });

            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.classList.add('active');

            // Redirect to first page of selected tab (only for artikel and penelitian)
            if (tabName === 'artikel' || tabName === 'penelitian') {
            window.location.href = '?tab=' + tabName + '&page=1';
            } else {
                window.location.href = '?tab=' + tabName;
            }
        }

        // Set active tab based on URL parameter
        window.addEventListener('DOMContentLoaded', function () {
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab') || 'artikel';

            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));

            // Show selected tab
            const tabElement = document.getElementById(tab + '-tab');
            if (tabElement) {
                tabElement.classList.add('active');
            }
            const tabButtons = document.querySelectorAll('.tab');
            tabButtons.forEach(btn => {
                const href = btn.getAttribute('href') || '';
                if (href.includes('tab=' + tab)) {
                    btn.classList.add('active');
                }
            });
        });

        function editArtikel(artikel) {
            document.getElementById('edit_artikel_id').value = artikel.id_artikel;
            document.getElementById('edit_artikel_judul').value = artikel.judul || '';
            document.getElementById('edit_artikel_tahun').value = artikel.tahun || '';
            document.getElementById('edit_artikel_konten').value = artikel.konten || '';
            document.getElementById('edit_artikel_id_penelitian').value = artikel.id_penelitian || '';
            if (document.getElementById('edit_artikel_nim')) {
                document.getElementById('edit_artikel_nim').value = artikel.nim || '';
            }
            if (document.getElementById('edit_artikel_id_member')) {
                document.getElementById('edit_artikel_id_member').value = artikel.id_member || '';
            }
            if (document.getElementById('edit_artikel_id_produk')) {
                document.getElementById('edit_artikel_id_produk').value = artikel.id_produk || '';
            }
            if (document.getElementById('edit_artikel_id_mitra')) {
                document.getElementById('edit_artikel_id_mitra').value = artikel.id_mitra || '';
            }

            document.getElementById('edit-artikel-section').classList.add('active');
            document.querySelector('#artikel-tab .form-section:not(.edit-form-section)').style.display = 'none';

            document.getElementById('edit-artikel-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function cancelEditArtikel() {
            document.getElementById('edit-artikel-section').classList.remove('active');
            document.querySelector('#artikel-tab .form-section:not(.edit-form-section)').style.display = 'block';
            document.getElementById('edit-artikel-form').reset();
        }

        function editPenelitian(penelitian) {
            document.getElementById('edit_penelitian_id').value = penelitian.id_penelitian;
            document.getElementById('edit_penelitian_judul').value = penelitian.judul || '';
            document.getElementById('edit_penelitian_tahun').value = penelitian.tahun || '';
            document.getElementById('edit_penelitian_deskripsi').value = penelitian.deskripsi || '';
            document.getElementById('edit_penelitian_tgl_mulai').value = penelitian.tgl_mulai || '';
            document.getElementById('edit_penelitian_tgl_selesai').value = penelitian.tgl_selesai || '';

            document.getElementById('edit-penelitian-section').classList.add('active');
            document.querySelector('#penelitian-tab .form-section:not(.edit-form-section)').style.display = 'none';

            document.getElementById('edit-penelitian-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function cancelEditPenelitian() {
            document.getElementById('edit-penelitian-section').classList.remove('active');
            document.querySelector('#penelitian-tab .form-section:not(.edit-form-section)').style.display = 'block';
            document.getElementById('edit-penelitian-form').reset();
        }

        function editResearchDetail(researchDetail) {
            document.getElementById('edit_research_detail_id').value = researchDetail.id_fp;
            document.getElementById('edit_research_detail_title').value = researchDetail.title || '';
            document.getElementById('edit_research_detail_deskripsi').value = researchDetail.deskripsi || '';
            const detailTextarea = document.getElementById('edit_research_detail_detail');
            detailTextarea.value = researchDetail.detail || '';
            // Update character counter
            updateCharCounter('edit_research_detail_detail', 'edit_detail_count');

            document.getElementById('edit-research-detail-section').classList.add('active');
            document.querySelector('#research_detail-tab .form-section:not(.edit-form-section)').style.display = 'none';

            document.getElementById('edit-research-detail-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function cancelEditResearchDetail() {
            document.getElementById('edit-research-detail-section').classList.remove('active');
            document.querySelector('#research_detail-tab .form-section:not(.edit-form-section)').style.display = 'block';
            document.getElementById('edit-research-detail-form').reset();
            // Reset counters
            updateCharCounter('edit_research_detail_detail', 'edit_detail_count');
        }

        function editProduct(product) {
            // Populate edit form - handle null/undefined to prevent "undefined" text
            document.getElementById('edit_product_id').value = product.id_produk || '';
            document.getElementById('edit_product_nama').value = product.nama_produk || '';
            document.getElementById('edit_product_deskripsi').value = product.deskripsi || '';
            document.getElementById('edit_product_gambar').value = product.gambar || '';
            document.getElementById('edit_product_current_gambar').value = product.gambar || '';
            document.getElementById('edit_product_try').value = product.try || '';
            
            // Show current image preview if exists
            const previewDiv = document.getElementById('edit_product_image_preview');
            const previewImg = document.getElementById('edit_product_image_preview_img');
            if (product.gambar) {
                previewImg.src = product.gambar;
                previewDiv.style.display = 'block';
            } else {
                previewDiv.style.display = 'none';
            }

            document.querySelector('#product-tab .form-section:not(.edit-form-section)').style.display = 'none';
            document.getElementById('edit-product-section').style.display = 'block';
            document.getElementById('edit-product-section').classList.add('active');
            document.getElementById('edit-product-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function cancelEditProduct() {
            document.querySelector('#product-tab .form-section:not(.edit-form-section)').style.display = 'block';
            document.getElementById('edit-product-section').style.display = 'none';
            document.getElementById('edit-product-section').classList.remove('active');
            document.getElementById('edit-product-form').reset();
        }

        // Character counter function
        function updateCharCounter(textareaId, counterId) {
            const textarea = document.getElementById(textareaId);
            const counter = document.getElementById(counterId);
            if (textarea && counter) {
                const length = textarea.value.length;
                counter.textContent = length.toLocaleString();
            }
        }

        // Initialize character counters
        document.addEventListener('DOMContentLoaded', function() {
            // Edit form counter
            const editDetailTextarea = document.getElementById('edit_research_detail_detail');
            if (editDetailTextarea) {
                editDetailTextarea.addEventListener('input', function() {
                    updateCharCounter('edit_research_detail_detail', 'edit_detail_count');
                });
                // Initial count
                updateCharCounter('edit_research_detail_detail', 'edit_detail_count');
            }

            // Add form counter
            const addDetailTextarea = document.getElementById('add_research_detail_detail');
            if (addDetailTextarea) {
                addDetailTextarea.addEventListener('input', function() {
                    updateCharCounter('add_research_detail_detail', 'add_detail_count');
                });
                // Initial count
                updateCharCounter('add_research_detail_detail', 'add_detail_count');
            }
        });
    </script>
</body>

</html>