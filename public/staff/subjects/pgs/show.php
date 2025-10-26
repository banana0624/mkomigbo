<?php
// project-root/public/staff/subjects/pgs/show.php
declare(strict_types=1);

// Boot
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

// Permissions (only if your middleware exists)
if (is_file(PRIVATE_PATH . '/middleware/guard.php')) {
  define('REQUIRE_LOGIN', true);
  define('REQUIRE_PERMS', ['pages.view']);
  require PRIVATE_PATH . '/middleware/guard.php';
}

$id   = (int)($_GET['id'] ?? 0);
$page = $id > 0 ? page_get_by_id($id) : null;
if (!$page) {
  if (function_exists('flash')) flash('error','Page not found.');
  header('Location: ' . url_for('/staff/subjects/pgs/'));
  exit;
}

// Page chrome
$page_title    = 'View Page';
$active_nav    = 'pages';
$body_class    = 'role--staff';
$page_logo     = '/lib/images/icons/doc.svg';
$stylesheets[] = '/lib/css/ui.css';

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Pages','url'=>'/staff/subjects/pgs/'],
  ['label'=>'View'],
];

require PRIVATE_PATH . '/shared/staff_header.php';
?>
<main class="container" style="max-width:860px;padding:1.25rem 0">
  <h1>Page #<?= (int)$page['id'] ?></h1>

  <div class="grid" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin:1rem 0">
    <div class="card" style="padding:1rem;border:1px solid #ddd;border-radius:.5rem">
      <h3 style="margin:.25rem 0 .5rem">Meta</h3>
      <dl class="dl">
        <dt>Title</dt><dd><?= h((string)($page['title'] ?? '')) ?></dd>
        <dt>Slug</dt><dd><?= h((string)($page['slug']  ?? '')) ?></dd>
        <dt>Published</dt><dd><?= !empty($page['is_published']) ? 'Yes' : 'No' ?></dd>
        <dt>Created</dt><dd><?= h((string)($page['created_at'] ?? '')) ?></dd>
        <dt>Updated</dt><dd><?= h((string)($page['updated_at'] ?? '')) ?></dd>
        <?php if (!empty($page['subject_slug']) || !empty($page['subject_id'])): ?>
          <dt>Subject</dt>
          <dd>
            <?php if (!empty($page['subject_slug'])): ?>
              <?= h((string)$page['subject_slug']) ?>
            <?php elseif (!empty($page['subject_id'])): ?>
              #<?= (int)$page['subject_id'] ?>
            <?php endif; ?>
          </dd>
        <?php endif; ?>
      </dl>
    </div>

    <div class="card" style="padding:1rem;border:1px solid #ddd;border-radius:.5rem">
      <h3 style="margin:.25rem 0 .5rem">Body</h3>
      <div class="prose" style="white-space:pre-wrap"><?= h((string)($page['body'] ?? '')) ?></div>
    </div>
  </div>

  <div class="actions" style="display:flex;gap:.5rem;flex-wrap:wrap">
    <a class="btn btn-primary" href="<?= h(url_for('/staff/subjects/pgs/edit.php?id='.$id)) ?>">Edit</a>
    <a class="btn btn-danger"  href="<?= h(url_for('/staff/subjects/pgs/delete.php?id='.$id)) ?>">Delete…</a>
    <a class="btn" href="<?= h(url_for('/staff/subjects/pgs/')) ?>">&larr; Back to Pages</a>
  </div>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
