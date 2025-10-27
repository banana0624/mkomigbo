<?php
// project-root/public/staff/subjects/struggles/pages/index.php
declare(strict_types=1);

// From /public/staff/subjects/struggles/pages
$init = dirname(__DIR__, 5) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

require_once PRIVATE_PATH . '/functions/auth.php';
require_staff();

$subject_slug = basename(dirname(__DIR__)); // "struggles"
$subject_name = function_exists('subject_human_name')
  ? subject_human_name($subject_slug)
  : ucfirst(str_replace('-', ' ', $subject_slug));

define('REQUIRE_LOGIN', true);
define('REQUIRE_PERMS', ['pages.view']);
require PRIVATE_PATH . '/middleware/guard.php';

// Standard subject pages listing template (same one tradition uses)
require PRIVATE_PATH . '/common/staff_subject_pages/index.php';
?><!---- pages-index-wrapper-ok ---->
