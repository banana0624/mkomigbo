<?php
// project-root/public/staff/admins/users/index.php
declare(strict_types=1);

$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found: '.$init); }
require_once $init;

define('REQUIRE_ROLES', ['admin']);
require PRIVATE_PATH . '/middleware/guard.php';

require_once PRIVATE_PATH . '/functions/user_functions.php';
require_once PRIVATE_PATH . '/functions/role_functions.php';

$page_title    = 'Users';
$active_nav    = 'staff';
$body_class    = 'role--staff role--admin';
$stylesheets[] = '/lib/css/ui.css';
$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Admins','url'=>'/staff/admins/'],
  ['label'=>'Users'],
];

$users = users_all();
$roles = roles_all(); // to show quick badges
$roleById = [];
foreach ($roles as $r) $roleById[(int)$r['id']] = $r;

require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="max-width:1000px;padding:1rem 0">
  <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem;">
    <h1 style="margin:0;">Users</h1>
  </header>

  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Legacy Role</th><th class="actions">Actions</th></tr></thead>
      <tbody>
      <?php if (!$users): ?>
        <tr><td colspan="5" class="muted">No users yet.</td></tr>
      <?php else: foreach ($users as $u): ?>
        <tr>
          <td><?= (int)$u['id'] ?></td>
          <td><?= h($u['username']) ?></td>
          <td><?= h($u['email']) ?></td>
          <td><code><?= h($u['role'] ?? '') ?></code></td>
          <td class="actions" style="white-space:nowrap">
            <a class="btn btn-sm" href="<?= h(url_for('/staff/admins/users/edit.php?id='.(int)$row['id'])) ?>">Edit</a>
            <a class="btn btn-sm" href="<?= h(url_for('/staff/admins/users/password.php?id='.(int)$row['id'])) ?>">Change Password</a>
            <a class="btn btn-sm btn-danger" href="<?= h(url_for('/staff/admins/users/delete.php?id='.(int)$row['id'])) ?>">Delete</a>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <p style="margin-top:1rem">
    <a class="btn" href="<?= h(url_for('/staff/admins/')) ?>">&larr; Back to Admins</a>
  </p>
</main>
<?php require PRIVATE_PATH . '/shared/staff_footer.php'; ?>
