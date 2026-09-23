<?php

    require_once __DIR__ . '/app/controllers/FacilityController.php';

    $controller = new FacilityController();

    if (isset($_GET['page']) && $_GET['page'] === 'fasilitas') {
        $controller->fasilitas();
    }
?>