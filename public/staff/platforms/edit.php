<?php
// project-root/public/staff/platforms/edit.php

require_once __DIR__ . '/../../../private/assets/initialize.php';

if (defined('APP_DEBUG') && APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

$page_title = 'Edit Platform';
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

if (is_post_request()) {
    $name = $_POST['name'] ?? '';
    if (function_exists('update_platform')) {
        $res = update_platform((int)$id, ['name' => $name]);
        if ($res) {
            redirect_to(url_for('/staff/platforms/show.php?id=' . u($id)));
        }
    }
}
?>

<h2>Edit Platform</h2>
<form action="<?php echo url_for('/staff/platforms/edit.php?id=' . u($id)); ?>" method="post">
  <label for="name">Name:<br>
    <input type="text" name="name" id="name" value="<?php echo h($plt['name']); ?>" required>
  </label><br><br>
  <button type="submit">Save</button>
</form>
<p><a href="<?php echo url_for('/staff/platforms/show.php?id=' . u($id)); ?>">Back to detail</a></p>

<?php
include_once SHARED_PATH . '/staff_footer.php';
?>
