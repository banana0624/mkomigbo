<?php
// project-root/public/contributors/index.php

require_once __DIR__ . '/../../../private/assets/initialize.php';
$page_title = 'Contributors';
include_once PRIVATE_PATH . '/shared/staff_header.php';

$contributors = [];
if (function_exists('find_all_contributors')) {
    $contributors = find_all_contributors();
}
?>

<h2>All Contributors</h2>
<p><a href="<?php echo url_for('/staff/contributors/new.php'); ?>">+ Add Contributor</a></p>

<table>
  <thead>
    <tr><th>ID</th><th>Name</th><th>Actions</th></tr>
  </thead>
  <tbody>
    <?php foreach ($contributors as $c): ?>
      <tr>
        <td><?php echo h($c['id']); ?></td>
        <td><a href="<?php echo url_for('/staff/contributors/show.php?id=' . u($c['id'])); ?>">
              <?php echo h($c['name']); ?></a></td>
        <td>
          <a href="<?php echo url_for('/staff/contributors/edit.php?id=' . u($c['id'])); ?>">Edit</a> |
          <a href="<?php echo url_for('/staff/contributors/delete.php?id=' . u($c['id'])); ?>"
             onclick="return confirm('Delete contributor?');">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php
include_once PRIVATE_PATH . '/shared/staff_footer.php';
?>
