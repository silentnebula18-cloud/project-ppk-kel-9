<?php
require_once __DIR__ . "/../models/facility_model.php";

// Load Composer autoloader
$autoload = __DIR__ . "/../../vendor/autoload.php";
if (!file_exists($autoload)) {
    http_response_code(500);
    die("Library dompdf/dompdf belum terpasang. Jalankan: composer require dompdf/dompdf");
}
require_once $autoload;

use Dompdf\Dompdf;
use Dompdf\Options;

$facilities = getAllFacilities($conn);

// Susun HTML untuk PDF -
$tanggal = date('d/m/Y');
$namaFile = 'rekap_fasilitas_' . date('Y-m-d') . '.pdf';

// Bangun baris tabel
$tableRows = '';
foreach ($facilities as $i => $f) {
    $no = $i + 1;
    $nama = htmlspecialchars($f['fac_name']);
    $tipe = htmlspecialchars($f['type']);
    $lokasi = htmlspecialchars($f['location']);
    $kapasitas = (int) $f['capacity'];
    $status = htmlspecialchars($f['fac_status']);
    $rsv = (int) $f['rsv_count'];
    $report = (int) $f['report_count'];

    // Warna status
    $statusColor = match (strtolower($f['fac_status'])) {
        'aktif' => '#2E7D4F',
        'dalam perbaikan' => '#8A5A00',
        'nonaktif' => '#B3261E',
        default => '#2A2019',
    };

    // Warna baris selang-seling
    $bgColor = ($i % 2 === 0) ? '#FFFDFA' : '#F5EFE6';

    $tableRows .= "
        <tr style=\"background-color:{$bgColor};\">
            <td style=\"text-align:center;\">{$no}</td>
            <td>{$nama}</td>
            <td>{$tipe}</td>
            <td>{$lokasi}</td>
            <td style=\"text-align:center;\">{$kapasitas}</td>
            <td style=\"text-align:center; color:{$statusColor}; font-weight:bold;\">{$status}</td>
            <td style=\"text-align:center;\">{$rsv}</td>
            <td style=\"text-align:center;\">{$report}</td>
        </tr>
    ";
}

$html = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #2A2019;
        }

        /* Header laporan */
        .header {
            text-align: center;
            margin-bottom: 16px;
            border-bottom: 2px solid #C89B3C;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 16px;
            font-weight: bold;
            color: #3E2A1E;
        }
        .header p {
            font-size: 9px;
            color: #7A6F63;
            margin-top: 4px;
        }

        /* Tabel */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead tr {
            background-color: #3E2A1E;
            color: #ffffff;
        }
        thead th {
            padding: 6px 4px;
            text-align: center;
            font-size: 9px;
            border: 1px solid #4E3626;
        }
        tbody td {
            padding: 5px 4px;
            border: 1px solid #DDD3C7;
            font-size: 9px;
        }
        tbody tr:last-child td {
            border-bottom: 2px solid #C89B3C;
        }

        /* Footer */
        .footer {
            margin-top: 14px;
            font-size: 8px;
            color: #7A6F63;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rekap Data Fasilitas</h1>
        <p>Tanggal cetak: {$tanggal}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:4%;">No</th>
                <th style="width:18%;">Nama Fasilitas</th>
                <th style="width:12%;">Tipe</th>
                <th style="width:18%;">Lokasi</th>
                <th style="width:8%;">Kapasitas</th>
                <th style="width:10%;">Status</th>
                <th style="width:15%;">Frekuensi Reservasi</th>
                <th style="width:15%;">Frekuensi Kerusakan</th>
            </tr>
        </thead>
        <tbody>
            {$tableRows}
        </tbody>
    </table>

    <div class="footer">
        Dicetak secara otomatis oleh sistem &mdash; {$tanggal}
    </div>
</body>
</html>
HTML;

// Render PDF dengan Dompdf
$options = new Options();
$options->set('isRemoteEnabled', false);
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$dompdf->stream($namaFile, ['Attachment' => true]);
exit;