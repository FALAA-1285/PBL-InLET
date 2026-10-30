<?php
require_once '../config/auth.php';
requireAdmin();

$conn = getDBConnection();
$message = '';
$message_type = '';

// Include procedures
require_once __DIR__ . '/../config/procedures.php';

// Check if request_peminjaman table exists (check once)
$hasRequestTable = false;
try {
    $check_table = $conn->query("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'request_peminjaman')");
    $hasRequestTable = $check_table->fetchColumn();
} catch (PDOException $e) {
    $hasRequestTable = false;
}

// Get filter parameters early (before POST handling for redirects)
$filter_type = $_GET['filter_type'] ?? $_POST['filter_type'] ?? '';
$filter_search = $_GET['filter_search'] ?? $_POST['filter_search'] ?? '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $admin_id = $_SESSION['id_admin'] ?? null;

    if ($action === 'approve_request') {
        $id_request = intval($_POST['id_request'] ?? 0);
        
        if ($id_request <= 0) {
            $message = 'Invalid request ID!';
            $message_type = 'error';
        } elseif (!$hasRequestTable) {
            // If table doesn't exist, peminjaman already created with status 'dipinjam'
            // Mark as approved by updating keterangan
            try {
                $check_stmt = $conn->prepare("SELECT id_peminjaman, status, keterangan FROM peminjaman WHERE id_peminjaman = :id");
                $check_stmt->execute(['id' => $id_request]);
                $peminjaman = $check_stmt->fetch();
                
                if ($peminjaman) {
                    if ($peminjaman['status'] === 'dipinjam') {
                        // Mark as approved by adding note to keterangan
                        $keterangan_baru = ($peminjaman['keterangan'] ? $peminjaman['keterangan'] . "\n" : '') . '[APPROVED]';
                        $update_stmt = $conn->prepare("UPDATE peminjaman SET keterangan = :keterangan WHERE id_peminjaman = :id");
                        $update_stmt->execute([
                            'id' => $id_request,
                            'keterangan' => $keterangan_baru
                        ]);
                        
                        // Redirect to refresh the page
                        header('Location: peminjaman.php?approved=1&filter_type=' . urlencode($filter_type) . '&filter_search=' . urlencode($filter_search));
                        exit;
                    } else {
                        $message = 'This loan is no longer pending.';
            $message_type = 'error';
                    }
                } else {
                    $message = 'Loan record not found!';
                    $message_type = 'error';
                }
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $message_type = 'error';
            }
        } else {
            $result = callApproveRequest($id_request, $admin_id);
            
            if ($result['success']) {
                // Redirect to refresh the page and show updated data
                header('Location: peminjaman.php?approved=1&filter_type=' . urlencode($filter_type) . '&filter_search=' . urlencode($filter_search));
                exit;
            } else {
                $message = $result['message'];
                $message_type = 'error';
            }
        }
    } elseif ($action === 'reject_request') {
        $id_request = intval($_POST['id_request'] ?? 0);
        $alasan_reject = trim($_POST['alasan_reject'] ?? '');
        
        if ($id_request <= 0) {
            $message = 'Invalid request ID!';
            $message_type = 'error';
        } elseif (empty($alasan_reject)) {
            $message = 'Rejection reason must be filled!';
            $message_type = 'error';
        } elseif (!$hasRequestTable) {
            // If table doesn't exist, update peminjaman status to 'ditolak' or delete it
            try {
                // Try to update status to 'ditolak'
                $stmt = $conn->prepare("UPDATE peminjaman SET status = 'ditolak', keterangan = COALESCE(keterangan || E'\n', '') || 'Rejected: ' || :alasan WHERE id_peminjaman = :id AND status = 'dipinjam'");
                $stmt->execute([
                    'id' => $id_request,
                    'alasan' => $alasan_reject
                ]);
                
                if ($stmt->rowCount() > 0) {
                    // Redirect to refresh the page
                    header('Location: peminjaman.php?rejected=1&filter_type=' . urlencode($filter_type) . '&filter_search=' . urlencode($filter_search));
                    exit;
                } else {
                    // If update didn't work, try deleting the peminjaman record
                    try {
                        $stmt = $conn->prepare("DELETE FROM peminjaman WHERE id_peminjaman = :id AND status = 'dipinjam'");
                        $stmt->execute(['id' => $id_request]);
                        
                        if ($stmt->rowCount() > 0) {
                            // Redirect to refresh the page
                            header('Location: peminjaman.php?rejected=1&filter_type=' . urlencode($filter_type) . '&filter_search=' . urlencode($filter_search));
                            exit;
                        } else {
                            $message = 'Loan record not found or already processed.';
                            $message_type = 'error';
                        }
                    } catch (PDOException $e2) {
                        // If delete fails, just update keterangan
                    $stmt = $conn->prepare("UPDATE peminjaman SET keterangan = COALESCE(keterangan || E'\n', '') || 'Rejected: ' || :alasan WHERE id_peminjaman = :id");
                    $stmt->execute([
                        'id' => $id_request,
                        'alasan' => $alasan_reject
                    ]);
                        // Redirect to refresh the page
                        header('Location: peminjaman.php?rejected=1&filter_type=' . urlencode($filter_type) . '&filter_search=' . urlencode($filter_search));
                        exit;
                    }
                }
            } catch (PDOException $e) {
                // If 'ditolak' status doesn't work, try deleting
                try {
                    $stmt = $conn->prepare("DELETE FROM peminjaman WHERE id_peminjaman = :id AND status = 'dipinjam'");
                    $stmt->execute(['id' => $id_request]);
                    
                    if ($stmt->rowCount() > 0) {
                        // Redirect to refresh the page
                        header('Location: peminjaman.php?rejected=1&filter_type=' . urlencode($filter_type) . '&filter_search=' . urlencode($filter_search));
                        exit;
                    } else {
                        $message = 'Loan record not found or already processed.';
                        $message_type = 'error';
                    }
                } catch (PDOException $e2) {
                    $message = 'Error: ' . $e2->getMessage();
                    $message_type = 'error';
                }
            }
        } else {
            $result = callRejectRequest($id_request, $admin_id, $alasan_reject);
            
            if ($result['success']) {
                // Redirect to refresh the page and show updated data
                header('Location: peminjaman.php?rejected=1&filter_type=' . urlencode($filter_type) . '&filter_search=' . urlencode($filter_search));
                exit;
            } else {
                $message = $result['message'];
                $message_type = 'error';
            }
        }
    } elseif ($action === 'return_peminjaman') {
        $id_peminjaman = intval($_POST['id_peminjaman'] ?? 0);
        $kondisi_barang = trim($_POST['kondisi_barang'] ?? 'baik');
        $catatan_return = trim($_POST['catatan_return'] ?? '');
        
        if ($id_peminjaman <= 0) {
            $message = 'Invalid loan ID!';
            $message_type = 'error';
        } else {
            $result = callReturnPeminjaman($id_peminjaman, $admin_id, $kondisi_barang, $catatan_return ?: null);
            
            if ($result['success']) {
                $message = $result['message'];
                $message_type = 'success';
                // Redirect to prevent resubmission
                header('Location: peminjaman.php?returned=1&return_filter_type=' . urlencode($return_filter_type ?? '') . '&return_filter_search=' . urlencode($return_filter_search ?? ''));
                exit;
            } else {
                $message = $result['message'];
                $message_type = 'error';
            }
        }
    }
}

