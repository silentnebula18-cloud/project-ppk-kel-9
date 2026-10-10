<?php
require_once __DIR__ . "/../../config/db_connect.php";

/**
 * Ambil semua fasilitas, lengkap dengan hitungan frekuensi reservasi & frekuensi laporan kerusakan
 * @param mysqli $conn
 * @return array daftar fasilitas (tiap elemen = array asosiatif satu baris)
 */
function getAllFacilities($conn)
{
    $sql = "SELECT
                f.fac_id,
                f.fac_name,
                f.type,
                f.location,
                f.capacity,
                f.fac_desc,
                f.fac_photo,
                f.fac_status,
                (SELECT COUNT(*) FROM reservations r WHERE r.fac_id = f.fac_id) as rsv_total,
                (SELECT COUNT(*) FROM reports rp WHERE rp.fac_id = f.fac_id) as report_total
            FROM facilities f
            ORDER BY f.fac_name ASC";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("Query gagal: " . mysqli_error($conn));
    }

    // Rata-rata per bulan = total / jumlah bulan sejak data pertama tercatat
    $bulan = getJumlahBulanData($conn);

    $facilities = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['periode_bulan'] = $bulan;
        $row['rsv_avg'] = round($row['rsv_total'] / $bulan, 1);
        $row['report_avg'] = round($row['report_total'] / $bulan, 1);
        $facilities[] = $row;
    }
    return $facilities;
}

/**
 * Hitung berapa bulan data sudah tercatat: dari reservasi/laporan paling awal sampai hari ini. Bulan yg berjalan ikut dihitung dan minimalnya 1, jadi pembagi tidak pernah 0.
 * @return int
 */
function getJumlahBulanData($conn)
{
    $sql = "SELECT GREATEST(1, TIMESTAMPDIFF(MONTH,
                LEAST(
                    COALESCE((SELECT MIN(rsv_date) FROM reservations), CURDATE()),
                    COALESCE((SELECT MIN(DATE(reported_at)) FROM reports), CURDATE())
                ),
                CURDATE()) + 1) AS bulan";
    $result = mysqli_query($conn, $sql);
    $row = $result ? mysqli_fetch_assoc($result) : null;

    return max(1, (int) ($row['bulan'] ?? 1));
}

/**
 * Format angka rata-rata untuk ditampilkan, contoh 2.4 -> "2,4"
 */
function formatRataBulan($nilai)
{
    return number_format((float) $nilai, 1, ',', '.');
}

/**
 * Ambil satu fasilitas berdasarkan fac_id
 * @param mysqli $conn
 * @param string $id
 * @return array|null null kalau gak ketemu
 */
function getFacilityById($conn, $id)
{
    $stmt = mysqli_prepare($conn, "SELECT * FROM facilities WHERE fac_id = ?");
    mysqli_stmt_bind_param($stmt, "s", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $facility = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    return $facility ?: null;
}

/**
 * Insert fasilitas baru. Status baru selalu "Aktif"
 * @return bool berhasil/tidak
 */
function createFacility($conn, $id, $name, $type, $location, $capacity, $desc, $photo = null)
{
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO facilities (fac_id, fac_name, type, location, capacity, fac_desc, fac_photo, fac_status)
         VALUES (?, ?, ?, ?, ?, ?, ?, 'Aktif')"
    );
    mysqli_stmt_bind_param($stmt, "ssssiss", $id, $name, $type, $location, $capacity, $desc, $photo);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $ok;
}

/**
 * Update data fasilitas yang sudah ada
 * @return bool berhasil/tidak
 */
function updateFacility($conn, $id, $name, $type, $location, $capacity, $desc, $status, $photo = null)
{
    // fac_photo = COALESCE(?, fac_photo): kalau tidak ada foto baru ($photo null), path foto lama dipertahankan
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE facilities
         SET fac_name = ?, type = ?, location = ?, capacity = ?, fac_desc = ?, fac_status = ?,
             fac_photo = COALESCE(?, fac_photo)
         WHERE fac_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "sssissss", $name, $type, $location, $capacity, $desc, $status, $photo, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $ok;
}

/**
 * Hapus fasilitas berdasarkan fac_id
 * @return bool berhasil/tidak
 */
function deleteFacility($conn, $id)
{
    try {
        $stmt = mysqli_prepare($conn, "DELETE FROM facilities WHERE fac_id = ?");
        mysqli_stmt_bind_param($stmt, "s", $id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    } catch (mysqli_sql_exception $e) {
        // error code 1451 = foreign key constraint fails (masih ada reservasi/laporan)
        if ($e->getCode() === 1451) {
            return 'has_history';
        }
        throw $e;
    }
}

/**
 * Ganti status fasilitas (dipakai buat nonaktifkan/aktifkan kembali)
 * @return bool berhasil/tidak
 */
function setFacilityStatus($conn, $id, $status)
{
    $stmt = mysqli_prepare($conn, "UPDATE facilities SET fac_status = ? WHERE fac_id = ?");
    mysqli_stmt_bind_param($stmt, "ss", $status, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $ok;
}

/**
 * Hitung jumlah fasilitas berdasarkan status tertentu (mis. 'Aktif')
 * @return int
 */
function countFacilitiesByStatus($conn, $status)
{
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM facilities WHERE fac_status = ?");
    mysqli_stmt_bind_param($stmt, "s", $status);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    return (int) $row['total'];
}

/**
 * Rekap per lokasi: jumlah fasilitas, serta rata-rata reservasi dan laporan kerusakan per bulan
 * @param array $facilities hasil getAllFacilities()
 * @return array satu elemen per lokasi, urut abjad
 */
function getRekapPerLokasi($facilities)
{
    $rekap = [];
    foreach ($facilities as $f) {
        $lok = $f['location'];
        if (!isset($rekap[$lok])) {
            $rekap[$lok] = ['location' => $lok, 'fac_count' => 0, 'rsv_total' => 0, 'report_total' => 0, 'periode' => (int) $f['periode_bulan']];
        }
        $rekap[$lok]['fac_count']++;
        $rekap[$lok]['rsv_total']    += (int) $f['rsv_total'];
        $rekap[$lok]['report_total'] += (int) $f['report_total'];
    }
    ksort($rekap);
    foreach ($rekap as &$r) {
        $r['rsv_avg']    = round($r['rsv_total'] / $r['periode'], 1);
        $r['report_avg'] = round($r['report_total'] / $r['periode'], 1);
    }
    unset($r);
    return array_values($rekap);
}

/**
 * UUID v4 dibuat di PHP supaya fac_id sudah diketahui sebelum insert, jadi foto bisa langsung diberi nama <fac_id>.webp
 */
function buatUuid()
{
    $b = random_bytes(16);
    $b[6] = chr((ord($b[6]) & 0x0f) | 0x40);
    $b[8] = chr((ord($b[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($b), 4));
}

/**
 * Kolom fac_photo menyimpan path foto relatif dari root project, contoh: storage/fac_img/<fac_id>.webp (NULL = belum ada foto).
 */
function adaFoto($path)
{
    if (empty($path) || strpos($path, '..') !== false) {
        return false;
    }
    return is_file(__DIR__ . '/../../' . ltrim($path, '/'));
}

/**
 * URL foto untuk dipakai di <img> dari folder views/admin/
 * ?v=... supaya browser memuat ulang setelah foto diganti.
 */
function urlFoto($path)
{
    $path = ltrim($path, '/');
    $encoded = implode('/', array_map('rawurlencode', explode('/', $path)));
    return '../../' . $encoded . '?v=' . filemtime(__DIR__ . '/../../' . $path);
}