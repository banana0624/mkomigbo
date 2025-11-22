<?php
// project-root/public/test_php.php
declare(strict_types=1);
echo "PHP OK<br>";
$init = dirname(__DIR__) . '/private/assets/initialize.php';
echo "Init: $init (exists? " . (is_file($init)?'YES':'NO') . ")<br>";
require_once $init;
echo "Initialize included OK<br>";
