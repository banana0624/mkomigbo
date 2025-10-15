<?php
// project-root/public/staff/contributors/credits/create.php
declare(strict_types=1);
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

require_once PRIVATE_PATH . '/common/contributors/contributors_common.php';
require_once PRIVATE_PATH . '/common/contributors/contrib_common.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $title       = trim((string)($_POST['title'] ?? ''));
  $url         = trim((string)($_POST['url'] ?? ''));
  $contributor = trim((string)($_POST['contributor'] ?? ''));
  $role        = trim((string)($_POST['role'] ?? ''));
  if ($title !== '') {
    credit_add(compact('title','url','contributor','role'));
    flash('success', 'Credit saved.');
    header('Location: ' . url_for('/staff/contributors/credits/')); exit;
  }
  flash('error', 'Title is required.');
}

$page_title = 'Add Credit';
$active_nav = 'contributors';
$body_class = 'role--staff role--contrib';
$page_logo  = '/lib/images/icons/hand-heart.svg';
$stylesheets[] = '/lib/css/ui.css';

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Contributors','url'=>'/staff/contributors/'],
  ['label'=>'Credits','url'=>'/staff/contributors/credits/'],
  ['label'=>'Create'],
];

require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="max-width:720px;padding:1.25rem 0">
  <h1>Add Credit</h1>
  <?= function_exists('display_session_message') ? display_session_message() : '' ?>
  <form method="post">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <div class="field"><label>Title</label><input class="input" type="text" name="title" required></div>
    <div class="field"><label>URL</label><input class="input" type="url" name="url" placeholder="https://..."></div>
    <div class="field"><label>Contributor (name or ID)</label><input class="input" type="text" name="contributor"></div>
    <div class="field"><label>Role</label><input class="input" type="text" name="role" placeholder="Research, Writing, Photo, …"></div>
    <div class="actions">
      <button class="btn btn-primary" type="submit">Save</button>
      <a class="btn" href="<?= h(url_for('/staff/contributors/credits/')) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
