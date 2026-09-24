<?php
require_once __DIR__ . "/../models/account_model.php";
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'admit':
        $unv_id = $_POST['unv_id'] ?? '';
        $role   = $_POST['role'] ?? '';
        if ($unv_id === '' || !in_array($role, ['pengguna', 'petugas', 'admin'])) {
            header("Location: ../../views/admin/kelola_akun.php?error=1");
            exit;
        }

        admitAccount($conn, $unv_id, $role);
        header("Location: ../../views/admin/kelola_akun.php");
        exit;

    case 'reject':
        $unv_id = $_POST['unv_id'] ?? '';
        if ($unv_id === '') {
            header("Location: ../../views/admin/kelola_akun.php?error=1");
            exit;
        }

        rejectAccount($conn, $unv_id);
        header("Location: ../../views/admin/kelola_akun.php");
        exit;

    case 'create':
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? '';

        if ($username === '' || $password === '' || $email === '' || !in_array($role, ['pengguna', 'petugas'])) {
            header("Location: ../../views/admin/kelola_akun_baru.php?error=1");
            exit;
        }

        $result = createAccountDirect($conn, $username, $password, $email, $role);
        if ($result !== true) {
            header("Location: ../../views/admin/kelola_akun_baru.php?error=1");
            exit;
        }

        header("Location: ../../views/admin/kelola_akun.php");
        exit;

    default:
        header("Location: ../../views/admin/kelola_akun.php");
        exit;
}