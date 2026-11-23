<?php
if (!isset($conn)) {
    header("Location: ../index.php?page=404");
    exit;
}
?>
<div class="alert alert-danger mt-3" role="alert">
  <h4 class="alert-heading">
    <i class="fas fa-exclamation-triangle"></i> 404 - Halaman Tidak Ditemukan
  </h4>
  <p>Maaf, halaman yang Anda cari tidak tersedia atau telah dipindahkan.</p>
  <hr>
  <a href="?page=home" class="btn btn-primary btn-sm">
    <i class="fas fa-home"></i> Kembali ke Home
  </a>
</div>
