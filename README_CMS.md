# CMS InLET - Content Management System

## 📋 Overview

CMS lengkap untuk mengelola konten website InLET dengan fitur:
- Login system untuk Admin dan Pengunjung
- Dashboard admin dengan statistik
- CMS untuk Research (Artikel & Progress)
- CMS untuk Member
- CMS untuk News
- Visitor tracking untuk pengunjung yang login

## 🚀 Setup & Instalasi

### 1. Setup Database

1. Import schema database:
```bash
psql -U username -d database_name -f database/schema.sql
```

2. Konfigurasi koneksi database di `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'inlet_db');
define('DB_USER', 'postgres');
define('DB_PASS', 'your_password');
define('DB_PORT', '5432');
```

### 2. Buat Admin User Pertama

1. Buka browser dan akses: `http://localhost/setup/create_admin.php`
2. Admin default akan dibuat dengan:
   - Username: `admin`
   - Password: `admin123`
3. **PENTING**: Setelah login pertama kali, ganti password!
4. **PENTING**: Hapus atau rename file `setup/create_admin.php` setelah setup!

### 3. Login ke CMS

1. Akses: `http://localhost/login.php`
2. Login sebagai admin untuk mengakses CMS
3. Login sebagai pengunjung untuk tracking visitor

## 📁 Struktur File

```
copy/
├── config/
│   ├── database.php      # Koneksi database
│   └── auth.php          # Fungsi autentikasi
├── admin/
│   ├── dashboard.php     # Dashboard admin
│   ├── research.php      # CMS Research
│   ├── member.php        # CMS Member
│   ├── news.php          # CMS News
│   └── logout.php        # Logout handler
├── setup/
│   └── create_admin.php  # Script setup admin (hapus setelah setup!)
├── includes/
│   ├── header.php
│   └── footer.php
├── database/
│   ├── schema.sql        # Schema database
│   ├── README.md         # Dokumentasi database
│   └── sample_queries.sql
├── login.php             # Halaman login
├── index.php             # Homepage
├── research.php          # Halaman research (frontend)
├── member.php            # Halaman member (frontend)
└── news.php              # Halaman news (frontend)
```

## 🎯 Fitur CMS

### Dashboard Admin
- Statistik: Total Artikel, Berita, Member, Progress, Visitor
- Quick actions ke setiap CMS
- Recent news

### CMS Research
- **Artikel**: Tambah, lihat, hapus artikel
- **Progress**: Tambah, lihat, hapus progress penelitian
- Relasi dengan artikel, mahasiswa, dan member

### CMS Member
- Tambah member baru
- Tambah profil detail (alamat, no telp, deskripsi)
- Lihat dan hapus member

### CMS News
- Tambah berita baru dengan thumbnail
- Lihat dan hapus berita
- Auto timestamp

## 👤 User Roles

### Admin
- Akses penuh ke semua CMS
- Bisa CRUD semua konten
- Melihat statistik dashboard

### Pengunjung
- Login untuk tracking visitor
- Data kunjungan tersimpan di tabel `visitor`
- Tidak bisa akses CMS

## 🔐 Keamanan

1. **Password Hashing**: Menggunakan `password_hash()` dengan bcrypt
2. **Session Management**: Session-based authentication
3. **SQL Injection Protection**: Menggunakan prepared statements
4. **XSS Protection**: Menggunakan `htmlspecialchars()` untuk output

## 📝 Cara Menggunakan

### Menambah Artikel
1. Login sebagai admin
2. Buka **Research** di menu
3. Tab **Artikel**
4. Isi form: Judul, Tahun, Konten
5. Klik **Tambah Artikel**

### Menambah Member
1. Login sebagai admin
2. Buka **Member** di menu
3. Isi form: Nama (wajib), Email, Jabatan, Foto (URL)
4. Isi profil detail (opsional): Alamat, No Telp, Deskripsi
5. Klik **Tambah Member**

### Menambah News
1. Login sebagai admin
2. Buka **News** di menu
3. Isi form: Judul, Konten, Gambar Thumbnail (URL)
4. Klik **Tambah Berita**

### Tracking Visitor
1. Pengunjung login di `login.php`
2. Jika user adalah pengunjung, visitor count otomatis diupdate
3. Data tersimpan di tabel `visitor`

## 🐛 Troubleshooting

### Error: Connection failed
- Pastikan PostgreSQL running
- Cek kredensial di `config/database.php`
- Pastikan database sudah dibuat

### Error: Table doesn't exist
- Pastikan sudah import `database/schema.sql`
- Cek nama database di config

### Admin tidak bisa login
- Pastikan sudah run `setup/create_admin.php`
- Cek password di database (harus hashed)

### Visitor tidak ter-track
- Pastikan user sudah dibuat di tabel `pengunjung`
- Cek relasi `pengunjung.id_user` dengan `users.id_user`

## 📚 Dokumentasi Database

Lihat `database/README.md` untuk dokumentasi lengkap struktur database.

## 🔄 Update Frontend

Halaman frontend (`member.php`, `news.php`, `research.php`) sudah diupdate untuk menampilkan data dari database. Pastikan:
1. Database sudah diisi data melalui CMS
2. Koneksi database sudah benar

## ⚠️ Catatan Penting

1. **Hapus `setup/create_admin.php`** setelah setup selesai!
2. **Ganti password default** admin setelah login pertama
3. **Backup database** secara berkala
4. **Gunakan password yang kuat** untuk production

## 📞 Support

Jika ada masalah, cek:
1. Error log PHP
2. Error log PostgreSQL
3. Dokumentasi database di `database/README.md`

---

**Happy Coding! 🚀**

