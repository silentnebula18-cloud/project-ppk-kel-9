<?php
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
            <h3>Buat Akun Baru (Petugas/Pengguna)</h3>
            <?php if (isset($_GET['error'])): ?>
            <p style="color:red">Akun gagal dibuat: cek kembali semua field (username/email mungkin sudah dipakai).</p>
            <?php endif; ?>

            <form method="post" action="../../app/controllers/account_controller.php">
                <input type="hidden" name="action" value="create">

                <div class="form_row">
                    <label>Username:</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form_row">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form_row">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form_row">
                    <label>Roles:</label>
                    <select name="role" required>
                        <option value="">--</option>
                        <option value="petugas">Petugas</option>
                        <option value="pengguna">Pengguna</option>
                    </select>
                </div>

                <button type="submit">Create Account</button>
            </form>
        </div>
    </div>
</body>

<link rel="stylesheet" href="../../public/css/admin/admin_style.css">
<script src="../../public/js/admin/sidebar.js"></script>