<?php
// public/index.php
if (session_status() === PHP_SESSION_NONE) session_start();

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
    }
} else {
    $controller = new DashboardController();
    $controller->index();
}