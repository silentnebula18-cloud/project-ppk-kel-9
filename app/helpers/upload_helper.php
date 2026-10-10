<?php
const FOTO_MAKS_BYTE = 2 * 1024 * 1024; 
const FOTO_MAKS_SISI = 1280;            
const FOTO_KUALITAS  = 82;              

/**
 * Ubah file gambar (JPG/PNG/WebP) menjadi WebP
 * @return true|string true jika berhasil, atau teks pesan error
 */
function konversiKeWebp($sumber, $tujuan)
{
    if (!function_exists('imagewebp')) {
        return 'Server belum mendukung WebP (aktifkan extension=gd di php.ini).';
    }

    $info = @getimagesize($sumber);
    if ($info === false) {
        return 'Format foto harus JPG, PNG, atau WebP.';
    }

    switch ($info[2]) {
        case IMAGETYPE_JPEG:
            $img = @imagecreatefromjpeg($sumber);
            break;
        case IMAGETYPE_PNG:
            $img = @imagecreatefrompng($sumber);
            break;
        case IMAGETYPE_WEBP:
            $img = @imagecreatefromwebp($sumber);
            break;
        default:
            return 'Format foto harus JPG, PNG, atau WebP.';
    }
    if (!$img) {
        return 'Foto tidak bisa dibaca. Coba foto lain.';
    }
    imagepalettetotruecolor($img);

    if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
        $exif  = @exif_read_data($sumber);
        $sudut = [3 => 180, 6 => -90, 8 => 90][$exif['Orientation'] ?? 1] ?? 0;
        if ($sudut !== 0) {
            $putar = imagerotate($img, $sudut, 0);
            if ($putar) {
                imagedestroy($img);
                $img = $putar;
            }
        }
    }

    // Perkecil jika file terlalu besar, supaya file ringan
    $w = imagesx($img);
    $h = imagesy($img);
    $skala = min(1, FOTO_MAKS_SISI / max($w, $h));
    if ($skala < 1) {
        $baru = imagecreatetruecolor((int) round($w * $skala), (int) round($h * $skala));
        imagealphablending($baru, false);
        imagesavealpha($baru, true);
        imagefill($baru, 0, 0, imagecolorallocatealpha($baru, 0, 0, 0, 127));
        imagecopyresampled($baru, $img, 0, 0, 0, 0, imagesx($baru), imagesy($baru), $w, $h);
        imagedestroy($img);
        $img = $baru;
    }
    imagesavealpha($img, true);

    $sementara = $tujuan . '.tmp';
    $ok = imagewebp($img, $sementara, FOTO_KUALITAS);
    imagedestroy($img);
    if (!$ok || !@rename($sementara, $tujuan)) {
        @unlink($sementara);
        return 'Foto gagal diubah ke WebP.';
    }
    return true;
}

/**
 * Simpan satu foto hasil upload sebagai storage/<subfolder>/<nama>.webp
 * @param array|null $fileisi $_FILES['nama_input'] (boleh null)
 * @param string $subfolder folder di dalam storage/
 * @param string $nama nama file (tanpa ekstensi)
 * @return array ['status' => 'kosong'|'ok'|'error', 'file' => nama file, 'path' => path relatif dari root project, 'pesan' => teks error]
 */
function simpanFotoWebp($file, $subfolder, $nama)
{
    // Tidak ada file yang dipilih (foto bersifat opsional)
    if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['status' => 'kosong'];
    }

    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return ['status' => 'error', 'pesan' => 'Ukuran foto terlalu besar (maksimal 2 MB).'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['status' => 'error', 'pesan' => 'Upload foto gagal (kode ' . $file['error'] . ').'];
    }
    if ($file['size'] > FOTO_MAKS_BYTE) {
        return ['status' => 'error', 'pesan' => 'Ukuran foto terlalu besar (maksimal 2 MB).'];
    }

    // Nama file hanya boleh huruf, angka, dan strip 
    if (!preg_match('/^[A-Za-z0-9\-]+$/', $nama)) {
        return ['status' => 'error', 'pesan' => 'Nama file foto tidak valid.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
        return ['status' => 'error', 'pesan' => 'Format foto harus JPG, PNG, atau WebP.'];
    }

    $folder = __DIR__ . '/../../storage/' . $subfolder;
    if (!is_dir($folder) && !mkdir($folder, 0775, true)) {
        return ['status' => 'error', 'pesan' => 'Folder penyimpanan foto tidak bisa dibuat.'];
    }

    $hasil = konversiKeWebp($file['tmp_name'], $folder . '/' . $nama . '.webp');
    if ($hasil !== true) {
        return ['status' => 'error', 'pesan' => $hasil];
    }

    return [
        'status' => 'ok',
        'file'   => $nama . '.webp',
        'path'   => 'storage/' . $subfolder . '/' . $nama . '.webp',
    ];
}

/**
 * Hapus file foto dari server (aman jika nama kosong / file tidak ada)
 */
function hapusFoto($nama, $subfolder)
{
    if (!$nama) {
        return;
    }
    $path = __DIR__ . '/../../storage/' . $subfolder . '/' . basename($nama);
    if (is_file($path)) {
        @unlink($path);
    }
}
?>