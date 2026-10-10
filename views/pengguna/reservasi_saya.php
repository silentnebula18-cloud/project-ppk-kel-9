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
        <span id="header_title">Reservasi Saya</span>
        <button id="logout_btn">Keluar</button>
    </div>

    <div id="dashboard_layout">
        <div id="sidebar" class="hidden">
            <a class="nav_item" href="../../index.php?page=beranda"><span class="nav_icon">🏠</span>Beranda</a>
            <a class="nav_item active" href="../../index.php?page=reservasi_saya"><span class="nav_icon">🚪</span>Reservasi Saya</a>
            <a class="nav_item" href="../../index.php?page=laporan_saya"><span class="nav_icon">📜</span>Laporan saya</a>
        </div>

        <div class="main_content">
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

            <?php $rsv_saya_fltr_status = $_GET['rsv_saya_fltr_status'] ?? 'semua';?>
            <div id="content_1">
                <div class="content_title">Reservasi Saya</div>
                <p id="rsv_saya_description">
                    <?php
                        switch ($rsv_saya_fltr_status) {
                            case 'semua':
                                echo "Berikut semua reservasi terlepas dari statusnya. "
                                . "Pilih salah satu baris untuk melihat detail reservasinya. "
                                . "Khusus reservasi berstatus 'Proses Pengajuan' dan 'Disetujui' "
                                . "dapat dibatalkan sesuai batas waktu yang tertera.";
                                break;

                            case 'proses pengajuan':
                                echo "Berikut semua reservasi yang masih dalam proses pengajuan "
                                . "yang belum melewati slot waktu yang dipilih reservasi. "
                                . "Pilih salah satu baris untuk melihat detail atau membatalkan reservasinya.";
                                break;

                            case 'disetujui':
                                echo "Berikut semua reservasi yang sudah disetujui pengajuan "
                                . "yang belum melewati slot waktu yang dipilih reservasi. "
                                . "Pilih salah satu baris untuk melihat detail atau membatalkan reservasinya.";
                                break;

                            case 'ditolak':
                                echo "Berikut reservasi yang telah ditolak oleh petugas. "
                                . "Pilih salah satu baris untuk melihat detail reservasinya.";
                                break;

                            case 'dibatalkan':
                                echo "Berikut reservasi yang telah dibatalkan (oleh anda maupun petugas). "
                                . "Pilih salah satu baris untuk melihat detail reservasinya.";
                                break;

                            case 'kadaluarsa':
                                echo "Berikut reservasi yang kadaluarsa "
                                . "(pengajuan reservasi yang belum disetujui/ditolak petugas "
                                . "hingga slot waktu reservasi). "
                                . "Pilih salah satu baris untuk melihat detail reservasinya.";
                                break;

                            case 'riwayat':
                                echo "Reservasi yang ditampilkan termasuk yang sudah disetujui "
                                . "(sudah melewati slot waktu yang dipilih reservasi), "
                                . "sudah ditolak petugas, sudah dbatalkan oleh anda maupun petugas, atau sudah kadaluarsa. "
                                . "Pilih salah satu baris untuk melihat detail reservasinya.";
                                break;
                            }
                    ?>
                </p>
            </div>

            
            <div id="rsv_saya_top">
                <div id="rsv_saya_filter">
                    <label for="rsv_saya_fltr_status">Filter Status:</label>
                    <select id="rsv_saya_fltr_status" name="rsv_saya_fltr_status">
                        <option value="semua" <?= $rsv_saya_fltr_status === 'semua' ? 'selected' : ''; ?>>
                            Semua
                        </option>
                        <option value="proses pengajuan" <?= $rsv_saya_fltr_status === 'proses pengajuan' ? 'selected' : ''; ?>>
                            Proses Pengajuan
                        </option>
                        <option value="disetujui" <?= $rsv_saya_fltr_status === 'disetujui' ? 'selected' : ''; ?>>
                            Disetujui
                        </option>
                        <option value="ditolak" <?= $rsv_saya_fltr_status === 'ditolak' ? 'selected' : ''; ?>>
                            Ditolak
                        </option>
                        <option value="dibatalkan" <?= $rsv_saya_fltr_status === 'dibatalkan' ? 'selected' : ''; ?>>
                            Dibatalkan
                        </option>
                        <option value="kadaluarsa" <?= $rsv_saya_fltr_status === 'kadaluarsa' ? 'selected' : ''; ?>>
                            Kadaluarsa
                        </option>
                        <option value="riwayat" <?= $rsv_saya_fltr_status === 'riwayat' ? 'selected' : ''; ?>>
                            Riwayat
                        </option>
                    </select>
                </div>

                <div class="action_group">
                    <a href="../../index.php?page=form_rsv_1" class="btn">
                        Buat Reservasi
                    </a>
                </div>
            </div>


            <!-- TABEL RESERVASI -->
           <div id="rsv_content">
                <table id="rsv_saya_table">
                    <thead>
                        <tr>
                            <th>ID Reservasi</th>
                            <th>Fasilitas</th>
                            <th>Tanggal & Jam Reservasi</th>
                            <th>Batas Pembatalan</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($reservasi)): ?>
                            <tr>
                                <td colspan="5" class="table_empty">
                                    Tidak ada reservasi.
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($reservasi as $rsv): ?>

                                <?php
                                    // Batas pembatalan hanya untuk
                                    // reservasi yang masih aktif
                                    if ($rsv['rsv_status'] === 'proses pengajuan' || $rsv['rsv_status'] === 'disetujui') {
                                        $batasPembatalan = date(
                                            'd-m-Y H:i',
                                            strtotime($rsv['rsv_date'] . ' ' . $rsv['start_time']) - 86400
                                        );
                                    } else {
                                        $batasPembatalan = '-';
                                    }

                                    // Class untuk warna status
                                    $statusClass = match ($rsv['rsv_status']) {
                                        'proses pengajuan' => 'status-proses',
                                        'disetujui'        => 'status-disetujui',
                                        'ditolak'          => 'status-ditolak',
                                        'dibatalkan'       => 'status-dibatalkan',
                                        'kadaluarsa'       => 'status-kadaluarsa',
                                        default            => ''
                                    };
                                ?>

                               <tr
                                    class="clickable-row <?= $statusClass ?>"
                                    onclick="window.location.href='../../index.php?page=detail_reservasi&rsv_id=<?= urlencode($rsv['rsv_id']) ?>&fac_id=<?= urlencode($rsv['fac_id']) ?>&status=<?= urlencode($rsv['rsv_status']) ?>'"
                                >
                                    <td>
                                        <?= htmlspecialchars($rsv['rsv_id']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($rsv['fac_name']) ?>
                                        (<?= htmlspecialchars($rsv['location']) ?>)
                                    </td>

                                    <td>
                                        <?= date('d-m-Y', strtotime($rsv['rsv_date'])) ?>
                                        <br>
                                        <?= date('H:i', strtotime($rsv['start_time'])) ?>
                                        -
                                        <?= date('H:i', strtotime($rsv['end_time'])) ?>
                                    </td>

                                    <td>
                                        <?= $batasPembatalan ?>
                                    </td>

                                    <td>
                                        <?php if($rsv['rsv_status'] === 'dibatalkan'): ?>
                                            <?php if($rsv['cancelled_by'] === $_SESSION['user_id']):?>
                                                <?= htmlspecialchars(ucwords($rsv['rsv_status']) . ' (pengguna)') ?>
                                            <?php else: ?>
                                                <?= htmlspecialchars(ucwords($rsv['rsv_status']) . ' (petugas)') ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?= htmlspecialchars(ucwords($rsv['rsv_status'])) ?>
                                        <?php endif; ?>
                                        
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <div id="pagination_controls">

                <a href="../../index.php?page=reservasi_saya&haltab=<?= max(1, $page - 1); ?>&rsv_saya_fltr_status=<?= urlencode($rsv_saya_fltr_status); ?>"
                class="pagin_link">
                    Previous
                </a>

                <span id="page_info">
                    Page <?= $page; ?> of <?= max(1, $totalPages); ?>
                </span>

                <a href="../../index.php?page=reservasi_saya&haltab=<?= min(max(1, $totalPages), $page + 1); ?>&rsv_saya_fltr_status=<?= urlencode($rsv_saya_fltr_status); ?>"
                class="pagin_link">
                    Next
                </a>
            </div>

        </div> <!-- main_content -->
    </div> <!-- dashboard_layout -->
           
</body>
</html>
<script src="../../public/js/pengguna/pengguna.js"></script>
<script src="../../public/js/pengguna/reservasi_saya.js"></script>
