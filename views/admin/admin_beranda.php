<?php

require_once __DIR__ . '/../../app/helpers/validate_session.php';
check_session_valid('admin');

require_once __DIR__ . "/../../app/models/account_model.php";
require_once __DIR__ . "/../../app/models/facility_model.php";
require_once __DIR__ . "/../../app/models/dashboard_model.php";

date_default_timezone_set('Asia/Jakarta');

// Kartu ringkasan
$pendingCount = countPendingAccounts($conn);
$petugasCount = countUsersByRole($conn, 'petugas');
$fasilitasCount = countFacilitiesByStatus($conn, 'Aktif');
$laporanBaruCount = countReportsByStatus($conn, 'baru');
$reservasiMenungguCount = countReservationsByStatus($conn, 'proses pengajuan');

// Data panel
$statusCounts = getFacilityStatusCounts($conn);
$topFasilitas = getTopReservedFacilities($conn, 5);
$buildings = getFacilitiesByBuilding($conn);
$typeIcons = ['Ruang Kelas' => '🏫', 'Aula' => '🏛️', 'Laboratorium' => '🔬', 'Alat' => '🎥', 'Lapangan' => '⚽'];
$maxReservasi = !empty($topFasilitas) ? (int) $topFasilitas[0]['total'] : 0;

// Sapaan, tanggal, & maskot sesuai jam
$hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$jam = (int) date('G');
if ($jam < 11) {
    $sapaan = 'Selamat pagi';
    $maskot = '☀️';
} elseif ($jam < 15) {
    $sapaan = 'Selamat siang';
    $maskot = '🌤️';
} elseif ($jam < 18) {
    $sapaan = 'Selamat sore';
    $maskot = '🌇';
} else {
    $sapaan = 'Selamat malam';
    $maskot = '🌙';
}
$tanggal = $hari[(int) date('w')] . ', ' . date('j') . ' ' . $bulan[(int) date('n')] . ' ' . date('Y');

// Daftar tugas hari ini (dibuat otomatis dari data)
$todos = [];
if ($pendingCount > 0) {
    $todos[] = ['key' => 'akun', 'text' => "Verifikasi $pendingCount akun pending", 'href' => 'kelola_akun.php'];
}
if ($laporanBaruCount > 0) {
    $todos[] = ['key' => 'laporan', 'text' => "Tindak lanjuti $laporanBaruCount laporan kerusakan baru", 'href' => null];
}
if ($reservasiMenungguCount > 0) {
    $todos[] = ['key' => 'reservasi', 'text' => "Cek $reservasiMenungguCount reservasi menunggu", 'href' => null];
}
if ($statusCounts['Dalam perbaikan'] > 0) {
    $todos[] = ['key' => 'perbaikan', 'text' => "Pantau {$statusCounts['Dalam perbaikan']} fasilitas dalam perbaikan", 'href' => 'kelola_fasilitas.php'];
}
$todos[] = ['key' => 'cek', 'text' => 'Lihat ringkasan hari ini', 'href' => null];

$statusEmoji = ['Aktif' => '🟢', 'Dalam perbaikan' => '🟡', 'Nonaktif' => '🔴'];

