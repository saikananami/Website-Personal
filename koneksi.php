<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "ukk2025_todolist";  // Pastikan nama database di sini benar

$koneksi = mysqli_connect($host, $username, $password, $dbname);

if (mysqli_connect_errno()) {
    echo "Koneksi database gagal: " . mysqli_connect_error();
}
?>
