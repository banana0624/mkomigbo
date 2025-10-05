<?php
// project-root/public/index.php

require_once __DIR__ . '/../private/assets/initialize.php';

if (defined('APP_DEBUG') && APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

$page_title = 'Home';
include_once SHARED_PATH . '/public_header.php';

$subjects = [];
if (function_exists('subject_registry_all')) {
    $subjects = subject_registry_all();
} else {
    if (function_exists('find_all_subjects')) {
        foreach (find_all_subjects() as $s) {
            $subjects[$s['id']] = [
                'name' => $s['name'] ?? '',
                'slug' => $s['slug'] ?? '',
                'icon' => ''
            ];
        }
    }
}
?>

<main class="home-subject-grid">
  <?php foreach ($subjects as $id => $info):
        $name = $info['name'];
        $slug = $info['slug'];
        $icon = $info['icon'] ?? '/lib/images/subjects/' . $slug . '.png';
  ?>
    <div class="subject-card">
      <a href="<?php echo url_for('/subjects/show.php?id=' . u($id)); ?>">
        <img src="<?php echo h($icon); ?>" alt="<?php echo h($name); ?> Logo">
        <span><?php echo h($name); ?></span>
      </a>
    </div>
  <?php endforeach; ?>
</main>

<?php
include_once SHARED_PATH . '/public_footer.php';
?>
