<?php
if (!isset($conn)) {
    header("Location: ../index.php?page=profil");
    exit;
}

$escape = static fn($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
?>
<h3>Profil Saya</h3>
<?php if (!empty($data)): ?>
  <div class="table-responsive">
    <table class="table table-bordered mt-3">
      <tr><th>NIM</th><td><?= $escape($data['nim'] ?? '') ?></td></tr>
      <tr><th>Nama</th><td><?= $escape($data['nama'] ?? '') ?></td></tr>
      <tr><th>Jenis Kelamin</th><td><?= $escape($data['gender'] ?? '') ?></td></tr>
      <tr><th>Kelas</th><td><?= $escape($data['kelas'] ?? '') ?></td></tr>
      <tr><th>Program Studi</th><td><?= $escape($data['prodi'] ?? '') ?></td></tr>
      <tr><th>Nomor Telepon</th><td><?= $escape($data['notelp'] ?? '') ?></td></tr>
      <tr><th>Email</th><td><?= $escape($data['email'] ?? '') ?></td></tr>
      <tr><th>Alamat</th><td><?= nl2br($escape($data['alamat'] ?? '')) ?></td></tr>
    </table>
  </div>
  <a href="pages/cetak.php" target="_blank" class="btn btn-success mt-3">
    <i class="fas fa-print"></i> Cetak Kartu Anggota
  </a>
<?php else: ?>
  <div class="alert alert-warning mt-3">Data tidak ditemukan.</div>
<?php endif; ?>
