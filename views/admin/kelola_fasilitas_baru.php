<?php
require_once __DIR__ . "/../../app/models/facility_model.php";

$facility = null;
$isEdit = isset($_GET['id']) && $_GET['id'] !== '';

if ($isEdit) {
    $facility = getFacilityById($conn, $_GET['id']);
    if (!$facility) {
        die("Fasilitas tidak ditemukan.");
    }
}

$types = ["ruang kelas", "aula", "laboratorium", "alat", "lapangan"];
$statuses = ["Aktif", "Dalam Perbaikan", "Nonaktif"];
?>
<body id="admin_body">
    <div id="dashboard_header">
        <span id="header_logo" onclick="toggleSidebar()">★</span>
        <span id="header_title">Dashboard Admin</span>
        <button id="logout_btn">LogOut</button>
    </div>

    <div id="dashboard_layout">
        <div id="sidebar" class="hidden">
            <a class="nav_item" href="admin_beranda.php"><span class="nav_icon">🏠</span>Beranda</a>
            <a class="nav_item" href="kelola_akun.php"><span class="nav_icon">👤</span>Kelola akun</a>
            <a class="nav_item active" href="kelola_fasilitas.php"><span class="nav_icon">🏢</span>Kelola fasilitas</a>
        </div>

        <div id="main_content">
            <h3><?= $isEdit ? "Edit Data Fasilitas" : "Masuk Data Fasilitas Baru" ?></h3>
            <?php if (!$isEdit): ?>
            <p id="form_note">Catatan: Saat data fasilitas berhasil dimasukan, status langsung aktif (bisa di reservasi)</p>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
            <p style="color:red">Data gagal disimpan: semua field wajib diisi dengan benar.</p>
            <?php endif; ?>

            <form method="post" action="../../app/controllers/facility_controller.php">
                <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'create' ?>">
                <?php if ($isEdit): ?>
                <input type="hidden" name="fac_id" value="<?= htmlspecialchars($facility['fac_id']) ?>">
                <?php endif; ?>

                <div class="form_row">
                    <label>Nama Fasilitas:</label>
                    <input type="text" name="fac_name" required
                           value="<?= htmlspecialchars($facility['fac_name'] ?? '') ?>">
                </div>

                <div class="form_row">
                    <label>Tipe:</label>
                    <select name="type" required>
                        <option value="">-- Pilih Tipe --</option>
                        <?php foreach ($types as $t): ?>
                        <option value="<?= $t ?>" <?= (isset($facility['type']) && $facility['type'] === $t) ? 'selected' : '' ?>>
                            <?= ucfirst($t) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form_row">
                    <label>Lokasi:</label>
                    <input type="text" name="location" required
                           value="<?= htmlspecialchars($facility['location'] ?? '') ?>">
                </div>

                <div class="form_row">
                    <label>Kapasitas:</label>
                    <input type="number" name="capacity" min="1" required
                           value="<?= htmlspecialchars($facility['capacity'] ?? '') ?>">
                </div>

                <div class="form_row">
                    <label>Deskripsi:</label>
                    <textarea name="fac_desc" required><?= htmlspecialchars($facility['fac_desc'] ?? '') ?></textarea>
                </div>

                <?php if ($isEdit): ?>
                <div class="form_row">
                    <label>Status:</label>
                    <select name="fac_status" required>
                        <?php foreach ($statuses as $s): ?>
                        <option value="<?= $s ?>" <?= ($facility['fac_status'] === $s) ? 'selected' : '' ?>>
                            <?= ucfirst($s) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <button type="submit"><?= $isEdit ? "Simpan Perubahan" : "Create Data" ?></button>
            </form>
        </div>
    </div>
</body>

<link rel="stylesheet" href="../../public/css/admin/admin_style.css">
<script src="../../public/js/admin/sidebar.js"></script>