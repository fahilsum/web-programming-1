<?php
if (headers_sent()) {
    echo '<div class="alert alert-info mt-3">Halaman cetak tidak dapat dimuat di dalam dashboard. '
        . '<a href="pages/cetak.php" target="_blank" class="alert-link">Klik di sini untuk membuka Kartu Anggota (PDF)</a>.</div>';
    return;
}

require_once __DIR__ . '/../vendor/fpdf.php';
require_once __DIR__ . '/../koneksi/koneksi.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit;
}

$id = (int) $_SESSION['id'];
$stmt = $conn->prepare("SELECT nim, nama, gender, kelas, prodi FROM anggota WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$data) {
    http_response_code(404);
    die("Data tidak ditemukan.");
}

/**
 * Mengonversi teks UTF-8 ke CP1252 untuk font standar FPDF dan memotong jika melebihi lebar sel.
 */
function formatPdfText(FPDF $pdf, $value, float $cellWidth): string
{
    $text = trim((string) ($value ?? ''));
    if ($text !== '' && function_exists('iconv')) {
        $converted = @iconv('UTF-8', 'CP1252//TRANSLIT//IGNORE', $text);
        if ($converted !== false) {
            $text = $converted;
        }
    }

    $maxTextWidth = max(1.0, $cellWidth - 2.0);
    if ($pdf->GetStringWidth($text) <= $maxTextWidth) {
        return $text;
    }

    $ellipsis = '...';
    $limit = $maxTextWidth - $pdf->GetStringWidth($ellipsis);
    while ($text !== '' && $pdf->GetStringWidth($text) > $limit) {
        $text = substr($text, 0, -1);
    }

    return rtrim($text) . $ellipsis;
}

// Buat PDF baru ukuran kartu ID standar (85 x 54 mm landscape)
$pdf = new FPDF('L', 'mm', array(85, 54));
$pdf->SetMargins(5, 5, 5);
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();

// Border kartu
$pdf->SetDrawColor(0, 0, 0);
$pdf->Rect(2, 2, 81, 50, 'D');

// Logo
$logoPath = __DIR__ . '/../img/logo.png';
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, 4, 3.5, 9);
}

// Judul
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetXY(15, 5);
$pdf->Cell(66, 6, 'KARTU ANGGOTA ORMAWA', 0, 1, 'C');

// Garis bawah
$pdf->Line(4, 13.5, 81, 13.5);

// Isi data
$fields = [
    'NIM'           => 'nim',
    'Nama'          => 'nama',
    'Jenis Kelamin' => 'gender',
    'Kelas'         => 'kelas',
    'Program Studi' => 'prodi',
];

$pdf->SetFont('Arial', '', 8.5);
$pdf->SetXY(5, 16);
foreach ($fields as $label => $key) {
    $pdf->Cell(24, 4.5, $label, 0, 0);
    $pdf->Cell(3, 4.5, ':', 0, 0);
    $pdf->Cell(48, 4.5, formatPdfText($pdf, $data[$key] ?? '', 48), 0, 1);
}

// Tanggal cetak
$pdf->SetFont('Arial', 'I', 7.5);
$pdf->SetXY(5, 45);
$pdf->Cell(75, 4, 'Dicetak: ' . date('d-m-Y'), 0, 0, 'R');

if (ob_get_length()) {
    ob_end_clean();
}

// Output
$pdf->Output('I', 'kartu-anggota.pdf');
