# Laporan Analisis Bug dan Kekurangan Kode — `ormawa_center`

Dokumen ini berisi hasil analisis seluruh file aplikasi pada folder `pertemuan9&10/ormawa_center` (di luar library pihak ketiga `vendor/`), termasuk pemeriksaan sintaks PHP 8.3 dan kesesuaian terhadap skema database `ormawa` (tabel `anggota`).

---

## Ringkasan Eksekutif

Aplikasi saat ini **tidak dapat digunakan secara normal** karena terdapat beberapa bug fatal yang memblokir alur utama:
1. **Login dan Registrasi gagal (`404 Not Found`)** akibat kesalahan path CSS, JavaScript, `action` form, dan redirect relatif antar folder.
2. **Tampilan Dashboard (`index.php`) rusak** karena tautan CDN Bootstrap CSS & JS hanya mengarah ke `https://jsdelivr.net`, bukan file aset Bootstrap.
3. **Fitur Cetak PDF (`pages/cetak.php`) crash (`HTTP 500` / Parse Error)** karena ada teks komentar yang terpotong ke baris baru di luar `//`.
4. **Celah Keamanan Kritis** berupa *SQL Injection* pada login, *Local File Inclusion (LFI)* pada routing `?page=`, penyimpanan password menggunakan `md5()`, dan tidak adanya proteksi akses langsung file internal maupun *CSRF protection*.

---

## 1. Bug Kritis (Memblokir Fitur Utama)

### 1.1. Path Aset & Form Action Salah pada `login.php` dan `registrasi.php`
File `login.php` dan `registrasi.php` berada di root `ormawa_center/`, tetapi masih merujuk aset dan handler seolah-olah berada di folder yang sama (padahal sudah dipindahkan ke `css/`, `javascript/`, dan `koneksi/`):

| File | Baris | Kode Saat Ini | Seharusnya / Dampak |
| :--- | :---: | :--- | :--- |
| `login.php` | 7 | `href="main.css"` | `href="css/main.css"` (CSS tidak termuat / 404) |
| `login.php` | 12 | `action="ceklogin.php"` | `action="koneksi/ceklogin.php"` (Submit login 404) |
| `login.php` | 20 | `src="script.js"` | `src="javascript/script.js"` (JS tidak termuat / 404) |
| `registrasi.php` | 7 | `href="main.css"` | `href="css/main.css"` (CSS tidak termuat / 404) |
| `registrasi.php` | 13 | `action="add_registrasi.php"` | `action="koneksi/add_registrasi.php"` (Submit daftar 404) |
| `registrasi.php` | 39 | `<script src="script.js></script>` | **Tanda kutip penutup `"` hilang** dan path salah; seharusnya `<script src="javascript/script.js"></script>` |

### 1.2. Redirect Relatif Salah di `koneksi/ceklogin.php` dan `koneksi/add_registrasi.php`
- **`koneksi/ceklogin.php` (baris 16)**:
  ```php
  window.location='login.php';
  ```
  Karena file berada di dalam subfolder `koneksi/`, browser diarahkan ke `koneksi/login.php` (`404 Not Found`). Seharusnya `../login.php`.
- **`koneksi/add_registrasi.php` (baris 23)**:
  ```php
  window.location='login.php';
  ```
  Sama-sama mengarah ke `koneksi/login.php` (`404 Not Found`). Seharusnya `../login.php`.

### 1.3. Syntax Error Fatal pada `pages/cetak.php`
- **`pages/cetak.php` (baris 13–15)**:
  ```php
  $pdf = new FPDF('L', 'mm', array(85, 80)); // Ukuran kartu ID
  (landscape)
  $pdf->AddPage();
  ```
  Kata `(landscape)` terpotong ke baris baru di luar komentar `//`. Hasil uji `php -l`:
  > `PHP Parse error: syntax error, unexpected variable "$pdf" in ...\pages\cetak.php on line 15`
  
  Akibatnya fitur cetak kartu anggota selalu error total.

### 1.4. Link CDN Bootstrap Salah Total pada `index.php`
- **`index.php` (baris 22 & 83)**:
  ```html
  <link href="https://jsdelivr.net" rel="stylesheet">
  ...
  <script src="https://jsdelivr.net"></script>
  ```
  Kedua tag tersebut hanya mengarah ke halaman utama `jsdelivr.net` (HTML, bukan file CSS/JS Bootstrap 5). Akibatnya seluruh class Bootstrap (`navbar`, `row`, `col-md-*`, `sidebar collapse`, `table`, `btn`, `alert`, dll.) tidak berfungsi sama sekali.

