<?php
include('koneksi.php');
// Ambil data dari form (dengan filter dasar)
$nim = $_POST['nim'];
$nama = $_POST['nama'];
$alamat = $_POST['alamat'];
$password = md5($_POST['password']);
// Simpan ke database
$sql = "INSERT INTO user (nim, nama, alamat, password)
VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $nim, $nama, $alamat, $password);
if ($stmt->execute()) {
echo "<script>alert('Registrasi berhasil!');
window.location='../index.php';</script>";
} else {
echo "Gagal menyimpan data: " . $stmt->error;
}
$stmt->close();
$conn->close();
?>
