<body id="pengguna_body">
    
    <div id="dashboard_header">
        <span id="header_logo" onclick="toggleSidebar()">★</span>
        <span id="header_title">Laporan Saya</span>
        <button id="logout_btn">LogOut</button>
    </div>

    <div id="dashboard_layout">
        <div id="sidebar" class="hidden">
            <a class="nav_item" href="../../index.php?page=beranda"><span class="nav_icon">🏠</span>Beranda</a>
            <a class="nav_item" href="../../index.php?page=reservasi_saya"><span class="nav_icon">🚪</span>Reservasi Saya</a>
            <a class="nav_item active" href="../../index.php?page=laporan_saya"><span class="nav_icon">📜</span>Laporan saya</a>
        </div>

        <div class="main_content" style="display: flex; flex-direction: column; gap: 20px;">
        
            <a class = "back_href" href="../../index.php?page=laporan_saya">⬅️</a>
            

            <!-- DETAIL FASILITAS -->    
            <div id="dtl_rep_detailFasilitas">
                <h2> Detail Fasilitas </h2>
                <div> fac_id : <?=htmlspecialchars($fasilitas['fac_id']);?> </div>
                <div> Nama : <?=htmlspecialchars($fasilitas['fac_name']);?> </div>
                <div> Deskripsi : <?=htmlspecialchars($fasilitas['fac_desc']);?> </div>
                <div> Tipe : <?=htmlspecialchars($fasilitas['type']);?> </div>
                <div> Lokasi : <?=htmlspecialchars($fasilitas['location']);?> </div>
                <div> Kapasitas : <?=htmlspecialchars($fasilitas['capacity']);?> </div>
                <div> Status : <?=htmlspecialchars($fasilitas['fac_status']);?> </div>
            </div>
            
            <!-- DETAIL LAPORAN -->
            <div id="dtl_rep_detailLaporan">
                <h2>Detail Laporan</h2>

                <div>ID Laporan:
                    <?= htmlspecialchars($laporan['rep_id']); ?>
                </div>

                <div>Waktu Pelaporan:
                    <?= htmlspecialchars($laporan['reported_at']); ?>
                </div>

                <div>Kategori:
                    <?= htmlspecialchars($laporan['category']); ?>
                </div>

                <div>Deskripsi:
                    <?= nl2br(htmlspecialchars($laporan['rep_desc'])); ?>
                </div>

                <div>Status:
                    <?= htmlspecialchars($laporan['rep_status']); ?>
                </div>

                <?php if ($laporan['rep_status'] === 'selesai'): ?>
                    <div>Catatan Penyelesaian:
                        <?= nl2br(htmlspecialchars($laporan['resolution_note'] ?? '')); ?>
                    </div>

                    <div>Waktu Penutupan:
                        <?= htmlspecialchars($laporan['closed_at'] ?? ''); ?>
                    </div>
                <?php endif; ?>

                <div>
                    <h3>Foto Laporan</h3>

                    <?php if ($foto !== null): ?>
                        <img
                            src="<?= htmlspecialchars($foto); ?>"
                            alt="Foto laporan"
                            style="max-width: 100%; max-height: 400px;"
                        >
                    <?php else: ?>
                        <p>Foto laporan tidak ditemukan.</p>
                    <?php endif; ?>
                </div>
            </div>

    </div>
</body>
</html>

<link rel="stylesheet" href="../../public/css/pengguna/pengguna_style.css">
<script src="../../public/js/pengguna/pengguna.js"></script>