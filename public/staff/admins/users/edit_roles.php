<?php
// project-root/public/staff/admins/users/edit_roles.php
declare(strict_types=1);

$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found: '.$init); }
require_once $init;

define('REQUIRE_ROLES', ['admin']);
require PRIVATE_PATH . '/middleware/guard.php';

require_once PRIVATE_PATH . '/functions/user_functions.php';
require_once PRIVATE_PATH . '/functions/role_functions.php';

$id   = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$user = $id ? user_find($id) : null;
if (!$user) { http_response_code(404); die('User not found'); }

$roles    = roles_all();
$current  = user_role_ids($id);
$isCoreAdmin = false;

// Optional guard: prevent removing last admin (basic heuristic)
try {
  $st = $db->query("SELECT COUNT(*) FROM user_roles ur JOIN roles r ON r.id=ur.role_id WHERE r.slug='admin'");
  $adminCount = (int)$st->fetchColumn();
  $isCoreAdmin = ($adminCount <= 1 && in_array('admin', array_map(fn($r)=>$r['slug'], $roles), true));
} catch (Throwable $e) { /* ignore */ }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $selected = array_map('intval', (array)($_POST['roles'] ?? []));

  // Optional: do not allow removing last admin
  if ($isCoreAdmin) {
    // If this user currently has admin and POST removes it while adminCount==1, block.
    $adminRoleId = 0;
    foreach ($roles as $r) if ($r['slug']==='admin') { $adminRoleId = (int)$r['id']; break; }
    if ($adminRoleId && in_array($adminRoleId, $current, true) && !in_array($adminRoleId, $selected, true)) {
      if (function_exists('flash')) flash('error','Cannot remove the last remaining admin.');
      header('Location: ' . url_for('/staff/admins/users/edit_roles.php?id='.$id)); exit;
    }
  }

  if (user_roles_replace($id, $selected)) {
    if (function_exists('flash')) flash('success','Roles updated.');
    header('Location: ' . url_for('/staff/admins/users/')); exit;
  }
  if (function_exists('flash')) flash('error','Save failed.');
}

$page_title    = 'Edit User Roles';
$active_nav    = 'staff';
$body_class    = 'role--staff role--admin';
$stylesheets[] = '/lib/css/ui.css';
$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Admins','url'=>'/staff/admins/'],
  ['label'=>'Users','url'=>'/staff/admins/users/'],
  ['label'=>'Edit Roles'],
];

require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="max-width:720px;padding:1rem 0">
  <h1>Edit Roles — <?= h($user['username'] ?? $user['email'] ?? ('User #'.$id)) ?></h1>
  <?= function_exists('display_session_message') ? display_session_message() : '' ?>

  <form method="post">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <input type="hidden" name="id" value="<?= (int)$id ?>">

    <fieldset class="field">
      <legend>Assign roles</legend>
      <?php if (!$roles): ?>
        <p class="muted">No roles defined.</p>
      <?php else: foreach ($roles as $r): ?>
        <?php $rid = (int)$r['id']; $checked = in_array($rid, $current, true) ? 'checked' : ''; ?>
        <label style="display:block;margin:.25rem 0">
          <input type="checkbox" name="roles[]" value="<?= $rid ?>" <?= $checked ?>>
          <strong><?= h($r['name']) ?></strong> <code><?= h($r['slug']) ?></code>
        </label>
      <?php endforeach; endif; ?>
    </fieldset>

    <div class="actions" style="margin-top:.75rem">
      <button class="btn btn-primary" type="submit">Save</button>
      <a class="btn" href="<?= h(url_for('/staff/admins/users/')) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php require PRIVATE_PATH . '/shared/staff_footer.php'; ?>
