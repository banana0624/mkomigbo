<?php
declare(strict_types=1);
$tpl = dirname(__DIR__, 5) . '/private/common/staff_subject_media/upload.php';
if (!is_file($tpl)) { die('Template not found at: ' . $tpl); }
$subject_slug = 'arabs'; $subject_name = 'Arabs';
require $tpl;
