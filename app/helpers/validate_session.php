<?php
date_default_timezone_set('Asia/Jakarta');
function check_session_valid($expected_role) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['user_id']) || empty($_SESSION['role']) || $_SESSION['role'] !== $expected_role) {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        header("Location: /views/auth/login.html");
        exit;
    }
}