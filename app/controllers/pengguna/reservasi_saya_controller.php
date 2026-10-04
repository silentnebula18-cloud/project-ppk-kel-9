<?php
require_once __DIR__ . '/../../../config/db_connect.php';
require_once __DIR__ . "/../../models/reservation_model.php";

class reservasi_saya_controller {
    private $db;
    private $rsvModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $database = new db_connect();
        $this->db = $database->getConnection();
        
        $this->rsvModel = new reservation_model($this->db);
    }

    public function index($tab) {
        $user_id = $_SESSION['user_id'] ?? '';

        if ($user_id === '') {
            die("User belum login.");
        }

        if ($tab === 'semua') {

            $reservations = $this->rsvModel->getByUser($user_id);

        } elseif ($tab === 'proses') {

            $reservations = $this->rsvModel->getByUser_status_1(
                $user_id,
                'proses pengajuan'
            );

        }

        require_once __DIR__ . "/../../../views/pengguna/reservasi_saya.php";
    }
    
}