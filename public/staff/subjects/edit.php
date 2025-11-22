<?php
declare(strict_types=1);

/**
 * project-root/public/staff/subjects/edit.php
 * Thin forwarder for legacy/pretty URL:
 *   /staff/subjects/edit.php?slug=history
 *   → /staff/subjects/index.php?action=edit&slug=history
 */

$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (is_file($init)) {
  require_once $init;
}

$slug = $_GET['slug'] ?? $_GET['subject'] ?? '';
$qs   = $slug !== '' ? ('?action=edit&slug=' . urlencode($slug)) : '?action=edit';

$target = function_exists('url_for')
  ? url_for('/staff/subjects/index.php' . $qs)
  : '/staff/subjects/index.php' . $qs;

header('Location: ' . $target);
exit;
