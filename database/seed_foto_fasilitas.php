<?php
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../app/helpers/upload_helper.php';

header('Content-Type: text/plain; charset=utf-8');

$peta = [
    'Aula Mini' => 'Contoh-Aula-Mini.jpeg',
    'Aula Utama' => 'Contoh-Aula-Utama.webp',
    'Lapangan Basket' => 'Contoh-Lapangan-Basket.jpg',
    'Ruang Kelas 101' => 'Contoh-Classroom-1.jpeg',
    'Ruang Kelas 102' => 'Contoh-Classroom-2.jpeg',
    'Ruang Kelas 201' => 'Contoh-Classroom-3.jpg',
    'Lab Komputer' => 'Contoh-Lab-Komputer.png',
    'Lab Kimia' => 'Contoh-Lab-Kimia.webp',
    'Lapangan Futsal' => 'Contoh-Lapangan-Futsal.webp',
    'Proyektor Portabel' => 'Contoh-Proyektor-Portabel.png',
];

$cek = mysqli_query($conn, "SHOW COLUMNS FROM facilities LIKE 'fac_photo'");
if (!$cek || mysqli_num_rows($cek) === 0) {
    exit("GAGAL: tabel facilities belum punya kolom fac_photo.\nJalankan dulu di phpMyAdmin:\nALTER TABLE facilities ADD COLUMN fac_photo VARCHAR(300) NULL AFTER fac_desc;\n");
}

$tujuanFolder = __DIR__ . '/../storage/fac_img';
if (!is_dir($tujuanFolder) && !mkdir($tujuanFolder, 0775, true)) {
    exit("GAGAL: folder $tujuanFolder tidak bisa dibuat.\n");
}

$ok = 0;
foreach ($peta as $nama => $berkas) {
    $sumber = __DIR__ . '/../storage/fac_img/' . $berkas;

    $stmt = mysqli_prepare($conn, "SELECT fac_id FROM facilities WHERE fac_name = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $nama);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$row) {
        echo "LEWATI  $nama: tidak ada fasilitas dengan nama itu di database\n";
        continue;
    }
    if (!is_file($sumber)) {
        echo "LEWATI  $nama: file $berkas tidak ada di storage/fac_img/\n";
        continue;
    }

    $hasil = konversiKeWebp($sumber, $tujuanFolder . '/' . $row['fac_id'] . '.webp');
    if ($hasil === true) {
        $path = 'storage/fac_img/' . $row['fac_id'] . '.webp';
        $upd  = mysqli_prepare($conn, "UPDATE facilities SET fac_photo = ? WHERE fac_id = ?");
        mysqli_stmt_bind_param($upd, "ss", $path, $row['fac_id']);
        mysqli_stmt_execute($upd);
        mysqli_stmt_close($upd);
        echo "OK $nama -> $path\n";
        $ok++;
    } else {
        echo "GAGAL $nama: $hasil\n";
    }
}

echo "\nSelesai: $ok dari " . count($peta) . " foto terpasang.\n";