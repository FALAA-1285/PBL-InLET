# 📋 Struktur CMS InLET - Quick Reference

## ✅ File yang Sudah Dibuat

### 🔧 Konfigurasi & Setup
- ✅ `config/database.php` - Koneksi database PostgreSQL
- ✅ `config/auth.php` - Fungsi autentikasi & session management
- ✅ `setup/create_admin.php` - Script untuk membuat admin pertama

### 🔐 Autentikasi
- ✅ `login.php` - Halaman login (admin & pengunjung)
- ✅ `admin/logout.php` - Handler logout

### 📊 Dashboard & CMS
- ✅ `admin/dashboard.php` - Dashboard admin dengan statistik
- ✅ `admin/research.php` - CMS untuk Artikel & Progress
- ✅ `admin/member.php` - CMS untuk Member
- ✅ `admin/news.php` - CMS untuk News

### 🌐 Frontend (Updated)
- ✅ `member.php` - Menampilkan member dari database
- ✅ `news.php` - Menampilkan berita dari database
- ✅ `research.php` - Menampilkan artikel & progress dari database
- ✅ `includes/header.php` - Header dengan link login/logout

### 📚 Dokumentasi
- ✅ `README_CMS.md` - Dokumentasi lengkap CMS
- ✅ `database/README.md` - Dokumentasi database
- ✅ `database/schema.sql` - Schema database

## 🚀 Cara Setup

### 1. Setup Database
```bash
# Import schema
psql -U postgres -d inlet_db -f database/schema.sql
```

### 2. Konfigurasi Database
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'inlet_db');
define('DB_USER', 'postgres');
define('DB_PASS', 'your_password');
define('DB_PORT', '5432');
```

### 3. Buat Admin Pertama
1. Buka browser: `http://localhost/setup/create_admin.php`
2. Admin default akan dibuat:
   - Username: `admin`
   - Password: `admin123`
3. **PENTING**: Hapus file `setup/create_admin.php` setelah setup!

### 4. Login ke CMS
1. Buka: `http://localhost/login.php`
2. Login sebagai admin
3. Akses dashboard di: `http://localhost/admin/dashboard.php`

## 📁 Struktur Folder

```
copy/
├── config/              # Konfigurasi
│   ├── database.php
│   └── auth.php
├── admin/               # CMS Admin
│   ├── dashboard.php
│   ├── research.php
│   ├── member.php
│   ├── news.php
│   └── logout.php
├── setup/               # Setup Script
│   └── create_admin.php (hapus setelah setup!)
├── includes/            # Includes
│   ├── header.php
│   └── footer.php
├── database/            # Database Files
│   ├── schema.sql
│   ├── README.md
│   └── sample_queries.sql
├── login.php            # Login Page
├── index.php            # Homepage
├── research.php         # Research Page (Frontend)
├── member.php           # Member Page (Frontend)
├── news.php             # News Page (Frontend)
└── README_CMS.md        # Dokumentasi CMS
```

## 🎯 Fitur CMS

### Dashboard Admin
- ✅ Statistik: Artikel, Berita, Member, Progress, Visitor
- ✅ Quick actions ke setiap CMS
- ✅ Recent news

### CMS Research
- ✅ Tambah Artikel (Judul, Tahun, Konten)
- ✅ Tambah Progress (dengan relasi ke Artikel, Mahasiswa, Member)
- ✅ Lihat & Hapus Artikel
- ✅ Lihat & Hapus Progress

### CMS Member
- ✅ Tambah Member (Nama, Email, Jabatan, Foto)
- ✅ Tambah Profil Detail (Alamat, No Telp, Deskripsi)
- ✅ Lihat & Hapus Member

### CMS News
- ✅ Tambah Berita (Judul, Konten, Thumbnail)
- ✅ Lihat & Hapus Berita
- ✅ Auto timestamp

## 🔐 Login System

### Admin
- Login di `login.php`
- Akses penuh ke semua CMS
- Redirect ke `admin/dashboard.php`

### Pengunjung
- Login di `login.php`
- Visitor count otomatis diupdate
- Redirect ke `index.php`

## 📝 Cara Menggunakan

### Menambah Konten
1. Login sebagai admin
2. Pilih menu di header (Research/Member/News)
3. Isi form
4. Klik submit

### Menghapus Konten
1. Login sebagai admin
2. Pilih menu di header
3. Klik "Hapus" pada item yang ingin dihapus
4. Konfirmasi

## ⚠️ Catatan Penting

1. **Hapus `setup/create_admin.php`** setelah setup!
2. **Ganti password default** admin setelah login pertama
3. **Backup database** secara berkala
4. **Gunakan password yang kuat** untuk production

## 🐛 Troubleshooting

### Error Connection
- Cek PostgreSQL running
- Cek kredensial di `config/database.php`
- Cek database sudah dibuat

### Error Table doesn't exist
- Pastikan sudah import `database/schema.sql`
- Cek nama database di config

### Admin tidak bisa login
- Pastikan sudah run `setup/create_admin.php`
- Cek password di database (harus hashed)

## 📞 Dokumentasi Lengkap

- **CMS**: Lihat `README_CMS.md`
- **Database**: Lihat `database/README.md`
- **Schema**: Lihat `database/schema.sql`

---

**CMS InLET siap digunakan! 🚀**

