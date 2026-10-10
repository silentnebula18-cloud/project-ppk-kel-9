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
            <a class="nav_item" href="../../index.php?page=beranda">
                <span class="nav_icon">🏠</span>Beranda
            </a>

            <a class="nav_item" href="../../index.php?page=reservasi_saya">
                <span class="nav_icon">🚪</span>Reservasi Saya
            </a>

            <a class="nav_item active" href="../../index.php?page=laporan_saya">
                <span class="nav_icon">📜</span>Laporan Saya
            </a>
        </div>

        <div class="main_content" id="hal_laporan_saya">
            <?php if (isset($_SESSION['notice'])): ?>
                <?php
                    $notice = $_SESSION['notice'];
                    unset($_SESSION['notice']);

                    // Format array baru; string lama tetap ditangani sebagai error.
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
                <div class="content_title">Laporan Saya</div>

                <p class="small_desc">
                    Berikut adalah daftar laporan yang telah Anda ajukan.
                </p>
            </div>

            <div id="rep_saya_top">

                <div>
                    <!-- Filter kategori dan status -->
                    <form action="../../index.php" method="get" id="rep_saya_filter">
                        <input type="hidden" name="page" value="laporan_saya">

                        <label for="category">Kategori:</label>
                        <select name="category" id="category">

                            <option value="" <?= $category === '' ? 'selected' : ''; ?> hidden></option>

                            <option value="kerusakan bangunan"
                                <?= $category === 'kerusakan bangunan' ? 'selected' : ''; ?>>
                                Kerusakan Bangunan
                            </option>

                            <option value="kerusakan peralatan"
                                <?= $category === 'kerusakan peralatan' ? 'selected' : ''; ?>>
                                Kerusakan Peralatan
                            </option>

                            <option value="kelistrikan"
                                <?= $category === 'kelistrikan' ? 'selected' : ''; ?>>
                                Kelistrikan
                            </option>

                            <option value="kebersihan"
                                <?= $category === 'kebersihan' ? 'selected' : ''; ?>>
                                Kebersihan
                            </option>

                            <option value="lainnya"
                                <?= $category === 'lainnya' ? 'selected' : ''; ?>>
                                Lainnya
                            </option>

                        </select>

                        <label for="status">Status:</label>
                        <select name="status" id="status">

                            <option value="" <?= $status === '' ? 'selected' : ''; ?> hidden></option>

                            <option value="baru"
                                <?= $status === 'baru' ? 'selected' : ''; ?>>
                                Baru
                            </option>

                            <option value="diproses"
                                <?= $status === 'diproses' ? 'selected' : ''; ?>>
                                Diproses
                            </option>

                            <option value="selesai"
                                <?= $status === 'selesai' ? 'selected' : ''; ?>>
                                Selesai
                            </option>

                            <option value="ditolak"
                                <?= $status === 'ditolak' ? 'selected' : ''; ?>>
                                Ditolak
                            </option>

                        </select>

                        <div class="action_group">
                            <button type="submit">Terapkan Filter</button>
                            <a href="../../index.php?page=laporan_saya" class="btn">
                                Reset Filter
                            </a>
                        </div>

                    </form>
                </div>
                

                <div class="action_group">
                    <a href="../../index.php?page=form_rep_1" class="btn">
                        Buat Laporan
                    </a>
                </div>

            </div>

            <!-- Tabel laporan -->
            <table id="report_table">

                <thead>
                    <tr>
                        <th>ID Laporan</th>
                        <th>Waktu Laporan</th>
                        <th>Kategori</th>
                        <th>Fasilitas</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (!empty($reports)): ?>

                        <?php foreach ($reports as $rep): ?>

                            <tr
                            class="clickable-row"
                            onclick="window.location.href='../../index.php?page=detail_laporan&rep_id=<?= urlencode($rep['rep_id']); ?>'"
                            >

                                <td>
                                    <?= htmlspecialchars($rep['rep_id']); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($rep['reported_at']); ?>
                                </td>

                                <td><?= htmlspecialchars($rep['category']); ?></td>

                                <td>
                                    <?= htmlspecialchars(
                                        $rep['fac_name'] . ' (' . $rep['location'] . ')'
                                    ); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($rep['rep_status']); ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="5" class="table_empty">Belum ada laporan yang sesuai.</td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

            <!-- Pagination -->
            <div id="pagination_controls">

                <a href="../../index.php?page=laporan_saya&haltab=<?= max(1, $page - 1); ?>&category=<?= urlencode($category); ?>&status=<?= urlencode($status); ?>"
                   class="pagin_link">
                    Previous
                </a>

                <span id="page_info">
                    Page <?= $page; ?> of <?= max(1, $totalPages); ?>
                </span>

                <a href="../../index.php?page=laporan_saya&haltab=<?= min(max(1, $totalPages), $page + 1); ?>&category=<?= urlencode($category); ?>&status=<?= urlencode($status); ?>"
                   class="pagin_link">
                    Next
                </a>

            </div>

        </div>

    </div>
</body>
</html>
<script src="../../public/js/pengguna/pengguna.js"></script>
