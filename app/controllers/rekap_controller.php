<?php

require_once _DIR_ . '/../../app/helpers/validate_session.php';
check_session_valid('admin');
require_once __DIR__ . "/../models/facility_model.php";

$facilities = getAllFacilities($conn);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=rekap_fasilitas_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

// baris pertama = nama kolom
fputcsv($output, ['Nama Fasilitas', 'Tipe', 'Lokasi', 'Status', 'Okupansi (Reservasi/bulan)', 'Frekuensi Kerusakan (Laporan/bulan)']);

foreach ($facilities as $f) {
    fputcsv($output, [
        $f['fac_name'],
        $f['type'],
        $f['location'],
        $f['fac_status'],
        formatRataBulan($f['rsv_avg']),
        formatRataBulan($f['report_avg']),
    ]);
}

// bagian kedua: rekap per lokasi
fputcsv($output, []);
fputcsv($output, ['Rekap per Lokasi']);
fputcsv($output, ['Lokasi', 'Jumlah Fasilitas', 'Okupansi (Reservasi/bulan)', 'Frekuensi Kerusakan (Laporan/bulan)']);
foreach (getRekapPerLokasi($facilities) as $lok) {
    fputcsv($output, [$lok['location'], $lok['fac_count'], formatRataBulan($lok['rsv_avg']), formatRataBulan($lok['report_avg'])]);
}

fclose($output);
exit;