# Database Documentation Index

## 📁 File Structure

```
database/
├── schema.sql          # File SQL lengkap untuk membuat semua tabel
├── README.md           # Dokumentasi lengkap struktur database
├── ERD_NOTES.md        # Catatan Entity Relationship Diagram
├── sample_queries.sql  # Contoh query yang berguna
└── INDEX.md           # File ini (index navigasi)
```

## 📚 Quick Navigation

### 1. **schema.sql**
   - File SQL lengkap untuk setup database
   - Berisi semua CREATE TABLE statements
   - Berisi semua CREATE INDEX statements
   - Siap di-import ke PostgreSQL

### 2. **README.md**
   - Dokumentasi lengkap semua tabel
   - Penjelasan setiap kolom
   - Relasi antar tabel
   - Contoh penggunaan
   - Tips dan best practices

### 3. **ERD_NOTES.md**
   - Diagram relasi database (text-based)
   - Penjelasan cardinality (1:1, 1:N, M:N)
   - Foreign key actions (CASCADE, SET NULL)
   - Tabel summary relasi

### 4. **sample_queries.sql**
   - 15+ contoh query yang sering digunakan
   - Query untuk CRUD operations
   - Query untuk reporting/statistik
   - Query untuk relasi kompleks

## 🚀 Quick Start

### Import Database Schema
```bash
psql -U username -d database_name -f database/schema.sql
```

### Baca Dokumentasi
1. Mulai dari **README.md** untuk overview lengkap
2. Lihat **ERD_NOTES.md** untuk memahami relasi
3. Gunakan **sample_queries.sql** sebagai referensi query

## 📊 Database Summary

- **Total Tabel**: 15 tabel
- **Tabel Utama**: users, admin, pengunjung, mahasiswa, member
- **Tabel Content**: artikel, berita, progress
- **Tabel Business**: produk, resource, mitra
- **Tabel Tracking**: visitor
- **Tabel Relasi**: produk_resource, profil_member

## 🔑 Key Features

1. **Autentikasi Terpusat**: Semua user login melalui tabel `users`
2. **Multi-Role Support**: Satu user bisa menjadi admin, pengunjung, atau mahasiswa
3. **Visitor Tracking**: Tracking kunjungan per pengunjung yang login
4. **Status Mahasiswa**: Support status magang, skripsi, atau regular
5. **Relasi Fleksibel**: Progress bisa terkait dengan artikel, mahasiswa, atau member

## 📝 Catatan Penting

- Semua kolom TEXT sudah diganti menjadi VARCHAR dengan panjang yang wajar
- Menggunakan TIMESTAMP WITH TIME ZONE untuk semua kolom waktu
- Foreign key dengan ON DELETE CASCADE atau SET NULL sesuai kebutuhan
- Indeks sudah dibuat untuk kolom yang sering di-query

## 🔍 Cari Sesuatu?

- **Struktur Tabel** → Lihat `README.md` bagian "Struktur Database"
- **Relasi Tabel** → Lihat `ERD_NOTES.md`
- **Contoh Query** → Lihat `sample_queries.sql`
- **Setup Database** → Gunakan `schema.sql`

