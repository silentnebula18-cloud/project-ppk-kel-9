<?php
class FacilityModel {
    private $db;

    public function __construct($dbConnection){
        $this->db = $dbConnection;
    }

    public function getFacilities($limit, $offset){
        $query = "SELECT fac_id, fac_name, type, location, capacity, fac_status FROM facilities LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(1, (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalFacilities(){
        $query = "SELECT COUNT(*) as total FROM facilities";
        $stmt = $this->db->query($query);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data['total'] ?? 0;
    }

    public function getFacOnId($id){
        $query = "SELECT fac_name, type, location, capacity, fac_desc, fac_status FROM facilities WHERE fac_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}