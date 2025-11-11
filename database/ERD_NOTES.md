# Entity Relationship Diagram (ERD) Notes

## Diagram Relasi Database

```
┌─────────────┐
│   users     │ (Central Authentication)
│─────────────│
│ id_user PK  │
│ username    │
│ password    │
│ role        │
└──────┬──────┘
       │
       ├─────────────────┬──────────────────┐
       │                 │                  │
       │ (1:1)           │ (1:1)            │ (0..1:1)
       │                 │                  │
┌──────▼──────┐  ┌──────▼──────┐  ┌───────▼────────┐
│   admin     │  │ pengunjung  │  │  mahasiswa    │
│─────────────│  │─────────────│  │────────────────│
│ id_admin PK│  │id_pengunjung│  │ id_mhs PK      │
│ id_user FK │  │ id_user FK  │  │ id_user FK     │
│ nama       │  │ nama        │  │ nama           │
│ email      │  │ email       │  │ status         │
│ phone      │  │ asal_inst   │  │ (magang/skripsi│
└────────────┘  └──────┬───────┘  │  /regular)     │
                       │          └───────┬─────────┘
                       │ (1:1)            │
                       │                  │
                ┌──────▼──────┐          │
                │   visitor   │          │
                │─────────────│          │
                │id_visitor PK│          │
                │id_pengunjung│          │
                │ visit_count │          │
                │ last_visit  │          │
                └─────────────┘          │
                                         │
┌─────────────┐                         │
│   member    │                         │
│─────────────│                         │
│id_member PK │                         │
│ nama        │                         │
│ email       │                         │
│ jabatan     │                         │
└──────┬──────┘                         │
       │                                │
       │ (1:1)                          │
       │                                │
┌──────▼─────────┐                      │
│profil_member   │                      │
│────────────────│                      │
│id_profile PK   │                      │
│id_member FK    │                      │
│ alamat         │                      │
│ no_tlp         │                      │
└────────────────┘                      │
                                        │
┌─────────────┐                         │
│   artikel   │                         │
│─────────────│                         │
│id_artikel PK│                         │
│ judul       │                         │
│ tahun       │                         │
└──────┬──────┘                         │
       │                                │
       │                                │
       │                                │
┌──────▼─────────┐                      │
│   progress     │                      │
│────────────────│                      │
│id_progress PK  │                      │
│id_artikel FK   │◄─────────────────────┘
│id_mhs FK       │
│id_member FK    │◄──────────────────────┐
│ judul          │                       │
│ tahun          │                       │
└────────────────┘                       │
                                         │
┌─────────────┐                          │
│   berita    │                          │
│─────────────│                          │
│id_berita PK │                          │
│ judul       │                          │
│ konten      │                          │
│ gambar      │                          │
└─────────────┘                          │
                                         │
┌─────────────┐                          │
│   mitra     │                          │
│─────────────│                          │
│id_mitra PK  │                          │
│nama_institusi                          │
│ logo       │                           │
└─────────────┘                          │
                                         │
┌─────────────┐                          │
│   produk    │                          │
│─────────────│                          │
│id_produk PK │                          │
│nama_produk  │                          │
│ deskripsi   │                          │
│ harga       │                          │
└──────┬──────┘                          │
       │                                 │
       │ (M:N)                           │
       │                                 │
┌──────▼──────────────┐                  │
│  produk_resource   │                  │
│────────────────────│                  │
│id_prod_res PK      │                  │
│id_produk FK        │                  │
│id_resource FK      │                  │
└──────┬─────────────┘                  │
       │                                │
       │ (M:N)                          │
       │                                │
┌──────▼─────────┐                      │
│   resource     │                      │
│────────────────│                      │
│id_resource PK  │                      │
│ judul          │                      │
│ deskripsi      │                      │
│ gambar         │                      │
└────────────────┘                      │
```

## Keterangan Relasi

### One-to-One (1:1)
- `users` ↔ `admin` - Satu user bisa menjadi satu admin
- `users` ↔ `pengunjung` - Satu user bisa menjadi satu pengunjung
- `users` ↔ `mahasiswa` - Satu user bisa menjadi satu mahasiswa (nullable)
- `member` ↔ `profil_member` - Satu member punya satu profil detail
- `pengunjung` ↔ `visitor` - Satu pengunjung punya satu record visitor tracking

### One-to-Many (1:N)
- `artikel` → `progress` - Satu artikel bisa punya banyak progress
- `mahasiswa` → `progress` - Satu mahasiswa bisa punya banyak progress
- `member` → `progress` - Satu member bisa punya banyak progress

### Many-to-Many (M:N)
- `produk` ↔ `resource` (via `produk_resource`) - Satu produk bisa punya banyak resource, satu resource bisa dipakai banyak produk

## Cardinality Summary

| Tabel Parent | Tabel Child | Relasi | Constraint |
|--------------|------------|--------|------------|
| users | admin | 1:1 | UNIQUE, CASCADE |
| users | pengunjung | 1:1 | UNIQUE, CASCADE |
| users | mahasiswa | 0..1:1 | UNIQUE, SET NULL |
| member | profil_member | 1:1 | UNIQUE, CASCADE |
| pengunjung | visitor | 1:1 | CASCADE |
| artikel | progress | 1:N | SET NULL |
| mahasiswa | progress | 1:N | SET NULL |
| member | progress | 1:N | SET NULL |
| produk | produk_resource | 1:N | CASCADE |
| resource | produk_resource | 1:N | CASCADE |

## Foreign Key Actions

- **ON DELETE CASCADE**: Menghapus parent akan menghapus child
  - users → admin, pengunjung
  - member → profil_member
  - produk/resource → produk_resource
  
- **ON DELETE SET NULL**: Menghapus parent akan set FK child menjadi NULL
  - users → mahasiswa
  - artikel/mahasiswa/member → progress

