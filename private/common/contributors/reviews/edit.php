<?php
// project-root/public/staff/contributors/reviews/edit.php
declare(strict_types=1);

$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

define('REQUIRE_LOGIN', true);
define('REQUIRE_PERMS', ['contributors.reviews.edit']);
require PRIVATE_PATH . '/common/contributors/contrib_common.php';

$id = (int)($_GET['id'] ?? 0);
// TODO: $row = find_review($id);
$row = ['id'=>$id,'title'=>'Sample Review','rating'=>5]; // placeholder

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $title  = trim((string)($_POST['title'] ?? ''));
  $rating = (int)($_POST['rating'] ?? 0);
  if ($title !== '') {
    // TODO: update_review($id, ['title'=>$title,'rating'=>$rating])
    if (function_exists('flash')) flash('success','Review updated.');
    header('Location: ' . url_for('/staff/contributors/reviews/')); exit;
  }
  if (function_exists('flash')) flash('error','Title is required.');
}

$breadcrumbs = contrib_breadcrumbs([['label'=>'Reviews','url'=>'/staff/contributors/reviews/'],['label'=>'Edit']]);
contrib_header('Edit Review • Reviews');
?>
<main class="container" style="max-width:760px;padding:1.25rem 0">
  <h1>Edit Review</h1>
  <?= function_exists('display_session_message') ? display_session_message() : '' ?>
  <form method="post">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <div class="field"><label>Title</label>
      <input class="input" type="text" name="title" required value="<?= h($_POST['title'] ?? $row['title'] ?? '') ?>">
    </div>
    <div class="field"><label>Rating (1–5)</label>
      <input class="input" type="number" name="rating" min="1" max="5" value="<?= h($_POST['rating'] ?? $row['rating'] ?? '5') ?>">
    </div>
    <div class="actions">
      <button class="btn btn-primary" type="submit">Save</button>
      <a class="btn" href="<?= h(url_for('/staff/contributors/reviews/')) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php contrib_footer(); ?>
