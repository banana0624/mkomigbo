<?php
// project-root/public/staff/subjects/pgs/show.php
declare(strict_types=1);

$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) {
  echo "<h1>FATAL: initialize.php missing</h1>";
  echo "<p>Expected at: {$init}</p>";
  exit;
}
require_once $init;

if (function_exists('require_staff')) {
  require_staff();
} elseif (function_exists('require_login')) {
  require_login();
}

global $db;

if (!function_exists('h')) {
  function h(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
  }
}

// Detect content column
$body_column = null;
if (function_exists('pf__column_exists')) {
  foreach (['body_html', 'body', 'content_html', 'content'] as $col) {
    if (pf__column_exists('pages', $col)) {
      $body_column = $col;
      break;
    }
  }
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  http_response_code(400);
  echo "Missing or invalid page id.";
  exit;
}

$page = null;
try {
  $cols = "p.id, p.subject_id, p.title, p.slug, p.visible, p.nav_order,
           s.name AS subject_name, s.slug AS subject_slug";
  if ($body_column !== null) {
    $cols .= ", p.{$body_column}";
  }
  $sql = "SELECT {$cols}
            FROM pages p
            LEFT JOIN subjects s ON p.subject_id = s.id
           WHERE p.id = :id
           LIMIT 1";
  $st = $db->prepare($sql);
  $st->execute([':id' => $id]);
  $page = $st->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (Throwable $e) {
  $page = null;
}

if (!$page) {
  http_response_code(404);
  echo "Page not found.";
  exit;
}

$page_title = 'View Subject Page (Staff)';
$body_class = 'role--staff role--subject-pages';
$stylesheets = $stylesheets ?? [];
if (!in_array('/lib/css/ui.css', $stylesheets, true)) {
  $stylesheets[] = '/lib/css/ui.css';
}

// Header
if (defined('SHARED_PATH') && is_file(SHARED_PATH . '/header.php')) {
  include SHARED_PATH . '/header.php';
} elseif (defined('PRIVATE_PATH') && is_file(PRIVATE_PATH . '/shared/staff_header.php')) {
  include PRIVATE_PATH . '/shared/staff_header.php';
} else {
  ?><!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <title><?= h($page_title); ?></title>
    <link rel="stylesheet" href="<?= h(url_for('/lib/css/ui.css')); ?>">
  </head>
  <body>
  <?php
}

if (defined('SHARED_PATH') && is_file(SHARED_PATH . '/nav.php')) {
  include SHARED_PATH . '/nav.php';
}
?>
<main class="container" style="max-width:720px;padding:1.75rem 0;">
  <div class="page-header-block">
    <h1><?= h($page['title'] ?? 'Page'); ?></h1>
    <p class="page-intro">
      Subject:
      <strong><?= h($page['subject_name'] ?? ('#' . ($page['subject_id'] ?? ''))); ?></strong>
      <?php if (!empty($page['subject_slug'])): ?>
        <span class="muted">
          (slug: <code><?= h($page['subject_slug']); ?></code>)
        </span>
      <?php endif; ?>
    </p>
  </div>

  <div style="margin-bottom:1rem;display:flex;gap:.5rem;flex-wrap:wrap;">
    <a href="<?= h(url_for('/staff/subjects/pgs/index.php')); ?>" class="btn">
      &larr; Back to Subject Pages
    </a>
    <a href="<?= h(url_for('/staff/subjects/pgs/edit.php?id=' . (int)$page['id'])); ?>" class="btn btn--primary">
      Edit
    </a>
    <a href="<?= h(url_for('/staff/subjects/pgs/delete.php?id=' . (int)$page['id'])); ?>" class="btn btn--danger">
      Delete
    </a>
    <?php if (!empty($page['subject_slug']) && !empty($page['slug'])): ?>
      <?php $publicUrl = url_for('/subjects/page.php')
          . '?subject=' . rawurlencode((string)$page['subject_slug'])
          . '&page='    . rawurlencode((string)$page['slug']); ?>
      <a href="<?= h($publicUrl); ?>" class="btn" target="_blank">
        View on public site
      </a>
    <?php endif; ?>
  </div>

  <dl class="data-list">
    <dt>Slug</dt>
    <dd><code><?= h((string)$page['slug']); ?></code></dd>

    <dt>Visible</dt>
    <dd><?= ((int)$page['visible'] === 1) ? 'Yes' : 'No'; ?></dd>

    <dt>Nav order</dt>
    <dd><?= h((string)($page['nav_order'] ?? '')); ?></dd>
  </dl>

  <?php if ($body_column !== null): ?>
    <section style="margin-top:1.5rem;">
      <h2>Page content</h2>
      <div class="box">
        <pre style="white-space:pre-wrap;word-wrap:break-word;"><?= h((string)($page[$body_column] ?? '')); ?></pre>
      </div>
    </section>
  <?php endif; ?>
</main>
<?php
if (defined('SHARED_PATH') && is_file(SHARED_PATH . '/footer.php')) {
  include SHARED_PATH . '/footer.php';
} elseif (defined('PRIVATE_PATH') && is_file(PRIVATE_PATH . '/shared/footer.php')) {
  include PRIVATE_PATH . '/shared/footer.php';
} else {
  ?></body></html><?php
}