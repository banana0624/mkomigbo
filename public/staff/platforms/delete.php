<?php
// project-root/public/staff/platforms/delete.php
declare(strict_types=1);

$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (!is_file($init)) {
  http_response_code(500);
  exit('Init not found');
}
require_once $init;

// Auth
$auth_ok = false;
if (is_file(PRIVATE_PATH . '/functions/auth.php')) {
  require_once PRIVATE_PATH . '/functions/auth.php';
  if (function_exists('require_staff')) {
    require_staff();
    $auth_ok = true;
  }
}
if (!$auth_ok) {
  if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
  }
  if (empty($_SESSION['admin']['id'])) {
    header('Location: ' . url_for('/staff/login.php'));
    exit;
  }
}

require_once PRIVATE_PATH . '/functions/platform_functions.php';

if (!function_exists('h')) {
  function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  header('Location: ' . url_for('/staff/platforms/'));
  exit;
}

$platform = find_platform_by_id($id);
if (!$platform) {
  header('Location: ' . url_for('/staff/platforms/'));
  exit;
}

$page_title  = 'Delete Platform';
$active_nav  = 'staff';
$body_class  = 'role--staff';
$stylesheets = $stylesheets ?? [];
$stylesheets[] = '/lib/css/ui.css';

$staffHeader = PRIVATE_PATH . '/shared/staff_header.php';
$baseHeader  = PRIVATE_PATH . '/shared/header.php';
$footer      = PRIVATE_PATH . '/shared/footer.php';

if (is_file($staffHeader)) {
  require $staffHeader;
} else {
  require $baseHeader;
}

/* ====== NAV BLOCK (breadcrumbs + back) ====== */
$breadcrumbs = [
  ['label' => 'Staff',     'url' => '/staff/'],
  ['label' => 'Platforms', 'url' => '/staff/platforms/'],
  ['label' => 'Delete'],
];
$back_href = '/staff/platforms/';
$back_text = 'Back to Platforms';

$nav_partial = PRIVATE_PATH . '/common/ui/staff_platforms_nav.php';
if (is_file($nav_partial)) {
  require $nav_partial;
}
/* ============================================ */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (function_exists('csrf_check')) {
    csrf_check();
  }
  $ok = delete_platform($id);
  // You could inspect $ok and show a flash, but for now just redirect:
  header('Location: ' . url_for('/staff/platforms/'));
  exit;
}
?>
<main class="container" style="max-width:760px;padding:1rem 0;">
  <h1>Delete Platform</h1>
  <p>Are you sure you want to delete “<?= h($platform['name']) ?>” and all its items?</p>

  <form method="post" onsubmit="return confirm('This cannot be undone. Continue?');">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <button class="btn btn-danger">Yes, delete</button>
    <a class="btn" href="<?= h(url_for('/staff/platforms/')) ?>">Cancel</a>
  </form>
</main>
<?php
if (is_file($footer)) {
  require $footer;
}
