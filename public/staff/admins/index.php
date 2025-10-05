<?php
// project-root/public/staff/admins/index.php

require_once __DIR__ . '/../../../private/assets/initialize.php';

$page_title = 'Admins';
include_once PRIVATE_PATH . '/shared/staff_header.php';

$admins = [];
if (function_exists('find_all_admins')) {
    $admins = find_all_admins();
}
?>

<h2>All Admins</h2>
<p><a href="<?php echo url_for('/staff/admins/new.php'); ?>">+ Add Admin</a></p>

<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Username</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($admins as $admin): ?>
      <tr>
        <td><?php echo h($admin['id']); ?></td>
        <td>
          <a href="<?php echo url_for('/staff/admins/show.php?id=' . u($admin['id'])); ?>">
            <?php echo h($admin['username']); ?>
          </a>
        </td>
        <td>
          <a href="<?php echo url_for('/staff/admins/edit.php?id=' . u($admin['id'])); ?>">Edit</a> |
          <a href="<?php echo url_for('/staff/admins/delete.php?id=' . u($admin['id'])); ?>"
             onclick="return confirm('Delete this admin?');">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php
include_once PRIVATE_PATH . '/shared/staff_footer.php';
?>
