#!/usr/bin/env php
<?php
// project-root/bin/db-check.php

declare(strict_types=1);

/**
 * bin/db-check.php
 * Prints DSN (masked), connects via PDO, runs SELECT 1, exits with code 0/1.
 */

$BASE = dirname(__DIR__);

// Minimal bootstrap (no sessions/headers)
require_once $BASE . '/private/assets/config.php';

// Composer + Dotenv (optional but nice for CLI)
$autoload = $BASE . '/vendor/autoload.php';
if (is_file($autoload)) require_once $autoload;
if (class_exists('Dotenv\Dotenv')) {
    Dotenv\Dotenv::createImmutable($BASE)->safeLoad();
}

// Credentials shim (optional) then DB connect
$cred = $BASE . '/private/assets/db_credentials.php';
if (is_file($cred)) require_once $cred;
require_once $BASE . '/private/assets/database.php';

$dsn = defined('DB_DSN') ? DB_DSN : '(no DSN)';
$mask = preg_replace('~(//)([^:@/]+)(:([^@/]*))?@~', '$1***@', (string)$dsn);

echo "DB DSN: {$mask}\n";
try {
    /** @var PDO $db */
    $ok = $db->query('SELECT 1')->fetchColumn();
    echo "SELECT 1 => {$ok}\n";
    echo "Status: OK\n";
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, "Status: FAIL\n");
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    exit(1);
}
