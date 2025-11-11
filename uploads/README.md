# 📁 Folder Uploads

Folder ini digunakan untuk menyimpan file yang diupload melalui CMS.

## 📂 Struktur Folder

```
uploads/
├── members/     # Foto member
├── news/        # Thumbnail berita
└── .htaccess    # Konfigurasi keamanan
```

## ⚠️ Catatan Penting

1. **Pastikan folder ini writable** (permission 755 atau 777 untuk testing)
2. **Folder akan dibuat otomatis** saat pertama kali upload
3. **Jangan hapus file di folder ini** kecuali yakin tidak digunakan lagi

## 🔐 Keamanan

- Folder dilindungi dengan `.htaccess`
- Hanya file gambar yang diizinkan
- File divalidasi sebelum disimpan

