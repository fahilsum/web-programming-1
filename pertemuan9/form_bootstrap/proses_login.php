<?php
session_start();
require_once __DIR__ . '/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

$identifier = trim($_POST['email'] ?? '');
$password   = $_POST['password'] ?? '';

if ($identifier === '' || $password === '') {
    header('Location: index.html?status=error&msg=' . urlencode('Email/NIM dan password wajib diisi'));
    exit;
}

function verifyStoredPassword(string $inputPassword, string $storedHash): bool
{
    if ($storedHash === '') {
        return false;
    }
    if (password_verify($inputPassword, $storedHash)) {
        return true;
    }
    if (
        strlen($storedHash) === 32
        && ctype_xdigit($storedHash)
        && hash_equals(strtolower($storedHash), md5($inputPassword))
    ) {
        return true;
    }
    return false;
}

try {
    $authenticated = false;

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id, nim, nama, email, password FROM anggota WHERE email = ? OR nim = ? LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt, 'ss', $identifier, $identifier);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $anggota = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($anggota && verifyStoredPassword($password, (string) $anggota['password'])) {
        $_SESSION['id']    = $anggota['id'];
        $_SESSION['nim']   = $anggota['nim'];
        $_SESSION['nama']  = $anggota['nama'];
        $_SESSION['email'] = $anggota['email'];
        $authenticated = true;
    } else {
        $stmtUser = mysqli_prepare(
            $koneksi,
            "SELECT iduser, username, password FROM user WHERE username = ? LIMIT 1"
        );
        mysqli_stmt_bind_param($stmtUser, 's', $identifier);
        mysqli_stmt_execute($stmtUser);
        $resUser = mysqli_stmt_get_result($stmtUser);
        $userRow = mysqli_fetch_assoc($resUser);
        mysqli_stmt_close($stmtUser);

        if ($userRow && verifyStoredPassword($password, (string) $userRow['password'])) {
            $_SESSION['iduser']   = $userRow['iduser'];
            $_SESSION['username'] = $userRow['username'];
            $authenticated = true;
        }
    }

    mysqli_close($koneksi);

    if ($authenticated) {
        // Redirect client-side ke about:blank karena browser memblokir HTTP Location: about:blank (ERR_UNSAFE_REDIRECT).
        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8" />
    <title>Login Berhasil</title>
    <script>
      window.location.replace("about:blank");
    </script>
  </head>
  <body></body>
</html>';
        exit;
    }

    header('Location: index.html?status=error&msg=' . urlencode('Email/NIM atau password salah'));
} catch (mysqli_sql_exception $e) {
    $err = urlencode($e->getMessage());
    if (isset($koneksi) && $koneksi instanceof mysqli) {
        mysqli_close($koneksi);
    }
    header("Location: index.html?status=error&msg=$err");
}
exit;
?>
