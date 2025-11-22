<?php
declare(strict_types=1);
/**
 * project-root/private/common/staff_subjects/new.php
 * Create a new subject (staff)
 */
require __DIR__ . '/../_common_boot.php';

$ctx    = 'staff';
$entity = 'subject';
$page_title = 'New Subject';

$errors = [];
$data = [
    'name'             => '',
    'slug'             => '',
    'meta_description' => '',
    'meta_keywords'    => '',
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
        // simple slug from name
        $data['slug'] = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $data['name']));
    }

    if (empty($errors)) {
        $ok = false;
        $new_id = 0;

        if (function_exists('insert_subject')) {
            $res = insert_subject($data);
            if ($res === true) {
                $ok = true;
                if (function_exists('last_insert_id')) {
                    $new_id = (int)last_insert_id();
                }
            } elseif (is_int($res)) {
                $ok = true;
                $new_id = $res;
            }
        }

        if ($ok) {
            // go back to staff subjects list
            redirect_to(url_for('/staff/subjects/'));
        } else {
            $errors[] = 'Failed to create subject.';
        }
    }
}

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
      <input id="slug" name="slug" type="text" value="<?= h($data['slug']) ?>" placeholder="e.g. history">
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
      <button class="btn btn-primary" type="submit">Create subject</button>
      <a class="btn" href="<?= h(url_for('/staff/subjects/')) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php
common_close($ctx, $entity);
