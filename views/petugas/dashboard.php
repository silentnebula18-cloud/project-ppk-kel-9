<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Petugas - PPK</title>
    <link rel="stylesheet" href="/public/css/style.css"> <!-- Sesuaikan file CSS kamu -->
</head>
<body>

<div class="dashboard-wrapper" style="display: flex; gap: 20px; padding: 20px;">
    
    <!-- List Reservasi Diproses -->
    <div class="main-content" style="flex: 2; border: 2px solid #000; padding: 15px;">
        <h2>Reservations</h2>
        <hr>
        
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
                        <form action="/officer/reservation/approve.php" method="POST">
                            <input type="hidden" name="rsv_id" value="<?= $rsv['rsv_id'] ?>">
                            <button type="submit" style="background: green; color: white;">Setujui</button>
                        </form>
                        
                        <form action="/officer/reservation/reject.php" method="POST">
                            <input type="hidden" name="rsv_id" value="<?= $rsv['rsv_id'] ?>">
                            <button type="submit" style="background: red; color: white;">Tolak</button>
                        </form>
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

</body>
</html>