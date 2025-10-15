<?php
// project-root/public/staff/subjects/pgs/delete.php
declare(strict_types=1);

// Self-check: init (depth = 4)
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

$type = 'page';
$id   = (int)($_GET['id'] ?? 0);
require dirname(__DIR__, 4) . '/private/common/delete.php';
