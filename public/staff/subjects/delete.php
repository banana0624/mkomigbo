<?php
declare(strict_types=1);

/**
 * project-root/public/staff/subjects/delete.php
 * Thin forwarder for legacy/pretty URL:
 *   /staff/subjects/delete.php?slug=history
 *   → /staff/subjects/index.php?action=delete&slug=history
 *
 * The real handling is intended to live in index.php
 * (or whatever controller you later wire to ?action=delete).
 */

// If you want url_for(), we can safely include initialize.php;
// if it's ever “too heavy”, you can drop back to the hard-coded path.
$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (is_file($init)) {
  require_once $init;
}

$slug = $_GET['slug'] ?? $_GET['subject'] ?? '';
$qs   = $slug !== '' ? ('?action=delete&slug=' . urlencode($slug)) : '?action=delete';

$target = function_exists('url_for')
  ? url_for('/staff/subjects/index.php' . $qs)
  : '/staff/subjects/index.php' . $qs;

header('Location: ' . $target);
exit;
