<?php
// project-root/public/staff/subjects/pgs/show.php

require_once __DIR__ . '/../../../../private/assets/initialize.php';

if (defined('APP_DEBUG') && APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

$page_title = 'Resource Detail';
include_once SHARED_PATH . '/staff_header.php';

$id = $_GET['id'] ?? '';
if (!is_numeric($id)) {
    echo "<p>Invalid resource id.</p>";
    echo '<p><a href="' . url_for('/staff/subjects/') . '">Back to subjects</a></p>';
    include_once SHARED_PATH . '/staff_footer.php';
    exit;
}

$file = null;
if (function_exists('find_file_by_id')) {
    $file = find_file_by_id((int)$id);
}
if (!$file) {
    echo "<p>Resource not found.</p>";
    echo '<p><a href="' . url_for('/staff/subjects/') . '">Back to subjects</a></p>';
    include_once SHARED_PATH . '/staff_footer.php';
    exit;
}

$subject_id = $file['subject_id'];
?>

<h2><?php echo h($file['title'] ?? $file['filename']); ?></h2>
<p><strong>Filename:</strong> <?php echo h($file['filename']); ?></p>
<p><strong>Uploaded at:</strong> <?php echo h($file['uploaded_at']); ?></p>

<p><a href="<?php echo url_for($file['filepath']); ?>">Download / View File</a></p>

<p>
  <a href="<?php echo url_for('/staff/subjects/pgs/edit.php?id=' . u($id)); ?>">Edit</a> |
  <a href="<?php echo url_for('/staff/subjects/pgs/delete.php?id=' . u($id)); ?>" onclick="return confirm('Delete this resource?');">Delete</a>
</p>

<p><a href="<?php echo url_for('/staff/subjects/pgs/index.php?subject_id=' . u($subject_id)); ?>">Back to resources list</a></p>

<?php
include_once SHARED_PATH . '/staff_footer.php';
?>
