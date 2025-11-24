-- -------------------------------------------------------
-- Tabel Buku Tamu (Guestbook)
-- Untuk menyimpan pesan dari pengunjung/tamu
-- -------------------------------------------------------

CREATE TABLE IF NOT EXISTS buku_tamu (
    id_buku_tamu SERIAL PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    institusi VARCHAR(200),
    no_hp VARCHAR(50),
    pesan VARCHAR(2000),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
    is_read BOOLEAN DEFAULT false,
    admin_response VARCHAR(2000)
);

-- Indeks untuk performa query
CREATE INDEX IF NOT EXISTS idx_buku_tamu_created_at ON buku_tamu(created_at DESC);
CREATE INDEX IF NOT EXISTS idx_buku_tamu_is_read ON buku_tamu(is_read);
CREATE INDEX IF NOT EXISTS idx_buku_tamu_email ON buku_tamu(email);

-- -------------------------------------------------------
-- Catatan:
-- 1. nama: Nama pengunjung/tamu
-- 2. email: Email pengunjung (untuk follow up jika perlu)
-- 3. institusi: Asal institusi pengunjung
-- 4. no_hp: Nomor HP pengunjung
-- 5. pesan: Pesan yang ingin disampaikan pengunjung (opsional, bisa untuk daftar hadir saja)
-- 6. created_at: Waktu pesan dibuat
-- 7. is_read: Status apakah pesan sudah dibaca admin
-- 8. admin_response: Respon dari admin (opsional)
-- -------------------------------------------------------

