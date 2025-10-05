<?php
// project-root/public/staff/subjects/edit.php

require_once __DIR__ . '/../../../private/assets/initialize.php';
$page_title = 'Edit Subject';
include_once PRIVATE_PATH . '/shared/staff_header.php';

$id = $_GET['id'] ?? '';
$subject = null;
if (function_exists('find_subject_by_id')) {
    $subject = find_subject_by_id($id);
}

if (!$subject) {
    echo "<p>Subject not found.</p>";
} else {
    if (is_post_request()) {
        $name = $_POST['name'] ?? '';
        if (function_exists('update_subject')) {
            $res = update_subject($id, ['name' => $name]);
            if ($res) {
                redirect_to(url_for('/staff/subjects/'));
            }
        }
    }
    ?>
    <h2>Edit Subject</h2>
    <form action="<?php echo url_for('/staff/subjects/edit.php?id=' . u($id)); ?>" method="post">
      <label>Name:<br>
        <input type="text" name="name" value="<?php echo h($subject['name']); ?>" required>
      </label><br><br>
      <button type="submit">Update</button>
    </form>
    <p><a href="<?php echo url_for('/staff/subjects/'); ?>">Back to list</a></p>
    <?php
}

include_once PRIVATE_PATH . '/shared/staff_footer.php';
?>
