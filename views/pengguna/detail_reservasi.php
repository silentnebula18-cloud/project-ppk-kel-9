<body id="pengguna_body">
    
    <div id="dashboard_header">
        <span id="header_logo" onclick="toggleSidebar()">★</span>
        <span id="header_title">Reservasi Saya</span>
        <button id="logout_btn">LogOut</button>
    </div>

    <div id="dashboard_layout">
        <div id="sidebar" class="hidden">
            <a class="nav_item" href="../../index.php?page=beranda"><span class="nav_icon">🏠</span>Beranda</a>
            <a class="nav_item active" href="../../index.php?page=reservasi_saya"><span class="nav_icon">🚪</span>Reservasi Saya</a>
            <a class="nav_item" href="../../index.php?page=laporan_saya"><span class="nav_icon">📜</span>Laporan saya</a>
        </div>

        <div class="main_content" style="display: flex; flex-direction: column; gap: 20px;">
        
            <a class = "back_href" href="../../index.php?page=reservasi_saya">⬅️</a>
            

            <!-- DETAIL FASILITAS -->    
            <div id="dtl_rsv_detailFasilitas">
                <h2> Detail Fasilitas </h2>
                <div> fac_id : <?=htmlspecialchars($fasilitas['fac_id']);?> </div>
                <div> Nama : <?=htmlspecialchars($fasilitas['fac_name']);?> </div>
                <div> Deskripsi : <?=htmlspecialchars($fasilitas['fac_desc']);?> </div>
                <div> Tipe : <?=htmlspecialchars($fasilitas['type']);?> </div>
                <div> Lokasi : <?=htmlspecialchars($fasilitas['location']);?> </div>
                <div> Kapasitas : <?=htmlspecialchars($fasilitas['capacity']);?> </div>
                <div> Status : <?=htmlspecialchars($fasilitas['fac_status']);?> </div>
            </div>
            

            <!-- DETAIL RESERVASI -->
            <div id="dtl_rsv_detailReservasi">
                <h2> Detail Reservasi </h2>
                <div> rsv_id : <?=htmlspecialchars($reservasi['rsv_id']);?> </div>
                <div> Tanggal : <?=htmlspecialchars($reservasi['rsv_date']);?> </div>
                <div> Waktu Mulai : <?=htmlspecialchars($reservasi['start_time']);?> </div>
                <div> Waktu Selesai : <?=htmlspecialchars($reservasi['end_time']);?> </div>
                <div> Tujuan : <?=htmlspecialchars($reservasi['purpose']);?> </div>
                <div> Tanggal Buat Reservasi : <?=htmlspecialchars($reservasi['created_at']);?> </div>
                <?php if ($reservasi['rsv_status'] === 'dibatalkan') : ?>
                    <div> Dibatalkan pada : <?=htmlspecialchars($reservasi['cancelled_at']);?> </div>
                <?php elseif ($reservasi['rsv_status'] === 'ditolak') : ?>
                    <div> Ditolak pada : <?=htmlspecialchars($reservasi['cancelled_at']);?> </div>
                    <div> Alasan Ditolak : <?=htmlspecialchars($reservasi['cancel_reason']);?> </div>
                <?php endif; ?>
                <div> Status : <?=htmlspecialchars($reservasi['rsv_status']);?> </div>
            </div>

           <!-- TOMBOL PEMBATALAN -->
            <div id="dtl_rsv_tombolBatal">

                <?php
                    $batas_pembatalan = strtotime(
                        $reservasi['rsv_date'] . ' ' . $reservasi['start_time']
                    ) - (24 * 60 * 60);

                    $sekarang = time();

                    $bisa_batal =
                        ($reservasi['rsv_status'] === 'proses pengajuan'
                        || $reservasi['rsv_status'] === 'disetujui')
                        && $sekarang < $batas_pembatalan;
                ?>

                <?php if ($bisa_batal): ?>

                    <button
                        type="button"
                        id="btn_batal_reservasi"
                        data-rsv-id="<?= htmlspecialchars($reservasi['rsv_id']); ?>"
                        data-status="<?= htmlspecialchars($reservasi['rsv_status']); ?>"
                        data-rsv-date="<?= htmlspecialchars($reservasi['rsv_date']); ?>"
                        data-start-time="<?= htmlspecialchars($reservasi['start_time']); ?>"
                    >
                        Batalkan Reservasi
                    </button>

                <?php endif; ?>

            </div>
        </div>

        <!-- POPUP KONFIRMASI -->
        <div id="dtl_rsv_popup_konfirmasi" class="hidden">
            <div class="popup_content">
                <h3>Batalkan Reservasi?</h3>

                <p>
                    Apakah Anda yakin ingin membatalkan reservasi ini?
                </p>

                <button type="button" id="btn_batal_konfirmasi">
                    Ya, Batalkan
                </button>

                <button type="button" id="btn_batal_batal">
                    Tidak
                </button>
            </div>
        </div>

    </div>
</body>
</html>

<link rel="stylesheet" href="../../public/css/pengguna/pengguna_style.css">
<script src="../../public/js/pengguna/pengguna.js"></script>
<script src="../../public/js/pengguna/detail_reservasi.js"></script>