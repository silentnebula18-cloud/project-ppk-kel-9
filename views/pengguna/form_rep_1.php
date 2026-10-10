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
        <span id="header_title">Laporan Saya</span>
        <button id="logout_btn">LogOut</button>
    </div>

    <div id="dashboard_layout">

        <div id="sidebar" class="hidden">
            <a class="nav_item" href="../../index.php?page=beranda"><span class="nav_icon">🏠</span>Beranda</a>
            <a class="nav_item" href="../../index.php?page=reservasi_saya"><span class="nav_icon">🚪</span>Reservasi Saya</a>
            <a class="nav_item active" href="../../index.php?page=laporan_saya"><span class="nav_icon">📜</span>Laporan Saya</a>
        </div>

        <div class="main_content">

            <div id="content_1">
                <div class="section_header">
                    <a class="back_href" href="../../index.php?page=laporan_saya">⬅️</a>
                    <div class="content_title">Pilih Fasilitas</div>
                </div>

                <p class="small_desc">
                    Pilih fasilitas yang ingin Anda laporakn.
                </p>
            </div>
            
            <div id="filter_fac">
                <form action="../../index.php">

                    <input type="hidden" name="page" value="form_rep_1">

                    <div id="filter_fac_box">

                        <label for="tipe_fac">Tipe:</label>

                        <select name="tipe_fac" id="tipe_fac">
                            <option value="" disabled <?= $tipe_fac === '' ? 'selected' : ''; ?> hidden></option>

                            <option value="ruang kelas" <?= $tipe_fac === 'ruang kelas' ? 'selected' : ''; ?>>
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

                            <a href="../../index.php?page=form_rep_1">
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
                        <th class="col-facility-action">Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (!empty($facilities)): ?>

                        <?php foreach ($facilities as $f): ?>

                            <tr>
                                <td> <?= htmlspecialchars($f['fac_name']); ?></td>
                                <td><?= htmlspecialchars($f['type']); ?></td>
                                <td><?= htmlspecialchars($f['location']); ?></td>
                                <td><?= htmlspecialchars($f['capacity']); ?></td>

                                <td>
                                    <a
                                        href="../../index.php?page=form_rep_2&id=<?= urlencode($f['fac_id']); ?>&from=form_rep_1"
                                        class="btn"
                                    >
                                        Pilih
                                    </a>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="5" class="table_empty">
                                Belum ada fasilitas aktif.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

            <div id="pagination_controls">

                <a href="../../index.php?page=form_rep_1&haltab=<?= max(1, $page - 1); ?>&tipe_fac=<?= urlencode($tipe_fac); ?>&lokasi_fac=<?= urlencode($lokasi_fac); ?>&kapasitas_fac=<?= urlencode($kapasitas_fac); ?>"
                class="pagin_link">
                    Previous
                </a>

                <span id="page_info">
                    Page <?= $page; ?> of <?= $totalPages; ?>
                </span>

                <a href="../../index.php?page=form_rep_1&haltab=<?= min($totalPages, $page + 1); ?>&tipe_fac=<?= urlencode($tipe_fac); ?>&lokasi_fac=<?= urlencode($lokasi_fac); ?>&kapasitas_fac=<?= urlencode($kapasitas_fac); ?>"
                class="pagin_link">
                    Next
                </a>

            </div>

        </div>

    </div>

</body>

</html>
<script src="../../public/js/pengguna/pengguna.js"></script>