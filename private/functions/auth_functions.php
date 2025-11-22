<?php
// project-root/private/assets/auth_functions.php
declare(strict_types=1);

/**
 * Compatibility / convenience layer over
 * project-root/private/functions/auth.php
 *
 * This file should NOT redefine the main logic — that stays in auth.php.
 * We only provide shorter names and bridges.
 */

require_once PRIVATE_PATH . '/functions/auth.php';

// ---------------------------------------------------------------------
// login state
// ---------------------------------------------------------------------
if (!function_exists('is_logged_in')) {
    function is_logged_in(): bool {
        return current_user() !== null;
    }
}

if (!function_exists('log_out_user')) {
    function log_out_user(): void {
        auth_logout();
    }
}

// ---------------------------------------------------------------------
// bridges to current user fields
// ---------------------------------------------------------------------
if (!function_exists('current_user_id_safe')) {
    function current_user_id_safe(): ?int {
        return current_user_id();
    }
}

if (!function_exists('current_username_safe')) {
    function current_username_safe(): ?string {
        return current_username();
    }
}

if (!function_exists('current_user_role_safe')) {
    function current_user_role_safe(): ?string {
        return current_user_role();
    }
}

// ---------------------------------------------------------------------
// require_* short forms (use the ones from auth.php)
// ---------------------------------------------------------------------
if (!function_exists('require_login_safe')) {
    function require_login_safe(): void {
        auth_require_login();
    }
}

// Keep the name from my earlier sample, but make it call the merged one
if (!function_exists('require_role')) {
    /**
     * Usage: require_role(['admin','superadmin']);
     */
    function require_role(array $allowed_roles): void {
        auth_require_role($allowed_roles);
    }
}

if (!function_exists('require_level')) {
    function require_level(int $min_level): void {
        auth_require_level($min_level);
    }
}

// ---------------------------------------------------------------------
// redirect helper
// ---------------------------------------------------------------------
if (!function_exists('redirect_after_login')) {
    /**
     * After login, send user to proper place.
     * This just defers to auth_redirect_after_login() in the main file.
     */
    function redirect_after_login(?array $user = null, ?string $fallback = null): void {
        auth_redirect_after_login($user, $fallback);
    }
}
