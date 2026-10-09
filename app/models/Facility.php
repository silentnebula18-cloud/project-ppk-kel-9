<?php
class Facility {
    private $conn;
    private $table_name = "facilities";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Ambil semua fasilitas
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY fac_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update status fasilitas (Hanya Boleh 'Aktif' atau 'Dalam perbaikan')
    public function updateStatus($fac_id, $status) {
        $allowed = ['Aktif', 'Dalam perbaikan'];
        if (!in_array($status, $allowed)) {
            return false;
        }

        $query = "UPDATE " . $this->table_name . " SET fac_status = :status WHERE fac_id = :id AND fac_status != 'Nonaktif'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $fac_id);
        return $stmt->execute();
    }
}