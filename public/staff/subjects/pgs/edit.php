<?php
// project-root/public/staff/subjects/pgs/edit.php

require_once __DIR__ . '/../../../../private/assets/initialize.php';

if (defined('APP_DEBUG') && APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

$page_title = 'Edit Resource';
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

if (is_post_request()) {
    $newTitle = $_POST['title'] ?? '';
    if (function_exists('update_file_record')) {
        $res = update_file_record((int)$id, ['title' => $newTitle]);
        if ($res) {
            redirect_to(url_for('/staff/subjects/pgs/show.php?id=' . u($id)));
        }
    }
}
?>

<h2>Edit Resource</h2>
<form action="<?php echo url_for('/staff/subjects/pgs/edit.php?id=' . u($id)); ?>" method="post">
  <label for="title">Title:<br>
    <input type="text" name="title" id="title" value="<?php echo h($file['title']); ?>">
  </label><br><br>
  <button type="submit">Save</button>
</form>

<p><a href="<?php echo url_for('/staff/subjects/pgs/show.php?id=' . u($id)); ?>">Back to detail</a></p>

<?php
include_once SHARED_PATH . '/staff_footer.php';
?>
