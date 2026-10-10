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

        <div class="main_content">
            <?php if (isset($_SESSION['notice'])): ?>
                <?php
                    $notice = $_SESSION['notice'];
                    unset($_SESSION['notice']);

                    if (is_array($notice)) {
                        $noticeType = $notice['type'] ?? 'error';
                        $noticeMessage = $notice['message'] ?? '';
                    } else {
                        $noticeType = 'error';
                        $noticeMessage = $notice;
                    }

                    if (!in_array($noticeType, ['success', 'error'], true)) {
                        $noticeType = 'error';
                    }
                ?>

                <div class="notice <?= htmlspecialchars($noticeType); ?>">
                    <?= htmlspecialchars($noticeMessage); ?>
                </div>
            <?php endif; ?>
            
            <div id="content_1">
                <div class="content_title">Daftar Fasilitas</div>
                <p class="small_desc">Berikut adalah daftar fasilitas yang ada. Anda dapat memilih salah satu untuk melihat detail dan melakukan reservasi/ membuat laporan.</p>
            </div>
                
            <div id="filter_fac">
                <form action = "../../index.php">
                    <input type="hidden" name="page" value="beranda">
                    <div id = "filter_fac_box">
                        <label for="tipe_fac">Tipe:</label>
                        <select name="tipe_fac" id="tipe_fac">
                            <option value="" disabled <?= $tipe_fac === '' ? 'selected' : ''; ?> hidden></option>
                            <option value="ruang Kelas" <?= $tipe_fac === 'ruang kelas' ? 'selected' : ''; ?>>
                                Ruang Kelas
                            </option>
                            <option value="aula" <?= $tipe_fac === 'aula' ? 'selected' : ''; ?>>
                                Aula
                            </option>
                            <option value="laboratorium" <?= $tipe_fac === 'laboratorium' ? 'selected' : ''; ?>>
                                Laboratorium
                            </option>
                            <option value="alat" <?= $tipe_fac === 'alat' ? 'selected' : ''; ?>>
                                Alat
                            </option>
                            <option value="lapangan" <?= $tipe_fac === 'lapangan' ? 'selected' : ''; ?>>
                                Lapangan
                            </option>
                        </select>

                        <label for="lokasi_fac">Lokasi:</label>
                        <select name="lokasi_fac" id="lokasi_fac">
                            <option value="" disabled <?= $lokasi_fac === '' ? 'selected' : ''; ?> hidden></option>

                            <option value="Fakultas Hukum (FH)" <?= $lokasi_fac === 'Fakultas Hukum (FH)' ? 'selected' : ''; ?>>
                                Fakultas Hukum (FH)
                            </option>

                            <option value="Fakultas Ekonomika dan Bisnis (FEB)" <?= $lokasi_fac === 'Fakultas Ekonomika dan Bisnis (FEB)' ? 'selected' : ''; ?>>
                                Fakultas Ekonomika dan Bisnis (FEB)
                            </option>

                            <option value="Fakultas Teknik (FT)" <?= $lokasi_fac === 'Fakultas Teknik (FT)' ? 'selected' : ''; ?>>
                                Fakultas Teknik (FT)
                            </option>

                            <option value="Fakultas Kedokteran (FK)" <?= $lokasi_fac === 'Fakultas Kedokteran (FK)' ? 'selected' : ''; ?>>
                                Fakultas Kedokteran (FK)
                            </option>

                            <option value="Fakultas Peternakan dan Pertanian (FPP)" <?= $lokasi_fac === 'Fakultas Peternakan dan Pertanian (FPP)' ? 'selected' : ''; ?>>
                                Fakultas Peternakan dan Pertanian (FPP)
                            </option>

                            <option value="Fakultas Ilmu Budaya (FIB)" <?= $lokasi_fac === 'Fakultas Ilmu Budaya (FIB)' ? 'selected' : ''; ?>>
                                Fakultas Ilmu Budaya (FIB)
                            </option>

                            <option value="Fakultas Ilmu Sosial dan Ilmu Politik (FISIP)" <?= $lokasi_fac === 'Fakultas Ilmu Sosial dan Ilmu Politik (FISIP)' ? 'selected' : ''; ?>>
                                Fakultas Ilmu Sosial dan Ilmu Politik (FISIP)
                            </option>

                            <option value="Fakultas Sains dan Matematika (FSM)" <?= $lokasi_fac === 'Fakultas Sains dan Matematika (FSM)' ? 'selected' : ''; ?>>
                                Fakultas Sains dan Matematika (FSM)
                            </option>

                            <option value="Sekolah Vokasi & Sekolah Pascasarjana" <?= $lokasi_fac === 'Sekolah Vokasi & Sekolah Pascasarjana' ? 'selected' : ''; ?>>
                                Sekolah Vokasi & Sekolah Pascasarjana
                            </option>
                        </select>

                        <label for="kapasitas_fac">Kapasitas Minimum:</label>
                        <input type="number" name="kapasitas_fac" id="kapasitas_fac" class="filter_capacity" min="1" step="1" value="<?= htmlspecialchars($kapasitas_fac); ?>">

                        <div class="action_group">
                            <input type="submit" value="Terapkan Filter">

                            <a href="../../index.php?page=beranda">
                                <button type="button">Reset Filter</button>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <table id="facility_table">
                <thead>
                    <tr>
                        <th class="col-facility-name">Nama Fasilitas</th>
                        <th class="col-facility-type">Tipe</th>
                        <th class="col-facility-location">Lokasi</th>
                        <th class="col-facility-capacity">Kapasitas</th>
                        <th class="col-facility-status">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($facilities)): ?>
                        <?php foreach ($facilities as $f): ?>
                        <tr class="clickable-row"
                            data-fac-id="<?= htmlspecialchars($f['fac_id']); ?>"
                            onclick="window.location.href='../../index.php?page=detail_fasilitas&id=<?= urlencode($f['fac_id']); ?>'">
                            <td ><?= htmlspecialchars($f['fac_name'] ?? ''); ?></td>
                            <td ><?= htmlspecialchars($f['type'] ?? ''); ?></td>
                            <td><?= htmlspecialchars($f['location'] ?? ''); ?></td>
                            <td><?= htmlspecialchars($f['capacity'] ?? ''); ?></td>
                            <td><?= htmlspecialchars($f['fac_status'] ?? ''); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="table_empty">Belum ada data fasilitas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div id="pagination_controls">
                <a href="../../index.php?page=beranda&haltab=<?php echo max(1, $page - 1); ?>&tipe_fac=<?php echo urlencode($tipe_fac); ?>&lokasi_fac=<?php echo urlencode($lokasi_fac); ?>&kapasitas_fac=<?php echo urlencode($kapasitas_fac); ?>"
                id="prev_page_link" class="pagin_link">
                    Previous
                </a>

                <span id="page_info">
                    Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                </span>

                <a href="../../index.php?page=beranda&haltab=<?php echo min($totalPages, $page + 1); ?>&tipe_fac=<?php echo urlencode($tipe_fac); ?>&lokasi_fac=<?php echo urlencode($lokasi_fac); ?>&kapasitas_fac=<?php echo urlencode($kapasitas_fac); ?>"
                id="next_page_link" class="pagin_link">
                    Next
                </a>
            </div>
        </div>
    </div>
</body>
</html>
<script src="../../public/js/pengguna/pengguna.js"></script>
