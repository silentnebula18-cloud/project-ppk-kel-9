<?php
require_once __DIR__ . '/../../config/db_connect.php';
require_once __DIR__ . "/../models/account_model.php";

class account_controller {
    private $db;
    private $accModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $database = new db_connect();
        $this->db = $database->getConnection();
        $this->accModel = new account_model($this->db);
    }

    // Halaman kelola akun - admin
    public function kelola_akun(){
        $pending = $this->accModel->getPendingAccounts();
        require_once __DIR__ . '/../../views/admin/kelola_akun.php';
    }

    // Halaman kelola akun baru - admin
    public function kelola_akun_baru(){
        require_once __DIR__ . '/../../views/admin/kelola_akun_baru.php';
    }

    // Admit pengajuan akun
    public function admit(){
        $unv_id = $_POST['unv_id'] ?? '';
        $role   = $_POST['role'] ?? '';
        if ($unv_id === '' || !in_array($role, ['pengguna', 'petugas', 'admin'])) {
            header("Location: index.php?page=kelola_akun&error=1");
            exit;
        }

        $this->accModel->admitAccount($unv_id, $role);
        header("Location: index.php?page=kelola_akun");
        exit;
    }

    // Reject pengajuan akun
    public function reject(){
        $unv_id = $_POST['unv_id'] ?? '';
        if ($unv_id === '') {
            header("Location: index.php?page=kelola_akun&error=1");
            exit;
        }

        $this->accModel->rejectAccount($unv_id);
        header("Location: index.php?page=kelola_akun");
        exit;
    }

    // Membuat akun baru
    public function create(){
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? '';

        if ($username === '' || $password === '' || $email === '' || !in_array($role, ['pengguna', 'petugas'])) {
            header("Location: index.php?page=kelola_akun_baru&error=1");
            exit;
        }

        $result = $this->accModel->createAccountDirect($username, $password, $email, $role);
        if ($result !== true) {
            header("Location: index.php?page=kelola_akun_baru&error=1");
            exit;
        }

        header("Location: index.php?page=kelola_akun");
        exit;
    }

}