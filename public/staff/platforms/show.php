<?php
// project-root/public/platforms/show.php

require_once __DIR__ . '/../../../private/assets/initialize.php';

if (defined('APP_DEBUG') && APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

$page_title = 'Platform Detail';
include_once SHARED_PATH . '/staff_header.php';

$id = $_GET['id'] ?? '';
if (!is_numeric($id)) {
    echo "<p>Invalid platform id.</p>";
    echo '<p><a href="' . url_for('/staff/platforms/') . '">Back</a></p>';
    include_once SHARED_PATH . '/staff_footer.php';
    exit;
}

$plt = null;
if (function_exists('find_platform_by_id')) {
    $plt = find_platform_by_id((int)$id);
}
if (!$plt) {
    echo "<p>Platform not found.</p>";
    echo '<p><a href="' . url_for('/staff/platforms/') . '">Back</a></p>';
    include_once SHARED_PATH . '/staff_footer.php';
    exit;
}
?>

<h2><?php echo h($plt['name']); ?></h2>

<p>
  <a href="<?php echo url_for('/staff/platforms/edit.php?id=' . u($id)); ?>">Edit</a> |
  <a href="<?php echo url_for('/staff/platforms/delete.php?id=' . u($id)); ?>" onclick="return confirm('Delete?');">Delete</a>
</p>
<p><a href="<?php echo url_for('/staff/platforms/'); ?>">Back to platforms list</a></p>

<?php
include_once SHARED_PATH . '/staff_footer.php';
?>
