<html>
<head>
      <title>Form Registrasi</title>
</head>
<body bgcolor="white">
<form action="tampilformregistrasi.php" method="post">
<h1>Form Registrasi</h1>
<p>Isi Data Dibawah ini :</p>
<p>Nama : <input type="text" name="nama" size="25" maxlength="50">
<p>Alamat : <textarea rows="3" cols="25" name="alamat"> </textarea>
<p>Tempat Lahir : <input type="text" name="tempatlahir" size="25" maxlength="50">
<p>Tanggal Lahir : <input type="text" name="tanggallahir" size="25" maxlength="50">
<p>Jenis Kelamin :
<input type="radio" name="jeniskel" value="Laki-Laki"> Laki - Laki
<input type="radio" name="jeniskel" value="Perempuan"> Perempuan
<p>Pendidikan :
   <select id="tingkat" name="pendidikan" required>
         <option value="">-- Pilih Pendidikan --</option>
         <option value="Sistem Informasi">S1</option>
         <option value="Teknologi Informasi">S2</option>
         <option value="Teknik Elektro">S3</option>
   </select><br><br>
<p><input type="submit" value="Submit"><input type="reset" value="Batal">
</form>
</body>
</html>
