<?php
// project-root/public/staff/hello.php

declare(strict_types=1);
$init = dirname(__DIR__, 2) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found: ' . $init); }
require_once $init;

header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html>
  <head><meta charset="utf-8"><title>Hello (staff)</title></head>
  <body>
    <h1>Hello staff</h1>
    <p>The time is <?= h(date('Y-m-d H:i:s')) ?></p>
  </body>
</html>
