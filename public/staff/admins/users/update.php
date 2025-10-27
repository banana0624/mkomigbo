<?php
// project-root/public/staff/admins/users/update.php
declare(strict_types=1);
$init = dirname(__DIR__, 5) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found'); }
require_once $init;

require_once PRIVATE_PATH . '/functions/auth.php';
require_staff();
define('REQUIRE_LOGIN', true);
define('REQUIRE_PERMS', ['users.edit','admins']);
require PRIVATE_PATH . '/middleware/guard.php';

if (function_exists('csrf_check')) { csrf_check(); }

require_once PRIVATE_PATH . '/functions/user_functions.php';

$id   = (int)($_POST['id'] ?? 0);
$data = [
  'username' => $_POST['username'] ?? '',
  'email'    => $_POST['email'] ?? '',
  'role'     => $_POST['role'] ?? 'editor',
];

$pw  = (string)($_POST['password'] ?? '');
$pw2 = (string)($_POST['password_confirm'] ?? '');
if ($pw !== '') {
  if ($pw !== $pw2) {
    if (function_exists('flash')) flash('error', 'Passwords do not match.');
    header('Location: ' . url_for('/staff/admins/users/edit.php?id='.$id)); exit;
  }
  $data['password'] = $pw;
}

$ok = user_update_secure($id, $data);
if (function_exists('flash')) {
  flash($ok ? 'success' : 'error', $ok ? 'User updated.' : 'Update failed.');
}
header('Location: ' . url_for('/staff/admins/users/'));
