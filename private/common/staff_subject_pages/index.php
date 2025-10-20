<?php
// project-root/private/common/staff_subject_pages/index.php
declare(strict_types=1);

/**
 * Requires (from the caller wrapper):
 *   - $subject_slug (string)
 *   - $subject_name (string optional; will be derived if empty)
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

if (empty($subject_slug)) { die('index.php: $subject_slug required'); }
if (empty($subject_name)) { $subject_name = ucfirst(str_replace('-', ' ', $subject_slug)); }

// DRY subject prelude
require_once __DIR__ . '/_prelude.php';

// Pull data
$pages = [];
if (function_exists('pages_list_by_subject')) {
  $pages = pages_list_by_subject($subject_slug);
}
if (empty($pages) && function_exists('find_pages_by_subject_slug')) {
  $pages = find_pages_by_subject_slug($subject_slug);
}

// What can the current user do?
$canCreate  = function_exists('auth_has_permission') ? auth_has_permission('pages.create')  : true;
$canEdit    = function_exists('auth_has_permission') ? auth_has_permission('pages.edit')    : true;
$canDelete  = function_exists('auth_has_permission') ? auth_has_permission('pages.delete')  : true;
$canPublish = function_exists('auth_has_permission') ? auth_has_permission('pages.publish') : true;

$page_title     = "Pages • {$subject_name}";
$active_nav     = 'staff';
$body_class     = "role--staff subject--{$subject_slug}";
$stylesheets[]  = '/lib/css/ui.css';
$stylesheets[]  = '/lib/css/landing.css';
$breadcrumbs    = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Subjects','url'=>'/staff/subjects/'],
  ['label'=>$subject_name,'url'=>"/staff/subjects/{$subject_slug}/"],
  ['label'=>'Pages'],
];

require PRIVATE_PATH . '/shared/header.php';
?>
<main id="main" class="container" style="max-width:1000px;margin:1.25rem auto;padding:0 1rem;">
  <header style="display:flex;justify-content:space-between;align-items:center;margin:.25rem 0 1rem;">
    <div>
      <h1 style="margin:0;">Pages — <?= h($subject_name) ?></h1>
      <p class="muted" style="margin:.25rem 0 0;">
        <?= count($pages) ?> <?= count($pages) === 1 ? 'page' : 'pages' ?> in this subject
      </p>
    </div>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap">
      <?php if ($canCreate): ?>
        <a class="btn btn-primary" href="<?= h(url_for("/staff/subjects/{$subject_slug}/pages/new.php")) ?>">New Page</a>
      <?php else: ?>
        <a class="btn btn-primary is-disabled" role="button" aria-disabled="true" tabindex="-1" title="You don’t have permission to create pages" style="pointer-events:none;opacity:.55;">New Page</a>
      <?php endif; ?>
      <a class="btn" href="<?= h(url_for("/staff/subjects/{$subject_slug}/")) ?>">Subject Hub</a>
    </div>
  </header>

  <div class="table-wrap">
    <table class="table">
      <thead>
        <tr>
          <th style="width:72px">ID</th>
          <th>Title</th>
          <th>Slug</th>
          <th style="width:110px">Status</th>
          <th class="actions" style="width:260px">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!$pages): ?>
        <tr>
          <td colspan="5" class="muted">No pages yet.</td>
        </tr>
      <?php else: foreach ($pages as $p): ?>
        <?php
          $id    = (int)($p['id'] ?? 0);
          $title = (string)($p['title'] ?? '');
          $slug  = (string)($p['slug'] ?? '');
          $pub   = !empty($p['is_published']);

          // tiny badge styles (inline so you don’t need extra CSS right now)
          $badgeStyle = 'display:inline-block;padding:.15rem .4rem;border-radius:.4rem;font-size:.75rem;line-height:1;border:1px solid;';
          $badgePublished = 'color:#064e3b;background:#ecfdf5;border-color:#10b981;';
          $badgeDraft     = 'color:#7c2d12;background:#fff7ed;border-color:#f97316;';
        ?>
        <tr>
          <td><?= $id ?></td>
          <td>
            <?= h($title) ?>
            <?php if ($pub): ?>
              <span style="<?= $badgeStyle . $badgePublished ?>">Published</span>
            <?php else: ?>
              <span style="<?= $badgeStyle . $badgeDraft ?>">Draft</span>
            <?php endif; ?>
          </td>
          <td class="muted"><?= h($slug) ?></td>
          <td><?= $pub ? 'Published' : 'Draft' ?></td>
          <td class="actions" style="display:flex;gap:.4rem;flex-wrap:wrap">
            <a class="btn btn-sm" href="<?= h(url_for("/staff/subjects/{$subject_slug}/pages/show.php?id={$id}")) ?>">Show</a>

            <?php if ($canEdit): ?>
              <a class="btn btn-sm" href="<?= h(url_for("/staff/subjects/{$subject_slug}/pages/edit.php?id={$id}")) ?>">Edit</a>
            <?php else: ?>
              <a class="btn btn-sm is-disabled" role="button" aria-disabled="true" tabindex="-1" title="You can’t edit pages" style="pointer-events:none;opacity:.55;">Edit</a>
            <?php endif; ?>

            <?php if ($canPublish): ?>
              <form method="post" action="<?= h(url_for("/staff/subjects/{$subject_slug}/pages/toggle_publish.php")) ?>"
                    style="display:inline" onsubmit="return confirmTogglePublish(this);">
                <?= function_exists('csrf_field') ? csrf_field() : '' ?>
                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="hidden" name="back" value="<?= h($_SERVER['REQUEST_URI'] ?? url_for("/staff/subjects/{$subject_slug}/pages/")) ?>">
                <?php if ($pub): ?>
                  <input type="hidden" name="action" value="unpublish">
                  <button class="btn btn-sm" type="submit" title="Unpublish">Unpublish</button>
                <?php else: ?>
                  <input type="hidden" name="action" value="publish">
                  <button class="btn btn-sm btn-success" type="submit" title="Publish">Publish</button>
                <?php endif; ?>
              </form>
            <?php else: ?>
              <?php if ($pub): ?>
                <a class="btn btn-sm is-disabled" role="button" aria-disabled="true" tabindex="-1" title="You can’t change publish state" style="pointer-events:none;opacity:.55;">Unpublish</a>
              <?php else: ?>
                <a class="btn btn-sm is-disabled" role="button" aria-disabled="true" tabindex="-1" title="You can’t change publish state" style="pointer-events:none;opacity:.55;">Publish</a>
              <?php endif; ?>
            <?php endif; ?>

            <?php if ($canDelete): ?>
              <a class="btn btn-sm btn-danger"
                 href="<?= h(url_for("/staff/subjects/{$subject_slug}/pages/delete.php?id={$id}")) ?>"
                 onclick="return confirm('Delete this page?');">Delete</a>
            <?php else: ?>
              <a class="btn btn-sm btn-danger is-disabled" role="button" aria-disabled="true" tabindex="-1" title="You can’t delete pages" style="pointer-events:none;opacity:.55;">Delete</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <p style="margin-top:1rem;">
    <a class="btn" href="<?= h(url_for("/staff/subjects/{$subject_slug}/")) ?>">&larr; Back to <?= h($subject_name) ?> Hub</a>
  </p>
</main>

<script>
function confirmTogglePublish(formEl){
  try {
    const action = (formEl.querySelector('input[name="action"]') || {}).value;
    if (action === 'unpublish') return confirm('Unpublish this page?');
    if (action === 'publish')   return confirm('Publish this page?');
  } catch(e){}
  return true;
}
</script>

<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
