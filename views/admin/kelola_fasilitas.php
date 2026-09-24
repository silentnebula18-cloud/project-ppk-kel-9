<?php
require_once __DIR__ . "/../../app/models/facility_model.php";

$facilities = getAllFacilities($conn);
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
            <div id="content_top_bar">
                <h3>Daftar Fasilitas</h3>
                <a href="kelola_fasilitas_baru.php"><button>Add Fasilitas</button></a>
            </div>

            <input type="text" placeholder="Cari nama fasilitas" oninput="filterTable(this, 'fasilitas_table')">

            <table id="fasilitas_table">
                <tr>
                    <th>id_Fac</th>
                    <th>Nama</th>
                    <th>...</th>
                    <th>Status</th>
                    <th>Frekuensi Reservasi</th>
                    <th>Frekuensi Kerusakan</th>
                </tr>
                <?php if (empty($facilities)): ?>
                <tr>
                    <td colspan="6">Belum ada data fasilitas.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($facilities as $fac): ?>
                    <tr>
                        <td><a href="kelola_fasilitas_detail.php?id=<?= htmlspecialchars($fac['fac_id']) ?>">
                            <?= htmlspecialchars($fac['fac_id']) ?>
                        </a></td>
                        <td><?= htmlspecialchars($fac['fac_name']) ?></td>
                        <td><?= htmlspecialchars($fac['type']) ?> — <?= htmlspecialchars($fac['location']) ?></td>
                        <td><?= htmlspecialchars($fac['fac_status']) ?></td>
                        <td><?= (int) $fac['rsv_count'] ?></td>
                        <td><?= (int) $fac['report_count'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </table>

            <button onclick="downloadRekap()">Download Rekap</button>
        </div>
    </div>
</body>

<link rel="stylesheet" href="../../public/css/admin/admin_style.css">
<script src="../../public/js/admin/sidebar.js"></script>
<script src="../../public/js/admin/table_actions.js"></script>
<script src="../../public/js/admin/form_actions.js"></script>