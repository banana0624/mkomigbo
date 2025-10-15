<?php
declare(strict_types=1);

/**
 * Session-based auth helpers for Admins (and a light Contributor hook).
 * Uses the admins table helpers from admin_functions.php.
 */

require_once __DIR__ . '/admin_functions.php';

function is_admin_logged_in(): bool {
    return isset($_SESSION['admin_id']) && (int)$_SESSION['admin_id'] > 0;
}
function current_admin_id(): ?int {
    return is_admin_logged_in() ? (int)$_SESSION['admin_id'] : null;
}
function current_admin(): ?array {
    $id = current_admin_id();
    return $id ? find_admin_by_id($id) : null;
}

function log_in_admin(array $admin): void {
    $_SESSION['admin_id'] = (int)$admin['id'];
    $_SESSION['admin_username'] = $admin['username'] ?? '';
    // renew session id
    if (function_exists('session_regenerate_id')) { @session_regenerate_id(true); }
}

function log_out_admin(): void {
    unset($_SESSION['admin_id'], $_SESSION['admin_username']);
}

function require_admin_login(): void {
    if (!is_admin_logged_in()) {
        // Save where we were trying to go
        $_SESSION['return_to'] = $_SERVER['REQUEST_URI'] ?? '/';
        redirect_to('/staff/login.php');
    }
}

/* ----- Optional: Contributor hooks (expand later) ----- */
function is_contributor_logged_in(): bool {
    return isset($_SESSION['contrib_id']) && (int)$_SESSION['contrib_id'] > 0;
}
function require_contributor_login(): void {
    if (!is_contributor_logged_in()) {
        $_SESSION['return_to'] = $_SERVER['REQUEST_URI'] ?? '/';
        redirect_to('/contributors/login.php');
    }
}
