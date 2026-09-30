<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Petugas - Daftar Laporan</title>
    <link rel="stylesheet" href="../../public/css/petugas/dashboard_style.css">
    <style>
        .report-card {
            display: flex;
            align-items: center;
            border: 2px solid #333;
            background: #00008B;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            gap: 20px;
        }
        .report-thumb {
            width: 80px;
            height: 80px;
            background: #fff3a0;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #ccc;
            font-weight: bold;
        }
        .report-info { flex: 1; }
        .report-info p { margin: 4px 0; font-size: 14px; }
        
        /* Modal Popup */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: #7393B3; margin: 10% auto; padding: 25px; width: 450px; border-radius: 8px; border: 2px solid #333; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group select, .form-group textarea { width: 100%; padding: 8px; box-sizing: border-box; }
    </style>
</head>
<body>

    <aside id="sidebar">
    <div id="sidebar_logo">
        <span id="header_logo">★</span>
        <span>Petugas</span>
    </div>
    <nav>
        <a href="index.php?page=dashboard">Beranda</a>
        <a href="#">Reservasi</a>
        <a href="index.php?page=reports" class="active">Laporan</a>
        <a href="#">Fasilitas</a>
        <a href="#">Log Out</a>
    </nav>
</aside>

    <main id="main">
        <div class="report-list">
            <?php if (empty($reports)): ?>
                <p>Belum ada laporan masuk.</p>
            <?php else: ?>
                <?php foreach ($reports as $index => $rp): ?>
                    <div class="report-card">
                        <div class="report-title">Laporan <?= $index + 1 ?></div>
                        <div class="report-thumb">
                            <?php if (!empty($rp['photo'])): ?>
                                <img src="../../public/uploads/<?= htmlspecialchars($rp['photo']) ?>" alt="Foto">
                            <?php else: ?>
                                Foto
                            <?php endif; ?>
                        </div>
                        <div class="report-info">
                            <p><strong>Kategori:</strong> <?= htmlspecialchars(ucwords($rp['category'])) ?></p>
                            <p><strong>Status:</strong> <?= htmlspecialchars(ucfirst($rp['rep_status'])) ?></p>
                            <p><strong>Fasilitas:</strong> <?= htmlspecialchars($rp['fac_name'] ?? 'Tidak Terkait') ?></p>
                        </div>
                        <div>
                            <button type="button" class="btn-edit"
                                    onclick='openEditModal(<?= json_encode($rp, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>Edit</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Pop Up ketika Laporan diubah -->
    <div id="editReportModal" class="modal">
        <div class="modal-content">
            <h3 style="text-align: center; margin-top:0;">Pop up ketika Laporan diubah</h3>
            <form action="index.php?page=reports&action=update" method="POST">
                <input type="hidden" name="rep_id" id="edit_rep_id">

                <!-- Dropdown Status -->
                <div class="form-group">
                    <label for="edit_rep_status">Status</label>
                    <select name="rep_status" id="edit_rep_status" required>
                        <option value="baru">Baru</option>
                        <option value="diproses">Diproses</option>
                        <option value="selesai">Selesai</option>
                        <option value="ditolak">Ditolak</option>
                    </select>
                </div>

                <!-- Dropdown Fasilitas -->
                <div class="form-group">
                    <label for="edit_fac_id">Fasilitas</label>
                    <select name="fac_id" id="edit_fac_id" required>
                        <?php foreach ($facilities as $fac): ?>
                            <option value="<?= $fac['fac_id'] ?>"><?= htmlspecialchars($fac['fac_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Textbox Resolusi -->
                <div class="form-group">
                    <label for="edit_resolution">Textbox resolusi</label>
                    <textarea name="resolution" id="edit_resolution" rows="4" placeholder="Tuliskan resolusi/penanganan laporan..."></textarea>
                </div>

                <div style="text-align: right; gap: 10px; display: flex; justify-content: flex-end;">
                    <button type="button" onclick="closeEditModal()">Batal</button>
                    <button type="submit" class="btn_ok">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(report) {
            document.getElementById('edit_rep_id').value = report.rep_id;
            document.getElementById('edit_rep_status').value = report.rep_status;
            document.getElementById('edit_fac_id').value = report.fac_id;
            document.getElementById('edit_resolution').value = report.resolution || '';
            
            document.getElementById('editReportModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editReportModal').style.display = 'none';
        }
    </script>
</body>
</html>