<?php
require_once '../config/auth.php';
requireAdmin();

$conn = getDBConnection();
$message = '';
$message_type = '';

// Handle filter
$filter_date = $_GET['date'] ?? '';
$filter_mhs = $_GET['mhs'] ?? '';

// Build query
$query = "SELECT a.*, m.nama as nama_mahasiswa, m.status as status_mahasiswa 
          FROM absensi a 
          LEFT JOIN mahasiswa m ON a.id_mahasiswa = m.id_mahasiswa
          WHERE 1=1";
$params = [];

if ($filter_date) {
    $query .= " AND DATE(a.tanggal) = :filter_date";
    $params['filter_date'] = $filter_date;
}

if ($filter_mhs) {
    $query .= " AND a.id_mahasiswa = :filter_mhs";
    $params['filter_mhs'] = $filter_mhs;
}

$query .= " ORDER BY a.tanggal DESC, a.waktu_datang DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$absensi_list = $stmt->fetchAll();

// Get all mahasiswa for filter
$mhs_stmt = $conn->query("SELECT id_mahasiswa, nama FROM mahasiswa ORDER BY nama");
$mahasiswa_list = $mhs_stmt->fetchAll();

// Statistics
$stats_stmt = $conn->query("SELECT 
    COUNT(*) as total_absensi,
    COUNT(DISTINCT id_mahasiswa) as total_mahasiswa,
    COUNT(DISTINCT DATE(tanggal)) as total_hari
    FROM absensi");
$stats = $stats_stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi - CMS InLET</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <style>
        .cms-content {
            max-width: 1400px;
            margin: 0 auto;
            padding-bottom: 4rem;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            text-align: center;
        }
        .stat-card h3 {
            color: var(--gray);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
        }
        .filter-section {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }
        .filter-section h3 {
            color: var(--primary);
            margin-bottom: 1rem;
        }
        .filter-form {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: end;
        }
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark);
            font-weight: 500;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            font-family: inherit;
        }
        .btn-filter {
            background: var(--primary);
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            height: fit-content;
        }
        .btn-filter:hover {
            background: var(--primary-dark);
        }
        .btn-reset {
            background: #6b7280;
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            height: fit-content;
            text-decoration: none;
            display: inline-block;
        }
        .btn-reset:hover {
            background: #4b5563;
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
        th, td {
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
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-magang {
            background: #dbeafe;
            color: #1e40af;
        }
        .badge-skripsi {
            background: #fce7f3;
            color: #9f1239;
        }
        .badge-regular {
            background: #f1f5f9;
            color: #475569;
        }
        .time-badge {
            padding: 0.25rem 0.5rem;
            background: #e0e7ff;
            color: #3730a3;
            border-radius: 6px;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <?php $active_page = 'absensi'; include __DIR__ . '/partials/sidebar.php'; ?>
    
    <main class="content">
        <div class="content-inner">
            <div class="cms-content">
                <h1 style="color: var(--primary); margin-bottom: 2rem;"><i class="ri-calendar-check-line"></i> Data Absensi</h1>

                <!-- Statistics -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Total Absensi</h3>
                        <div class="stat-number"><?php echo $stats['total_absensi'] ?? 0; ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Total Mahasiswa</h3>
                        <div class="stat-number"><?php echo $stats['total_mahasiswa'] ?? 0; ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Total Hari</h3>
                        <div class="stat-number"><?php echo $stats['total_hari'] ?? 0; ?></div>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="filter-section">
                    <h3>Filter Data</h3>
                    <form method="GET" class="filter-form">
                        <div class="form-group">
                            <label for="date">Tanggal</label>
                            <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($filter_date); ?>">
                        </div>
                        <div class="form-group">
                            <label for="mhs">Mahasiswa</label>
                            <select id="mhs" name="mhs">
                                <option value="">Semua Mahasiswa</option>
                                <?php foreach ($mahasiswa_list as $mhs): ?>
                                    <option value="<?php echo $mhs['id_mahasiswa']; ?>" <?php echo ($filter_mhs == $mhs['id_mahasiswa']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($mhs['nama']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn-filter">
                            <i class="ri-search-line"></i> Filter
                        </button>
                        <a href="absensi.php" class="btn-reset">
                            <i class="ri-refresh-line"></i> Reset
                        </a>
                    </form>
                </div>

                <!-- Data List -->
                <div class="data-section">
                    <h2>Daftar Absensi (<?php echo count($absensi_list); ?>)</h2>
                    
                    <?php if (empty($absensi_list)): ?>
                        <p style="color: var(--gray);">Belum ada data absensi.</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Mahasiswa</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                        <th>Waktu Datang</th>
                                        <th>Waktu Pulang</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($absensi_list as $abs): ?>
                                        <tr>
                                            <td><?php echo $abs['id_absensi']; ?></td>
                                            <td><?php echo htmlspecialchars($abs['nama_mahasiswa'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php if ($abs['status_mahasiswa']): ?>
                                                    <span class="badge badge-<?php echo $abs['status_mahasiswa']; ?>">
                                                        <?php echo ucfirst($abs['status_mahasiswa']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('d M Y', strtotime($abs['tanggal'])); ?></td>
                                            <td>
                                                <?php if ($abs['waktu_datang']): ?>
                                                    <span class="time-badge">
                                                        <?php echo date('H:i', strtotime($abs['waktu_datang'])); ?>
                                                    </span>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($abs['waktu_pulang']): ?>
                                                    <span class="time-badge">
                                                        <?php echo date('H:i', strtotime($abs['waktu_pulang'])); ?>
                                                    </span>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($abs['keterangan'] ?? '-'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html>

