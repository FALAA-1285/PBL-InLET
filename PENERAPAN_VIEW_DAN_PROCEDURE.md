# Penerapan View dan Procedure Database di Program

Dokumen ini menjelaskan dimana view dan stored procedure dari database diterapkan di dalam program.

---

## 📋 DAFTAR VIEW DATABASE

Program menggunakan **3 view** utama:

1. **`view_alat_dipinjam`** - Menampilkan alat yang sedang dipinjam
2. **`view_alat_tersedia`** - Menampilkan alat yang tersedia dengan informasi stok
3. **`view_ruang_dipinjam`** - Menampilkan ruangan yang sedang dipinjam

---

## 📋 DAFTAR STORED PROCEDURE

Program menggunakan **4 stored procedure** utama:

1. **`create_request`** - Membuat request peminjaman baru
2. **`proc_update_absensi`** - Update absensi (checkin/checkout)
3. **`proc_return_peminjaman`** - Proses pengembalian peminjaman
4. **`proc_reject_request`** - Menolak request peminjaman

---

## 📍 LOKASI PENERAPAN

### 1. VIEW: `view_alat_dipinjam`

#### **Pembuatan View:**
- **`setup/create_views.php`** (baris 16-34)
- **`setup/create_view_alat_dipinjam.php`** (baris 16-31)
- **`service/peminjaman.php`** (baris 19-35)
- **`admin/dashboard.php`** (baris 26-39)
- **`admin/alat_lab.php`** (baris 12-34)
- **`test_joins.php`** (baris 13-30)

#### **Penggunaan View:**
- **`admin/dashboard.php`** (baris 285)
  ```php
  $stmt = $conn->query("SELECT COUNT(*) as count FROM view_alat_dipinjam");
  ```
  - Menampilkan jumlah alat yang sedang dipinjam di dashboard

- **`service/peminjaman.php`** (baris 510-514)
  ```php
  $peminjaman_alat_stmt = $conn->query("
      SELECT * FROM view_alat_dipinjam
      WHERE id_alat IS NOT NULL
      ORDER BY tanggal_pinjam DESC
  ");
  ```
  - Menampilkan daftar alat yang sedang dipinjam

- **`setup/create_view_alat_dipinjam.php`** (baris 44)
  ```php
  $test_stmt = $conn->query("SELECT COUNT(*) FROM view_alat_dipinjam");
  ```
  - Testing dan verifikasi view

---

### 2. VIEW: `view_alat_tersedia`

#### **Pembuatan View:**
- **`setup/create_views.php`** (baris 41-58)
- **`service/peminjaman.php`** (baris 40-57)
- **`admin/dashboard.php`** (baris 41-56)
- **`admin/alat_lab.php`** (baris 36-56)
- **`test_joins.php`** (baris 33-51)

#### **Penggunaan View:**
- **`service/peminjaman.php`** (baris 118)
  ```php
  $check_stmt = $conn->prepare("SELECT * FROM view_alat_tersedia WHERE id_alat_lab = :id");
  ```
  - Mengecek stok tersedia saat proses peminjaman

- **`service/peminjaman.php`** (baris 455-459)
  ```php
  $alat_stmt = $conn->query("
      SELECT * FROM view_alat_tersedia
      WHERE stok_tersedia > 0
      ORDER BY nama_alat
  ");
  ```
  - Menampilkan daftar alat yang tersedia untuk dipinjam

---

### 3. VIEW: `view_ruang_dipinjam`

#### **Pembuatan View:**
- **`service/peminjaman.php`** (baris 60-78)
- **`test_joins.php`** (baris 54-72)

#### **Penggunaan View:**
- **`service/peminjaman.php`** (baris 522-525)
  ```php
  $peminjaman_ruang_stmt = $conn->query("
      SELECT * FROM view_ruang_dipinjam
      ORDER BY tanggal_pinjam DESC, waktu_pinjam DESC
  ");
  ```
  - Menampilkan daftar ruangan yang sedang dipinjam

---

### 4. PROCEDURE: `create_request`

#### **Wrapper Function:**
- **`config/procedures.php`** (baris 67-128)
  ```php
  function callCreateRequest($p_id_alat, $p_id_ruang, $p_nama_peminjam, ...)
  ```

#### **Pemanggilan Procedure:**
- Procedure dipanggil melalui wrapper function `callCreateRequest()` di `config/procedures.php`
- Menggunakan DO block dengan temporary table untuk mendapatkan OUT parameters
- Belum ditemukan penggunaan langsung di file lain (kemungkinan digunakan di form peminjaman)

---

### 5. PROCEDURE: `proc_update_absensi`

#### **Wrapper Function:**
- **`config/procedures.php`** (baris 134-296)
  - `callUpdateAbsensi()` - Wrapper utama (baris 134-172)
  - `callProcUpdateAbsensiDirect()` - Implementasi langsung sebagai fallback (baris 178-296)

#### **Penggunaan:**
- **`service/absen.php`** (baris 117)
  ```php
  require_once __DIR__ . '/../config/procedures.php';
  $result = callUpdateAbsensi($mahasiswa['nim'], $action, $keterangan_full);
  ```
  - Dipanggil saat user melakukan check-in atau check-out
  - Mengupdate absensi mahasiswa

