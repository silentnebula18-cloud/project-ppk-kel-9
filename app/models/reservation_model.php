<?php
class reservation_model {
    private $conn;
    private $table_name = "reservations";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Ambil detail 1 reservasi berdasarkan rsv_id
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE rsv_id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Cek Bentrok Jadwal (Anti-Overlap SRS 9)
    public function checkOverlap($fac_id, $rsv_date, $start_time, $end_time, $exclude_id) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
                  WHERE fac_id = :fac_id 
                    AND rsv_date = :rsv_date 
                    AND rsv_status = 'disetujui' 
                    AND rsv_id != :exclude_id 
                    AND (start_time < :end_time AND end_time > :start_time)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":fac_id", $fac_id);
        $stmt->bindParam(":rsv_date", $rsv_date);
        $stmt->bindParam(":start_time", $start_time);
        $stmt->bindParam(":end_time", $end_time);
        $stmt->bindParam(":exclude_id", $exclude_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] > 0; // Return true jika BENTROK
    }

    // Update Status & Simpan Alasan Penolakan ke cancel_reason
    public function updateStatus($id, $status, $reason = null, $cancelledBy = null) {
        $query = "UPDATE " . $this->table_name . " 
                SET rsv_status = :status, 
                    cancel_reason = :reason,
                    cancelled_by = :cancelled_by
                WHERE rsv_id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":reason", $reason);
        $stmt->bindParam(":cancelled_by", $cancelledBy);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    // Mengambil semua reservasi yang disetujui untuk fasilitas tertentu pada rentang tanggal tertentu
    public function getApprovedByFacilityAndDateRange($fac_id, $start_date, $end_date) {
        $query = "SELECT *
                FROM " . $this->table_name . "
                WHERE fac_id = :fac_id
                    AND rsv_date BETWEEN :start_date AND :end_date
                    AND rsv_status = 'disetujui'
                ORDER BY rsv_date, start_time";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":fac_id", $fac_id);
        $stmt->bindParam(":start_date", $start_date);
        $stmt->bindParam(":end_date", $end_date);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Menambahkan reservasi baru ke database
    public function create($user_id, $fac_id, $rsv_date, $start_time, $end_time, $purpose) {

        $sql = "INSERT INTO reservations
                (user_id, fac_id, rsv_date, start_time, end_time, purpose)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $user_id,
            $fac_id,
            $rsv_date,
            $start_time,
            $end_time,
            $purpose
        ]);
    }

    // Memeriksa apakah reservasi yang akan diajukan bertabrakan dengan reservasi yang sudah disetujui
    // untuk fasilitas yang sama pada tanggal yang sama
    public function checkOverlapNew($fac_id, $rsv_date, $start_time, $end_time) {
        $sql = "SELECT COUNT(*) FROM reservations
                WHERE fac_id = ?
                AND rsv_date = ? AND rsv_status = 'disetujui'
                AND start_time < ? AND end_time > ?";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([
            $fac_id,
            $rsv_date,
            $end_time,
            $start_time
        ]);

        return $stmt->fetchColumn() > 0;
    }

    // Mengambil reservasi pengguna dengan status 'proses pengajuan'
    public function getByUser_ProsesPengajuan($user_id) {
        $sql = "SELECT
                    r.rsv_id,
                    r.fac_id,
                    f.fac_name,
                    f.location,
                    r.rsv_date,
                    r.start_time,
                    r.end_time,
                    r.rsv_status
                FROM " . $this->table_name . " r
                JOIN facilities f ON r.fac_id = f.fac_id
                WHERE r.user_id = ?
                AND r.rsv_status = 'proses pengajuan'
                ORDER BY r.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Mengambil reservasi pengguna dengan status 'disetujui'
    public function getByUser_Disetujui($user_id) {
        $sql = "SELECT
                    r.rsv_id,
                    r.fac_id,
                    f.fac_name,
                    f.location,
                    r.rsv_date,
                    r.start_time,
                    r.end_time,
                    r.rsv_status
                FROM " . $this->table_name . " r
                JOIN facilities f ON r.fac_id = f.fac_id
                WHERE r.user_id = ?
                AND r.rsv_status = 'disetujui'
                ORDER BY r.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Mengambil reservasi pengguna dengan status 'kadaluarsa'
    public function getByUser_Kadaluarsa($user_id) {
        $sql = "SELECT
                    r.rsv_id,
                    r.fac_id,
                    f.fac_name,
                    f.location,
                    r.rsv_date,
                    r.start_time,
                    r.end_time,
                    r.rsv_status
                FROM " . $this->table_name . " r
                JOIN facilities f ON r.fac_id = f.fac_id
                WHERE r.user_id = ?
                AND r.rsv_status = 'kadaluarsa'
                ORDER BY r.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Mengambil reservasi pengguna dengan status 'dibatalkan'
    public function getByUser_Dibatalkan($user_id) {
        $sql = "SELECT
                    r.rsv_id,
                    r.fac_id,
                    f.fac_name,
                    f.location,
                    r.rsv_date,
                    r.start_time,
                    r.end_time,
                    r.rsv_status
                FROM " . $this->table_name . " r
                JOIN facilities f ON r.fac_id = f.fac_id
                WHERE r.user_id = ?
                AND r.rsv_status = 'dibatalkan'
                ORDER BY r.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Mengambil reservasi pengguna dengan status 'ditolak'
    public function getByUser_Ditolak($user_id) {
        $sql = "SELECT
                    r.rsv_id,
                    r.fac_id,
                    f.fac_name,
                    f.location,
                    r.rsv_date,
                    r.start_time,
                    r.end_time,
                    r.rsv_status
                FROM " . $this->table_name . " r
                JOIN facilities f ON r.fac_id = f.fac_id
                WHERE r.user_id = ?
                AND r.rsv_status = 'ditolak'
                ORDER BY r.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Mengambil reservasi pengguna dengan status 'disetujui'
    // yang slot waktunya sudah selesai, sehingga masuk Riwayat
    public function getByUser_RiwayatDisetujui($user_id) {
        $sql = "SELECT
                    r.rsv_id,
                    r.fac_id,
                    f.fac_name,
                    f.location,
                    r.rsv_date,
                    r.start_time,
                    r.end_time,
                    r.rsv_status
                FROM " . $this->table_name . " r
                JOIN facilities f ON r.fac_id = f.fac_id
                WHERE r.user_id = ?
                AND r.rsv_status = 'disetujui'
                AND TIMESTAMP(r.rsv_date, r.end_time) <= NOW()
                ORDER BY r.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Detail reservasi: proses pengajuan
    public function getDetailReservasi_ProsesPengajuan($rsv_id, $user_id) {
        $sql = "SELECT
                    rsv_id,
                    user_id,
                    rsv_date,
                    start_time,
                    end_time,
                    rsv_status,
                    purpose,
                    created_at
                FROM " . $this->table_name . "
                WHERE rsv_id = ?
                AND user_id = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$rsv_id, $user_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // Detail reservasi: disetujui
    public function getDetailReservasi_Disetujui($rsv_id, $user_id) {
        $sql = "SELECT
                    rsv_id,
                    user_id,
                    rsv_date,
                    start_time,
                    end_time,
                    rsv_status,
                    purpose,
                    created_at
                FROM " . $this->table_name . "
                WHERE rsv_id = ?
                AND user_id = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$rsv_id, $user_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // Detail reservasi: kadaluarsa
    public function getDetailReservasi_Kadaluarsa($rsv_id, $user_id) {
        $sql = "SELECT
                    rsv_id,
                    user_id,
                    rsv_date,
                    start_time,
                    end_time,
                    rsv_status,
                    purpose,
                    created_at
                FROM " . $this->table_name . "
                WHERE rsv_id = ?
                AND user_id = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$rsv_id, $user_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // Detail reservasi: dibatalkan
    public function getDetailReservasi_Dibatalkan($rsv_id, $user_id) {
        $sql = "SELECT
                    rsv_id,
                    user_id,
                    rsv_date,
                    start_time,
                    end_time,
                    rsv_status,
                    purpose,
                    created_at,
                    cancelled_at,
                    cancelled_by
                FROM " . $this->table_name . "
                WHERE rsv_id = ?
                AND user_id = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$rsv_id, $user_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // Detail reservasi: ditolak
    public function getDetailReservasi_Ditolak($rsv_id, $user_id) {
        $sql = "SELECT
                    rsv_id,
                    user_id,
                    rsv_date,
                    start_time,
                    end_time,
                    rsv_status,
                    purpose,
                    created_at,
                    cancelled_at,
                    cancelled_by,
                    cancel_reason
                FROM " . $this->table_name . "
                WHERE rsv_id = ?
                AND user_id = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$rsv_id, $user_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Ambil reservasi milik user untuk validasi pembatalan
    public function getRsvForCancel($rsv_id, $user_id) {
        $sql = "SELECT
                    rsv_id,
                    rsv_date,
                    start_time,
                    rsv_status
                FROM " . $this->table_name . "
                WHERE rsv_id = ?
                AND user_id = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$rsv_id, $user_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Batalkan reservasi
    public function batalkanReservasi($rsv_id, $user_id) {
        $sql = "UPDATE " . $this->table_name . "
                SET
                    rsv_status = 'dibatalkan',
                    cancelled_at = NOW(),
                    cancelled_by = ?
                WHERE rsv_id = ?
                AND user_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $user_id,
            $rsv_id,
            $user_id
        ]);

        return $stmt->rowCount() > 0;
    }
}