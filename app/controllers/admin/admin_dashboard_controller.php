<?php
require_once __DIR__ . '/../../../config/db_connect.php';
require_once __DIR__ . "/../../../app/models/account_model.php";
require_once __DIR__ . "/../../../app/models/facility_model.php";

class admin_dashboard_controller {
    private $db;
    private $accModel;
    private $facModel;

    public function __construct() {
        $database = new db_connect();
        $this->db = $database->getConnection();
        $this->accModel = new account_model($this->db);
        $this->facModel = new facility_model($this->db);
    }

    public function index() {
        $pendingCount = $this->accModel->countPendingAccounts();
        $petugasCount = $this->accModel->countUsersByRole('petugas');
        $fasilitasCount = $this->facModel->countFacilitiesByStatus('Aktif');

        //Render file view dari folder views
        require_once __DIR__ . '/../../../views/admin/admin_beranda.php';
    }
}