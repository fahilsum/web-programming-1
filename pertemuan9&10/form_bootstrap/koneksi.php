<?php
$host = "localhost";
$db   = "ormawa";
$user = "root";
$pass = "";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $koneksi = mysqli_connect($host, $user, $pass, $db);
    mysqli_set_charset($koneksi, "utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>