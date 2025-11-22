<?php
declare(strict_types=1);
/**
 * project-root/public/staff/subjects/history/pages/new.php
 * Create a new page record for subject “history”
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

$page_title    = 'New Page for ' . h($subject['name']);
$stylesheets[] = '/lib/css/ui.css';

require PRIVATE_PATH . '/shared/header.php';

$title        = '';
$slug         = '';
$body         = '';
$is_published = 0;
$errors       = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title        = trim((string)($_POST['title'] ?? ''));
    $slug         = trim((string)($_POST['slug'] ?? ''));
    $body         = (string)($_POST['body'] ?? '');
    $is_published = !empty($_POST['is_published']) ? 1 : 0;

    if ($title === '' || $slug === '') {
        $errors[] = 'Title & Slug are required.';
    }

    if (empty($errors)) {
        $new_id = subject_page_create_by_subject_slug($subject_slug, [
            'title'        => $title,
            'slug'         => $slug,
            'body'         => $body,
            'is_published' => $is_published,
        ]);
        if ($new_id) {
            redirect_to(url_for('/staff/subjects/' . h($subject_slug) . '/pages/show.php?id=' . urlencode($new_id)));
        } else {
            $errors[] = 'Create failed.';
        }
    }
}
?>
<main class="container" style="padding:1rem 0">
  <h1>New Page for: <?= h($subject['name']) ?></h1>
  <?php if (!empty($errors)): ?>
    <div class="errors">
      <ul>
        <?php foreach ($errors as $error): ?>
          <li><?= h($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
  <form action="<?= url_for('/staff/subjects/' . h($subject_slug) . '/pages/new.php') ?>" method="post">
    <div class="form-group">
      <label for="title">Title</label>
      <input type="text" name="title" id="title" value="<?= h($title) ?>" required>
    </div>
    <div class="form-group">
      <label for="slug">Slug</label>
      <input type="text" name="slug" id="slug" value="<?= h($slug) ?>" required>
    </div>
    <div class="form-group">
      <label for="body">Body</label>
      <textarea name="body" id="body"><?= h($body) ?></textarea>
    </div>
    <div class="form-group">
      <label>
        <input type="checkbox" name="is_published" value="1" <?= $is_published ? 'checked' : '' ?>>
        Published
      </label>
    </div>
    <button class="btn" type="submit">Create Page</button>
  </form>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
