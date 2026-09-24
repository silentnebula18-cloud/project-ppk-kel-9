<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Petugas - PPK</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>

<div class="dashboard-wrapper" style="display: flex; gap: 20px; padding: 20px;">
    
    <!-- List Reservasi Diproses -->
    <div class="main-content" style="flex: 2; border: 2px solid #000; padding: 15px;">
        <h2>Reservations</h2>
        <hr>

        <!-- Notifikasi Pesan Sukses / Gagal (Gagal karena Jadwal Bentrok) -->
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
                <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_success'])): ?>
            <div style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
                <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($pendingReservations)): ?>
            <p>There are no reservations at the moment...</p>
        <?php else: ?>
            <?php foreach ($pendingReservations as $rsv): ?>
                <div class="card" style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
                    <h3>Fasilitas: <?= htmlspecialchars($rsv['fac_name']) ?></h3>
                    <p><b>Pemohon:</b> <?= htmlspecialchars($rsv['username']) ?></p>
                    <p><b>Tanggal:</b> <?= $rsv['rsv_date'] ?> (<?= $rsv['start_time'] ?> - <?= $rsv['end_time'] ?>)</p>
                    <p><b>Tujuan:</b> <?= htmlspecialchars($rsv['purpose']) ?></p>
                    
                    <!-- Aksi Setujui / Tolak -->
                    <div style="display: flex; gap: 10px; margin-top: 10px;">
                        <!-- Form Setujui -->
                        <form action="index.php?action=approve_reservation" method="POST">
                            <input type="hidden" name="rsv_id" value="<?= $rsv['rsv_id'] ?>">
                            <button type="submit" style="background: green; color: white; border: none; padding: 6px 12px; cursor: pointer;" onclick="return confirm('Setujui reservasi ini?')">Setujui</button>
                        </form>
                        
                        <!-- Tombol Tolak (Memicu Modal Pop-up) -->
                        <button type="button" style="background: red; color: white; border: none; padding: 6px 12px; cursor: pointer;" onclick="openRejectModal('<?= $rsv['rsv_id'] ?>')">Tolak</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="sidebar-right" style="flex: 1; display: flex; flex-direction: column; gap: 20px;">
        
        <!-- List Laporan  -->
        <div class="box-reports" style="border: 2px solid #000; padding: 15px;">
            <h3>Reports</h3>
            <hr>
            <?php if (empty($reports)): ?>
                <p>There are no new reports...</p>
            <?php else: ?>
                <?php foreach ($reports as $rep): ?>
                    <div style="border-bottom: 1px solid #ddd; padding-bottom: 8px; margin-bottom: 8px;">
                        <strong><?= htmlspecialchars($rep['fac_name']) ?></strong> 
                        <span>[Status: <?= $rep['rep_status'] ?>]</span>
                        <p style="margin: 3px 0;"><small>Kategori: <?= $rep['category'] ?></small></p>
                        <a href="/officer/report/edit.php?rep_id=<?= $rep['rep_id'] ?>">Edit Status</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- List Reservasi Diterima  -->
        <div class="box-approved" style="border: 2px solid #000; padding: 15px;">
            <h3>Accepted Reservations</h3>
            <hr>
            <?php if (empty($approvedReservations)): ?>
                <p>You have not accepted any Resevations...</p>
            <?php else: ?>
                <?php foreach ($approvedReservations as $app): ?>
                    <div style="border-bottom: 1px solid #ddd; padding-bottom: 8px; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong><?= htmlspecialchars($app['fac_name']) ?></strong>
                            <br><small><?= $app['rsv_date'] ?> (<?= $app['start_time'] ?>)</small>
                        </div>
                        <a href="/officer/reservation/cancel.php?rsv_id=<?= $app['rsv_id'] ?>" style="background: darkred; color: white; padding: 5px 8px; text-decoration: none;">Cancel</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

</div>

<!-- Modal Form Penolakan (Pop-up SRS 9) -->
<div id="rejectModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999; justify-content:center; align-items:center;">
    <div style="background:#fff; padding:20px; border-radius:5px; width:400px; box-shadow:0 4px 8px rgba(0,0,0,0.2);">
        <h3 style="margin-top:0;">Alasan Penolakan</h3>
        <form action="index.php?action=reject_reservation" method="POST">
            <input type="hidden" name="rsv_id" id="modal_rsv_id">
            <label for="rejection_reason">Masukkan alasan penolakan:</label><br>
            <textarea name="rejection_reason" id="rejection_reason" rows="4" style="width:100%; margin-top:8px; margin-bottom:15px;" required></textarea>
            
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="closeRejectModal()" style="padding:6px 12px; cursor:pointer;">Batal</button>
                <button type="submit" style="background:red; color:white; border:none; padding:6px 12px; cursor:pointer;">Kirim Penolakan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRejectModal(id) {
    document.getElementById('modal_rsv_id').value = id;
    document.getElementById('rejectModal').style.display = 'flex';
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}
</script>

</body>
</html>