<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// SEMENTARA: simulasi login petugas, hapus kalau login sudah jadi
//$_SESSION['user_id'] = 'off-2';
//$_SESSION['role']    = 'petugas';


require_once __DIR__ . '/../app/controllers/DashboardController.php';
require_once __DIR__ . '/../app/controllers/ReservationController.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'approve_reservation':
        $controller = new ReservationController();
        $controller->approve();
        break;

    case 'reject_reservation':
        $controller = new ReservationController();
        $controller->reject();
        break;

    default:
        $controller = new DashboardController();
        $controller->index();
        break;
}