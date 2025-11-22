<?php
declare(strict_types=1);
/**
 * project-root/private/common/staff_subjects/edit.php
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

$errors = [];
$data = [
    'id'               => (int)($subject['id'] ?? 0),
    'name'             => $subject['name'] ?? '',
    'slug'             => $subject['slug'] ?? $slug,
    'meta_description' => $subject['meta_description'] ?? '',
    'meta_keywords'    => $subject['meta_keywords'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['name']             = trim((string)($_POST['name'] ?? ''));
    $data['slug']             = trim((string)($_POST['slug'] ?? ''));
    $data['meta_description'] = (string)($_POST['meta_description'] ?? '');
    $data['meta_keywords']    = (string)($_POST['meta_keywords'] ?? '');

    if ($data['name'] === '') {
        $errors[] = 'Name is required.';
    }
    if ($data['slug'] === '') {
        $errors[] = 'Slug is required.';
    }

    if (empty($errors)) {
        $ok = false;
        if (function_exists('update_subject')) {
            $ok = update_subject($data);
        }
        if ($ok) {
            redirect_to(url_for('/staff/subjects/'));
        } else {
            $errors[] = 'Update failed.';
        }
    }
}

$page_title = 'Edit Subject: ' . ($subject['name'] ?? $slug);
common_open($ctx, $entity, $page_title);
?>
<main class="container" style="padding:1rem 0;">
  <?php if ($errors): ?>
    <div class="errors">
      <p>Please fix:</p>
      <ul><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <form method="post">
    <div class="field">
      <label for="name">Name</label>
      <input id="name" name="name" type="text" value="<?= h($data['name']) ?>">
    </div>

    <div class="field">
      <label for="slug">Slug</label>
      <input id="slug" name="slug" type="text" value="<?= h($data['slug']) ?>">
    </div>

    <div class="field">
      <label for="meta_description">Meta description</label>
      <textarea id="meta_description" name="meta_description" rows="3"><?= h($data['meta_description']) ?></textarea>
    </div>

    <div class="field">
      <label for="meta_keywords">Meta keywords</label>
      <input id="meta_keywords" name="meta_keywords" type="text" value="<?= h($data['meta_keywords']) ?>">
    </div>

    <div style="margin-top:1rem;">
      <button class="btn btn-primary" type="submit">Save</button>
      <a class="btn" href="<?= h(url_for('/staff/subjects/')) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php
common_close($ctx, $entity);
