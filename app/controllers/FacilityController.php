<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Facility.php';
require_once __DIR__ . '/../../config/db_connect.php';
require_once __DIR__ . '/../models/FacilityModel.php';
require_once __DIR__ . '/../helpers/validate_session.php';

class FacilityController {
    private $db;
    private $facilityModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $database = new Database();
        $this->db = $database->getConnection();
        $this->facilityModel = new Facility($this->db);
    }

    public function index() {
        check_session_valid('petugas');
        $facilities = $this->facilityModel->getAll();
        require_once __DIR__ . '/../../views/petugas/facilities.php';
    }

    public function updateStatus() {
        check_session_valid('petugas');
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fac_id'], $_POST['fac_status'])) {
            $fac_id = $_POST['fac_id'];
            $status = $_POST['fac_status'];

            if ($this->facilityModel->updateStatus($fac_id, $status)) {
                $_SESSION['flash_success'] = "Status fasilitas berhasil diperbarui!";
            } else {
                $_SESSION['flash_error'] = "Gagal memperbarui status fasilitas (Fasilitas nonaktif tidak dapat diubah).";
            }

            header("Location: index.php?page=facilities");
            exit;
        }
    }

    public function fasilitas() {
        global $conn;
        $model = new FacilityModel($conn);

        $limit = 5;
        $page = isset($_GET['haltab']) ? (int)$_GET['haltab'] : 1;
        $offset = ($page - 1) * $limit;

        $facilities = $model->getFacilities($limit, $offset);
        $totalData = $model->getTotalFacilities();
        $totalPages = ceil($totalData / $limit);

        require_once __DIR__ . '/../../views/pengguna/katalog_fasilitas.php';
    }

    public function getFacilityDetail() {
        global $conn;
        $model = new FacilityModel($conn);

        $id = $_GET['fac_id'] ?? '';
        $facility = $model->getFacOnId($id);

        echo json_encode($facility);
    }
}
