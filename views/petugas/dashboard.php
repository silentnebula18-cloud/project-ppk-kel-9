<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Petugas - Dashboard</title>
    <link rel="stylesheet" href="../../public/css/petugas/dashboard_style.css">
</head>
<body id="body">

    <aside id="sidebar">
        <div id="sidebar_logo">
            <span id="header_logo">★</span>
            <span>Petugas</span>
        </div>
        <nav>
            <a href="#" class="active">Beranda</a>
            <a href="#">Reservasi</a>
            <a href="#">Laporan</a>
            <a href="#">Fasilitas</a>
            <a href="#">Log Out</a>
        </nav>
    </aside>

    <main id="main">

        <!-- Section 1: Reservasi diproses -->
        <section class="panel" id="panel_proses">
            <div class="panel_header">
                <h2>Reservasi Menunggu Persetujuan</h2>
            </div>
            <div class="panel_body">
                <?php if (empty($pendingReservations)): ?>
                    <p class="empty">Tidak ada reservasi yang menunggu.</p>
                <?php else: ?>
                    <?php foreach ($pendingReservations as $r): ?>
                        <div class="item">
                            <div>
                                <div class="item_title"><?= htmlspecialchars($r['fac_name']) ?></div>
                                <div class="item_meta">
                                    <?= htmlspecialchars($r['username']) ?> · 
                                    <?= date('d M Y', strtotime($r['rsv_date'])) ?> · 
                                    <?= date('H:i', strtotime($r['start_time'])) ?>–<?= date('H:i', strtotime($r['end_time'])) ?>
                                </div>
                            </div>
                            <div class="actions">
                                <form action="process_reservation.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="rsv_id" value="<?= $r['rsv_id'] ?>">
                                    <button type="submit" name="action" value="approve" class="btn_ok">Terima</button>
                                    <button type="submit" name="action" value="reject" class="btn_danger">Tolak</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Section 2: Laporan -->
        <section class="panel" id="panel_laporan">
            <div class="panel_header">
                <h2>Laporan Masuk</h2>
                <a href="#">Lihat semua</a>
            </div>
            <div class="panel_body">
                <?php if (empty($reports)): ?>
                    <p class="empty">Tidak ada laporan baru atau diproses.</p>
                <?php else: ?>
                    <?php foreach ($reports as $rp): ?>
                        <div class="item">
                            <div>
                                <div class="item_title">
                                    [<?= htmlspecialchars(ucwords($rp['category'])) ?>] <?= htmlspecialchars($rp['rep_desc']) ?>
                                </div>
                                <div class="item_meta">
                                    <?= htmlspecialchars($rp['fac_name']) ?> · <?= htmlspecialchars($rp['username']) ?>
                                </div>
                            </div>
                            <div class="actions">
                                <a href="edit_report.php?id=<?= $rp['rep_id'] ?>"><button type="button">Edit</button></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Section 3: Reservasi diterima -->
        <section class="panel" id="panel_diterima">
            <div class="panel_header">
                <h2>Reservasi Disetujui</h2>
            </div>
            <div class="panel_body">
                <?php if (empty($approvedReservations)): ?>
                    <p class="empty">Belum ada reservasi disetujui mendatang.</p>
                <?php else: ?>
                    <?php foreach ($approvedReservations as $app): ?>
                        <div class="item">
                            <div>
                                <div class="item_title"><?= htmlspecialchars($app['fac_name']) ?></div>
                                <div class="item_meta">
                                    <?= htmlspecialchars($app['username']) ?> · 
                                    <?= date('d M Y', strtotime($app['rsv_date'])) ?> · 
                                    <?= date('H:i', strtotime($app['start_time'])) ?>–<?= date('H:i', strtotime($app['end_time'])) ?>
                                </div>
                            </div>
                            <div class="actions">
                                <form action="process_reservation.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="rsv_id" value="<?= $app['rsv_id'] ?>">
                                    <button type="submit" name="action" value="cancel" class="btn_danger">Batalkan</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

    </main>

</body>
</html>