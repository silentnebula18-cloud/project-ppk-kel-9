<?php
require_once __DIR__ . '/../../../config/db_connect.php';
require_once __DIR__ . "/../../models/reservation_model.php";
require_once __DIR__ . "/../../models/facility_model.php";

class reservasi_saya_controller {
    private $db;
    private $rsvModel;
    private $facModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $database = new db_connect();
        $this->db = $database->getConnection();
        
        $this->rsvModel = new reservation_model($this->db);
        $this->facModel = new facility_model($this->db);
    }

    public function index($rsv_saya_fltr_status) {
        $user_id = $_SESSION['user_id'] ?? '';

        if ($user_id === '') {
            die("User belum login.");
        }

        switch ($rsv_saya_fltr_status) {
            case 'proses pengajuan':
                $reservasi = $this->rsvModel->getByUser_ProsesPengajuan($user_id);
                break;
            case 'disetujui':
                $reservasi = $this->rsvModel->getByUser_Disetujui($user_id);
                break;
            case 'kadaluarsa':
                $reservasi = $this->rsvModel->getByUser_Kadaluarsa($user_id);
                break;
            case 'dibatalkan':
                $reservasi = $this->rsvModel->getByUser_Dibatalkan($user_id);
                break;
            case 'ditolak':
                $reservasi = $this->rsvModel->getByUser_Ditolak($user_id);
                break;
            case 'semua':
                $reservasi = array_merge(
                    $this->rsvModel->getByUser_ProsesPengajuan($user_id),
                    $this->rsvModel->getByUser_Disetujui($user_id),
                    $this->rsvModel->getByUser_Kadaluarsa($user_id),
                    $this->rsvModel->getByUser_Dibatalkan($user_id),
                    $this->rsvModel->getByUser_Ditolak($user_id)
                );
                break;
            case 'riwayat':
                $reservasi = array_merge(
                    $this->rsvModel->getByUser_RiwayatDisetujui($user_id),
                    $this->rsvModel->getByUser_Kadaluarsa($user_id),
                    $this->rsvModel->getByUser_Dibatalkan($user_id),
                    $this->rsvModel->getByUser_Ditolak($user_id)
                );
                break;
            default:
                $rsv_saya_fltr_status = 'semua';
                $reservasi = [];
                break;
        }

        require_once __DIR__ . "/../../../views/pengguna/reservasi_saya.php";
    }

    public function detailReservasi($rsv_id, $fac_id, $status) {

        $user_id = $_SESSION['user_id'] ?? '';

        if ($user_id === '') {
            die("User belum login.");
        }

        switch ($status) {

            case 'proses pengajuan':
                $reservasi = $this->rsvModel
                    ->getDetailReservasi_ProsesPengajuan($rsv_id, $user_id);
                break;

            case 'disetujui':
                $reservasi = $this->rsvModel
                    ->getDetailReservasi_Disetujui($rsv_id, $user_id);
                break;

            case 'kadaluarsa':
                $reservasi = $this->rsvModel
                    ->getDetailReservasi_Kadaluarsa($rsv_id, $user_id);
                break;

            case 'dibatalkan':
                $reservasi = $this->rsvModel
                    ->getDetailReservasi_Dibatalkan($rsv_id, $user_id);
                break;

            case 'ditolak':
                $reservasi = $this->rsvModel
                    ->getDetailReservasi_Ditolak($rsv_id, $user_id);
                break;

            default:
                die("Status reservasi tidak valid.");
        }

        if (!$reservasi) {
            die("Reservasi tidak ditemukan.");
        }

        $fasilitas = $this->facModel->getFacilityById($fac_id);

        if (!$fasilitas) {
            die("Fasilitas tidak ditemukan.");
        }

        require_once __DIR__ . "/../../../views/pengguna/detail_reservasi.php";
    }

    public function batalkanReservasi($rsv_id) {

        $user_id = $_SESSION['user_id'] ?? '';

        if ($user_id === '') {
            die("User belum login.");
        }

        // Ambil reservasi milik user
        $reservasi = $this->rsvModel->getRsvForCancel(
            $rsv_id,
            $user_id
        );

        if (!$reservasi) {
            $_SESSION['notice'] = "Reservasi tidak ditemukan.";
            header("Location: ../../index.php?page=reservasi_saya");
            exit;
        }

        // SERVER-SIDE VALIDATION
        $statusValid =
            $reservasi['rsv_status'] === 'proses pengajuan'
            || $reservasi['rsv_status'] === 'disetujui';

        if (!$statusValid) {
            $_SESSION['notice'] = "Reservasi tidak dapat dibatalkan.";
            header("Location: ../../index.php?page=reservasi_saya");
            exit;
        }

        $batas_pembatalan = strtotime(
            $reservasi['rsv_date'] . ' ' . $reservasi['start_time']
        ) - (24 * 60 * 60);

        if (time() >= $batas_pembatalan) {
            $_SESSION['notice'] = "Batas waktu pembatalan reservasi telah lewat.";
            header("Location: ../../index.php?page=reservasi_saya");
            exit;
        }

        // Batalkan reservasi
        $berhasil = $this->rsvModel->batalkanReservasi(
            $rsv_id,
            $user_id
        );

        if ($berhasil) {
            $_SESSION['notice'] = "Reservasi berhasil dibatalkan.";
        } else {
            $_SESSION['notice'] = "Reservasi gagal dibatalkan.";
        }

        header("Location: ../../index.php?page=reservasi_saya");
        exit;
    }
    
}