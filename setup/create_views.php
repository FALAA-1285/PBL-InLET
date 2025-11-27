<?php
/**
 * Script untuk membuat views yang diperlukan di database
 * Jalankan script ini sekali untuk membuat views jika belum ada
 */

require_once '../config/database.php';

$conn = getDBConnection();

echo "<h2>Membuat Views Database</h2>";
echo "<hr>";

// View untuk melihat alat yang sedang dipinjam
try {
    $sql = "CREATE OR REPLACE VIEW view_alat_dipinjam AS
            SELECT 
                pj.id_peminjaman,
                pj.id_alat,
                alat.nama_alat,
                alat.deskripsi,
                pj.nama_peminjam,
                pj.tanggal_pinjam,
                pj.tanggal_kembali,
                pj.keterangan,
                pj.status,
                pj.created_at
            FROM peminjaman pj
            JOIN alat_lab alat 
                ON alat.id_alat = pj.id_alat
            WHERE pj.status = 'dipinjam'";
    
    $conn->exec($sql);
    echo "<p style='color: green;'>✅ View 'view_alat_dipinjam' berhasil dibuat!</p>";
} catch(PDOException $e) {
    echo "<p style='color: orange;'>⚠️ View 'view_alat_dipinjam': " . $e->getMessage() . "</p>";
}

// View untuk melihat alat yang tersedia dengan informasi stok
try {
    $sql = "CREATE OR REPLACE VIEW view_alat_tersedia AS
            SELECT 
                alat.id_alat,
                alat.nama_alat,
                alat.deskripsi,
                alat.stock,
                COALESCE(pj.jumlah_dipinjam, 0) AS jumlah_dipinjam,
                (alat.stock - COALESCE(pj.jumlah_dipinjam, 0)) AS stok_tersedia
            FROM alat_lab alat
            LEFT JOIN (
                SELECT id_alat, COUNT(*) AS jumlah_dipinjam
                FROM peminjaman
                WHERE status = 'dipinjam'
                GROUP BY id_alat
            ) pj ON pj.id_alat = alat.id_alat";
    
    $conn->exec($sql);
    echo "<p style='color: green;'>✅ View 'view_alat_tersedia' berhasil dibuat!</p>";
} catch(PDOException $e) {
    echo "<p style='color: orange;'>⚠️ View 'view_alat_tersedia': " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Selesai!</strong> Views telah dibuat. Anda dapat menghapus file ini setelah selesai.</p>";
echo "<p><a href='../admin/dashboard.php'>Kembali ke Dashboard</a></p>";
?>
