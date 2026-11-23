<?php
if (!isset($conn)) {
    header("Location: ../index.php?page=home");
    exit;
}
$nama = isset($data['nama']) && $data['nama'] !== '' ? (string) $data['nama'] : 'Anggota';
?>
<h2>Selamat Datang, <?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8'); ?>!</h2>
<p>Anda berhasil login ke sistem Ormawa.</p>
<div class="text-center my-4">
  <img
    src="img/logo.png"
    alt="Logo Ormawa"
    class="img-fluid"
    style="width: 400px; max-width: 100%; height: auto;"
  >
</div>
