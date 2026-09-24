<?php
    require_once __DIR__ . '/../../config/db_connect.php';
    require_once __DIR__ . '/../models/FacilityModel.php';

    class FacilityController{
        public function fasilitas(){
            global $conn;
            $model = new FacilityModel($conn);

            $limit = 5;
            $page = isset($_GET['haltab']) ? (int)$_GET['haltab'] : 1;
            $offset = ($page - 1) * $limit;

            $facilities = $model->getFacilities($limit, $offset);
            $totalData = $model->getTotalFacilities();
            $totalPages = ceil($totalData / $limit);

            require_once __DIR__ . '/../../views/pengguna/katalog_fasilitas.php';
        }

        public function getFacilityDetail(){
            global $conn;
            $model = new FacilityModel($conn);

            $id = $_GET['fac_id'] ?? '';
            $facility = $model->getFacOnId($id);

            echo json_encode($facility);
        }
    }
?>