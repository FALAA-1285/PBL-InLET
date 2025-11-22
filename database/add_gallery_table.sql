-- -------------------------------------------------------
-- Tambahan tabel untuk sistem gallery
-- -------------------------------------------------------

-- Tabel gallery untuk menyimpan gambar gallery
CREATE TABLE IF NOT EXISTS gallery (
    id_gallery SERIAL PRIMARY KEY,
    gambar VARCHAR(500) NOT NULL,
    judul VARCHAR(255),
    deskripsi VARCHAR(1000),
    urutan INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- Indeks untuk performa query
CREATE INDEX IF NOT EXISTS idx_gallery_urutan ON gallery(urutan);
CREATE INDEX IF NOT EXISTS idx_gallery_created ON gallery(created_at);

-- -------------------------------------------------------
-- Catatan:
-- 1. Kolom gambar: bisa URL atau path file lokal
-- 2. urutan: untuk mengatur urutan tampilan (0 = default)
-- 3. created_at dan updated_at: untuk tracking waktu
-- -------------------------------------------------------

