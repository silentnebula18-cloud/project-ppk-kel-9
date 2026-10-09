<?php
if (!function_exists('e')) {
    function e($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('statusSlug')) {
    function statusSlug($status) { return str_replace(' ', '-', strtolower(trim((string) $status))); }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Petugas - Fasilitas</title>
    <link rel="stylesheet" href="../../public/css/petugas/dashboard_style.css?v=<?= @filemtime(__DIR__ . '/../../public/css/petugas/dashboard_style.css') ?: 1 ?>">
</head>
<body id="petugas_body">

    <div class="toast_container">
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="toast toast_success" role="status">
                <span><?= e($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></span>
                <button type="button" class="close_btn" aria-label="Tutup" onclick="this.parentElement.remove()">&times;</button>
            </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="toast toast_error" role="alert">
                <span><?= e($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></span>
                <button type="button" class="close_btn" aria-label="Tutup" onclick="this.parentElement.remove()">&times;</button>
            </div>
        <?php endif; ?>
    </div>

    <div id="dashboard_header">
        <button type="button" id="header_logo" aria-label="Tampilkan atau sembunyikan menu" onclick="toggleSidebar()">★</button>
        <span id="header_brand">Campu<em>Space</em></span>
        <span id="header_title">Dashboard Petugas</span>
        <a href="../../app/controllers/logout_controller.php"><button type="button" id="logout_btn">Keluar</button></a>
    </div>

    <div id="dashboard_layout">
        <nav id="sidebar" aria-label="Menu utama">
            <a class="nav_item" href="index.php?page=dashboard"><span class="nav_icon">🏠</span>Beranda</a>
            <a class="nav_item" href="index.php?page=reports"><span class="nav_icon">🛠️</span>Laporan</a>
            <a class="nav_item active" href="index.php?page=facilities"><span class="nav_icon">🏢</span>Fasilitas</a>
        </nav>

        <main id="main_content">
            <div class="page_head">
                <h3>Daftar fasilitas</h3>
                <p>Ubah status fasilitas lewat menu pilihan di tiap kartu.</p>
            </div>

            <?php if (empty($facilities)): ?>
                <p class="empty_note">Belum ada data fasilitas.</p>
            <?php else: ?>
                <div class="facility_grid">
                    <?php foreach ($facilities as $fac): ?>
                        <?php
                            $status = $fac['fac_status'];
                            $isNonaktif = ($status === 'Nonaktif');
                            $cardClass = $isNonaktif ? ' is_nonaktif' : ($status === 'Dalam perbaikan' ? ' is_perbaikan' : '');
                        ?>
                        <div class="facility_card<?= $cardClass ?>">
                            <h4 class="facility_title"><?= e($fac['fac_name']) ?></h4>

                            <?php if ($isNonaktif): ?>
                                <span class="status_badge status_nonaktif">Nonaktif</span>
                                <p class="facility_note">Status hanya bisa diubah oleh admin.</p>
                            <?php else: ?>
                                <form action="index.php?page=facilities&action=update_status" method="POST">
                                    <input type="hidden" name="fac_id" value="<?= e($fac['fac_id']) ?>">
                                    <select name="fac_status" aria-label="Status <?= e($fac['fac_name']) ?>" onchange="this.form.submit()">
                                        <option value="Aktif" <?= $status === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                                        <option value="Dalam perbaikan" <?= $status === 'Dalam perbaikan' ? 'selected' : '' ?>>Dalam perbaikan</option>
                                    </select>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        function toggleSidebar() { document.getElementById('sidebar').classList.toggle('hidden'); }
        document.querySelectorAll('.toast').forEach(t => setTimeout(() => t.remove(), 5000));
    </script>
</body>
</html>