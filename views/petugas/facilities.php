<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petugas - Fasilitas</title>
    <link rel="stylesheet" href="../../public/css/petugas/dashboard_style.css">
    <style>
        /* Memaksa wrapper utama menggunakan flexbox */
        body {
            display: flex !important;
            margin: 0;
            padding: 0;
            width: 100vw;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Memastikan main mengambil sisa ruang dari sidebar secara penuh */
        main#main {
            flex: 1 !important;
            width: auto !important;
            float: none !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            padding: 30px !important;
            box-sizing: border-box !important;
            display: block !important;
        }

        /* Grid Fasilitas Rata Kiri */
        .facility-grid {
            display: grid !important;
            grid-template-columns: repeat(3, minmax(200px, 1fr)) !important; /* Pas 3 kolom sesuai wireframe */
            gap: 20px !important;
            margin-top: 20px !important;
            width: 100% !important;
            justify-content: start !important;
            justify-items: stretch !important;
        }

        .facility-card {
            border: 2px solid #000;
            background-color: #0165FC;
            padding: 20px 15px;
            border-radius: 8px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            min-height: 120px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            box-sizing: border-box;
        }

        .facility-card.disabled-card {
            background-color: #c8c8c8;
            border-color: #666;
            opacity: 0.85;
        }

        .facility-title {
            font-size: 16px;
            font-weight: bold;
            color: #222222;
            margin-bottom: 15px;
        }

        .disabled-card .facility-title {
            color: #444444;
        }

        .facility-card select {
            width: 100%;
            padding: 8px 10px;
            font-size: 14px;
            border: 2px solid #000;
            border-radius: 6px;
            cursor: pointer;
            background-color: #f8f9fa;
            color: #000;
            font-weight: 500;
        }

        .badge-disabled {
            font-size: 14px;
            color: #c92a2a;
            font-weight: bold;
            padding: 6px 12px;
        }
    </style>
</head>
<body>

    <aside id="sidebar">
        <div id="sidebar_logo"><span>★</span><span>Petugas</span></div>
        <nav>
            <a href="index.php?page=dashboard">Beranda</a>
            <a href="index.php?page=reports">Laporan</a>
            <a href="index.php?page=facilities" class="active">Fasilitas</a>
        </nav>
    </aside>

    <main id="main">
        <h2>Daftar Fasilitas</h2>

        <?php if (isset($_SESSION['flash_success'])): ?>
            <div style="background:#d4edda; color:#155724; padding:10px; margin-bottom:15px; border-radius:4px;">
                <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div style="background:#f8d7da; color:#721c24; padding:10px; margin-bottom:15px; border-radius:4px;">
                <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
            </div>
        <?php endif; ?>

        <div class="facility-grid">
            <?php foreach ($facilities as $fac): ?>
                <?php $isNonaktif = ($fac['fac_status'] === 'Nonaktif'); ?>
                
                <div class="facility-card <?= $isNonaktif ? 'disabled-card' : '' ?>">
                    <div class="facility-title"><?= htmlspecialchars($fac['fac_name']) ?></div>
                    
                    <?php if ($isNonaktif): ?>
                        <div class="badge-disabled">[Nonaktif]</div>
                    <?php else: ?>
                        <form action="index.php?page=facilities&action=update_status" method="POST" style="width: 100%;">
                            <input type="hidden" name="fac_id" value="<?= $fac['fac_id'] ?>">
                            <select name="fac_status" onchange="this.form.submit()">
                                <option value="Aktif" <?= $fac['fac_status'] === 'Aktif' ? 'selected' : '' ?>>
                                    [Aktif]
                                </option>
                                <option value="Dalam perbaikan" <?= $fac['fac_status'] === 'Dalam perbaikan' ? 'selected' : '' ?>>
                                    [Dalam perbaikan]
                                </option>
                            </select>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

</body>
</html>