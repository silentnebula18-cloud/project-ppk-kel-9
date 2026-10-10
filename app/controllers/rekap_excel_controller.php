<?php

require_once __DIR__ . '/../helpers/validate_session.php';
check_session_valid('admin');
require_once __DIR__ . "/../models/facility_model.php";

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

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Rekap Fasilitas');

$headers = [
    'A1' => 'No',
    'B1' => 'Nama Fasilitas',
    'C1' => 'Tipe',
    'D1' => 'Lokasi',
    'E1' => 'Kapasitas',
    'F1' => 'Status',
    'G1' => 'Okupansi (Reservasi/bulan)',
    'H1' => 'Frekuensi Kerusakan (Laporan/bulan)',
];
foreach ($headers as $cell => $label) {
    $sheet->setCellValue($cell, $label);
}

$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['argb' => 'FFFFFDFA'],
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FF3E2A1E'],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['argb' => 'FF4E3626'],
        ],
    ],
];
$sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

$row = 2;
foreach ($facilities as $i => $f) {
    $sheet->setCellValue("A{$row}", $i + 1);
    $sheet->setCellValue("B{$row}", $f['fac_name']);
    $sheet->setCellValue("C{$row}", $f['type']);
    $sheet->setCellValue("D{$row}", $f['location']);
    $sheet->setCellValue("E{$row}", $f['capacity']);
    $sheet->setCellValue("F{$row}", $f['fac_status']);
    $sheet->setCellValue("G{$row}", (float) $f['rsv_avg']);
    $sheet->setCellValue("H{$row}", (float) $f['report_avg']);

    $statusColor = match (strtolower($f['fac_status'])) {
        'aktif' => 'FF2E7D4F',
        'dalam perbaikan' => 'FF8A5A00',
        'nonaktif' => 'FFB3261E',
        default => 'FF2A2019',
    };
    $sheet->getStyle("F{$row}")->applyFromArray([
        'font' => ['bold' => true, 'color' => ['argb' => $statusColor]],
    ]);

    if ($row % 2 === 0) {
        $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF5EFE6'],
            ],
        ]);
    }

    $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => 'FFDDD3C7'],
            ],
        ],
        'alignment' => [
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
    ]);

    $row++;
}

foreach (range('A', 'H') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$sheet->getStyle("A2:A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("G2:H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("G2:H{$row}")->getNumberFormat()->setFormatCode('0.0');

$sheet2 = $spreadsheet->createSheet();
$sheet2->setTitle('Rekap per Lokasi');
foreach (['A1' => 'Lokasi', 'B1' => 'Jumlah Fasilitas', 'C1' => 'Okupansi (Reservasi/bulan)', 'D1' => 'Frekuensi Kerusakan (Laporan/bulan)'] as $cell => $label) {
    $sheet2->setCellValue($cell, $label);
}
$sheet2->getStyle('A1:D1')->applyFromArray($headerStyle);

$row2 = 2;
foreach (getRekapPerLokasi($facilities) as $lok) {
    $sheet2->setCellValue("A{$row2}", $lok['location']);
    $sheet2->setCellValue("B{$row2}", (int) $lok['fac_count']);
    $sheet2->setCellValue("C{$row2}", (float) $lok['rsv_avg']);
    $sheet2->setCellValue("D{$row2}", (float) $lok['report_avg']);
    $sheet2->getStyle("A{$row2}:D{$row2}")->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => 'FFDDD3C7'],
            ],
        ],
    ]);
    $row2++;
}
foreach (range('A', 'D') as $col) {
    $sheet2->getColumnDimension($col)->setAutoSize(true);
}
$sheet2->getStyle("B2:D{$row2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet2->getStyle("C2:D{$row2}")->getNumberFormat()->setFormatCode('0.0');
$spreadsheet->setActiveSheetIndex(0);

$filename = 'rekap_fasilitas_' . date('Y-m-d') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;