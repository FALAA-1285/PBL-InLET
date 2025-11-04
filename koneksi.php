<?php
$host     = "localhost";  
$user     = "root";     
$pass     = "";           
$db       = "prakwebdb";

// Membuat koneksi
$connect = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$connect) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>