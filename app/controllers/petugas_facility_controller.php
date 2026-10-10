<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Facility.php';
require_once __DIR__ . '/../helpers/validate_session.php';

class PetugasFacilityController {
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
}

class_alias('PetugasFacilityController', 'FacilityController');
