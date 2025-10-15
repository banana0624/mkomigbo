<?php
// project-root/public/staff/subjects/{slug}/media/delete.php

declare(strict_types=1);
$tpl = dirname(__DIR__, 5) . '/private/common/staff_subject_media/delete.php';
if (!is_file($tpl)) { die('Template not found at: ' . $tpl); }
$subject_slug = '__SLUG__'; $subject_name = '__NAME__';
require $tpl;