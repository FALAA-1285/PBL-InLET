# PERSON 5: Backend Developer - Admin Panel (Part 2) & Services

## 📋 **TUGAS UTAMA**

### **1. Admin Panel - Additional Modules**
- News management dengan CRUD (`admin/news.php`)
- Lab Partners management (`admin/mitra.php`)
- Students management (`admin/mahasiswa.php`)
- Gallery management (`admin/gallery.php`)

### **2. Lab Management System**
- Lab Tools management (`admin/alat_lab.php`)
- Lab Room management (`admin/ruang_lab.php`)
- Tool Loan system (`admin/peminjaman.php`)
- Attendance system (`admin/absensi.php`)
- Guestbook (`admin/buku_tamu.php`)

### **3. Service Layer**
- API endpoints untuk AJAX (`service/absen.php`)
- Guestbook service (`service/buku_tamu.php`)
- Loan service (`service/peminjaman.php`)

### **4. Configuration & Settings Management**
- Settings management (`config/settings.php`)
- Site configuration
- Dynamic logo & title management

---

## 📁 **FILE YANG DIKERJAKAN**

1. `admin/news.php` - News management dengan CRUD
2. `admin/mitra.php` - Lab Partners management
3. `admin/mahasiswa.php` - Students management
4. `admin/gallery.php` - Gallery management
5. `admin/alat_lab.php` - Lab Tools management
6. `admin/ruang_lab.php` - Lab Room management
7. `admin/peminjaman.php` - Tool Loan system
8. `admin/absensi.php` - Attendance system
9. `admin/buku_tamu.php` - Guestbook management
10. `service/absen.php` - Attendance API service
11. `service/buku_tamu.php` - Guestbook API service
12. `service/peminjaman.php` - Loan service API
13. `config/settings.php` - Settings helper functions
14. `css/style-absensi.css` - Attendance page styling
15. `css/style-peminjaman.css` - Loan page styling
16. `css/style-buku-tamu.css` - Guestbook page styling

---

## 💻 **KODE PROGRAM & PENJELASAN**

### **1. admin/peminjaman.php**

#### **Bagian 1: Setup & Include Procedures**

```php
<?php
require_once '../config/auth.php';
requireAdmin();

$conn = getDBConnection();
$message = '';
$message_type = '';

// Include procedures
require_once __DIR__ . '/../config/procedures.php';
```

**Penjelasan:**
- Memastikan hanya admin yang bisa akses
- Include file procedures untuk menggunakan stored procedure
- Inisialisasi variabel message untuk feedback

#### **Bagian 2: Check Request Table**

```php
// Check if request_peminjaman table exists (check once)
$hasRequestTable = false;
try {
    $check_table = $conn->query("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'request_peminjaman')");
    $hasRequestTable = $check_table->fetchColumn();
} catch (PDOException $e) {
    $hasRequestTable = false;
}
```

**Penjelasan:**
- Mengecek apakah tabel `request_peminjaman` ada
- Digunakan untuk fallback jika tabel tidak ada
- Menghindari error jika struktur database berbeda

#### **Bagian 3: Approve Request**

```php
if ($action === 'approve_request') {
    $id_request = intval($_POST['id_request'] ?? 0);
    
    if ($id_request <= 0) {
        $message = 'Invalid request ID!';
        $message_type = 'error';
    } elseif (!$hasRequestTable) {
        // Fallback: update keterangan dengan [APPROVED]
        $update_stmt = $conn->prepare("UPDATE peminjaman SET keterangan = :keterangan WHERE id_peminjaman = :id");
        $update_stmt->execute([
            'id' => $id_request,
            'keterangan' => $keterangan_baru
        ]);
    } else {
        $result = callApproveRequest($id_request, $admin_id);
        // Handle result...
    }
}
```

**Penjelasan:**
- Validasi ID request
- Fallback jika tabel request tidak ada
- Menggunakan procedure `callApproveRequest()` untuk approve request
- Redirect setelah berhasil untuk prevent resubmission

