<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CampuSpace</title>
  <link rel="stylesheet" href="../../public/css/pengguna/pengguna_style.css">
  <script src="../../public/js/pengguna/sidebar.js"></script>
</head>
<body id="pengguna_body">
    <div id="dashboard_header">
        <span id="header_logo" onclick="toggleSidebar()">★</span>
        <span id="header_title">Fasilitas</span>
        <button id="logout_btn">LogOut</button>
    </div>

    <div id="dashboard_layout">
        <div id="sidebar" class="hidden">
            <a class="nav_item" href="pengguna_beranda.html"><span class="nav_icon">🏠</span>Beranda</a>
            <a class="nav_item active" href="../../index.php?page=fasilitas"><span class="nav_icon">🚪</span>Fasilitas</a>
            <a class="nav_item" href="reservasi_saya.html"><span class="nav_icon">📅</span>Reservasi Saya</a>
            <a class="nav_item" href="pengguna_laporan.html"><span class="nav_icon">📝</span>Laporan</a>
        </div>

        <div id="main_content">
            <div id="content_1">
                <div class="content_title">Daftar Fasilitas</div>
                <p class="small_desc">Berikut adalah daftar fasilitas yang ada. Anda dapat memilih salah satu untuk melihat detail dan melakukan reservasi.</p>
            </div>
                
            <div id="filter_fac">
                <span class="filter_fac_title">Tipe:</span>
                <span class="filter_fac_title">Lokasi:</span>
                <span class="filter_fac_title">Kapasitas:</span>
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
                        <tr>
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

            <div id = "pop_up_bg">
                
            </div>

            <div id="pagination_controls">
                <a href="../../index.php?page=fasilitas&haltab=<?php echo max(1, $page - 1); ?>" id="prev_page_link" class="pagin_link">Previous</a>
                <span id="page_info">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                <a href="../../index.php?page=fasilitas&haltab=<?php echo min($totalPages, $page + 1); ?>" id="next_page_link" class="pagin_link">Next</a>
            </div>
        </div>
    </div>
</body>
</html>