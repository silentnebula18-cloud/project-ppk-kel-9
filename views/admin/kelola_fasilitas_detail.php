<body id="admin_body">
    <div id="dashboard_header">
        <span id="header_logo" onclick="toggleSidebar()">★</span>
        <span id="header_title">Dashboard Admin</span>
        <button id="logout_btn">LogOut</button>
    </div>

    <div id="dashboard_layout">
        <div id="sidebar" class="hidden">
            <a class="nav_item" href="../../index.php?page=beranda"><span class="nav_icon">🏠</span>Beranda</a>
            <a class="nav_item" href="../../index.php?page=kelola_akun"><span class="nav_icon">👤</span>Kelola akun</a>
            <a class="nav_item active" href="../../index.php?page=kelola_fasilitas"><span class="nav_icon">🏢</span>Kelola fasilitas</a>
        </div>

        <div id="main_content">
            <div id="content_top_bar">
                <h3><?= htmlspecialchars($facility['fac_name']) ?><br>
                    <span id="detail_id"><?= htmlspecialchars($facility['fac_id']) ?></span>
                </h3>
                <a href="../../index.php?page=kelola_fasilitas_baru&id=<?= urlencode($facility['fac_id']) ?>">
                    <button>Edit</button>
                </a>
            </div>

            <p>Tipe: <?= htmlspecialchars($facility['type']) ?></p>
            <p>Lokasi: <?= htmlspecialchars($facility['location']) ?></p>
            <p>Kapasitas: <?= htmlspecialchars($facility['capacity']) ?></p>
            <p>Status: <?= htmlspecialchars($facility['fac_status']) ?></p>
            <p>Deskripsi: <?= nl2br(htmlspecialchars($facility['fac_desc'])) ?></p>

            <form id="delete_form" method="post" action="../../index.php"
                  onsubmit="return confirm('Yakin mau hapus fasilitas ini? Data akan hilang permanen.');">
                <input type="hidden" name="action" value="deleteFac">
                <input type="hidden" name="fac_id" value="<?= htmlspecialchars($facility['fac_id']) ?>">
                <button id="delete_btn" type="submit">Hapus</button>
            </form>

            <?php if ($facility['fac_status'] === 'Nonaktif'): ?>
            <form method="post" action="../../index.php">
                <input type="hidden" name="action" value="activate">
                <input type="hidden" name="fac_id" value="<?= htmlspecialchars($facility['fac_id']) ?>">
                <button type="submit">Aktifkan Kembali</button>
            </form>
            <?php else: ?>
            <form method="post" action="../../index.php"
                  onsubmit="return confirm('Yakin mau nonaktifkan fasilitas ini? Fasilitas gak akan bisa direservasi selama nonaktif.');">
                <input type="hidden" name="action" value="deactivate">
                <input type="hidden" name="fac_id" value="<?= htmlspecialchars($facility['fac_id']) ?>">
                <button type="submit">Nonaktifkan</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</body>

<link rel="stylesheet" href="../../public/css/admin/admin_style.css">
<script src="../../public/js/admin/sidebar.js"></script>