### 1.5. Tag HTML `<form>` dan `<div>` Tumpang Tindih pada `registrasi.php`
- **`registrasi.php` (baris 11–35)**:
  ```html
  <div class="login-container">
    <form action="add_registrasi.php" method="POST" ...>
      ...
  </div> <!-- div pertama ditutup di dalam form! -->
  <div class="space"></div>
  <div class="login-container">
      ...
    </form> <!-- form ditutup di dalam div kedua! -->
  ```
  Penutupan `</div>` dan pembukaan `<div>` baru di tengah-tengah blok `<form>...</form>` adalah HTML invalid (*misnested tags*). Parser browser dapat menutup `<form>` lebih awal pada `</div>` pertama sehingga input di container kedua (`kelas`, `prodi`, `notelp`, `alamat`, `email`, `password`) dan tombol submit berisiko tidak terkirim atau tidak tervalidasi sebagai satu form.

---

## 2. Celah Keamanan (*Security Vulnerabilities*)

### 2.1. *SQL Injection* pada `koneksi/ceklogin.php` (Kritis)
- **`koneksi/ceklogin.php` (baris 3 & 6)**:
  ```php
  $username = $_POST['username'];
  ...
  $sql = "SELECT * FROM anggota WHERE nim='$username' AND password='$password'";
  ```
  Input pengguna dimasukkan langsung ke query SQL tanpa *prepared statement*. Penyerang dapat login tanpa mengetahui password dengan memasukkan payload SQL injection seperti `' OR '1'='1' -- `.
- **`index.php` (baris 10–11)** & **`pages/cetak.php` (baris 5–6)**:
  Query `"SELECT * FROM anggota WHERE id = $id"` juga menyisipkan variabel secara langsung tanpa prepared statement atau type-casting `(int)`.

### 2.2. *Local File Inclusion (LFI)* / *Path Traversal* pada `index.php` (Tinggi)
- **`index.php` (baris 66–72)**:
  ```php
  $page = isset($_GET['page']) ? $_GET['page'] : 'home';
  $file = "pages/$page.php";
  if (file_exists($file)) {
      include $file;
  }
  ```
  Parameter `$_GET['page']` tidak dibatasi dengan *whitelist* (misal `['home', 'profil']`). Pengguna yang sudah login dapat mengakses `?page=../koneksi/logout` atau path relatif lainnya (`../`) untuk mengeksekusi file `.php` di luar folder `pages/`, atau `?page=cetak` yang akan menyebabkan FPDF gagal karena output HTML sudah terkirim lebih dulu.

### 2.3. Hashing Password Lemah (`MD5` Tanpa Salt)
- **`koneksi/add_registrasi.php` (baris 13)** & **`koneksi/ceklogin.php` (baris 4)**:
  Menggunakan `md5($_POST['password'])`. Algoritma MD5 sudah usang, sangat cepat dipecahkan dengan *rainbow table* atau GPU brute-force, serta menghasilkan hash identik untuk password yang sama. Sebaiknya gunakan `password_hash()` dan `password_verify()`.

### 2.4. Tidak Ada Cek Autentikasi Session pada `pages/cetak.php`, `pages/home.php`, `pages/profil.php`
- **`pages/cetak.php` (baris 5)**: Langsung mengakses `$_SESSION['id']` tanpa memeriksa `if (!isset($_SESSION['id']))`. Jika file diakses langsung via URL tanpa login, terjadi error `Undefined array key "id"` diikuti *SQL syntax error* (`WHERE id = `).
- **`pages/home.php` & `pages/profil.php`**: Dapat diakses langsung melalui URL `pages/home.php` atau `pages/profil.php` tanpa pengecekan session, memicu error `Undefined variable $data`.

### 2.5. Tidak Ada `session_regenerate_id(true)` Saat Login
- **`koneksi/ceklogin.php` (baris 11)**: Session ID tidak diperbarui setelah autentikasi berhasil, membuat aplikasi rentan terhadap serangan *Session Fixation*.

### 2.6. File Pendukung & Arsip Terbuka di Web Root (`vendor/` dan `koneksi/`)
- Folder `vendor/` berisi file arsip mentah **`fpdf186.zip`**, dokumentasi (`doc/`), tutorial PHP yang dapat dieksekusi (`tutorial/tuto1.php`–`tuto7.php`), dan `makefont/makefont.php` di area publik.
- Tidak ada proteksi *CSRF token* pada form login, registrasi, maupun logout.

---

## 3. Bug Fungsional, Validasi, dan Penanganan Error

