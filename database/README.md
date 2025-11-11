# Database Schema Documentation

## Overview
Skema database PostgreSQL untuk sistem web dengan fitur autentikasi, manajemen mahasiswa, artikel, berita, dan tracking visitor.

## Struktur Database

### Tabel Utama

#### 1. **users** - Tabel Autentikasi
- **Primary Key**: `id_user` (SERIAL)
- **Fungsi**: Menyimpan data login semua pengguna
- **Kolom Penting**:
  - `username`: VARCHAR(100), UNIQUE
  - `password_hash`: VARCHAR(255) - untuk menyimpan hash password (bcrypt/argon2)
  - `role`: VARCHAR(50) - default 'user' (bisa: admin, visitor, member, dll)
  - `created_at`: TIMESTAMP

#### 2. **admin** - Profil Administrator
- **Primary Key**: `id_admin` (SERIAL)
- **Foreign Key**: `id_user` → users(id_user) [1-to-1, UNIQUE]
- **Fungsi**: Data profil admin yang memiliki akun login
- **Kolom**: nama, email, phone, foto

#### 3. **pengunjung** - Profil Pengunjung yang Login
- **Primary Key**: `id_pengunjung` (SERIAL)
- **Foreign Key**: `id_user` → users(id_user) [1-to-1, UNIQUE]
- **Fungsi**: Data profil pengunjung yang memiliki akun
- **Kolom**: nama, email, asal_institusi

#### 4. **mahasiswa** - Data Mahasiswa
- **Primary Key**: `id_mhs` (SERIAL)
- **Foreign Key**: `id_user` → users(id_user) [1-to-1, UNIQUE, nullable]
- **Fungsi**: Data mahasiswa yang bisa memiliki akun login
- **Kolom Penting**:
  - `status`: VARCHAR(20) - 'magang' | 'skripsi' | 'regular' (dengan CHECK constraint)
  - `nama`, `title`, `tahun`

#### 5. **member** - Anggota Tim
- **Primary Key**: `id_member` (SERIAL)
- **Fungsi**: Data anggota tim/dosen
- **Kolom**: nama, email, jabatan, foto

#### 6. **profil_member** - Detail Profil Member
- **Primary Key**: `id_profile` (SERIAL)
- **Foreign Key**: `id_member` → member(id_member) [1-to-1, UNIQUE]
- **Fungsi**: Informasi detail tambahan untuk member
- **Kolom**: alamat, no_tlp, deskripsi

#### 7. **artikel** - Artikel/Paper
- **Primary Key**: `id_artikel` (SERIAL)
- **Fungsi**: Menyimpan artikel penelitian
- **Kolom**: judul, tahun, konten

#### 8. **berita** - Berita/News
- **Primary Key**: `id_berita` (SERIAL)
- **Fungsi**: Menyimpan berita/update
- **Kolom**: judul, konten, gambar_thumbnail, created_at

#### 9. **progress** - Progress Penelitian
- **Primary Key**: `id_progress` (SERIAL)
- **Foreign Keys**:
  - `id_artikel` → artikel(id_artikel) [nullable]
  - `id_mhs` → mahasiswa(id_mhs) [nullable]
  - `id_member` → member(id_member) [nullable]
- **Fungsi**: Menghubungkan progress dengan artikel, mahasiswa, dan member
- **Kolom**: judul, tahun, deskripsi, created_at

#### 10. **mitra** - Mitra Kerja Sama
- **Primary Key**: `id_mitra` (SERIAL)
- **Fungsi**: Data institusi mitra
- **Kolom**: nama_institusi, logo

#### 11. **produk** - Produk
- **Primary Key**: `id_produk` (SERIAL)
- **Fungsi**: Data produk yang ditawarkan
- **Kolom**: nama_produk, deskripsi, harga (NUMERIC)

#### 12. **resource** - Resource/Dokumen
- **Primary Key**: `id_resource` (SERIAL)
- **Fungsi**: Resource/dokumen pendukung
- **Kolom**: judul, deskripsi, gambar

#### 13. **produk_resource** - Relasi Many-to-Many
- **Primary Key**: `id_produk_resource` (SERIAL)
- **Foreign Keys**:
  - `id_produk` → produk(id_produk)
  - `id_resource` → resource(id_resource)
