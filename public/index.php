<?php
require_once __DIR__ . '/../app/helpers/validate_session.php';
check_session_valid('petugas');

require_once __DIR__ . '/../app/controllers/DashboardController.php';
require_once __DIR__ . '/../app/controllers/ReservationController.php';
require_once __DIR__ . '/../app/controllers/ReportController.php';
require_once __DIR__ . '/../app/controllers/petugas_facility_controller.php';

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
} elseif ($page === 'reports') {
    $controller = new ReportController();
    if ($action === 'update') {
        $controller->update();
    } elseif ($action === 'image') {
        $controller->image();
    } else {
        $controller->index();
    }
} elseif ($page === 'facilities') {
    $controller = new FacilityController();
    if ($action === 'update_status') {
        $controller->updateStatus();
    } else {
        $controller->index();
    }
} else {
    $controller = new DashboardController();
    $controller->index();
}