<?php
class Report {
    private $conn;
    private $table_name = "reports";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Ambil semua laporan beserta data user & fasilitas
    public function getAll() {
        $query = "SELECT rp.*, u.username, f.fac_name 
                  FROM " . $this->table_name . " rp
                  LEFT JOIN users u ON rp.user_id = u.user_id
                  LEFT JOIN facilities f ON rp.fac_id = f.fac_id
                  ORDER BY rp.reported_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update status, fasilitas, dan resolusi
    public function updateReport($id, $status, $fac_id, $resolution) {
        $query = "UPDATE " . $this->table_name . " 
                  SET rep_status = :status, 
                      fac_id = :fac_id, 
                      resolution = :resolution 
                  WHERE rep_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":fac_id", $fac_id);
        $stmt->bindParam(":resolution", $resolution);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}