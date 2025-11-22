<?php
// project-root/public/staff/pages/delete.php
declare(strict_types=1);

// ---------------------------------------------------------------------------
// Bootstrap
// ---------------------------------------------------------------------------
$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (!is_file($init)) {
  http_response_code(500);
  exit('Init not found: ' . htmlspecialchars($init, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
}
require_once $init;

/** @var PDO $db */
global $db;

// ---------------------------------------------------------------------------
// Auth / Permissions
// ---------------------------------------------------------------------------
if (is_file(PRIVATE_PATH . '/middleware/guard.php')) {
  if (!defined('REQUIRE_LOGIN')) {
    define('REQUIRE_LOGIN', true);
  }
  if (!defined('REQUIRE_PERMS')) {
    define('REQUIRE_PERMS', [
      'pages.delete',
      'pages.write',
    ]);
  }
  require PRIVATE_PATH . '/middleware/guard.php';
} else {
  if (function_exists('require_staff')) {
    require_staff();
  } elseif (function_exists('require_login')) {
    require_login();
  }
}

// ---------------------------------------------------------------------------
// Load page
// ---------------------------------------------------------------------------
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
  http_response_code(400);
  exit('Missing page id.');
}

$st = $db->prepare("
  SELECT id, subject_id, title
    FROM pages
   WHERE id = :id
   LIMIT 1
");
$st->execute([':id' => $id]);
$page = $st->fetch(PDO::FETCH_ASSOC);

if (!$page) {
  http_response_code(404);
  exit('Page not found.');
}

// Derived upload folder
$uploadRel = '/lib/uploads/pages/' . (int)$page['id'];
$uploadAbs = rtrim(PUBLIC_PATH, DIRECTORY_SEPARATOR) . str_replace('/', DIRECTORY_SEPARATOR, $uploadRel);

// ---------------------------------------------------------------------------
// Handle POST
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (function_exists('csrf_check')) {
    csrf_check();
  }

  // Delete DB row (page_files are ON DELETE CASCADE)
  $del = $db->prepare("DELETE FROM pages WHERE id = :id LIMIT 1");
  $ok  = $del->execute([':id' => $id]);

  // Remove upload folder if exists
  if (is_dir($uploadAbs)) {
    $it = new RecursiveDirectoryIterator($uploadAbs, FilesystemIterator::SKIP_DOTS);
    $ri = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($ri as $f) {
      $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname());
    }
    @rmdir($uploadAbs);
  }

  if (function_exists('flash')) {
    flash($ok ? 'success' : 'error', $ok ? 'Page deleted.' : 'Delete failed.');
  }

  // Back to subject pages list
  $subjectId = (int)($page['subject_id'] ?? 0);
  $target    = $subjectId > 0
    ? url_for('/staff/pages/?subject_id=' . $subjectId)
    : url_for('/staff/pages/');
  header('Location: ' . $target);
  exit;
}

// ---------------------------------------------------------------------------
// Page chrome
// ---------------------------------------------------------------------------
$page_title  = 'Delete Page — ' . (string)($page['title'] ?? ('#' . $id));
$active_nav  = 'staff';
$body_class  = 'role--staff role--pages pages-delete';
$page_logo   = '/lib/images/icons/pages.svg';

$stylesheets = $stylesheets ?? [];
if (!in_array('/lib/css/ui.css', $stylesheets, true)) {
  $stylesheets[] = '/lib/css/ui.css';
}

$breadcrumbs = [
  ['label' => 'Home',   'url' => '/'],
  ['label' => 'Staff',  'url' => '/staff/'],
  ['label' => 'Pages',  'url' => '/staff/pages/'],
  ['label' => 'Delete'],
];

if (defined('SHARED_PATH') && is_file(SHARED_PATH . '/staff_header.php')) {
  require SHARED_PATH . '/staff_header.php';
} elseif (defined('PRIVATE_PATH') && is_file(PRIVATE_PATH . '/shared/header.php')) {
  require PRIVATE_PATH . '/shared/header.php';
}
?>
<main class="mk-main mk-main--staff">
  <section class="mk-section">
    <div class="mk-form">
      <p class="mk-form__crumb" style="margin:0 0 .75rem;">
        <a href="<?= h(url_for('/staff/pages/?subject_id=' . (int)($page['subject_id'] ?? 0))) ?>">
          &larr; Back to Pages
        </a>
      </p>

      <h1><?= h($page_title) ?></h1>
      <p class="mk-form__desc">
        This will permanently remove this page and its attachments.
      </p>

      <p>
        Are you sure you want to delete the page
        <strong><?= h((string)($page['title'] ?? ('#' . (int)$page['id']))) ?></strong>?
      </p>

      <form method="post" action="">
        <?= function_exists('csrf_field') ? csrf_field() : '' ?>
        <input type="hidden" name="id" value="<?= (int)$page['id'] ?>">

        <div class="mk-form__actions">
          <button class="mk-btn-primary mk-btn--danger" type="submit">
            Yes, delete
          </button>
          <a class="mk-btn-secondary"
             href="<?= h(url_for('/staff/pages/show.php?id=' . (int)$page['id'])) ?>">
            Cancel
          </a>
        </div>
      </form>
    </div>
  </section>
</main>
<?php
if (defined('SHARED_PATH') && is_file(SHARED_PATH . '/footer.php')) {
  require SHARED_PATH . '/footer.php';
} elseif (defined('PRIVATE_PATH') && is_file(PRIVATE_PATH . '/shared/footer.php')) {
  require PRIVATE_PATH . '/shared/footer.php';
}
