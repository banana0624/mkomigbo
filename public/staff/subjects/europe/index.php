<?php
declare(strict_types=1);
$hub = dirname(__DIR__, 4) . '/private/common/staff_subject_hub.php';
if (!is_file($hub)) { die('Hub template not found at: ' . $hub); }
$subject_slug = 'europe'; $subject_name = 'Europe';
require $hub;
