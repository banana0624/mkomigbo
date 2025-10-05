<?php
// project-root/public/staff/subjects/pgs/new.php

require_once __DIR__ . '/../../../../private/assets/initialize.php';

if (defined('APP_DEBUG') && APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

$page_title = 'Upload Resource';
include_once SHARED_PATH . '/staff_header.php';

$subject_id = $_GET['subject_id'] ?? '';
if (!is_numeric($subject_id)) {
    echo "<p>Invalid subject.</p>";
    echo '<p><a href="' . url_for('/staff/subjects/') . '">Back to subjects</a></p>';
    include_once SHARED_PATH . '/staff_footer.php';
    exit;
}

$subject = null;
if (function_exists('subject_registry_get')) {
    $subject = subject_registry_get((int)$subject_id);
}
if (!$subject) {
    echo "<p>Subject metadata not found.</p>";
    echo '<p><a href="' . url_for('/staff/subjects/') . '">Back to subjects</a></p>';
    include_once SHARED_PATH . '/staff_footer.php';
    exit;
}

if (is_post_request()) {
    if (isset($_FILES['resource_file']) && $_FILES['resource_file']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['resource_file']['tmp_name'];
        $orig = basename($_FILES['resource_file']['name']);
        $destRel = '/uploads/subjects/' . $subject_id . '/' . $orig;
        $destFull = PUBLIC_PATH . $destRel;

        $dir = dirname($destFull);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        if (move_uploaded_file($tmp, $destFull)) {
            if (function_exists('create_file_record')) {
                $newId = create_file_record([
                    'subject_id' => $subject_id,
                    'filename' => $orig,
                    'filepath' => $destRel,
                    'title' => $_POST['title'] ?? ''
                ]);
                if ($newId) {
                    redirect_to(url_for('/staff/subjects/pgs/index.php?subject_id=' . u($subject_id)));
                }
            }
        } else {
            echo "<p>Upload failed.</p>";
        }
    } else {
        echo "<p>No file or upload error.</p>";
    }
}
?>

<h2>Upload Resource to: <?php echo h($subject['name']); ?></h2>
<form action="<?php echo url_for('/staff/subjects/pgs/new.php?subject_id=' . u($subject_id)); ?>" method="post" enctype="multipart/form-data">
  <label for="title">Title (optional):<br>
    <input type="text" name="title" id="title">
  </label><br><br>
  <label for="resource_file">Choose file:<br>
    <input type="file" name="resource_file" id="resource_file" required>
  </label><br><br>
  <button type="submit">Upload</button>
</form>

<p><a href="<?php echo url_for('/staff/subjects/pgs/index.php?subject_id=' . u($subject_id)); ?>">Back to resources list</a></p>

<?php
include_once SHARED_PATH . '/staff_footer.php';
?>
