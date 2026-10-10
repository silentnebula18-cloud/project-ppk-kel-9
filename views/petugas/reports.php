<?php
if (!function_exists('e')) {
    function e($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('statusSlug')) {
    function statusSlug($status) { return str_replace(' ', '-', strtolower(trim((string) $status))); }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Petugas - Daftar Laporan</title>
    <link rel="stylesheet" href="../../public/css/petugas/dashboard_style.css?v=<?= @filemtime(__DIR__ . '/../../public/css/petugas/dashboard_style.css') ?: 1 ?>">
</head>
<body id="petugas_body">

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
            <a class="nav_item" href="index.php?page=dashboard"><span class="nav_icon">🏠</span>Beranda</a>
            <a class="nav_item active" href="index.php?page=reports"><span class="nav_icon">🛠️</span>Laporan</a>
            <a class="nav_item" href="index.php?page=facilities"><span class="nav_icon">🏢</span>Fasilitas</a>
        </nav>

        <main id="main_content">
            <div class="page_head">
                <h3>Daftar laporan</h3>
                <p>Tinjau laporan kerusakan dan perbarui statusnya.</p>
            </div>

            <div class="report_list">
                <?php if (empty($reports)): ?>
                    <p class="empty_note">Belum ada laporan masuk.</p>
                <?php else: ?>
                    <?php foreach ($reports as $index => $rp): ?>
                        <?php $slug = statusSlug($rp['rep_status']); ?>
                        <div class="report_card rep_<?= e($slug) ?>">
                            <div class="report_thumb">
                                <?php
                                    // Foto: storage/reports_img/{rep_id}.webp (disajikan lewat ReportController::image)
                                    $photoId = basename((string) $rp['rep_id']);
                                    $hasPhoto = is_file(__DIR__ . '/../../storage/reports_img/' . $photoId . '.webp');
                                ?>
                                <?php if ($hasPhoto): ?>
                                    <img src="index.php?page=reports&action=image&id=<?= urlencode($photoId) ?>" alt="Foto laporan <?= $index + 1 ?>" loading="lazy">
                                <?php else: ?>
                                    Tanpa foto
                                <?php endif; ?>
                            </div>
                            <div class="report_info">
                                <h4 class="report_title">Laporan <?= $index + 1 ?></h4>
                                <p><strong>Kategori:</strong> <?= e(ucwords($rp['category'])) ?></p>
                                <p><strong>Fasilitas:</strong> <?= e($rp['fac_name'] ?? 'Tidak terkait') ?></p>
                                <p><strong>Status:</strong> <span class="status_badge status_<?= e($slug) ?>"><?= e(ucfirst($rp['rep_status'])) ?></span></p>
                            </div>
                            <div class="actions">
                                <button type="button" class="js-edit"
                                        data-report="<?= e(json_encode($rp, JSON_UNESCAPED_UNICODE)) ?>">Edit</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Modal ubah laporan -->
    <div id="editReportModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="editTitle">
        <div class="modal_content modal_edit">
            <h3 id="editTitle">Ubah laporan</h3>
            <form action="index.php?page=reports&action=update" method="POST">
                <input type="hidden" name="rep_id" id="edit_rep_id">

                <div class="form_group">
                    <label for="edit_rep_status">Status</label>
                    <select name="rep_status" id="edit_rep_status" required>
                        <option value="baru">Baru</option>
                        <option value="diproses">Diproses</option>
                        <option value="selesai">Selesai</option>
                        <option value="ditolak">Ditolak</option>
                    </select>
                </div>

                <div class="form_group">
                    <label>Fasilitas</label>
                    <p id="edit_fac_name" class="readonly_value"></p>
                </div>

                <div class="form_group">
                    <label for="edit_resolution_note">Catatan penanganan</label>
                    <textarea name="resolution_note" id="edit_resolution_note" rows="4" placeholder="Tuliskan resolusi atau penanganan laporan..."></textarea>
                </div>

                <div class="modal_actions">
                    <button type="button" class="btn_secondary js-close">Batal</button>
                    <button type="submit" class="btn_ok">Simpan perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleSidebar() { document.getElementById('sidebar').classList.toggle('hidden'); }

        function openEditModal(report) {
            document.getElementById('edit_rep_id').value = report.rep_id;
            document.getElementById('edit_rep_status').value = report.rep_status;
            document.getElementById('edit_fac_name').textContent = report.fac_name || 'Tidak terkait';
            document.getElementById('edit_resolution_note').value = report.resolution_note || '';
            const modal = document.getElementById('editReportModal');
            modal.style.display = 'block';
            document.getElementById('edit_rep_status').focus();
        }
        function closeEditModal() { document.getElementById('editReportModal').style.display = 'none'; }

        document.addEventListener('click', e => {
            const edit = e.target.closest('.js-edit');
            if (edit) openEditModal(JSON.parse(edit.dataset.report));
            if (e.target.closest('.js-close') || e.target.classList.contains('modal')) closeEditModal();
        });
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeEditModal(); });

        // Buka modal otomatis jika datang dari dashboard (?edit=ID)
        const editId = new URLSearchParams(location.search).get('edit');
        if (editId) {
            document.querySelectorAll('.js-edit').forEach(btn => {
                const report = JSON.parse(btn.dataset.report);
                if (String(report.rep_id) === editId) openEditModal(report);
            });
        }

        document.querySelectorAll('.toast').forEach(t => setTimeout(() => t.remove(), 5000));
    </script>
</body>
</html>