#### **Bagian 4: Reject Request**

```php
elseif ($action === 'reject_request') {
    $id_request = intval($_POST['id_request'] ?? 0);
    $alasan_reject = trim($_POST['alasan_reject'] ?? '');
    
    if (empty($alasan_reject)) {
        $message = 'Rejection reason must be filled!';
        $message_type = 'error';
    } else {
        $result = callRejectRequest($id_request, $admin_id, $alasan_reject);
        // Handle result...
    }
}
```

**Penjelasan:**
- Validasi alasan reject harus diisi
- Menggunakan procedure `callRejectRequest()` untuk reject request
- Error handling yang baik

#### **Bagian 5: Return Peminjaman**

```php
elseif ($action === 'return_peminjaman') {
    $id_peminjaman = intval($_POST['id_peminjaman'] ?? 0);
    $kondisi_barang = trim($_POST['kondisi_barang'] ?? 'baik');
    $catatan_return = trim($_POST['catatan_return'] ?? '');
    
    $result = callReturnPeminjaman($id_peminjaman, $admin_id, $kondisi_barang, $catatan_return ?: null);
    
    if ($result['success']) {
        header('Location: peminjaman.php?returned=1&...');
        exit;
    }
}
```

**Penjelasan:**
- Menggunakan procedure `callReturnPeminjaman()` untuk proses pengembalian
- Mencatat kondisi barang dan catatan return
- Redirect untuk prevent resubmission

---

### **2. admin/absensi.php**

#### **Bagian 1: Pagination & Filtering**

```php
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
```

**Penjelasan:**
- Pagination dengan 15 items per page
- Filter berdasarkan tanggal (today, week, month)
- Menggunakan PostgreSQL date functions

#### **Bagian 2: Dynamic Column Detection**

```php
// Check what columns exist in the absensi table
$check_cols = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'absensi' AND table_schema = 'public'");
$columns = $check_cols->fetchAll(PDO::FETCH_COLUMN);

$student_col = null;
if (in_array('nim', $columns)) {
    $student_col = 'nim';
} elseif (in_array('id_mhs', $columns)) {
    $student_col = 'id_mhs';
} elseif (in_array('id_mahasiswa', $columns)) {
    $student_col = 'id_mahasiswa';
}
```

**Penjelasan:**
- Deteksi kolom identifier mahasiswa secara dinamis
- Support untuk berbagai struktur tabel
- Fallback jika kolom tidak ditemukan

#### **Bagian 3: Query dengan Student Info**

```php
$query = "SELECT
    a.id_absensi,
    a." . $student_col . " as student_id,
    a.tanggal as date,
    a.waktu_datang as check_in_time,
    a.waktu_pulang as check_out_time,
    a.keterangan as notes,
    COALESCE((SELECT nama FROM mahasiswa WHERE id_mahasiswa = CAST(a." . $student_col . " AS TEXT) LIMIT 1), 'N/A') as student_name
FROM absensi a
WHERE 1=1" . $date_filter . "
ORDER BY a.tanggal DESC, a.waktu_datang DESC
LIMIT :limit OFFSET :offset";
```

**Penjelasan:**
- Query dengan JOIN ke tabel mahasiswa
- Menggunakan COALESCE untuk fallback jika data tidak ada
- Pagination dengan LIMIT dan OFFSET

---

### **3. admin/alat_lab.php**

#### **Bagian 1: Create Views**

```php
// View untuk melihat alat yang sedang dipinjam
$view_dipinjam_sql = "CREATE OR REPLACE VIEW view_alat_dipinjam AS
    SELECT
        pj.id_peminjaman,
        pj.id_alat,
        alat.id_alat_lab,
        alat.nama_alat,
        alat.deskripsi,
        pj.nama_peminjam,
        pj.tanggal_pinjam,
        pj.tanggal_kembali,
        pj.keterangan,
        pj.status,
        pj.created_at
    FROM peminjaman pj
    JOIN alat_lab alat ON alat.id_alat_lab = pj.id_alat
    WHERE pj.status = 'dipinjam'";
$conn->exec($view_dipinjam_sql);
```

