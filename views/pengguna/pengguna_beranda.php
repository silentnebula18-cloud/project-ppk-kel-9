<body id="pengguna_body">
    
    <div id="dashboard_header">
        <span id="header_logo" onclick="toggleSidebar()">★</span>
        <span id="header_title">Beranda</span>
        <button id="logout_btn">LogOut</button>
    </div>

    <?php if (isset($_SESSION['notice'])): ?>
        <div class="notice">
            <?= htmlspecialchars($_SESSION['notice']); ?>
        </div>

        <?php unset($_SESSION['notice']); ?>
    <?php endif; ?>

    <div id="dashboard_layout">
        <div id="sidebar" class="hidden">
            <a class="nav_item active" href="../../index.php?page=beranda"><span class="nav_icon">🏠</span>Beranda</a>
            <a class="nav_item" href="../../index.php?page=reservasi_saya"><span class="nav_icon">🚪</span>Reservasi Saya</a>
            <!-- <a class="nav_item" href="../../index.php?page=laporan_saya"><span class="nav_icon">📜</span>Laporan saya</a> -->
        </div>

        <div class="main_content">
            <div id="content_1">
                <div class="content_title">Daftar Fasilitas</div>
                <p class="small_desc">Berikut adalah daftar fasilitas yang ada. Anda dapat memilih salah satu untuk melihat detail dan melakukan reservasi.</p>
            </div>
                
            <div id="filter_fac">
                <form action = "../../index.php">
                    <input type="hidden" name="page" value="beranda">
                    <div id = "filter_fac_box" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                        <label for="tipe_fac">Tipe:</label>
                        <select name="tipe_fac" id="tipe_fac">
                            <option value="" disabled <?= $tipe_fac === '' ? 'selected' : ''; ?> hidden></option>
                            <option value="ruang Kelas" <?= $tipe_fac === 'Ruang Kelas' ? 'selected' : ''; ?>>
                                Ruang Kelas
                            </option>
                            <option value="aula" <?= $tipe_fac === 'Aula' ? 'selected' : ''; ?>>
                                Aula
                            </option>
                            <option value="laboratorium" <?= $tipe_fac === 'Laboratorium' ? 'selected' : ''; ?>>
                                Laboratorium
                            </option>
                            <option value="alat" <?= $tipe_fac === 'Alat' ? 'selected' : ''; ?>>
                                Alat
                            </option>
                            <option value="lapangan" <?= $tipe_fac === 'Lapangan' ? 'selected' : ''; ?>>
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
                        <input type="number" name="kapasitas_fac" min="1" step="1" value="<?= htmlspecialchars($kapasitas_fac); ?>" style = "width: 70px">

                        <br> <br>

                        <input type = "submit" value="Terapkan Filter">

                        <a href="../../index.php?page=beranda">
                            <button type="button">Reset Filter</button>
                        </a>
                    </div>
                </form>
            </div>

            <table id="facility_table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Nama Fasilitas</th>
                        <th style="width: 15%;">Tipe</th>
                        <th style="width: 30%;">Lokasi</th>
                        <th style="width: 10%;">Kapasitas</th>
                        <th style="width: 15%;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($facilities)): ?>
                        <?php foreach ($facilities as $f): ?>
                        <tr data-fac-id="<?= htmlspecialchars($f['fac_id']); ?>"
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
                            <td colspan="5">Belum ada data fasilitas.</td>
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

<link rel="stylesheet" href="../../public/css/pengguna/pengguna_style.css">
<script src="../../public/js/pengguna/pengguna.js"></script>
