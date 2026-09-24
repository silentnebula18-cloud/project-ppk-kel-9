<?php

$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "projectppk";

$conn = mysqli_connect($host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>