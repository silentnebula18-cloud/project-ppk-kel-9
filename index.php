<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$validRoles = ['admin', 'pengguna', 'petugas'];

if (empty($_SESSION['role']) || !in_array($_SESSION['role'], $validRoles, true)) {
    unset($_SESSION['role']);
    header("Location: views/index.html");
    exit;
}

if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
    unset(
        $_SESSION['role'],
        $_SESSION['user_id'],
        $_SESSION['username'],
        $_SESSION['email']
    );
    header("Location: views/index.html");
    exit;
}

switch ($_SESSION["role"]) {
    case "admin":
        header("Location: views/admin/admin_beranda.php");
        exit;

    case "petugas":
        header("Location: public/index.php");
        exit;

    case "pengguna":
        $action = $_POST['action'] ?? '';
        switch ($action) {
            case 'batalkan_reservasi':
                require_once __DIR__ . "/app/controllers/pengguna/reservasi_saya_controller.php";
                $controller = new reservasi_saya_controller();
                $rsv_id = $_POST['rsv_id'] ?? '';
                $cancel_reason = $_POST['cancel_reason'] ?? '';
                $controller->batalkanReservasi($rsv_id, $cancel_reason);
                break;

            case 'ajukan_reservasi':
                require_once __DIR__ . "/app/controllers/pengguna/form_rsv_controller.php";
                $controller = new form_rsv_controller();
                $controller->ajukan_reservasi();
                break;

            case 'ajukan_laporan':
                require_once __DIR__ . "/app/controllers/pengguna/form_rep_controller.php";
                $controller = new form_rep_controller();
                $controller->ajukan_laporan();
                break;

            default:
                $page = $_GET['page'] ?? 'beranda';
                switch ($page) {
                    case 'beranda':
                    case 'fasilitas':
                        require_once __DIR__ . "/app/controllers/pengguna/pengguna_beranda_controller.php";
                        $controller = new pengguna_beranda_controller();
                        $controller->index();
                        break;

                    case 'detail_fasilitas':
                    case 'cek_slot_waktu':
                        require_once __DIR__ . "/app/controllers/pengguna/pengguna_beranda_controller.php";
                        $controller = new pengguna_beranda_controller();
                        $controller->detail_fasilitas();
                        break;

                    case 'form_rsv_2':
                    case 'cek_slot_waktu_form_rsv_2':
                        require_once __DIR__ . "/app/controllers/pengguna/form_rsv_controller.php";
                        $controller = new form_rsv_controller();
                        $controller->form_rsv_2();
                        break;

                    case 'reservasi_saya':
                        $rsv_saya_fltr_status = $_GET['rsv_saya_fltr_status'] ?? 'semua';
                        require_once __DIR__ . "/app/controllers/pengguna/reservasi_saya_controller.php";
                        $controller = new reservasi_saya_controller();
                        $controller->index($rsv_saya_fltr_status);
                        break;

                    case 'detail_reservasi':
                        $rsv_id = $_GET['rsv_id'] ?? '';
                        $fac_id = $_GET['fac_id'] ?? '';
                        $status = $_GET['status'] ?? '';

                        if ($rsv_id === '' || $fac_id === '' || $status === '') {
                            $_SESSION['notice'] = [
                                'type' => 'error',
                                'message' => 'Data reservasi tidak lengkap.'
                            ];
                            header("Location: index.php?page=reservasi_saya");
                            exit;
                        }

                        require_once __DIR__ . "/app/controllers/pengguna/reservasi_saya_controller.php";
                        $controller = new reservasi_saya_controller();
                        $controller->detailReservasi($rsv_id, $fac_id, $status);
                        break;

                    case 'form_rsv_1':
                        require_once __DIR__ . "/app/controllers/pengguna/form_rsv_controller.php";
                        $controller = new form_rsv_controller();
                        $controller->form_rsv_1();
                        break;

                    case 'form_rep_2':
                        require_once __DIR__ . "/app/controllers/pengguna/form_rep_controller.php";
                        $controller = new form_rep_controller();
                        $controller->form_rep_2();
                        break;

                    case 'form_rep_1':
                        require_once __DIR__ . "/app/controllers/pengguna/form_rep_controller.php";
                        $controller = new form_rep_controller();
                        $controller->form_rep_1();
                        break;

                    case 'laporan_saya':
                        require_once __DIR__ . "/app/controllers/pengguna/laporan_saya_controller.php";
                        $controller = new laporan_saya_controller();
                        $controller->index();
                        break;

                    case 'detail_laporan':
                        require_once __DIR__ . "/app/controllers/pengguna/laporan_saya_controller.php";
                        $controller = new laporan_saya_controller();
                        $controller->detailLaporan();
                        break;

                    default:
                        require_once __DIR__ . "/app/controllers/pengguna/pengguna_beranda_controller.php";
                        $controller = new pengguna_beranda_controller();
                        $controller->index();
                        break;
                }
                break;
        }
        break;

    default:
        header("Location: views/index.html");
        exit;
}