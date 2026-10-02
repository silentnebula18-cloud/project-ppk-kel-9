<?php
// Model khusus data ringkasan untuk Beranda Admin
require_once __DIR__ . "/../../config/db_connect.php";

// Hitung laporan kerusakan berdasarkan status 
function countReportsByStatus($conn, $status)
{
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM reports WHERE rep_status = ?");
    mysqli_stmt_bind_param($stmt, "s", $status);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return (int) $row['total'];
}

// Hitung reservasi berdasarkan status (mis. 'proses pengajuan')
function countReservationsByStatus($conn, $status)
{
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM reservations WHERE rsv_status = ?");
    mysqli_stmt_bind_param($stmt, "s", $status);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return (int) $row['total'];
}

/**
 * Jumlah fasilitas per status, selalu mengembalikan ketiga status
 * @return array ['Aktif' => n, 'Dalam perbaikan' => n, 'Nonaktif' => n]
 */
function getFacilityStatusCounts($conn)
{
    $counts = ['Aktif' => 0, 'Dalam perbaikan' => 0, 'Nonaktif' => 0];
    $result = mysqli_query($conn, "SELECT fac_status, COUNT(*) as total FROM facilities GROUP BY fac_status");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            if (isset($counts[$row['fac_status']])) {
                $counts[$row['fac_status']] = (int) $row['total'];
            }
        }
    }
    return $counts;
}

/**
 * Fasilitas yang paling sering direservasi (hanya yang pernah direservasi)
 * @return array tiap elemen: ['fac_name' => ..., 'total' => ...]
 */
function getTopReservedFacilities($conn, $limit = 5)
{
    $limit = (int) $limit;
    $sql = "SELECT f.fac_name, COUNT(r.rsv_id) as total
            FROM facilities f
            INNER JOIN reservations r ON r.fac_id = f.fac_id
            GROUP BY f.fac_id, f.fac_name
            ORDER BY total DESC, f.fac_name ASC
            LIMIT $limit";
    $result = mysqli_query($conn, $sql);

    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}
?>