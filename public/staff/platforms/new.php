<?php
/// project-root/public/staff/platforms/new.php

require_once __DIR__ . '/../../../private/assets/initialize.php';

if (defined('APP_DEBUG') && APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

$page_title = 'New Platform';
include_once SHARED_PATH . '/staff_header.php';

if (is_post_request()) {
    $name = $_POST['name'] ?? '';
    if (function_exists('create_platform')) {
        $newId = create_platform(['name' => $name]);
        if ($newId) {
            redirect_to(url_for('/staff/platforms/'));
        }
    }
}
?>

<h2>Add New Platform</h2>
<form action="<?php echo url_for('/staff/platforms/new.php'); ?>" method="post">
  <label for="name">Name:<br>
    <input type="text" name="name" id="name" required>
  </label><br><br>
  <button type="submit">Create Platform</button>
</form>
<p><a href="<?php echo url_for('/staff/platforms/'); ?>">Back to list</a></p>

<?php
include_once SHARED_PATH . '/staff_footer.php';
?>