**Penjelasan:**
- Membuat view untuk melihat alat yang dipinjam
- Menggunakan CREATE OR REPLACE untuk update view
- JOIN dengan tabel peminjaman

#### **Bagian 2: Add Alat**

```php
if ($action === 'add_alat') {
    $nama_alat = trim($_POST['nama_alat'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $stock = intval($_POST['stock'] ?? 0);

    if (empty($nama_alat)) {
        $message = 'Tool name must be filled!';
        $message_type = 'error';
    } else {
        $stmt = $conn->prepare("INSERT INTO alat_lab (nama_alat, deskripsi, stock) VALUES (:nama_alat, :deskripsi, :stock)");
        $stmt->execute([
            'nama_alat' => $nama_alat,
            'deskripsi' => $deskripsi ?: null,
            'stock' => $stock
        ]);
        header('Location: alat_lab.php?added=1');
        exit;
    }
}
```

**Penjelasan:**
- Validasi input (nama alat wajib)
- Prepared statement untuk prevent SQL injection
- Redirect setelah insert untuk prevent resubmission

---

### **4. admin/ruang_lab.php**

#### **Bagian 1: Admin ID Validation**

```php
// Get current admin ID and validate it exists in admin table
$admin_id = $_SESSION['id_admin'] ?? null;
if ($admin_id) {
    try {
        $check_admin = $conn->prepare("SELECT id_admin FROM admin WHERE id_admin = :id_admin LIMIT 1");
        $check_admin->execute(['id_admin' => $admin_id]);
        if (!$check_admin->fetch()) {
            $admin_id = null;
        }
    } catch (PDOException $e) {
        $admin_id = null;
    }
}
```

**Penjelasan:**
- Validasi admin ID dari session
- Mengecek apakah admin ID valid di database
- Set null jika tidak valid untuk safety

#### **Bagian 2: Add Ruang dengan Fallback**

```php
if ($action === 'add_ruang') {
    $nama_ruang = trim($_POST['nama_ruang'] ?? '');
    
    if (empty($nama_ruang)) {
        $message = 'Nama ruang harus diisi!';
        $message_type = 'error';
    } else {
        if ($admin_id) {
            $stmt = $conn->prepare("INSERT INTO ruang_lab (nama_ruang, id_admin) VALUES (:nama_ruang, :id_admin)");
            $stmt->execute([
                'nama_ruang' => $nama_ruang,
                'id_admin' => $admin_id
            ]);
        } else {
            // Fallback: insert without id_admin
            $stmt = $conn->prepare("INSERT INTO ruang_lab (nama_ruang) VALUES (:nama_ruang)");
            $stmt->execute(['nama_ruang' => $nama_ruang]);
        }
    }
}
```

**Penjelasan:**
- Fallback jika admin_id tidak valid
- Tetap bisa insert meskipun admin_id null
- Error handling yang baik

#### **Bagian 3: Delete dengan Check Borrowed**

```php
elseif ($action === 'delete_ruang') {
    $id = $_POST['id'] ?? 0;
    
    // Check if room is being borrowed
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM peminjaman WHERE id_ruang = :id AND status = 'dipinjam'");
    $check_stmt->execute(['id' => $id]);
    $borrowed_count = $check_stmt->fetchColumn();

    if ($borrowed_count > 0) {
        $message = 'Room cannot be deleted because it is currently borrowed!';
        $message_type = 'error';
    } else {
        $stmt = $conn->prepare("DELETE FROM ruang_lab WHERE id_ruang_lab = :id");
        $stmt->execute(['id' => $id]);
        $message = 'Lab room successfully deleted!';
        $message_type = 'success';
    }
}
```

