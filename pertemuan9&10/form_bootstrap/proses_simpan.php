<?php
require_once __DIR__ . '/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

$nim      = trim($_POST['nim']      ?? '');
$nama     = trim($_POST['nama']     ?? '');
$gender   = trim($_POST['gender']   ?? '');
$notelp   = trim($_POST['notelp']   ?? '');
$email    = trim($_POST['email']    ?? '');
$kelas    = trim($_POST['kelas']    ?? '');
$prodi    = trim($_POST['prodi']    ?? '');
$alamat   = trim($_POST['alamat']   ?? '');
$password = $_POST['password']      ?? '';

if ($gender === 'L') {
    $gender = 'Laki-Laki';
} elseif ($gender === 'P') {
    $gender = 'Perempuan';
}

$errors = [];
if ($nim === '')                                   $errors[] = 'NIM wajib diisi';
elseif (strlen($nim) > 8)                          $errors[] = 'NIM maksimal 8 karakter';
if ($nama === '')                                  $errors[] = 'Nama wajib diisi';
elseif (strlen($nama) > 100)                       $errors[] = 'Nama maksimal 100 karakter';
if (!in_array($gender, ['Laki-Laki', 'Perempuan'], true)) $errors[] = 'Gender tidak valid';
if ($notelp === '')                                $errors[] = 'No. telepon wajib diisi';
elseif (strlen($notelp) > 13)                      $errors[] = 'No. telepon maksimal 13 karakter';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))    $errors[] = 'Email tidak valid';
elseif (strlen($email) > 100)                      $errors[] = 'Email maksimal 100 karakter';
if ($kelas === '')                                 $errors[] = 'Kelas wajib diisi';
elseif (strlen($kelas) > 10)                       $errors[] = 'Kelas maksimal 10 karakter';
if ($prodi === '')                                 $errors[] = 'Prodi wajib diisi';
elseif (strlen($prodi) > 50)                       $errors[] = 'Prodi maksimal 50 karakter';
if ($alamat === '')                                $errors[] = 'Alamat wajib diisi';
if (strlen($password) < 6)                         $errors[] = 'Password minimal 6 karakter';

if (!empty($errors)) {
    header('Location: index.html?status=error&msg=' . urlencode(implode(', ', $errors)));
    exit;
}

try {
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id FROM anggota WHERE nim = ? OR email = ? LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, 'ss', $nim, $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_close($stmt);
        mysqli_close($koneksi);
        header('Location: index.html?status=error&msg=' . urlencode('NIM atau email sudah terdaftar'));
        exit;
    }
    mysqli_stmt_close($stmt);

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = mysqli_prepare(
        $koneksi,
        "INSERT INTO anggota (nim, nama, gender, notelp, email, kelas, prodi, alamat, password)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param(
        $stmt,
        'sssssssss',
        $nim,
        $nama,
        $gender,
        $notelp,
        $email,
        $kelas,
        $prodi,
        $alamat,
        $passwordHash
    );
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($koneksi);

    header('Location: index.html?status=sukses');
} catch (mysqli_sql_exception $e) {
    $err = urlencode($e->getMessage());
    if (isset($koneksi) && $koneksi instanceof mysqli) {
        mysqli_close($koneksi);
    }
    header("Location: index.html?status=error&msg=$err");
}
exit;
?>
