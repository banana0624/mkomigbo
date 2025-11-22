<?php
declare(strict_types=1);
/**
 * project-root/private/common/staff_subjects/delete.php
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ok = false;
    if (function_exists('delete_subject')) {
        $ok = delete_subject((int)$subject['id']);
    }
    redirect_to(url_for('/staff/subjects/'));
    exit;
}

$page_title = 'Delete subject: ' . ($subject['name'] ?? $slug);
common_open($ctx, $entity, $page_title);
?>
<main class="container" style="padding:1rem 0;">
  <p>Are you sure you want to delete this subject?</p>
  <dl>
    <dt>Name</dt>
    <dd><?= h($subject['name'] ?? '') ?></dd>
    <dt>Slug</dt>
    <dd><?= h($subject['slug'] ?? $slug) ?></dd>
  </dl>

  <form method="post">
    <button class="btn btn-danger" type="submit">Yes, delete</button>
    <a class="btn" href="<?= h(url_for('/staff/subjects/')) ?>">Cancel</a>
  </form>
</main>
<?php
common_close($ctx, $entity);
