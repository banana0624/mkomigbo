<?php
// project-root/public/staff/contributors/reviews/edit.php
declare(strict_types=1);
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

require_once PRIVATE_PATH . '/common/contributors/contributors_common.php';

$id  = (string)($_GET['id'] ?? $_POST['id'] ?? '');
$row = $id ? review_find($id) : null;
if (!$row) { http_response_code(404); die('Review not found'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $subject = trim((string)($_POST['subject'] ?? ''));
  $rating  = (int)($_POST['rating'] ?? 0);
  $comment = trim((string)($_POST['comment'] ?? ''));
  if ($subject !== '') {
    if (review_update($id, compact('subject','rating','comment'))) {
      flash('success', 'Review updated.');
      header('Location: ' . url_for('/staff/contributors/reviews/')); exit;
    }
    flash('error', 'Update failed.');
  } else {
    flash('error', 'Subject is required.');
  }
}

$page_title = 'Edit Review';
$active_nav = 'contributors';
$body_class = 'role--staff role--contrib';
$page_logo  = '/lib/images/icons/messages.svg';
$stylesheets[] = '/lib/css/ui.css';
$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Contributors','url'=>'/staff/contributors/'],
  ['label'=>'Reviews','url'=>'/staff/contributors/reviews/'],
  ['label'=>'Edit'],
];
require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="max-width:720px;padding:1.25rem 0">
  <h1>Edit Review</h1>
  <?= function_exists('display_session_message') ? display_session_message() : '' ?>
  <form method="post">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <input type="hidden" name="id" value="<?= h($id) ?>">
    <div class="field"><label>Subject</label><input class="input" type="text" name="subject" value="<?= h($row['subject'] ?? '') ?>" required></div>
    <div class="field"><label>Rating (0–5)</label><input class="input" type="number" name="rating" min="0" max="5" value="<?= (int)($row['rating'] ?? 0) ?>"></div>
    <div class="field"><label>Comment</label><textarea class="input" name="comment" rows="5"><?= h($row['comment'] ?? '') ?></textarea></div>
    <div class="actions">
      <button class="btn btn-primary" type="submit">Save</button>
      <a class="btn" href="<?= h(url_for('/staff/contributors/reviews/')) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
