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
            <!-- <a class="nav_item" href="../../index.php?page=laporan_saya"><span class="nav_icon">📜</span>Laporan saya</a> -->
        </div>

        <div class="main_content" id = "hal_detail_fasilitas">
            <h2> Reservasi Saya </h2>

            <div id="top_rsv_saya" style="display: flex; align-items: center;">
                <div id="filter_rsv_saya" style="display: flex; align-items: center; gap: 10px;">
                    <div id="rsv_tabs">
                        <a href="../../index.php?page=reservasi_saya&tab=semua"
                        class="rsv_tab <?= $tab === 'semua' ? 'active' : ''; ?>">
                            Semua
                        </a>

                        <a href="../../index.php?page=reservasi_saya&tab=proses"
                        class="rsv_tab <?= $tab === 'proses' ? 'active' : ''; ?>">
                            Proses Pengajuan
                        </a>
                        
                        <a href="#" class="rsv_tab">Disetujui</a>
                        <a href="#" class="rsv_tab">Ditolak</a>
                        <a href="#" class="rsv_tab">Dibatalkan</a>
                        <a href="#" class="rsv_tab">Kadaluarsa</a>
                        <a href="#" class="rsv_tab">Riwayat</a>
                    </div>
                </div>

                <div id="btn_buat_rsv" style="flex: 1; display: flex; justify-content: flex-end;">
                    <a href="../../index.php?page=form_rsv_1" class="btn">Buat Reservasi</a>
                </div>
            </div>

           <div id="rsv_content">
                <?php if ($tab === 'semua'): ?>
                    <p class="rsv_description">
                        Berikut semua reservasi terlepas dari statusnya.
                        Pilih salah satu baris untuk melihat detail reservasinya.
                    </p>
                    <div id="rsv_table_container">
                        <table id="rsv_table">
                            <thead>
                                <tr>
                                    <th>ID RSV</th>
                                    <th>Fasilitas</th>
                                    <th>Tanggal & Jam Slot</th>
                                    <th>Batas Tanggal & Jam Batal</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($reservations)): ?>
                                    <tr>
                                        <td colspan="5">Belum ada reservasi.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($reservations as $reservation): ?>
                                        <tr class="rsv_row">
                                            <td><?= htmlspecialchars($reservation['rsv_id']); ?></td>
                                            <td><?= htmlspecialchars($reservation['fac_name']); ?></td>
                                            <td>
                                                <?= htmlspecialchars($reservation['rsv_date']); ?><br>
                                                <?= htmlspecialchars($reservation['start_time']); ?>
                                                -
                                                <?= htmlspecialchars($reservation['end_time']); ?>
                                            </td>
                                            <td>
                                                <?php if (
                                                    $reservation['rsv_status'] === 'proses pengajuan' ||
                                                    $reservation['rsv_status'] === 'disetujui'
                                                ): ?>
                                                    <?php
                                                        $slotStart = new DateTime(
                                                            $reservation['rsv_date'] . ' ' . $reservation['start_time']
                                                        );

                                                        $cancelDeadline = clone $slotStart;
                                                        $cancelDeadline->modify('-24 hours');
                                                    ?>
                                                    <?= $cancelDeadline->format('d-m-Y H:i'); ?>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($reservation['rsv_status']); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                <?php elseif ($tab === 'proses'): ?>
                    <p class="rsv_description">
                        Berikut semua reservasi yang masih dalam proses pengajuan.
                        Pilih salah satu baris untuk melihat detail atau membatalkan reservasinya.
                    </p>

                    <div id="rsv_table_container">
                        <table id="rsv_table">
                            <thead>
                                <tr>
                                    <th>ID RSV</th>
                                    <th>Fasilitas</th>
                                    <th>Tanggal & Jam Slot</th>
                                    <th>Batas Tanggal & Jam Batal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if (empty($reservations)): ?>

                                    <tr>
                                        <td colspan="5">
                                            Belum ada reservasi dalam proses pengajuan.
                                        </td>
                                    </tr>

                                <?php else: ?>

                                    <?php foreach ($reservations as $reservation): ?>

                                        <?php
                                            $slotStart = new DateTime(
                                                $reservation['rsv_date'] . ' ' . $reservation['start_time']
                                            );

                                            $cancelDeadline = clone $slotStart;
                                            $cancelDeadline->modify('-24 hours');
                                        ?>

                                        <tr class="rsv_row">
                                            <td>
                                                <?= htmlspecialchars($reservation['rsv_id']); ?>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($reservation['fac_name']); ?>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($reservation['rsv_date']); ?>
                                                <br>
                                                <?= htmlspecialchars($reservation['start_time']); ?>
                                                -
                                                <?= htmlspecialchars($reservation['end_time']); ?>
                                            </td>
                                            <td>
                                                <?= $cancelDeadline->format('d-m-Y H:i'); ?>
                                            </td>
                                            <td>
                                                <button
                                                    type="button"
                                                    class="btn_batal"
                                                    data-rsv-id="<?= htmlspecialchars($reservation['rsv_id']); ?>">
                                                    Batal
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                <?php elseif ($tab === 'disetujui'): ?>
                    <!-- nanti tabel Disetujui di sini -->

                <?php elseif ($tab === 'ditolak'): ?>
                    <!-- nanti tabel Ditolak di sini -->

                <?php elseif ($tab === 'dibatalkan'): ?>
                    <!-- nanti tabel Dibatalkan di sini -->

                <?php elseif ($tab === 'kadaluarsa'): ?>
                        <!-- nanti tabel Kadaluarsa di sini -->

                <?php elseif ($tab === 'riwayat'): ?>
                        <!-- nanti tabel Riwayat di sini -->
                <?php endif; ?>

            </div>
           
        </div>
    </div>
</body>
</html>
<link rel="stylesheet" href="../../public/css/pengguna/pengguna_style.css">
<script src="../../public/js/pengguna/pengguna.js"></script>

