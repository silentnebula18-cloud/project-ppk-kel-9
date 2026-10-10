<?php
require_once __DIR__ . '/../../../config/db_connect.php';
require_once __DIR__ . "/../../models/facility_model.php";
require_once __DIR__ . "/../../models/report_model.php";

class form_rep_controller {
    private $db;
    private $facModel;
    private $repModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $database = new db_connect();
        $this->db = $database->getConnection();
        
        $this->facModel = new facility_model($this->db);
        $this->repModel = new report_model($this->db);
    }

    
    public function form_rep_2() {

        $id = $_GET['id'] ?? '';
        $from = $_GET['from'] ?? '';

        // Tentukan halaman untuk kembali
        if ($from === 'detail_fasilitas') {
            $redirect = "../../index.php?page=beranda";
        } elseif ($from === 'form_rep_1') {
            $redirect = "../../index.php?page=laporan_saya";
        } else {
            $redirect = "../../index.php?page=beranda";
        }

        // Validasi ID fasilitas
        if ($id === '') {
            $_SESSION['notice'] = "ID fasilitas tidak ditemukan.";
            header("Location: " . $redirect);
            exit;
        }

        // Ambil data fasilitas
        $fac = $this->facModel->getFacilityById($id);

        if (!$fac) {
            $_SESSION['notice'] = "Fasilitas tidak ditemukan.";
            header("Location: " . $redirect);
            exit;
        }

        // Pastikan fasilitas aktif
        if ($fac['fac_status'] !== 'aktif') {
            $_SESSION['notice'] = "Fasilitas sedang tidak dapat digunakan untuk membuat laporan.";
            header("Location: " . $redirect);
            exit;
        }

        require_once __DIR__ . '/../../../views/pengguna/form_rep_2.php';
    }
        
    
    
    public function ajukan_laporan() {

        $user_id = $_SESSION['user_id'] ?? '';
        $fac_id = $_POST['fac_id'] ?? '';
        $category = $_POST['category'] ?? '';
        $rep_desc = trim($_POST['rep_desc'] ?? '');
        $from = $_POST['from'] ?? '';

        // Tentukan halaman tujuan redirect
        if ($from === 'detail_fasilitas') {
            $redirect = "../../index.php?page=beranda";
        } elseif ($from === 'form_rep_1') {
            $redirect = "../../index.php?page=laporan_saya";
        } else {
            $redirect = "../../index.php?page=beranda";
        }

        // 1. Validasi session dan data wajib
        if (
            $user_id === '' ||
            $fac_id === '' ||
            $category === '' ||
            $rep_desc === ''
        ) {
            $_SESSION['notice'] = [
                'type' => 'error',
                'message' => 'Data laporan belum lengkap.'
            ];
            header("Location: " . $redirect);
            exit;
        }

        // 2. Validasi kategori
        $kategoriValid = [
            'kerusakan bangunan',
            'kerusakan peralatan',
            'kelistrikan',
            'kebersihan',
            'lainnya'
        ];

        if (!in_array($category, $kategoriValid, true)) {
            $_SESSION['notice'] = [
                'type' => 'error',
                'message' => 'Kategori laporan tidak valid.'
            ];
            header("Location: " . $redirect);
            exit;
        }

        // 3. Validasi deskripsi
        if (mb_strlen($rep_desc) > 1000) {
            $_SESSION['notice'] = [
                'type' => 'error',
                'message' => 'Deskripsi laporan maksimal 1000 karakter.'
            ];
            header("Location: " . $redirect);
            exit;
        }

        // 4. Validasi fasilitas
        $fac = $this->facModel->getFacilityById($fac_id);

        if (!$fac) {
            $_SESSION['notice'] = [
                'type' => 'error',
                'message' => 'Fasilitas tidak ditemukan.'
            ];
            header("Location: " . $redirect);
            exit;
        }

        if ($fac['fac_status'] !== 'aktif') {
            $_SESSION['notice'] = [
                'type' => 'error',
                'message' => 'Fasilitas sedang tidak dapat digunakan untuk membuat laporan.'
            ];
            header("Location: " . $redirect);
            exit;
        }

        // 5. Persiapan upload foto (opsional)
        $folder = __DIR__ . '/../../../storage/report_img';
        $filePath = null;
        $extension = null;
        $file = null;

        // Validasi hanya dilakukan jika pengguna memilih foto
        if (
            isset($_FILES['rep_img']) &&
            $_FILES['rep_img']['error'] !== UPLOAD_ERR_NO_FILE
        ) {
            $file = $_FILES['rep_img'];

            // Periksa error upload
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $message = in_array(
                    $file['error'],
                    [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE],
                    true
                )
                    ? 'Ukuran foto maksimal 2 MB.'
                    : 'Foto gagal diunggah.';

                $_SESSION['notice'] = [
                    'type' => 'error',
                    'message' => $message
                ];
                header("Location: " . $redirect);
                exit;
            }

            // Validasi ukuran maksimal 2 MB
            $maxSize = 2 * 1024 * 1024;

            if ($file['size'] > $maxSize) {
                $_SESSION['notice'] = [
                    'type' => 'error',
                    'message' => 'Ukuran foto maksimal 2 MB.'
                ];
                header("Location: " . $redirect);
                exit;
            }

            // Pastikan file berasal dari upload HTTP
            if (!is_uploaded_file($file['tmp_name'])) {
                $_SESSION['notice'] = [
                    'type' => 'error',
                    'message' => 'File upload tidak valid.'
                ];
                header("Location: " . $redirect);
                exit;
            }

            // Validasi MIME type berdasarkan isi file
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);

            $allowedTypes = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp'
            ];

            if (!isset($allowedTypes[$mimeType])) {
                $_SESSION['notice'] = [
                    'type' => 'error',
                    'message' => 'Foto harus berformat JPG, PNG, atau WEBP.'
                ];
                header("Location: " . $redirect);
                exit;
            }

            // Pastikan file benar-benar gambar
            if (@getimagesize($file['tmp_name']) === false) {
                $_SESSION['notice'] = [
                    'type' => 'error',
                    'message' => 'File yang diunggah bukan gambar yang valid.'
                ];
                header("Location: " . $redirect);
                exit;
            }

            // Simpan ekstensi hanya setelah semua validasi lolos
            $extension = $allowedTypes[$mimeType];
        }

        // 6. Buat UUID laporan
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        $hex = bin2hex($data);

        $rep_id = sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );

        // 7. Simpan foto hanya jika pengguna mengunggahnya
        if ($extension !== null) {

            // Buat folder hanya jika diperlukan
            if (!is_dir($folder) && !mkdir($folder, 0755, true)) {
                $_SESSION['notice'] = [
                    'type' => 'error',
                    'message' => 'Folder penyimpanan foto gagal dibuat.'
                ];
                header("Location: " . $redirect);
                exit;
            }

            $fileName = $rep_id . '.' . $extension;
            $filePath = $folder . '/' . $fileName;

            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                $_SESSION['notice'] = [
                    'type' => 'error',
                    'message' => 'Foto laporan gagal disimpan.'
                ];
                header("Location: " . $redirect);
                exit;
            }
        }

        // 8. Simpan laporan ke database
        try {
            $success = $this->repModel->create(
                $rep_id,
                $category,
                $rep_desc,
                $user_id,
                $fac_id
            );
        } catch (Throwable $e) {
            error_log("Gagal menyimpan laporan: " . $e->getMessage());
            $success = false;
        }

        // 9. Jika database gagal, hapus foto yang sudah tersimpan
        if (!$success) {
            if ($filePath !== null && is_file($filePath)) {
                unlink($filePath);
            }

            $_SESSION['notice'] = [
                'type' => 'error',
                'message' => 'Laporan gagal disimpan.'
            ];
            header("Location: " . $redirect);
            exit;
        }

        // 10. Berhasil
        $_SESSION['notice'] = [
            'type' => 'success',
            'message' => 'Laporan berhasil diajukan.'
        ];

        header("Location: " . $redirect);
        exit;
    }



    
    public function form_rep_1() {

        $limit = 5;

        $page = isset($_GET['haltab']) ? (int)$_GET['haltab'] : 1;
        $page = max(1, $page);

        $offset = ($page - 1) * $limit;

        $tipe_fac = $_GET['tipe_fac'] ?? '';
        $lokasi_fac = $_GET['lokasi_fac'] ?? '';
        $kapasitas_fac = $_GET['kapasitas_fac'] ?? '';

        $facilities = $this->facModel->getActiveFac_limit_filtered(
            $limit,
            $offset,
            $tipe_fac,
            $lokasi_fac,
            $kapasitas_fac
        );

        $totalData = $this->facModel->countActiveFac_filtered(
            $tipe_fac,
            $lokasi_fac,
            $kapasitas_fac
        );

        $totalPages = (int) ceil($totalData / $limit);

        require_once __DIR__ . '/../../../views/pengguna/form_rep_1.php';
    }

}