<head>
    <link rel="stylesheet" href="../../public/css/pengguna/pengguna_style.css">
</head>

<body id="pengguna_body">
    
    <div id="dashboard_header">
        <span id="header_logo" onclick="toggleSidebar()">★</span>
        <span id="header_brand">Campu<em>Space</em></span>
        <span id="header_title">Reservasi Saya</span>
        <button id="logout_btn">LogOut</button>
    </div>

    <div id="dashboard_layout">
        <div id="sidebar" class="hidden">
            <a class="nav_item" href="../../index.php?page=beranda"><span class="nav_icon">🏠</span>Beranda</a>
            <a class="nav_item active" href="../../index.php?page=reservasi_saya"><span class="nav_icon">🚪</span>Reservasi Saya</a>
            <a class="nav_item" href="../../index.php?page=laporan_saya"><span class="nav_icon">📜</span>Laporan saya</a>
        </div>

        <div class="main_content content_stack">
        
            <div class="section_header">
                <a class = "back_href" href="../../index.php?page=reservasi_saya">⬅️</a>
                <div class="content_title">Reservasi <?=htmlspecialchars($reservasi['rsv_id']);?></div>
            </div>

            <!-- DETAIL FASILITAS -->    
            <div id="detail_fac">
                <div id="detail_fac_img">                    
                    <img
                        src="<?= htmlspecialchars($fasilitas['fac_photo']); ?>"
                        alt="Foto <?= htmlspecialchars($fasilitas['fac_name']); ?>"
                    >
                </div>

                <div id="detail_fac_txt">
                    <h3>Detail Fasilitas</h3>

                    <div>fac_id : <?= htmlspecialchars($fasilitas['fac_id']); ?></div>
                    <div>Nama : <?= htmlspecialchars($fasilitas['fac_name']); ?></div>
                    <div>Deskripsi : <?= htmlspecialchars($fasilitas['fac_desc']); ?></div>
                    <div>Tipe : <?= htmlspecialchars($fasilitas['type']); ?></div>
                    <div>Lokasi : <?= htmlspecialchars($fasilitas['location']); ?></div>
                    <div>Kapasitas : <?= htmlspecialchars($fasilitas['capacity']); ?></div>
                    <div>Status : <?= htmlspecialchars($fasilitas['fac_status']); ?></div>
                </div>
            </div>
            

            <!-- DETAIL RESERVASI -->
            <div id="dtl_rsv_detailReservasi">
                <h3>Detail Reservasi</h3>
                <div> ID Reservasi : <?=htmlspecialchars($reservasi['rsv_id']);?> </div>
                <div> Tanggal : <?=htmlspecialchars($reservasi['rsv_date']);?> </div>
                <div> Waktu Mulai : <?=htmlspecialchars($reservasi['start_time']);?> </div>
                <div> Waktu Selesai : <?=htmlspecialchars($reservasi['end_time']);?> </div>
                <div> Tujuan : <?=htmlspecialchars($reservasi['purpose']);?> </div>
                <div> Tanggal Buat Reservasi : <?=htmlspecialchars($reservasi['created_at']);?> </div>
                <?php if ($reservasi['rsv_status'] === 'dibatalkan') : ?>
                    <div> Dibatalkan pada : <?=htmlspecialchars($reservasi['cancelled_at']);?> </div>
                    <?php if($reservasi['cancelled_by'] === $_SESSION['user_id']):?>
                        <div> Dibatalkan oleh : pengguna </div>
                    <?php else: ?>
                        <div> Dibatalkan oleh : petugas </div>
                    <?php endif; ?>
                <?php elseif ($reservasi['rsv_status'] === 'ditolak') : ?>
                    <div> Ditolak pada : <?=htmlspecialchars($reservasi['cancelled_at']);?> </div>
                    <div> Alasan Ditolak : <?=htmlspecialchars($reservasi['cancel_reason']);?> </div>
                <?php elseif ($reservasi['rsv_status'] === 'proses pengajuan' || $reservasi['rsv_status'] === 'disetujui') : ?>
                    <div>Batas pembatalan: <?=date('d-m-Y H:i:s',strtotime($reservasi['rsv_date'] . ' ' . $reservasi['start_time']) - 86400);?></div>
                <?php endif; ?>
                <div> Status : <?= htmlspecialchars(ucwords($reservasi['rsv_status'])) ?></div>
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

                <label for="alasan_batal">Alasan pembatalan</label>
                <textarea
                    id="alasan_batal"
                    maxlength="300"
                    required
                    placeholder="Tuliskan alasan pembatalan..."
                ></textarea>

                <p id="error_alasan_batal" class="hidden">
                    Alasan pembatalan wajib diisi.
                </p>

                <div>
                    <button type="button" id="btn_batal_konfirmasi">
                        Ya, Batalkan
                    </button>

                    <button type="button" id="btn_batal_batal">
                        Tidak
                    </button>
                </div>
                
            </div>
        </div>

    </div>
</body>
</html>

<script src="../../public/js/pengguna/pengguna.js"></script>
<script src="../../public/js/pengguna/detail_reservasi.js"></script>