<?php
// project-root/public/staff/contributors/credits/delete.php
declare(strict_types=1);
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

require_once PRIVATE_PATH . '/common/contributors/contributors_common.php';

$id  = (string)($_GET['id'] ?? $_POST['id'] ?? '');
$row = $id ? credit_find($id) : null;
if (!$row) { http_response_code(404); die('Credit not found'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  if (isset($_POST['confirm']) && $_POST['confirm'] === '1') {
    if (credit_delete($id)) {
      flash('success', 'Credit deleted.');
    } else {
      flash('error', 'Delete failed.');
    }
    header('Location: ' . url_for('/staff/contributors/credits/')); exit;
  }
  header('Location: ' . url_for('/staff/contributors/credits/')); exit;
}

$page_title = 'Delete Credit';
$active_nav = 'contributors';
$body_class = 'role--staff role--contrib';
$page_logo  = '/lib/images/icons/hand-heart.svg';
$stylesheets[] = '/lib/css/ui.css';
$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Contributors','url'=>'/staff/contributors/'],
  ['label'=>'Credits','url'=>'/staff/contributors/credits/'],
  ['label'=>'Delete'],
];
require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="max-width:720px;padding:1.25rem 0">
  <h1>Delete Credit</h1>
  <p>Delete credit: <strong><?= h($row['title'] ?? '') ?></strong>?</p>
  <form method="post">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <input type="hidden" name="id" value="<?= h($id) ?>">
    <div class="actions">
      <button class="btn btn-danger" type="submit" name="confirm" value="1">Yes, delete</button>
      <a class="btn" href="<?= h(url_for('/staff/contributors/credits/')) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
