# InLET — Information and Learning Engineering Technology

Website Lab InLET (PHP native + PostgreSQL), dapat dijalankan di Laragon maupun di Vercel.

## Struktur Folder

```
PBL-InLET/
├── index.php, member.php, news.php,     # Controller halaman publik: logika & query saja
│   research.php, login.php, register.php  (URL tetap /member, /news, dst.)
├── admin/                # Controller panel admin (butuh login)
├── service/              # Controller layanan publik: absensi, buku tamu, peminjaman
├── views/                # Tampilan (HTML) untuk setiap controller, struktur sama
│   ├── admin/, service/
│   └── partials/         # header, footer, admin_sidebar
├── config/               # Koneksi DB, auth, upload, settings, stored procedure
├── api/index.php         # Front controller untuk Vercel
├── assets/
│   ├── css/              # Stylesheet halaman publik + admin.css
│   │   └── admin/        # Stylesheet per halaman admin
│   └── *.png, *.jpg      # Logo & gambar statis
├── uploads/              # File unggahan dari admin (gallery, members, news, ...)
├── database/
│   └── schema.sql        # Skema database (dump lokal *.sql tidak di-commit)
├── setup/                # Skrip setup sekali jalan (buat admin, view, reset)
│   └── diagnostics/      # Skrip uji koneksi & query (khusus development)
└── docs/                 # Dokumentasi proyek
    └── tugas/            # Pembagian tugas per anggota
```

Catatan:
- Gambar di `assets/` sengaja tidak dipindah ke subfolder karena path-nya (`assets/logo.png`, dll.) juga tersimpan di tabel pengaturan database.
- Setiap halaman dipisah menjadi controller (mis. `admin/news.php`) yang memproses data, lalu memanggil view-nya (`views/admin/news.php`) di baris terakhir.
- `config/`, `views/`, `setup/`, dan `database/` diblokir dari akses langsung oleh `api/index.php` (Vercel). Di Apache/Laragon, `.htaccess` memblokir `config/`, `views/`, `database/`, dan `docs/`; `setup/` tetap bisa dibuka karena dibutuhkan untuk setup lokal dan sudah punya pengecekan environment sendiri.

## Menjalankan Lokal

1. Salin `.env.example` menjadi `.env` lalu isi kredensial database.
2. Import `database/schema.sql` ke PostgreSQL.
3. Jalankan `setup/create_admin.php` sekali untuk membuat akun admin.
4. Buka `http://localhost/PBL-InLET/`.
