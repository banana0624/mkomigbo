<?php
// public/staff/admins/roles/new.php
declare(strict_types=1);
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

$page_title = 'Admin • New Role';
$active_nav = 'staff';
$body_class = 'role--staff role--admin admin--roles';
$page_logo  = '/lib/images/icons/shield.svg';
$stylesheets[] = '/lib/css/ui.css';

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Admins','url'=>'/staff/admins/'],
  ['label'=>'Roles','url'=>'/staff/admins/roles/'],
  ['label'=>'New'],
];

require PRIVATE_PATH . '/shared/staff_header.php';
?>
<main class="container" style="max-width:720px;padding:1.25rem 0">
  <h1>New Role</h1>
  <form method="post" action="#">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <div class="field">
      <label>Role name</label>
      <input class="input" type="text" name="name" required>
    </div>
    <div class="field">
      <label>Description</label>
      <textarea class="input" name="description" rows="3"></textarea>
    </div>
    <div class="actions">
      <button class="btn btn-primary" type="submit">Save</button>
      <a class="btn" href="<?= h(url_for('/staff/admins/roles/')) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php require PRIVATE_PATH . '/shared/staff_footer.php'; ?>
