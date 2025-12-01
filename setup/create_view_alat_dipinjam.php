<?php
/**
 * Script untuk membuat view view_alat_dipinjam
 * Jalankan script ini untuk membuat view jika belum ada
 */

require_once __DIR__ . '/../config/database.php';

$conn = getDBConnection();

echo "<h2>Membuat View view_alat_dipinjam</h2>";
echo "<hr>";

try {
    // View untuk melihat alat yang sedang dipinjam
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
    echo "<p style='color: green;'>✅ View 'view_alat_dipinjam' successfully created!</p>";
    
    // Verify the view was created
    $stmt = $conn->query("SELECT COUNT(*) FROM information_schema.views WHERE table_name = 'view_alat_dipinjam' AND table_schema = 'public'");
    $exists = $stmt->fetchColumn() > 0;
    
    if ($exists) {
        echo "<p style='color: green;'>✅ View successfully verified in database.</p>";
        
        // Test query
        $test_stmt = $conn->query("SELECT COUNT(*) FROM view_alat_dipinjam");
        $count = $test_stmt->fetchColumn();
        echo "<p>Jumlah data di view: <strong>" . $count . "</strong> alat yang sedang dipinjam.</p>";
    }
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Selesai!</strong> View telah dibuat.</p>";
echo "<p><a href='../admin/dashboard.php'>Kembali ke Dashboard</a></p>";
?>

