<?php
// Model untuk `unverified_acc` (akun pending) dan `users` (akun aktif)
require_once __DIR__ . "/../../config/db_connect.php";

// Ambil semua akun yang masih pending untuk diverifikasi
function getPendingAccounts($conn)
{
    $sql = "SELECT unv_id, unv_username, unv_password, unv_email, unv_registered_at
            FROM unverified_acc
            ORDER BY unv_registered_at ASC";

    $result = mysqli_query($conn, $sql);
    if (!$result) {
        die("Query gagal: " . mysqli_error($conn));
    }

    $pending = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $pending[] = $row;
    }
    return $pending;
}

/**
 * Admit 1 akun pending: pindahin dari unverified_acc ke users dgn role yg dipilih admin, lalu hapus dari unverified_acc. 
 * Dan dibungkus dgn transaction biar gak ada data nyangkut kalau salah satu query gagal
 * @return bool berhasil/tidak
 */
function admitAccount($conn, $unv_id, $role)
{
    $stmt = mysqli_prepare($conn, "SELECT unv_username, unv_password, unv_email FROM unverified_acc WHERE unv_id = ?");
    mysqli_stmt_bind_param($stmt, "s", $unv_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $acc = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$acc) {
        return false;
    }

    mysqli_begin_transaction($conn);
    try {
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "ssss", $acc['unv_username'], $acc['unv_password'], $acc['unv_email'], $role);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($conn, "DELETE FROM unverified_acc WHERE unv_id = ?");
        mysqli_stmt_bind_param($stmt, "s", $unv_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        mysqli_commit($conn);
        return true;
    } catch (mysqli_sql_exception $e) {
        mysqli_rollback($conn);
        return false;
    }
}

/**
 * Admin bikin akun langsung ke tabel `users` (skip proses verifikasi).
 * Password di-hash di sini karena ini input baru dari form admin.
 * @return bool|string true kalau berhasil, string pesan error kalau gagal
 */
function createAccountDirect($conn, $username, $password, $email, $role)
{
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "ssss", $username, $hashed, $email, $role);
    $ok = mysqli_stmt_execute($stmt);
    $error = mysqli_error($conn);
    mysqli_stmt_close($stmt);

    return $ok ? true : $error;
}

// Hitung jumlah akun yang masih pending verifikasi
function countPendingAccounts($conn)
{
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM unverified_acc");
    $row = mysqli_fetch_assoc($result);
    return (int) $row['total'];
}

// Hitung jumlah akun dengan role tertentu (mis. 'petugas')
function countUsersByRole($conn, $role)
{
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM users WHERE role = ?");
    mysqli_stmt_bind_param($stmt, "s", $role);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return (int) $row['total'];
}

/**
 * Tolak satu akun pending: langsung hapus dari unverified_acc, ga pernah masuk ke tabel users
 * @return bool berhasil/tidak
 */
function rejectAccount($conn, $unv_id)
{
    $stmt = mysqli_prepare($conn, "DELETE FROM unverified_acc WHERE unv_id = ?");
    mysqli_stmt_bind_param($stmt, "s", $unv_id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $ok;
}