<?php
declare(strict_types=1);
/**
 * project-root/private/common/staff_subjects/show.php
 * Show one subject to staff.
 *
 * Called by:
 *  /staff/subjects/index.php?action=show&slug=history
 */
require __DIR__ . '/../_common_boot.php';

$ctx    = 'staff';
$entity = 'subject';

$slug = $_GET['slug'] ?? $_GET['subject'] ?? '';
$slug = trim((string)$slug);

$subject = null;
if ($slug !== '') {
    if (function_exists('find_subject_by_slug')) {
        $subject = find_subject_by_slug($slug);
    } elseif (function_exists('subjects_catalog')) {
        $all = subjects_catalog();
        if (isset($all[$slug])) {
            $subject = $all[$slug];
        }
    }
}

if (!$subject) {
    common_open($ctx, $entity, 'Subject not found');
    echo '<main class="container" style="padding:1rem 0;">';
    echo '<p>Subject <code>' . h($slug) . '</code> not found.</p>';
    echo '<p><a class="btn" href="' . h(url_for('/staff/subjects/')) . '">Back to subjects</a></p>';
    echo '</main>';
    common_close($ctx, $entity);
    exit;
}

$page_title = 'Subject: ' . ($subject['name'] ?? $slug);
common_open($ctx, $entity, $page_title);
?>
<main class="container" style="padding:1rem 0;">
  <dl class="detail">
    <dt>ID</dt>
    <dd><?= h((string)($subject['id'] ?? '')) ?></dd>

    <dt>Name</dt>
    <dd><?= h($subject['name'] ?? '') ?></dd>

    <dt>Slug</dt>
    <dd><?= h($subject['slug'] ?? $slug) ?></dd>

    <?php if (!empty($subject['meta_description'])): ?>
      <dt>Meta description</dt>
      <dd><?= h($subject['meta_description']) ?></dd>
    <?php endif; ?>

    <?php if (!empty($subject['meta_keywords'])): ?>
      <dt>Meta keywords</dt>
      <dd><?= h($subject['meta_keywords']) ?></dd>
    <?php endif; ?>
  </dl>

  <p>
    <a class="btn" href="<?= h(url_for('/staff/subjects/')) ?>">Back</a>
    <a class="btn" href="<?= h(url_for('/staff/subjects/index.php?action=edit&slug=' . urlencode($subject['slug'] ?? $slug))) ?>">Edit</a>
    <a class="btn btn-danger" href="<?= h(url_for('/staff/subjects/index.php?action=delete&slug=' . urlencode($subject['slug'] ?? $slug))) ?>">Delete</a>
  </p>
</main>
<?php
common_close($ctx, $entity);
