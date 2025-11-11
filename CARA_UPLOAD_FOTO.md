# 📸 Cara Upload Foto di CMS InLET

## 🎯 Overview

CMS InLET mendukung **2 cara** untuk menambahkan foto:
1. **Upload File** - Upload file langsung ke server
2. **Input URL** - Masukkan URL gambar dari internet

## 📁 Lokasi File

File yang diupload akan disimpan di:
- **Member Foto:** `uploads/members/`
- **News Thumbnail:** `uploads/news/`

## 🚀 Cara Upload Foto

### 1. Upload Foto Member

1. **Login sebagai admin**
2. **Buka:** `admin/member.php`
3. **Scroll ke form "Tambah Member Baru"**
4. **Pilih salah satu cara:**

#### **Cara A: Upload File**
- Klik **"Choose File"** di field "Upload Foto (File)"
- Pilih file gambar dari komputer
- Format yang didukung: **JPG, PNG, GIF, WEBP**
- Maksimal ukuran: **5MB**
- Klik **"Tambah Member"**

#### **Cara B: Input URL**
- Masukkan URL gambar di field "Atau Masukkan URL Foto"
- Contoh: `https://example.com/foto.jpg`
- Klik **"Tambah Member"**

**Catatan:** Jika upload file dan input URL keduanya diisi, **file upload akan diprioritaskan**.

### 2. Upload Thumbnail Berita

1. **Login sebagai admin**
2. **Buka:** `admin/news.php`
3. **Scroll ke form "Tambah Berita Baru"**
4. **Pilih salah satu cara:**

#### **Cara A: Upload File**
- Klik **"Choose File"** di field "Upload Gambar Thumbnail (File)"
- Pilih file gambar dari komputer
- Format yang didukung: **JPG, PNG, GIF, WEBP**
- Maksimal ukuran: **5MB**
- Klik **"Tambah Berita"**

#### **Cara B: Input URL**
- Masukkan URL gambar di field "Atau Masukkan URL Gambar"
- Contoh: `https://example.com/image.jpg`
- Klik **"Tambah Berita"**

**Catatan:** Jika upload file dan input URL keduanya diisi, **file upload akan diprioritaskan**.

## ✅ Format File yang Didukung

- ✅ **JPEG/JPG** - `.jpg`, `.jpeg`
- ✅ **PNG** - `.png`
- ✅ **GIF** - `.gif`
- ✅ **WEBP** - `.webp`

## 📏 Batasan

- **Ukuran maksimal:** 5MB per file
- **Format:** Hanya file gambar (image)
- **Nama file:** Otomatis di-generate untuk menghindari konflik

## 🔍 Cara Melihat File yang Diupload

File yang diupload dapat diakses langsung melalui URL:
- **Member Foto:** `http://localhost/dasarWeb/copy/uploads/members/[nama-file]`
- **News Thumbnail:** `http://localhost/dasarWeb/copy/uploads/news/[nama-file]`

## 🛠️ Troubleshooting

### Error: File terlalu besar
**Penyebab:** File lebih dari 5MB
**Solusi:** 
- Kompres gambar terlebih dahulu
- Gunakan tool online seperti TinyPNG atau Squoosh
- Atau gunakan input URL jika gambar sudah di-hosting

### Error: Tipe file tidak diizinkan
**Penyebab:** Format file tidak didukung
**Solusi:**
- Convert file ke JPG, PNG, GIF, atau WEBP
- Atau gunakan input URL

### Error: Gagal memindahkan file
**Penyebab:** Folder uploads tidak ada atau tidak writable
**Solusi:**
1. Pastikan folder `uploads/` sudah dibuat
2. Pastikan folder `uploads/members/` dan `uploads/news/` sudah dibuat
3. Set permission folder menjadi 755 atau 777 (untuk testing)

### File tidak muncul di website
**Penyebab:** Path file salah atau file tidak ada
**Solusi:**
1. Cek apakah file ada di folder `uploads/`
2. Cek path di database (harus relatif: `uploads/members/...`)
3. Pastikan `.htaccess` di folder uploads sudah benar

## 📝 Tips

1. **Optimasi Gambar:**
   - Gunakan gambar dengan ukuran yang wajar (max 1920px width)
   - Kompres gambar sebelum upload untuk loading lebih cepat

2. **Nama File:**
   - Nama file otomatis di-generate dengan format: `img_[timestamp].[ext]`
   - Tidak perlu khawatir tentang nama file yang sama

3. **URL vs Upload:**
   - **Upload File:** Lebih aman, file tersimpan di server sendiri
   - **Input URL:** Lebih cepat, tapi bergantung pada server eksternal

4. **Backup:**
   - Backup folder `uploads/` secara berkala
   - File yang diupload tidak otomatis ter-backup ke database

## 🔐 Keamanan

- File yang diupload divalidasi tipe dan ukurannya
- Hanya file gambar yang diizinkan
- Folder uploads dilindungi dengan `.htaccess`
- Nama file di-generate secara unik untuk menghindari konflik

---

**Selamat menggunakan fitur upload foto! 📸**

