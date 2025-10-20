<?php
// project-root/public/staff/admins/roles/delete.php
declare(strict_types=1);

$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found: '.$init); }
require_once $init;

define('REQUIRE_ROLES', ['admin']);
require PRIVATE_PATH . '/middleware/guard.php';

require_once PRIVATE_PATH . '/functions/role_functions.php';

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$role = $id ? role_find($id) : null;
if (!$role) { http_response_code(404); die('Role not found'); }

$guarded = in_array($role['slug'], ['admin','editor','viewer'], true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  if (!$guarded) {
    $ok = role_delete($id);
    if ($ok && function_exists('flash')) flash('success','Role deleted.');
    if (!$ok && function_exists('flash')) flash('error','Delete failed (guarded or in use).');
  } else {
    if (function_exists('flash')) flash('error','Core role cannot be deleted.');
  }
  header('Location: ' . url_for('/staff/admins/roles/')); exit;
}

$page_title='Delete Role'; $active_nav='staff'; $body_class='role--staff role--admin'; $stylesheets[]='/lib/css/ui.css';
$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Admins','url'=>'/staff/admins/'],
  ['label'=>'Roles','url'=>'/staff/admins/roles/'],
  ['label'=>'Delete'],
];
require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="max-width:720px;padding:1rem 0">
  <h1>Delete Role</h1>
  <p>Delete role <strong><?= h($role['name']) ?></strong> (<code><?= h($role['slug']) ?></code>)?</p>
  <?php if ($guarded): ?>
    <div class="notice error" style="margin:.75rem 0">This is a core role and cannot be deleted.</div>
  <?php endif; ?>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$role['id'] ?>">
    <div class="actions">
      <button class="btn btn-danger" type="submit" <?= $guarded ? 'disabled' : '' ?>>Yes, delete</button>
      <a class="btn" href="<?= h(url_for('/staff/admins/roles/')) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php require PRIVATE_PATH . '/shared/staff_footer.php'; ?>
