<?php
// public/staff/admins/roles/index.php
declare(strict_types=1);
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

$page_title = 'Admin • Roles';
$active_nav = 'staff';
$body_class = 'role--staff role--admin admin--roles';
$page_logo  = '/lib/images/icons/shield.svg';
$stylesheets[] = '/lib/css/ui.css';

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Admins','url'=>'/staff/admins/'],
  ['label'=>'Roles'],
];

require PRIVATE_PATH . '/shared/staff_header.php';
?>
<main class="container" style="padding:1.25rem 0">
  <h1>Roles</h1>
  <ul>
    <li><a href="<?= h(url_for('/staff/admins/roles/new.php')) ?>">New Role</a></li>
    <li><a href="<?= h(url_for('/staff/admins/roles/permissions.php')) ?>">Permissions Matrix</a></li>
  </ul>
  <p><a class="btn" href="<?= h(url_for('/staff/admins/')) ?>">&larr; Back to Admins</a></p>
</main>
<?php require PRIVATE_PATH . '/shared/staff_footer.php'; ?>