// Filter parameters already defined above

// Check for success messages from redirect
if (isset($_GET['approved']) && $_GET['approved'] == '1') {
    $message = 'Request approved successfully!';
    $message_type = 'success';
}

if (isset($_GET['rejected']) && $_GET['rejected'] == '1') {
    $message = 'Request rejected successfully!';
    $message_type = 'success';
}

if (isset($_GET['returned']) && $_GET['returned'] == '1') {
    $message = 'Item returned successfully!';
    $message_type = 'success';
}

// Get pending requests for approval
if ($hasRequestTable) {
    // Use request_peminjaman table if it exists
    $query = "
        SELECT
            r.id_request,
            r.nama_peminjam,
            r.tanggal_pinjam,
            r.waktu_pinjam,
            r.waktu_kembali,
            r.keterangan,
            r.status,
            r.created_at,
            CASE
                WHEN r.id_ruang IS NOT NULL THEN 'ruang'
                WHEN r.id_alat IS NOT NULL AND r.id_alat > 0 THEN 'alat'
                ELSE 'unknown'
            END as type,
            COALESCE(alat.nama_alat, ruang.nama_ruang) as item_name,
            alat.deskripsi as alat_deskripsi,
            r.id_alat,
            r.id_ruang
        FROM request_peminjaman r
        LEFT JOIN alat_lab alat ON r.id_alat = alat.id_alat_lab
        LEFT JOIN ruang_lab ruang ON r.id_ruang = ruang.id_ruang_lab
        WHERE r.status = 'pending'
    ";
} else {
    // Fallback: Use peminjaman table with recent records (created in last 24 hours) as pending requests
    // This is a workaround since request_peminjaman table doesn't exist
    $query = "
        SELECT
            p.id_peminjaman as id_request,
            p.nama_peminjam,
            p.tanggal_pinjam,
            p.waktu_pinjam,
            p.waktu_kembali,
            p.keterangan,
            p.status,
            p.created_at,
            CASE
                WHEN p.id_ruang IS NOT NULL THEN 'ruang'
                WHEN p.id_alat IS NOT NULL AND p.id_alat > 0 THEN 'alat'
                ELSE 'unknown'
            END as type,
            COALESCE(alat.nama_alat, ruang.nama_ruang) as item_name,
            alat.deskripsi as alat_deskripsi,
            p.id_alat,
            p.id_ruang
        FROM peminjaman p
        LEFT JOIN alat_lab alat ON p.id_alat = alat.id_alat_lab
        LEFT JOIN ruang_lab ruang ON p.id_ruang = ruang.id_ruang_lab
        WHERE p.status = 'dipinjam' 
        AND p.created_at >= NOW() - INTERVAL '24 hours'
        AND (p.keterangan IS NULL OR p.keterangan NOT LIKE '%[APPROVED]%')
    ";
}

