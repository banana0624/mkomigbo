<?php
// project-root/public/staff/admins/roles/new.php
declare(strict_types=1);

$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found: '.$init); }
require_once $init;

define('REQUIRE_ROLES', ['admin']);
require PRIVATE_PATH . '/middleware/guard.php';

require_once PRIVATE_PATH . '/functions/role_functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $in = [
    'slug' => strtolower(trim((string)($_POST['slug'] ?? ''))),
    'name' => trim((string)($_POST['name'] ?? '')),
    'permissions_json' => (string)($_POST['permissions_json'] ?? ''),
  ];
  $res = role_create($in);
  if (!empty($res['ok'])) {
    if (function_exists('flash')) flash('success','Role created.');
    header('Location: ' . url_for('/staff/admins/roles/')); exit;
  }
  if (function_exists('flash')) flash('error', $res['error'] ?? 'Create failed.');
}

$page_title='New Role'; $active_nav='staff'; $body_class='role--staff role--admin'; $stylesheets[]='/lib/css/ui.css';
$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Admins','url'=>'/staff/admins/'],
  ['label'=>'Roles','url'=>'/staff/admins/roles/'],
  ['label'=>'New'],
];
require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="max-width:720px;padding:1rem 0">
  <h1>New Role</h1>
  <?= function_exists('display_session_message') ? display_session_message() : '' ?>
  <form method="post">
    <?= csrf_field() ?>
    <div class="field"><label>Slug</label>
      <input class="input" name="slug" required pattern="[a-z0-9][a-z0-9_-]{1,63}" placeholder="admin, editor, viewer"
             value="<?= h($_POST['slug'] ?? '') ?>">
      <small class="muted">Lowercase letters, numbers, hyphen/underscore; 2–64 chars.</small>
    </div>
    <div class="field"><label>Name</label>
      <input class="input" name="name" required value="<?= h($_POST['name'] ?? '') ?>">
    </div>
    <div class="field"><label>Permissions (JSON array)</label>
      <textarea class="input" name="permissions_json" rows="6" placeholder='["manage-users","publish"]'><?= h($_POST['permissions_json'] ?? '') ?></textarea>
      <small class="muted">Example: <code>["manage-users","publish"]</code></small>
    </div>
    <div class="actions">
      <button class="btn btn-primary" type="submit">Create</button>
      <a class="btn" href="<?= h(url_for('/staff/admins/roles/')) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php require PRIVATE_PATH . '/shared/staff_footer.php'; ?>
