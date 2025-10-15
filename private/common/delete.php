<?php
declare(strict_types=1);

/**
 * project-root/private/common/delete.php
 * Centralized "delete" (with confirm) for subjects/pages.
 * Accepts $type and $id (preferred) or ?type=...&id=...
 */

require_once dirname(__DIR__) . '/assets/initialize.php';
$stylesheets[] = '/lib/css/ui.css';

if (function_exists('require_admin_login')) { require_admin_login(); }

$helperFile = __DIR__ . '/_common_model_helpers.inc.php';
if (is_file($helperFile)) require_once $helperFile;

$type = $type ?? ($_GET['type'] ?? '');
$id   = isset($id) ? (int)$id : (int)($_GET['id'] ?? 0);

$m = __mk_model($type);
if (!$m || $id <= 0) { if (function_exists('render_404')) render_404('Invalid request'); http_response_code(400); echo 'Invalid request.'; exit; }

$row = __mk_find($m['type'], $id);
if (!$row) { if (function_exists('render_404')) render_404($m['entity'].' not found'); http_response_code(404); echo 'Not found.'; exit; }

$page_title = 'Delete ' . $m['entity'];
require PRIVATE_PATH . '/shared/staff_header.php';

if (is_post_request()) {
    if (function_exists('csrf_verify') && !csrf_verify($_POST['csrf_token'] ?? '')) {
        echo '<div class="alert danger">Invalid CSRF token.</div>';
    } else {
        if (__mk_delete($m['type'], $id)) {
            if (function_exists('flash_set')) flash_set($m['entity'].' deleted', 'success');
            redirect_to($m['list_url']);
        } else {
            echo '<div class="alert danger">Failed to delete.</div>';
        }
    }
    // fallthrough displays nothing; redirect above
}
?>
<div class="card">
  <div class="title">Confirm deletion</div>
  <p>Are you sure you want to delete this <?= h(strtolower($m['entity'])) ?>?</p>

  <dl class="muted" style="margin: .5rem 0 1rem">
    <?php if ($m['type']==='subject'): ?>
      <dt>Name</dt><dd><?= h($row['name'] ?? '') ?></dd>
      <dt>Slug</dt><dd><?= h($row['slug'] ?? '') ?></dd>
    <?php else: ?>
      <dt>Title</dt><dd><?= h($row['title'] ?? '') ?></dd>
      <dt>Slug</dt><dd><?= h($row['slug'] ?? '') ?></dd>
    <?php endif; ?>
  </dl>

  <form method="post">
    <?= function_exists('csrf_tag') ? csrf_tag() : '' ?>
    <button class="btn btn-danger" type="submit">Yes, delete</button>
    <a class="btn" href="<?= h(url_for(($m['edit_url'])($id))) ?>">Cancel</a>
  </form>
</div>

<?php require PRIVATE_PATH . '/shared/staff_footer.php'; ?>
