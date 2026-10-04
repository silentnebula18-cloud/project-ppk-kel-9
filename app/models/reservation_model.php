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

    // Mengambil semua reservasi yang diajukan oleh pengguna tertentu
    public function getByUser($user_id) {
        $sql = "SELECT 
                    r.*,
                FROM " . $this->table_name . " r
                JOIN facilities f ON r.fac_id = f.fac_id
                WHERE r.user_id = ?
                ORDER BY r.rsv_date DESC, r.start_time DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Mengambil detail reservasi berstatus 'proses pengajuan' atau 'disetujui' untuk pengguna tertentu
    public function getByUser_status_1($user_id, $status) {
        $sql = "SELECT 
                    r.rsv_id,
                    r.rsv_date,
                    r.start_time,
                    r.end_time,
                    r.purpose,
                    r.rsv_status,
                    r.created_at,
                    f.*,
                FROM " . $this->table_name . " r
                JOIN facilities f ON r.fac_id = f.fac_id
                WHERE r.user_id = ?
                AND r.rsv_status = ?
                ORDER BY r.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id, $status]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Mengambil detail reservasi berstatus 'ditolak' untuk pengguna tertentu
    public function getByUser_status_2($user_id) {
        $sql = "SELECT 
                    r.rsv_id,
                    r.rsv_date,
                    r.start_time,
                    r.end_time,
                    r.purpose,
                    r.rsv_status,
                    r.created_at,
                    r.canceled_at,
                    r.cancel_reason,
                    f.*,
                FROM " . $this->table_name . " r
                JOIN facilities f ON r.fac_id = f.fac_id 
                WHERE r.user_id = ?
                AND r.rsv_status = ?
                AND (SELECT role FROM users WHERE user_id = r.cancelled_by) = 'petugas'
                ORDER BY r.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id, 'ditolak']);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}