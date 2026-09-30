<?php
// public/index.php
if (session_status() === PHP_SESSION_NONE) session_start();

// SIMULASI SESSION UNTUK TESTING
$_SESSION['user_id'] = 'off-1'; 
$_SESSION['username'] = 'petugas_1';
$_SESSION['role'] = 'petugas';

require_once __DIR__ . '/../app/controllers/DashboardController.php';
require_once __DIR__ . '/../app/controllers/ReservationController.php';
require_once __DIR__ . '/../app/controllers/ReportController.php';

$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';

if ($page === 'reservation') {
    $controller = new ReservationController();
    if ($action === 'approve') {
        $controller->approve();
    } elseif ($action === 'reject') {
        $controller->reject();
    } elseif ($action === 'cancel') {
        $controller->cancel();
    }
} elseif ($page === 'reports') { // <--- TAMBAHKAN BLOK PERCABANGAN INI
    $controller = new ReportController();
    if ($action === 'update') {
        $controller->update();
    } else {
        $controller->index();
    }
} else {
    $controller = new DashboardController();
    $controller->index();
}