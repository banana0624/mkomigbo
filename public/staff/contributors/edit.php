<?php
// project-root/public/staff/contributors/edit.php

require_once __DIR__ . '/../../../private/assets/initialize.php';

if (defined('APP_DEBUG') && APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

$page_title = 'Edit Contributor';
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

if (is_post_request()) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    if (function_exists('update_contributor')) {
        $res = update_contributor((int)$id, ['name' => $name, 'email' => $email]);
        if ($res) {
            redirect_to(url_for('/staff/contributors/show.php?id=' . u($id)));
        }
    }
}
?>

<h2>Edit Contributor</h2>
<form action="<?php echo url_for('/staff/contributors/edit.php?id=' . u($id)); ?>" method="post">
  <label for="name">Name:<br>
    <input type="text" name="name" id="name" value="<?php echo h($contributor['name']); ?>" required>
  </label><br><br>
  <label for="email">Email:<br>
    <input type="email" name="email" id="email" value="<?php echo h($contributor['email']); ?>" required>
  </label><br><br>
  <button type="submit">Update</button>
</form>
<p><a href="<?php echo url_for('/staff/contributors/show.php?id=' . u($id)); ?>">Back to detail</a></p>

<?php
include_once SHARED_PATH . '/staff_footer.php';
?>
