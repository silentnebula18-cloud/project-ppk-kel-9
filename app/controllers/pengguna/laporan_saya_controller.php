<?php

require_once __DIR__ . '/../../../config/db_connect.php';
require_once __DIR__ . "/../../models/facility_model.php";
require_once __DIR__ . "/../../models/report_model.php";
require_once __DIR__ . '/../../helpers/validate_session.php';

class laporan_saya_controller {
    private $db;
    private $facModel;
    private $repModel;

    public function __construct() {
        check_session_valid('pengguna');

        $database = new db_connect();
        $this->db = $database->getConnection();

        $this->facModel = new facility_model($this->db);
        $this->repModel = new report_model($this->db);
    }

    public function index() {
        check_session_valid('pengguna');
        $user_id = $_SESSION['user_id'] ?? '';

        // Ambil filter
        $category = $_GET['category'] ?? '';
        $status = $_GET['status'] ?? '';

        // Validasi filter kategori
        $kategoriValid = ['kerusakan bangunan', 'kerusakan peralatan', 'kelistrikan', 'kebersihan', 'lainnya'];

        if (!in_array($category, $kategoriValid, true)) {
            $category = '';
        }

        // Validasi filter status
        $statusValid = ['baru', 'diproses', 'selesai', 'ditolak'];

        if (!in_array($status, $statusValid, true)) {
            $status = '';
        }

        // Pengaturan pagination
        $limit = 5;
        $page = max(1, (int) ($_GET['haltab'] ?? 1));

        // Hitung jumlah data sesuai filter
        $totalData = $this->repModel->countByUser_filtered(
            $user_id,
            $category,
            $status
        );

        $totalPages = (int) ceil($totalData / $limit);

        // Jika tidak ada data, tetap gunakan halaman 1
        if ($totalPages === 0) {
            $page = 1;
        } else {
            $page = min($page, $totalPages);
        }

        $offset = ($page - 1) * $limit;

        // Ambil data laporan untuk halaman saat ini
        $reports = $this->repModel->getByUser_filtered(
            $user_id,
            $category,
            $status,
            $limit,
            $offset
        );

        require_once __DIR__ . '/../../../views/pengguna/laporan_saya.php';
    }

    public function detailLaporan() {
        // Memastikan pengguna sudah login
        $user_id = $_SESSION['user_id'] ?? '';

        if ($user_id === '') {
            header("Location: ../../index.php");
            exit;
        }

        $rep_id = $_GET['rep_id'] ?? '';

        if ($rep_id === '') {
            $_SESSION['notice'] = [
                'type' => 'error',
                'message' => 'Laporan tidak ditemukan.'
            ];
            header("Location: ../../index.php?page=laporan_saya");
            exit;
        }

        // Ambil detail laporan milik pengguna yang login
        $laporan = $this->repModel->getDetailById($rep_id, $user_id);

        if (!$laporan) {
            $_SESSION['notice'] = [
                'type' => 'error',
                'message' => 'Laporan tidak ditemukan.'
            ];
            header("Location: ../../index.php?page=laporan_saya");
            exit;
        }

        // Ambil detail fasilitas melalui facility_model
        $fasilitas = $this->facModel->getFacilityById($laporan['fac_id']);

        if (!$fasilitas) {
            $_SESSION['notice'] = [
                'type' => 'error',
                'message' => 'Data fasilitas tidak ditemukan.'
            ];
            header("Location: ../../index.php?page=laporan_saya");
            exit;
        }

        // Path foto berdasarkan rep_id
        $foto = 'storage/report_img/' . $rep_id . ".webp";

        require_once __DIR__ . '/../../../views/pengguna/detail_laporan.php';
    }
}
