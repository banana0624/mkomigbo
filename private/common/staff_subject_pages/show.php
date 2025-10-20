<?php
// project-root/private/common/staff_subject_pages/show.php
declare(strict_types=1);

/**
 * Requires in caller: $subject_slug (string), $subject_name (string optional)
 * Assumes initialize.php loads: page_find(), url_for(), flash(), etc.
 */
$init = dirname(__DIR__, 2) . '/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

/** ---- Permission gate (tolerant if wrapper already defined) ---- */
$__need_guard = (!defined('REQUIRE_LOGIN') || !defined('REQUIRE_PERMS'));
if (!defined('REQUIRE_LOGIN')) {
  define('REQUIRE_LOGIN', true);
}
if (!defined('REQUIRE_PERMS')) {
  define('REQUIRE_PERMS', ['pages.view']);
}
if ($__need_guard) {
  require PRIVATE_PATH . '/middleware/guard.php';
}

if (empty($subject_slug)) { die('show.php: $subject_slug required'); }
if (empty($subject_name)) { $subject_name = ucfirst(str_replace('-', ' ', $subject_slug)); }

// DRY logo + subject visuals
require_once __DIR__ . '/_prelude.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(404); die('Page not found'); }

// Correct argument order: (id, subject_slug)
$row = page_find($id, $subject_slug);
if (!$row) { http_response_code(404); die('Page not found'); }

$canEdit   = function_exists('auth_has_permission') ? auth_has_permission('pages.edit')   : true;
$canDelete = function_exists('auth_has_permission') ? auth_has_permission('pages.delete') : true;

$page_title     = "View Page • {$subject_name}";
$active_nav     = 'staff';
$body_class     = "role--staff subject--{$subject_slug}";
$stylesheets[]  = '/lib/css/ui.css';
$breadcrumbs    = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Subjects','url'=>'/staff/subjects/'],
  ['label'=>$subject_name,'url'=>"/staff/subjects/{$subject_slug}/"],
  ['label'=>'Pages','url'=>"/staff/subjects/{$subject_slug}/pages/"],
  ['label'=>'Show'],
];

require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="max-width:880px;padding:1.25rem 0">
  <h1 style="margin-bottom:.25rem"><?= h($row['title'] ?? '') ?></h1>
  <p class="muted" style="margin-top:0">
    Slug: <code><?= h($row['slug'] ?? '') ?></code>
    • Status: <?= !empty($row['is_published']) ? 'Published' : 'Draft' ?>
    • ID: <?= (int)$row['id'] ?>
  </p>

  <hr style="margin:.75rem 0 1rem">

  <!-- Staff preview: render stored HTML as-is -->
  <article class="prose">
    <?= $row['body'] ?? '' ?>
  </article>

  <p style="margin-top:1.25rem;display:flex;gap:.5rem;flex-wrap:wrap">
    <?php if ($canEdit): ?>
      <a class="btn" href="<?= h(url_for("/staff/subjects/{$subject_slug}/pages/edit.php?id=" . (int)$row['id'])) ?>">Edit</a>
    <?php else: ?>
      <a class="btn is-disabled" role="button" aria-disabled="true" tabindex="-1" title="You can’t edit pages" style="pointer-events:none;opacity:.55;">Edit</a>
    <?php endif; ?>

    <?php if ($canDelete): ?>
      <a class="btn btn-danger" href="<?= h(url_for("/staff/subjects/{$subject_slug}/pages/delete.php?id=" . (int)$row['id'])) ?>">Delete</a>
    <?php else: ?>
      <a class="btn btn-danger is-disabled" role="button" aria-disabled="true" tabindex="-1" title="You can’t delete pages" style="pointer-events:none;opacity:.55;">Delete</a>
    <?php endif; ?>

    <a class="btn" href="<?= h(url_for("/staff/subjects/{$subject_slug}/pages/")) ?>">&larr; Back to Pages</a>
  </p>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
