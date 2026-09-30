<?php
// public/index.php
if (session_status() === PHP_SESSION_NONE) session_start();

// SIMULASI SESSION UNTUK TESTING
// Menggunakan ID petugas dummy dari database yang sudah Anda insert ('off-1' atau 'off-2')
$_SESSION['user_id'] = 'off-1'; 
$_SESSION['username'] = 'petugas_1';
$_SESSION['role'] = 'petugas';

require_once __DIR__ . '/../app/controllers/DashboardController.php';
require_once __DIR__ . '/../app/controllers/ReservationController.php';

$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';

if ($page === 'reservation') {
    $controller = new ReservationController();
    if ($action === 'approve') {
        $controller->approve();
    } elseif ($action === 'reject') {
        $controller->reject();
    } elseif ($action === 'cancel') { // Tambahkan routing ini
        $controller->cancel();
    }
} else {
    $controller = new DashboardController();
    $controller->index();
}