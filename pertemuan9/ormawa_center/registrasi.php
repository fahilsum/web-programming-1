<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Registrasi Ormawa</title>
  <link rel="stylesheet" href="main.css">
</head>

<body>
  <div class="login-container">
    <h2>Form Registrasi</h2>
    <form action="add_registrasi.php" method="POST" onsubmit="return validateForm()">
      <input type="text" name="nim" id="nim" placeholder="NIM" required>
      <input type="text" name="nama" id="nama" placeholder="Nama Lengkap" required>
      <label class="radio-label">Jenis Kelamin:</label>
      <div class="radio-group">
        <label>
          <input type="radio" name="jeniskel" value="Laki-Laki" required>Laki-laki
        </label>
        <label>
          <input type="radio" name="jeniskel" value="Perempuan">Perempuan
        </label>
      </div>
  </div>
  <div class="space"></div>
  <div class="login-container">
      <input type="text" name="kelas" id="kelas" placeholder="Kelas" required>
      <input type="text" name="prodi" id="prodi" placeholder="Program Studi" required>
      <input type="number" name="notelp" id="notelp" placeholder="Nomor Telephone" required>
      <input type="text" name="alamat" id="alamat" placeholder="Alamat" required>
      <input type="email" name="email" id="email" placeholder="Email" required>
      <input type="password" name="password" id="password" placeholder="Password" required>
      <button type="submit">Daftar</button>
    </form>
    <p id="registerError" class="error-message"></p>
    <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
  </div>
  <script src="script.js></script>
</body>
</html>