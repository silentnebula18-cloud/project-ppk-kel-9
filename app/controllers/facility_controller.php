<?php

require_once _DIR_ . '/../../app/helpers/validate_session.php';
check_session_valid('admin');

require_once __DIR__ . "/../models/facility_model.php";
require_once __DIR__ . "/../helpers/upload_helper.php";
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

        // ID dibuat di sini supaya foto bisa langsung diberi nama <fac_id>.webp
        $id = buatUuid();
        $foto = simpanFotoWebp($_FILES['fac_photo'] ?? null, 'fac_img', $id);
        if ($foto['status'] === 'error') {
            header("Location: ../../views/admin/kelola_fasilitas_baru.php?error=foto&msg=" . urlencode($foto['pesan']));
            exit;
        }

        $photoPath = $foto['status'] === 'ok' ? $foto['path'] : null;

        if (!createFacility($conn, $id, $name, $type, $location, $capacity, $desc, $photoPath)) {
            hapusFoto($id . '.webp', 'fac_img');
            header("Location: ../../views/admin/kelola_fasilitas_baru.php?error=1");
            exit;
        }
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

        // Kalau ada foto baru, file <fac_id>.webp otomatis tertimpa (foto lama hilang sendiri)
        $foto = simpanFotoWebp($_FILES['fac_photo'] ?? null, 'fac_img', $id);
        if ($foto['status'] === 'error') {
            header("Location: ../../views/admin/kelola_fasilitas_baru.php?id=" . urlencode($id) . "&error=foto&msg=" . urlencode($foto['pesan']));
            exit;
        }

        $photoPath = $foto['status'] === 'ok' ? $foto['path'] : null;

        updateFacility($conn, $id, $name, $type, $location, $capacity, $desc, $status, $photoPath);
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
            $lama = getFacilityById($conn, $id);
            $result = deleteFacility($conn, $id);
            if ($result === 'has_history') {
                header("Location: ../../views/admin/kelola_fasilitas_detail.php?id=" . urlencode($id) . "&error=has_history");
                exit;
            }
            // Fasilitas berhasil dihapus & file fotonya dihapus juga
            if ($result === true && $lama && !empty($lama['fac_photo'])) {
                hapusFoto(basename($lama['fac_photo']), 'fac_img');
            }
        }
        header("Location: ../../views/admin/kelola_fasilitas.php");
        exit;

    default:
        header("Location: ../../views/admin/kelola_fasilitas.php");
        exit;
}