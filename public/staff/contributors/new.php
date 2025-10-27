<?php
// project-root/public/staff/contributors/new.php

require_once __DIR__ . '/../../../private/assets/initialize.php';

if (defined('APP_DEBUG') && APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

$page_title = 'New Contributor';
include_once SHARED_PATH . '/staff_header.php';

if (is_post_request()) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    if (function_exists('create_contributor')) {
        $newId = create_contributor(['name' => $name, 'email' => $email]);
        if ($newId) {
            redirect_to(url_for('/staff/contributors/'));
        }
    }
}
?>

<h2>Add New Contributor</h2>
<form action="<?php echo url_for('/staff/contributors/new.php'); ?>" method="post">
  <label for="name">Name:<br>
    <input type="text" name="name" id="name" required>
  </label><br><br>
  <label for="email">Email:<br>
    <input type="email" name="email" id="email" required>
  </label><br><br>
  <button type="submit">Create</button>
</form>
<p><a href="<?php echo url_for('/staff/contributors/'); ?>">Back to list</a></p>

<?php
include_once SHARED_PATH . '/staff_footer.php';
?>


