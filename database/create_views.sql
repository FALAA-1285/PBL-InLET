-- Script untuk membuat views yang diperlukan
-- Jalankan script ini jika view belum ada di database

-- View untuk melihat alat yang sedang dipinjam
CREATE OR REPLACE VIEW view_alat_dipinjam AS
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
    pj.created_at
FROM peminjaman pj
JOIN alat_lab alat 
    ON alat.id_alat = pj.id_alat
WHERE pj.status = 'dipinjam';

-- View untuk melihat alat yang tersedia dengan informasi stok
CREATE OR REPLACE VIEW view_alat_tersedia AS
SELECT 
    alat.id_alat,
    alat.nama_alat,
    alat.deskripsi,
    alat.stock,
    COALESCE(pj.jumlah_dipinjam, 0) AS jumlah_dipinjam,
    (alat.stock - COALESCE(pj.jumlah_dipinjam, 0)) AS stok_tersedia
FROM alat_lab alat
LEFT JOIN (
    SELECT id_alat, COUNT(*) AS jumlah_dipinjam
    FROM peminjaman
    WHERE status = 'dipinjam'
    GROUP BY id_alat
) pj ON pj.id_alat = alat.id_alat;

