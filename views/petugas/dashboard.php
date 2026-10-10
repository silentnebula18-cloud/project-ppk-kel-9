<?php
date_default_timezone_set('Asia/Jakarta');

// ===== Helper tampilan =====
if (!function_exists('e')) {
    function e($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('statusSlug')) {
    // "Dalam perbaikan" -> "dalam-perbaikan"
    function statusSlug($status) { return str_replace(' ', '-', strtolower(trim((string) $status))); }
}
if (!function_exists('facilityBadge')) {
    function facilityBadge($status) {
        if ($status === 'Dalam perbaikan') {
            return '<span class="fac_badge fac_dalam-perbaikan">⚠️ Dalam perbaikan</span>';
        }
        if ($status === 'Nonaktif') {
            return '<span class="fac_badge fac_nonaktif">🚫 Nonaktif</span>';
        }
        return '';
    }
}
if (!function_exists('timeRange')) {
    function timeRange($start, $end) { return date('H:i', strtotime($start)) . '–' . date('H:i', strtotime($end)); }
}

// ===== Sapaan & tanggal =====
$hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$jam = (int) date('G');
$sapaan = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 18 ? 'Selamat sore' : 'Selamat malam'));
$tanggal = $hari[(int) date('w')] . ', ' . date('j') . ' ' . $bulan[(int) date('n')] . ' ' . date('Y');

// ===== Ringkasan (dihitung dari data yang sudah ada) =====
$pendingCount = count($pendingReservations ?? []);
$reportCount = count($reports ?? []);
$approvedCount = count($approvedReservations ?? []);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Petugas - Dashboard</title>
    <link rel="stylesheet" href="../../public/css/petugas/dashboard_style.css?v=<?= @filemtime(__DIR__ . '/../../public/css/petugas/dashboard_style.css') ?: 1 ?>">
