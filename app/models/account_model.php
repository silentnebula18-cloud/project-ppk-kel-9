<?php

require_once __DIR__ . "/../../config/db_connect.php";

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

function countPendingAccounts($conn)
{
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM unverified_acc");
    $row = mysqli_fetch_assoc($result);
    return (int) $row['total'];
}

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

function rejectAccount($conn, $unv_id)
{
    $stmt = mysqli_prepare($conn, "DELETE FROM unverified_acc WHERE unv_id = ?");
    mysqli_stmt_bind_param($stmt, "s", $unv_id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $ok;
}

class account_model {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    function getPendingAccounts()
    {
       $sql = "SELECT unv_id, unv_username, unv_password, unv_email, unv_registered_at
                  FROM unverified_acc
                  ORDER BY unv_registered_at ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function admitAccount($unv_id, $role)
    {
        $sql = "SELECT unv_username, unv_password, unv_email FROM unverified_acc WHERE unv_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$unv_id]);

        $acc = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$acc) {
            return false;
        }

        try {
            $this->conn->beginTransaction();

            $sql = "INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $acc['unv_username'],
                $acc['unv_password'],
                $acc['unv_email'],
                $role
            ]);

            $sql = "DELETE FROM unverified_acc WHERE unv_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$unv_id]);

            $this->conn->commit();
            return true;
        } catch (PDOException $exception) {
            $this->conn->rollBack();
            return false;
        }
    }

    function rejectAccount($unv_id)
    {
        $sql = "DELETE FROM unverified_acc WHERE unv_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$unv_id]);
    }

    function createAccountDirect($username, $password, $email, $role)
    {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$username, $hashed, $email, $role]);
            return true;
        } catch (PDOException $exception) {
            return $exception->getMessage();
        }
    }

    function countPendingAccounts()
    {
        $sql = "SELECT COUNT(*) AS total FROM unverified_acc";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $row['total'];
    }

    function countUsersByRole($role)
    {
        $sql = "SELECT COUNT(*) AS total FROM users WHERE role = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$role]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $row['total'];
    }
}
