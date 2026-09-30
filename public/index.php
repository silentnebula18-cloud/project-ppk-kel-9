<?php
// index.php di root proyek atau public/index.php
require_once __DIR__ . '/../app/controllers/DashboardController.php';

$controller = new DashboardController();
$controller->index();