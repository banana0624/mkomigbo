<?php
declare(strict_types=1);
/**
 * project-root/public/staff/subjects/history/pages/show.php
 * View details of a single page record for subject “history”
 */

require_once dirname(__DIR__, 5) . '/private/assets/initialize.php';
require_once PRIVATE_PATH . '/functions/auth.php';
require_staff();

$subject_slug = 'history';
$subject      = subject_row_by_slug($subject_slug);
if (!$subject) {
    http_response_code(404);
    die('Subject not found');
}

$page_id = (int)($_GET['id'] ?? 0);
$page    = page_find($page_id, $subject_slug);
if (!$page) {
    http_response_code(404);
    die('Page not found');
}

$page_title    = 'View Page: ' . h($page['title']);
$stylesheets[] = '/lib/css/ui.css';

require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="padding:1rem 0">
  <h1>Page: <?= h($page['title']) ?></h1>
  <dl class="details">
    <dt>ID</dt><dd><?= (int)$page['id'] ?></dd>
    <dt>Slug</dt><dd><?= h($page['slug']) ?></dd>
    <dt>Published</dt><dd><?= !empty($page['is_published']) ? 'Yes' : 'No' ?></dd>
    <dt>Created At</dt><dd><?= h($page['created_at']) ?></dd>
    <dt>Updated At</dt><dd><?= h($page['updated_at']) ?></dd>
    <dt>Body</dt><dd><?= nl2br(h($page['body'])) ?></dd>
  </dl>
  <p>
    <a class="btn" href="<?= url_for('/staff/subjects/' . h($subject_slug) . '/pages/edit.php?id=' . urlencode($page_id)) ?>">Edit</a>
    |
    <a class="btn" href="<?= url_for('/staff/subjects/' . h($subject_slug) . '/pages/delete.php?id=' . urlencode($page_id)) ?>" onclick="return confirm('Delete this page?');">Delete</a>
    |
    <a class="btn" href="<?= url_for('/staff/subjects/' . h($subject_slug) . '/pages/index.php') ?>">Back to List</a>
  </p>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
