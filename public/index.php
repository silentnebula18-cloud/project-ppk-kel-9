<?php
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