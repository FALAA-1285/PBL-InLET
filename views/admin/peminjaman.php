<?php
/**
 * Variables provided by admin/peminjaman.php
 *
 * @var string $message
 * @var string $message_type
 * @var array $request_list
 * @var string $filter_type
 * @var string $filter_search
 * @var array $return_list
 * @var string $return_filter_type
 * @var string $return_filter_search
 * @var int $history_total_items
 * @var string $history_filter_status
 * @var string $history_filter_type
 * @var string $history_filter_search
 * @var array $history_list
 * @var mixed $matches
 * @var int $history_total_pages
 * @var int $history_current_page
 * @var int $history_offset
 * @var int $history_items_per_page
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Loan - CMS InLET</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/admin/peminjaman.css">
</head>

<body>
    <?php $active_page = 'peminjaman';
    include __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <main class="content">
        <div class="content-inner">
            <div class="cms-content">
                <h1 class="text-primary mb-4"><i class="ri-exchange-line"></i> Borrowing Approval</h1>

                <?php if ($message): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Approval Table -->
                <div class="data-section">
                    <h2>Pending Approval Requests (<?php echo count($request_list); ?>)</h2>

                    <!-- Filters -->
                    <div class="filters" style="margin-bottom: 1.5rem; padding: 1.5rem; background: var(--light); border-radius: 10px;">
                        <h3 style="margin-top: 0; color: var(--primary); margin-bottom: 1rem; font-size: 1rem;">Filters</h3>
                        <form method="GET" class="filter-row">
                            <div class="filter-group">
                                <label>Type</label>
                                <select name="filter_type" class="form-group" style="width: 100%; padding: 0.75rem; border: 2px solid #e2e8f0; border-radius: 10px;">
                                    <option value="">All Types</option>
                                    <option value="alat" <?php echo $filter_type === 'alat' ? 'selected' : ''; ?>>Tool</option>
                                    <option value="ruang" <?php echo $filter_type === 'ruang' ? 'selected' : ''; ?>>Room</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>Check Item Name</label>
                                <input type="text" name="filter_search" class="form-group" 
                                    style="width: 100%; padding: 0.75rem; border: 2px solid #e2e8f0; border-radius: 10px;"
                                    placeholder="Search item/tool/room name..." 
                                    value="<?php echo htmlspecialchars($filter_search); ?>">
                            </div>
                            <div class="filter-group">
                                <button type="submit" class="filter-btn">Apply Filter</button>
                                <a href="peminjaman.php" class="clear-filter">Clear</a>
                            </div>
                        </form>
                    </div>

                    <?php if (empty($request_list)): ?>
                        <p class="muted-gray">No pending approval requests.</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Type</th>
                                        <th>Item</th>
                                        <th>Borrower</th>
                                        <th>Borrow Date</th>
                                        <th>Time</th>
                                        <th>Notes</th>
                                        <th>Request Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($request_list as $request): ?>
                                        <tr>
                                            <td><?php echo $request['id_request']; ?></td>
                                            <td>
                                                <span class="type-badge type-<?php echo $request['type']; ?>">
                                                    <?php echo ucfirst($request['type']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($request['item_name'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($request['nama_peminjam']); ?></td>
                                            <td><?php echo date('d M Y', strtotime($request['tanggal_pinjam'])); ?></td>
                                            <td>
                                                <?php if ($request['waktu_pinjam'] && $request['waktu_kembali']): ?>
                                                    <?php echo substr($request['waktu_pinjam'], 0, 5); ?> - <?php echo substr($request['waktu_kembali'], 0, 5); ?>
                                                <?php else: ?>
                                                    <span class="muted-gray">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($request['keterangan'] ?? '-'); ?></td>
                                            <td><?php echo date('d M Y H:i', strtotime($request['created_at'])); ?></td>
                                            <td>
                                                <button type="button" class="btn-return" onclick="showApproveModal(<?php echo $request['id_request']; ?>)">
                                                        <i class="ri-check-line"></i> Approve
                                                    </button>
                                                <button type="button" class="btn-delete" onclick="showRejectModal(<?php echo $request['id_request']; ?>)">
                                                    <i class="ri-close-line"></i> Reject
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Active Loans for Return -->
                <div class="data-section">
                    <h2>Active Loans (<?php echo count($return_list); ?>)</h2>

                    <!-- Return Filters -->
                    <div class="filters" style="margin-bottom: 1.5rem; padding: 1.5rem; background: var(--light); border-radius: 10px;">
                        <h3 style="margin-top: 0; color: var(--primary); margin-bottom: 1rem; font-size: 1rem;">Return Filters</h3>
                        <form method="GET" class="filter-row">
                            <input type="hidden" name="filter_type" value="<?php echo htmlspecialchars($filter_type); ?>">
                            <input type="hidden" name="filter_search" value="<?php echo htmlspecialchars($filter_search); ?>">
                            <div class="filter-group">
                                <label>Type</label>
                                <select name="return_filter_type" class="form-group" style="width: 100%; padding: 0.75rem; border: 2px solid #e2e8f0; border-radius: 10px;">
                                    <option value="">All Types</option>
                                    <option value="alat" <?php echo $return_filter_type === 'alat' ? 'selected' : ''; ?>>Tool</option>
                                    <option value="ruang" <?php echo $return_filter_type === 'ruang' ? 'selected' : ''; ?>>Room</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>Check Item Name</label>
                                <input type="text" name="return_filter_search" class="form-group" 
                                    style="width: 100%; padding: 0.75rem; border: 2px solid #e2e8f0; border-radius: 10px;"
                                    placeholder="Search item/tool/room name..." 
                                    value="<?php echo htmlspecialchars($return_filter_search); ?>">
                            </div>
                            <div class="filter-group">
                                <button type="submit" class="filter-btn">Apply Filter</button>
                                <a href="peminjaman.php" class="clear-filter">Clear</a>
                            </div>
                        </form>
                    </div>

                    <?php if (empty($return_list)): ?>
                        <p class="muted-gray">No active loans.</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Type</th>
                                        <th>Item</th>
                                        <th>Borrower</th>
                                        <th>Borrow Date</th>
                                        <th>Return Date</th>
                                        <th>Time</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($return_list as $loan): ?>
                                        <tr>
                                            <td><?php echo $loan['id_peminjaman']; ?></td>
                                            <td>
                                                <span class="type-badge type-<?php echo $loan['type']; ?>">
                                                    <?php echo ucfirst($loan['type']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($loan['item_name'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($loan['nama_peminjam']); ?></td>
                                            <td><?php echo date('d M Y', strtotime($loan['tanggal_pinjam'])); ?></td>
                                            <td><?php echo $loan['tanggal_kembali'] ? date('d M Y', strtotime($loan['tanggal_kembali'])) : '-'; ?></td>
                                            <td>
                                                <?php if ($loan['waktu_pinjam'] && $loan['waktu_kembali']): ?>
                                                    <?php echo substr($loan['waktu_pinjam'], 0, 5); ?> - <?php echo substr($loan['waktu_kembali'], 0, 5); ?>
                                                <?php else: ?>
                                                    <span class="muted-gray">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn-return" onclick="showReturnModal(<?php echo $loan['id_peminjaman']; ?>, '<?php echo htmlspecialchars($loan['item_name'] ?? ''); ?>')">
                                                    <i class="ri-arrow-left-line"></i> Return
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- History -->
                <div class="data-section">
                    <h2>History (<?php echo $history_total_items; ?>)</h2>

                    <!-- History Filters -->
                    <div class="filters" style="margin-bottom: 1.5rem; padding: 1.5rem; background: var(--light); border-radius: 10px;">
                        <h3 style="margin-top: 0; color: var(--primary); margin-bottom: 1rem; font-size: 1rem;">History Filters</h3>
                        <form method="GET" class="filter-row">
                            <input type="hidden" name="filter_type" value="<?php echo htmlspecialchars($filter_type); ?>">
                            <input type="hidden" name="filter_search" value="<?php echo htmlspecialchars($filter_search); ?>">
                            <input type="hidden" name="return_filter_type" value="<?php echo htmlspecialchars($return_filter_type); ?>">
                            <input type="hidden" name="return_filter_search" value="<?php echo htmlspecialchars($return_filter_search); ?>">
                            <div class="filter-group">
                                <label>Status</label>
                                <select name="history_filter_status" class="form-group" style="width: 100%; padding: 0.75rem; border: 2px solid #e2e8f0; border-radius: 10px;">
                                    <option value="all" <?php echo $history_filter_status === 'all' ? 'selected' : ''; ?>>All</option>
                                    <option value="rejected" <?php echo $history_filter_status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                    <option value="returned" <?php echo $history_filter_status === 'returned' ? 'selected' : ''; ?>>Returned</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>Type</label>
                                <select name="history_filter_type" class="form-group" style="width: 100%; padding: 0.75rem; border: 2px solid #e2e8f0; border-radius: 10px;">
                                    <option value="">All Types</option>
                                    <option value="alat" <?php echo $history_filter_type === 'alat' ? 'selected' : ''; ?>>Tool</option>
                                    <option value="ruang" <?php echo $history_filter_type === 'ruang' ? 'selected' : ''; ?>>Room</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>Check Item Name</label>
                                <input type="text" name="history_filter_search" class="form-group" 
                                    style="width: 100%; padding: 0.75rem; border: 2px solid #e2e8f0; border-radius: 10px;"
                                    placeholder="Search item/tool/room name..." 
                                    value="<?php echo htmlspecialchars($history_filter_search); ?>">
                            </div>
                            <div class="filter-group">
                                <button type="submit" class="filter-btn">Apply Filter</button>
                                <a href="peminjaman.php" class="clear-filter">Clear</a>
                            </div>
                        </form>
                    </div>

                    <?php if (empty($history_list)): ?>
                        <p class="muted-gray">No history found.</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Status</th>
                                        <th>Type</th>
                                        <th>Item</th>
                                        <th>Borrower</th>
                                        <th>Borrow Date</th>
                                        <th>Return Date</th>
                                        <th>Time</th>
                                        <th>Notes/Reason</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($history_list as $history): ?>
                                        <tr>
                                            <td><?php echo $history['id_peminjaman']; ?></td>
                                            <td>
                                                <?php if ($history['history_status'] === 'rejected'): ?>
                                                    <span class="type-badge" style="background: #ef4444; color: white;">Rejected</span>
                                                <?php elseif ($history['history_status'] === 'returned'): ?>
                                                    <span class="type-badge" style="background: #10b981; color: white;">Returned</span>
                                                <?php else: ?>
                                                    <span class="type-badge"><?php echo ucfirst($history['history_status']); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="type-badge type-<?php echo $history['type']; ?>">
                                                    <?php echo ucfirst($history['type']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($history['item_name'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($history['nama_peminjam']); ?></td>
                                            <td><?php echo date('d M Y', strtotime($history['tanggal_pinjam'])); ?></td>
                                            <td><?php echo $history['tanggal_kembali'] ? date('d M Y', strtotime($history['tanggal_kembali'])) : '-'; ?></td>
                                            <td>
                                                <?php if ($history['waktu_pinjam'] && $history['waktu_kembali']): ?>
                                                    <?php echo substr($history['waktu_pinjam'], 0, 5); ?> - <?php echo substr($history['waktu_kembali'], 0, 5); ?>
                                                <?php else: ?>
                                                    <span class="muted-gray">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $keterangan = $history['keterangan'] ?? '';
                                                if ($history['history_status'] === 'rejected') {
                                                    // Extract rejection reason from keterangan
                                                    if (preg_match('/Rejected:\s*(.+?)(?:\n|$)/i', $keterangan, $matches)) {
                                                        echo htmlspecialchars(trim($matches[1]));
                                                    } elseif (strpos($keterangan, 'Rejected:') !== false) {
                                                        $reason = substr($keterangan, strpos($keterangan, 'Rejected:') + 9);
                                                        echo htmlspecialchars(trim($reason));
                                                    } else {
                                                        echo htmlspecialchars($keterangan ?: '-');
                                                    }
                                                } else {
                                                    echo htmlspecialchars($keterangan ?: '-');
                                                }
                                                ?>
                                            </td>
                                            <td><?php echo date('d M Y H:i', strtotime($history['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination for History -->
                        <?php if ($history_total_pages > 1): ?>
                            <div class="pagination">
                                <?php if ($history_current_page > 1): ?>
                                    <a href="?history_page=<?php echo $history_current_page - 1; ?>&history_filter_status=<?php echo urlencode($history_filter_status); ?>&history_filter_type=<?php echo urlencode($history_filter_type); ?>&history_filter_search=<?php echo urlencode($history_filter_search); ?>&filter_type=<?php echo urlencode($filter_type); ?>&filter_search=<?php echo urlencode($filter_search); ?>&return_filter_type=<?php echo urlencode($return_filter_type); ?>&return_filter_search=<?php echo urlencode($return_filter_search); ?>">&laquo; Previous</a>
                                <?php else: ?>
                                    <span class="disabled">&laquo; Previous</span>
                                <?php endif; ?>
                                
                                <?php
                                $history_start_page = max(1, $history_current_page - 2);
                                $history_end_page = min($history_total_pages, $history_current_page + 2);
                                
                                if ($history_start_page > 1): ?>
                                    <a href="?history_page=1&history_filter_status=<?php echo urlencode($history_filter_status); ?>&history_filter_type=<?php echo urlencode($history_filter_type); ?>&history_filter_search=<?php echo urlencode($history_filter_search); ?>&filter_type=<?php echo urlencode($filter_type); ?>&filter_search=<?php echo urlencode($filter_search); ?>&return_filter_type=<?php echo urlencode($return_filter_type); ?>&return_filter_search=<?php echo urlencode($return_filter_search); ?>">1</a>
                                    <?php if ($history_start_page > 2): ?>
                                        <span>...</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php for ($i = $history_start_page; $i <= $history_end_page; $i++): ?>
                                    <?php if ($i == $history_current_page): ?>
                                        <span class="active"><?php echo $i; ?></span>
                                    <?php else: ?>
                                        <a href="?history_page=<?php echo $i; ?>&history_filter_status=<?php echo urlencode($history_filter_status); ?>&history_filter_type=<?php echo urlencode($history_filter_type); ?>&history_filter_search=<?php echo urlencode($history_filter_search); ?>&filter_type=<?php echo urlencode($filter_type); ?>&filter_search=<?php echo urlencode($filter_search); ?>&return_filter_type=<?php echo urlencode($return_filter_type); ?>&return_filter_search=<?php echo urlencode($return_filter_search); ?>"><?php echo $i; ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                
                                <?php if ($history_end_page < $history_total_pages): ?>
                                    <?php if ($history_end_page < $history_total_pages - 1): ?>
                                        <span>...</span>
                                    <?php endif; ?>
                                    <a href="?history_page=<?php echo $history_total_pages; ?>&history_filter_status=<?php echo urlencode($history_filter_status); ?>&history_filter_type=<?php echo urlencode($history_filter_type); ?>&history_filter_search=<?php echo urlencode($history_filter_search); ?>&filter_type=<?php echo urlencode($filter_type); ?>&filter_search=<?php echo urlencode($filter_search); ?>&return_filter_type=<?php echo urlencode($return_filter_type); ?>&return_filter_search=<?php echo urlencode($return_filter_search); ?>"><?php echo $history_total_pages; ?></a>
                                <?php endif; ?>
                                
                                <?php if ($history_current_page < $history_total_pages): ?>
                                    <a href="?history_page=<?php echo $history_current_page + 1; ?>&history_filter_status=<?php echo urlencode($history_filter_status); ?>&history_filter_type=<?php echo urlencode($history_filter_type); ?>&history_filter_search=<?php echo urlencode($history_filter_search); ?>&filter_type=<?php echo urlencode($filter_type); ?>&filter_search=<?php echo urlencode($filter_search); ?>&return_filter_type=<?php echo urlencode($return_filter_type); ?>&return_filter_search=<?php echo urlencode($return_filter_search); ?>">Next &raquo;</a>
                                <?php else: ?>
                                    <span class="disabled">Next &raquo;</span>
                                <?php endif; ?>
                            </div>
                            <div class="pagination-info">
                                Showing <?php echo ($history_offset + 1); ?> - <?php echo min($history_offset + $history_items_per_page, $history_total_items); ?> of <?php echo $history_total_items; ?> history records
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Approve Modal -->
                <div id="approveModal" class="modal-overlay">
                    <div class="modal-content">
                        <h3>Approve Request</h3>
                        <p>Are you sure you want to approve this request?</p>
                        <form method="POST" id="approveForm">
                            <input type="hidden" name="action" value="approve_request">
                            <input type="hidden" name="id_request" id="approve_id_request">
                            <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
                                <button type="submit" class="btn-return">Approve</button>
                                <button type="button" class="btn-cancel" onclick="closeApproveModal()">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Reject Modal -->
                <div id="rejectModal" class="modal-overlay">
                    <div class="modal-content">
                        <h3>Reject Request</h3>
                        <form method="POST" id="rejectForm">
                            <input type="hidden" name="action" value="reject_request">
                            <input type="hidden" name="id_request" id="reject_id_request">
                            <div class="form-group">
                                <label for="alasan_reject">Rejection Reason *</label>
                                <textarea id="alasan_reject" name="alasan_reject" required placeholder="Please provide a reason for rejection..."></textarea>
                            </div>
                            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                <button type="submit" class="btn-delete">Reject</button>
                                <button type="button" class="btn-cancel" onclick="closeRejectModal()">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Return Modal -->
                <div id="returnModal" class="modal-overlay">
                    <div class="modal-content">
                        <h3>Return Item</h3>
                        <p id="returnItemName" style="margin-bottom: 1rem; color: var(--gray);"></p>
                        <form method="POST" id="returnForm">
                            <input type="hidden" name="action" value="return_peminjaman">
                            <input type="hidden" name="id_peminjaman" id="return_id_peminjaman">
                            <div class="form-group">
                                <label for="kondisi_barang">Item Condition *</label>
                                <select id="kondisi_barang" name="kondisi_barang" required>
                                    <option value="baik">Good</option>
                                    <option value="rusak_ringan">Minor Damage</option>
                                    <option value="rusak_berat">Major Damage</option>
                                    <option value="hilang">Lost</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="catatan_return">Return Notes</label>
                                <textarea id="catatan_return" name="catatan_return" placeholder="Optional notes about the return..."></textarea>
                            </div>
                            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                <button type="submit" class="btn-return">Confirm Return</button>
                                <button type="button" class="btn-cancel" onclick="closeReturnModal()">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>

                <script>
                    function showApproveModal(idRequest) {
                        document.getElementById('approve_id_request').value = idRequest;
                        document.getElementById('approveModal').classList.add('active');
                    }

                    function closeApproveModal() {
                        document.getElementById('approveModal').classList.remove('active');
                    }

                    function showRejectModal(idRequest) {
                        document.getElementById('reject_id_request').value = idRequest;
                        document.getElementById('rejectModal').classList.add('active');
                    }

                    function closeRejectModal() {
                        document.getElementById('rejectModal').classList.remove('active');
                        document.getElementById('rejectForm').reset();
                    }

                    function showReturnModal(idPeminjaman, itemName) {
                        document.getElementById('return_id_peminjaman').value = idPeminjaman;
                        document.getElementById('returnItemName').textContent = 'Item: ' + itemName;
                        document.getElementById('returnModal').classList.add('active');
                    }

                    function closeReturnModal() {
                        document.getElementById('returnModal').classList.remove('active');
                        document.getElementById('returnForm').reset();
                    }

                    // Close modals when clicking outside
                    document.getElementById('approveModal').addEventListener('click', function(e) {
                        if (e.target === this) {
                            closeApproveModal();
                        }
                    });

                    document.getElementById('rejectModal').addEventListener('click', function(e) {
                        if (e.target === this) {
                            closeRejectModal();
                        }
                    });

                    document.getElementById('returnModal').addEventListener('click', function(e) {
                        if (e.target === this) {
                            closeReturnModal();
                        }
                    });
                </script>
            </div>
        </div>
    </main>
</body>

</html>