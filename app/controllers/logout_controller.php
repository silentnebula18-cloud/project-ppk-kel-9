<?php

// Logout: hapus sesi lalu arahkan kembali ke halaman login
session_start();

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

session_destroy();
$loginPage = "../../views/auth/login.html";

header("Location: " . $loginPage);
exit;
?>