<?php
require_once __DIR__ . "/../../config/db_connect.php";

function getPendingAccounts($conn)
{
    $sql = "SELECT unv_id, unv_username, unv_password, unv_email, unv_registered_at
            FROM unverified_acc
            ORDER BY unv_registered_at ASC";
    $stmt = $conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function admitAccount($conn, $unv_id, $role)
{
    $stmt = $conn->prepare("SELECT unv_username, unv_password, unv_email FROM unverified_acc WHERE unv_id = ?");
    $stmt->execute([$unv_id]);
    $acc = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$acc) {
        return false;
    }

    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$acc['unv_username'], $acc['unv_password'], $acc['unv_email'], $role]);

        $stmt = $conn->prepare("DELETE FROM unverified_acc WHERE unv_id = ?");
        $stmt->execute([$unv_id]);

        $conn->commit();
        return true;
    } catch (Throwable $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        return false;
    }
}

function createAccountDirect($conn, $username, $password, $email, $role)
{
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    try {
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $hashed, $email, $role]);
        return true;
    } catch (Throwable $e) {
        return $e->getMessage();
    }
}

function countPendingAccounts($conn)
{
    $stmt = $conn->query("SELECT COUNT(*) as total FROM unverified_acc");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int) ($row['total'] ?? 0);
}

function countUsersByRole($conn, $role)
{
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE role = ?");
    $stmt->execute([$role]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int) ($row['total'] ?? 0);
}

function rejectAccount($conn, $unv_id)
{
    $stmt = $conn->prepare("DELETE FROM unverified_acc WHERE unv_id = ?");
    return $stmt->execute([$unv_id]);
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
        } catch (Throwable $exception) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
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
        } catch (Throwable $exception) {
            return $exception->getMessage();
        }
    }

    function countPendingAccounts()
    {
        $sql = "SELECT COUNT(*) AS total FROM unverified_acc";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['total'] ?? 0);
    }

    function countUsersByRole($role)
    {
        $sql = "SELECT COUNT(*) AS total FROM users WHERE role = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$role]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['total'] ?? 0);
    }
}
