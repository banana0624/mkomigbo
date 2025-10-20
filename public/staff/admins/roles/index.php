<?php
// project-root/public/staff/admins/roles/index.php
declare(strict_types=1);

$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found: '.$init); }
require_once $init;

define('REQUIRE_ROLES', ['admin']);            // admin-only
require PRIVATE_PATH . '/middleware/guard.php';

require_once PRIVATE_PATH . '/functions/role_functions.php';

$page_title    = 'Roles';
$active_nav    = 'staff';
$body_class    = 'role--staff role--admin';
$stylesheets[] = '/lib/css/ui.css';
$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Admins','url'=>'/staff/admins/'],
  ['label'=>'Roles'],
];

$roles = roles_all();
require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="max-width:980px;padding:1rem 0">
  <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem;">
    <h1 style="margin:0;">Roles</h1>
    <a class="btn" href="<?= h(url_for('/staff/admins/roles/new.php')) ?>">New Role</a>
  </header>

  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>ID</th><th>Slug</th><th>Name</th><th>Permissions</th><th class="actions">Actions</th></tr></thead>
      <tbody>
      <?php if (!$roles): ?>
        <tr><td colspan="5" class="muted">No roles yet.</td></tr>
      <?php else: foreach ($roles as $r): ?>
        <?php
          $perms = [];
          if (!empty($r['permissions_json'])) {
            $arr = json_decode((string)$r['permissions_json'], true);
            if (is_array($arr)) $perms = $arr;
          }
        ?>
        <tr>
          <td><?= (int)$r['id'] ?></td>
          <td><code><?= h($r['slug']) ?></code></td>
          <td><?= h($r['name']) ?></td>
          <td><?= $perms ? h(implode(', ', $perms)) : '<span class="muted">—</span>' ?></td>
          <td class="actions">
            <a class="btn btn-sm" href="<?= h(url_for('/staff/admins/roles/edit.php?id='.(int)$r['id'])) ?>">Edit</a>
            <a class="btn btn-sm btn-danger" href="<?= h(url_for('/staff/admins/roles/delete.php?id='.(int)$r['id'])) ?>">Delete</a>
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
