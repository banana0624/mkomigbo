<?php
declare(strict_types=1);

/**
 * project-root/private/functions/compat_shims.php
 * compat_shims.php
 * Legacy helper shims so older pages keep working.
 * This file is safe to include multiple times.
 */

if (!function_exists('is_post_request')) {
  function is_post_request(): bool {
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
  }
}

if (!function_exists('is_get_request')) {
  function is_get_request(): bool {
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET';
  }
}

if (!function_exists('u')) {
  function u(string $value): string {
    return urlencode($value);
  }
}

if (!function_exists('raw_u')) {
  function raw_u(string $value): string {
    return rawurlencode($value);
  }
}

/**
 * db_escape(): legacy helper that mimics old escaping,
 * but routes to PDO::quote safely.
 */
if (!function_exists('db_escape')) {
  function db_escape(string $value): string {
    global $db;
    if (!isset($db) || !($db instanceof PDO)) {
      // last-resort, still return something safe-ish
      return addslashes($value);
    }
    // PDO::quote returns the value wrapped in quotes → strip them
    $q = $db->quote($value);
    return ($q !== false) ? substr($q, 1, -1) : addslashes($value);
  }
}
