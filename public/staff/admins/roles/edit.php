<?php
// project-root/public/staff/admins/roles/edit.php
declare(strict_types=1);

$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found: '.$init); }
require_once $init;

define('REQUIRE_ROLES', ['admin']);
require PRIVATE_PATH . '/middleware/guard.php';

require_once PRIVATE_PATH . '/functions/role_functions.php';

$id = (int)($_GET['id'] ?? 0);
$role = $id ? role_find($id) : null;
if (!$role) { http_response_code(404); die('Role not found'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $in = [
    'slug' => strtolower(trim((string)($_POST['slug'] ?? ''))),
    'name' => trim((string)($_POST['name'] ?? '')),
    'permissions_json' => (string)($_POST['permissions_json'] ?? ''),
  ];
  $res = role_update($id, $in);
  if (!empty($res['ok'])) {
    if (function_exists('flash')) flash('success','Role updated.');
    header('Location: ' . url_for('/staff/admins/roles/')); exit;
  }
  if (function_exists('flash')) flash('error', $res['error'] ?? 'Update failed.');
}

$page_title='Edit Role'; $active_nav='staff'; $body_class='role--staff role--admin'; $stylesheets[]='/lib/css/ui.css';
$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Admins','url'=>'/staff/admins/'],
  ['label'=>'Roles','url'=>'/staff/admins/roles/'],
  ['label'=>'Edit'],
];
require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="max-width:720px;padding:1rem 0">
  <h1>Edit Role</h1>
  <?= function_exists('display_session_message') ? display_session_message() : '' ?>
  <form method="post">
    <?= csrf_field() ?>
    <div class="field"><label>Slug</label>
      <input class="input" name="slug" required pattern="[a-z0-9][a-z0-9_-]{1,63}"
             value="<?= h($_POST['slug'] ?? $role['slug'] ?? '') ?>">
    </div>
    <div class="field"><label>Name</label>
      <input class="input" name="name" required value="<?= h($_POST['name'] ?? $role['name'] ?? '') ?>">
    </div>
    <div class="field"><label>Permissions (JSON array)</label>
      <textarea class="input" name="permissions_json" rows="6"><?= h($_POST['permissions_json'] ?? ($role['permissions_json'] ?? '')) ?></textarea>
    </div>
    <div class="actions">
      <button class="btn btn-primary" type="submit">Save</button>
      <a class="btn" href="<?= h(url_for('/staff/admins/roles/')) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php require PRIVATE_PATH . '/shared/staff_footer.php'; ?>
