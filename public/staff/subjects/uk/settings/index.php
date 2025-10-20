<?php
// project-root/public/staff/subjects/<slug>/settings/index.php
declare(strict_types=1);
$init = dirname(__DIR__, 5) . '/private/assets/initialize.php'; // from .../<slug>/settings/
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

require_once PRIVATE_PATH . '/functions/auth.php';
require_staff();

$subject_slug = basename(dirname(__DIR__));
$subject_name = function_exists('subject_human_name') ? subject_human_name($subject_slug) : ucfirst(str_replace('-', ' ', $subject_slug));

// Guard (wrapper-level)
define('REQUIRE_LOGIN', true);
define('REQUIRE_PERMS', ['subjects.manage']);
require PRIVATE_PATH . '/middleware/guard.php';

require PRIVATE_PATH . '/common/staff_subjects/settings.php';
