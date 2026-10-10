<?php
require_once __DIR__ . '/../../../config/db_connect.php';
require_once __DIR__ . "/../../models/facility_model.php";
require_once __DIR__ . "/../../models/reservation_model.php";
require_once __DIR__ . '/../../helpers/validate_session.php';

class pengguna_beranda_controller {
    private $db;
    private $facModel;
    private $rsvModel;

    public function __construct() {
        check_session_valid('pengguna');
        $database = new db_connect();
        $this->db = $database->getConnection();
        
        $this->facModel = new facility_model($this->db);
        $this->rsvModel = new reservation_model($this->db);
    }

    public function index() {
        check_session_valid('pengguna');

        // Pengaturan pagination
        $limit = 5;
        $page = max(1, (int) ($_GET['haltab'] ?? 1));

        $tipe_fac = $_GET['tipe_fac'] ?? '';
        $lokasi_fac = $_GET['lokasi_fac'] ?? '';
        $kapasitas_fac = $_GET['kapasitas_fac'] ?? '';

        // Hitung total data dan halaman
        if ($tipe_fac !== '' || $lokasi_fac !== '' || $kapasitas_fac !== '') {
            $totalData = $this->facModel->countFac_filtered(
                $tipe_fac,
                $lokasi_fac,
                $kapasitas_fac
            );
        } else {
            $totalData = $this->facModel->getTotalFacilities();
        }

        $totalPages = (int) ceil($totalData / $limit);

        // Jika belum ada data, tetap gunakan halaman 1
        if ($totalPages === 0) {
            $page = 1;
        } else {
            $page = min($page, $totalPages);
        }

        $offset = ($page - 1) * $limit;

        // Ambil fasilitas untuk halaman saat ini
        if ($tipe_fac !== '' || $lokasi_fac !== '' || $kapasitas_fac !== '') {
            $facilities = $this->facModel->getFac_limit_filtered(
                $limit,
                $offset,
                $tipe_fac,
                $lokasi_fac,
                $kapasitas_fac
            );
        } else {
            $facilities = $this->facModel->getFac_limit($limit, $offset);
        }

        require_once __DIR__ . '/../../../views/pengguna/pengguna_beranda.php';
    }

    public function detail_fasilitas(){
        $id = $_GET['id'] ?? '';

        $fac = $this->facModel->getFacilityById($id);

        $tgl_cek_slot_waktu = $_GET['tgl_cek_slot_waktu'] ?? date('Y-m-d');

        $date = new DateTime($tgl_cek_slot_waktu);

        $dayOfWeek = (int) $date->format('w');

        $startOfWeek = clone $date;
        $startOfWeek->modify("-{$dayOfWeek} days");

        $weekDates = [];

        for ($i = 0; $i < 7; $i++) {
            $weekDates[] = (clone $startOfWeek)->modify("+{$i} days");
        }
        
        $startDate = $weekDates[0]->format('Y-m-d');
        $endDate = $weekDates[6]->format('Y-m-d');

        $reservations = $this->rsvModel->getApprovedByFacilityAndDateRange(
            $id,
            $startDate,
            $endDate
        );
        require_once __DIR__ . '/../../../views/pengguna/detail_fasilitas.php';
    }
    
}