// Apply filters
if (!empty($filter_type)) {
    if ($filter_type === 'alat') {
        $query .= " AND " . ($hasRequestTable ? "r.id_alat" : "p.id_alat") . " > 0";
    } elseif ($filter_type === 'ruang') {
        $query .= " AND " . ($hasRequestTable ? "r.id_ruang" : "p.id_ruang") . " IS NOT NULL";
    }
}

if (!empty($filter_search)) {
    $query .= " AND (COALESCE(alat.nama_alat, ruang.nama_ruang) ILIKE :filter_search)";
}

$query .= " ORDER BY " . ($hasRequestTable ? "r.created_at" : "p.created_at") . " DESC";
if (!$hasRequestTable) {
    $query .= " LIMIT 50";
}

$stmt = $conn->prepare($query);
$params = [];
if (!empty($filter_search)) {
    $params['filter_search'] = '%' . $filter_search . '%';
}
if (!empty($params)) {
    $stmt->execute($params);
} else {
$stmt->execute();
}
$request_list = $stmt->fetchAll();

// Get active loans (dipinjam) for return section
// Show only approved items (both alat and ruang need approval)
$return_query = "
    SELECT
        p.id_peminjaman,
        p.nama_peminjam,
        p.tanggal_pinjam,
        p.tanggal_kembali,
        p.waktu_pinjam,
        p.waktu_kembali,
        p.keterangan,
        p.status,
        p.created_at,
        CASE
            WHEN p.id_ruang IS NOT NULL THEN 'ruang'
            WHEN p.id_alat IS NOT NULL AND p.id_alat > 0 THEN 'alat'
            ELSE 'unknown'
        END as type,
        COALESCE(alat.nama_alat, ruang.nama_ruang) as item_name,
        p.id_alat,
        p.id_ruang
    FROM peminjaman p
    LEFT JOIN alat_lab alat ON p.id_alat = alat.id_alat_lab
    LEFT JOIN ruang_lab ruang ON p.id_ruang = ruang.id_ruang_lab
    WHERE p.status = 'dipinjam'
    AND (p.keterangan IS NOT NULL AND p.keterangan LIKE '%[APPROVED]%') -- Only approved items (both alat and ruang)
