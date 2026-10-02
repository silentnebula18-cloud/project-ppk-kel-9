<?php
require_once __DIR__ . "/../models/facility_model.php";

// Load Composer autoloader
$autoload = __DIR__ . "/../../vendor/autoload.php";
if (!file_exists($autoload)) {
    http_response_code(500);
    die("Library phpoffice/phpspreadsheet belum terpasang. Jalankan: composer require phpoffice/phpspreadsheet");
}
require_once $autoload;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

$facilities = getAllFacilities($conn);

// ---------- Buat spreadsheet ----------
$spreadsheet = new Spreadsheet();
$sheet       = $spreadsheet->getActiveSheet();
$sheet->setTitle('Rekap Fasilitas');

// ----- Header -----
$headers = [
    'A1' => 'No',
    'B1' => 'Nama Fasilitas',
    'C1' => 'Tipe',
    'D1' => 'Lokasi',
    'E1' => 'Kapasitas',
    'F1' => 'Status',
    'G1' => 'Frekuensi Reservasi',
    'H1' => 'Frekuensi Kerusakan',
];
foreach ($headers as $cell => $label) {
    $sheet->setCellValue($cell, $label);
}

// Style header: bold, background coklat, teks emas, border
$headerStyle = [
    'font' => [
        'bold'  => true,
        'color' => ['argb' => 'FFFFFDFA'],
    ],
    'fill' => [
        'fillType'   => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FF3E2A1E'],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical'   => Alignment::VERTICAL_CENTER,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color'       => ['argb' => 'FF4E3626'],
        ],
    ],
];
$sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

// ----- Isi data -----
$row = 2;
foreach ($facilities as $i => $f) {
    $sheet->setCellValue("A{$row}", $i + 1);
    $sheet->setCellValue("B{$row}", $f['fac_name']);
    $sheet->setCellValue("C{$row}", $f['type']);
    $sheet->setCellValue("D{$row}", $f['location']);
    $sheet->setCellValue("E{$row}", $f['capacity']);
    $sheet->setCellValue("F{$row}", $f['fac_status']);
    $sheet->setCellValue("G{$row}", (int) $f['rsv_count']);
    $sheet->setCellValue("H{$row}", (int) $f['report_count']);

    // Warna teks status, sama kayak badge di web
    $statusColor = match (strtolower($f['fac_status'])) {
        'aktif' => 'FF2E7D4F',
        'dalam perbaikan' => 'FF8A5A00',
        'nonaktif' => 'FFB3261E',
        default => 'FF2A2019',
    };
    $sheet->getStyle("F{$row}")->applyFromArray([
        'font' => ['bold' => true, 'color' => ['argb' => $statusColor]],
    ]);

    // Zebra striping tiap baris genap
    if ($row % 2 === 0) {
        $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF5EFE6'],
            ],
        ]);
    }

    // Border data
    $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color'       => ['argb' => 'FFDDD3C7'],
            ],
        ],
        'alignment' => [
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
    ]);

    $row++;
}

// ----- Auto-size kolom -----
foreach (range('A', 'H') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// ----- Kolom numerik rata tengah -----
$sheet->getStyle("A2:A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("G2:H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// ----- Kirim sebagai file download -----
$filename = 'rekap_fasilitas_' . date('Y-m-d') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;