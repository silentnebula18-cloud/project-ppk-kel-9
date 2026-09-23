<?php
    // Kredensial Konfigurasi Database
    $host = "localhost";
    $username = "root";
    $password = "mysql8034";
    $database = "projectppk";

    // Membuat koneksi ke database
    $conn = mysqli_connect($host, $username, $password, $database);

    // Cek Koneksi
    if(!$conn){
        die("Koneksi ke database gagal:" . mysqli_connect_error());
    }
?>