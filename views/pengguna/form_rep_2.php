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
            <?php if ($_GET['from'] === 'form_rep_1') : ?>
                <a class="nav_item" href="../../index.php?page=beranda"><span class="nav_icon">🏠</span>Beranda</a>
                <a class="nav_item" href="../../index.php?page=reservasi_saya"><span class="nav_icon">🚪</span>Reservasi Saya</a>
                <a class="nav_item active" href="../../index.php?page=laporan_saya"><span class="nav_icon">📜</span>Laporan saya</a>
            <?php elseif ($_GET['from'] === 'detail_fasilitas') : ?>
                <a class="nav_item active" href="../../index.php?page=beranda"><span class="nav_icon">🏠</span>Beranda</a>
                <a class="nav_item" href="../../index.php?page=reservasi_saya"><span class="nav_icon">🚪</span>Reservasi Saya</a>
                <a class="nav_item" href="../../index.php?page=laporan_saya"><span class="nav_icon">📜</span>Laporan saya</a>
            <?php endif; ?>
        </div>

        <div class="main_content">
            <?php if ($_GET['from'] === 'form_rep_1') : ?>
                <a class = "back_href" href="../../index.php?page=form_rep_1">⬅️</a>
            <?php elseif ($_GET['from'] === 'detail_fasilitas') : ?>
                <a class = "back_href" href="../../index.php?page=detail_fasilitas&id=<?= urlencode($fac['fac_id']); ?>">⬅️</a>
            <?php endif; ?>

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
                
                <form id="form_rep_2" method="post" action="../../index.php" enctype="multipart/form-data">

                    <input type="hidden" name="action" value="ajukan_laporan">

                    <input type="hidden" name="fac_id" value="<?= htmlspecialchars($fac['fac_id']); ?>">

                    <input type="hidden" name="from" value="<?= htmlspecialchars($_GET['from'] ?? ''); ?>">

                    <!-- Kategori Laporan -->
                    <div class="form_group">
                        <label for="category">Kategori Laporan</label>
                        <select id="category" name="category" required>
                            <option value="">Pilih kategori</option>
                            <option value="kerusakan bangunan">Kerusakan Bangunan</option>
                            <option value="kerusakan peralatan">Kerusakan Peralatan</option>
                            <option value="kelistrikan">Kelistrikan</option>
                            <option value="kebersihan">Kebersihan</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>

                    <!-- Deskripsi Laporan -->
                    <div class="form_group">
                        <label for="rep_desc">Deskripsi Laporan</label>
                        <textarea id="rep_desc"
                                name="rep_desc"
                                maxlength="1000"
                                required></textarea>
                    </div>

                    <!-- Foto Laporan -->
                    <div class="form_group">
                        <label for="rep_img">Foto Laporan</label>
                        <input type="file"
                            id="rep_img"
                            name="rep_img"
                            accept="image/jpeg,image/png,image/webp"
                            required>
                        <small>Unggah satu foto berformat JPG, PNG, atau WEBP.</small>
                    </div>

                    <button type="submit">Kirim Laporan</button>

                </form>
            </div>

        </div>
    </div>
</body>
</html>
<link rel="stylesheet" href="../../public/css/pengguna/pengguna_style.css">
<script src="../../public/js/pengguna/pengguna.js"></script>
<script src="../../public/js/pengguna/form_rep_2.js"></script>