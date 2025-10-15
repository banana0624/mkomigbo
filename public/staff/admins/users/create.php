<?php
// public/staff/admins/users/create.php
declare(strict_types=1);
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

$page_title = 'Admin • Create User';
$active_nav = 'staff';
$body_class = 'role--staff role--admin admin--users';
$page_logo  = '/lib/images/icons/users.svg';
$stylesheets[] = '/lib/css/ui.css';

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Admins','url'=>'/staff/admins/'],
  ['label'=>'Users','url'=>'/staff/admins/users/'],
  ['label'=>'Create'],
];

require PRIVATE_PATH . '/shared/staff_header.php';
?>
<main class="container" style="max-width:720px;padding:1.25rem 0">
  <h1>Create User</h1>
  <form method="post" action="#">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <div class="field">
      <label>Name</label>
      <input class="input" type="text" name="name" required>
    </div>
    <div class="field">
      <label>Email</label>
      <input class="input" type="email" name="email" required>
    </div>
    <div class="field">
      <label>Password</label>
      <input class="input" type="password" name="password" required>
    </div>
    <div class="actions">
      <button class="btn btn-primary" type="submit">Save</button>
      <a class="btn" href="<?= h(url_for('/staff/admins/users/')) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php require PRIVATE_PATH . '/shared/staff_footer.php'; ?>
