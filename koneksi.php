<?php
$host = "localhost";
$username = "postgres";
$password = "12345678";
$database = "db_lba"; // ← GANTI INI dari db_akademik ke lab_database

$conn_string = "host=$host dbname=$database user=$username password=$password";
$koneksi = pg_connect($conn_string);

if (!$koneksi) {
    die("Koneksi ke database gagal: " . pg_last_error());
}
?>