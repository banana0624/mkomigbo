<?php
/**
 * project-root/private/registry/staff_register.php
 *
 * Define staff roles and capabilities; helpful for UI conditionals and guards,
 * even if final enforcement is done at DB/middleware level.
 *
 * Exposes: $STAFF_ROLES_REGISTER, staff_roles_all(), staff_role(string $role)
 */

if (defined('MK_REGISTRY_STAFF_LOADED')) { return; }
define('MK_REGISTRY_STAFF_LOADED', true);

$STAFF_ROLES_REGISTER = [
    'superadmin' => [
        'name'        => 'Super Admin',
        'permissions' => ['*'], // all
    ],
    'admin' => [
        'name'        => 'Admin',
        'permissions' => [
            'subjects:crud', 'pages:crud', 'platforms:manage',
            'contributors:manage', 'seo:manage', 'settings:read',
        ],
    ],
    'editor' => [
        'name'        => 'Editor',
        'permissions' => ['subjects:read', 'pages:crud', 'seo:read'],
    ],
    'moderator' => [
        'name'        => 'Moderator',
        'permissions' => ['comments:moderate', 'communities:moderate', 'threads:moderate'],
    ],
];

function staff_roles_all(): array {
    global $STAFF_ROLES_REGISTER;
    return $STAFF_ROLES_REGISTER;
}

function staff_role(string $role): ?array {
    $all = staff_roles_all();
    return $all[$role] ?? null;
}

