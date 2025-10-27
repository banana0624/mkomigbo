<?php
// project-root/public/staff/contributors/show.php

require_once __DIR__ . '/../../../private/assets/initialize.php';

if (defined('APP_DEBUG') && APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

$page_title = 'Contributor Detail';
include_once SHARED_PATH . '/staff_header.php';

$id = $_GET['id'] ?? '';
if (!is_numeric($id)) {
    echo "<p>Invalid contributor id.</p>";
    echo '<p><a href="' . url_for('/staff/contributors/') . '">Back</a></p>';
    include_once SHARED_PATH . '/staff_footer.php';
    exit;
}

$contributor = null;
if (function_exists('find_contributor_by_id')) {
    $contributor = find_contributor_by_id((int)$id);
}
if (!$contributor) {
    echo "<p>Contributor not found.</p>";
    echo '<p><a href="' . url_for('/staff/contributors/') . '">Back</a></p>';
    include_once SHARED_PATH . '/staff_footer.php';
    exit;
}
?>

<h2><?php echo h($contributor['name']); ?></h2>
<p><strong>Email:</strong> <?php echo h($contributor['email']); ?></p>

<p>
  <a href="<?php echo url_for('/staff/contributors/edit.php?id=' . u($id)); ?>">Edit</a> |
  <a href="<?php echo url_for('/staff/contributors/delete.php?id=' . u($id)); ?>" onclick="return confirm('Delete?');">Delete</a>
</p>
<p><a href="<?php echo url_for('/staff/contributors/'); ?>">Back to contributors list</a></p>

<?php
include_once SHARED_PATH . '/staff_footer.php';
?>


