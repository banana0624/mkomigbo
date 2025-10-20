<?php
// project-root/private/functions/asset_functions.php
declare(strict_types=1);

/**
 * asset_functions.php
 * - asset_url() builds a URL for anything under /public and cache-busts with filemtime().
 * - asset_exists() answers “is this asset present under /public?”.
 */

if (!function_exists('asset_exists')) {
  function asset_exists(string $rel): bool {
    if ($rel === '') return false;
    if ($rel[0] !== '/') $rel = '/' . $rel;
    $abs = rtrim(defined('PUBLIC_PATH') ? PUBLIC_PATH : dirname(__DIR__, 2) . '/public', DIRECTORY_SEPARATOR)
         . str_replace('/', DIRECTORY_SEPARATOR, $rel);
    return is_file($abs);
  }
}

if (!function_exists('asset_url')) {
  function asset_url(string $rel): string {
    // absolute URL? just return as-is
    if (preg_match('#^https?://#i', $rel)) return $rel;

    // normalize relative → “/path”
    if ($rel === '' || $rel[0] !== '/') $rel = '/' . $rel;

    $public = rtrim(defined('PUBLIC_PATH') ? PUBLIC_PATH : dirname(__DIR__, 2) . '/public', DIRECTORY_SEPARATOR);
    $abs    = $public . str_replace('/', DIRECTORY_SEPARATOR, $rel);

    // build base URL
    $url = function_exists('url_for') ? url_for($rel) : $rel;

    // add cache-busting if file is on disk
    $v = is_file($abs) ? @filemtime($abs) : null;
    if ($v) {
      $url .= (strpos($url, '?') === false ? '?' : '&') . 'v=' . $v;
    }
    return $url;
  }
}
