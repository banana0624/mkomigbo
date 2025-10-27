<?php
// project-root/public/staff/contributors/reviews/new.php
declare(strict_types=1);

$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

define('REQUIRE_LOGIN', true);
define('REQUIRE_PERMS', ['contributors.reviews.create']);
require PRIVATE_PATH . '/common/contributors/contrib_common.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $title  = trim((string)($_POST['title'] ?? ''));
  $rating = (int)($_POST['rating'] ?? 0);
  if ($title !== '') {
    // TODO: insert_review(['title'=>$title,'rating'=>$rating])
    if (function_exists('flash')) flash('success','Review created.');
    header('Location: ' . url_for('/staff/contributors/reviews/')); exit;
  }
  if (function_exists('flash')) flash('error','Title is required.');
}

$breadcrumbs = contrib_breadcrumbs([['label'=>'Reviews','url'=>'/staff/contributors/reviews/'],['label'=>'New']]);
contrib_header('Add Review • Reviews');
?>
<main class="container" style="max-width:760px;padding:1.25rem 0">
  <h1>Add Review</h1>
  <?= function_exists('display_session_message') ? display_session_message() : '' ?>
  <form method="post">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <div class="field"><label>Title</label>
      <input class="input" type="text" name="title" required value="<?= h($_POST['title'] ?? '') ?>">
    </div>
    <div class="field"><label>Rating (1–5)</label>
      <input class="input" type="number" name="rating" min="1" max="5" value="<?= h($_POST['rating'] ?? '5') ?>">
    </div>
    <div class="actions">
      <button class="btn btn-primary" type="submit">Create</button>
      <a class="btn" href="<?= h(url_for('/staff/contributors/reviews/')) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php contrib_footer(); ?>