### 3.1. Tidak Ada Pengecekan Method `POST` & Validasi Input Server-Side
- **`koneksi/ceklogin.php` (baris 3–4)**: Mengakses `$_POST['username']` dan `$_POST['password']` langsung. Jika URL dibuka via `GET`, muncul PHP Warning `Undefined array key` dan `Deprecated: md5(): Passing null...` di PHP 8.3.
- **`koneksi/add_registrasi.php` (baris 5–13)**:
  - Tidak mengecek `$_SERVER['REQUEST_METHOD'] === 'POST'`.
  - Tidak ada validasi field kosong, format email (`FILTER_VALIDATE_EMAIL`), nilai enum gender (`Laki-Laki` / `Perempuan`), maupun panjang maksimum sesuai skema tabel `anggota` di database `ormawa`:
    - `nim`: `varchar(8)`
    - `notelp`: `varchar(13)`
    - `kelas`: `varchar(10)`
    - `prodi`: `varchar(50)`
    - `nama`, `email`: `varchar(100)`
  - Pada PHP 8.1+, jika input melebihi batas kolom (misal NIM > 8 karakter) atau koneksi bermasalah, `mysqli` melempar `mysqli_sql_exception` yang belum ditangani dengan `try/catch`, sehingga blok `else` pada baris 24 tidak pernah berjalan dan error mentah database tampil ke layar.
  - Tidak ada pengecekan duplikat `nim` atau `email` sebelum `INSERT`.

### 3.2. Konflik Validasi JavaScript (`javascript/script.js`) & DOM (`login.php`, `registrasi.php`)
- **`javascript/script.js` (baris 1–11)**:
  ```javascript
  const username = document.getElementById("username").value.trim();
  const password = document.getElementById("password").value.trim();
  const error = document.getElementById("errorMessage");
  ```
  - **Crash di `registrasi.php`**: `registrasi.php` juga memanggil `onsubmit="return validateForm()"`, padahal elemen `#username` dan `#errorMessage` tidak ada di `registrasi.php` (hanya ada `#nim` dan `#registerError`). Pemanggilan `.value` pada `null` menyebabkan `TypeError` sehingga validasi gagal jalan dan form tetap ter-submit.
  - **Menimpa Link Registrasi di `login.php` (baris 17–18)**: Elemen `<p id="errorMessage" class="error-message">` di `login.php` membungkus `<a href="registrasi.php">Klik untuk Registrasi</a>`. Ketika `error.textContent = ...` dijalankan, tautan registrasi akan hilang tertimpa pesan error.
  - Memanggil `.trim()` pada password secara keliru menolak/memodifikasi logika spasi pada password.

### 3.3. File `pages/404.php` Kosong (0 Bytes)
- **`pages/404.php`**: Berisi 0 byte. Jika user mengakses `?page=tidakada` pada `index.php`, halaman tampil kosong di bagian konten utama tanpa pesan kesalahan dan tanpa kode status HTTP `404`.

### 3.4. Penanganan Null Data Anggota di Dashboard & Profil
- **`index.php` (baris 13)**, **`pages/home.php` (baris 1)**, **`pages/profil.php` (baris 4–18)**:
  - Jika akun terhapus dari database ketika session masih aktif, `$data` bernilai `null`. `pages/home.php` langsung mengakses `$data['nama']` yang memicu warning PHP 8.
  - Di `pages/profil.php`, kolom-kolom tabel `anggota` bernilai `DEFAULT NULL` di database. Jika ada nilai `NULL`, `htmlspecialchars($data['...'])` di PHP 8.3 memicu warning `Deprecated: Passing null to parameter #1`.
  - Di `pages/profil.php` (baris 23–25), tombol **Cetak Kartu Anggota** berada di luar blok `<?php if ($data): ?> ... <?php endif; ?>`, sehingga tetap muncul meskipun data tidak ditemukan.

### 3.5. Pembersihan Session Belum Lengkap di `koneksi/logout.php`
- **`koneksi/logout.php` (baris 1–5)**: Hanya memanggil `session_destroy()` tanpa mengosongkan `$_SESSION = []` dan tanpa menghapus cookie `PHPSESSID` di browser.

---

## 4. Bug UI, CSS, dan Tata Letak

### 4.1. `css/main.css`
- **Layout potong/overflow (baris 7–14)**: `body` memakai `height: 100vh` (bukan `min-height: 100vh`) dan `display: flex` tanpa `flex-wrap: wrap` atau `flex-direction: column` pada layar menengah/kecil. Pada `registrasi.php` yang memiliki dua `.login-container` (masing-masing `360px` + `.space` `60px` = `780px`), tampilan akan meluber horizontal pada layar `< 780px`, dan media query `@media (max-width: 420px)` tidak mengubah arah flex.
- **Properti CSS tidak valid (baris 81)**: `align-items: left;` pada `.radio-label` bukan nilai CSS yang valid (seharusnya `flex-start`).
- **Radio button rusak (baris 31–38 & 103–124)**: Aturan `.login-container input` memberikan `padding: 12px;` dan `margin: 10px 0;` ke seluruh `<input>`, termasuk `input[type="radio"]`. Karena tidak di-reset pada `.radio-group input[type="radio"]`, ukuran radio menjadi membesar/tidak proporsional dan titik `::before` tidak berada di tengah lingkaran.

