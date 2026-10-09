<body id="pengguna_body">
    <div id="dashboard_header">
        <span id="header_logo" onclick="toggleSidebar()">★</span>
        <?php if ($_GET['from'] === 'form_rep_1') : ?>
            <span id="header_title">Laporan Saya</span>
        <?php elseif ($_GET['from'] === 'detail_fasilitas') : ?>
            <span id="header_title">Beranda</span>
        <?php endif; ?>
        <button id="logout_btn">LogOut</button>
    </div>

    <div id="dashboard_layout">
        <div id="sidebar" class="hidden">
            <a class="nav_item active" href="../../index.php?page=beranda"><span class="nav_icon">🏠</span>Beranda</a>
            <a class="nav_item" href="../../index.php?page=reservasi_saya"><span class="nav_icon">🚪</span>Reservasi Saya</a>
            <!-- <a class="nav_item" href="../../index.php?page=laporan_saya"><span class="nav_icon">📜</span>Laporan saya</a> -->
        </div>

        <div class="main_content">
            <div id = "detail_fac">
                <h2> Detail Fasilitas </h2>
                <div> fac_id : <?=htmlspecialchars($fac['fac_id']);?> </div>
                <div> Nama : <?=htmlspecialchars($fac['fac_name']);?> </div>
                <div> Deskripsi : <?=htmlspecialchars($fac['fac_desc']);?> </div>
                <div> Tipe : <?=htmlspecialchars($fac['type']);?> </div>
                <div> Lokasi : <?=htmlspecialchars($fac['location']);?> </div>
                <div> Kapasitas : <?=htmlspecialchars($fac['capacity']);?> </div>
                <div> Status : <?=htmlspecialchars($fac['fac_status']);?> </div>
            </div>

            <div id = "form_rep_entry">
                <h2>Buat Laporan</h2>

                <form id="form_rsv_2" method="post" action="../../index.php">

                    <input type="hidden" name="action" value="ajukan_reservasi">
                    <input type="hidden" name="fac_id" value="<?= htmlspecialchars($fac['fac_id']); ?>">
                    <input type="hidden" name="from" value="<?= htmlspecialchars($_GET['from'] ?? ''); ?>">

            </div>

        </div>
    </div>
</body>
</html>
<link rel="stylesheet" href="../../public/css/pengguna/pengguna_style.css">
<script src="../../public/js/pengguna/pengguna.js"></script>