";

// Apply filters for return section
$return_filter_type = $_GET['return_filter_type'] ?? '';
$return_filter_search = $_GET['return_filter_search'] ?? ''; // Search by item name

if (!empty($return_filter_type)) {
    if ($return_filter_type === 'alat') {
        $return_query .= " AND p.id_alat > 0";
    } elseif ($return_filter_type === 'ruang') {
        $return_query .= " AND p.id_ruang IS NOT NULL";
    }
}

if (!empty($return_filter_search)) {
    $return_query .= " AND (COALESCE(alat.nama_alat, ruang.nama_ruang) ILIKE :return_filter_search)";
}

$return_query .= " ORDER BY p.tanggal_pinjam DESC";

$return_stmt = $conn->prepare($return_query);
$return_params = [];
if (!empty($return_filter_search)) {
    $return_params['return_filter_search'] = '%' . $return_filter_search . '%';
}
if (!empty($return_params)) {
    $return_stmt->execute($return_params);
} else {
    $return_stmt->execute();
}
$return_list = $return_stmt->fetchAll();

// Get history - combine rejected and returned items
$history_filter_status = $_GET['history_filter_status'] ?? 'all'; // 'all', 'rejected', 'returned'
$history_filter_type = $_GET['history_filter_type'] ?? '';
$history_filter_search = $_GET['history_filter_search'] ?? '';

// Build history query parts
$rejected_where = [];
$returned_where = [];

// Status filter
if ($history_filter_status === 'rejected') {
    // Only show rejected
} elseif ($history_filter_status === 'returned') {
    // Only show returned
} else {
    // Show all (both rejected and returned)
}

// Type filter
if (!empty($history_filter_type)) {
    if ($history_filter_type === 'alat') {
        $rejected_where[] = ($hasRequestTable ? "r.id_alat" : "p.id_alat") . " > 0";
        $returned_where[] = "p.id_alat > 0";
    } elseif ($history_filter_type === 'ruang') {
        $rejected_where[] = ($hasRequestTable ? "r.id_ruang" : "p.id_ruang") . " IS NOT NULL";
        $returned_where[] = "p.id_ruang IS NOT NULL";
    }
}

// Search filter
if (!empty($history_filter_search)) {
    $rejected_where[] = "(COALESCE(alat.nama_alat, ruang.nama_ruang) ILIKE :history_filter_search)";
    $returned_where[] = "(COALESCE(alat2.nama_alat, ruang2.nama_ruang) ILIKE :history_filter_search)";
}

// Build UNION query
$history_query_parts = [];

