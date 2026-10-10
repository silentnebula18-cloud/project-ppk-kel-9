<?php
require_once __DIR__ . '/../../config/db_connect.php';
require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/../helpers/validate_session.php';

class ReportController {
    private $db;
    private $reportModel;

    public function __construct() {
        check_session_valid('petugas');
        $database = new Database();
        $this->db = $database->getConnection();
        $this->reportModel = new Report($this->db);
    }

    public function index() {
        $reports = $this->reportModel->getAll();

        require_once __DIR__ . '/../../views/petugas/reports.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rep_id'])) {
            $id = $_POST['rep_id'];
            $status = $_POST['rep_status'] ?? '';
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

    public function image() {
        if (empty($_SESSION['user_id'])) {
            http_response_code(403);
            exit;
        }

        $id = basename((string) ($_GET['id'] ?? ''));
        $file = __DIR__ . '/../../storage/reports_img/' . $id . '.webp';

        if ($id === '' || !is_file($file)) {
            http_response_code(404);
            exit;
        }

        header('Content-Type: image/webp');
        header('Content-Length: ' . filesize($file));
        header('Cache-Control: private, max-age=3600');
        header('X-Content-Type-Options: nosniff');
        readfile($file);
        exit;
    }
}