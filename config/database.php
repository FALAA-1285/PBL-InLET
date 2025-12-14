<?php
// Database Configuration
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '5432');
define('DB_NAME', 'pbl_inlet'); // Ganti dengan nama database Anda
define('DB_USER', 'postgres'); // Ganti dengan username PostgreSQL Anda
define('DB_PASS', '123456789'); // Ganti dengan password PostgreSQL Anda


// Koneksi ke PostgreSQL
function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
            $conn = new PDO($dsn, DB_USER, DB_PASS);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    return $conn;
}

// Test connection (optional)
// $conn = getDBConnection();
// echo "Connected successfully";
?>

