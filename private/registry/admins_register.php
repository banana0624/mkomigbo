<?php
/**
 * project-root/private/registry/admins_register.php
 *
 * Registry for static/admin bootstrap entries (optional).
 * In production you’ll normally store admins in DB; this registry can seed defaults
 * or power CLI bootstrap scripts (e.g., create initial superadmin).
 *
 * Exposes:
 *  - $ADMINS_REGISTER
 *  - admins_all(), admin_by_username()
 */

if (defined('MK_REGISTRY_ADMINS_LOADED')) { return; }
define('MK_REGISTRY_ADMINS_LOADED', true);

$ADMINS_REGISTER = [
    // Example (comment out in production):
    // [
    //   'username' => 'superadmin',
    //   'email'    => 'admin@example.com',
    //   'role'     => 'superadmin',  // superadmin|admin|editor
    //   'status'   => 'active',      // active|disabled
    // ],
];

function admins_all(): array {
    global $ADMINS_REGISTER;
    return $ADMINS_REGISTER;
}

function admin_by_username(string $username): ?array {
    foreach (admins_all() as $row) {
        if (($row['username'] ?? '') === $username) {
            return $row;
        }
    }
    return null;
}
