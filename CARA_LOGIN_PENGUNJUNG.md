# 👤 Cara Login sebagai Pengunjung

Ada **2 cara** untuk membuat akun pengunjung:

## 🚀 Cara 1: Menggunakan Script Setup (Cepat)

### Langkah-langkah:

1. **Buka browser**, akses:
   ```
   http://localhost/dasarWeb/copy/setup/create_pengunjung.php
   ```

2. **User pengunjung akan dibuat** dengan:
   - **Username:** `pengunjung`
   - **Password:** `pengunjung123`
   - **Nama:** Pengunjung Test
   - **Email:** pengunjung@test.com
   - **Asal Institusi:** Institusi Test

3. **Login di:**
   ```
   http://localhost/dasarWeb/copy/login.php
   ```
   - Username: `pengunjung`
   - Password: `pengunjung123`

4. **Setelah login**, akan redirect ke homepage dan visitor count otomatis diupdate!

---

## 📝 Cara 2: Registrasi Manual (Form Registrasi)

### Langkah-langkah:

1. **Buka browser**, akses:
   ```
   http://localhost/dasarWeb/copy/register.php
   ```

2. **Isi form registrasi:**
   - Username (wajib)
   - Password (wajib, minimal 6 karakter)
   - Konfirmasi Password (wajib)
   - Nama Lengkap (wajib)
   - Email (opsional)
   - Asal Institusi (opsional)

3. **Klik "Daftar"**

4. **Setelah berhasil**, klik "Login Sekarang" atau akses:
   ```
   http://localhost/dasarWeb/copy/login.php
   ```

5. **Login dengan username dan password yang baru dibuat**

---

## ✅ Setelah Login sebagai Pengunjung

1. **Redirect ke homepage** (`index.php`)
2. **Visitor count otomatis diupdate** di database
3. **Data tersimpan di tabel `visitor`**:
   - `visit_count` - jumlah kunjungan
   - `last_visit` - kunjungan terakhir
   - `first_visit` - kunjungan pertama

## 🔍 Cek Visitor Count

Untuk melihat visitor count, admin bisa cek di:
- **Dashboard Admin** → Statistik "Total Kunjungan"
- **Database** → Tabel `visitor`

## ⚠️ Catatan Penting

1. **Pengunjung TIDAK bisa akses CMS**
   - Hanya admin yang bisa akses dashboard dan CMS
   - Pengunjung hanya bisa melihat website dan tracking visitor

2. **Visitor Tracking Otomatis**
   - Setiap kali pengunjung login, `visit_count` otomatis +1
   - `last_visit` otomatis diupdate

3. **Hapus Setup Script**
   - Setelah membuat pengunjung, **hapus atau rename** file:
     - `setup/create_pengunjung.php`

## 🎯 URL Penting

- **Registrasi:** `http://localhost/dasarWeb/copy/register.php`
- **Login:** `http://localhost/dasarWeb/copy/login.php`
- **Setup Pengunjung:** `http://localhost/dasarWeb/copy/setup/create_pengunjung.php` (hapus setelah setup!)

---

**Selamat menggunakan! 🚀**

