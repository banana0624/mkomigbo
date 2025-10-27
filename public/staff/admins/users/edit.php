<?php
// project-root/public/staff/admins/users/edit.php
declare(strict_types=1);
$init = dirname(__DIR__, 5) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found'); }
require_once $init;

require_once PRIVATE_PATH . '/functions/auth.php';
require_staff();
define('REQUIRE_LOGIN', true);
define('REQUIRE_PERMS', ['users.edit','admins']); // adjust if you have a specific perm
require PRIVATE_PATH . '/middleware/guard.php';

require_once PRIVATE_PATH . '/functions/user_functions.php';

$id = (int)($_GET['id'] ?? 0);
$user = $id ? user_find($id) : null;
if (!$user) { echo "User not found."; exit; }

$page_title = "Edit User";
include SHARED_PATH . '/staff_header.php';
echo display_session_message();
?>

<h1>Edit User</h1>
<form action="<?= h(url_for('/staff/admins/users/update.php')) ?>" method="post" autocomplete="off">
  <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">

  <div class="form-row">
    <label>Username</label>
    <input type="text" name="username" value="<?= h($user['username']) ?>" required>
  </div>

  <div class="form-row">
    <label>Email</label>
    <input type="email" name="email" value="<?= h($user['email']) ?>" required>
  </div>

  <div class="form-row">
    <label>Role</label>
    <select name="role">
      <option value="admin"  <?= $user['role']==='admin'?'selected':'' ?>>Admin</option>
      <option value="editor" <?= $user['role']==='editor'?'selected':'' ?>>Editor</option>
    </select>
  </div>

  <fieldset style="margin:1rem 0;padding:.75rem;border:1px solid #ddd">
    <legend>Change Password (optional)</legend>
    <div class="form-row">
      <label>New Password</label>
      <input type="password" name="password" value="">
    </div>
    <div class="form-row">
      <label>Confirm Password</label>
      <input type="password" name="password_confirm" value="">
    </div>
    <p style="font-size:.9rem;color:#666">Leave blank to keep existing password.</p>
  </fieldset>

  <?= function_exists('csrf_field') ? csrf_field() : '' ?>
  <button class="btn btn-primary">Save</button>
  <a class="btn" href="<?= h(url_for('/staff/admins/users/')) ?>">Cancel</a>
</form>

<?php include SHARED_PATH . '/staff_footer.php'; ?>
