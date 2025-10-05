<?php
// project-root/private/functions/auth_functions.php

// Authentication helpers

function log_in_user($user) {
    $_SESSION['user_id'] = $user['id'] ?? null;
    $_SESSION['username'] = $user['username'] ?? null;
    $_SESSION['role'] = $user['role'] ?? 'viewer';
    return true;
}

function log_out_user() {
    unset($_SESSION['user_id']);
    unset($_SESSION['username']);
    unset($_SESSION['role']);
    session_destroy();
    return true;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if(!is_logged_in()) {
        header("Location: " . url_for('/auth/login.php'));
        exit;
    }
}
