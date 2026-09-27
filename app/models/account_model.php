<?php
// Model untuk `unverified_acc` (akun pending) dan `users` (akun aktif)

class account_model {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Ambil semua akun yang masih pending untuk diverifikasi
    function getPendingAccounts()
    {
       $sql = "SELECT unv_id, unv_username, unv_password, unv_email, unv_registered_at
                  FROM unverified_acc
                  ORDER BY unv_registered_at ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Admit 1 akun pending: pindahin dari unverified_acc ke users dgn role yg dipilih admin, lalu hapus dari unverified_acc. 
     * Dan dibungkus dgn transaction biar gak ada data nyangkut kalau salah satu query gagal
     * @return bool berhasil/tidak
     */
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

    /**
     * Admin bikin akun langsung ke tabel `users` (skip proses verifikasi).
     * Password di-hash di sini karena ini input baru dari form admin.
     * @return bool|string true kalau berhasil, string pesan error kalau gagal
     */
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

    // Hitung jumlah akun yang masih pending verifikasi
    function countPendingAccounts()
    {
        $sql = "SELECT COUNT(*) AS total FROM unverified_acc";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $row['total'];
    }

    // Hitung jumlah akun dengan role tertentu (mis. 'petugas')
    function countUsersByRole($role)
    {
        $sql = "SELECT COUNT(*) AS total FROM users WHERE role = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$role]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $row['total'];
    }

    /**
     * Tolak satu akun pending: langsung hapus dari unverified_acc, ga pernah masuk ke tabel users
     * @return bool berhasil/tidak
     */
    function rejectAccount($unv_id)
    {
        $sql = "DELETE FROM unverified_acc WHERE unv_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$unv_id]);
        return $stmt->rowCount() > 0;
    }
}