**Penjelasan:**
- Validasi sebelum delete
- Cek apakah ruangan sedang dipinjam
- Prevent delete jika masih ada peminjaman aktif

---

### **5. service/peminjaman.php**

#### **Bagian 1: Create Database Views**

```php
// Create database views
try {
    $view_dipinjam_sql = "CREATE OR REPLACE VIEW view_alat_dipinjam AS
        SELECT
            pj.id_peminjaman,
            pj.id_alat,
            alat.nama_alat,
            alat.deskripsi,
            pj.nama_peminjam,
            pj.tanggal_pinjam,
            pj.tanggal_kembali,
            pj.keterangan,
            pj.status,
            pj.created_at,
            pj.id_ruang
        FROM peminjaman pj
        LEFT JOIN alat_lab alat ON alat.id_alat_lab = pj.id_alat
        WHERE pj.status = 'dipinjam'";
    $conn->exec($view_dipinjam_sql);
} catch (PDOException $e) {
    // Silent fail - view might already exist
}
```

**Penjelasan:**
- Membuat view saat halaman dimuat
- Silent fail jika view sudah ada
- Menggunakan LEFT JOIN untuk include semua data

#### **Bagian 2: Pinjam Alat dengan Stock Check**

```php
if ($action === 'pinjam_alat') {
    // Validasi input...
    
    // Menggunakan view_alat_tersedia untuk cek stok
    $check_stmt = $conn->prepare("SELECT * FROM view_alat_tersedia WHERE id_alat_lab = :id");
    $check_stmt->execute(['id' => $id_alat]);
    $alat = $check_stmt->fetch();

    if (!$alat) {
        $message = 'Tool not found!';
        $message_type = 'error';
    } elseif ($alat['stok_tersedia'] < $jumlah) {
        $message = 'Insufficient stock! Available: ' . $alat['stok_tersedia'] . ' unit(s), requested: ' . $jumlah;
        $message_type = 'error';
    } else {
        // Insert peminjaman...
    }
}
```

**Penjelasan:**
- Menggunakan view untuk cek stok tersedia
- Validasi stok sebelum insert
- Error message yang informatif

#### **Bagian 3: Sequence Fix**

```php
// Fix sequence if it's out of sync
try {
    $max_id_stmt = $conn->query("SELECT COALESCE(MAX(id_peminjaman), 0) as max_id FROM peminjaman");
    $max_id = $max_id_stmt->fetch()['max_id'];
    $conn->exec("SELECT setval('peminjaman_id_peminjaman_seq', " . ($max_id + 1) . ", false)");
} catch (PDOException $seq_e) {
    // Sequence might not exist or error, continue anyway
}
```

**Penjelasan:**
- Fix sequence jika out of sync
- Mencegah error saat insert dengan auto increment
- Silent fail jika sequence tidak ada

#### **Bagian 4: Return Peminjaman dengan Procedure**

```php
if ($action === 'kembalikan_alat') {
    $id_peminjaman = intval($_POST['id_peminjaman'] ?? 0);
    
    // Menggunakan prosedur proc_return_peminjaman
    require_once __DIR__ . '/../config/procedures.php';
    
    $result = callReturnPeminjaman($id_peminjaman, $admin_id, 'baik', null);
    
    if ($result['success']) {
        $message = $result['message'];
        $message_type = 'success';
    } else {
        $message = $result['message'];
        $message_type = 'error';
    }
}
```

**Penjelasan:**
- Menggunakan stored procedure untuk return
- Include procedures.php untuk akses function
- Handle result dari procedure

---

### **6. service/absen.php**

#### **Bagian 1: Search NIM API**

