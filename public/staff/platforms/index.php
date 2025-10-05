<?php
// project-root/public/staff/platforms/index.php

require_once __DIR__ . '/../../../private/assets/initialize.php';

if (defined('APP_DEBUG') && APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

$page_title = 'Platforms';
include_once SHARED_PATH . '/staff_header.php';

$platforms = [];
if (function_exists('find_all_platforms')) {
    $platforms = find_all_platforms();
}
?>

<h2>All Platforms</h2>
<p><a href="<?php echo url_for('/staff/platforms/new.php'); ?>">+ Add New Platform</a></p>

<?php if (empty($platforms)): ?>
  <p>No platforms found.</p>
<?php else: ?>
  <table>
    <thead>
      <tr><th>ID</th><th>Name</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($platforms as $p): ?>
        <tr>
          <td><?php echo h($p['id']); ?></td>
          <td>
            <a href="<?php echo url_for('/staff/platforms/show.php?id=' . u($p['id'])); ?>">
              <?php echo h($p['name']); ?>
            </a>
          </td>
          <td>
            <a href="<?php echo url_for('/staff/platforms/edit.php?id=' . u($p['id'])); ?>">Edit</a> |
            <a href="<?php echo url_for('/staff/platforms/delete.php?id=' . u($p['id'])); ?>"
               onclick="return confirm('Delete this platform?');">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php
include_once SHARED_PATH . '/staff_footer.php';
?>