// Rejected part
if ($history_filter_status === 'all' || $history_filter_status === 'rejected') {
    if ($hasRequestTable) {
        $rejected_query = "
            SELECT
                r.id_request as id_peminjaman,
                r.nama_peminjam,
                r.tanggal_pinjam,
                NULL as tanggal_kembali,
                r.waktu_pinjam,
                r.waktu_kembali,
                r.keterangan,
                'rejected' as history_status,
                r.created_at,
                CASE
                    WHEN r.id_ruang IS NOT NULL THEN 'ruang'
                    WHEN r.id_alat IS NOT NULL AND r.id_alat > 0 THEN 'alat'
                    ELSE 'unknown'
                END as type,
                COALESCE(alat.nama_alat, ruang.nama_ruang) as item_name,
                r.id_alat,
                r.id_ruang
            FROM request_peminjaman r
            LEFT JOIN alat_lab alat ON r.id_alat = alat.id_alat_lab
            LEFT JOIN ruang_lab ruang ON r.id_ruang = ruang.id_ruang_lab
            WHERE r.status = 'rejected'
        ";
        if (!empty($rejected_where)) {
            $rejected_query .= " AND " . implode(" AND ", $rejected_where);
        }
        $history_query_parts[] = $rejected_query;
    } else {
        $rejected_query = "
            SELECT
                p.id_peminjaman,
                p.nama_peminjam,
                p.tanggal_pinjam,
                p.tanggal_kembali,
                p.waktu_pinjam,
                p.waktu_kembali,
                p.keterangan,
                'rejected' as history_status,
                p.created_at,
                CASE
                    WHEN p.id_ruang IS NOT NULL THEN 'ruang'
                    WHEN p.id_alat IS NOT NULL AND p.id_alat > 0 THEN 'alat'
                    ELSE 'unknown'
                END as type,
                COALESCE(alat.nama_alat, ruang.nama_ruang) as item_name,
                p.id_alat,
                p.id_ruang
            FROM peminjaman p
            LEFT JOIN alat_lab alat ON p.id_alat = alat.id_alat_lab
            LEFT JOIN ruang_lab ruang ON p.id_ruang = ruang.id_ruang_lab
            WHERE (p.status = 'ditolak' OR p.keterangan LIKE '%Rejected:%')
        ";
        if (!empty($rejected_where)) {
            $rejected_query .= " AND " . implode(" AND ", $rejected_where);
        }
        $history_query_parts[] = $rejected_query;
    }
}

// Returned part
if ($history_filter_status === 'all' || $history_filter_status === 'returned') {
    $returned_query = "
        SELECT
            p.id_peminjaman,
            p.nama_peminjam,
            p.tanggal_pinjam,
            p.tanggal_kembali,
            p.waktu_pinjam,
            p.waktu_kembali,
            p.keterangan,
            'returned' as history_status,
            p.created_at,
            CASE
                WHEN p.id_ruang IS NOT NULL THEN 'ruang'
                WHEN p.id_alat IS NOT NULL AND p.id_alat > 0 THEN 'alat'
                ELSE 'unknown'
            END as type,
            COALESCE(alat2.nama_alat, ruang2.nama_ruang) as item_name,
            p.id_alat,
            p.id_ruang
        FROM peminjaman p
        LEFT JOIN alat_lab alat2 ON p.id_alat = alat2.id_alat_lab
        LEFT JOIN ruang_lab ruang2 ON p.id_ruang = ruang2.id_ruang_lab
        WHERE p.status = 'dikembalikan'
    ";
    if (!empty($returned_where)) {
        $returned_query .= " AND " . implode(" AND ", $returned_where);
    }
    $history_query_parts[] = $returned_query;
}

// Combine with UNION ALL
if (empty($history_query_parts)) {
    // No history to show
    $history_query = "SELECT NULL as id_peminjaman, NULL as nama_peminjam, NULL as tanggal_pinjam, NULL as tanggal_kembali, NULL as waktu_pinjam, NULL as waktu_kembali, NULL as keterangan, NULL as history_status, NULL as created_at, NULL as type, NULL as item_name, NULL as id_alat, NULL as id_ruang WHERE 1=0";
} elseif (count($history_query_parts) > 1) {
    $history_query = "SELECT * FROM ((" . implode(") UNION ALL (", $history_query_parts) . ")) AS history_combined ORDER BY created_at DESC";
} else {
    $history_query = $history_query_parts[0] . " ORDER BY created_at DESC";
}

// Pagination for history
$history_items_per_page = 10;
$history_current_page = isset($_GET['history_page']) ? max(1, intval($_GET['history_page'])) : 1;
$history_offset = ($history_current_page - 1) * $history_items_per_page;

// Get total count for history - count separately and sum
$history_total_items = 0;

