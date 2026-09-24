<?php
require_once __DIR__ . "/../models/facility_model.php";
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'create':
        $name = trim($_POST['fac_name'] ?? '');
        $type = $_POST['type'] ?? '';
        $location = trim($_POST['location'] ?? '');
        $capacity = (int) ($_POST['capacity'] ?? 0);
        $desc = trim($_POST['fac_desc'] ?? '');

        if ($name === '' || $type === '' || $location === '' || $capacity <= 0 || $desc === '') {
            header("Location: ../../views/admin/kelola_fasilitas_baru.php?error=1");
            exit;
        }

        createFacility($conn, $name, $type, $location, $capacity, $desc);
        header("Location: ../../views/admin/kelola_fasilitas.php");
        exit;

    case 'update':
        $id = $_POST['fac_id'] ?? '';
        $name = trim($_POST['fac_name'] ?? '');
        $type = $_POST['type'] ?? '';
        $location = trim($_POST['location'] ?? '');
        $capacity = (int) ($_POST['capacity'] ?? 0);
        $desc = trim($_POST['fac_desc'] ?? '');
        $status = $_POST['fac_status'] ?? '';

        if ($id === '' || $name === '' || $type === '' || $location === '' || $capacity <= 0 || $desc === '' || $status === '') {
            header("Location: ../../views/admin/kelola_fasilitas_baru.php?id=" . urlencode($id) . "&error=1");
            exit;
        }

        updateFacility($conn, $id, $name, $type, $location, $capacity, $desc, $status);
        header("Location: ../../views/admin/kelola_fasilitas_detail.php?id=" . urlencode($id));
        exit;

    case 'deactivate':
        $id = $_POST['fac_id'] ?? '';
        if ($id === '') {
            header("Location: ../../views/admin/kelola_fasilitas.php");
            exit;
        }

        setFacilityStatus($conn, $id, 'Nonaktif');
        header("Location: ../../views/admin/kelola_fasilitas_detail.php?id=" . urlencode($id));
        exit;

    case 'activate':
        $id = $_POST['fac_id'] ?? '';
        if ($id === '') {
            header("Location: ../../views/admin/kelola_fasilitas.php");
            exit;
        }

        setFacilityStatus($conn, $id, 'Aktif');
        header("Location: ../../views/admin/kelola_fasilitas_detail.php?id=" . urlencode($id));
        exit;

    case 'delete':
        $id = $_POST['fac_id'] ?? '';
        if ($id !== '') {
            deleteFacility($conn, $id);
        }
        header("Location: ../../views/admin/kelola_fasilitas.php");
        exit;

    default:
        header("Location: ../../views/admin/kelola_fasilitas.php");
        exit;
}