- **Fungsi**: Menghubungkan produk dengan resource (many-to-many)
- **Constraint**: UNIQUE (id_produk, id_resource)

#### 14. **visitor** - Tracking Kunjungan
- **Primary Key**: `id_visitor` (SERIAL)
- **Foreign Key**: `id_pengunjung` → pengunjung(id_pengunjung)
- **Fungsi**: Menyimpan agregat jumlah kunjungan per pengunjung yang login
- **Kolom**:
  - `visit_count`: INTEGER - jumlah kunjungan
  - `last_visit`: TIMESTAMP - kunjungan terakhir
  - `first_visit`: TIMESTAMP - kunjungan pertama
  - `keterangan`: VARCHAR(500)

## Indeks Database

Indeks yang telah dibuat untuk optimasi query:
- `idx_progress_member` - pada progress(id_member)
- `idx_progress_mhs` - pada progress(id_mhs)
- `idx_progress_artikel` - pada progress(id_artikel)
- `idx_produk_nama` - pada produk(nama_produk)
- `idx_resource_judul` - pada resource(judul)
- `idx_pengunjung_user` - pada pengunjung(id_user)
- `idx_mahasiswa_user` - pada mahasiswa(id_user)
- `idx_visitor_pengunjung` - pada visitor(id_pengunjung)

## Relasi Penting

### Autentikasi & User Management
```
users (1) ──< (1) admin
users (1) ──< (1) pengunjung
users (1) ──< (0..1) mahasiswa
```

### Content Management
```
member (1) ──< (1) profil_member
artikel (1) ──< (*) progress
mahasiswa (1) ──< (*) progress
member (1) ──< (*) progress
```

### Product & Resource
```
produk (*) ──< (*) resource (via produk_resource)
```

### Visitor Tracking
```
pengunjung (1) ──< (1) visitor
```

## Catatan Desain

### 1. Mahasiswa dengan Akun Login
- Mahasiswa dapat memiliki akun login melalui `mahasiswa.id_user`
- Satu mahasiswa bisa memiliki akun user (nullable)
- Jika mahasiswa juga ingin berperan sebagai pengunjung, bisa dibuat record di tabel `pengunjung` dengan `id_user` yang sama

### 2. Visitor Tracking
- Tabel `pengunjung` menyimpan profil pengunjung yang login
- Tabel `visitor` menyimpan agregat kunjungan (visit_count, last_visit, first_visit)
- Untuk tracking lebih detail, bisa ditambahkan tabel `visitor_log` yang mencatat setiap hit (timestamp, path, IP, user_agent)

### 3. Tipe Data
- Semua kolom yang sebelumnya TEXT telah diganti menjadi VARCHAR dengan panjang yang wajar
- Menggunakan TIMESTAMP WITH TIME ZONE untuk semua kolom waktu

### 4. Constraints
- UNIQUE constraint pada `mahasiswa.id_user` dan `pengunjung.id_user` memastikan satu akun user dipetakan ke satu entitas
- CHECK constraint pada `mahasiswa.status` memastikan nilai hanya: 'magang', 'skripsi', atau 'regular'

## Cara Menggunakan

### 1. Import Schema
```bash
psql -U username -d database_name -f database/schema.sql
```

### 2. Setup Awal
Saat mahasiswa mendaftar dan ingin memiliki akun:
1. Buat record di `users` → dapatkan `id_user`
2. Buat record di `mahasiswa` dengan `id_user` yang sama
3. (Opsional) Buat record di `pengunjung` dengan `id_user` yang sama
4. (Opsional) Inisialisasi record di `visitor` untuk tracking

### 3. Contoh Query

**Mendapatkan mahasiswa dengan akun login:**
```sql
SELECT m.*, u.username, u.role
FROM mahasiswa m
INNER JOIN users u ON m.id_user = u.id_user;
```

**Mendapatkan visitor count per pengunjung:**
```sql
SELECT p.nama, v.visit_count, v.last_visit
FROM visitor v
INNER JOIN pengunjung p ON v.id_pengunjung = p.id_pengunjung
ORDER BY v.visit_count DESC;
```

## File Terkait
- `schema.sql` - File SQL lengkap untuk membuat semua tabel dan indeks