```php
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'search_nim') {
    header('Content-Type: application/json');
    $search_term = trim($_GET['q'] ?? '');

    if (strlen($search_term) < 2) {
        echo json_encode([]);
        exit;
    }

    $stmt = $conn->prepare("SELECT id_mahasiswa as nim, nama, status FROM mahasiswa WHERE CAST(id_mahasiswa AS TEXT) ILIKE :search ORDER BY id_mahasiswa LIMIT 10");
    $stmt->execute([':search' => '%' . $search_term . '%']);
    $results = $stmt->fetchAll();

    $suggestions = [];
    foreach ($results as $row) {
        $suggestions[] = [
            'nim' => $row['nim'],
            'nama' => $row['nama'],
            'status' => $row['status']
        ];
    }

    echo json_encode($suggestions);
    exit;
}
```

**Penjelasan:**
- API endpoint untuk search NIM
- JSON response untuk AJAX
- ILIKE untuk case-insensitive search
- Limit 10 hasil untuk performa

#### **Bagian 2: Validate NIM API**

```php
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'validate_nim') {
    header('Content-Type: application/json');
    $nim = trim($_GET['nim'] ?? '');

    $stmt = $conn->prepare("SELECT id_mahasiswa as nim, nama, status FROM mahasiswa WHERE id_mahasiswa = :nim LIMIT 1");
    $stmt->execute(['nim' => $nim]);
    $mahasiswa = $stmt->fetch();

    if ($mahasiswa) {
        echo json_encode([
            'valid' => true,
            'nama' => $mahasiswa['nama'],
            'status' => $mahasiswa['status']
        ]);
    } else {
        echo json_encode(['valid' => false, 'message' => 'Student ID is not registered']);
    }
    exit;
}
```

**Penjelasan:**
- API untuk validasi NIM
- Return JSON dengan status valid/invalid
- Include nama dan status mahasiswa

#### **Bagian 3: Process Attendance dengan Procedure**

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = trim($_POST['nim'] ?? '');
    $tipe_absen = $_POST['tipe_absen'] ?? '';
    
    // Menggunakan prosedur proc_update_absensi
    require_once __DIR__ . '/../config/procedures.php';
    
    $action = ($tipe_absen === 'masuk') ? 'checkin' : 'checkout';
    
    $result = callUpdateAbsensi($mahasiswa['nim'], $action, $keterangan_full);
    
    if ($result['success']) {
        if ($action === 'checkin') {
            $message = 'Check-in successful! Welcome, ' . htmlspecialchars($mahasiswa['nama']) . '.';
        } else {
            $message = 'Check-out successful! Thank you, ' . htmlspecialchars($mahasiswa['nama']) . '.';
        }
        $message_type = 'success';
    }
}
```

**Penjelasan:**
- Menggunakan procedure untuk update absensi
- Convert tipe_absen ke action (checkin/checkout)
- User-friendly message setelah success

---

### **7. config/settings.php**

#### **Bagian 1: Get Settings Function**

```php
function getSettings() {
    static $settings = null;
    
    if ($settings === null) {
        try {
            $conn = getDBConnection();
            $stmt = $conn->query("SELECT * FROM settings ORDER BY id_setting LIMIT 1");
            $settings = $stmt->fetch();
            
            // If no settings found, initialize as empty array
            if ($settings === false) {
                $settings = [];
            }
            
            // Parse page_titles JSON if exists
            if (!empty($settings['page_titles'])) {
                if (is_string($settings['page_titles'])) {
                    $settings['page_titles'] = json_decode($settings['page_titles'], true) ?: [];
                }
            } else {
                $settings['page_titles'] = [];
            }
        } catch (PDOException $e) {
            error_log("Error fetching settings: " . $e->getMessage());
            $settings = [];
        }
    }
    
    return $settings;
}
```

**Penjelasan:**
- Static variable untuk cache settings
- Parse JSON untuk page_titles
- Error handling dengan fallback ke empty array
- Singleton pattern untuk performa

#### **Bagian 2: Get Page Title Function**

```php
function getPageTitle($page_name) {
    $settings = getSettings();
    
    if (empty($settings)) {
        // Default fallback
        return [
            'title' => 'InLET - Information And Learning Engineering Technology',
            'subtitle' => 'State Polytechnic of Malang'
        ];
    }
    
    $page_titles = $settings['page_titles'] ?? [];
    
    if (isset($page_titles[$page_name])) {
        return [
            'title' => $page_titles[$page_name]['title'] ?? '',
            'subtitle' => $page_titles[$page_name]['subtitle'] ?? ''
        ];
    }
    
    // Fallback to default
    return [
        'title' => $settings['site_title'] ?? 'InLET - Information And Learning Engineering Technology',
        'subtitle' => $settings['site_subtitle'] ?? 'State Polytechnic of Malang'
    ];
}
```

**Penjelasan:**
- Dynamic page title dari database
- Fallback ke default jika tidak ada
- Support untuk multiple pages

#### **Bagian 3: Get Site Logo Function**

```php
function getSiteLogo() {
    $settings = getSettings();
    if (!empty($settings['site_logo'])) {
        return $settings['site_logo'];
    }
    return 'assets/logo.png'; // Default fallback
}

