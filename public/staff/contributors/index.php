<?php
// project-root/public/staff/contributors/index.php

require_once __DIR__ . '/../../../private/assets/initialize.php';

if (defined('APP_DEBUG') && APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

$page_title = 'Contributors';
include_once SHARED_PATH . '/staff_header.php';  // or PRIVATE_PATH . '/shared/staff_header.php'

$contributors = [];
if (function_exists('find_all_contributors')) {
    $contributors = find_all_contributors();
}
?>

<h2>Contributors</h2>
<p><a href="<?php echo url_for('/staff/contributors/new.php'); ?>">+ Add Contributor</a></p>

<?php if (empty($contributors)): ?>
  <p>No contributors found.</p>
<?php else: ?>
  <table>
    <thead>
      <tr><th>ID</th><th>Name / Email</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($contributors as $c): ?>
        <tr>
          <td><?php echo h($c['id']); ?></td>
          <td>
            <a href="<?php echo url_for('/staff/contributors/show.php?id=' . u($c['id'])); ?>">
              <?php echo h($c['name'] ?? $c['email']); ?>
            </a>
          </td>
          <td>
            <a href="<?php echo url_for('/staff/contributors/edit.php?id=' . u($c['id'])); ?>">Edit</a> |
            <a href="<?php echo url_for('/staff/contributors/delete.php?id=' . u($c['id'])); ?>" onclick="return confirm('Delete this contributor?');">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php
include_once SHARED_PATH . '/staff_footer.php';
?>
