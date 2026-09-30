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

        // Ambil daftar fasilitas untuk dropdown modal
        $queryFac = "SELECT fac_id, fac_name FROM facilities WHERE fac_status = 'aktif'";
        $stmtFac = $this->db->prepare($queryFac);
        $stmtFac->execute();
        $facilities = $stmtFac->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../../views/petugas/reports.php';
    }

    // Process Update Laporan
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rep_id'])) {
            $id = $_POST['rep_id'];
            $status = $_POST['rep_status'];
            $fac_id = $_POST['fac_id'];
            $resolution = trim($_POST['resolution'] ?? '');

            try {
                $this->reportModel->updateReport($id, $status, $fac_id, $resolution);
                $_SESSION['flash_success'] = "Laporan berhasil diperbarui!";
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $_SESSION['flash_error'] = "Gagal memperbarui laporan: " . $e->getMessage();
            }

            header("Location: index.php?page=reports");
            exit;
        }
    }
}