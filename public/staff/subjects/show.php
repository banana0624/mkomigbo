<?php
// project-root/public/staff/subjects/show.php
$type = 'subject';
$id   = (int)($_GET['id'] ?? 0);
require dirname(__DIR__, 3) . '/private/common/show.php';
