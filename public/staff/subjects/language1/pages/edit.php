<?php
declare(strict_types=1);
$init = dirname(__DIR__, 5) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

$subject_slug = 'language1'; $subject_name = 'Language 1';
require PRIVATE_PATH . '/common/staff_subject_pages/edit.php';