// Rejected count
if ($history_filter_status === 'all' || $history_filter_status === 'rejected') {
    if ($hasRequestTable) {
        $rejected_count_query = "
            SELECT COUNT(*)
            FROM request_peminjaman r
            LEFT JOIN alat_lab alat ON r.id_alat = alat.id_alat_lab
            LEFT JOIN ruang_lab ruang ON r.id_ruang = ruang.id_ruang_lab
            WHERE r.status = 'rejected'
        ";
        if (!empty($rejected_where)) {
            $rejected_count_query .= " AND " . implode(" AND ", $rejected_where);
        }
        $rejected_count_stmt = $conn->prepare($rejected_count_query);
        $rejected_count_params = [];
        if (!empty($history_filter_search)) {
            $rejected_count_params['history_filter_search'] = '%' . $history_filter_search . '%';
        }
        if (!empty($rejected_count_params)) {
            $rejected_count_stmt->execute($rejected_count_params);
        } else {
            $rejected_count_stmt->execute();
        }
        $history_total_items += (int) $rejected_count_stmt->fetchColumn();
    } else {
        $rejected_count_query = "
            SELECT COUNT(*)
            FROM peminjaman p
            LEFT JOIN alat_lab alat ON p.id_alat = alat.id_alat_lab
            LEFT JOIN ruang_lab ruang ON p.id_ruang = ruang.id_ruang_lab
            WHERE (p.status = 'ditolak' OR p.keterangan LIKE '%Rejected:%')
        ";
        if (!empty($rejected_where)) {
            $rejected_count_query .= " AND " . implode(" AND ", $rejected_where);
        }
        $rejected_count_stmt = $conn->prepare($rejected_count_query);
        $rejected_count_params = [];
        if (!empty($history_filter_search)) {
            $rejected_count_params['history_filter_search'] = '%' . $history_filter_search . '%';
        }
        if (!empty($rejected_count_params)) {
            $rejected_count_stmt->execute($rejected_count_params);
        } else {
            $rejected_count_stmt->execute();
        }
        $history_total_items += (int) $rejected_count_stmt->fetchColumn();
    }
}

// Returned count
if ($history_filter_status === 'all' || $history_filter_status === 'returned') {
    $returned_count_query = "
        SELECT COUNT(*)
        FROM peminjaman p
        LEFT JOIN alat_lab alat2 ON p.id_alat = alat2.id_alat_lab
        LEFT JOIN ruang_lab ruang2 ON p.id_ruang = ruang2.id_ruang_lab
        WHERE p.status = 'dikembalikan'
    ";
    if (!empty($returned_where)) {
        $returned_count_query .= " AND " . implode(" AND ", $returned_where);
    }
    $returned_count_stmt = $conn->prepare($returned_count_query);
    $returned_count_params = [];
    if (!empty($history_filter_search)) {
        $returned_count_params['history_filter_search'] = '%' . $history_filter_search . '%';
    }
    if (!empty($returned_count_params)) {
        $returned_count_stmt->execute($returned_count_params);
    } else {
        $returned_count_stmt->execute();
    }
    $history_total_items += (int) $returned_count_stmt->fetchColumn();
}
$history_total_pages = (int) ceil($history_total_items / max(1, $history_items_per_page));

// Add LIMIT and OFFSET to history query
$history_query .= " LIMIT :history_limit OFFSET :history_offset";

$history_stmt = $conn->prepare($history_query);
$history_params = [];
if (!empty($history_filter_search)) {
    $history_params['history_filter_search'] = '%' . $history_filter_search . '%';
}
$history_params['history_limit'] = $history_items_per_page;
$history_params['history_offset'] = $history_offset;

foreach ($history_params as $key => $value) {
    if ($key === 'history_limit' || $key === 'history_offset') {
        $history_stmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
    } else {
        $history_stmt->bindValue(':' . $key, $value);
    }
}
$history_stmt->execute();
$history_list = $history_stmt->fetchAll();

// Get all alat and ruang for filter dropdowns
$alat_list = $conn->query("SELECT id_alat_lab, nama_alat FROM alat_lab ORDER BY nama_alat")->fetchAll();
$ruang_list = $conn->query("SELECT id_ruang_lab, nama_ruang FROM ruang_lab ORDER BY nama_ruang")->fetchAll();

require __DIR__ . '/../views/admin/peminjaman.php';
