<?php
require_once __DIR__ . '/../../config/db_connect.php';
require_once __DIR__ . "/../models/facility_model.php";

class facility_controller {
    private $db;
    private $facModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $database = new db_connect();
        $this->db = $database->getConnection();
        $this->facModel = new facility_model($this->db);
    }

    // Halaman kelola fasilitas - admin
    public function kelola_fasilitas(){
        $facilities = $this->facModel->getAllFacilities();
        require_once __DIR__ . '/../../views/admin/kelola_fasilitas.php';
    }

    // Halaman kelola fasilitas baru - admin
    public function kelola_fasilitas_baru(){
        $facility = null;
        $isEdit = isset($_GET['id']) && $_GET['id'] !== '';

        if ($isEdit) {
            $facility = $this->facModel->getFacilityById($_GET['id']);
            if (!$facility) {
                die("Fasilitas tidak ditemukan.");
            }
        }

        $types = ["ruang kelas", "aula", "laboratorium", "alat", "lapangan"];
        $statuses = ["Aktif", "Dalam Perbaikan", "Nonaktif"];
        require_once __DIR__ . '/../../views/admin/kelola_fasilitas_baru.php';
    }

    // Halaman kelola fasilitas detail - admin
    public function kelola_fasilitas_detail(){
        $id = $_GET['id'] ?? '';
        $facility = $id !== '' ? $this->facModel->getFacilityById($id) : null;

        if (!$facility) {
            die("Fasilitas tidak ditemukan.");
        }
        require_once __DIR__ . '/../../views/admin/kelola_fasilitas_detail.php';           
    }

    // Create data fasilitas baru
    public function create(){
        $name = trim($_POST['fac_name'] ?? '');
        $type = $_POST['type'] ?? '';
        $location = trim($_POST['location'] ?? '');
        $capacity = (int) ($_POST['capacity'] ?? 0);
        $desc = trim($_POST['fac_desc'] ?? '');

        if ($name === '' || $type === '' || $location === '' || $capacity <= 0 || $desc === '') {
            header("Location: index.php?page=kelola_fasilitas_baru&error=1");
            exit;
        }

        $this->facModel->createFacility($name, $type, $location, $capacity, $desc);
        header("Location: index.php?page=kelola_fasilitas");
        exit;
    }

    // Update data fasilitas yang sudah ada
    public function update(){
        $id = $_POST['fac_id'] ?? '';
        $name = trim($_POST['fac_name'] ?? '');
        $type = $_POST['type'] ?? '';
        $location = trim($_POST['location'] ?? '');
        $capacity = (int) ($_POST['capacity'] ?? 0);
        $desc = trim($_POST['fac_desc'] ?? '');
        $status = $_POST['fac_status'] ?? '';

        if ($id === '' || $name === '' || $type === '' || $location === '' || $capacity <= 0 || $desc === '' || $status === '') {
            header("Location: index.php?page=kelola_fasilitas_baru&id=" . urlencode($id) . "&error=1");
            exit;
        }

        $this->facModel->updateFacility($id, $name, $type, $location, $capacity, $desc, $status);
        header("Location: index.php?page=kelola_fasilitas_detail&id=" . urlencode($id));
        exit;
    }

    // Delete data fasilitas yang sudah ada
    public function delete(){
        $id = $_POST['fac_id'] ?? '';
        if ($id !== '') {
            $this->facModel->deleteFacility($id);
        }
        header("Location: index.php?page=kelola_fasilitas");
        exit;
    }

    // Mengaktifikan fasilitas yang awalnya non-aktif
    public function activate(){
        $id = $_POST['fac_id'] ?? '';
        if ($id === '') {
            header("Location: index.php?page=kelola_fasilitas");
            exit;
        }

        $this->facModel->setFacilityStatus($id, 'aktif');
        header("Location: index.php?page=kelola_fasilitas_detail&id=" . urlencode($id));
        exit;
    }

    // Me-non-aktifkan fasilitas yang awalnya aktif
    public function deactivate(){
        $id = $_POST['fac_id'] ?? '';
        if ($id === '') {
            header("Location: index.php?page=kelola_fasilitas");
            exit;
        }

        $this->facModel->setFacilityStatus($id, 'nonaktif');
        header("Location: index.php?page=kelola_fasilitas_detail&id=" . urlencode($id));
        exit;
    }
    
    
}