<?php
    class FacilityModel{
        private $db;

        // Konstruktor dan inisialisasi koneksi database
        public function __construct($dbConnection){
            $this->db = $dbConnection;
        }

        // Mengambil data fasilitas sesuai halaman
        public function getFacilities($limit, $offset){
            $query = "SELECT * FROM facilities LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ii", $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            return mysqli_fetch_all($result, MYSQLI_ASSOC);
        }

        // Menghitung total semua data fasilitas
        public function getTotalFacilities(){
            $query = "SELECT COUNT(*) as total FROM facilities";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = mysqli_fetch_assoc($result);
            return $data['total'] ?? 0;
        }
    }
?>