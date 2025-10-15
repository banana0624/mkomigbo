<?php
declare(strict_types=1);
$tpl = dirname(__DIR__, 5) . "/private/common/staff_subject_media/delete.php";
if (!is_file($tpl)) { die("Template not found at: " . $tpl); }
$subject_slug = "slavery/__NAME__/Slavery"; $subject_name = "__NAME__";
require $tpl;

