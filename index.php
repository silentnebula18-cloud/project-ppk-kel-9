<?php

session_start();

if (!isset($_SESSION["role"])) {
    require __DIR__ . "/views/index.html";
    exit;
}

switch ($_SESSION["role"]) {
    case "admin":
        $action = $_POST['action'] ?? '';
        switch($action){
            case 'admit':
                require_once __DIR__ . "/app/controllers/account_controller.php";
                $controller = new account_controller();
                $controller->admit();
                break;
            case 'reject':
                require_once __DIR__ . "/app/controllers/account_controller.php";
                $controller = new account_controller();
                $controller->reject();
                break;
            case 'create':
                require_once __DIR__ . "/app/controllers/account_controller.php";
                $controller = new account_controller();
                $controller->create();
                break;
            case 'createFac':
                require_once __DIR__ . "/app/controllers/facility_controller.php";
                $controller = new facility_controller();
                $controller->create();
                break;
            case 'updateFac':
                require_once __DIR__ . "/app/controllers/facility_controller.php";
                $controller = new facility_controller();
                $controller->update();
                break;
            case 'deleteFac':
                require_once __DIR__ . "/app/controllers/facility_controller.php";
                $controller = new facility_controller();
                $controller->delete();
                break;
            case 'activate':
                require_once __DIR__ . "/app/controllers/facility_controller.php";
                $controller = new facility_controller();
                $controller->activate();
                break;
            case 'deactivate':
                require_once __DIR__ . "/app/controllers/facility_controller.php";
                $controller = new facility_controller();
                $controller->deactivate();
                break;
            default: 
                $page = $_GET['page'] ?? '';
                switch($page){
                    case 'beranda':
                        require_once __DIR__ . "/app/controllers/admin/admin_dashboard_controller.php";
                        $controller = new admin_dashboard_controller();
                        $controller->index();
                        break;
                    case 'kelola_akun':
                        require_once __DIR__ . "/app/controllers/account_controller.php";
                        $controller = new account_controller();
                        $controller->kelola_akun();
                        break;
                    case 'kelola_akun_baru':
                        require_once __DIR__ . "/app/controllers/account_controller.php";
                        $controller = new account_controller();
                        $controller->kelola_akun_baru();
                        break;
                    case 'kelola_fasilitas':
                        require_once __DIR__ . "/app/controllers/facility_controller.php";
                        $controller = new facility_controller();
                        $controller->kelola_fasilitas();
                        break;
                    case 'kelola_fasilitas_baru':
                        require_once __DIR__ . "/app/controllers/facility_controller.php";
                        $controller = new facility_controller();
                        $controller->kelola_fasilitas_baru();
                        break;
                    case 'kelola_fasilitas_detail':
                        require_once __DIR__ . "/app/controllers/facility_controller.php";
                        $controller = new facility_controller();
                        $controller->kelola_fasilitas_detail();
                        break;
                    default:
                        require_once __DIR__ . "/app/controllers/admin/admin_dashboard_controller.php";
                        $controller = new admin_dashboard_controller();
                        $controller->index();
                        break;    
                }
                break;
            }
        break;

    case "pengguna":
        $action = $_POST['action'] ?? '';
        switch($action){
            case 'ajukan_reservasi':
                require_once __DIR__ . "/app/controllers/pengguna/form_rsv_controller.php";
                $controller = new form_rsv_controller();
                $controller->ajukan_reservasi();
                break;
            default: 
                $page = $_GET['page'] ?? '';
                switch($page){
                    case 'beranda':
                        require_once __DIR__ . "/app/controllers/pengguna/pengguna_dashboard_controller.php";
                        $controller = new pengguna_dashboard_controller();
                        $controller->index();
                        break;
                    case 'detail_fasilitas':
                        require_once __DIR__ . "/app/controllers/pengguna/pengguna_dashboard_controller.php";
                        $controller = new pengguna_dashboard_controller();
                        $controller->detail_fasilitas();
                        break;
                    case 'cek_slot_waktu':
                        require_once __DIR__ . "/app/controllers/pengguna/pengguna_dashboard_controller.php";
                        $controller = new pengguna_dashboard_controller();
                        $controller->detail_fasilitas();
                        break;
                    case 'form_rsv_2':
                        require_once __DIR__ . "/app/controllers/pengguna/form_rsv_controller.php";
                        $controller = new form_rsv_controller();
                        $controller->form_rsv_2();
                        break;
                    case 'cek_slot_waktu_form_rsv_2':
                        require_once __DIR__ . "/app/controllers/pengguna/form_rsv_controller.php";
                        $controller = new form_rsv_controller();
                        $controller->form_rsv_2();
                        break;
                    case 'reservasi_saya':
                        $tab = $_GET['tab'] ?? 'semua';

                        require_once __DIR__ . "/app/controllers/pengguna/reservasi_saya_controller.php";
                        $controller = new reservasi_saya_controller();
                        $controller->index($tab);
                        break;
                    case 'form_rsv_1':
                        require_once __DIR__ . "/app/controllers/pengguna/form_rsv_controller.php";
                        $controller = new form_rsv_controller();
                        $controller->form_rsv_1();
                        break;
                    default:
                        require_once __DIR__ . "/app/controllers/pengguna/pengguna_dashboard_controller.php";
                        $controller = new pengguna_dashboard_controller();
                        $controller->index();
                        break;    
                }
                break;
            }
        break;

    case "petugas":
        $action = $_GET['action'] ?? '';
        switch ($action) {
            case 'approve_reservation':
                require_once __DIR__ . '/app/controllers/petugas/reservation_controller.php';

                $controller = new reservation_controller();
                $controller->approve();
                break;

            case 'reject_reservation':
                require_once __DIR__ . '/app/controllers/petugas/reservation_controller.php';
                $controller = new reservation_controller();
                $controller->reject();
                break;

            default:
                require_once __DIR__ . '/app/controllers/petugas/dashboard_controller.php';
                $controller = new dashboard_controller();
                $controller->index();
                break;
        }
        break;
    
    default:
        require __DIR__ . "/views/index.html";
        break;
    break;   
}
?>