<?php
/**
 * Variables provided by service/peminjaman.php
 *
 * @var array $page_info
 * @var string $message
 * @var string $message_type
 * @var array $alat_list
 * @var array $ruang_list_all
 * @var array $bookings_by_date
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_info['title'] ?: 'Tool & Room Loans - InLET'); ?></title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- RemixIcon -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <?php $css_version = file_exists(__DIR__ . '/../../assets/css/style-peminjaman.css') ? filemtime(__DIR__ . '/../../assets/css/style-peminjaman.css') : time(); ?>
    <link rel="stylesheet" href="../assets/css/style-peminjaman.css?v=<?= $css_version ?>">
    <link rel="stylesheet" href="../assets/css/site.css?v=<?= filemtime(__DIR__ . '/../../assets/css/site.css') ?>">
</head>

<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="page-main">
        <!-- Hero Section -->
        <section class="hero d-flex align-items-center" id="home">
            <div class="container text-center text-white">
                <h1 class="display-4 fw-bold"><?= htmlspecialchars($page_info['title'] ?: 'Lab Borrowing Dashboard'); ?></h1>
                <?php if (!empty($page_info['subtitle'])): ?>
                    <p class="lead mt-3"><?= htmlspecialchars($page_info['subtitle']); ?></p>
                <?php else: ?>
                    <p class="lead mt-3">Easily explore available lab equipment and schedule laboratory rooms online.</p>
                <?php endif; ?>
            </div>
        </section>

        <div class="container my-5">
            <!-- Alert / Notification Message -->
            <?php if (!empty($message)): ?>
                <div class="message <?= $message_type === 'success' ? 'success' : 'error' ?>" role="alert">
                    <i class="<?= $message_type === 'success' ? 'ri-checkbox-circle-fill' : 'ri-error-warning-fill' ?>"></i>
                    <div>
                        <strong><?= $message_type === 'success' ? 'Success:' : 'Attention:' ?></strong>
                        <span><?= htmlspecialchars($message) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Navigation Tabs (Pills) -->
            <?php
            $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'alat';
            $is_alat_active = $active_tab !== 'ruang';
            $is_ruang_active = $active_tab === 'ruang';
            ?>
            <div class="d-flex justify-content-center">
                <ul class="nav custom-loan-tabs" id="borrowingTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $is_alat_active ? 'active' : '' ?>" id="alat-tab" data-bs-toggle="tab" data-bs-target="#alat" type="button" role="tab" aria-selected="<?= $is_alat_active ? 'true' : 'false' ?>">
                            <i class="ri-tools-line"></i> Borrow Tools
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $is_ruang_active ? 'active' : '' ?>" id="ruang-tab" data-bs-toggle="tab" data-bs-target="#ruang" type="button" role="tab" aria-selected="<?= $is_ruang_active ? 'true' : 'false' ?>">
                            <i class="ri-door-open-line"></i> Borrow Room
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Tab Content -->
            <div class="tab-content" id="borrowingTabContent">
                <!-- ========================================== -->
                <!-- Tab 1: Borrow Tools                        -->
                <!-- ========================================== -->
                <div class="tab-pane fade <?= $is_alat_active ? 'show active' : '' ?>" id="alat" role="tabpanel" aria-labelledby="alat-tab">
                    
                    <!-- Section Header -->
                    <div class="section-header-modern">
                        <div class="section-title-group">
                            <h2><i class="ri-archive-line"></i> Available Lab Tools</h2>
                            <p>Explore equipment ready for academic research, coursework, and laboratory experiments</p>
                        </div>
                        <span class="count-note">
                            <?= count($alat_list) ?> <?= count($alat_list) === 1 ? 'tool' : 'tools' ?> available
                        </span>
                    </div>

                    <!-- Available Tools Cards -->
                    <div class="row g-4 mb-5">
                        <?php if (empty($alat_list)): ?>
                            <div class="col-12">
                                <div class="empty-state-card">
                                    <i class="ri-tools-line"></i>
                                    <h4>No Tools Available</h4>
                                    <p>Currently all equipment is either borrowed or undergoing maintenance.</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($alat_list as $alat): 
                                $stok_tersedia = (int)$alat['stok_tersedia'];
                                $stock_total = (int)$alat['stock'];
                                $status_class = $stok_tersedia > 5 ? 'available' : ($stok_tersedia > 0 ? 'low' : 'empty');
                            ?>
                                <div class="col-xl-3 col-lg-4 col-md-6">
                                    <div class="tool-card">
                                        <div>
                                            <div class="tool-card-header">
                                                <div class="tool-icon-wrapper">
                                                    <i class="ri-cpu-line"></i>
                                                </div>
                                                <span class="stock-pill <?= $status_class ?>">
                                                    <i class="ri-checkbox-circle-fill"></i>
                                                    <?= $stok_tersedia ?> <?= $stok_tersedia === 1 ? 'unit' : 'units' ?>
                                                </span>
                                            </div>
                                            <h4 class="tool-title"><?= htmlspecialchars($alat['nama_alat']) ?></h4>
                                            <p class="tool-desc"><?= htmlspecialchars($alat['deskripsi'] ?? '') ?></p>
                                        </div>
                                        <div class="tool-card-footer">
                                            <small class="text-muted">
                                                <i class="ri-stack-line"></i> Total: <?= $stock_total ?>
                                            </small>
                                            <button type="button" class="btn-select-tool" onclick="selectTool(<?= $alat['id_alat_lab'] ?>)">
                                                Select <i class="ri-arrow-down-line"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Tool Borrowing Form Card -->
                    <div class="card-surface" id="toolFormSection">
                        <div class="form-header-bar">
                            <i class="ri-draft-line"></i>
                            <div>
                                <h3>Tool Borrowing Form</h3>
                                <p>Fill in the required information to borrow equipment from Lab InLET</p>
                            </div>
                        </div>

                        <form method="POST" id="formPinjamAlat">
                            <input type="hidden" name="action" value="pinjam_alat">

                            <div class="row g-4 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="nama_peminjam_alat">
                                        <i class="ri-user-3-line"></i> Borrower Name <span class="required">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="nama_peminjam" id="nama_peminjam_alat" 
                                        placeholder="Enter your full name" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="select_id_alat">
                                        <i class="ri-tools-line"></i> Select Tool <span class="required">*</span>
                                    </label>
                                    <select class="form-select" name="id_alat" id="select_id_alat" required>
                                        <option value="">-- Choose Lab Tool --</option>
                                        <?php foreach ($alat_list as $alat): ?>
                                            <option value="<?= $alat['id_alat_lab']; ?>" data-stock="<?= $alat['stok_tersedia']; ?>">
                                                <?= htmlspecialchars($alat['nama_alat']); ?> (Available: <?= $alat['stok_tersedia']; ?> unit<?= $alat['stok_tersedia'] > 1 ? 's' : '' ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="tanggal_pinjam_alat">
                                        <i class="ri-calendar-event-line"></i> Borrow Date <span class="required">*</span>
                                    </label>
                                    <input type="date" class="form-control" name="tanggal_pinjam" id="tanggal_pinjam_alat"
                                        value="<?= date('Y-m-d'); ?>" 
                                        min="<?= date('Y-m-d'); ?>"
                                        required>
                                    <div class="form-hint"><i class="ri-information-line"></i> Must be today or a future date</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="tanggal_kembali_alat">
                                        <i class="ri-calendar-check-line"></i> Return Date <span class="required">*</span>
                                    </label>
                                    <input type="date" class="form-control" name="tanggal_kembali" id="tanggal_kembali_alat"
                                        value="<?= date('Y-m-d'); ?>" 
                                        min="<?= date('Y-m-d'); ?>"
                                        required>
                                    <div class="form-hint"><i class="ri-information-line"></i> Must be on or after borrow date</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="jumlahInput">
                                        <i class="ri-numbers-line"></i> Quantity <span class="required">*</span>
                                    </label>
                                    <input type="number" class="form-control" name="jumlah" id="jumlahInput"
                                        min="1" value="1" required>
                                    <div class="form-hint" id="stockInfo">
                                        <i class="ri-information-line"></i> Stock limit will adapt automatically when you choose a tool
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="keterangan_alat">
                                        <i class="ri-sticky-note-line"></i> Purpose / Notes
                                    </label>
                                    <input type="text" class="form-control" name="keterangan" id="keterangan_alat"
                                        placeholder="e.g. Practicum, research, final project (optional)">
                                </div>
                            </div>

                            <button type="submit" class="btn-submit-loan">
                                <i class="ri-send-plane-fill"></i> Submit Tool Loan Request
                            </button>
                        </form>
                    </div>

                </div>

                <!-- ========================================== -->
                <!-- Tab 2: Borrow Room                         -->
                <!-- ========================================== -->
                <div class="tab-pane fade <?= $is_ruang_active ? 'show active' : '' ?>" id="ruang" role="tabpanel" aria-labelledby="ruang-tab">
                    
                    <!-- Available Rooms Status Chips -->
                    <div class="section-header-modern">
                        <div class="section-title-group">
                            <h2><i class="ri-community-line"></i> Laboratory Rooms Status</h2>
                            <p>Real-time room availability status for workshops, meetings, and classes</p>
                        </div>
                    </div>

                    <div class="room-status-grid">
                        <?php if (empty($ruang_list_all)): ?>
                            <div class="col-12">
                                <div class="empty-state-card">
                                    <i class="ri-door-closed-line"></i>
                                    <h4>No Rooms Registered</h4>
                                    <p>No laboratory spaces are currently registered in the system.</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($ruang_list_all as $ruang): 
                                $r_status = strtolower($ruang['status'] ?? 'tersedia');
                                $status_label = $r_status === 'tersedia' ? 'Available' : ($r_status === 'maintenance' ? 'Maintenance' : 'Not Available');
                                $icon_class = $r_status === 'tersedia' ? 'available' : ($r_status === 'maintenance' ? 'maintenance' : 'unavailable');
                                $badge_bg = $r_status === 'tersedia' ? 'success' : ($r_status === 'maintenance' ? 'warning' : 'danger');
                            ?>
                                <div class="room-chip-card">
                                    <div class="room-chip-info">
                                        <div class="room-chip-icon <?= $icon_class ?>">
                                            <i class="ri-door-open-line"></i>
                                        </div>
                                        <div>
                                            <h5 class="room-chip-title"><?= htmlspecialchars($ruang['nama_ruang']); ?></h5>
                                            <span class="room-chip-status text-<?= $badge_bg ?>">
                                                <i class="ri-record-circle-fill me-1"></i><?= $status_label ?>
                                            </span>
                                        </div>
                                    </div>
                                    <span class="badge bg-<?= $badge_bg ?> text-white rounded-pill px-3 py-1">
                                        <?= $status_label ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Room Booking Calendar Section -->
                    <div class="section-header-modern">
                        <div class="section-title-group">
                            <h2><i class="ri-calendar-2-line"></i> Room Booking Schedule</h2>
                            <p>Select any available date on the calendar to quickly prefill your booking form</p>
                        </div>
                    </div>

                    <div class="calendar-card" id="roomCalendarContainer">
                        <?php
                        $current_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
                        $today = new DateTime();
                        $view_date = new DateTime($current_month . '-01');
                        
                        $start_date = clone $view_date;
                        $start_date->modify('first day of this month');
                        $end_date = clone $view_date;
                        $end_date->modify('last day of this month');
                        
                        $today->setTime(0, 0, 0);
                        
                        $current = clone $start_date;
                        $calendar_days = [];
                        while ($current <= $end_date) {
                            $current->setTime(0, 0, 0);
                            $date_str = $current->format('Y-m-d');
                            $is_today = $date_str === $today->format('Y-m-d');
                            $is_past = $current < $today;
                            $is_today_or_past = $current <= $today;
                            
                            $calendar_days[] = [
                                'date' => $date_str,
                                'day' => $current->format('d'),
                                'day_name' => $current->format('D'),
                                'month' => $current->format('M'),
                                'is_today' => $is_today,
                                'is_past' => $is_past,
                                'is_today_or_past' => $is_today_or_past,
                                'bookings' => $bookings_by_date[$date_str] ?? []
                            ];
                            $current->modify('+1 day');
                        }
                        
                        $weeks = [];
                        $week = [];
                        $first_day = clone $start_date;
                        $first_day->modify('first day of this month');
                        $day_of_week = (int)$first_day->format('w'); // 0 = Sunday
                        
                        for ($i = 0; $i < $day_of_week; $i++) {
                            $week[] = null;
                        }
                        
                        foreach ($calendar_days as $day) {
                            $week[] = $day;
                            if (count($week) == 7) {
                                $weeks[] = $week;
                                $week = [];
                            }
                        }
                        
                        if (!empty($week)) {
                            while (count($week) < 7) {
                                $week[] = null;
                            }
                            $weeks[] = $week;
                        }
                        
                        $prev_month = clone $view_date;
                        $prev_month->modify('-1 month');
                        $next_month = clone $view_date;
                        $next_month->modify('+1 month');
                        ?>
                        
                        <!-- Navigation Bar -->
                        <div class="calendar-navigation-bar">
                            <h4 class="calendar-month-title">
                                <i class="ri-calendar-check-line"></i> <?= $view_date->format('F Y'); ?>
                            </h4>
                            <div class="calendar-nav-buttons">
                                <button type="button" class="btn-calendar-nav" onclick="changeMonth('<?= $prev_month->format('Y-m'); ?>')">
                                    <i class="ri-arrow-left-s-line"></i> Previous
                                </button>
                                <?php if ($current_month !== date('Y-m')): ?>
                                    <button type="button" class="btn-calendar-nav" onclick="changeMonth('<?= date('Y-m'); ?>')">
                                        <i class="ri-time-line"></i> Today
                                    </button>
                                <?php endif; ?>
                                <button type="button" class="btn-calendar-nav" onclick="changeMonth('<?= $next_month->format('Y-m'); ?>')">
                                    Next <i class="ri-arrow-right-s-line"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Calendar Grid -->
                        <div class="calendar-grid-modern">
                            <div class="calendar-header-row">
                                <div class="calendar-day-label">Sun</div>
                                <div class="calendar-day-label">Mon</div>
                                <div class="calendar-day-label">Tue</div>
                                <div class="calendar-day-label">Wed</div>
                                <div class="calendar-day-label">Thu</div>
                                <div class="calendar-day-label">Fri</div>
                                <div class="calendar-day-label">Sat</div>
                            </div>
                            
                            <?php foreach ($weeks as $week): ?>
                                <div class="calendar-week-row">
                                    <?php foreach ($week as $day): ?>
                                        <?php if ($day === null): ?>
                                            <div class="calendar-cell empty"></div>
                                        <?php else: 
                                            $cell_classes = ['calendar-cell'];
                                            if ($day['is_today']) $cell_classes[] = 'today';
                                            if ($day['is_past']) $cell_classes[] = 'past';
                                            if (!$day['is_today_or_past']) $cell_classes[] = 'selectable';
                                        ?>
                                            <div class="<?= implode(' ', $cell_classes); ?>" 
                                                 data-date="<?= $day['date']; ?>"
                                                 <?= (!$day['is_today_or_past']) ? 'onclick="selectRoomDate(\'' . $day['date'] . '\')"' : ''; ?>>
                                                
                                                <div class="cell-top-bar">
                                                    <span class="cell-day-num"><?= (int)$day['day']; ?></span>
                                                    <?php if ($day['is_today']): ?>
                                                        <span class="badge-today-pill">Today</span>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="cell-bookings-list">
                                                    <?php if (!empty($day['bookings'])): ?>
                                                        <?php foreach ($day['bookings'] as $booking): ?>
                                                            <div class="booking-pill" 
                                                                 title="<?= htmlspecialchars($booking['nama_peminjam']); ?> (<?= substr($booking['waktu_pinjam'], 0, 5); ?> - <?= substr($booking['waktu_kembali'], 0, 5); ?>)">
                                                                <span class="booking-pill-time">
                                                                    <i class="ri-time-line"></i>
                                                                    <?= substr($booking['waktu_pinjam'], 0, 5); ?>-<?= substr($booking['waktu_kembali'], 0, 5); ?>
                                                                </span>
                                                                <span class="booking-pill-name">
                                                                    <?= htmlspecialchars($booking['nama_peminjam']); ?>
                                                                </span>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php elseif (!$day['is_today_or_past']): ?>
                                                        <div class="cell-available-indicator">
                                                            <i class="ri-checkbox-circle-line"></i> Available
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Calendar Legend Bar -->
                        <div class="calendar-legend-bar">
                            <div class="legend-item">
                                <span class="legend-dot today-dot"></span>
                                <span>Today</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-dot available-dot"></span>
                                <span>Available (Click to Select)</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-dot booked-dot"></span>
                                <span>Booked Session</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-dot past-dot"></span>
                                <span>Past Date (Closed)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Room Borrowing Form Card -->
                    <div class="card-surface" id="roomFormSection">
                        <div class="form-header-bar">
                            <i class="ri-door-lock-line"></i>
                            <div>
                                <h3>Room Borrowing Request Form</h3>
                                <p>Submit a room reservation request for laboratory spaces and meeting rooms</p>
                            </div>
                        </div>

                        <form method="POST" id="formPinjamRuang">
                            <input type="hidden" name="action" value="pinjam_ruang">

                            <div class="row g-4 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="select_id_ruang">
                                        <i class="ri-community-line"></i> Select Room <span class="required">*</span>
                                    </label>
                                    <select class="form-select" name="id_ruang" id="select_id_ruang" required>
                                        <option value="">-- Choose Lab Room --</option>
                                        <?php if (!empty($ruang_list_all)): ?>
                                            <?php foreach ($ruang_list_all as $ruang): ?>
                                                <option value="<?= $ruang['id_ruang_lab']; ?>">
                                                    <?= htmlspecialchars($ruang['nama_ruang']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label" for="nama_peminjam_ruang">
                                        <i class="ri-user-3-line"></i> Borrower Name <span class="required">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="nama_peminjam" id="nama_peminjam_ruang" 
                                        placeholder="Enter your full name or team coordinator" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label" for="tanggal_pinjam_ruang">
                                        <i class="ri-calendar-event-line"></i> Booking Date <span class="required">*</span>
                                    </label>
                                    <?php 
                                    $tomorrow = date('Y-m-d', strtotime('+1 day'));
                                    ?>
                                    <input type="date" class="form-control" name="tanggal_pinjam" id="tanggal_pinjam_ruang"
                                        value="<?= $tomorrow; ?>" 
                                        min="<?= $tomorrow; ?>"
                                        required>
                                    <div class="form-hint"><i class="ri-information-line"></i> Room reservations must be made at least 1 day in advance</div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label" for="waktu_pinjam_ruang">
                                        <i class="ri-time-line"></i> Start Time <span class="required">*</span>
                                    </label>
                                    <input type="time" class="form-control" name="waktu_pinjam" id="waktu_pinjam_ruang" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label" for="waktu_kembali_ruang">
                                        <i class="ri-time-line"></i> End Time <span class="required">*</span>
                                    </label>
                                    <input type="time" class="form-control" name="waktu_kembali" id="waktu_kembali_ruang" required>
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="keterangan_ruang">
                                        <i class="ri-file-text-line"></i> Purpose / Activity Details
                                    </label>
                                    <textarea class="form-control" name="keterangan" id="keterangan_ruang" rows="2"
                                        placeholder="e.g. Research group coordination, software demo, thesis preparation (optional)"></textarea>
                                </div>
                            </div>

                            <button type="submit" class="btn-submit-loan">
                                <i class="ri-send-plane-fill"></i> Submit Room Reservation Request
                            </button>
                        </form>
                    </div>

                    <!-- Guidelines Notice Box -->
                    <div class="guidelines-box">
                        <i class="ri-information-line"></i>
                        <div class="guidelines-content">
                            <h5>Important Guidelines & Etiquette:</h5>
                            <p>Please arrive punctually according to your reserved schedule. Keep all lab stations clean, organized, and ensure equipment power is safely switched off when your session ends. For booking cancellations or adjustments, please inform the lab administrator immediately.</p>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </main>

    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tab switching and preserving URL state
        document.addEventListener('DOMContentLoaded', function () {
            const tabButtons = document.querySelectorAll('#borrowingTabs button[data-bs-toggle="tab"]');
            tabButtons.forEach(button => {
                button.addEventListener('shown.bs.tab', function (e) {
                    const tabId = e.target.getAttribute('data-bs-target').replace('#', '');
                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', tabId);
                    window.history.replaceState({}, '', url.toString());
                });
            });

            // Tool stock dynamic info
            const toolSelect = document.getElementById('select_id_alat');
            const jumlahInput = document.getElementById('jumlahInput');
            const stockInfo = document.getElementById('stockInfo');

            if (toolSelect && jumlahInput && stockInfo) {
                toolSelect.addEventListener('change', function () {
                    const selectedOption = this.options[this.selectedIndex];
                    const stock = parseInt(selectedOption.getAttribute('data-stock') || '0');
                    if (this.value && stock > 0) {
                        stockInfo.innerHTML = `<i class="ri-checkbox-circle-line text-success"></i> <span class="text-success fw-semibold">Available stock: ${stock} unit(s)</span>`;
                        jumlahInput.max = stock;
                        if (parseInt(jumlahInput.value) > stock) {
                            jumlahInput.value = stock;
                        }
                    } else if (this.value) {
                        stockInfo.innerHTML = `<i class="ri-alert-line text-danger"></i> <span class="text-danger fw-semibold">No stock available currently</span>`;
                        jumlahInput.max = 0;
                        jumlahInput.value = 0;
                    } else {
                        stockInfo.innerHTML = `<i class="ri-information-line"></i> Stock limit will adapt automatically when you choose a tool`;
                        jumlahInput.removeAttribute('max');
                    }
                });
            }

            // Tool dates consistency
            const tanggalPinjamAlat = document.getElementById('tanggal_pinjam_alat');
            const tanggalKembaliAlat = document.getElementById('tanggal_kembali_alat');
            if (tanggalPinjamAlat && tanggalKembaliAlat) {
                tanggalPinjamAlat.addEventListener('change', function () {
                    tanggalKembaliAlat.min = this.value;
                    if (tanggalKembaliAlat.value < this.value) {
                        tanggalKembaliAlat.value = this.value;
                    }
                });
            }
        });

        // Quick Select Tool from Card
        function selectTool(id) {
            const select = document.getElementById('select_id_alat');
            if (select) {
                select.value = id;
                select.dispatchEvent(new Event('change'));
            }
            const formSection = document.getElementById('toolFormSection');
            if (formSection) {
                formSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                select.focus();
            }
        }

        // Calendar Month Navigation
        function changeMonth(month) {
            const url = new URL(window.location.href);
            url.searchParams.set('month', month);
            url.searchParams.set('tab', 'ruang');
            window.location.href = url.toString();
        }

        // Click Calendar Date to Prefill Form
        function selectRoomDate(dateStr) {
            const inputDate = document.getElementById('tanggal_pinjam_ruang');
            if (inputDate) {
                inputDate.value = dateStr;
                
                // Highlight active cell visually
                document.querySelectorAll('.calendar-cell').forEach(c => c.classList.remove('selected-date'));
                const targetCell = document.querySelector(`.calendar-cell[data-date="${dateStr}"]`);
                if (targetCell) {
                    targetCell.classList.add('selected-date');
                }

                // Scroll smoothly to form and focus
                const formSection = document.getElementById('roomFormSection');
                if (formSection) {
                    formSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    inputDate.focus();
                }
            }
        }
    </script>
</body>

</html>
