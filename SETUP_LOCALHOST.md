# 🚀 Panduan Setup CMS InLET di Localhost (Laragon)

## 📋 Prerequisites

Pastikan sudah terinstall:
- ✅ Laragon (dengan PHP & Apache/Nginx)
- ✅ PostgreSQL
- ✅ PostgreSQL sudah running

## 🔧 Langkah-langkah Setup

### 1. Pastikan PostgreSQL Running

1. Buka Laragon
2. Klik **Start All** atau pastikan PostgreSQL service running
3. Atau buka **Services** di Windows dan pastikan PostgreSQL service running

### 2. Buat Database PostgreSQL

**Opsi A: Via pgAdmin**
1. Buka pgAdmin
2. Klik kanan **Databases** → **Create** → **Database**
3. Nama database: `inlet_pbl`
4. Klik **Save**

**Opsi B: Via Command Line**
```bash
# Buka Command Prompt atau PowerShell
psql -U postgres

# Di dalam psql, jalankan:
CREATE DATABASE inlet_pbl;

# Keluar dari psql
\q
```

**Opsi C: Via Laragon Terminal**
1. Buka Laragon
2. Klik **Terminal** → **PostgreSQL**
3. Jalankan:
```sql
CREATE DATABASE inlet_pbl;
```

### 3. Import Schema Database

**Opsi A: Via pgAdmin**
1. Buka pgAdmin
2. Klik kanan database `inlet_pbl` → **Query Tool**
3. Buka file `database/schema.sql`
4. Copy semua isinya
5. Paste di Query Tool
6. Klik **Execute** (F5)

**Opsi B: Via Command Line**
```bash
# Buka Command Prompt/PowerShell di folder project
cd C:\laragon\www\dasarWeb\copy

# Import schema
psql -U postgres -d inlet_pbl -f database/schema.sql
```

**Opsi C: Via Laragon Terminal**
1. Buka Laragon → Terminal → PostgreSQL
2. Connect ke database:
```sql
\c inlet_pbl
```
3. Copy isi file `database/schema.sql` dan paste di terminal
4. Tekan Enter

### 4. Verifikasi Database

Pastikan tabel sudah dibuat:
```sql
-- Di pgAdmin atau psql
\c inlet_pbl
\dt
```

Harus muncul tabel: users, admin, pengunjung, mahasiswa, member, artikel, berita, progress, dll.

### 5. Konfigurasi Database (Sudah Dikonfigurasi)

File `config/database.php` sudah dikonfigurasi:
- ✅ DB_NAME: `inlet_pbl`
- ✅ DB_USER: `postgres`
- ✅ DB_PASS: `828`
- ✅ DB_HOST: `localhost`
- ✅ DB_PORT: `5432`

### 6. Buat Admin User Pertama

1. Buka browser
2. Akses: `http://localhost/dasarWeb/copy/setup/create_admin.php`
3. Jika berhasil, akan muncul:
   - Username: `admin`
   - Password: `admin123`
4. **PENTING**: Copy informasi login ini!

### 7. Hapus Setup Script (Keamanan)

Setelah admin dibuat, **HAPUS** atau **RENAME** file:
```
setup/create_admin.php
```

Atau rename menjadi:
```
setup/create_admin.php.bak
```

### 8. Akses CMS

1. **Login Page**: `http://localhost/dasarWeb/copy/login.php`
   - Username: `admin`
   - Password: `admin123`

2. **Dashboard Admin**: `http://localhost/dasarWeb/copy/admin/dashboard.php`
   - Otomatis redirect setelah login sebagai admin

3. **Homepage**: `http://localhost/dasarWeb/copy/index.php`

## 🎯 Testing

### Test Login
1. Buka: `http://localhost/dasarWeb/copy/login.php`
2. Login dengan:
   - Username: `admin`
   - Password: `admin123`
3. Harus redirect ke dashboard

### Test CMS
1. Setelah login, coba akses:
   - **Research**: `http://localhost/dasarWeb/copy/admin/research.php`
   - **Member**: `http://localhost/dasarWeb/copy/admin/member.php`
   - **News**: `http://localhost/dasarWeb/copy/admin/news.php`

### Test Frontend
1. Buka: `http://localhost/dasarWeb/copy/member.php`
2. Buka: `http://localhost/dasarWeb/copy/news.php`
3. Buka: `http://localhost/dasarWeb/copy/research.php`

## 🐛 Troubleshooting

### Error: Connection failed
**Penyebab**: PostgreSQL tidak running atau kredensial salah

**Solusi**:
1. Pastikan PostgreSQL service running di Laragon
2. Cek password di `config/database.php` (harus `828`)
3. Test koneksi:
```php
// Buat file test: test_db.php
<?php
require_once 'config/database.php';
$conn = getDBConnection();
echo "Connected successfully!";
?>
```

### Error: Table doesn't exist
**Penyebab**: Schema belum diimport

**Solusi**:
1. Pastikan sudah import `database/schema.sql`
2. Cek di pgAdmin apakah tabel sudah ada
3. Import ulang schema jika perlu

### Error: Admin tidak bisa login
**Penyebab**: Admin belum dibuat atau password salah

**Solusi**:
1. Pastikan sudah run `setup/create_admin.php`
2. Cek di database apakah user admin sudah ada:
```sql
SELECT * FROM users WHERE username = 'admin';
SELECT * FROM admin;
```
3. Jika belum ada, run `setup/create_admin.php` lagi

### Error: 404 Not Found
**Penyebab**: Path URL salah atau file tidak ada

**Solusi**:
1. Pastikan path benar: `http://localhost/dasarWeb/copy/...`
2. Sesuaikan dengan folder project Anda
3. Cek apakah file ada di folder tersebut

### Error: Permission denied
**Penyebab**: PostgreSQL permission

**Solusi**:
1. Pastikan user `postgres` punya akses ke database `inlet_pbl`
2. Atau gunakan superuser untuk setup awal

## 📝 Checklist Setup

- [ ] PostgreSQL running
- [ ] Database `inlet_pbl` sudah dibuat
- [ ] Schema database sudah diimport
- [ ] Konfigurasi database sudah benar
- [ ] Admin user sudah dibuat
- [ ] File `setup/create_admin.php` sudah dihapus/rename
- [ ] Bisa login sebagai admin
- [ ] Bisa akses dashboard
- [ ] Bisa akses CMS (Research, Member, News)
- [ ] Frontend menampilkan data dari database

## 🎉 Selesai!

Jika semua checklist sudah dicentang, CMS siap digunakan!

**URL Penting**:
- Login: `http://localhost/dasarWeb/copy/login.php`
- Dashboard: `http://localhost/dasarWeb/copy/admin/dashboard.php`
- Homepage: `http://localhost/dasarWeb/copy/index.php`

---

**Selamat menggunakan CMS InLET! 🚀**

