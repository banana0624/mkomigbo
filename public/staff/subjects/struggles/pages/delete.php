<?php
declare(strict_types=1);
$init = dirname(__DIR__, 5) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

$subject_slug = 'struggles'; $subject_name = 'Struggles';
require PRIVATE_PATH . '/common/staff_subject_pages/delete.php';
