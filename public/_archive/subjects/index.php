<?php
// project-root/public/subjects/index.php

require_once dirname(__DIR__, 3) . '/private/assets/initialize.php';

// Get slug from URL
$slug = $_GET['slug'] ?? null;

// Load subject registry
$subjects = [];
$registry_file = PRIVATE_PATH . '/registry/subjects.php';
if (file_exists($registry_file)) {
    $subjects = include $registry_file;
}

// Find subject by slug
$current_subject = null;
if ($slug && isset($subjects)) {
    foreach ($subjects as $subject) {
        if ($subject['slug'] === $slug) {
            $current_subject = $subject;
            break;
        }
    }
}

// Page title
$page_title = $current_subject ? $current_subject['name'] : 'Subjects';

// Include header
include_once PRIVATE_PATH . '/shared/public_header.php';
?>

<div class="subject-container">
  <?php if ($current_subject): ?>
    <h2><?php echo h($current_subject['name']); ?></h2>
    <p><?php echo h($current_subject['meta_description']); ?></p>

    <div class="subject-pages">
      <h3>Pages under <?php echo h($current_subject['name']); ?></h3>
      <ul>
        <?php
        // Example: fetch pages by subject_id (DB)
        if (function_exists('find_pages_by_subject_id')) {
            $pages = find_pages_by_subject_id($current_subject['id']);
            foreach ($pages as $page) {
                echo '<li><a href="' . url_for('/pages/show.php?id=' . h($page['id'])) . '">' . h($page['title']) . '</a></li>';
            }
        } else {
            echo '<li>No pages available yet.</li>';
        }
        ?>
      </ul>
    </div>

  <?php else: ?>
    <h2>All Subjects</h2>
    <ul>
      <?php foreach ($subjects as $s): ?>
        <li>
          <a href="<?php echo url_for('/subjects/' . h($s['slug']) . '/'); ?>">
            <?php echo h($s['name']); ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>

<?php include_once PRIVATE_PATH . '/shared/public_footer.php'; ?>
