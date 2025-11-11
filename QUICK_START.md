# ⚡ Quick Start - CMS InLET di Localhost

## 🚀 Setup Cepat (5 Menit)

### 1. Pastikan PostgreSQL Running
- Buka Laragon
- Klik **Start All** atau pastikan PostgreSQL service running

### 2. Buat Database
Buka pgAdmin atau psql, lalu jalankan:
```sql
CREATE DATABASE inlet_pbl;
```

### 3. Import Schema
**Via pgAdmin:**
1. Buka pgAdmin
2. Klik kanan database `inlet_pbl` → **Query Tool**
3. Buka file `database/schema.sql`
4. Copy semua isinya dan paste di Query Tool
5. Klik **Execute** (F5)

**Via Command Line:**
```bash
cd C:\laragon\www\dasarWeb\copy
psql -U postgres -d inlet_pbl -f database/schema.sql
```

### 4. Test Koneksi Database
Buka browser, akses:
```
http://localhost/dasarWeb/copy/test_db.php
```

Jika berhasil, akan muncul:
- ✅ Koneksi database berhasil!
- Daftar tabel
- Status setup

### 5. Buat Admin User
Buka browser, akses:
```
http://localhost/dasarWeb/copy/setup/create_admin.php
```

Admin akan dibuat dengan:
- **Username:** `admin`
- **Password:** `admin123`

### 6. Hapus Setup Script
**HAPUS** atau **RENAME** file:
- `setup/create_admin.php`
- `test_db.php`

### 7. Login ke CMS
Buka browser, akses:
```
http://localhost/dasarWeb/copy/login.php
```

Login dengan:
- **Username:** `admin`
- **Password:** `admin123`

## ✅ Checklist

- [ ] PostgreSQL running
- [ ] Database `inlet_pbl` dibuat
- [ ] Schema database diimport
- [ ] Test koneksi berhasil (`test_db.php`)
- [ ] Admin user dibuat (`setup/create_admin.php`)
- [ ] Setup script dihapus
- [ ] Login berhasil
- [ ] Dashboard bisa diakses

## 🎯 URL Penting

- **Login:** `http://localhost/dasarWeb/copy/login.php`
- **Dashboard:** `http://localhost/dasarWeb/copy/admin/dashboard.php`
- **Homepage:** `http://localhost/dasarWeb/copy/index.php`
- **Test DB:** `http://localhost/dasarWeb/copy/test_db.php` (hapus setelah setup!)

## 🐛 Troubleshooting

### Error: Connection failed
1. Pastikan PostgreSQL running
2. Cek password di `config/database.php` (harus `828`)
3. Test dengan `test_db.php`

### Error: Table doesn't exist
1. Pastikan sudah import `database/schema.sql`
2. Cek di pgAdmin apakah tabel sudah ada

### Error: Admin tidak bisa login
1. Pastikan sudah run `setup/create_admin.php`
2. Cek di database apakah user admin sudah ada

---

**Selamat menggunakan CMS InLET! 🚀**

