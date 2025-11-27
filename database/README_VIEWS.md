# Views dan Stored Procedures

File ini menjelaskan cara membuat views dan stored procedures yang diperlukan oleh aplikasi web.

## Cara Membuat Views dan Stored Procedures

### Opsi 1: Menggunakan File SQL Terpisah (Disarankan)

Jalankan file `create_views_and_procedures.sql`:

```bash
psql -U postgres -d inlet_pbl -f database/create_views_and_procedures.sql
```

Atau jika menggunakan Windows dengan Laragon:

```bash
cd C:\laragon\www\PBL-InLET
psql -U postgres -d inlet_pbl -f database/create_views_and_procedures.sql
```

### Opsi 2: Menggunakan pgAdmin atau Tool Database Lain

1. Buka pgAdmin atau tool database PostgreSQL lainnya
2. Connect ke database `inlet_pbl`
3. Buka file `database/create_views_and_procedures.sql`
4. Jalankan seluruh script

### Opsi 3: Menggunakan PHP Script

Jalankan file `setup/create_views.php` (akan dibuat jika diperlukan)

## Views yang Dibutuhkan

### 1. view_alat_dipinjam
Menampilkan alat yang sedang dipinjam dengan informasi lengkap.

**Digunakan di:**
- `admin/dashboard.php` - Statistik alat dipinjam
- `admin/alat_lab.php` - Cek alat yang dipinjam saat delete
- `service/peminjaman.php` - Daftar peminjaman aktif

### 2. view_alat_tersedia
Menampilkan alat dengan informasi stok tersedia (total stock dikurangi yang dipinjam).

**Digunakan di:**
- `admin/alat_lab.php` - Daftar alat dengan stok tersedia
- `service/peminjaman.php` - Cek stok dan daftar alat tersedia

## Stored Procedures yang Dibutuhkan

### 1. tambah_artikel(p_judul, p_tahun, p_konten)
Menambahkan artikel baru ke database.

**Digunakan di:**
- `admin/research.php` - Form tambah artikel

### 2. tambah_member(p_nama, p_email, p_jabatan, p_foto, p_keahlian, p_notlp, p_deskripsi, p_alamat, p_id_admin)
Menambahkan member baru ke database.

**Digunakan di:**
- `admin/member.php` - Form tambah member

## Verifikasi

Setelah menjalankan script, verifikasi dengan query berikut:

```sql
-- Cek views
SELECT table_name FROM information_schema.views 
WHERE table_schema = 'public' 
AND table_name IN ('view_alat_dipinjam', 'view_alat_tersedia');

-- Cek stored procedures
SELECT routine_name FROM information_schema.routines 
WHERE routine_schema = 'public' 
AND routine_name IN ('tambah_artikel', 'tambah_member');
```

Jika semua views dan procedures muncul, berarti sudah berhasil dibuat.

## Troubleshooting

### Error: relation "view_alat_dipinjam" does not exist

**Solusi:**
1. Pastikan sudah menjalankan `create_views_and_procedures.sql`
2. Pastikan koneksi ke database yang benar (`inlet_pbl`)
3. Pastikan user memiliki permission untuk membuat views

### Error: permission denied for schema public

**Solusi:**
```sql
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO postgres;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO postgres;
```

