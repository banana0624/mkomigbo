<?php
// project-root/public/staff/logout.php
declare(strict_types=1);
$init = dirname(__DIR__, 2) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found: '.$init); }
require_once $init;
require_once PRIVATE_PATH . '/functions/auth.php';

auth_logout();
flash('success','Signed out.');
header('Location: ' . url_for('/staff/login.php'));
exit;
