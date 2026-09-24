<?php
require_once __DIR__ . "/../../app/models/account_model.php";

$pending = getPendingAccounts($conn);
?>
<body id="admin_body">
    <div id="dashboard_header">
        <span id="header_logo" onclick="toggleSidebar()">★</span>
        <span id="header_title">Dashboard Admin</span>
        <button id="logout_btn">LogOut</button>
    </div>

    <div id="dashboard_layout">
        <div id="sidebar" class="hidden">
            <a class="nav_item" href="admin_beranda.php"><span class="nav_icon">🏠</span>Beranda</a>
            <a class="nav_item active" href="kelola_akun.php"><span class="nav_icon">👤</span>Kelola akun</a>
            <a class="nav_item" href="kelola_fasilitas.php"><span class="nav_icon">🏢</span>Kelola fasilitas</a>
        </div>

        <div id="main_content">
            <div id="content_top_bar">
                <h3>Daftar Akun Pending</h3>
                <a href="kelola_akun_baru.php"><button>Bikin akun baru</button></a>
            </div>

            <input type="text" placeholder="Search by Username/Email" oninput="filterTable(this, 'akun_table')">

            <table id="akun_table">
                <tr>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Tanggal daftar</th>
                    <th>Aksi</th>
                </tr>
                <?php if (empty($pending)): ?>
                <tr>
                    <td colspan="6">Tidak ada akun pending.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($pending as $acc): ?>
                    <tr>
                        <td><?= htmlspecialchars($acc['unv_username']) ?></td>
                        <td>••••••••</td>
                        <td><?= htmlspecialchars($acc['unv_email']) ?></td>
                        <td>Belum ditentukan</td>
                        <td><?= htmlspecialchars($acc['unv_registered_at']) ?></td>
                        <td>
                            <form class="admit_form" method="post" action="../../app/controllers/account_controller.php"
                                  onsubmit="return confirm('Yakin mau admit akun ini?');">
                                <input type="hidden" name="action" value="admit">
                                <input type="hidden" name="unv_id" value="<?= htmlspecialchars($acc['unv_id']) ?>">
                                <select name="role" required>
                                    <option value=""> Role </option>
                                    <option value="pengguna">Pengguna</option>
                                    <option value="petugas">Petugas</option>
                                    <option value="admin">Admin</option>
                                </select>
                                <button type="submit">Admit</button>
                            </form>

                            <form class="reject_form" method="post" action="../../app/controllers/account_controller.php"
                                  onsubmit="return confirm('Yakin mau tolak akun ini? Data pendaftaran akan dihapus permanen.');">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="unv_id" value="<?= htmlspecialchars($acc['unv_id']) ?>">
                                <button id="reject_btn" type="submit">Tolak</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </table>
        </div>
    </div>
</body>

<link rel="stylesheet" href="../../public/css/admin/admin_style.css">
<script src="../../public/js/admin/sidebar.js"></script>
<script src="../../public/js/admin/table_actions.js"></script>