// Ubah status jadi nama class: "Dalam perbaikan" -> "dalam-perbaikan"
function statusSlug($status)
{
    return str_replace(' ', '-', strtolower($status));
}
?>
<body id="admin_body">
    <div id="dashboard_header">
        <span id="header_logo" onclick="toggleSidebar()">★</span>
        <span id="header_brand">Campu<em>Space</em></span>
        <span id="header_title">Dashboard Admin</span>
        <a href="../../app/controllers/logout_controller.php"><button id="logout_btn">LogOut</button></a>
    </div>

    <div id="dashboard_layout">
        <div id="sidebar" class="hidden">
            <a class="nav_item active" href="admin_beranda.php"><span class="nav_icon">🏠</span>Beranda</a>
            <a class="nav_item" href="kelola_akun.php"><span class="nav_icon">👤</span>Kelola akun</a>
            <a class="nav_item" href="kelola_fasilitas.php"><span class="nav_icon">🏢</span>Kelola fasilitas</a>
        </div>

        <div id="main_content">
            <div id="welcome_bar">
                <div id="welcome_left">
                    <button id="mascot" type="button" title="Klik aku!"><?= $maskot ?></button>
                    <div>
                        <h3><?= $sapaan ?>, Admin <span id="wave">👋</span></h3>
                        <p id="welcome_date"><?= $tanggal ?> · <span id="live_clock"></span></p>
                    </div>
                    <div id="mascot_bubble" class="bubble_hidden">Klik aku buat semangat! ✨</div>
                </div>
                <div id="quick_actions">
                    <a href="kelola_fasilitas_baru.php"><button>+ Tambah fasilitas</button></a>
                    <a href="kelola_akun.php"><button class="btn_secondary">Verifikasi akun</button></a>
                </div>
            </div>

            <div id="summary_cards">
                <a class="summary_card<?= $pendingCount > 0 ? ' card_alert' : '' ?>" href="kelola_akun.php">
                    <span class="card_icon">📝</span>
                    <p class="card_label">Akun pending</p>
                    <span class="card_value" data-target="<?= $pendingCount ?>"><?= $pendingCount ?></span>
                </a>
                <div class="summary_card<?= $laporanBaruCount > 0 ? ' card_alert' : '' ?>">
                    <span class="card_icon">🛠️</span>
                    <p class="card_label">Laporan kerusakan baru</p>
                    <span class="card_value" data-target="<?= $laporanBaruCount ?>"><?= $laporanBaruCount ?></span>
                </div>
                <div class="summary_card">
                    <span class="card_icon">📅</span>
                    <p class="card_label">Reservasi menunggu</p>
                    <span class="card_value" data-target="<?= $reservasiMenungguCount ?>"><?= $reservasiMenungguCount ?></span>
                </div>
                <div class="summary_card">
                    <span class="card_icon">🧑‍💼</span>
                    <p class="card_label">Total petugas</p>
                    <span class="card_value" data-target="<?= $petugasCount ?>"><?= $petugasCount ?></span>
                </div>
                <a class="summary_card" href="kelola_fasilitas.php">
                    <span class="card_icon">🏢</span>
                    <p class="card_label">Fasilitas aktif</p>
                    <span class="card_value" data-target="<?= $fasilitasCount ?>"><?= $fasilitasCount ?></span>
                </a>
            </div>

            <div id="status_strip">
                <?php foreach ($statusCounts as $label => $count): ?>
                    <a class="status_pill" href="kelola_fasilitas.php">
                        <?= $statusEmoji[$label] ?> <?= htmlspecialchars($label) ?>
                        <strong><?= $count ?></strong>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="panel" id="campus_map">
                <div class="map_head">
                    <h4>Denah kampus mini</h4>
                    <div class="map_legend">
                        <span><i class="map_dot dot_aktif"></i>Aktif</span>
                        <span><i class="map_dot dot_dalam-perbaikan"></i>Dalam perbaikan</span>
                        <span><i class="map_dot dot_nonaktif"></i>Nonaktif</span>
                    </div>
                </div>
                <?php if (empty($buildings)): ?>
                    <p class="empty_note">Belum ada data fasilitas.</p>
                <?php else: ?>
                    <p class="map_hint">Klik sebuah gedung untuk melihat isinya.</p>
                    <div class="map_body">
                        <div class="map_grid">
                            <?php $i = 0; foreach ($buildings as $name => $items): ?>
                                <button type="button" class="map_tile<?= $i === 0 ? ' active' : '' ?>" data-target="map_b<?= $i ?>">
                                    <span class="map_tile_name"><?= htmlspecialchars($name) ?></span>
                                    <span class="map_dots">
                                        <?php foreach ($items as $it): ?>
                                            <i class="map_dot dot_<?= statusSlug($it['fac_status']) ?>" title="<?= htmlspecialchars($it['fac_name'] . ' · ' . $it['fac_status']) ?>"></i>
                                        <?php endforeach; ?>
                                    </span>
                                    <span class="map_tile_count"><?= count($items) ?> fasilitas</span>
                                </button>
                            <?php $i++; endforeach; ?>
                        </div>

                        <div class="map_detail_wrap">
                            <?php $i = 0; foreach ($buildings as $name => $items): ?>
                                <div class="map_detail" id="map_b<?= $i ?>"<?= $i === 0 ? '' : ' hidden' ?>>
                                    <h5><?= htmlspecialchars($name) ?></h5>
                                    <ul>
                                        <?php foreach ($items as $it): ?>
                                            <li>
                                                <span class="map_item_name">
                                                    <?= $typeIcons[$it['type']] ?? '📍' ?> <?= htmlspecialchars($it['fac_name']) ?>
                                                    <small><?= htmlspecialchars($it['location']) ?> · <?= (int) $it['capacity'] ?> orang</small>
                                                </span>
                                                <span class="status_badge status_<?= statusSlug($it['fac_status']) ?>"><?= ucwords(htmlspecialchars($it['fac_status'])) ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php $i++; endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div id="dashboard_panels">
                <div class="panel panel_note" id="todo_panel" data-date="<?= date('Y-m-d') ?>">
                    <h4>Tugas hari ini</h4>
                    <div class="progress_track"><div id="todo_progress" class="progress_fill"></div></div>
                    <p id="todo_status" class="empty_note"></p>
                    <ul class="todo_list">
                        <?php foreach ($todos as $todo): ?>
                            <li>
                                <label>
                                    <input type="checkbox" data-key="<?= $todo['key'] ?>">
                                    <span class="todo_text"><?= htmlspecialchars($todo['text']) ?></span>
                                </label>
                                <?php if ($todo['href']): ?>
                                    <a class="todo_go" href="<?= $todo['href'] ?>">Buka →</a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="panel panel_leaderboard">
                    <h4>Fasilitas paling sering direservasi</h4>
                    <?php if (empty($topFasilitas)): ?>
                        <p class="empty_note">Belum ada reservasi.</p>
                    <?php else: ?>
                        <ol class="top_list">
                            <?php foreach ($topFasilitas as $i => $row): ?>
                                <?php $width = $maxReservasi > 0 ? round($row['total'] / $maxReservasi * 100) : 0; ?>
                                <li>
                                    <div class="top_row">
                                        <span><?= ['🥇', '🥈', '🥉', '4️⃣', '5️⃣'][$i] ?> <?= htmlspecialchars($row['fac_name']) ?></span>
                                        <strong><?= (int) $row['total'] ?>x</strong>
                                    </div>
                                    <div class="top_bar"><div class="top_bar_fill" style="width: <?= $width ?>%"></div></div>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>

<link rel="stylesheet" href="../../public/css/admin/admin_style.css?v=<?= filemtime(__DIR__ . '/../../public/css/admin/admin_style.css') ?>">
<script src="../../public/js/admin/sidebar.js"></script>
<script src="../../public/js/admin/beranda.js?v=<?= filemtime(__DIR__ . '/../../public/js/admin/beranda.js') ?>"></script>