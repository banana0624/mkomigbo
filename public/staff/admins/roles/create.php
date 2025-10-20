<?php
// project-root/public/staff/admins/roles/create.php
declare(strict_types=1);
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) die('Init not found'); require_once $init;

define('REQUIRE_ROLES', ['admin']);
require PRIVATE_PATH . '/middleware/guard.php';

require_once PRIVATE_PATH . '/functions/role_functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $in = [
    'slug' => (string)($_POST['slug'] ?? ''),
    'name' => (string)($_POST['name'] ?? ''),
    'permissions_json' => trim((string)($_POST['permissions_json'] ?? '')),
  ];
  $res = role_create($in);
  if ($res['ok']) {
    flash('success','Role created.');
    header('Location: ' . url_for('/staff/admins/roles/')); exit;
  }
  $errors = $res['errors'] ?? [];
}

$page_title = 'New Role';
$active_nav = 'admins';
$body_class = 'role--staff role--admin';
$stylesheets[] = '/lib/css/ui.css';
$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Admins','url'=>'/staff/admins/'],['label'=>'Roles','url'=>'/staff/admins/roles/'],['label'=>'Create'],
];
require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="max-width:760px;padding:1.25rem 0">
  <h1>Create Role</h1>
  <?= function_exists('display_session_message') ? display_session_message() : '' ?>
  <form method="post">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <div class="field"><label>Slug</label>
      <input class="input" name="slug" required>
      <small class="muted">e.g. <code>curator</code></small>
      <?php if (!empty($errors['slug'])): ?><div class="error"><?= h($errors['slug']) ?></div><?php endif; ?>
    </div>
    <div class="field"><label>Name</label>
      <input class="input" name="name" required>
      <?php if (!empty($errors['name'])): ?><div class="error"><?= h($errors['name']) ?></div><?php endif; ?>
    </div>
    <div class="field"><label>Permissions (JSON array)</label>
      <textarea class="input" name="permissions_json" rows="6" placeholder='["manage-platforms","publish"]'></textarea>
      <?php if (!empty($errors['permissions_json'])): ?><div class="error"><?= h($errors['permissions_json']) ?></div><?php endif; ?>
    </div>
    <div class="actions">
      <button class="btn btn-primary">Save</button>
      <a class="btn" href="<?= h(url_for('/staff/admins/roles/')) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
