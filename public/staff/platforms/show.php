<?php
// project-root/public/staff/platforms/show.php
declare(strict_types=1);

$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (!is_file($init)) {
  http_response_code(500);
  exit('Init not found at: ' . $init);
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
if (!function_exists('u')) {
  function u(string $s): string {
    return urlencode($s);
  }
}

// Debug toggle
if (defined('APP_DEBUG') && APP_DEBUG) {
  ini_set('display_errors', '1');
  error_reporting(E_ALL);
}

$id_raw = $_GET['id'] ?? '';
$id     = is_numeric($id_raw) ? (int)$id_raw : 0;
if ($id <= 0) {
  $back = url_for('/staff/platforms/');
  $msg  = 'Invalid platform id.';
} else {
  $plt = function_exists('find_platform_by_id') ? find_platform_by_id($id) : null;
  if (!$plt) {
    $back = url_for('/staff/platforms/');
    $msg  = 'Platform not found.';
  }
}

$page_title  = 'Platform Detail';
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

/* ====== NAV BLOCK ====== */
$breadcrumbs = [
  ['label' => 'Staff',     'url' => '/staff/'],
  ['label' => 'Platforms', 'url' => '/staff/platforms/'],
  ['label' => 'Show'],
];

$nav_partial = PRIVATE_PATH . '/common/ui/staff_platforms_nav.php';
if (is_file($nav_partial)) {
  require $nav_partial;
}
/* ======================= */

if (!empty($msg ?? '')): ?>
  <main class="container" style="max-width:760px;padding:1rem 0;">
    <p><?= h($msg) ?></p>
    <p><a href="<?= h($back) ?>">Back</a></p>
  </main>
<?php
  if (is_file($footer)) {
    require $footer;
  }
  exit;
endif;
?>
<main class="container" style="max-width:760px;padding:1rem 0;">
  <h1>Platform: <?= h($plt['name']) ?></h1>

  <p>
    <a href="<?= h(url_for('/staff/platforms/edit.php?id=' . u((string)$id))) ?>">Edit</a> |
    <a href="<?= h(url_for('/staff/platforms/delete.php?id=' . u((string)$id))) ?>"
       onclick="return confirm('Delete this platform and its items?');">
      Delete
    </a>
  </p>
  <p>
    <a href="<?= h(url_for('/staff/platforms/')) ?>">Back to platforms list</a>
  </p>

  <dl>
    <dt>ID</dt>
    <dd><?= (int)$plt['id'] ?></dd>

    <dt>Name</dt>
    <dd><?= h($plt['name']) ?></dd>

    <dt>Slug</dt>
    <dd><code><?= h($plt['slug']) ?></code></dd>

    <dt>Visible</dt>
    <dd><?= (int)$plt['visible'] === 1 ? 'Yes' : 'No' ?></dd>

    <dt>Position</dt>
    <dd><?= (int)$plt['position'] ?></dd>

    <dt>Description (HTML)</dt>
    <dd>
      <?php
      // Display raw HTML description; you may want to trust this only for staff.
      echo $plt['description_html'] ?? '<span class="muted">(none)</span>';
      ?>
    </dd>
  </dl>
</main>
<?php
if (is_file($footer)) {
  require $footer;
}
