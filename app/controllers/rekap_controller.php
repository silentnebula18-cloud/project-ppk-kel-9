<?php
require_once __DIR__ . "/../models/facility_model.php";

$facilities = getAllFacilities($conn);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=rekap_fasilitas_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

// baris pertama = nama kolom
fputcsv($output, ['Nama Fasilitas', 'Tipe', 'Lokasi', 'Status', 'Frekuensi Reservasi', 'Frekuensi Kerusakan']);

foreach ($facilities as $f) {
    fputcsv($output, [
        $f['fac_name'],
        $f['type'],
        $f['location'],
        $f['fac_status'],
        $f['rsv_count'],
        $f['report_count'],
    ]);
}

fclose($output);
exit;