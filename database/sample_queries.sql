-- -------------------------------------------------------
-- Sample Queries - Contoh Query yang Berguna
-- -------------------------------------------------------

-- 1. Mendapatkan semua mahasiswa yang memiliki akun login
SELECT 
    m.id_mhs,
    m.nama,
    m.status,
    m.tahun,
    u.username,
    u.role,
    u.created_at as tanggal_daftar
FROM mahasiswa m
INNER JOIN users u ON m.id_user = u.id_user
ORDER BY m.nama;

-- 2. Mendapatkan mahasiswa yang belum memiliki akun login
SELECT 
    id_mhs,
    nama,
    status,
    tahun
FROM mahasiswa
WHERE id_user IS NULL;

-- 3. Mendapatkan visitor count per pengunjung
SELECT 
    p.id_pengunjung,
    p.nama,
    p.email,
    p.asal_institusi,
    v.visit_count,
    v.first_visit,
    v.last_visit
FROM visitor v
INNER JOIN pengunjung p ON v.id_pengunjung = p.id_pengunjung
ORDER BY v.visit_count DESC;

-- 4. Mendapatkan progress dengan detail mahasiswa, artikel, dan member
SELECT 
    pr.id_progress,
    pr.judul as judul_progress,
    pr.tahun,
    pr.deskripsi,
    pr.created_at,
    m.nama as nama_mahasiswa,
    m.status as status_mahasiswa,
    a.judul as judul_artikel,
    mem.nama as nama_member,
    mem.jabatan
FROM progress pr
LEFT JOIN mahasiswa m ON pr.id_mhs = m.id_mhs
LEFT JOIN artikel a ON pr.id_artikel = a.id_artikel
LEFT JOIN member mem ON pr.id_member = mem.id_member
ORDER BY pr.created_at DESC;

-- 5. Mendapatkan produk beserta resource yang terkait
SELECT 
    p.id_produk,
    p.nama_produk,
    p.deskripsi as deskripsi_produk,
    p.harga,
    r.id_resource,
    r.judul as judul_resource,
    r.deskripsi as deskripsi_resource,
    pr.keterangan
FROM produk p
INNER JOIN produk_resource pr ON p.id_produk = pr.id_produk
INNER JOIN resource r ON pr.id_resource = r.id_resource
ORDER BY p.nama_produk, r.judul;

-- 6. Mendapatkan member beserta profil detailnya
SELECT 
    m.id_member,
    m.nama,
    m.email,
    m.jabatan,
    m.foto,
    pm.alamat,
    pm.no_tlp,
    pm.deskripsi
FROM member m
LEFT JOIN profil_member pm ON m.id_member = pm.id_member
ORDER BY m.nama;

-- 7. Mendapatkan admin beserta data user login
SELECT 
    a.id_admin,
    a.nama,
    a.email,
    a.phone,
    a.foto,
    u.username,
    u.role,
    u.created_at as tanggal_daftar
FROM admin a
INNER JOIN users u ON a.id_user = u.id_user;

-- 8. Mendapatkan berita terbaru (limit 10)
SELECT 
    id_berita,
    judul,
    LEFT(konten, 200) as konten_preview, -- preview 200 karakter
    gambar_thumbnail,
    created_at
FROM berita
ORDER BY created_at DESC
LIMIT 10;

-- 9. Mendapatkan artikel berdasarkan tahun
SELECT 
    id_artikel,
    judul,
    tahun,
    LEFT(konten, 300) as konten_preview
FROM artikel
WHERE tahun = 2024  -- ganti tahun sesuai kebutuhan
ORDER BY judul;

-- 10. Update visitor count (contoh untuk increment visit)
-- Pertama, cek apakah visitor record sudah ada
-- Jika belum ada, insert baru
-- Jika sudah ada, update visit_count dan last_visit

-- Contoh: Increment visit untuk pengunjung tertentu
UPDATE visitor
SET 
    visit_count = visit_count + 1,
    last_visit = now()
WHERE id_pengunjung = 1;  -- ganti dengan id_pengunjung yang sesuai

-- Jika belum ada record, insert:
INSERT INTO visitor (id_pengunjung, visit_count, last_visit, first_visit)
VALUES (1, 1, now(), now())
ON CONFLICT DO NOTHING;  -- jika menggunakan constraint unique

-- 11. Mendapatkan statistik kunjungan
SELECT 
    COUNT(DISTINCT v.id_pengunjung) as total_pengunjung_aktif,
    SUM(v.visit_count) as total_kunjungan,
    AVG(v.visit_count) as rata_rata_kunjungan,
    MAX(v.last_visit) as kunjungan_terakhir
FROM visitor v;

-- 12. Mendapatkan mahasiswa berdasarkan status
SELECT 
    status,
    COUNT(*) as jumlah_mahasiswa
FROM mahasiswa
GROUP BY status
ORDER BY jumlah_mahasiswa DESC;

-- 13. Mendapatkan progress per tahun
SELECT 
    tahun,
    COUNT(*) as jumlah_progress
FROM progress
WHERE tahun IS NOT NULL
GROUP BY tahun
ORDER BY tahun DESC;

-- 14. Mendapatkan produk yang belum memiliki resource
SELECT 
    p.id_produk,
    p.nama_produk,
    p.deskripsi,
    p.harga
FROM produk p
LEFT JOIN produk_resource pr ON p.id_produk = pr.id_produk
WHERE pr.id_produk IS NULL;

-- 15. Mendapatkan user dengan multiple roles (jika mahasiswa juga pengunjung)
SELECT 
    u.id_user,
    u.username,
    u.role,
    m.nama as nama_mahasiswa,
    m.status as status_mahasiswa,
    p.nama as nama_pengunjung,
    p.asal_institusi
FROM users u
LEFT JOIN mahasiswa m ON u.id_user = m.id_user
LEFT JOIN pengunjung p ON u.id_user = p.id_user
WHERE m.id_user IS NOT NULL AND p.id_user IS NOT NULL;

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

