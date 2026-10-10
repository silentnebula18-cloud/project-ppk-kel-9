<?php
require_once __DIR__ . '/../../app/helpers/validate_session.php';
check_session_valid('pengguna');
?>
<head>
    <link rel="stylesheet" href="../../public/css/pengguna/pengguna_style.css">
</head>
<body id="pengguna_body">
    <div id="dashboard_header">
        <button id="header_logo" type="button" onclick="toggleSidebar()" aria-label="Buka atau tutup menu"><span></span><span></span><span></span></button>
        <span id="header_brand">Campu<em>Space</em></span>
        <span id="header_title">Beranda</span>
        <button id="logout_btn">LogOut</button>
    </div>

    <div id="dashboard_layout">
        <div id="sidebar" class="hidden">
            <a class="nav_item active" href="../../index.php?page=beranda"><span class="nav_icon">🏠</span>Beranda</a>
            <a class="nav_item" href="../../index.php?page=reservasi_saya"><span class="nav_icon">🚪</span>Reservasi Saya</a>
            <a class="nav_item" href="../../index.php?page=laporan_saya"><span class="nav_icon">📜</span>Laporan saya</a>
        </div>

        <div class="main_content" id="hal_detail_fasilitas">
            <div class="section_header">
                <a class = "back_href" href="../../index.php?page=beranda">⬅️</a>
                <button id="cek_slot_waktu_btn" onclick="toggleSlotWaktu()">Cek Slot Waktu</button>
            </div>

            <div id="slot_waktu" class="<?= isset($_GET['tgl_cek_slot_waktu']) ? '' : 'hidden' ?>">
                <h2> Slot Waktu </h2>
                <form method="get" action="../../index.php" id="formCekSlotWaktu">
                    <input type="hidden" name="page" value="cek_slot_waktu">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($fac['fac_id']); ?>">

                    <span class="form_group">
                        <label for="tgl_cek_slot_waktu">Pilih Tanggal:</label>
                        <input type="date" id="tgl_cek_slot_waktu" name="tgl_cek_slot_waktu" required>
                    </span>

                    <button type="submit">Cek Slot</button>
                </form>

                <div id="tabel_slot_waktu">
                    <div id="slot_waktu_table_container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Jam</th>

                                    <?php
                                    $dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                    ?>

                                    <?php foreach ($weekDates as $i => $date): ?>
                                        <th>
                                            <?= $dayNames[$i] ?><br>
                                            <?= $date->format('d/m') ?>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>

                            </thead>

                            <?php
                                function isSlotOccupied($date, $startTime, $endTime, $reservations) {
                                    foreach ($reservations as $reservation) {
                                        $reservationStart = substr($reservation['start_time'], 0, 5);
                                        $reservationEnd = substr($reservation['end_time'], 0, 5);

                                        if (
                                            $reservation['rsv_date'] === $date &&
                                            $reservationStart < $endTime &&
                                            $reservationEnd > $startTime
                                        ) {
                                            return true;
                                        }
                                    }

                                    return false;
                                }
                            ?>
                            
                            <tbody>
                                <?php
                                $startMinutes = 7 * 60;
                                $endMinutes = 20 * 60;

                                for ($time = $startMinutes; $time < $endMinutes; $time += 30):
                                    $hourStart = floor($time / 60);
                                    $minuteStart = $time % 60;

                                    $nextTime = $time + 30;
                                    $hourEnd = floor($nextTime / 60);
                                    $minuteEnd = $nextTime % 60;

                                    $startLabel = sprintf("%02d:%02d", $hourStart, $minuteStart);
                                    $endLabel = sprintf("%02d:%02d", $hourEnd, $minuteEnd);
                                ?>
                                <tr>
                                    <td><?= $startLabel ?> - <?= $endLabel ?></td>

                                    <?php foreach ($weekDates as $date): ?>
                                        <?php
                                            $dateString = $date->format('Y-m-d');

                                            $occupied = isSlotOccupied(
                                                $dateString,
                                                $startLabel,
                                                $endLabel,
                                                $reservations
                                            );
                                        ?>

                                        <td
                                            data-date="<?= $dateString ?>"
                                            data-start="<?= $startLabel ?>"
                                            data-end="<?= $endLabel ?>"
                                            class="<?= $occupied ? 'slot_terisi' : '' ?>"
                                        >
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="slot_legend">
                    <span class="slot_legend_item">
                        <span class="slot_legend_color slot_terisi"></span>
                        Terisi
                    </span>

                    <span class="slot_legend_item">
                        <span class="slot_legend_color"></span>
                        Tersedia
                    </span>
                </div>
                
            </div>

            <!-- DETAIL FASILITAS -->
            <div id="detail_fac">
                <div id="detail_fac_img">
                    <img
                        src="<?= htmlspecialchars($fac['fac_photo']); ?>"
                        alt="Foto <?= htmlspecialchars($fac['fac_name']); ?>"
                    >
                </div>

                <div id="detail_fac_txt">
                    <h2>Detail Fasilitas</h2>

                    <div>fac_id : <?= htmlspecialchars($fac['fac_id']); ?></div>
                    <div>Nama : <?= htmlspecialchars($fac['fac_name']); ?></div>
                    <div>Deskripsi : <?= htmlspecialchars($fac['fac_desc']); ?></div>
                    <div>Tipe : <?= htmlspecialchars($fac['type']); ?></div>
                    <div>Lokasi : <?= htmlspecialchars($fac['location']); ?></div>
                    <div>Kapasitas : <?= htmlspecialchars($fac['capacity']); ?></div>
                    <div>Status : <?= htmlspecialchars($fac['fac_status']); ?></div>
                </div>
            </div>

            <div id="detail_fac_btn">
                 <div id="buat_reservasi">
                        <?php if (strtolower($fac['fac_status'] ?? '') === 'aktif'): ?>
                            <a href="../../index.php?page=form_rsv_2&id=<?= urlencode($fac['fac_id']); ?>&from=detail_fasilitas">
                                <button type="button">Ajukan Reservasi</button>
                            </a>
                        <?php else: ?>
                            <p>
                                Fasilitas sedang tidak dapat digunakan untuk reservasi.
                            </p>
                        <?php endif; ?>
                 </div>
                 <div id="buat_laporan">
                        <?php if (strtolower($fac['fac_status'] ?? '') === 'aktif'): ?>
                            <a href="../../index.php?page=form_rep_2&id=<?= urlencode($fac['fac_id']); ?>&from=detail_fasilitas">
                                <button type="button">Buat Laporan</button>
                            </a>
                        <?php else: ?>
                            <p>
                                Fasilitas sedang tidak dapat digunakan untuk membuat laporan.
                            </p>
                        <?php endif; ?>
                 </div>
            </div>

        </div>
    </div>
</body>
</html>
<script src="../../public/js/pengguna/pengguna.js"></script>
