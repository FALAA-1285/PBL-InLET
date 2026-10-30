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
    $date_filter = " AND CAST(tanggal AS DATE) = CAST(CURRENT_DATE AS DATE)";
} elseif ($filter === 'week') {
    $date_filter = " AND CAST(tanggal AS DATE) >= CAST(CURRENT_DATE AS DATE) - INTERVAL '7 days'";
} elseif ($filter === 'month') {
    $date_filter = " AND CAST(tanggal AS DATE) >= CAST(CURRENT_DATE AS DATE) - INTERVAL '30 days'";
}

// Get total count (using database column name 'tanggal')
$count_query = "SELECT COUNT(*) FROM absensi WHERE 1=1" . $date_filter;
$stmt = $conn->query($count_query);
$total_items = $stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

// Get attendance data with student info
// First, check what columns exist in the absensi table
try {
    $check_cols = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'absensi' AND table_schema = 'public'");
    $columns = $check_cols->fetchAll(PDO::FETCH_COLUMN);
    
    // Determine which column to use for student identifier
    // absensi table uses 'nim' column which stores id_mahasiswa value from mahasiswa table
    $student_col = null;
    if (in_array('nim', $columns)) {
        $student_col = 'nim';
    } elseif (in_array('id_mhs', $columns)) {
        $student_col = 'id_mhs';
    } elseif (in_array('id_mahasiswa', $columns)) {
        $student_col = 'id_mahasiswa';
    }
    
    if ($student_col) {
        // Build query with student info if we can identify the student
        $query = "SELECT
            a.id_absensi,
            a." . $student_col . " as student_id,
            a.tanggal as date,
            a.waktu_datang as check_in_time,
            a.waktu_pulang as check_out_time,
            a.keterangan as notes,
            COALESCE((SELECT nama FROM mahasiswa WHERE CAST(id_mahasiswa AS TEXT) = CAST(a." . $student_col . " AS TEXT) LIMIT 1), 'N/A') as student_name,
            COALESCE((SELECT status FROM mahasiswa WHERE CAST(id_mahasiswa AS TEXT) = CAST(a." . $student_col . " AS TEXT) LIMIT 1), '') as student_status
        FROM absensi a
        WHERE 1=1" . $date_filter . "
        ORDER BY a.tanggal DESC, a.waktu_datang DESC
        LIMIT :limit OFFSET :offset";
    } else {
        // Fallback: no student identifier column found
        $query = "SELECT
            a.id_absensi,
            '' as student_id,
            a.tanggal as date,
            a.waktu_datang as check_in_time,
            a.waktu_pulang as check_out_time,
            a.keterangan as notes,
            'N/A' as student_name,
            '' as student_status
        FROM absensi a
        WHERE 1=1" . $date_filter . "
        ORDER BY a.tanggal DESC, a.waktu_datang DESC
        LIMIT :limit OFFSET :offset";
    }
    
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $attendance_list = $stmt->fetchAll();
} catch (PDOException $e) {
    // If everything fails, use the most basic query
    error_log("Error in attendance query: " . $e->getMessage());
    $query_simple = "SELECT
        a.id_absensi,
        '' as student_id,
        a.tanggal as date,
        a.waktu_datang as check_in_time,
        a.waktu_pulang as check_out_time,
        a.keterangan as notes,
        'N/A' as student_name,
        '' as student_status
    FROM absensi a
    WHERE 1=1" . $date_filter . "
    ORDER BY a.tanggal DESC, a.waktu_datang DESC
    LIMIT :limit OFFSET :offset";
    $stmt = $conn->prepare($query_simple);
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $attendance_list = $stmt->fetchAll();
}

// Get statistics
$stats = [];
// Use CAST to ensure date comparison works correctly
$stats['today'] = $conn->query("SELECT COUNT(*) FROM absensi WHERE CAST(tanggal AS DATE) = CAST(CURRENT_DATE AS DATE)")->fetchColumn();
$stats['week'] = $conn->query("SELECT COUNT(*) FROM absensi WHERE CAST(tanggal AS DATE) >= CAST(CURRENT_DATE AS DATE) - INTERVAL '7 days'")->fetchColumn();
$stats['month'] = $conn->query("SELECT COUNT(*) FROM absensi WHERE CAST(tanggal AS DATE) >= CAST(CURRENT_DATE AS DATE) - INTERVAL '30 days'")->fetchColumn();
$stats['total'] = $conn->query("SELECT COUNT(*) FROM absensi")->fetchColumn();

require __DIR__ . '/../views/admin/absensi.php';
