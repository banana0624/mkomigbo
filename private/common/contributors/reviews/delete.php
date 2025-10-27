<?php
// project-root/public/staff/contributors/reviews/delete.php
declare(strict_types=1);
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

if (is_file(PRIVATE_PATH . '/middleware/guard.php')) {
  define('REQUIRE_LOGIN', true);
  define('REQUIRE_PERMS', ['contributors.reviews.delete']);
  require PRIVATE_PATH . '/middleware/guard.php';
}

require_once PRIVATE_PATH . '/common/contributors/contributors_common.php';

$id  = (string)($_GET['id'] ?? $_POST['id'] ?? '');
$row = $id ? review_find($id) : null;
if (!$row) { http_response_code(404); die('Review not found'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $typed = trim((string)($_POST['confirm_text'] ?? ''));
  if ($typed === 'DELETE') {
    $ok = review_delete($id);
    flash($ok ? 'success':'error', $ok ? 'Review deleted.' : 'Delete failed.');
    header('Location: ' . url_for('/staff/contributors/reviews/')); exit;
  }
  flash('error','Please type DELETE to confirm.');
}

$page_title = 'Delete Review';
$active_nav = 'contributors';
$stylesheets[] = '/lib/css/ui.css';
$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Contributors','url'=>'/staff/contributors/'],
  ['label'=>'Reviews','url'=>'/staff/contributors/reviews/'],
  ['label'=>'Delete'],
];
require PRIVATE_PATH . '/shared/staff_header.php';
?>
<main class="container" style="max-width:720px;padding:1.25rem 0">
  <h1>Delete Review</h1>
  <p>To delete the review for <strong><?= h($row['subject'] ?? '') ?></strong>, please type <code>DELETE</code> and press the button.</p>
  <form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= h($id) ?>">
    <div class="field">
      <label>Type DELETE</label>
      <input class="input" type="text" name="confirm_text" autocomplete="off" required>
    </div>
    <div class="actions">
      <button class="btn btn-danger" type="submit">Delete</button>
      <a class="btn" href="<?= h(url_for('/staff/contributors/reviews/')) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
