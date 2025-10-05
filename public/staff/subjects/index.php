<?php
// project-root/public/staff/subjects/index.php

require_once __DIR__ . '/../../../private/assets/initialize.php';

$page_title = 'Subject List';
include_once PRIVATE_PATH . '/shared/staff_header.php';

$subjects = [];
if (function_exists('find_all_subjects')) {
    $subjects = find_all_subjects();
}
?>

<h2>All Subjects</h2>

<?php if (count($subjects) === 0): ?>
  <p>No subjects found.</p>
<?php else: ?>
  <ul>
    <?php foreach ($subjects as $sub): ?>
      <li>
        <a href="<?php echo url_for('/staff/subjects/show.php?id=' . u($sub['id'])); ?>">
          <?php echo h($sub['name']); ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>

<p><a href="<?php echo url_for('/staff/subjects/new.php'); ?>">+ Add New Subject</a></p>

<?php
include_once PRIVATE_PATH . '/shared/staff_footer.php';
?>
