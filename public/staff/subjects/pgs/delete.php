<?php
// project-root/public/staff/subjects/pgs/delete.php
declare(strict_types=1);

$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) {
  echo "<h1>FATAL: initialize.php missing</h1>";
  echo "<p>Expected at: {$init}</p>";
  exit;
}
require_once $init;

global $db;

if (function_exists('require_staff')) {
  require_staff();
} elseif (function_exists('require_login')) {
  require_login();
}

if (!function_exists('h')) {
  function h(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
  }
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  http_response_code(400);
  echo "Missing page ID.";
  exit;
}

// Load page + subject for display
$page = null;
$subject = null;
try {
  $sql = "SELECT p.id, p.title, p.slug, p.subject_id, s.name AS subject_name, s.slug AS subject_slug
          FROM pages p
          JOIN subjects s ON s.id = p.subject_id
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

$subject_id   = (int)$page['subject_id'];
$subject_name = (string)($page['subject_name'] ?? '');
$subject_slug = (string)($page['subject_slug'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Perform delete
  try {
    $sql = "DELETE FROM pages WHERE id = :id LIMIT 1";
    $st = $db->prepare($sql);
    $st->execute([':id' => $id]);
  } catch (Throwable $e) {
    // ignore; if it fails, page may remain
  }

  $redir = url_for('/staff/subjects/pgs/') . '?subject_id=' . $subject_id;
  header('Location: ' . $redir);
  exit;
}

$page_title = 'Delete Page (Subject Pages)';
$body_class = 'role--staff role--subjects-pages';

$stylesheets = $stylesheets ?? [];
if (!in_array('/lib/css/ui.css', $stylesheets, true)) {
  $stylesheets[] = '/lib/css/ui.css';
}

// Header
if (defined('SHARED_PATH') && is_file(SHARED_PATH . '/header.php')) {
  include SHARED_PATH . '/header.php';
} elseif (defined('PRIVATE_PATH') && is_file(PRIVATE_PATH . '/shared/staff_header.php')) {
  include PRIVATE_PATH . '/shared/staff_header.php';
}
?>
<main class="container" style="max-width:700px;padding:1.75rem 0;">
  <div class="page-header-block">
    <h1>Delete Page</h1>
    <p class="page-intro">
      This will permanently delete the page.
    </p>
  </div>

  <p>
    <a href="<?= h(url_for('/staff/subjects/pgs/')); ?>" class="btn">
      &larr; Back to Subject Pages
    </a>
  </p>

  <div class="alert alert--warning">
    <p>
      Are you sure you want to delete this page?
    </p>
    <ul>
      <li><strong>Subject:</strong> <?= h($subject_name); ?> (<?= h($subject_slug); ?>)</li>
      <li><strong>Title:</strong> <?= h($page['title'] ?? ''); ?></li>
      <li><strong>Slug:</strong> <code><?= h($page['slug'] ?? ''); ?></code></li>
    </ul>
  </div>

  <form method="post">
    <button type="submit" class="btn btn--danger">
      Yes, delete this page
    </button>
  </form>
</main>

<?php
if (defined('SHARED_PATH') && is_file(SHARED_PATH . '/footer.php')) {
  include SHARED_PATH . '/footer.php';
} elseif (defined('PRIVATE_PATH') && is_file(PRIVATE_PATH . '/shared/footer.php')) {
  include PRIVATE_PATH . '/shared/footer.php';
}
