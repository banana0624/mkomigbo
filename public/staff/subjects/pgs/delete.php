<?php
// project-root/public/staff/subjects/pgs/delete.php
declare(strict_types=1);

// Boot
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

// Permissions (only if your middleware exists)
if (is_file(PRIVATE_PATH . '/middleware/guard.php')) {
  define('REQUIRE_LOGIN', true);
  define('REQUIRE_PERMS', ['pages.delete']);
  require PRIVATE_PATH . '/middleware/guard.php';
}

$id   = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$page = $id > 0 ? page_get_by_id($id) : null;
if (!$page) {
  if (function_exists('flash')) flash('error','Page not found.');
  header('Location: ' . url_for('/staff/subjects/pgs/'));
  exit;
}

// POST → delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (function_exists('csrf_check')) csrf_check();

  $confirm = trim((string)($_POST['confirm_text'] ?? ''));
  // Safer deletion: require typing DELETE
  if (strtoupper($confirm) !== 'DELETE') {
    if (function_exists('flash')) flash('error','Type DELETE to confirm.');
    header('Location: ' . url_for('/staff/subjects/pgs/delete.php?id='.$id));
    exit;
  }

  $ok = page_delete_by_id($id);
  if ($ok) {
    if (function_exists('flash')) flash('success','Page deleted.');
  } else {
    if (function_exists('flash')) flash('error','Delete failed.');
  }
  header('Location: ' . url_for('/staff/subjects/pgs/'));
  exit;
}

// Page chrome
$page_title    = 'Delete Page';
$active_nav    = 'pages';
$body_class    = 'role--staff';
$page_logo     = '/lib/images/icons/doc.svg';
$stylesheets[] = '/lib/css/ui.css';

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Pages','url'=>'/staff/subjects/pgs/'],
  ['label'=>'Delete'],
];

require PRIVATE_PATH . '/shared/staff_header.php';
?>
<main class="container" style="max-width:720px;padding:1.25rem 0">
  <h1>Delete Page</h1>
  <p>Really delete page <strong><?= h((string)($page['title'] ?? 'Untitled')) ?></strong> (ID #<?= (int)$id ?>)?</p>

  <form method="post" action="<?= h(url_for('/staff/subjects/pgs/delete.php?id='.$id)) ?>">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <input type="hidden" name="id" value="<?= (int)$id ?>">
    <div class="field">
      <label>Type <strong>DELETE</strong> to confirm</label>
      <input class="input" type="text" name="confirm_text" autocomplete="off">
    </div>
    <div class="actions" style="display:flex;gap:.5rem;flex-wrap:wrap">
      <button class="btn btn-danger" type="submit">Yes, delete</button>
      <a class="btn" href="<?= h(url_for('/staff/subjects/pgs/show.php?id='.$id)) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
