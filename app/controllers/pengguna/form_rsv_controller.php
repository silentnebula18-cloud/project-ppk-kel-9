<?php
require_once __DIR__ . '/../../../config/db_connect.php';
require_once __DIR__ . "/../../models/facility_model.php";
require_once __DIR__ . "/../../models/reservation_model.php";

class form_rsv_controller {
    private $db;
    private $facModel;
    private $rsvModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $database = new db_connect();
        $this->db = $database->getConnection();
        
        $this->facModel = new facility_model($this->db);
        $this->rsvModel = new reservation_model($this->db);
    }

    public function form_rsv_2() {
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

        require_once __DIR__ . '/../../../views/pengguna/form_rsv_2.php';
    }

    public function cek_slot_waktu_form_rsv_2() {
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

        require_once __DIR__ . '/../../../views/pengguna/form_rsv_2.php';
    }
    
    public function ajukan_reservasi() {

        $user_id = $_SESSION['user_id'] ?? '';
        $fac_id = $_POST['fac_id'] ?? '';
        $rsv_date = $_POST['rsv_date'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';
        $purpose = trim($_POST['purpose'] ?? '');

        // 1. Data wajib harus lengkap
        if (
            $user_id === '' ||
            $fac_id === '' ||
            $rsv_date === '' ||
            $start_time === '' ||
            $end_time === '' ||
            $purpose === ''
        ) {
            die("Data reservasi belum lengkap.");
        }

        // 2. Fasilitas harus aktif
        $fac = $this->facModel->getFacilityById($fac_id);

        if (!$fac) {
            die("Fasilitas tidak ditemukan.");
        }

        if ($fac['fac_status'] !== 'aktif') {
            die("Fasilitas sedang tidak dapat digunakan untuk reservasi.");
        }

        // 3. Waktu mulai harus >= 48 jam dari sekarang
        $selectedStart = new DateTime(
            $rsv_date . ' ' . $start_time
        );

        $minimumDateTime = new DateTime();
        $minimumDateTime->modify('+48 hours');

        if ($selectedStart < $minimumDateTime) {
            die("Reservasi harus diajukan minimal 48 jam sebelum waktu mulai.");
        }

        // 4. Jam harus berada dalam 07:00 - 20:00
        if ($start_time < '07:00' || $end_time > '20:00') {
            die("Waktu reservasi harus berada antara 07:00 dan 20:00.");
        }

        // 5. Jam mulai harus sebelum jam selesai
        if ($start_time >= $end_time) {
            die("Jam mulai harus lebih awal daripada jam selesai.");
        }

        // 6. Durasi harus kelipatan 30 menit
        $start = new DateTime($rsv_date . ' ' . $start_time);
        $end = new DateTime($rsv_date . ' ' . $end_time);

        $durationMinutes = ($end->getTimestamp() - $start->getTimestamp()) / 60;

        if ($durationMinutes <= 0 || $durationMinutes % 30 !== 0) {
            die("Durasi reservasi harus dalam kelipatan 30 menit.");
        }

        // 7. Cek bentrok dengan reservasi yang sudah disetujui
        if ($this->rsvModel->checkOverlapNew(
            $fac_id,
            $rsv_date,
            $start_time,
            $end_time
        )) {
            die("Waktu yang dipilih bertabrakan dengan reservasi yang sudah disetujui.");
        }

        // 8. Simpan reservasi
        $success = $this->rsvModel->create($user_id, $fac_id, $rsv_date, $start_time, $end_time, $purpose);

        if (!$success) {
            die("Gagal mengajukan reservasi.");
        }

        header("Location: ../../index.php?page=reservasi_saya");
        exit;
    }

    public function form_rsv_1() {

        $limit = 5;

        $page = isset($_GET['haltab']) ? (int)$_GET['haltab'] : 1;
        $offset = ($page - 1) * $limit;

        $tipe_fac = $_GET['tipe_fac'] ?? '';
        $lokasi_fac = $_GET['lokasi_fac'] ?? '';
        $kapasitas_fac = $_GET['kapasitas_fac'] ?? '';

        $facilities = $this->facModel->getActiveFac_limit_filtered(
            $limit,
            $offset,
            $tipe_fac,
            $lokasi_fac,
            $kapasitas_fac
        );

        $totalData = $this->facModel->countActiveFac_filtered(
            $tipe_fac,
            $lokasi_fac,
            $kapasitas_fac
        );

        $totalPages = ceil($totalData / $limit);

        require_once __DIR__ . '/../../../views/pengguna/form_rsv_1.php';
    }
    
}