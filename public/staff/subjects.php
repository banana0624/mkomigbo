<?php
// project-root/public/staff/subjects.php

require_once __DIR__ . '/../../private/assets/initialize.php';

if (defined('APP_DEBUG') && APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

$page_title = 'Subjects';
include_once SHARED_PATH . '/staff_header.php';

$subjects = [];
if (function_exists('subject_registry_all')) {
    $subjects = subject_registry_all();
} else {
    if (function_exists('find_all_subjects')) {
        $subjects = array_map(function($s) {
            return [
                'name' => $s['name'] ?? '',
                'slug' => $s['slug'] ?? ''
            ];
        }, find_all_subjects());
    }
}
?>

<h2>Subjects</h2>
<?php if (empty($subjects)) { ?>
  <p>No subjects registered.</p>
<?php } else { ?>
  <ul>
    <?php foreach ($subjects as $id => $info): ?>
      <li>
        <a href="<?php echo url_for('/staff/subjects/show.php?id=' . u($id)); ?>">
          <?php echo h($info['name']); ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
<?php } ?>

<?php
include_once SHARED_PATH . '/staff_footer.php';
?>
