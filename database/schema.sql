--
-- InLET (Information and Learning Engineering Technology)
-- Clean Production Database Schema for PostgreSQL
--

-- Enable UUID extension if needed
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- 1. Table: admin
CREATE TABLE IF NOT EXISTS admin (
    id_admin SERIAL PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'admin',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- 2. Table: settings
CREATE TABLE IF NOT EXISTS settings (
    id_setting SERIAL PRIMARY KEY,
    site_title VARCHAR(255),
    site_subtitle VARCHAR(255),
    site_logo VARCHAR(255),
    footer_logo VARCHAR(255),
    footer_title VARCHAR(255),
    copyright_text TEXT,
    contact_email VARCHAR(255),
    contact_phone VARCHAR(50),
    contact_address TEXT,
    page_titles JSONB,
    updated_by INTEGER REFERENCES admin(id_admin) ON DELETE SET NULL,
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 3. Table: contact_info
CREATE TABLE IF NOT EXISTS contact_info (
    id_contact SERIAL PRIMARY KEY,
    contact_email VARCHAR(255),
    contact_phone VARCHAR(50),
    contact_address VARCHAR(255)
);

-- 4. Table: footer_settings
CREATE TABLE IF NOT EXISTS footer_settings (
    id_footer SERIAL PRIMARY KEY,
    footer_logo VARCHAR(255),
    footer_title VARCHAR(255),
    footer_subtitle VARCHAR(255),
    copyright_text TEXT
);

-- 5. Table: member
CREATE TABLE IF NOT EXISTS member (
    id_member SERIAL PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    jabatan VARCHAR(100),
    foto VARCHAR(255),
    bidang_keahlian VARCHAR(255),
    notlp VARCHAR(50),
    deskripsi TEXT,
    alamat TEXT,
    google_scholar TEXT,
    id_admin INTEGER REFERENCES admin(id_admin) ON DELETE SET NULL
);

-- 6. Table: mitra
CREATE TABLE IF NOT EXISTS mitra (
    id_mitra SERIAL PRIMARY KEY,
    nama_institusi VARCHAR(255) NOT NULL,
    logo VARCHAR(255)
);

-- 7. Table: produk
CREATE TABLE IF NOT EXISTS produk (
    id_produk SERIAL PRIMARY KEY,
    nama_produk VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    gambar VARCHAR(255)
);

-- 8. Table: fokus_penelitian
CREATE TABLE IF NOT EXISTS fokus_penelitian (
    id_fp SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    detail TEXT
);

-- 9. Table: penelitian
CREATE TABLE IF NOT EXISTS penelitian (
    id_penelitian SERIAL PRIMARY KEY,
    id_artikel INTEGER,
    id_mhs INTEGER,
    judul VARCHAR(255) NOT NULL,
    tahun INTEGER,
    id_member INTEGER REFERENCES member(id_member) ON DELETE SET NULL,
    deskripsi TEXT,
    id_produk INTEGER REFERENCES produk(id_produk) ON DELETE SET NULL,
    id_mitra INTEGER REFERENCES mitra(id_mitra) ON DELETE SET NULL,
    tgl_mulai DATE,
    tgl_selesai DATE,
    id_fp INTEGER REFERENCES fokus_penelitian(id_fp) ON DELETE SET NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- 10. Table: artikel
CREATE TABLE IF NOT EXISTS artikel (
    id_artikel SERIAL PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    tahun INTEGER,
    konten TEXT,
    id_penelitian INTEGER REFERENCES penelitian(id_penelitian) ON DELETE SET NULL,
    nim VARCHAR(50),
    id_member INTEGER REFERENCES member(id_member) ON DELETE SET NULL,
    id_produk INTEGER REFERENCES produk(id_produk) ON DELETE SET NULL,
    id_mitra INTEGER REFERENCES mitra(id_mitra) ON DELETE SET NULL
);

-- 11. Table: berita
CREATE TABLE IF NOT EXISTS berita (
    id_berita SERIAL PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    konten TEXT,
    gambar_thumbnail TEXT,
    id_admin INTEGER REFERENCES admin(id_admin) ON DELETE SET NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- 12. Table: video
CREATE TABLE IF NOT EXISTS video (
    id_video SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    href_link TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- 13. Table: gallery
CREATE TABLE IF NOT EXISTS gallery (
    id_gallery SERIAL PRIMARY KEY,
    id_berita INTEGER REFERENCES berita(id_berita) ON DELETE SET NULL,
    gambar TEXT,
    judul VARCHAR(255),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- 14. Table: mahasiswa
CREATE TABLE IF NOT EXISTS mahasiswa (
    id_mahasiswa SERIAL PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    tahun INTEGER,
    status VARCHAR(50),
    id_admin INTEGER REFERENCES admin(id_admin) ON DELETE SET NULL
);

-- 15. Table: absensi
CREATE TABLE IF NOT EXISTS absensi (
    id_absensi SERIAL PRIMARY KEY,
    id_mhs INTEGER REFERENCES mahasiswa(id_mahasiswa) ON DELETE CASCADE,
    waktu_datang TIMESTAMP WITHOUT TIME ZONE,
    waktu_pulang TIMESTAMP WITHOUT TIME ZONE,
    keterangan TEXT,
    tanggal DATE DEFAULT CURRENT_DATE
);

-- 16. Table: buku_tamu
CREATE TABLE IF NOT EXISTS buku_tamu (
    id_buku_tamu SERIAL PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    institusi VARCHAR(255),
    no_hp VARCHAR(50),
    pesan TEXT,
    is_read BOOLEAN DEFAULT FALSE,
    admin_response TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- 17. Table: pengunjung
CREATE TABLE IF NOT EXISTS pengunjung (
    id_pengunjung SERIAL PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    asal_institusi VARCHAR(255),
    no_hp VARCHAR(50),
    pesan TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- 18. Table: visitor
CREATE TABLE IF NOT EXISTS visitor (
    id_visitor SERIAL PRIMARY KEY,
    id_pengunjung INTEGER REFERENCES pengunjung(id_pengunjung) ON DELETE CASCADE,
    visit_count INTEGER DEFAULT 0,
    first_visit TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    last_visit TIMESTAMP WITH TIME ZONE,
    keterangan VARCHAR(255),
    is_read BOOLEAN DEFAULT FALSE,
    admin_response TEXT
);

-- 19. Table: alat_lab
CREATE TABLE IF NOT EXISTS alat_lab (
    id_alat_lab SERIAL PRIMARY KEY,
    nama_alat VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    stock INTEGER DEFAULT 0,
    id_admin INTEGER REFERENCES admin(id_admin) ON DELETE SET NULL,
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 20. Table: ruang_lab
CREATE TABLE IF NOT EXISTS ruang_lab (
    id_ruang_lab SERIAL PRIMARY KEY,
    nama_ruang VARCHAR(255) NOT NULL,
    status VARCHAR(50) DEFAULT 'tersedia',
    id_admin INTEGER REFERENCES admin(id_admin) ON DELETE SET NULL,
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 21. Table: peminjaman
CREATE TABLE IF NOT EXISTS peminjaman (
    id_peminjaman SERIAL PRIMARY KEY,
    id_alat INTEGER REFERENCES alat_lab(id_alat_lab) ON DELETE CASCADE,
    id_ruang INTEGER REFERENCES ruang_lab(id_ruang_lab) ON DELETE SET NULL,
    nama_peminjam VARCHAR(255) NOT NULL,
    tanggal_pinjam DATE NOT NULL,
    tanggal_kembali DATE,
    waktu_pinjam TIME WITHOUT TIME ZONE,
    waktu_kembali TIME WITHOUT TIME ZONE,
    status VARCHAR(50) DEFAULT 'dipinjam',
    keterangan TEXT,
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- 22. Database Views
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
    pj.created_at,
    pj.id_ruang
FROM peminjaman pj
JOIN alat_lab alat ON alat.id_alat_lab = pj.id_alat
WHERE pj.status = 'dipinjam';

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

CREATE OR REPLACE VIEW view_ruang_dipinjam AS
SELECT 
    pj.id_peminjaman,
    pj.id_ruang,
    ruang.nama_ruang,
    ruang.status AS status_ruang,
    pj.nama_peminjam,
    pj.tanggal_pinjam,
    pj.tanggal_kembali,
    pj.waktu_pinjam,
    pj.waktu_kembali,
    pj.keterangan,
    pj.status,
    pj.created_at
FROM peminjaman pj
JOIN ruang_lab ruang ON ruang.id_ruang_lab = pj.id_ruang
WHERE pj.status = 'dipinjam';


-- 23. Default Initial Seed Data (Safe for fresh Supabase database)
INSERT INTO admin (username, password_hash, role)
VALUES ('admin', '$2y$12$fiOX/y2dpexzDxaTIVmVpePmp8d276ieNr8Y8hsI5UoLdliQFTgxq', 'admin')
ON CONFLICT (username) DO NOTHING;

INSERT INTO settings (id_setting, site_title, site_subtitle, site_logo, footer_logo, copyright_text)
VALUES (
    1, 
    'InLET - Information And Learning Engineering Technology', 
    'State Polytechnic of Malang', 
    'assets/logo.png', 
    'assets/logoPutih.png', 
    'Copyright © InLET. All Rights Reserved.'
)
ON CONFLICT (id_setting) DO NOTHING;
