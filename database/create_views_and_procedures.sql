-- ============================================
-- Create Views and Stored Procedures
-- ============================================
-- File ini berisi views dan stored procedures
-- yang digunakan oleh aplikasi web
-- 
-- Jalankan file ini di PostgreSQL:
-- psql -U postgres -d inlet_pbl -f database/create_views_and_procedures.sql
-- ============================================

-- ============================================
-- VIEWS
-- ============================================

-- View: view_alat_dipinjam
-- Menampilkan alat yang sedang dipinjam
CREATE OR REPLACE VIEW view_alat_dipinjam AS
SELECT 
    pj.id_peminjaman,
    pj.id_alat,
    alat.nama_alat,
    alat.deskripsi,
    pj.nama_peminjam,
    pj.tanggal_pinjam,
    pj.waktu_pinjam,
    pj.keterangan,
    pj.status
FROM peminjaman pj
JOIN alat_lab alat 
    ON alat.id_alat_lab = pj.id_alat
WHERE pj.status = 'dipinjam';

ALTER VIEW view_alat_dipinjam OWNER TO postgres;

-- View: view_alat_tersedia
-- Menampilkan alat dengan informasi stok tersedia
CREATE OR REPLACE VIEW view_alat_tersedia AS
SELECT 
    alat.id_alat_lab,
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
) pj ON pj.id_alat = alat.id_alat_lab;

ALTER VIEW view_alat_tersedia OWNER TO postgres;

-- ============================================
-- STORED PROCEDURES
-- ============================================

-- Function: tambah_artikel
-- Menambahkan artikel baru
CREATE OR REPLACE FUNCTION tambah_artikel(
    p_judul VARCHAR,
    p_tahun INT,
    p_konten VARCHAR
)
RETURNS VOID AS $$
BEGIN
    INSERT INTO artikel (judul, tahun, konten)
    VALUES (p_judul, p_tahun, p_konten);
END;
$$ LANGUAGE plpgsql;

ALTER FUNCTION tambah_artikel(VARCHAR, INT, VARCHAR) OWNER TO postgres;

-- Function: tambah_member
-- Menambahkan member baru
CREATE OR REPLACE FUNCTION tambah_member(
    p_nama VARCHAR,
    p_email VARCHAR,
    p_jabatan VARCHAR,
    p_foto VARCHAR,
    p_keahlian VARCHAR,
    p_notlp VARCHAR,
    p_deskripsi TEXT,
    p_alamat TEXT,
    p_id_admin INT
)
RETURNS VOID AS $$
BEGIN
    INSERT INTO member (
        nama, email, jabatan, foto, bidang_keahlian,
        notlp, deskripsi, alamat, id_admin
    )
    VALUES (
        p_nama, p_email, p_jabatan, p_foto, p_keahlian,
        p_notlp, p_deskripsi, p_alamat, p_id_admin
    );
END;
$$ LANGUAGE plpgsql;

ALTER FUNCTION tambah_member(VARCHAR, VARCHAR, VARCHAR, VARCHAR, VARCHAR, VARCHAR, TEXT, TEXT, INT) OWNER TO postgres;

-- ============================================
-- Selesai
-- ============================================
-- Views dan stored procedures telah dibuat
-- ============================================

