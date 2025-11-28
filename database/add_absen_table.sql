-- -------------------------------------------------------
-- Tambahan tabel untuk sistem absensi
-- Menambahkan kolom NIM ke tabel mahasiswa dan membuat tabel absensi
-- -------------------------------------------------------

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

-- -------------------------------------------------------
-- Catatan:
-- 1. Kolom NIM ditambahkan ke tabel mahasiswa untuk identifikasi
-- 2. Tabel absensi menyimpan semua data absensi masuk dan keluar
-- 3. Tipe absen: 'masuk' untuk absen masuk, 'keluar' untuk absen keluar
-- 4. Status: 'magang', 'skripsi', atau 'regular' (selain magang dan skripsi)
-- -------------------------------------------------------


