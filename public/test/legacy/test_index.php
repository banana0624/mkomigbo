<?php
require_once(__DIR__ . '/../private/assets/initialize.php');
echo "<h1>Initialize reached </h1>";
echo "<pre>";
echo "PRIVATE_PATH: " . (defined("PRIVATE_PATH") ? PRIVATE_PATH : "NOT SET") . PHP_EOL;
echo "WWW_ROOT: " . (defined("WWW_ROOT") ? WWW_ROOT : "NOT SET") . PHP_EOL;
echo "</pre>";