---

### 6. PROCEDURE: `proc_return_peminjaman`

#### **Wrapper Function:**
- **`config/procedures.php`** (baris 301-427)
  - `callReturnPeminjaman()` - Wrapper utama (baris 301-349)
  - `callReturnPeminjamanDirect()` - Implementasi langsung sebagai fallback (baris 354-427)

#### **Penggunaan:**
- **`service/peminjaman.php`** (baris 430-434)
  ```php
  require_once __DIR__ . '/../config/procedures.php';
  $result = callReturnPeminjaman($id_peminjaman, $admin_id, 'baik', null);
  ```
  - Dipanggil saat proses pengembalian peminjaman

- **`admin/peminjaman.php`** (baris 171)
  ```php
  $result = callReturnPeminjaman($id_peminjaman, $admin_id, $kondisi_barang, $catatan_return ?: null);
  ```
  - Dipanggil dari halaman admin untuk proses pengembalian

---

### 7. PROCEDURE: `proc_reject_request`

#### **Wrapper Function:**
- **`config/procedures.php`** (baris 432-486)
  ```php
  function callRejectRequest($p_id_request, $p_id_admin, $p_alasan_reject)
  ```

#### **Penggunaan:**
- **`admin/peminjaman.php`** (baris 151)
  ```php
  $result = callRejectRequest($id_request, $admin_id, $alasan_reject);
  ```
  - Dipanggil saat admin menolak request peminjaman

---

### 8. FUNCTION: `callApproveRequest`

**Catatan:** Ini bukan stored procedure, tapi function PHP yang mengimplementasikan logika approve request.

#### **Function:**
- **`config/procedures.php`** (baris 491-567)
  ```php
  function callApproveRequest($p_id_request, $p_id_admin)
  ```

#### **Penggunaan:**
- **`admin/peminjaman.php`** (baris 70)
  ```php
  $result = callApproveRequest($id_request, $admin_id);
  ```
  - Dipanggil saat admin menyetujui request peminjaman

---

## 📂 FILE UTAMA YANG MENGGUNAKAN VIEW & PROCEDURE

### **File yang Menggunakan VIEW:**

1. **`service/peminjaman.php`**
   - Membuat 3 view (baris 19-78)
   - Menggunakan `view_alat_tersedia` (baris 118, 455)
   - Menggunakan `view_alat_dipinjam` (baris 510)
   - Menggunakan `view_ruang_dipinjam` (baris 522)

2. **`admin/dashboard.php`**
   - Membuat 2 view (baris 26-56)
   - Menggunakan `view_alat_dipinjam` (baris 285)

3. **`admin/alat_lab.php`**
   - Membuat 2 view (baris 12-56)

### **File yang Menggunakan PROCEDURE:**

1. **`config/procedures.php`**
   - File utama yang berisi semua wrapper function untuk stored procedure
   - Semua procedure dipanggil melalui file ini

2. **`admin/peminjaman.php`**
   - Menggunakan `callApproveRequest()` (baris 70)
   - Menggunakan `callRejectRequest()` (baris 151)
   - Menggunakan `callReturnPeminjaman()` (baris 171)
   - Include: `require_once __DIR__ . '/../config/procedures.php';` (baris 10)

3. **`service/peminjaman.php`**
   - Menggunakan `callReturnPeminjaman()` (baris 434)
   - Include: `require_once __DIR__ . '/../config/procedures.php';` (baris 431)

4. **`service/absen.php`**
   - Menggunakan `callUpdateAbsensi()` (baris 117)
   - Include: `require_once __DIR__ . '/../config/procedures.php';` (baris 107)

---

## 🔄 ALUR KERJA

### **View:**
1. View dibuat secara dinamis di beberapa file saat halaman dimuat
2. View digunakan untuk query data yang kompleks dengan JOIN
3. View menyederhanakan query dan meningkatkan performa

### **Procedure:**
1. Procedure dipanggil melalui wrapper function di `config/procedures.php`
2. Wrapper function menggunakan DO block untuk mendapatkan OUT parameters
3. Jika procedure call gagal, ada fallback ke implementasi langsung (direct implementation)
4. Semua procedure memiliki error handling yang baik

---

## 📝 CATATAN PENTING

1. **View dibuat secara dinamis** - View dibuat ulang setiap kali halaman dimuat menggunakan `CREATE OR REPLACE VIEW`
2. **Procedure dengan fallback** - Semua procedure memiliki implementasi langsung sebagai fallback jika procedure call gagal
3. **Error handling** - Semua penggunaan view dan procedure memiliki try-catch untuk error handling
4. **Wrapper functions** - Semua procedure dipanggil melalui wrapper function untuk konsistensi dan kemudahan maintenance

---

## 🎯 KESIMPULAN

**View digunakan untuk:**
- Menyederhanakan query kompleks dengan JOIN
- Menampilkan data agregat (stok tersedia, jumlah dipinjam)
- Dashboard dan laporan

**Procedure digunakan untuk:**
- Business logic yang kompleks (create request, return peminjaman)
- Transaksi yang memerlukan validasi (update absensi, reject request)
- Konsistensi data dan integritas database

Semua view dan procedure terpusat di `config/procedures.php` untuk kemudahan maintenance dan konsistensi.

