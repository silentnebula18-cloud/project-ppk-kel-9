<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Petugas - Dashboard</title>
    <link rel="stylesheet" href="../../public/css/petugas/dashboard_style.css">
    <style>
        /* CSS Sederhana untuk Modal Penolakan */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: #fff; margin: 15% auto; padding: 20px; width: 400px; border-radius: 8px; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        .alert-success { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body id="body">

    <aside id="sidebar">
        <div id="sidebar_logo">
            <span id="header_logo">★</span>
            <span>Petugas</span>
        </div>
        <nav>
            <a href="index.php?page=dashboard" class="active">Beranda</a>
            <a href="#">Reservasi</a>
            <a href="#">Laporan</a>
            <a href="#">Fasilitas</a>
            <a href="#">Log Out</a>
        </nav>
    </aside>

    <main id="main">

        <!-- Toast Container (Layer Terpisah) -->
        <div class="toast-container">
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="toast toast-success" id="toast-msg">
                    <span><?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></span>
                    <button class="close-btn" onclick="this.parentElement.remove()">&times;</button>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="toast toast-error" id="toast-msg">
                    <span><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></span>
                    <button class="close-btn" onclick="this.parentElement.remove()">&times;</button>
                </div>
            <?php endif; ?>
        </div>

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
                                <!-- Form Terima (Approve) -->
                                <form action="index.php?page=reservation&action=approve" method="POST" style="display:inline;">
                                    <input type="hidden" name="rsv_id" value="<?= $r['rsv_id'] ?>">
                                    <button type="submit" class="btn_ok">Terima</button>
                                </form>
                                
                                <!-- Tombol Buka Modal Tolak -->
                                <button type="button" class="btn_danger" onclick="openRejectModal('<?= $r['rsv_id'] ?>')">Tolak</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Section 2: Laporan Masuk -->
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
                                <span class="status <?= strtolower($rp['rep_status']) ?>"><?= htmlspecialchars($rp['rep_status']) ?></span>
                                <a href="edit_report.php?id=<?= $rp['rep_id'] ?>"><button type="button">Edit</button></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Section 3: Reservasi diterima/disetujui -->
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
                                <!-- Tombol Buka Modal Batal -->
                                <button type="button" class="btn_danger" onclick="openCancelModal('<?= $app['rsv_id'] ?>')">Batalkan</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

    </main>

    <!-- Modal Penolakan Reservasi -->
    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <h3>Tolak Reservasi</h3>
            <form action="index.php?page=reservation&action=reject" method="POST">
                <input type="hidden" name="rsv_id" id="reject_rsv_id">
                <div style="margin-bottom: 15px;">
                    <label for="rejection_reason">Alasan Penolakan:</label><br>
                    <textarea name="rejection_reason" id="rejection_reason" rows="4" style="width: 100%;" required></textarea>
                </div>
                <div style="text-align: right;">
                    <button type="button" onclick="closeRejectModal()">Batal</button>
                    <button type="submit" class="btn_danger">Kirim Penolakan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Pembatalan Reservasi Disetujui -->
    <div id="cancelModal" class="modal">
        <div class="modal-content">
            <h3>Batalkan Reservasi Disetujui</h3>
            <form action="index.php?page=reservation&action=cancel" method="POST">
                <input type="hidden" name="rsv_id" id="cancel_rsv_id">
                <div style="margin-bottom: 15px;">
                    <label for="cancel_reason">Alasan Pembatalan:</label><br>
                    <textarea name="cancel_reason" id="cancel_reason" rows="4" style="width: 100%;" required placeholder="Tuliskan alasan pembatalan..."></textarea>
                </div>
                <div style="text-align: right;">
                    <button type="button" onclick="closeCancelModal()">Kembali</button>
                    <button type="submit" class="btn_danger">Konfirmasi Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openRejectModal(id) {
            document.getElementById('reject_rsv_id').value = id;
            document.getElementById('rejectModal').style.display = 'block';
        }
        function closeRejectModal() {
            document.getElementById('rejectModal').style.display = 'none';
        }
        function openCancelModal(id) {
            document.getElementById('cancel_rsv_id').value = id;
            document.getElementById('cancelModal').style.display = 'block';
        }

        function closeCancelModal() {
            document.getElementById('cancelModal').style.display = 'none';
        }
    </script>
</body>
</html>