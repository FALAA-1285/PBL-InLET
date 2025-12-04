<?php
require_once '../config/auth.php';
requireAdmin();

$conn = getDBConnection();
$message = '';
$message_type = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        try {
            // NOTE: Database table and column names (absensi, id_absensi) are kept as they are in the database
            $stmt = $conn->prepare("DELETE FROM absensi WHERE id_absensi = :id");
            $stmt->execute(['id' => $id]);
            $message = 'Attendance record successfully deleted!';
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Pagination setup
$items_per_page = 15;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Filtering setup
$filter = $_GET['filter'] ?? 'all'; // 'all', 'today', 'week', 'month'
$date_filter = '';
if ($filter === 'today') {
    $date_filter = " AND tanggal = CURRENT_DATE";
} elseif ($filter === 'week') {
    $date_filter = " AND tanggal >= CURRENT_DATE - INTERVAL '7 days'";
} elseif ($filter === 'month') {
    $date_filter = " AND tanggal >= CURRENT_DATE - INTERVAL '30 days'";
}

// Get total count (using database column name 'tanggal')
$count_query = "SELECT COUNT(*) FROM absensi WHERE 1=1" . $date_filter;
$stmt = $conn->query($count_query);
$total_items = $stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

// Get attendance data with student info
// NOTE: Database column names are used (a.tanggal, a.waktu_datang, etc.) but aliased to English for the PHP array keys
$query = "SELECT
    a.id_absensi,
    a.nim,
    a.tanggal as date,
    a.waktu_datang as check_in_time,
    a.waktu_pulang as check_out_time,
    a.keterangan as notes,
    m.nama as student_name,
    m.nim as student_id,
    m.status as student_status
FROM absensi a
LEFT JOIN mahasiswa m ON m.nim = a.nim::text
WHERE 1=1" . $date_filter . "
ORDER BY a.tanggal DESC, a.waktu_datang DESC
LIMIT :limit OFFSET :offset";

$stmt = $conn->prepare($query);
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$attendance_list = $stmt->fetchAll();

// Get statistics
$stats = [];
$stats['today'] = $conn->query("SELECT COUNT(*) FROM absensi WHERE tanggal = CURRENT_DATE")->fetchColumn();
$stats['week'] = $conn->query("SELECT COUNT(*) FROM absensi WHERE tanggal >= CURRENT_DATE - INTERVAL '7 days'")->fetchColumn();
$stats['month'] = $conn->query("SELECT COUNT(*) FROM absensi WHERE tanggal >= CURRENT_DATE - INTERVAL '30 days'")->fetchColumn();
$stats['total'] = $conn->query("SELECT COUNT(*) FROM absensi")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Attendance - CMS InLET</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <style>
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .content-header h1 {
            color: var(--primary);
            font-size: 2rem;
            margin: 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.25rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.07);
            transition: 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .stat-card h3 {
            font-size: 0.85rem;
            color: var(--gray);
            margin: 0 0 0.5rem 0;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
        }

        .filter-tabs {
            display: flex;
            gap: 0.5rem;
            background: white;
            padding: 0.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .filter-tab {
            padding: 0.5rem 1.25rem;
            border: none;
            background: transparent;
            color: var(--gray);
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .filter-tab:hover {
            background: var(--light);
            color: var(--primary);
        }

        .filter-tab.active {
            background: var(--primary);
            color: white;
        }

        .table-wrapper {
            background: white;
            border-radius: 18px;
            padding: 0;
            box-shadow: 0 10px 35px rgba(15, 23, 42, 0.08);
            overflow-x: auto;
        }

        .absensi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 1000px;
        }

        .absensi-table thead th {
            background: var(--light);
            padding: 1.1rem;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--gray);
            text-align: left;
        }

        .absensi-table tbody td {
            padding: 1.1rem;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
            background: white;
        }

        .absensi-table tbody tr:hover td {
            background: rgba(79, 70, 229, 0.03);
        }

        .absensi-table tbody tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-badge.check_in {
            background: rgba(16, 185, 129, 0.15);
            color: #047857;
        }

        .status-badge.check_out {
            background: rgba(239, 68, 68, 0.15);
            color: #991b1b;
        }

        .status-badge.complete {
            background: rgba(59, 130, 246, 0.15);
            color: #1e40af;
        }

        .btn-action {
            padding: 0.45rem 0.85rem;
            border: none;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            color: white;
        }

        .btn-delete {
            background: #ef4444;
        }

        .btn-delete:hover {
            background: #dc2626;
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

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .time-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            font-size: 0.9rem;
        }

        .time-info span {
            color: var(--gray);
        }

        .time-info strong {
            color: var(--dark);
        }

        .notes-text {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
            padding: 1rem;
        }

        .pagination a,
        .pagination span {
            padding: 0.5rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            text-decoration: none;
            color: var(--dark);
            transition: all 0.3s;
        }

        .pagination a:hover {
            background: var(--light);
        }

        .pagination span.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .pagination span.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>

<body>
    <?php $active_page = 'attendance';
    include __DIR__ . '/partials/sidebar.php'; ?>

    <main class="content">
        <div class="content-inner">
            <div class="content-header">
                <h1><i class="ri-calendar-check-line"></i> Attendance</h1>
                <div class="filter-tabs">
                    <a href="?filter=all" class="filter-tab <?= $filter === 'all' ? 'active' : ''; ?>">
                        All
                    </a>
                    <a href="?filter=today" class="filter-tab <?= $filter === 'today' ? 'active' : ''; ?>">
                        Today
                    </a>
                    <a href="?filter=week" class="filter-tab <?= $filter === 'week' ? 'active' : ''; ?>">
                        This Week
                    </a>
                    <a href="?filter=month" class="filter-tab <?= $filter === 'month' ? 'active' : ''; ?>">
                        This Month
                    </a>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Today</h3>
                    <div class="stat-number"><?= $stats['today']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>This Week</h3>
                    <div class="stat-number"><?= $stats['week']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>This Month</h3>
                    <div class="stat-number"><?= $stats['month']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total</h3>
                    <div class="stat-number"><?= $stats['total']; ?></div>
                </div>
            </div>

            <?php if (empty($attendance_list)): ?>
                <div class="empty-state">
                    <i class="ri-calendar-line"></i>
                    <h3>No Attendance Data</h3>
                    <p>No attendance data found for the selected period.</p>
                </div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table class="absensi-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th>Date</th>
                                <th>Check-in Time</th>
                                <th>Check-out Time</th>
                                <th>Status</th>
                                <th>Notes</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendance_list as $index => $record): ?>
                                <tr>
                                    <td><?= $offset + $index + 1; ?></td>
                                    <td>
                                        <div>
                                            <strong><?= htmlspecialchars($record['student_name'] ?? 'N/A'); ?></strong>
                                            <?php if ($record['student_id']): ?>
                                                <div class="small text-muted mt-1">
                                                    NIM: <?= htmlspecialchars($record['student_id']); ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($record['student_status']): ?>
                                                <div class="small text-primary mt-1">
                                                    <?= ucfirst($record['student_status']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?= date('d M Y', strtotime($record['date'])); ?>
                                    </td>
                                    <td>
                                        <?php if ($record['check_in_time']): ?>
                                            <div class="time-info">
                                                <strong><?= date('H:i', strtotime($record['check_in_time'])); ?></strong>
                                            </div>
                                        <?php else: ?>
                                            <span class="muted-gray">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($record['check_out_time']): ?>
                                            <div class="time-info">
                                                <strong><?= date('H:i', strtotime($record['check_out_time'])); ?></strong>
                                            </div>
                                        <?php else: ?>
                                            <span class="muted-gray">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $has_check_in = !empty($record['check_in_time']);
                                        $has_check_out = !empty($record['check_out_time']);

                                        if ($has_check_in && $has_check_out) {
                                            echo '<span class="status-badge complete"><i class="ri-check-double-line"></i> Complete</span>';
                                        } elseif ($has_check_in) {
                                            echo '<span class="status-badge check_in"><i class="ri-login-box-line"></i> Checked In</span>';
                                        } elseif ($has_check_out) {
                                            // This case is rare/impossible if check-out requires check-in, but handled for completeness
                                            echo '<span class="status-badge check_out"><i class="ri-logout-box-line"></i> Checked Out</span>';
                                        } else {
                                            echo '<span class="muted-gray">-</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($record['notes']): ?>
                                            <div class="notes-text" title="<?= htmlspecialchars($record['notes']); ?>">
                                                <?= htmlspecialchars($record['notes']); ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="muted-gray">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this attendance record?');"
                                            class="d-inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $record['id_absensi']; ?>">
                                            <button type="submit" class="btn-action btn-delete">
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
                            <a href="?page=<?= $current_page - 1; ?>&filter=<?= $filter; ?>">&laquo; Previous</a>
                        <?php else: ?>
                            <span class="disabled">&laquo; Previous</span>
                        <?php endif; ?>

                        <?php
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);

                        if ($start_page > 1): ?>
                            <a href="?page=1&filter=<?= $filter; ?>">1</a>
                            <?php if ($start_page > 2): ?>
                                <span>...</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <?php if ($i == $current_page): ?>
                                <span class="active"><?= $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?= $i; ?>&filter=<?= $filter; ?>"><?= $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($end_page < $total_pages): ?>
                            <?php if ($end_page < $total_pages - 1): ?>
                                <span>...</span>
                            <?php endif; ?>
                            <a href="?page=<?= $total_pages; ?>&filter=<?= $filter; ?>"><?= $total_pages; ?></a>
                        <?php endif; ?>

                        <?php if ($current_page < $total_pages): ?>
                            <a href="?page=<?= $current_page + 1; ?>&filter=<?= $filter; ?>">Next &raquo;</a>
                        <?php else: ?>
                            <span class="disabled">Next &raquo;</span>
                        <?php endif; ?>
                    </div>
                    <div class="text-center muted-gray mt-3">
                        Showing <?= ($offset + 1); ?> - <?= min($offset + $items_per_page, $total_items); ?> of
                        <?= $total_items; ?> attendance records
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>