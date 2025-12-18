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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Loan - CMS InLET</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <style>
        .cms-content {
            max-width: 1200px;
            margin: 0 auto;
            padding-bottom: 4rem;
        }

        .message {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .message.success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .message.error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .filters {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .form-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .form-section h2 {
            color: var(--primary);
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark);
            font-weight: 500;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .btn-submit {
            background: var(--primary);
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .data-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }

        .data-section h2 {
            color: var(--primary);
            margin-bottom: 1.5rem;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: var(--light);
            color: var(--primary);
            font-weight: 600;
        }

        tr:hover {
            background: var(--light);
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-dipinjam {
            background: #fef3c7;
            color: #92400e;
        }

        .status-dikembalikan {
            background: #d1fae5;
            color: #065f46;
        }

        .type-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .type-alat {
            background: #dbeafe;
            color: #1e40af;
        }

        .type-ruang {
            background: #fce7f3;
            color: #be185d;
        }

        .btn-delete {
            background: #ef4444;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .btn-delete:hover {
            background: #dc2626;
        }

        .btn-edit {
            background: #3b82f6;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            margin-right: 0.5rem;
        }

        .btn-edit:hover {
            background: #2563eb;
        }

        .btn-return {
            background: #10b981;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            margin-right: 0.5rem;
        }

        .btn-return:hover {
            background: #059669;
        }

        .edit-form-section {
            display: none;
        }

        .edit-form-section.active {
            display: block;
        }

        .btn-cancel {
            background: #6b7280;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            margin-left: 0.5rem;
        }

        .btn-cancel:hover {
            background: #4b5563;
        }

        .filter-btn {
            background: var(--primary);
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            margin-right: 0.5rem;
        }

        .filter-btn:hover {
            background: var(--primary-dark);
        }

        .clear-filter {
            background: #6b7280;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
        }

        .clear-filter:hover {
            background: #4b5563;
        }

        .btn-approve {
            background: #10b981;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            margin-right: 0.5rem;
        }

        .btn-approve:hover {
            background: #059669;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
            padding: 1rem;
            flex-wrap: wrap;
        }

        .pagination a,
        .pagination span {
            padding: 0.5rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            text-decoration: none;
            color: var(--dark);
            transition: all 0.3s;
            display: inline-block;
            min-width: 40px;
            text-align: center;
        }

        .pagination a:hover {
            background: var(--light);
            border-color: var(--primary);
        }

        .pagination .active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            font-weight: 600;
        }

        .pagination .disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
            background: #f3f4f6;
        }

        .pagination-info {
            text-align: center;
            margin-top: 1rem;
            color: var(--gray);
            font-size: 0.9rem;
            padding: 0.5rem;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-content h3 {
            margin-top: 0;
            color: var(--primary);
            margin-bottom: 1.5rem;
        }

        .filter-row {
            display: flex;
            gap: 1rem;
            align-items: end;
            flex-wrap: wrap;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark);
            font-weight: 500;
        }
    </style>
</head>

<body>
    <?php $active_page = 'peminjaman';
    include __DIR__ . '/partials/sidebar.php'; ?>

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