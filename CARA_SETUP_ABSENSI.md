# 📋 Cara Setup Form Absensi

## ⚙️ Setup Database

Sebelum menggunakan form absensi, Anda perlu menjalankan script SQL untuk membuat tabel absensi dan menambahkan kolom NIM ke tabel mahasiswa.

### Langkah-langkah:

1. **Buka database PostgreSQL Anda** (misalnya menggunakan pgAdmin, psql, atau tools lainnya)

2. **Jalankan script SQL berikut** yang ada di `database/add_absen_table.sql`:
   ```sql
   -- Tambahkan kolom NIM ke tabel mahasiswa (jika belum ada)
   ALTER TABLE mahasiswa 
   ADD COLUMN IF NOT EXISTS nim VARCHAR(50);

   -- Buat index pada NIM untuk performa query
   CREATE INDEX IF NOT EXISTS idx_mahasiswa_nim ON mahasiswa(nim);

   -- Tabel absensi untuk menyimpan data absensi masuk dan keluar
   CREATE TABLE IF NOT EXISTS absensi (
       id_absensi SERIAL PRIMARY KEY,
       nim VARCHAR(50) NOT NULL,
       status VARCHAR(20) NOT NULL, -- 'magang' | 'skripsi' | 'regular'
       tipe_absen VARCHAR(10) NOT NULL, -- 'masuk' | 'keluar'
       waktu_absen TIMESTAMP WITH TIME ZONE DEFAULT now(),
       keterangan VARCHAR(500),
       CONSTRAINT chk_absen_status CHECK (status IN ('magang','skripsi','regular')),
       CONSTRAINT chk_absen_tipe CHECK (tipe_absen IN ('masuk','keluar'))
   );

   -- Indeks untuk performa query
   CREATE INDEX IF NOT EXISTS idx_absensi_nim ON absensi(nim);
   CREATE INDEX IF NOT EXISTS idx_absensi_waktu ON absensi(waktu_absen);
   CREATE INDEX IF NOT EXISTS idx_absensi_tipe ON absensi(tipe_absen);
   ```

   **Atau** jalankan file SQL langsung:
   ```bash
   psql -U postgres -d inlet_pbl -f database/add_absen_table.sql
   ```

3. **Verifikasi tabel sudah dibuat**:
   ```sql
   \d absensi
   ```

## 📝 Cara Menggunakan Form Absensi

### 1. Akses Halaman Absensi

Buka browser dan akses:
```
http://localhost/PBL-InLET/absen.php
```

Atau klik menu **"Absensi"** di navigasi header.

### 2. Isi Form Absensi

Form absensi memiliki 3 bagian utama:

#### a. **Input NIM** (Wajib)
- Masukkan NIM mahasiswa
- Field ini wajib diisi

#### b. **Pilihan Status** (Wajib)
Pilih salah satu status:
- **Skripsi** - Untuk mahasiswa yang sedang skripsi
- **Magang** - Untuk mahasiswa yang sedang magang
- **Selain Magang dan Skripsi** - Untuk mahasiswa dengan status lain

#### c. **Tombol Absensi**
- **Absen Masuk** - Klik untuk melakukan absensi masuk
- **Absen Keluar** - Klik untuk melakukan absensi keluar

### 3. Validasi Sistem

Sistem akan melakukan validasi berikut:

#### ✅ **Absen Masuk:**
- NIM harus diisi
- Status harus dipilih
- Tidak boleh ada absen masuk ganda dalam 1 hari

#### ✅ **Absen Keluar:**
- NIM harus diisi
- Status harus dipilih
- Harus sudah melakukan absen masuk hari ini
- Tidak boleh ada absen keluar ganda dalam 1 hari

### 4. Pesan Konfirmasi

Setelah melakukan absensi, sistem akan menampilkan pesan:
- **Sukses** (hijau) - Absensi berhasil dilakukan
- **Error** (merah) - Terjadi kesalahan (misalnya sudah absen, belum absen masuk, dll)

## 🔍 Struktur Database

### Tabel `absensi`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id_absensi | SERIAL PRIMARY KEY | ID unik absensi |
| nim | VARCHAR(50) | NIM mahasiswa |
| status | VARCHAR(20) | Status: 'magang', 'skripsi', atau 'regular' |
| tipe_absen | VARCHAR(10) | Tipe: 'masuk' atau 'keluar' |
| waktu_absen | TIMESTAMP | Waktu absensi (otomatis) |
| keterangan | VARCHAR(500) | Keterangan tambahan (opsional) |

### Tabel `mahasiswa` (diupdate)

| Kolom Baru | Tipe | Keterangan |
|------------|------|------------|
| nim | VARCHAR(50) | NIM mahasiswa (ditambahkan) |

## 📊 Query Berguna

### Melihat semua absensi hari ini:
```sql
SELECT * FROM absensi 
WHERE DATE(waktu_absen) = CURRENT_DATE 
ORDER BY waktu_absen DESC;
```

### Melihat absensi masuk yang belum keluar hari ini:
```sql
SELECT a1.* FROM absensi a1
WHERE a1.tipe_absen = 'masuk'
  AND DATE(a1.waktu_absen) = CURRENT_DATE
  AND NOT EXISTS (
    SELECT 1 FROM absensi a2
    WHERE a2.nim = a1.nim
      AND a2.tipe_absen = 'keluar'
      AND DATE(a2.waktu_absen) = CURRENT_DATE
  );
```

### Melihat riwayat absensi berdasarkan NIM:
```sql
SELECT * FROM absensi 
WHERE nim = 'YOUR_NIM' 
ORDER BY waktu_absen DESC;
```

## ⚠️ Catatan Penting

1. **Tabel `absensi` independen** dari tabel `mahasiswa`
   - Anda bisa absen meskipun NIM belum terdaftar di tabel mahasiswa
   - Ini memungkinkan absensi fleksibel untuk mahasiswa yang belum terdaftar

2. **Satu absen per hari per tipe**
   - Setiap NIM hanya bisa absen masuk 1x per hari
   - Setiap NIM hanya bisa absen keluar 1x per hari
   - Harus absen masuk dulu sebelum absen keluar

3. **Waktu absensi otomatis**
   - Waktu absensi diisi otomatis oleh database saat record dibuat
   - Menggunakan `TIMESTAMP WITH TIME ZONE` untuk akurasi waktu

## 🔗 Link Terkait

- File form absensi: `absen.php`
- Script SQL: `database/add_absen_table.sql`
- Header dengan link absensi: `includes/header.php`