</head>
<body id="petugas_body">

    <!-- Toast -->
    <div class="toast_container">
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="toast toast_success" role="status">
                <span><?= e($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></span>
                <button type="button" class="close_btn" aria-label="Tutup" onclick="this.parentElement.remove()">&times;</button>
            </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="toast toast_error" role="alert">
                <span><?= e($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></span>
                <button type="button" class="close_btn" aria-label="Tutup" onclick="this.parentElement.remove()">&times;</button>
            </div>
        <?php endif; ?>
    </div>

    <div id="dashboard_header">
        <button type="button" id="header_logo" aria-label="Tampilkan atau sembunyikan menu" onclick="toggleSidebar()">★</button>
        <span id="header_brand">Campu<em>Space</em></span>
        <span id="header_title">Dashboard Petugas</span>
        <a href="../../app/controllers/logout_controller.php"><button type="button" id="logout_btn">Keluar</button></a>
    </div>

    <div id="dashboard_layout">
        <nav id="sidebar" aria-label="Menu utama">
            <a class="nav_item active" href="index.php?page=dashboard"><span class="nav_icon">🏠</span>Beranda</a>
            <a class="nav_item" href="index.php?page=reports"><span class="nav_icon">🛠️</span>Laporan</a>
            <a class="nav_item" href="index.php?page=facilities"><span class="nav_icon">🏢</span>Fasilitas</a>
        </nav>

        <main id="main_content">
            <div id="welcome_bar">
                <h3><?= e($sapaan) ?>, Petugas 👋</h3>
                <p id="welcome_date"><?= e($tanggal) ?></p>
            </div>

            <div id="summary_cards">
                <div class="summary_card<?= $pendingCount > 0 ? ' card_alert' : '' ?>">
                    <span class="card_icon">📅</span>
                    <p class="card_label">Reservasi menunggu</p>
                    <span class="card_value"><?= $pendingCount ?></span>
                </div>
                <a class="summary_card" href="index.php?page=reports">
                    <span class="card_icon">🛠️</span>
                    <p class="card_label">Laporan masuk</p>
                    <span class="card_value"><?= $reportCount ?></span>
                </a>
                <div class="summary_card">
                    <span class="card_icon">✅</span>
                    <p class="card_label">Reservasi disetujui</p>
                    <span class="card_value"><?= $approvedCount ?></span>
                </div>
            </div>

            <div id="dashboard_panels">

                <!-- Reservasi menunggu persetujuan -->
                <section class="panel" id="panel_proses">
                    <div class="panel_header">
                        <h4>Reservasi menunggu persetujuan</h4>
                    </div>
                    <div class="panel_body">
                        <?php if (empty($pendingReservations)): ?>
                            <p class="empty_note">Tidak ada reservasi yang menunggu.</p>
                        <?php else: ?>
                            <?php foreach ($pendingReservations as $r): ?>
                                <div class="item">
                                    <div>
                                        <div class="item_title">
                                            <?= e($r['fac_name']) ?><?= facilityBadge($r['fac_status'] ?? '') ?>
                                        </div>
                                        <div class="item_meta">
                                            <?= e($r['username']) ?> · <?= e(date('d M Y', strtotime($r['rsv_date']))) ?> · <?= e(timeRange($r['start_time'], $r['end_time'])) ?>
                                        </div>
                                    </div>
                                    <div class="actions">
                                        <form action="index.php?page=reservation&action=approve" method="POST" class="js-approve">
                                            <input type="hidden" name="rsv_id" value="<?= e($r['rsv_id']) ?>">
                                            <button type="submit" class="btn_ok">Terima</button>
                                        </form>
                                        <button type="button" class="btn_danger js-reject" data-id="<?= e($r['rsv_id']) ?>">Tolak</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

                <div class="panel_column">

                    <!-- Laporan masuk -->
                    <section class="panel" id="panel_laporan">
                        <div class="panel_header">
                            <h4>Laporan masuk</h4>
                            <a href="index.php?page=reports">Lihat semua</a>
                        </div>
                        <div class="panel_body">
                            <?php if (empty($reports)): ?>
                                <p class="empty_note">Tidak ada laporan baru atau diproses.</p>
                            <?php else: ?>
                                <?php foreach ($reports as $rp): ?>
                                    <div class="item">
                                        <div>
                                            <div class="item_title">[<?= e(ucwords($rp['category'])) ?>] <?= e($rp['rep_desc']) ?></div>
                                            <div class="item_meta"><?= e($rp['fac_name']) ?> · <?= e($rp['username']) ?></div>
                                        </div>
                                        <div class="actions">
                                            <span class="status_badge status_<?= e(statusSlug($rp['rep_status'])) ?>"><?= e(ucfirst($rp['rep_status'])) ?></span>
                                            <a class="btn btn_secondary" href="index.php?page=reports&edit=<?= urlencode($rp['rep_id']) ?>">Edit</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </section>

                    <!-- Reservasi disetujui -->
                    <section class="panel" id="panel_diterima">
                        <div class="panel_header">
                            <h4>Reservasi disetujui</h4>
                        </div>
                        <div class="panel_body">
                            <?php if (empty($approvedReservations)): ?>
                                <p class="empty_note">Belum ada reservasi disetujui mendatang.</p>
                            <?php else: ?>
                                <?php foreach ($approvedReservations as $app): ?>
                                    <?php $needsAttention = in_array($app['fac_status'] ?? '', ['Nonaktif', 'Dalam perbaikan'], true); ?>
                                    <div class="item<?= $needsAttention ? ' item_alert' : '' ?>">
                                        <div>
                                            <div class="item_title">
                                                <?= e($app['fac_name']) ?><?= facilityBadge($app['fac_status'] ?? '') ?>
                                            </div>
                                            <div class="item_meta">
                                                <?= e($app['username']) ?> · <?= e(date('d M Y', strtotime($app['rsv_date']))) ?> · <?= e(timeRange($app['start_time'], $app['end_time'])) ?>
                                            </div>
                                        </div>
                                        <div class="actions">
                                            <button type="button" class="btn_danger js-cancel" data-id="<?= e($app['rsv_id']) ?>">Batalkan</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </section>

                </div>
            </div>
        </main>
    </div>

    <!-- Modal tolak -->
    <div id="rejectModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="rejectTitle">
        <div class="modal_content">
            <h3 id="rejectTitle">Tolak reservasi</h3>
            <form action="index.php?page=reservation&action=reject" method="POST">
                <input type="hidden" name="rsv_id" id="reject_rsv_id">
                <label for="rejection_reason">Alasan penolakan</label>
                <textarea name="rejection_reason" id="rejection_reason" rows="4" required></textarea>
                <div class="modal_actions">
                    <button type="button" class="btn_secondary js-close">Batal</button>
                    <button type="submit" class="btn_danger">Kirim penolakan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal batalkan -->
    <div id="cancelModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="cancelTitle">
        <div class="modal_content">
            <h3 id="cancelTitle">Batalkan reservasi disetujui</h3>
            <form action="index.php?page=reservation&action=cancel" method="POST">
                <input type="hidden" name="rsv_id" id="cancel_rsv_id">
                <label for="cancel_reason">Alasan pembatalan</label>
                <textarea name="cancel_reason" id="cancel_reason" rows="4" required placeholder="Tuliskan alasan pembatalan..."></textarea>
                <div class="modal_actions">
                    <button type="button" class="btn_secondary js-close">Kembali</button>
                    <button type="submit" class="btn_danger">Batalkan reservasi</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('hidden');
        }

        function openModal(modalId, inputId, rsvId) {
            document.getElementById(inputId).value = rsvId;
            const modal = document.getElementById(modalId);
            modal.style.display = 'block';
            modal.querySelector('textarea').focus();
        }
        function closeModals() {
            document.querySelectorAll('.modal').forEach(m => m.style.display = 'none');
        }

        document.addEventListener('click', e => {
            const btn = e.target.closest('button');
            if (btn && btn.classList.contains('js-reject')) openModal('rejectModal', 'reject_rsv_id', btn.dataset.id);
            if (btn && btn.classList.contains('js-cancel')) openModal('cancelModal', 'cancel_rsv_id', btn.dataset.id);
            if (btn && btn.classList.contains('js-close')) closeModals();
            if (e.target.classList.contains('modal')) closeModals();
        });
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModals(); });

        // Cegah klik ganda saat menerima reservasi
        document.querySelectorAll('form.js-approve').forEach(f => {
            f.addEventListener('submit', () => { f.querySelector('button').disabled = true; });
        });

        // Toast hilang otomatis
        document.querySelectorAll('.toast').forEach(t => setTimeout(() => t.remove(), 5000));
    </script>
</body>
</html>