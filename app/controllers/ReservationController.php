<?php
require_once __DIR__ . '/../../config/db_connect.php';
require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../helpers/validate_session.php';

class ReservationController {
    private $db;
    private $reservationModel;

    public function __construct() {
        check_session_valid('petugas');

        $database = new Database();
        $this->db = $database->getConnection();
        $this->reservationModel = new Reservation($this->db);
    }

    public function approve() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rsv_id'])) {
            $id = $_POST['rsv_id'];
            $res = $this->reservationModel->getById($id);

            if (!$res) {
                $_SESSION['flash_error'] = "Data reservasi tidak ditemukan!";
                header("Location: index.php?page=dashboard");
                exit;
            }

            $isOverlap = $this->reservationModel->checkOverlap(
                $res['fac_id'], 
                $res['rsv_date'], 
                $res['start_time'], 
                $res['end_time'], 
                $id
            );

            if ($isOverlap) {
                $_SESSION['flash_error'] = "Gagal menyetujui! Fasilitas ini sudah disetujui untuk reservasi lain pada jadwal yang sama.";
            } else {
                $this->reservationModel->updateStatus($id, 'disetujui');
                $_SESSION['flash_success'] = "Reservasi berhasil disetujui!";
            }

            header("Location: index.php?page=dashboard");
            exit;
        }
    }

    public function reject() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rsv_id'])) {
            $id = $_POST['rsv_id'];
            $reason = trim($_POST['rejection_reason'] ?? '');

            if (empty($reason)) {
                $_SESSION['flash_error'] = "Alasan penolakan wajib diisi!";
                header("Location: index.php?page=dashboard");
                exit;
            }

            $officerId = $_SESSION['user_id'] ?? null;
            if (!$officerId) {
                $_SESSION['flash_error'] = "Sesi login tidak ditemukan, silakan login ulang.";
                header("Location: index.php?page=dashboard");
                exit;
            }

            try {
                $this->reservationModel->updateStatus($id, 'ditolak', $reason, $officerId);
                $_SESSION['flash_success'] = "Reservasi telah ditolak.";
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $_SESSION['flash_error'] = "Gagal menolak reservasi: " . $e->getMessage();
            }

            header("Location: index.php?page=dashboard");
            exit;
        }
    }

    public function cancel() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rsv_id'])) {
            $id = $_POST['rsv_id'];
            $reason = trim($_POST['cancel_reason'] ?? '');

            if (empty($reason)) {
                $_SESSION['flash_error'] = "Alasan pembatalan wajib diisi!";
                header("Location: index.php?page=dashboard");
                exit;
            }

            $officerId = $_SESSION['user_id'] ?? null;
            if (!$officerId) {
                $_SESSION['flash_error'] = "Sesi login tidak ditemukan, silakan login ulang.";
                header("Location: index.php?page=dashboard");
                exit;
            }

            try {
                $this->reservationModel->updateStatus($id, 'dibatalkan', $reason, $officerId);
                $_SESSION['flash_success'] = "Reservasi yang telah disetujui berhasil dibatalkan.";
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $_SESSION['flash_error'] = "Gagal membatalkan reservasi: " . $e->getMessage();
            }

            header("Location: index.php?page=dashboard");
            exit;
        }
    }
}