-- -------------------------------------------------------
-- Skema database (PostgreSQL) - revisi sesuai permintaan
-- - semua TEXT -> VARCHAR(...)
-- - tambah tabel visitor untuk jumlah kunjungan
-- - mahasiswa dapat login (id_user) dan punya status (magang / skripsi)
-- -------------------------------------------------------

-- 1) Tabel users (untuk login)
CREATE TABLE users (
    id_user        SERIAL PRIMARY KEY,
    username       VARCHAR(100) NOT NULL UNIQUE,
    password_hash  VARCHAR(255) NOT NULL, -- simpan hash (bcrypt/argon2)
    role           VARCHAR(50) DEFAULT 'user', -- optional: admin/visitor/member/dll
    created_at     TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- 2) Tabel admin (1-to-1 dengan users)
CREATE TABLE admin (
    id_admin   SERIAL PRIMARY KEY,
    id_user    INTEGER NOT NULL UNIQUE REFERENCES users(id_user) ON DELETE CASCADE,
    nama       VARCHAR(150) NOT NULL,
    email      VARCHAR(150),
    phone      VARCHAR(50),
    foto       VARCHAR(255),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- 3) Tabel pengunjung (1-to-1 dengan users) -- pengunjung yang login
CREATE TABLE pengunjung (
    id_pengunjung SERIAL PRIMARY KEY,
    id_user       INTEGER NOT NULL UNIQUE REFERENCES users(id_user) ON DELETE CASCADE,
    nama          VARCHAR(150),
    email         VARCHAR(150),
    asal_institusi VARCHAR(200),
    created_at    TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- 4) Tabel mahasiswa (sekarang bisa login lewat users)
CREATE TABLE mahasiswa (
    id_mhs    SERIAL PRIMARY KEY,
    id_user   INTEGER UNIQUE REFERENCES users(id_user) ON DELETE SET NULL, -- mahasiswa bisa memiliki akun login
    nama      VARCHAR(150) NOT NULL,
    title     VARCHAR(200),
    tahun     INTEGER,
    status    VARCHAR(20) NOT NULL DEFAULT 'regular', -- 'magang' | 'skripsi' | 'regular'
    CONSTRAINT chk_mahasiswa_status CHECK (status IN ('magang','skripsi','regular'))
);

-- 5) Tabel member (entitas anggota)
CREATE TABLE member (
    id_member SERIAL PRIMARY KEY,
    nama      VARCHAR(150) NOT NULL,
    email     VARCHAR(150),
    jabatan   VARCHAR(100),
    foto      VARCHAR(255)
);

-- 6) Profil member (1-to-1 dengan member)
CREATE TABLE profil_member (
    id_profile SERIAL PRIMARY KEY,
    id_member  INTEGER NOT NULL UNIQUE REFERENCES member(id_member) ON DELETE CASCADE,
    alamat     VARCHAR(400),
    no_tlp     VARCHAR(50),
    deskripsi  VARCHAR(2000)
);

-- 7) Artikel
CREATE TABLE artikel (
    id_artikel SERIAL PRIMARY KEY,
    judul      VARCHAR(255) NOT NULL,
    tahun      INTEGER,
    konten     VARCHAR(4000)
);

-- 8) Berita
CREATE TABLE berita (
    id_berita SERIAL PRIMARY KEY,
    judul      VARCHAR(255) NOT NULL,
    konten     VARCHAR(4000),
    gambar_thumbnail VARCHAR(255),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- 9) Progress (menghubungkan member, artikel, mahasiswa dsb.)
CREATE TABLE progress (
    id_progress SERIAL PRIMARY KEY,
    id_artikel  INTEGER REFERENCES artikel(id_artikel) ON DELETE SET NULL,
    id_mhs      INTEGER REFERENCES mahasiswa(id_mhs) ON DELETE SET NULL,
    judul       VARCHAR(255) NOT NULL,
    tahun       INTEGER,
    id_member   INTEGER REFERENCES member(id_member) ON DELETE SET NULL,
    deskripsi   VARCHAR(2000),
    created_at  TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- 10) Mitra
