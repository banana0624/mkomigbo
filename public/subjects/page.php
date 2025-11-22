<?php
// project-root/public/subjects/page.php
declare(strict_types=1);

$init = dirname(__DIR__, 2) . '/private/assets/initialize.php';
// __DIR__ = project-root/public/subjects
// dirname(__DIR__, 2) = project-root
if (!is_file($init)) {
  die('Init not found at: ' . $init);
}
require_once $init;

global $db;

// Simple h() helper if not already defined
if (!function_exists('h')) {
  function h(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
  }
}

// --- 1) Read query params ---
$subject_slug = isset($_GET['subject']) ? trim((string)$_GET['subject']) : '';
$page_slug    = isset($_GET['page'])    ? trim((string)$_GET['page'])    : '';

if ($subject_slug === '' || $page_slug === '') {
  http_response_code(400);
  echo 'Missing subject or page parameter.';
  exit;
}

// --- 2) Load subject ---
$subject = null;
try {
  $sql = "SELECT *
            FROM subjects
           WHERE slug = :slug
           LIMIT 1";
  $st = $db->prepare($sql);
  $st->execute([':slug' => $subject_slug]);
  $subject = $st->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (Throwable $e) {
  http_response_code(500);
  echo "DB error loading subject: " . h($e->getMessage());
  exit;
}

if (!$subject) {
  http_response_code(404);
  echo "Subject not found: " . h($subject_slug);
  exit;
}

// --- 3) Detect body/content column ---
$body_column = null;
if (function_exists('pf__column_exists')) {
  foreach (['body_html', 'body', 'content_html', 'content'] as $col) {
    if (pf__column_exists('pages', $col)) {
      $body_column = $col;
      break;
    }
  }
}

// --- 4) Load page for this subject + slug ---
$page = null;
try {
  $selectCols = "p.id, p.subject_id, p.title, p.slug, p.visible, p.nav_order";
  if ($body_column !== null) {
    $selectCols .= ", p.{$body_column}";
  }

  $sql = "SELECT {$selectCols}
            FROM pages p
           WHERE p.slug = :page_slug
             AND p.subject_id = :sid
           LIMIT 1";
  $st = $db->prepare($sql);
  $st->execute([
    ':page_slug' => $page_slug,
    ':sid'       => (int)$subject['id'],
  ]);
  $page = $st->fetch(PDO::FETCH_ASSOC) ?: null;
} catch (Throwable $e) {
  http_response_code(500);
  echo "DB error loading page: " . h($e->getMessage());
  exit;
}

if (!$page) {
  http_response_code(404);
  echo "Page not found for subject: " . h($subject_slug) . " / " . h($page_slug);
  exit;
}

// Enforce visible flag if you want (optional)
if (isset($page['visible']) && (int)$page['visible'] !== 1) {
  // For now we simply show it; if you want hard hide:
//  http_response_code(404);
//  echo "Page is not visible.";
//  exit;
}

// --- 5) Get body content ---
$body_html = '';
if ($body_column !== null && array_key_exists($body_column, $page)) {
  // We assume this column may contain HTML written by staff
  $body_html = (string)$page[$body_column];
} else {
  // Fallback simple paragraph
  $body_html = '<p>No content has been added yet for this page.</p>';
}

// --- 6) Subject logo URL, if helper exists ---
$subject_logo_url = null;
if (function_exists('subject_logo_url')) {
  $subject_logo_url = subject_logo_url($subject);
}

// --- 7) Page meta / CSS flags ---
$page_title = ($page['title'] ?? '') !== ''
  ? $page['title']
  : ($subject['name'] ?? 'Page');

$body_class = 'public-subjects subject subject-' . h($subject_slug) . ' page-detail';

$stylesheets = $stylesheets ?? [];
if (!in_array('/lib/css/subjects.css', $stylesheets, true)) {
  $stylesheets[] = '/lib/css/subjects.css';
}

// --- 8) Header ---
if (defined('PRIVATE_PATH') && is_file(PRIVATE_PATH . '/shared/header.php')) {
  include PRIVATE_PATH . '/shared/header.php';
} elseif (defined('SHARED_PATH') && is_file(SHARED_PATH . '/header.php')) {
  include SHARED_PATH . '/header.php';
}
?>

<main class="subject-page subject page-detail subject-<?= h($subject_slug); ?>">
  <!-- Breadcrumb -->
  <nav class="page-detail__breadcrumb" aria-label="Breadcrumb">
    <a href="<?= h(url_for('/subjects/')); ?>">Subjects</a>
    <span>&rsaquo;</span>
    <a href="<?= h(url_for('/subjects/' . $subject['slug'] . '/')); ?>">
      <?= h($subject['name']); ?>
    </a>
    <span>&rsaquo;</span>
    <span><?= h($page['title'] ?? $page['slug']); ?></span>
  </nav>

  <!-- Hero -->
  <header class="subject-hero subject-hero--<?= h($subject_slug); ?>">
    <?php if ($subject_logo_url): ?>
      <img
        src="<?= h($subject_logo_url); ?>"
        alt="<?= h(($subject['name'] ?? 'Subject') . ' logo'); ?>"
        class="subject-hero__logo"
      >
    <?php endif; ?>

    <div class="subject-hero__text">
      <h1><?= h($page['title'] ?? $page['slug']); ?></h1>
      <p class="subject-hero__slug">
        <?= h($subject['name'] ?? 'Subject'); ?> &middot;
        <code><?= h($subject['slug']); ?></code>
      </p>
    </div>
  </header>

  <!-- Meta row -->
  <div class="page-detail__meta">
    <span class="pill">
      Subject: <?= h($subject['name'] ?? ''); ?>
    </span>
    <span class="pill pill--muted">
      Slug: <code><?= h($page['slug'] ?? ''); ?></code>
    </span>
    <?php if (isset($page['nav_order']) && $page['nav_order'] !== null): ?>
      <span class="pill pill--muted">
        Position: <?= h((string)$page['nav_order']); ?>
      </span>
    <?php endif; ?>
  </div>

  <!-- Article body -->
  <article class="page-detail__content">
    <?= $body_html; ?>
  </article>

  <!-- Back link -->
  <div class="back">
    <a href="<?= h(url_for('/subjects/' . $subject['slug'] . '/')); ?>">
      &larr; Back to <?= h($subject['name'] ?? 'subject'); ?> overview
    </a>
  </div>
</main>

<?php
// Footer
if (defined('PRIVATE_PATH') && is_file(PRIVATE_PATH . '/shared/footer.php')) {
  include PRIVATE_PATH . '/shared/footer.php';
} elseif (defined('SHARED_PATH') && is_file(SHARED_PATH . '/footer.php')) {
  include SHARED_PATH . '/footer.php';
}