### 4.2. `css/dash.css` & `javascript/sidebar-toggle.js`
- **`css/dash.css` (baris 2–5)**: `#sidebar` diberi `min-height: 100vh`, padahal di atasnya sudah ada `<header>` navbar sehingga total tinggi melebihi 100vh. Selain itu indentasi CSS tidak rapi.
- **`javascript/sidebar-toggle.js` (baris 1–4)**: Tidak memiliki pengecekan `if (toggle && sidebar)` dan hanya mengandalkan CSS Bootstrap yang saat ini gagal termuat.

### 4.3. `pages/home.php` & `pages/profil.php`
- **`pages/home.php` (baris 3–4)**: Menggunakan atribut usang/tidak valid `<p align="center"><img src="img/logo.png" size="35px" width="400" height="400"></p>`. Atribut `size` tidak valid pada `<img>`, tidak ada atribut `alt`, `align="center"` sudah *obsolete* di HTML5, dan ukuran `400px` tetap tanpa `img-fluid` akan overflow di layar sempit.
- **`pages/profil.php` (baris 13)**: Typo label `"Nomer Telphone"` (seharusnya `"Nomor Telepon"`), dan `<table>` belum dibungkus `.table-responsive`.

### 4.4. Tata Letak Kartu PDF di `pages/cetak.php`
- **Melebihi batas lebar halaman & border (baris 13, 18, 29–44)**: Halaman berukuran lebar `85mm` (`array(85, 80)`), border `Rect(2, 2, 81, 50)` berakhir di `X = 83mm`, sedangkan sel teks dimulai di `X = 10mm` dengan lebar total `25 + 2 + 50 = 77mm` sehingga berakhir di **`X = 87mm`** (melewati batas kotak kanan `83mm` dan kertas `85mm`).
- **Proporsi tinggi halaman (baris 13 & 18)**: Tinggi kertas `80mm` sementara kotak kartu hanya setinggi `50mm`, menyisakan ruang kosong besar di bawah.
- **Encoding teks**: FPDF `Arial` menggunakan ISO-8859-1/Windows-1252; teks UTF-8 dengan karakter non-ASCII perlu konversi agar tidak rusak saat dicetak.

---

## 5. Rangkuman Prioritas Perbaikan yang Disarankan

1. **Perbaiki seluruh path & markup dasar**:
   - Betulkan path `css/main.css`, `javascript/script.js`, `koneksi/ceklogin.php`, `koneksi/add_registrasi.php`, dan tutup tanda kutip `<script src="javascript/script.js"></script>` di `login.php` & `registrasi.php`.
   - Perbaiki struktur `<form>` di `registrasi.php` agar membungkus seluruh input dengan benar (gunakan satu `<div class="login-container">` atau letakkan `<form>` di luar kedua container), serta tambahkan `<meta name="viewport">`.
   - Ganti tag Bootstrap pada `index.php` ke URL CDN Bootstrap 5 yang lengkap (CSS & JS bundle).
   - Hapus baris `(landscape)` yang keluar dari komentar di `pages/cetak.php` dan sesuaikan koordinat/lebar `Cell` dengan ukuran kartu.
2. **Tutup celah keamanan**:
   - Gunakan *prepared statement* pada `koneksi/ceklogin.php`, `index.php`, dan `pages/cetak.php`.
   - Terapkan *whitelist* pada parameter `?page=` di `index.php` (misal hanya `home` dan `profil`, selain itu arahkan ke `pages/404.php` yang sudah diisi tampilan 404).
   - Tambahkan pengecekan session pada file di dalam `pages/` (atau blokir akses langsungnya), tambahkan `session_regenerate_id(true)` saat login, dan beralih ke `password_hash()` / `password_verify()`.
3. **Perbaiki validasi, koneksi & tampilan**:
   - Tambahkan `set_charset("utf8mb4")`, guard `session_status()`, dan validasi input POST/panjang field/duplikasi NIM-email di `koneksi/add_registrasi.php`.
   - Pisahkan logika validasi `javascript/script.js` untuk login vs registrasi serta pisahkan `<p id="errorMessage">` dari link registrasi di `login.php`.
   - Rapikan CSS responsif di `css/main.css` (reset `padding` radio button, ganti `height: 100vh` ke `min-height: 100vh`, perbaiki `align-items: flex-start`) dan isi konten informatif pada `pages/404.php`.
   - Hapus file arsip/tutorial publik tidak terpakai (`vendor/fpdf186.zip`, `vendor/doc/`, `vendor/tutorial/`) sebelum rilis produksi.
