<?php
declare(strict_types=1);
/**
 * project-root/private/common/staff_subject_pages/edit.php
 * Uses /pgs/ in generated URLs.
 */

require __DIR__ . '/../_common_boot.php';

$subjectSlug = $current_subject_slug ?? ($_GET['subject'] ?? '');
$pageSlug    = $current_page_slug    ?? ($_GET['slug'] ?? '');

$subjectSlug = trim((string)$subjectSlug);
$pageSlug    = trim((string)$pageSlug);

if ($subjectSlug === '' || $pageSlug === '') {
    http_response_code(400);
    echo 'Bad request: subject or page slug missing.';
    exit;
}

// load subject
$subject = $current_subject ?? null;
if (!$subject) {
    if (function_exists('find_subject_by_slug')) {
        $subject = find_subject_by_slug($subjectSlug);
    } elseif (function_exists('subjects_catalog')) {
        $all = subjects_catalog();
        if (isset($all[$subjectSlug])) {
            $subject = $all[$subjectSlug];
        }
    }
}
if (!$subject) {
    http_response_code(404);
    echo 'Subject not found: ' . h($subjectSlug);
    exit;
}

// load page
$page = null;
if (function_exists('find_page_by_slug_and_subject')) {
    $page = find_page_by_slug_and_subject($pageSlug, (int)$subject['id']);
} elseif (function_exists('find_page_by_slug')) {
    $page = find_page_by_slug($pageSlug);
}

if (!$page) {
    http_response_code(404);
    echo 'Page not found: ' . h($pageSlug);
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title            = trim((string)($_POST['title'] ?? ''));
    $slugNew          = trim((string)($_POST['slug'] ?? ''));
    $content          = (string)($_POST['content'] ?? '');
    $meta_description = (string)($_POST['meta_description'] ?? '');
    $meta_keywords    = (string)($_POST['meta_keywords'] ?? '');

    if ($title === '') {
        $errors[] = 'Title is required.';
    }
    if ($slugNew === '') {
        $errors[] = 'Slug is required.';
    }

    if (empty($errors)) {
        $pageData = [
            'id'               => $page['id'],
            'subject_id'       => $subject['id'],
            'title'            => $title,
            'slug'             => $slugNew,
            'content'          => $content,
            'meta_description' => $meta_description,
            'meta_keywords'    => $meta_keywords,
        ];

        $ok = false;
        if (function_exists('update_page')) {
            $ok = update_page($pageData);
        } elseif (function_exists('page_update')) {
            $ok = page_update($pageData);
        }

        if ($ok) {
            redirect_to(url_for('/staff/subjects/' . rawurlencode($subjectSlug) . '/pgs/' . rawurlencode($slugNew) . '/show.php'));
        } else {
            $errors[] = 'Update failed.';
        }
    } else {
        // repopulate
        $page['title']            = $title;
        $page['slug']             = $slugNew;
        $page['content']          = $content;
        $page['meta_description'] = $meta_description;
        $page['meta_keywords']    = $meta_keywords;
    }
}

$page_title = 'Edit page: ' . ($page['title'] ?? $pageSlug);
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
      <label for="title">Title</label>
      <input id="title" name="title" type="text" value="<?= h($page['title'] ?? '') ?>">
    </div>

    <div class="field">
      <label for="slug">Slug</label>
      <input id="slug" name="slug" type="text" value="<?= h($page['slug'] ?? $pageSlug) ?>">
    </div>

    <div class="field">
      <label for="content">Content</label>
      <textarea id="content" name="content" rows="10"><?= h($page['content'] ?? '') ?></textarea>
    </div>

    <div class="field">
      <label for="meta_description">Meta description</label>
      <textarea id="meta_description" name="meta_description" rows="3"><?= h($page['meta_description'] ?? '') ?></textarea>
    </div>

    <div class="field">
      <label for="meta_keywords">Meta keywords</label>
      <input id="meta_keywords" name="meta_keywords" type="text" value="<?= h($page['meta_keywords'] ?? '') ?>">
    </div>

    <div class="actions" style="margin-top:1rem;">
      <button class="btn btn-primary" type="submit">Save</button>
      <a class="btn" href="<?= h(url_for('/staff/subjects/pgs/index.php?subject=' . urlencode($subjectSlug))) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php
common_close('staff', 'subject');
