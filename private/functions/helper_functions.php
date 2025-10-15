<?php
declare(strict_types=1);

/**
 * project-root/private/functions/helper_functions.php
 *
 * General-purpose helpers used across the project.
 * - NO CSRF helpers here (see private/functions/csrf.php)
 * - NO Flash helpers here (see private/functions/flash.php)
 * - All functions are guarded to avoid redeclare conflicts.
 */

/* ================================
   HTML / URL helpers
   ================================ */

if (!function_exists('h')) {
  /** HTML-escape (UTF-8) */
  function h(string $s = ''): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
  }
}

if (!function_exists('u')) {
  /** urlencode() shortcut for path/query parts */
  function u(string $s = ''): string {
    return urlencode($s);
  }
}

if (!function_exists('url_for')) {
  /**
   * Build a root-absolute URL using WWW_ROOT ('' when vhost points to /public).
   * Accepts either '/path' or 'path' and normalizes to '/path'.
   */
  function url_for(string $path): string {
    if ($path === '' || $path[0] !== '/') $path = '/'.$path;
    $base = defined('WWW_ROOT') ? WWW_ROOT : '';
    return rtrim($base, '/') . $path;
  }
}

if (!function_exists('asset_url')) {
  /**
   * Append cache-busting query using ASSET_VERSION (if defined).
   * E.g. asset_url('/lib/css/site.css') => '/lib/css/site.css?v=20250101'
   */
  function asset_url(string $path): string {
    $url = url_for($path);
    if (defined('ASSET_VERSION') && ASSET_VERSION) {
      $sep = (str_contains($url, '?') ? '&' : '?');
      $url .= $sep . 'v=' . rawurlencode((string)ASSET_VERSION);
    }
    return $url;
  }
}

if (!function_exists('current_url')) {
  /** Current request URL (best effort; scheme/host may be missing in CLI) */
  function current_url(): string {
    $uri  = $_SERVER['REQUEST_URI'] ?? '/';
    $host = $_SERVER['HTTP_HOST']    ?? '';
    $https= !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $https ? 'https://' : 'http://';
    return $host ? ($scheme.$host.$uri) : $uri;
  }
}

/* ================================
   HTTP helpers
   ================================ */

if (!function_exists('redirect_to')) {
  function redirect_to(string $url): never {
    header('Location: ' . $url);
    exit;
  }
}

if (!function_exists('is_post_request')) {
  function is_post_request(): bool {
    return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
  }
}

if (!function_exists('is_get_request')) {
  function is_get_request(): bool {
    return ($_SERVER['REQUEST_METHOD'] ?? '') === 'GET';
  }
}

/* ================================
   String helpers
   ================================ */

if (!function_exists('safe_strimwidth')) {
  /**
   * Multibyte-safe trim with ellipsis (falls back gracefully if mbstring is absent)
   * Example: safe_strimwidth($text, 0, 60, '…')
   */
  function safe_strimwidth(string $text, int $start, int $width, string $trimMarker = '…'): string {
    if (function_exists('mb_strimwidth')) {
      return mb_strimwidth($text, $start, $width, $trimMarker, 'UTF-8');
    }
    // Fallback: naive ASCII trimming
    if (strlen($text) <= $width) return $text;
    return substr($text, $start, max(0, $width - strlen($trimMarker))) . $trimMarker;
  }
}

/* ================================
   Subject media convenience (URLs)
   ================================ */

if (!function_exists('subject_icon_url')) {
  /** `/lib/images/subjects/{slug}.svg` */
  function subject_icon_url(string $slug): string {
    return url_for('/lib/images/subjects/' . rawurlencode($slug) . '.svg');
  }
}

if (!function_exists('subject_banner_url')) {
  /**
   * Prefer .webp, fallback to .jpg (caller may still check existence server-side if needed)
   */
  function subject_banner_url(string $slug): string {
    $base = '/lib/images/banners/' . rawurlencode($slug);
    // We can’t check filesystem here reliably; return preferred-first and let the page choose.
    // If you want hard existence check, do it server-side where PUBLIC_PATH is available.
    return url_for($base . '.webp');
  }
}

/* ================================
   Filesystem helpers (optional)
   ================================ */

if (!function_exists('public_path')) {
  function public_path(string $rel = ''): string {
    $root = defined('PUBLIC_PATH') ? PUBLIC_PATH : (__DIR__ . '/../../public');
    if ($rel === '') return $root;
    $rel = ltrim($rel, DIRECTORY_SEPARATOR . '/');
    return $root . DIRECTORY_SEPARATOR . $rel;
  }
}

if (!function_exists('public_file_exists')) {
  function public_file_exists(string $rel): bool {
    return is_file(public_path($rel));
  }
}

/* ================================
   Dev / debug helpers (safe in prod)
   ================================ */

if (!function_exists('dd')) {
  /** dump and die — use sparingly */
  function dd(...$vars): never {
    http_response_code(500);
    echo '<pre style="white-space:pre-wrap;font:12px/1.3 monospace;background:#111;color:#eee;padding:12px;border-radius:8px">';
    foreach ($vars as $v) {
      echo htmlspecialchars(print_r($v, true), ENT_QUOTES, 'UTF-8') . "\n";
    }
    echo '</pre>';
    exit;
  }
}

/* ================================
   IMPORTANT: No CSRF or Flash here
   - CSRF helpers live in: private/functions/csrf.php
   - Flash helpers live in: private/functions/flash.php
   ================================ */
