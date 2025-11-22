<?php
declare(strict_types=1);

/**
 * project-root/public/staff/subjects/show.php
 * Thin forwarder for legacy/pretty URL:
 *   /staff/subjects/show.php?slug=history
 *   → /staff/subjects/index.php?action=show&slug=history
 */

$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (is_file($init)) {
  require_once $init;
}

$slug = $_GET['slug'] ?? $_GET['subject'] ?? '';
$qs   = $slug !== '' ? ('?action=show&slug=' . urlencode($slug)) : '?action=show';

$target = function_exists('url_for')
  ? url_for('/staff/subjects/index.php' . $qs)
  : '/staff/subjects/index.php' . $qs;

header('Location: ' . $target);
exit;
