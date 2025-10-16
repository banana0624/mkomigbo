<?php
// project-root/private/functions/helper_functions.php
declare(strict_types=1);

/**
 * Common helper functions used across the app.
 * Idempotent: each is guarded with function_exists().
 */

/* ===== Basic HTML helpers ===== */
if (!function_exists('h')) {
  function h(string $s = ''): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('html_decode_utf8')) {
  function html_decode_utf8(string $s): string {
    // Double decode to handle things like &amp;#803;
    $s = html_entity_decode($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    return html_entity_decode($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }
}

/* ===== Text cleaning / slugging ===== */
if (!function_exists('sanitize_text')) {
  /**
   * Strip tags, decode entities, collapse whitespace.
   */
  function sanitize_text(string $s): string {
    $s = html_decode_utf8($s);
    $s = strip_tags($s);
    $s = str_replace(["\r\n", "\r"], "\n", $s);
    $s = preg_replace('~\s+~u', ' ', $s);
    return trim($s);
  }
}

if (!function_exists('slugify')) {
  /**
   * Create a URL-safe slug from arbitrary text.
   * ICU transliterator preferred; falls back to iconv.
   */
  function slugify(string $s): string {
    $s = sanitize_text($s);

    if (class_exists('Transliterator')) {
      $tr = \Transliterator::create('NFKD; [:Nonspacing Mark:] Remove; NFC');
      if ($tr) $s = $tr->transliterate($s);
    } else {
      $s = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;
    }

    $s = mb_strtolower($s, 'UTF-8');
    $s = preg_replace('~[^a-z0-9]+~', '-', $s);
    $s = trim($s, '-');
    return $s !== '' ? $s : 'item';
  }
}

/* ===== File-system helpers ===== */
if (!function_exists('safe_path_join')) {
  function safe_path_join(string ...$parts): string {
    $p = join(DIRECTORY_SEPARATOR, array_map(fn($x) => trim($x, " \t\n\r\0\x0B\\/"), $parts));
    // Normalize double slashes
    return preg_replace('~[\\/]+~', DIRECTORY_SEPARATOR, $p);
  }
}

if (!function_exists('ensure_dir')) {
  function ensure_dir(string $dir, int $mode = 0775): bool {
    if (is_dir($dir)) return true;
    return @mkdir($dir, $mode, true);
  }
}

/* ===== URL helpers ===== */
if (!function_exists('url_for')) {
  function url_for(string $script_path): string {
    if ($script_path === '' || $script_path[0] !== '/') $script_path = '/' . $script_path;
    return rtrim(defined('WWW_ROOT') ? WWW_ROOT : '', '/') . $script_path;
  }
}

/* ===== Content helpers (Markdown & entity-aware echo) ===== */
if (!function_exists('render_markdown_safe')) {
  /**
   * Very lightweight Markdown: only paragraphs & line breaks.
   * (Swap for a real parser later if needed.)
   */
  function render_markdown_safe(string $md): string {
    $md = html_decode_utf8($md);
    $md = str_replace(["\r\n", "\r"], "\n", $md);
    $paras = array_map('trim', preg_split('~\n{2,}~', $md));
    $html = '';
    foreach ($paras as $p) {
      if ($p === '') continue;
      $p = nl2br(h($p));
      $html .= "<p>{$p}</p>\n";
    }
    return $html;
  }
}

/* ===== Puny helpers for arrays ===== */
if (!function_exists('array_get')) {
  function array_get(array $a, string $key, mixed $default = null): mixed {
    return array_key_exists($key, $a) ? $a[$key] : $default;
  }
}
