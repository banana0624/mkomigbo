<?php
// public/staff/admins/roles/permissions.php
declare(strict_types=1);
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

$page_title = 'Admin • Permissions';
$active_nav = 'staff';
$body_class = 'role--staff role--admin admin--roles';
$page_logo  = '/lib/images/icons/shield.svg';
$stylesheets[] = '/lib/css/ui.css';

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Admins','url'=>'/staff/admins/'],
  ['label'=>'Roles','url'=>'/staff/admins/roles/'],
  ['label'=>'Permissions'],
];

require PRIVATE_PATH . '/shared/staff_header.php';
?>
<main class="container" style="padding:1.25rem 0">
  <h1>Permissions Matrix</h1>
  <p class="muted">Stub page showing where a matrix/grid would live.</p>
  <table class="table">
    <thead>
      <tr><th>Permission</th><th>Editor</th><th>Admin</th></tr>
    </thead>
    <tbody>
      <tr><td>Publish pages</td><td><input type="checkbox" disabled></td><td><input type="checkbox" checked disabled></td></tr>
      <tr><td>Manage users</td><td><input type="checkbox" disabled></td><td><input type="checkbox" checked disabled></td></tr>
    </tbody>
  </table>
  <p><a class="btn" href="<?= h(url_for('/staff/admins/roles/')) ?>">&larr; Back to Roles</a></p>
</main>
<?php require PRIVATE_PATH . '/shared/staff_footer.php'; ?>