function getFooterLogo() {
    $settings = getSettings();
    if (!empty($settings['footer_logo'])) {
        return $settings['footer_logo'];
    }
    return 'assets/logoPutih.png'; // Default fallback
}
```

**Penjelasan:**
- Dynamic logo dari database
- Fallback ke default logo
- Separate function untuk header dan footer logo

---

## 🔑 **FITUR UTAMA**

### **1. Lab Management System**
- ✅ **Alat Lab**: CRUD dengan stock management
- ✅ **Ruang Lab**: CRUD dengan status tracking
- ✅ **Peminjaman**: Request, approval, return workflow
- ✅ **Absensi**: Check-in/check-out dengan procedure
- ✅ **Guestbook**: Guest registration dan tracking

### **2. Service Layer (API)**
- ✅ **Attendance API**: Search NIM, validate NIM, process attendance
- ✅ **Loan Service**: Pinjam alat, pinjam ruang, return
- ✅ **Guestbook Service**: Guest registration API

### **3. Database Views**
- ✅ **view_alat_dipinjam**: Alat yang sedang dipinjam
- ✅ **view_alat_tersedia**: Alat tersedia dengan stok
- ✅ **view_ruang_dipinjam**: Ruang yang sedang dipinjam

### **4. Stored Procedures Integration**
- ✅ **callApproveRequest()**: Approve peminjaman request
- ✅ **callRejectRequest()**: Reject peminjaman request
- ✅ **callReturnPeminjaman()**: Proses pengembalian
- ✅ **callUpdateAbsensi()**: Update absensi check-in/out

---

## 🎯 **HIGHLIGHTS**

1. **Robust Error Handling**: Semua operasi memiliki try-catch dengan fallback
2. **Dynamic Column Detection**: Support berbagai struktur database
3. **View Management**: View dibuat dinamis untuk query yang kompleks
4. **Procedure Integration**: Menggunakan stored procedure untuk business logic
5. **API Design**: Service layer dengan JSON response untuk AJAX
6. **Settings Management**: Dynamic configuration dari database
7. **Validation**: Input validation di semua form
8. **Security**: Prepared statements, admin authentication

---

## 📝 **CATATAN PENTING**

1. **Sequence Fix**: Perlu fix sequence untuk auto increment
2. **View Creation**: View dibuat setiap kali halaman dimuat
3. **Fallback Logic**: Banyak fallback untuk kompatibilitas database
4. **Procedure Fallback**: Direct implementation jika procedure gagal
5. **Admin ID Validation**: Validasi admin ID dari session

---

## ✅ **DELIVERABLES**

- ✅ Complete admin panel untuk semua modul tambahan
- ✅ Lab management system yang functional
- ✅ Service layer untuk AJAX operations
- ✅ All CRUD operations working
- ✅ Integration dengan stored procedures
- ✅ Dynamic settings management
- ✅ Robust error handling

---

**Selesai!** 🎉

