<?php
// project-root/public/staff/subjects/new.php

require_once __DIR__ . '/../../../private/assets/initialize.php';

$page_title = 'New Subject';
include_once PRIVATE_PATH . '/shared/staff_header.php';

if (is_post_request()) {
    $name = $_POST['name'] ?? '';
    if (function_exists('create_subject')) {
        $new_id = create_subject(['name' => $name]);
        if ($new_id) {
            redirect_to(url_for('/staff/subjects/'));
        }
    }
}
?>

<h2>Add New Subject</h2>
<form action="<?php echo url_for('/staff/subjects/new.php'); ?>" method="post">
  <label>Name:<br>
    <input type="text" name="name" required>
  </label><br><br>
  <button type="submit">Create</button>
</form>
<p><a href="<?php echo url_for('/staff/subjects/'); ?>">Back to list</a></p>

<?php
include_once PRIVATE_PATH . '/shared/staff_footer.php';
?>
