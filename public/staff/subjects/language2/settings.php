<?php
declare(strict_types=1);
$tpl = dirname(__DIR__, 4) . '/private/common/staff_subject_settings.php';
if (!is_file($tpl)) { die('Template not found at: ' . $tpl); }
$subject_slug = 'language2'; $subject_name = 'Language2';
require $tpl;
