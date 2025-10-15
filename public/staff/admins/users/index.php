<?php
// project-root/public/staff/admins/users/index.php

declare(strict_types=1);
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

$page_title = 'Admin • Users';
$active_nav = 'staff';
$body_class = 'role--staff role--admin admin--users';
$page_logo  = '/lib/images/icons/users.svg';
$stylesheets[] = '/lib/css/ui.css';

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Admins','url'=>'/staff/admins/'],
  ['label'=>'Users'],
];

require PRIVATE_PATH . '/shared/staff_header.php';
?>
<main class="container" style="padding:1.25rem 0">
  <h1>Users</h1>
  <ul>
    <li><a href="<?= h(url_for('/staff/admins/users/create.php')) ?>">Create User</a></li>
    <li><a href="<?= h(url_for('/staff/admins/users/invite.php')) ?>">Invite User</a></li>
  </ul>
  <p><a class="btn" href="<?= h(url_for('/staff/admins/')) ?>">&larr; Back to Admins</a></p>
</main>
<?php require PRIVATE_PATH . '/shared/staff_footer.php'; ?>
