<?php
require_once __DIR__ . "/../../config/db_connect.php";

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

function getFacilityStatusCounts($conn)
{
    $counts = ['Aktif' => 0, 'Dalam perbaikan' => 0, 'Nonaktif' => 0];
    $result = mysqli_query($conn, "SELECT fac_status, COUNT(*) as total FROM facilities GROUP BY fac_status");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $key = match (strtolower($row['fac_status'])) {
                'aktif' => 'Aktif',
                'dalam perbaikan' => 'Dalam perbaikan',
                'nonaktif' => 'Nonaktif',
                default => null,
            };
            if ($key !== null) {
                $counts[$key] += (int) $row['total'];
            }
        }
    }
    return $counts;
}

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

function getFacilitiesByBuilding($conn)
{
    $result = mysqli_query($conn, "SELECT fac_name, type, location, capacity, fac_status
                                   FROM facilities
                                   ORDER BY location ASC, fac_name ASC");
    $groups = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $building = trim(preg_replace('/\s+Lantai\s+\d+\s*$/i', '', $row['location']));
            if ($building === '') {
                $building = 'Lainnya';
            }
            $groups[$building][] = $row;
        }
    }
    ksort($groups);
    return $groups;
}
?>