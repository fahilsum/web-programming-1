<html>
<head>
      <title>Data Registrasi</title>
</head>
<body>
<?php
$nama =$_POST['nama'];
$alamat =$_POST['alamat'];
$tempatlahir =$_POST['tempatlahir'];
$tanggallahir =$_POST['tanggallahir'];
$jeniskel =$_POST['jeniskel'];
$pendidikan =$_POST['pendidikan'];

?>
<table border=1 bgcolor="white">
<tr>
<h1>Data Registrasi</h1>
</tr>
<tr><td>Nama</td><td><?php echo $nama; ?></td></tr>
<tr><td>Alamat</td><td><?php echo $alamat; ?></td></tr>
<tr><td>Tempat Lahir</td><td><?php echo $tempatlahir; ?></td></tr>
<tr><td>Tanggal Lahir</td><td><?php echo $tanggallahir; ?></td></tr>
<tr><td>Jenis Kelamin</td><td><?php echo $jeniskel; ?></td></tr>
<tr><td>Pendidikan</td><td><?php echo $pendidikan; ?></td></tr>
</table>
<a href="formregistrasi.php">Back To Home</a>
</body>
</html>
