<?php
// project-root/public/staff/subjects/pgs/index.php

require_once __DIR__ . '/../../../../private/assets/initialize.php';

if (defined('APP_DEBUG') && APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

$page_title = 'Resources';
include_once SHARED_PATH . '/staff_header.php';

$subject_id = $_GET['subject_id'] ?? '';
if (!is_numeric($subject_id)) {
    echo "<p>Invalid subject.</p>";
    echo '<p><a href="' . url_for('/staff/subjects/') . '">Back to subjects</a></p>';
    include_once SHARED_PATH . '/staff_footer.php';
    exit;
}

$subject = null;
if (function_exists('subject_registry_get')) {
    $subject = subject_registry_get((int)$subject_id);
}
if (!$subject) {
    echo "<p>Subject not found in registry.</p>";
    echo '<p><a href="' . url_for('/staff/subjects/') . '">Back to subjects</a></p>';
    include_once SHARED_PATH . '/staff_footer.php';
    exit;
}

$files = [];
if (function_exists('find_files_for_subject')) {
    $files = find_files_for_subject((int)$subject_id);
}
?>

<h2>Resources for Subject: <?php echo h($subject['name']); ?></h2>
<p><a href="<?php echo url_for('/staff/subjects/pgs/new.php?subject_id=' . u($subject_id)); ?>">+ Upload New Resource</a></p>

<?php if (empty($files)): ?>
  <p>No resources yet.</p>
<?php else: ?>
  <table>
    <thead>
      <tr>
        <th>Title / Filename</th>
        <th>Uploaded At</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($files as $f): ?>
        <tr>
          <td><a href="<?php echo url_for($f['filepath']); ?>"><?php echo h($f['title'] ?? $f['filename']); ?></a></td>
          <td><?php echo h($f['uploaded_at']); ?></td>
          <td>
            <a href="<?php echo url_for('/staff/subjects/pgs/show.php?id=' . u($f['id'])); ?>">View</a>
            |
            <a href="<?php echo url_for('/staff/subjects/pgs/edit.php?id=' . u($f['id'])); ?>">Edit</a>
            |
            <a href="<?php echo url_for('/staff/subjects/pgs/delete.php?id=' . u($f['id'])); ?>" onclick="return confirm('Delete this resource?');">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<p><a href="<?php echo url_for('/staff/subjects/show.php?id=' . u($subject_id)); ?>">Back to subject</a></p>

<?php
include_once SHARED_PATH . '/staff_footer.php';
?>
