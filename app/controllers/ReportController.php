<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Report.php';

class ReportController {
    private $db;
    private $reportModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $database = new Database();
        $this->db = $database->getConnection();
        $this->reportModel = new Report($this->db);
    }

    // Tampilkan halaman daftar laporan
    public function index() {
        $reports = $this->reportModel->getAll();

        require_once __DIR__ . '/../../views/petugas/reports.php';
    }

    // Proses update laporan
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rep_id'])) {
            $id = $_POST['rep_id'];
            $status = $_POST['rep_status'] ?? '';
            // Nama field di form adalah "resolution_note"
            $resolution = trim($_POST['resolution_note'] ?? '');

            $allowedStatus = ['baru', 'diproses', 'selesai', 'ditolak'];
            if (!in_array($status, $allowedStatus, true)) {
                $_SESSION['flash_error'] = "Data laporan tidak valid.";
                header("Location: index.php?page=reports");
                exit;
            }

            try {
                $this->reportModel->updateReport($id, $status, $resolution);
                $_SESSION['flash_success'] = "Laporan berhasil diperbarui!";
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $_SESSION['flash_error'] = "Gagal memperbarui laporan. Silakan coba lagi.";
            }
        }

        header("Location: index.php?page=reports");
        exit;
    }
}