CREATE TABLE mitra (
    id_mitra SERIAL PRIMARY KEY,
    nama_institusi VARCHAR(255) NOT NULL,
    logo VARCHAR(255)
);

-- 11) Produk
CREATE TABLE produk (
    id_produk SERIAL PRIMARY KEY,
    nama_produk VARCHAR(255) NOT NULL,
    deskripsi   VARCHAR(2000),
    harga       NUMERIC(14,2)
);

-- 12) Resource
CREATE TABLE resource (
    id_resource SERIAL PRIMARY KEY,
    judul       VARCHAR(255) NOT NULL,
    deskripsi   VARCHAR(2000),
    gambar      VARCHAR(255)
);

-- 13) Produk_resource (many-to-many antara produk & resource)
CREATE TABLE produk_resource (
    id_produk_resource SERIAL PRIMARY KEY,
    id_produk  INTEGER NOT NULL REFERENCES produk(id_produk) ON DELETE CASCADE,
    id_resource INTEGER NOT NULL REFERENCES resource(id_resource) ON DELETE CASCADE,
    keterangan  VARCHAR(1000),
    UNIQUE (id_produk, id_resource)
);

-- 14) Visitor (penyimpan jumlah hasil kunjungan web berdasarkan pengunjung yang melakukan login)
--    Setiap row menunjukkan agregat kunjungan untuk satu pengunjung (login).
CREATE TABLE visitor (
    id_visitor SERIAL PRIMARY KEY,
    id_pengunjung INTEGER NOT NULL REFERENCES pengunjung(id_pengunjung) ON DELETE CASCADE,
    visit_count  INTEGER NOT NULL DEFAULT 0,
    last_visit   TIMESTAMP WITH TIME ZONE,
    first_visit  TIMESTAMP WITH TIME ZONE DEFAULT now(),
    keterangan   VARCHAR(500)
);

-- 15) Indeks yang berguna
CREATE INDEX idx_progress_member ON progress(id_member);
CREATE INDEX idx_progress_mhs ON progress(id_mhs);
CREATE INDEX idx_progress_artikel ON progress(id_artikel);
CREATE INDEX idx_produk_nama ON produk(nama_produk);
CREATE INDEX idx_resource_judul ON resource(judul);
CREATE INDEX idx_pengunjung_user ON pengunjung(id_user);
CREATE INDEX idx_mahasiswa_user ON mahasiswa(id_user);
CREATE INDEX idx_visitor_pengunjung ON visitor(id_pengunjung);

-- -------------------------------------------------------
-- Catatan desain & penjelasan singkat:
-- 1) Mahasiswa sekarang dapat memiliki akun login:
--    - Jika mahasiswa punya akun, isi mahasiswa.id_user dengan users.id_user.
--    - Jika mahasiswa juga ingin berperan sebagai "pengunjung", Anda dapat
--      membuat baris di tabel pengunjung yang merefer ke users.id_user yang sama.
--      (Jadi 1 user bisa memiliki profile mahasiswa dan profile pengunjung.)
-- 2) Tabel pengunjung berisi data profil pengunjung (nama/email/asal_institusi).
--    Tabel visitor menyimpan agregat kunjungan per pengunjung (visit_count, last_visit).
-- 3) Semua kolom yang sebelumnya TEXT telah diganti menjadi VARCHAR dengan panjang wajar.
-- 4) Penggunaan UNIQUE pada mahasiswa.id_user dan pengunjung.id_user memastikan satu akun user dipetakan satu entitas.
-- 5) Jika Anda ingin mahasiswa otomatis menjadi pengunjung saat mendaftar, buat proses aplikasi yang:
--    - Buat users -> id_user
--    - Buat mahasiswa (id_user = id_user)
--    - Buat pengunjung (id_user = id_user) -- dan inisialisasi visitor record
-- 6) Untuk audit/riwayat kunjungan yang lebih rinci, buat tabel visitor_log yang mencatat setiap hit (timestamp, path, ip, user_agent).
-- -------------------------------------------------------

