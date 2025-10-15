<?php
// project-root/public/staff/contributors/reviews/create.php
declare(strict_types=1);
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

require_once PRIVATE_PATH . '/common/contributors/contributors_common.php';
require_once PRIVATE_PATH . '/common/contributors/contrib_common.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $subject = trim((string)($_POST['subject'] ?? ''));
  $rating  = (int)($_POST['rating'] ?? 0);
  $comment = trim((string)($_POST['comment'] ?? ''));
  if ($subject !== '' && $rating >= 0) {
    review_add(compact('subject','rating','comment'));
    flash('success', 'Review submitted.');
    header('Location: ' . url_for('/staff/contributors/reviews/')); exit;
  }
  flash('error', 'Subject is required.');
}

$page_title = 'New Review';
$active_nav = 'contributors';
$body_class = 'role--staff role--contrib';
$page_logo  = '/lib/images/icons/messages.svg';
$stylesheets[] = '/lib/css/ui.css';

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Contributors','url'=>'/staff/contributors/'],
  ['label'=>'Reviews','url'=>'/staff/contributors/reviews/'],
  ['label'=>'Create'],
];

require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="max-width:720px;padding:1.25rem 0">
  <h1>New Review</h1>
  <?= function_exists('display_session_message') ? display_session_message() : '' ?>
  <form method="post">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <div class="field"><label>Subject (name or ID)</label><input class="input" type="text" name="subject" required></div>
    <div class="field"><label>Rating (0–5)</label><input class="input" type="number" min="0" max="5" name="rating" value="5"></div>
    <div class="field"><label>Comment</label><textarea class="input" name="comment" rows="5"></textarea></div>
    <div class="actions">
      <button class="btn btn-primary" type="submit">Save</button>
      <a class="btn" href="<?= h(url_for('/staff/contributors/reviews/')) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
