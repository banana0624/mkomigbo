<?php
declare(strict_types=1);

/**
 * project-root/private/shared/public_header.php
 * Public-facing header. Sets defaults, ensures public styles,
 * then delegates to the canonical base header.
 */

if (!function_exists('h')) {
  function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
}
if (!function_exists('url_for')) {
  function url_for(string $p): string { return ($p !== '' && $p[0] !== '/') ? '/'.$p : $p; }
}

$page_title  = $page_title  ?? 'Mkomigbo';
$active_nav  = $active_nav  ?? 'home';
$body_class  = trim(($body_class ?? '') . ' public');
$stylesheets = $stylesheets ?? [];

// UI.css is ensured by base header; add subjects.css when relevant
$needs_subjects_css =
  ($active_nav === 'subjects') ||
  (isset($body_class) && strpos($body_class, 'public-subjects') !== false);

if ($needs_subjects_css && !in_array('/lib/css/subjects.css', $stylesheets, true)) {
  $stylesheets[] = '/lib/css/subjects.css';
}

// Public top-nav points to visitor home (/subjects/) and a small staff link
// (Main nav rendering is handled inside base header via render_main_nav() fallback)

// Hand off to the canonical base header:
require __DIR__ . '/header.php';
