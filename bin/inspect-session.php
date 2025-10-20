<?php
declare(strict_types=1);
$BASE = dirname(__DIR__);
require $BASE . '/private/assets/initialize.php';

session_start();
header('Content-Type: text/plain; charset=utf-8');

echo "current_user:\n";
print_r($_SESSION['user'] ?? null);

echo "\nroles:\n";
print_r($_SESSION['user']['roles'] ?? []);

echo "\nperms:\n";
print_r($_SESSION['user']['perms'] ?? []);
