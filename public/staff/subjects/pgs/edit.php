<?php
// project-root/public/staff/subjects/pgs/edit.php
declare(strict_types=1);

// Boot
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

// Permissions (only if your middleware exists)
if (is_file(PRIVATE_PATH . '/middleware/guard.php')) {
  define('REQUIRE_LOGIN', true);
  define('REQUIRE_PERMS', ['pages.edit']);
  require PRIVATE_PATH . '/middleware/guard.php';
}

// Grab page ID and fetch
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$page = $id > 0 ? page_get_by_id($id) : null;
if (!$page) {
  if (function_exists('flash')) flash('error', 'Page not found.');
  header('Location: ' . url_for('/staff/subjects/pgs/'));
  exit;
}

// Handle POST (update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (function_exists('csrf_check')) csrf_check();

  $data = [
    'title'        => trim((string)($_POST['title'] ?? '')),
    'slug'         => trim((string)($_POST['slug']  ?? '')),
    'body'         => (string)($_POST['body'] ?? ''),
    'is_published' => !empty($_POST['is_published']) ? 1 : 0,
  ];

  if ($data['title'] === '' || $data['slug'] === '') {
    if (function_exists('flash')) flash('error', 'Title and Slug are required.');
  } else {
    $ok = page_update_by_id($id, $data);
    if ($ok) {
      if (function_exists('flash')) flash('success', 'Page updated.');
      header('Location: ' . url_for('/staff/subjects/pgs/show.php?id=' . $id));
      exit;
    }
    if (function_exists('flash')) flash('error', 'Update failed.');
  }

  // Re-hydrate current $page for form re-display
  $page = page_get_by_id($id);
}

// Page chrome
$page_title    = 'Edit Page';
$active_nav    = 'pages';
$body_class    = 'role--staff';
$page_logo     = '/lib/images/icons/doc.svg';
$stylesheets[] = '/lib/css/ui.css';

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Pages','url'=>'/staff/subjects/pgs/'],
  ['label'=>'Edit'],
];

require PRIVATE_PATH . '/shared/staff_header.php';
?>
<main class="container" style="max-width:820px;padding:1.25rem 0">
  <h1>Edit Page</h1>

  <form method="post" action="<?= h(url_for('/staff/subjects/pgs/edit.php?id='.$id)) ?>">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <input type="hidden" name="id" value="<?= (int)$id ?>">

    <div class="field">
      <label>Title</label>
      <input class="input" type="text" name="title" value="<?= h((string)($page['title'] ?? '')) ?>" required>
    </div>

    <div class="field">
      <label>Slug</label>
      <input class="input" type="text" name="slug" value="<?= h((string)($page['slug'] ?? '')) ?>" required>
    </div>

    <div class="field">
      <label>Body</label>
      <textarea class="input" name="body" rows="10"><?= h((string)($page['body'] ?? '')) ?></textarea>
    </div>

    <div class="field">
      <label>
        <input type="checkbox" name="is_published" value="1" <?= !empty($page['is_published']) ? 'checked' : '' ?>>
        Published
      </label>
    </div>

    <div class="actions" style="display:flex;gap:.5rem;flex-wrap:wrap">
      <button class="btn btn-primary" type="submit">Save</button>
      <a class="btn" href="<?= h(url_for('/staff/subjects/pgs/show.php?id='.$id)) ?>">Cancel</a>
      <a class="btn btn-danger" href="<?= h(url_for('/staff/subjects/pgs/delete.php?id='.$id)) ?>">Delete…</a>
    </div>
  </form>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
