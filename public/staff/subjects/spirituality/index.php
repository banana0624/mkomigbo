<?php
// project-root/public/staff/subjects/spirituality/index.php
declare(strict_types=1);

$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) {
  die('Init not found at: ' . $init);
}
require_once $init;

require_once PRIVATE_PATH . '/functions/auth.php';
require_staff();

$subject_slug = 'spirituality';
$subject_name = function_exists('subject_human_name')
  ? subject_human_name($subject_slug)
  : ucfirst(str_replace('-', ' ', $subject_slug));

require PRIVATE_PATH . '/common/staff_subjects/hub.php';
?><!---- hub-wrapper-ok ---->
