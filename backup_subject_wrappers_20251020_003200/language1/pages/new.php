<?php
declare(strict_types=1);
$init = dirname(__DIR__, 5) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

require_once PRIVATE_PATH . '/functions/auth.php';
require_staff();

$subject_slug = basename(dirname(__DIR__));
$subject_name = function_exists('subject_human_name') ? subject_human_name($subject_slug) : ucfirst(str_replace('-', ' ', $subject_slug));

define('REQUIRE_LOGIN', true);
define('REQUIRE_PERMS', ['pages.create']);
require PRIVATE_PATH . '/middleware/guard.php';

require PRIVATE_PATH . '/common/staff_subject_pages/new.php';
