<?php

require_once __DIR__ . '/../../app/helpers/validate_session.php';
check_session_valid('admin');
require_once __DIR__ . "/../../app/models/facility_model.php";

$id = $_GET['id'] ?? '';
$facility = $id !== '' ? getFacilityById($conn, $id) : null;

if (!$facility) {
    die("Fasilitas tidak ditemukan.");
}

$typeIcons = [
    'Ruang Kelas' => '🏫',
    'Aula' => '🎤',
    'Laboratorium'=> '🔬',
    'Alat' => '🔧',
    'Lapangan' => '⚽',
];
$adaFoto = adaFoto($facility['fac_photo'] ?? null);
$icon = $typeIcons[$facility['type']] ?? '🏢';
$statusSlug = strtolower(str_replace(' ', '-', $facility['fac_status']));
?>
<body id="admin_body">
    <div id="dashboard_header">
        <button id="header_logo" type="button" onclick="toggleSidebar()" aria-label="Buka atau tutup menu"><span></span><span></span><span></span></button>
        <span id="header_brand">Campu<em>Space</em></span>
        <span id="header_title">Dashboard Admin</span>
        <a href="../../app/controllers/logout_controller.php"><button id="logout_btn">LogOut</button></a>
    </div>

    <div id="dashboard_layout">
        <div id="sidebar" class="hidden">
            <a class="nav_item" href="admin_beranda.php"><span class="nav_icon">🏠</span>Beranda</a>
            <a class="nav_item" href="kelola_akun.php"><span class="nav_icon">👤</span>Kelola akun</a>
            <a class="nav_item active" href="kelola_fasilitas.php"><span class="nav_icon">🏢</span>Kelola fasilitas</a>
        </div>

        <div id="main_content">
            <a id="back_link" href="kelola_fasilitas.php">Kembali ke daftar</a>

            <div id="content_top_bar">
                <h3><?= htmlspecialchars($facility['fac_name']) ?><br>
                    <span id="detail_id"><?= htmlspecialchars($facility['fac_id']) ?></span>
                </h3>
                <a href="kelola_fasilitas_baru.php?id=<?= urlencode($facility['fac_id']) ?>">
                    <button>Edit</button>
                </a>
            </div>

            <div id="detail_wrap">
                <div id="detail_photo">
                    <?php if ($adaFoto): ?>
                    <img class="foto_detail" src="<?= urlFoto($facility['fac_photo']) ?>"
                         alt="Foto <?= htmlspecialchars($facility['fac_name']) ?>">
                    <?php else: ?>
                    <div class="foto_kosong">
                        <span class="foto_kosong_icon"><?= $icon ?></span>
                        <span>Belum ada foto</span>
                        <a href="kelola_fasilitas_baru.php?id=<?= urlencode($facility['fac_id']) ?>">Tambah foto</a>
                    </div>
                    <?php endif; ?>
                    <span class="status_badge status_<?= $statusSlug ?> detail_status"><?= htmlspecialchars($facility['fac_status']) ?></span>
                </div>

                <div id="detail_info">
                    <div class="spec_grid">
                        <div class="spec_item">
                            <span class="spec_icon"><?= $icon ?></span>
                            <span class="spec_label">Tipe</span>
                            <span class="spec_value"><?= htmlspecialchars($facility['type']) ?></span>
                        </div>
                        <div class="spec_item">
                            <span class="spec_icon">📍</span>
                            <span class="spec_label">Lokasi</span>
                            <span class="spec_value"><?= htmlspecialchars($facility['location']) ?></span>
                        </div>
                        <div class="spec_item">
                            <span class="spec_icon">👥</span>
                            <span class="spec_label">Kapasitas</span>
                            <span class="spec_value"><?= htmlspecialchars($facility['capacity']) ?> orang</span>
                        </div>
                    </div>

                    <div class="desc_box">
                        <h4>Deskripsi</h4>
                        <p><?= nl2br(htmlspecialchars($facility['fac_desc'])) ?></p>
                    </div>
                </div>
            </div>

            <?php if (($_GET['error'] ?? '') === 'has_history'): ?>
            <p style="color:var(--danger,red); font-weight:bold;">
                Fasilitas ini tidak bisa dihapus karena masih mempunyai riwayat reservasi atau laporan.
                Coba dinonaktifkan saja jika tidak ingin dipakai lagi.
            </p>
            <?php endif; ?>

            <div class="detail_actions">
            <form id="delete_form" method="post" action="../../app/controllers/admin_facility_controller.php"
                  onsubmit="return confirm('Yakin mau hapus fasilitas ini? Data akan hilang permanen.');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="fac_id" value="<?= htmlspecialchars($facility['fac_id']) ?>">
                <button id="delete_btn" type="submit">Hapus</button>
            </form>

            <?php if ($facility['fac_status'] === 'Nonaktif'): ?>
            <form method="post" action="../../app/controllers/admin_facility_controller.php">
                <input type="hidden" name="action" value="activate">
                <input type="hidden" name="fac_id" value="<?= htmlspecialchars($facility['fac_id']) ?>">
                <button type="submit">Aktifkan Kembali</button>
            </form>
            <?php else: ?>
            <form method="post" action="../../app/controllers/admin_facility_controller.php"
                  onsubmit="return confirm('Yakin mau nonaktifkan fasilitas ini? Fasilitas tidak akan bisa direservasi selama nonaktif.');">
                <input type="hidden" name="action" value="deactivate">
                <input type="hidden" name="fac_id" value="<?= htmlspecialchars($facility['fac_id']) ?>">
                <button type="submit">Nonaktifkan</button>
            </form>
            <?php endif; ?>
            </div>
        </div>
    </div>
</body>

<link rel="stylesheet" href="../../public/css/admin/admin_style.css?v=<?= filemtime(__DIR__ . '/../../public/css/admin/admin_style.css') ?>">
<script src="../../public/js/admin/sidebar.js"></script>