<?php
class Reservation {
    private $conn;
    private $table_name = "reservations";

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. Ambil reservasi berdasarkan status (Digunakan oleh Dashboard)
    public function getByStatus($status) {
        $query = "SELECT r.*, u.username, u.email, f.fac_name, f.fac_status 
                  FROM " . $this->table_name . " r
                  JOIN users u ON r.user_id = u.user_id
                  JOIN facilities f ON r.fac_id = f.fac_id
                  WHERE r.rsv_status = :status
                  ORDER BY r.rsv_date ASC, r.start_time ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. Ambil semua reservasi
    public function getAllReservations() {
        $query = "SELECT r.*, u.username, u.email, f.fac_name, f.fac_status 
                  FROM " . $this->table_name . " r
                  JOIN users u ON r.user_id = u.user_id
                  JOIN facilities f ON r.fac_id = f.fac_id
                  ORDER BY r.rsv_date DESC, r.start_time ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. Ambil detail 1 reservasi berdasarkan rsv_id
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE rsv_id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 4. Cek Bentrok Jadwal (Anti-Overlap SRS 9)
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
        return $row['total'] > 0;
    }

    // 5. Update Status & Simpan Alasan Penolakan/Pembatalan
    public function updateStatus($id, $status, $reason = null, $cancelledBy = null) {
        $query = "UPDATE " . $this->table_name . " 
                  SET rsv_status = :status, 
                      cancel_reason = :reason,
                      cancelled_by = :cancelled_by
                  WHERE rsv_id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        
        if ($reason === null) {
            $stmt->bindValue(":reason", null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(":reason", $reason, PDO::PARAM_STR);
        }

        if ($cancelledBy === null) {
            $stmt->bindValue(":cancelled_by", null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(":cancelled_by", $cancelledBy, PDO::PARAM_STR);
        }

        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}