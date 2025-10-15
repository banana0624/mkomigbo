<?php
declare(strict_types=1);

/**
 * project-root/private/common/show.php
 * Centralized "show/details" for subjects/pages.
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

$page_title = $m['entity'] . ' Details';
require PRIVATE_PATH . '/shared/staff_header.php';
?>
<div class="toolbar">
  <div class="left">
    <a class="btn" href="<?= h(url_for($m['list_url'])) ?>">Back</a>
    <a class="btn btn-primary" href="<?= h(url_for(($m['edit_url'])($id))) ?>">Edit</a>
  </div>
  <div class="right">
    <a class="btn btn-danger" href="<?= h(url_for(($m['delete_url'])($id))) ?>">Delete</a>
  </div>
</div>

<div class="card">
  <div class="title"><?= h($m['entity']) ?> #<?= (int)$id ?></div>
  <div class="muted">Table: <?= h($m['table']) ?></div>

  <div class="hr"></div>

  <?php if ($m['type'] === 'subject'): ?>
    <p><strong>Name:</strong> <?= h($row['name'] ?? '') ?></p>
    <p><strong>Slug:</strong> <?= h($row['slug'] ?? '') ?></p>
    <p><strong>Meta description:</strong><br><?= nl2br(h($row['meta_description'] ?? '')) ?></p>
    <p><strong>Meta keywords:</strong><br><?= h($row['meta_keywords'] ?? '') ?></p>

  <?php else: /* page */ ?>
    <p><strong>Subject ID:</strong> <?= h((string)($row['subject_id'] ?? '')) ?></p>
    <p><strong>Title:</strong> <?= h($row['title'] ?? '') ?></p>
    <p><strong>Slug:</strong> <?= h($row['slug'] ?? '') ?></p>
    <p><strong>Content:</strong><br><?= nl2br(h($row['content'] ?? '')) ?></p>
    <p><strong>Meta description:</strong><br><?= nl2br(h($row['meta_description'] ?? '')) ?></p>
    <p><strong>Meta keywords:</strong><br><?= h($row['meta_keywords'] ?? '') ?></p>
  <?php endif; ?>
</div>

<?php require PRIVATE_PATH . '/shared/staff_footer.php'; ?>
