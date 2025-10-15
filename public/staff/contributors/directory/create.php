<?php
// project-root/public/staff/contributors/directory/create.php
declare(strict_types=1);
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

require_once PRIVATE_PATH . '/common/contributors/contributors_common.php';
require_once PRIVATE_PATH . '/common/contributors/contrib_common.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $name   = trim((string)($_POST['name'] ?? ''));
  $email  = trim((string)($_POST['email'] ?? ''));
  $handle = trim((string)($_POST['handle'] ?? ''));
  if ($name !== '') {
    contrib_add(compact('name','email','handle'));
    flash('success', 'Contributor created.');
    header('Location: ' . url_for('/staff/contributors/directory/')); exit;
  }
  flash('error', 'Name is required.');
}

$page_title = 'Create Contributor';
$active_nav = 'contributors';
$body_class = 'role--staff role--contrib';
$page_logo  = '/lib/images/icons/users.svg';
$stylesheets[] = '/lib/css/ui.css';

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Contributors','url'=>'/staff/contributors/'],
  ['label'=>'Directory','url'=>'/staff/contributors/directory/'],
  ['label'=>'Create'],
];

require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="max-width:720px;padding:1.25rem 0">
  <h1>Create Contributor</h1>
  <?= function_exists('display_session_message') ? display_session_message() : '' ?>
  <form method="post">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <div class="field"><label>Name</label><input class="input" type="text" name="name" required></div>
    <div class="field"><label>Email</label><input class="input" type="email" name="email"></div>
    <div class="field"><label>Handle</label><input class="input" type="text" name="handle" placeholder="@username"></div>
    <div class="actions">
      <button class="btn btn-primary" type="submit">Save</button>
      <a class="btn" href="<?= h(url_for('/staff/contributors/directory/')) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
