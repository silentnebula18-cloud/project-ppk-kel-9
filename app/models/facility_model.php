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
                f.fac_status,
                (SELECT COUNT(*) FROM reservations r WHERE r.fac_id = f.fac_id) as rsv_count,
                (SELECT COUNT(*) FROM reports rp WHERE rp.fac_id = f.fac_id) as report_count
            FROM facilities f
            ORDER BY f.fac_name ASC";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("Query gagal: " . mysqli_error($conn));
    }

    $facilities = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $facilities[] = $row;
    }
    return $facilities;
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
function createFacility($conn, $name, $type, $location, $capacity, $desc)
{
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO facilities (fac_name, type, location, capacity, fac_desc, fac_status)
         VALUES (?, ?, ?, ?, ?, 'Aktif')"
    );
    mysqli_stmt_bind_param($stmt, "sssis", $name, $type, $location, $capacity, $desc);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $ok;
}

/**
 * Update data fasilitas yang sudah ada
 * @return bool berhasil/tidak
 */
function updateFacility($conn, $id, $name, $type, $location, $capacity, $desc, $status)
{
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE facilities
         SET fac_name = ?, type = ?, location = ?, capacity = ?, fac_desc = ?, fac_status = ?
         WHERE fac_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "sssisss", $name, $type, $location, $capacity, $desc, $status, $id);
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
    $stmt = mysqli_prepare($conn, "DELETE FROM facilities WHERE fac_id = ?");
    mysqli_stmt_bind_param($stmt, "s", $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $ok;
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