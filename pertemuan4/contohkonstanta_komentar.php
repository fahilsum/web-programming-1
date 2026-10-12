<html>
<head>
    <title>Menghitung Luas Lingkaran</title>
</head>
<body>
<?php
//konstanta untuk nilai judul
define("Judul","Hitung Luas Lingkaran");
// konstanta untuk nilahi phi
define("PHI", 3.14);
echo Judul;
$r=10;
echo "<br> Jari-jari : $r <br>";
$luas=PHI*$r*$r;
echo "Luas Lingkara = $luas";
?>
</body>
</html>