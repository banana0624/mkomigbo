<?php
// project-root/public/staff/admins/refresh_perms.php
declare(strict_types=1);
require dirname(__DIR__, 3) . '/private/assets/initialize.php';
require_once PRIVATE_PATH . '/functions/auth.php';
auth_reload_permissions();
if (function_exists('flash')) flash('success','Permissions refreshed.');
header('Location: ' . (function_exists('url_for') ? url_for('/staff/') : '/staff/'));
