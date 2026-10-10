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
        // Memastikan pengguna sudah login
        $user_id = $_SESSION['user_id'] ?? '';

        if ($user_id === '') {
            header("Location: ../../index.php");
            exit;
        }

        // Validasi filter status
        $statusValid = ['semua', 'proses pengajuan', 'disetujui', 'ditolak', 'dibatalkan', 'kadaluarsa','riwayat'];

        if (!in_array($rsv_saya_fltr_status, $statusValid, true)) {
            $rsv_saya_fltr_status = 'semua';
        }

        // Pengaturan pagination
        $limit = 5;
        $page = max(1, (int) ($_GET['haltab'] ?? 1));

        // Hitung total data dan halaman
        $totalData = $this->rsvModel->countByUser_filtered(
            $user_id,
            $rsv_saya_fltr_status
        );

        $totalPages = (int) ceil($totalData / $limit);

        // Jika belum ada data, tetap gunakan halaman 1
        if ($totalPages === 0) {
            $page = 1;
        } else {
            $page = min($page, $totalPages);
        }

        $offset = ($page - 1) * $limit;

        // Ambil data reservasi untuk halaman saat ini
        $reservasi = $this->rsvModel->getByUser_filtered(
            $user_id,
            $rsv_saya_fltr_status,
            $limit,
            $offset
        );

        require_once __DIR__ . "/../../../views/pengguna/reservasi_saya.php";
    }

    public function detailReservasi($rsv_id, $fac_id, $status) {
        // Memastikan pengguna sudah login
        $user_id = $_SESSION['user_id'] ?? '';

        if ($user_id === '') {
            header("Location: ../../index.php");
            exit;
        }

        // Validasi filter status
        $statusValid = ['semua', 'proses pengajuan', 'disetujui', 'ditolak', 'dibatalkan', 'kadaluarsa','riwayat'];

        if (!in_array($status, $statusValid, true)) {
            $status = 'semua';
        }

        switch ($status) {
            case 'proses pengajuan':
                $reservasi = $this->rsvModel->getDetailReservasi_ProsesPengajuan($rsv_id, $user_id);
                break;
            case 'disetujui':
                $reservasi = $this->rsvModel->getDetailReservasi_Disetujui($rsv_id, $user_id);
                break;
            case 'kadaluarsa':
                $reservasi = $this->rsvModel->getDetailReservasi_Kadaluarsa($rsv_id, $user_id);
                break;
            case 'dibatalkan':
                $reservasi = $this->rsvModel->getDetailReservasi_Dibatalkan($rsv_id, $user_id);
                break;
            case 'ditolak':
                $reservasi = $this->rsvModel->getDetailReservasi_Ditolak($rsv_id, $user_id);
                break;
            default:
                $_SESSION['notice'] = [
                    'type' => 'error',
                    'message' => 'Status reservasi tidak valid'
                ];

                header("Location: index.php?page=reservasi_saya");
        }

        if (!$reservasi) {
            $_SESSION['notice'] = [
                'type' => 'error',
                'message' => 'Reservasi tidak ditemukan atau tidak dapat diakses.'
            ];

            header("Location: index.php?page=reservasi_saya");
            exit;
        }

        $fasilitas = $this->facModel->getFacilityById($fac_id);

        if (!$fasilitas) {
            $_SESSION['notice'] = [
                'type' => 'error',
                'message' => 'Fasilitas tidak ditemukan.'
            ];

            header("Location: index.php?page=reservasi_saya");
            exit;
        }

        require_once __DIR__ . "/../../../views/pengguna/detail_reservasi.php";
    }

    public function batalkanReservasi($rsv_id, $cancel_reason) {
        // Memastikan pengguna sudah login
        $user_id = $_SESSION['user_id'] ?? '';

        if ($user_id === '') {
            header("Location: ../../index.php");
            exit;
        }

        // Validasi alasan pembatalan
        $cancel_reason = trim($cancel_reason);

        if ($cancel_reason === '' || strlen($cancel_reason) > 300) {
            $_SESSION['notice'] = [
                'type' => 'error',
                'message' => 'Alasan pembatalan wajib diisi dan maksimal 300 karakter.'
            ];

            header("Location: index.php?page=reservasi_saya");
            exit;
        }

        // Ambil reservasi milik pengguna
        $reservasi = $this->rsvModel->getRsvForCancel(
            $rsv_id,
            $user_id
        );

        if (!$reservasi) {
            $_SESSION['notice'] = [
                'type' => 'error',
                'message' => 'Reservasi tidak ditemukan atau tidak dapat diakses.'
            ];

            header("Location: index.php?page=reservasi_saya");
            exit;
        }

        // Validasi status reservasi
        if (
            $reservasi['rsv_status'] !== 'proses pengajuan' &&
            $reservasi['rsv_status'] !== 'disetujui'
        ) {
            $_SESSION['notice'] = [
                'type' => 'error',
                'message' => 'Reservasi tidak dapat dibatalkan.'
            ];

            header("Location: index.php?page=reservasi_saya");
            exit;
        }

        // Validasi batas pembatalan: 24 jam sebelum waktu mulai
        $batas_pembatalan = strtotime(
            $reservasi['rsv_date'] . ' ' . $reservasi['start_time']
        ) - (24 * 60 * 60);

        if (time() >= $batas_pembatalan) {
            $_SESSION['notice'] = [
                'type' => 'error',
                'message' => 'Batas waktu pembatalan reservasi telah lewat.'
            ];

            header("Location: index.php?page=reservasi_saya");
            exit;
        }

        // Proses pembatalan beserta alasan
        $berhasil = $this->rsvModel->batalkanReservasi_pengguna(
            $rsv_id,
            $user_id,
            $cancel_reason
        );

        if ($berhasil) {
            $_SESSION['notice'] = [
                'type' => 'success',
                'message' => 'Reservasi berhasil dibatalkan.'
            ];
        } else {
            $_SESSION['notice'] = [
                'type' => 'error',
                'message' => 'Reservasi gagal dibatalkan.'
            ];
        }

        header("Location: index.php?page=reservasi_saya");
        exit;
    }
    
}