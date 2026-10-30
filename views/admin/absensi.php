<?php
/**
 * Variables provided by admin/absensi.php
 *
 * @var string $filter
 * @var string $message
 * @var string $message_type
 * @var array $stats
 * @var array $attendance_list
 * @var int $offset
 * @var float $total_pages
 * @var int $current_page
 * @var int $items_per_page
 * @var int $total_items
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Attendance - CMS InLET</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/admin/absensi.css">
</head>

<body>
    <?php $active_page = 'attendance';
    include __DIR__ . '/../partials/admin_sidebar.php'; ?>

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
