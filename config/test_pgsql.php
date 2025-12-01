<?php
try {
    $pdo = new PDO("pgsql:host=localhost;port=5433;dbname=inlet_pbl", "postgres", "sileysa-984");
    echo "✅ Connection successful!";
} catch (PDOException $e) {
    echo "❌ Gagal: " . $e->getMessage();
}
?>