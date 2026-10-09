<script>
    const approvedReservations = <?= json_encode($reservations); ?>;
</script>

<body id="pengguna_body">

    <div id="dashboard_header">
        <span id="header_logo" onclick="toggleSidebar()">★</span>
        <?php if ($_GET['from'] === 'form_rsv_1') : ?>
            <span id="header_title">Reservasi Saya</span>
        <?php elseif ($_GET['from'] === 'detail_fasilitas') : ?>
            <span id="header_title">Beranda</span>
        <?php endif; ?>
        <button id="logout_btn">LogOut</button>
    </div>

    <div id="dashboard_layout">

        <div id="sidebar" class="hidden">
            <?php if ($_GET['from'] === 'form_rsv_1') : ?>
                <a class="nav_item" href="../../index.php?page=beranda"><span class="nav_icon">🏠</span>Beranda</a>
                <a class="nav_item active" href="../../index.php?page=reservasi_saya"><span class="nav_icon">🚪</span>Reservasi Saya</a>
            <?php elseif ($_GET['from'] === 'detail_fasilitas') : ?>
                <a class="nav_item active" href="../../index.php?page=beranda"><span class="nav_icon">🏠</span>Beranda</a>
                <a class="nav_item" href="../../index.php?page=reservasi_saya"><span class="nav_icon">🚪</span>Reservasi Saya</a>
            <?php endif; ?>
            <a class="nav_item" href="../../index.php?page=laporan_saya"><span class="nav_icon">📜</span>Laporan saya</a>
        </div>

        <div class="main_content">

           <div style= "display: flex; align-items: center">
            <?php if ($_GET['from'] === 'form_rsv_1') : ?>
                <a class = "back_href" href="../../index.php?page=form_rsv_1">⬅️</a>
            <?php elseif ($_GET['from'] === 'detail_fasilitas') : ?>
                <a class = "back_href" href="../../index.php?page=detail_fasilitas&id=<?= urlencode($fac['fac_id']); ?>">⬅️</a>
            <?php endif; ?>
            
            <button id="cek_slot_waktu_btn" onclick="toggleSlotWaktu()">Cek Slot Waktu</button>
            </div>

            <div id="slot_waktu" class="<?= isset($_GET['tgl_cek_slot_waktu']) ? '' : 'hidden' ?>">
                <h2> Slot Waktu </h2>
                <form method="get" action="../../index.php" id="formCekSlotWaktu">
                    <input type="hidden" name="page" value="cek_slot_waktu_form_rsv_2">
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
                                    if (
                                        $reservation['rsv_date'] === $date &&
                                        $reservation['start_time'] < $endTime &&
                                        $reservation['end_time'] > $startTime
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

            <div id = "form_rsv_entry">
                <h2>Ajukan Reservasi</h2>

                <form id="form_rsv_2" method="post" action="../../index.php">

                    <input type="hidden" name="action" value="ajukan_reservasi">
                    <input type="hidden" name="fac_id" value="<?= htmlspecialchars($fac['fac_id']); ?>">
                    <input type="hidden" name="from" value="<?= htmlspecialchars($_GET['from'] ?? ''); ?>">

                    <div>
                        <label for="rsv_date">Tanggal:</label>
                        <input type="date" id="rsv_date" name="rsv_date" required>
                    </div>

                    <div>
                        <label for="start_time">Jam Mulai:</label>
                        <select id="start_time" name="start_time" required>
                            <option value="">-- Pilih Jam Mulai --</option>

                            <?php
                            for ($time = 7 * 60; $time < 20 * 60; $time += 30):
                                $hour = floor($time / 60);
                                $minute = $time % 60;
                                $value = sprintf("%02d:%02d", $hour, $minute);
                            ?>
                                <option value="<?= $value ?>">
                                    <?= $value ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div>
                        <label for="end_time">Jam Selesai:</label>
                        <select id="end_time" name="end_time" required disabled>
                            <option value="">-- Pilih Jam Selesai --</option>

                            <?php
                            for ($time = 7 * 60 + 30; $time <= 20 * 60; $time += 30):
                                $hour = floor($time / 60);
                                $minute = $time % 60;
                                $value = sprintf("%02d:%02d", $hour, $minute);
                            ?>
                                <option value="<?= $value ?>">
                                    <?= $value ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div>
                        <label for="purpose">Tujuan:</label>
                        <textarea id="purpose" name="purpose" maxlength="300" required></textarea>
                    </div>

                    <button type="submit" style="margin-top: 20px;">Ajukan Reservasi</button>

                </form>

            </div>


           
        </div>

    </div>

</body>
</html>

<link rel="stylesheet" href="../../public/css/pengguna/pengguna_style.css">
<script src="../../public/js/pengguna/pengguna.js"></script>
<script src="../../public/js/pengguna/form_rsv_2.js"></script>