<?php
class Reservation {
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
    public function updateStatus($id, $status, $reason = null) {
        $query = "UPDATE " . $this->table_name . " 
                  SET rsv_status = :status, cancel_reason = :reason 
                  WHERE rsv_id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":reason", $reason);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}