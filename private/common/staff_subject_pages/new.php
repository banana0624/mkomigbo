<?php
declare(strict_types=1);
/**
 * project-root/private/common/staff_subject_pages/new.php
 * Creates a page under a subject, public URLs use /pgs/
 */

require __DIR__ . '/../_common_boot.php';

$subjectSlug = $current_subject_slug ?? ($_GET['subject'] ?? '');
$subjectSlug = trim((string)$subjectSlug);

if ($subjectSlug === '') {
    http_response_code(400);
    echo 'Subject slug missing.';
    exit;
}

// load subject
$subject = null;
if (function_exists('find_subject_by_slug')) {
    $subject = find_subject_by_slug($subjectSlug);
} elseif (function_exists('subjects_catalog')) {
    $all = subjects_catalog();
    if (isset($all[$subjectSlug])) {
        $subject = $all[$subjectSlug];
    }
}

if (!$subject) {
    http_response_code(404);
    echo 'Subject not found: ' . h($subjectSlug);
    exit;
}

$errors = [];
$data = [
    'subject_id'       => $subject['id'],
    'title'            => '',
    'slug'             => '',
    'content'          => '',
    'meta_description' => '',
    'meta_keywords'    => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['title']            = trim((string)($_POST['title'] ?? ''));
    $data['slug']             = trim((string)($_POST['slug'] ?? ''));
    $data['content']          = (string)($_POST['content'] ?? '');
    $data['meta_description'] = (string)($_POST['meta_description'] ?? '');
    $data['meta_keywords']    = (string)($_POST['meta_keywords'] ?? '');

    if ($data['title'] === '') {
        $errors[] = 'Title is required.';
    }
    if ($data['slug'] === '') {
        $data['slug'] = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $data['title']));
    }

    if (empty($errors)) {
        $ok     = false;
        $new_id = 0;

        if (function_exists('insert_page')) {
            $res = insert_page($data);
            if ($res === true) {
                $ok = true;
                if (function_exists('last_insert_id')) {
                    $new_id = (int)last_insert_id();
                }
            } elseif (is_int($res)) {
                $ok     = true;
                $new_id = $res;
            }
        } elseif (function_exists('__mk_insert') && function_exists('__mk_model')) {
            $model  = __mk_model('page');
            $new_id = __mk_insert('page', $data);
            $ok     = (bool)$new_id;
        }

        if ($ok) {
            redirect_to(url_for('/staff/subjects/' . rawurlencode($subjectSlug) . '/pgs/' . rawurlencode($data['slug']) . '/show.php'));
        } else {
            $errors[] = 'Failed to create page.';
        }
    }
}

$page_title = 'New page for subject: ' . ($subject['name'] ?? $subjectSlug);
common_open('staff', 'subject', $page_title);
?>
<main class="container" style="padding:1rem 0;">
  <?php if (!empty($errors)): ?>
    <div class="errors">
      <p>Please fix:</p>
      <ul><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <form method="post">
    <div class="field">
      <label>Subject</label>
      <input type="text" value="<?= h($subject['name'] ?? $subjectSlug) ?>" disabled>
    </div>

    <div class="field">
      <label for="title">Title</label>
      <input id="title" name="title" type="text" value="<?= h($data['title']) ?>">
    </div>

    <div class="field">
      <label for="slug">Slug</label>
      <input id="slug" name="slug" type="text" value="<?= h($data['slug']) ?>" placeholder="auto from title if empty">
    </div>

    <div class="field">
      <label for="content">Content</label>
      <textarea id="content" name="content" rows="8"><?= h($data['content']) ?></textarea>
    </div>

    <div class="field">
      <label for="meta_description">Meta description</label>
      <textarea id="meta_description" name="meta_description"><?= h($data['meta_description']) ?></textarea>
    </div>

    <div class="field">
      <label for="meta_keywords">Meta keywords</label>
      <input id="meta_keywords" name="meta_keywords" type="text" value="<?= h($data['meta_keywords']) ?>">
    </div>

    <div class="actions" style="margin-top:1rem;">
      <button class="btn btn-primary" type="submit">Create Page</button>
      <a class="btn" href="<?= h(url_for('/staff/subjects/pgs/index.php?subject=' . urlencode($subjectSlug))) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php
common_close('staff', 'subject');
