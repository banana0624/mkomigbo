<?php
// project-root/public/staff/subjects/{slug}/index.php

// example: project-root/public/staff/subjects/history/index.php
declare(strict_types=1);
$hub = dirname(__DIR__, 4) . '/private/common/staff_subject_hub.php'; // up to /project-root
if (!is_file($hub)) { die('Hub template not found at: ' . $hub); }
$subject_slug = 'history'; $subject_name = 'History';
// $subject_logo = '/lib/images/subjects/history.svg'; // optional override
require $hub;

