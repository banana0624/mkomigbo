<?php
declare(strict_types=1);

/**
 * project-root/public/staff/subjects/new.php
 *
 * Thin forwarder → real controller (index.php with ?action=new).
 * Reason: keep pretty URL working even if rewrite didn’t fire.
 */

$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (is_file($init)) {
  require_once $init;
}

$target = function_exists('url_for')
  ? url_for('/staff/subjects/index.php?action=new')
  : '/staff/subjects/index.php?action=new';

header('Location: ' . $target);
exit;
