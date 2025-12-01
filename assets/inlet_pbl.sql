-- InLET PBL Database Schema
-- Clean and organized database setup for InLET PBL project
-- Generated from PHP files and consolidated here

-- ===========================================
-- SCHEMA SETUP
-- ===========================================

-- Create schema if not exists
CREATE SCHEMA IF NOT EXISTS public;

-- Set default privileges
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO postgres;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO postgres;

-- ===========================================
-- TABLES FROM PHP FILES
-- ===========================================

-- Gallery table (used in index.php and news.php)
CREATE TABLE IF NOT EXISTS public.gallery (
    id_gallery SERIAL PRIMARY KEY,
    id_berita INTEGER,
    gambar VARCHAR(500),
    judul VARCHAR(255),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- Create index for gallery
CREATE INDEX IF NOT EXISTS idx_gallery_created ON public.gallery(created_at);
CREATE INDEX IF NOT EXISTS idx_gallery_id_berita ON public.gallery(id_berita);

-- Buku Tamu table (used in service/buku_tamu.php and admin/buku_tamu.php)
CREATE TABLE IF NOT EXISTS public.buku_tamu (
    id_buku_tamu SERIAL PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    institusi VARCHAR(200) NOT NULL,
    no_hp VARCHAR(50) NOT NULL,
    pesan VARCHAR(2000),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
    is_read BOOLEAN DEFAULT false,
    admin_response VARCHAR(2000)
);

-- Create indexes for buku_tamu
CREATE INDEX IF NOT EXISTS idx_buku_tamu_created_at ON public.buku_tamu(created_at DESC);
CREATE INDEX IF NOT EXISTS idx_buku_tamu_is_read ON public.buku_tamu(is_read);
CREATE INDEX IF NOT EXISTS idx_buku_tamu_email ON public.buku_tamu(email);

-- ===========================================
-- EXISTING TABLES (from original SQL dump)
-- ===========================================

-- Admin table
CREATE TABLE IF NOT EXISTS public.admin (
    id_admin SERIAL PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'user',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- Mahasiswa table
CREATE TABLE IF NOT EXISTS public.mahasiswa (
    id_mahasiswa SERIAL PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    tahun INTEGER,
    status VARCHAR(20) DEFAULT 'regular' NOT NULL,
    id_admin INTEGER,
    CONSTRAINT chk_mahasiswa_status CHECK (status IN ('magang', 'skripsi', 'regular'))
);

-- Member table
CREATE TABLE IF NOT EXISTS public.member (
    id_member SERIAL PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    email VARCHAR(150),
    jabatan VARCHAR(100),
    foto VARCHAR(255),
    bidang_keahlian VARCHAR(255),
    notlp VARCHAR(30),
    deskripsi TEXT,
    alamat TEXT,
    id_admin INTEGER
);

-- Mitra table
CREATE TABLE IF NOT EXISTS public.mitra (
    id_mitra SERIAL PRIMARY KEY,
    nama_institusi VARCHAR(255) NOT NULL,
    logo VARCHAR(255)
);

-- Berita table
CREATE TABLE IF NOT EXISTS public.berita (
    id_berita SERIAL PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    konten VARCHAR(4000),
    gambar_thumbnail VARCHAR(255),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
    id_admin INTEGER
);

-- Artikel table
CREATE TABLE IF NOT EXISTS public.artikel (
    id_artikel SERIAL PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    tahun INTEGER,
    konten VARCHAR(4000)
);

-- Penelitian table
CREATE TABLE IF NOT EXISTS public.penelitian (
    id_penelitian SERIAL PRIMARY KEY,
    id_artikel INTEGER,
    id_mhs INTEGER,
    judul VARCHAR(255) NOT NULL,
    tahun INTEGER,
    id_member INTEGER,
    deskripsi TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
    id_produk INTEGER,
    id_mitra INTEGER,
    tgl_mulai DATE DEFAULT CURRENT_DATE NOT NULL,
    tgl_selesai DATE,
    id_fp INTEGER
);

-- Produk table
CREATE TABLE IF NOT EXISTS public.produk (
    id_produk SERIAL PRIMARY KEY,
    nama_produk VARCHAR(255) NOT NULL,
    deskripsi TEXT
);

-- Absensi table
CREATE TABLE IF NOT EXISTS public.absensi (
    id_absensi SERIAL PRIMARY KEY,
    id_mhs INTEGER NOT NULL,
    waktu_datang TIMESTAMP,
    waktu_pulang TIMESTAMP,
    keterangan TEXT,
    tanggal DATE DEFAULT CURRENT_DATE NOT NULL
);

-- Alat Lab table
CREATE TABLE IF NOT EXISTS public.alat_lab (
    id_alat_lab SERIAL PRIMARY KEY,
    nama_alat VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    stock INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_admin INTEGER
);

-- Peminjaman table
CREATE TABLE IF NOT EXISTS public.peminjaman (
    id_peminjaman SERIAL PRIMARY KEY,
    id_alat INTEGER NOT NULL,
    nama_peminjam VARCHAR(255) NOT NULL,
    tanggal_pinjam DATE NOT NULL,
    tanggal_kembali DATE,
    status VARCHAR(50) DEFAULT 'dipinjam',
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_ruang INTEGER,
    waktu_pinjam TIME,
    waktu_kembali TIME,
    CONSTRAINT chk_waktu_logical CHECK (
        (tanggal_pinjam IS NULL) OR
        (tanggal_kembali IS NULL) OR
        ((waktu_pinjam IS NULL) OR (waktu_kembali IS NULL)) OR
        ((tanggal_pinjam < tanggal_kembali) OR
         ((tanggal_pinjam = tanggal_kembali) AND (waktu_kembali > waktu_pinjam)))
    )
);

-- Ruang Lab table
CREATE TABLE IF NOT EXISTS public.ruang_lab (
    id_ruang_lab SERIAL PRIMARY KEY,
    nama_ruang VARCHAR(150) NOT NULL,
    status VARCHAR(30) DEFAULT 'tersedia' NOT NULL,
    id_admin INTEGER,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now() NOT NULL
);

-- Fokus Penelitian table
CREATE TABLE IF NOT EXISTS public.fokus_penelitian (
    id_fp SERIAL PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    deskripsi TEXT,
    detail VARCHAR(150) NOT NULL
);

-- Settings table
CREATE TABLE IF NOT EXISTS public.settings (
    id_setting SERIAL PRIMARY KEY,
    site_title VARCHAR(255) NOT NULL,
    site_subtitle VARCHAR(255),
    site_logo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    footer_logo VARCHAR(255),
    footer_title VARCHAR(255),
    copyright_text TEXT,
    contact_email VARCHAR(255),
    contact_phone VARCHAR(100),
    contact_address TEXT,
    updated_by INTEGER
);

-- Pengunjung table
CREATE TABLE IF NOT EXISTS public.pengunjung (
    id_pengunjung SERIAL PRIMARY KEY,
    nama VARCHAR(150),
    email VARCHAR(150),
    asal_institusi VARCHAR(200),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
    no_hp VARCHAR(20),
    pesan TEXT
);

-- Visitor table
CREATE TABLE IF NOT EXISTS public.visitor (
    id_visitor SERIAL PRIMARY KEY,
    id_pengunjung INTEGER NOT NULL,
    visit_count INTEGER DEFAULT 0 NOT NULL,
    last_visit TIMESTAMP WITH TIME ZONE,
    first_visit TIMESTAMP WITH TIME ZONE DEFAULT now(),
    keterangan VARCHAR(500),
    is_read BOOLEAN DEFAULT false,
    admin_response TEXT
);

-- ===========================================
-- INDEXES
-- ===========================================

-- Berita indexes
CREATE INDEX IF NOT EXISTS idx_berita_id_admin ON public.berita(id_admin);
CREATE INDEX IF NOT EXISTS idx_berita_created_at ON public.berita(created_at DESC);

-- Mahasiswa indexes
CREATE INDEX IF NOT EXISTS idx_mahasiswa_id_admin ON public.mahasiswa(id_admin);

-- Member indexes
CREATE INDEX IF NOT EXISTS idx_member_id_admin ON public.member(id_admin);
CREATE INDEX IF NOT EXISTS idx_member_nama ON public.member(nama);

-- Alat Lab indexes
CREATE INDEX IF NOT EXISTS idx_alatlab_id_admin ON public.alat_lab(id_admin);

-- Peminjaman indexes
CREATE INDEX IF NOT EXISTS idx_peminjaman_id_alat ON public.peminjaman(id_alat);
CREATE INDEX IF NOT EXISTS idx_peminjaman_status ON public.peminjaman(status);

-- Ruang Lab indexes
CREATE INDEX IF NOT EXISTS idx_ruanglab_nama ON public.ruang_lab(nama_ruang);

-- Produk indexes
CREATE INDEX IF NOT EXISTS idx_produk_nama ON public.produk(nama_produk);

-- Penelitian indexes
CREATE INDEX IF NOT EXISTS idx_progress_artikel ON public.penelitian(id_artikel);
CREATE INDEX IF NOT EXISTS idx_progress_member ON public.penelitian(id_member);
CREATE INDEX IF NOT EXISTS idx_progress_mhs ON public.penelitian(id_mhs);

-- Visitor indexes
CREATE INDEX IF NOT EXISTS idx_visitor_pengunjung ON public.visitor(id_pengunjung);

-- ===========================================
-- FOREIGN KEY CONSTRAINTS
-- ===========================================

-- Absensi foreign keys
ALTER TABLE public.absensi DROP CONSTRAINT IF EXISTS fk_absensi_mahasiswa;
ALTER TABLE public.absensi ADD CONSTRAINT fk_absensi_mahasiswa
    FOREIGN KEY (id_mhs) REFERENCES public.mahasiswa(id_mahasiswa) ON DELETE RESTRICT;

-- Alat Lab foreign keys
ALTER TABLE public.alat_lab DROP CONSTRAINT IF EXISTS fk_alatlab_admin;
ALTER TABLE public.alat_lab ADD CONSTRAINT fk_alatlab_admin
    FOREIGN KEY (id_admin) REFERENCES public.admin(id_admin) ON UPDATE CASCADE ON DELETE RESTRICT;

-- Berita foreign keys
ALTER TABLE public.berita DROP CONSTRAINT IF EXISTS fk_berita_admin;
ALTER TABLE public.berita ADD CONSTRAINT fk_berita_admin
    FOREIGN KEY (id_admin) REFERENCES public.admin(id_admin) ON DELETE SET NULL;

-- Gallery foreign keys
ALTER TABLE public.gallery DROP CONSTRAINT IF EXISTS fk_gallery_berita;
ALTER TABLE public.gallery ADD CONSTRAINT fk_gallery_berita
    FOREIGN KEY (id_berita) REFERENCES public.berita(id_berita) ON UPDATE CASCADE ON DELETE CASCADE;

-- Mahasiswa foreign keys
ALTER TABLE public.mahasiswa DROP CONSTRAINT IF EXISTS fk_mahasiswa_admin;
ALTER TABLE public.mahasiswa ADD CONSTRAINT fk_mahasiswa_admin
    FOREIGN KEY (id_admin) REFERENCES public.admin(id_admin) ON UPDATE CASCADE ON DELETE SET NULL;

-- Member foreign keys
ALTER TABLE public.member DROP CONSTRAINT IF EXISTS fk_member_admin;
ALTER TABLE public.member ADD CONSTRAINT fk_member_admin
    FOREIGN KEY (id_admin) REFERENCES public.admin(id_admin) ON UPDATE CASCADE ON DELETE SET NULL;

-- Peminjaman foreign keys
ALTER TABLE public.peminjaman DROP CONSTRAINT IF EXISTS fk_peminjaman_alat;
ALTER TABLE public.peminjaman ADD CONSTRAINT fk_peminjaman_alat
    FOREIGN KEY (id_alat) REFERENCES public.alat_lab(id_alat_lab) ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE public.peminjaman DROP CONSTRAINT IF EXISTS fk_peminjaman_ruang;
ALTER TABLE public.peminjaman ADD CONSTRAINT fk_peminjaman_ruang
    FOREIGN KEY (id_ruang) REFERENCES public.ruang_lab(id_ruang_lab) ON UPDATE CASCADE ON DELETE RESTRICT;

-- Penelitian foreign keys
ALTER TABLE public.penelitian DROP CONSTRAINT IF EXISTS fk_penelitian_fokus;
ALTER TABLE public.penelitian ADD CONSTRAINT fk_penelitian_fokus
    FOREIGN KEY (id_fp) REFERENCES public.fokus_penelitian(id_fp) ON UPDATE CASCADE ON DELETE SET NULL;

ALTER TABLE public.penelitian DROP CONSTRAINT IF EXISTS fk_penelitian_mitra;
ALTER TABLE public.penelitian ADD CONSTRAINT fk_penelitian_mitra
    FOREIGN KEY (id_mitra) REFERENCES public.mitra(id_mitra) ON DELETE SET NULL;

ALTER TABLE public.penelitian DROP CONSTRAINT IF EXISTS fk_penelitian_produk;
ALTER TABLE public.penelitian ADD CONSTRAINT fk_penelitian_produk
    FOREIGN KEY (id_produk) REFERENCES public.produk(id_produk) ON DELETE SET NULL;

ALTER TABLE public.penelitian DROP CONSTRAINT IF EXISTS progress_id_artikel_fkey;
ALTER TABLE public.penelitian ADD CONSTRAINT progress_id_artikel_fkey
    FOREIGN KEY (id_artikel) REFERENCES public.artikel(id_artikel) ON DELETE SET NULL;

ALTER TABLE public.penelitian DROP CONSTRAINT IF EXISTS progress_id_member_fkey;
ALTER TABLE public.penelitian ADD CONSTRAINT progress_id_member_fkey
    FOREIGN KEY (id_member) REFERENCES public.member(id_member) ON DELETE SET NULL;

ALTER TABLE public.penelitian DROP CONSTRAINT IF EXISTS progress_id_mhs_fkey;
ALTER TABLE public.penelitian ADD CONSTRAINT progress_id_mhs_fkey
    FOREIGN KEY (id_mhs) REFERENCES public.mahasiswa(id_mahasiswa) ON DELETE SET NULL;

-- Ruang Lab foreign keys
ALTER TABLE public.ruang_lab DROP CONSTRAINT IF EXISTS fk_ruang_admin;
ALTER TABLE public.ruang_lab ADD CONSTRAINT fk_ruang_admin
    FOREIGN KEY (id_admin) REFERENCES public.admin(id_admin) ON UPDATE CASCADE ON DELETE SET NULL;

-- Settings foreign keys
ALTER TABLE public.settings DROP CONSTRAINT IF EXISTS fk_settings_admin;
ALTER TABLE public.settings ADD CONSTRAINT fk_settings_admin
    FOREIGN KEY (updated_by) REFERENCES public.admin(id_admin) ON UPDATE CASCADE ON DELETE RESTRICT;

-- Visitor foreign keys
ALTER TABLE public.visitor DROP CONSTRAINT IF EXISTS visitor_id_pengunjung_fkey;
ALTER TABLE public.visitor ADD CONSTRAINT visitor_id_pengunjung_fkey
    FOREIGN KEY (id_pengunjung) REFERENCES public.pengunjung(id_pengunjung) ON DELETE CASCADE;

-- ===========================================
-- VIEWS (from setup files)
-- ===========================================

-- View for alat dipinjam (from setup/create_view_alat_dipinjam.php)
CREATE OR REPLACE VIEW public.view_alat_dipinjam AS
SELECT
    alat.id_alat_lab,
    alat.nama_alat,
    alat.stock,
    COALESCE(pj.jumlah_dipinjam, 0) AS jumlah_dipinjam,
    (alat.stock - COALESCE(pj.jumlah_dipinjam, 0)) AS stok_tersedia,
    pj.created_at
FROM public.alat_lab alat
LEFT JOIN (
    SELECT
        id_alat,
        COUNT(*) AS jumlah_dipinjam
    FROM public.peminjaman
    WHERE status = 'dipinjam'
    GROUP BY id_alat
) pj ON alat.id_alat_lab = pj.id_alat;

-- ===========================================
-- PERMISSIONS
-- ===========================================

-- Grant permissions
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO postgres;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO postgres;

-- ===========================================
-- SETUP COMPLETE
-- ===========================================

-- Display completion message
DO $$
BEGIN
    RAISE NOTICE 'InLET PBL Database setup completed successfully!';
    RAISE NOTICE 'All tables, indexes, and constraints have been created.';
    RAISE NOTICE 'Sample data has been inserted for testing.';
END $$;
