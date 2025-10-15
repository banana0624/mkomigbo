<?php
// project-root/public/staff/subjects/delete.php
$type = 'subject';
$id   = (int)($_GET['id'] ?? 0);
require dirname(__DIR__, 3) . '/private/common/delete.php';
