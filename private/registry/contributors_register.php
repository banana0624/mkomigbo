<?php
/**
 * project-root/private/registry/contributors_register.php
 *
 * Optional static registry for contributor bootstraps or defaults.
 * Use DB for real data; this is helpful for seeding and non-DB fallbacks.
 *
 * Exposes: $CONTRIBUTORS_REGISTER, contributors_all(), contributor_by_email()
 */

if (defined('MK_REGISTRY_CONTRIBS_LOADED')) { return; }
define('MK_REGISTRY_CONTRIBS_LOADED', true);

$CONTRIBUTORS_REGISTER = [
    // [
    //   'display_name' => 'Jane Doe',
    //   'email'        => 'jane@example.com',
    //   'roles'        => ['author'],    // author|moderator|reviewer
    //   'status'       => 'active',
    // ],
];

function contributors_all(): array {
    global $CONTRIBUTORS_REGISTER;
    return $CONTRIBUTORS_REGISTER;
}

function contributor_by_email(string $email): ?array {
    foreach (contributors_all() as $row) {
        if (strcasecmp($row['email'] ?? '', $email) === 0) {
            return $row;
        }
    }
    return null;
}
