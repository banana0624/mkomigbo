<?php
declare(strict_types=1);

/**
 * project-root/private/assets/db_credentials.php
 * Minimal, idempotent credential shim.
 * - Reads from $_ENV (Dotenv should already be loaded in initialize.php)
 * - Defines DB_DSN / DB_USER / DB_PASS only if not already defined
 * - No side effects (no PDO connection here)
 */

// Helper to read env with default (kept local, no global function pollution)
$env = static function (string $key, $default = null) {
    return array_key_exists($key, $_ENV) ? $_ENV[$key] : $default;
};

/* ---- DB_DSN ----
 * Prefer a complete DSN via .env: 
 *   DB_DSN="mysql:host=127.0.0.1;port=3306;dbname=mkomigbo;charset=utf8mb4"
 * If not provided, we DO NOT synthesize a DSN here—leave that to config.php (or your database.php).
 */
if (!defined('DB_DSN')) {
    $dsn = (string) $env('DB_DSN', '');
    if ($dsn !== '') {
        define('DB_DSN', $dsn);
    }
}

/* ---- DB_USER / DB_PASS ----
 * If not provided here, config.php may supply safe defaults (e.g., root / empty pass in XAMPP).
 */
if (!defined('DB_USER')) {
    $user = (string) $env('DB_USER', '');
    if ($user !== '') define('DB_USER', $user);
}
if (!defined('DB_PASS')) {
    $pass = (string) $env('DB_PASS', '');
    if ($pass !== '') define('DB_PASS', $pass);
}

/* No closing PHP tag */
