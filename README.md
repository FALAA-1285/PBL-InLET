# InLET — Information and Learning Engineering Technology

Website Lab InLET (PHP native + PostgreSQL), dapat dijalankan di Laragon maupun di Vercel.

## Struktur Folder

```
PBL-InLET/
├── index.php, member.php, news.php,     # Halaman publik (URL tetap /member, /news, dst.)
│   research.php, login.php, register.php
├── admin/                # Panel admin (butuh login)
│   └── partials/         # Komponen admin yang di-include (sidebar)
├── service/              # Layanan publik: absensi, buku tamu, peminjaman
├── includes/             # Header & footer halaman publik
├── config/               # Koneksi DB, auth, upload, settings, stored procedure
├── api/index.php         # Front controller untuk Vercel
├── assets/
│   ├── css/              # Seluruh stylesheet (publik + admin.css)
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
- `config/`, `includes/`, `admin/partials/`, `setup/`, dan `database/` diblokir dari akses langsung oleh `api/index.php` saat berjalan di Vercel.

## Menjalankan Lokal

1. Salin `.env.example` menjadi `.env` lalu isi kredensial database.
2. Import `database/schema.sql` ke PostgreSQL.
3. Jalankan `setup/create_admin.php` sekali untuk membuat akun admin.
4. Buka `http://localhost/PBL-InLET/`.
