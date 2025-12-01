<?php
require_once 'config/database.php';

$conn = getDBConnection();

echo "Testing JOIN fixes in service/peminjaman.php\n\n";

// Test 1: Check if views can be created without errors
try {
    echo "1. Testing view creation...\n";

    // View to see tools currently being borrowed
    $view_dipinjam_sql = "CREATE OR REPLACE VIEW view_alat_dipinjam AS
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
            pj.created_at,
            pj.id_ruang
        FROM peminjaman pj
        LEFT JOIN alat_lab alat ON alat.id_alat_lab = pj.id_alat
        WHERE pj.status = 'dipinjam'";
    $conn->exec($view_dipinjam_sql);
    echo "✓ view_alat_dipinjam created successfully\n";

    // View to see available tools with stock information
    $view_tersedia_sql = "CREATE OR REPLACE VIEW view_alat_tersedia AS
        SELECT
            alat.id_alat_lab,
            alat.nama_alat,
            alat.deskripsi,
            alat.stock,
            COALESCE(pj.jumlah_dipinjam, 0) AS jumlah_dipinjam,
            (alat.stock - COALESCE(pj.jumlah_dipinjam, 0)) AS stok_tersedia
        FROM alat_lab alat
        LEFT JOIN (
            SELECT id_alat, COUNT(*) AS jumlah_dipinjam
            FROM peminjaman
            WHERE status = 'dipinjam' AND id_alat IS NOT NULL
            GROUP BY id_alat
        ) pj ON pj.id_alat = alat.id_alat_lab
        WHERE (alat.stock - COALESCE(pj.jumlah_dipinjam, 0)) > 0
        ORDER BY alat.nama_alat";
    $conn->exec($view_tersedia_sql);
    echo "✓ view_alat_tersedia created successfully\n";

    // View untuk melihat ruangan yang dipinjam
    $view_ruang_dipinjam_sql = "CREATE OR REPLACE VIEW view_ruang_dipinjam AS
        SELECT
            pj.id_peminjaman,
            pj.id_ruang,
            r.nama_ruang,
            r.status as status_ruang,
            pj.nama_peminjam,
            pj.tanggal_pinjam,
            pj.tanggal_kembali,
            pj.waktu_pinjam,
            pj.waktu_kembali,
            pj.keterangan,
            pj.status,
            pj.created_at
        FROM peminjaman pj
        JOIN ruang_lab r ON r.id_ruang = pj.id_ruang
        WHERE pj.status = 'dipinjam'";
    $conn->exec($view_ruang_dipinjam_sql);
    echo "✓ view_ruang_dipinjam created successfully\n";

} catch(PDOException $e) {
    echo "✗ Error creating views: " . $e->getMessage() . "\n";
}

// Test 2: Test the main queries from service/peminjaman.php
try {
    echo "\n2. Testing main queries...\n";

    // Get alat lab yang tersedia
    $alat_stmt = $conn->query("
        SELECT
            alat.id_alat_lab,
            alat.nama_alat,
            alat.deskripsi,
            alat.stock,
            COALESCE(pj.jumlah_dipinjam, 0) AS jumlah_dipinjam,
            (alat.stock - COALESCE(pj.jumlah_dipinjam, 0)) AS stok_tersedia
        FROM alat_lab alat
        LEFT JOIN (
            SELECT id_alat, COUNT(*) AS jumlah_dipinjam
            FROM peminjaman
            WHERE status = 'dipinjam' AND id_alat IS NOT NULL
            GROUP BY id_alat
        ) pj ON pj.id_alat = alat.id_alat_lab
        WHERE (alat.stock - COALESCE(pj.jumlah_dipinjam, 0)) > 0
        ORDER BY alat.nama_alat
    ");
    $alat_list = $alat_stmt->fetchAll();
    echo "✓ Alat query executed successfully, found " . count($alat_list) . " available tools\n";

    // Get active peminjaman alat
    $peminjaman_alat_stmt = $conn->query("
        SELECT
            pj.id_peminjaman,
            pj.id_alat,
            alat.nama_alat,
            alat.deskripsi,
            pj.nama_peminjam,
            pj.tanggal_pinjam,
            pj.keterangan,
            pj.status
        FROM peminjaman pj
        JOIN alat_lab alat ON alat.id_alat = pj.id_alat
        WHERE pj.status = 'dipinjam' AND pj.id_alat IS NOT NULL
        ORDER BY pj.tanggal_pinjam DESC
    ");
    $peminjaman_alat_list = $peminjaman_alat_stmt->fetchAll();
    echo "✓ Active tool loans query executed successfully, found " . count($peminjaman_alat_list) . " active loans\n";

    // Get active peminjaman ruang
    $peminjaman_ruang_stmt = $conn->query("
        SELECT
            pj.id_peminjaman,
            pj.id_ruang,
            r.nama_ruang,
            pj.nama_peminjam,
            pj.tanggal_pinjam,
            pj.waktu_pinjam,
            pj.waktu_kembali,
            pj.keterangan,
            pj.status
        FROM peminjaman pj
        JOIN ruang_lab r ON r.id_ruang_lab = pj.id_ruang
        WHERE pj.status = 'dipinjam' AND pj.id_ruang IS NOT NULL
        ORDER BY pj.tanggal_pinjam DESC, pj.waktu_pinjam DESC
    ");
    $peminjaman_ruang_list = $peminjaman_ruang_stmt->fetchAll();
    echo "✓ Active room loans query executed successfully, found " . count($peminjaman_ruang_list) . " active loans\n";

} catch(PDOException $e) {
    echo "✗ Error in main queries: " . $e->getMessage() . "\n";
}

echo "\nTesting completed!\n";
?>