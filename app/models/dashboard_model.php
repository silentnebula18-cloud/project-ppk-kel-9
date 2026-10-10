<?php
require_once __DIR__ . "/../../config/db_connect.php";

function countReportsByStatus($conn, $status)
{
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM reports WHERE rep_status = ?");
    $stmt->execute([$status]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int) ($row['total'] ?? 0);
}

function countReservationsByStatus($conn, $status)
{
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM reservations WHERE rsv_status = ?");
    $stmt->execute([$status]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int) ($row['total'] ?? 0);
}

function getFacilityStatusCounts($conn)
{
    $counts = ['Aktif' => 0, 'Dalam perbaikan' => 0, 'Nonaktif' => 0];
    $stmt = $conn->query("SELECT fac_status, COUNT(*) as total FROM facilities GROUP BY fac_status");
    if ($stmt) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
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
    $stmt = $conn->query($sql);
    $rows = [];
    if ($stmt) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function getFacilitiesByBuilding($conn)
{
    $stmt = $conn->query("SELECT fac_name, type, location, capacity, fac_status
                           FROM facilities
                           ORDER BY location ASC, fac_name ASC");
    $groups = [];
    if ($stmt) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
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