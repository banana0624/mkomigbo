<?php
// project-root/public/staff/platforms/items/delete.php
declare(strict_types=1);

$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { http_response_code(500); exit('Init not found'); }
require_once $init;

$auth_ok = false;
if (is_file(PRIVATE_PATH . '/functions/auth.php')) {
  require_once PRIVATE_PATH . '/functions/auth.php';
  if (function_exists('require_staff')) { require_staff(); $auth_ok = true; }
}
if (!$auth_ok) {
  if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
  if (empty($_SESSION['admin']['id'])) { header('Location: ' . url_for('/staff/login.php')); exit; }
}

require_once PRIVATE_PATH . '/functions/platform_functions.php';

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

$item_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($item_id<=0) { header('Location: ' . url_for('/staff/platforms/')); exit; }

if (function_exists('find_item_by_id')) {
  $item = find_item_by_id($item_id);
} else {
  $item = null;
}
if (!$item) { header('Location: ' . url_for('/staff/platforms/')); exit; }

$platform_id = (int)($item['platform_id'] ?? (int)($_GET['platform_id'] ?? 0));
$platform = $platform_id ? find_platform_by_id($platform_id) : null;

$page_title='Delete Item'; $active_nav='staff'; $body_class='role--staff';
$stylesheets = $stylesheets ?? []; $stylesheets[]='/lib/css/ui.css';

$staffHeader = PRIVATE_PATH . '/shared/staff_header.php';
$baseHeader  = PRIVATE_PATH . '/shared/header.php';
$footer      = PRIVATE_PATH . '/shared/footer.php';
if (is_file($staffHeader)) require $staffHeader; else require $baseHeader;

/* ====== NAV BLOCK ====== */
$breadcrumbs = [
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Platforms','url'=>'/staff/platforms/'],
  ['label'=>'Delete Item'],
];
$back_href = '/staff/platforms/?pid='. (int)$platform_id;
$back_text = 'Back to Items';
require PRIVATE_PATH . '/common/ui/staff_platforms_nav.php';
/* ======================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (function_exists('csrf_check')) csrf_check();
  $ok = delete_item($item_id);
  header('Location: ' . url_for('/staff/platforms/?pid='.$platform_id)); exit;
}
?>
<main class="container" style="max-width:760px;padding:1rem 0;">
  <h1>Delete Item<?= $platform ? ' — '.h($platform['name']) : '' ?></h1>
  <p>Are you sure you want to delete item “<?= h((string)($item['menu_name'] ?? '')) ?>”?</p>
  <form method="post" onsubmit="return confirm('This cannot be undone. Continue?');">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <button class="btn btn-danger">Yes, delete</button>
    <a class="btn" href="<?= h(url_for('/staff/platforms/?pid='.$platform_id)) ?>">Cancel</a>
  </form>
</main>
<?php if (is_file($footer)) require $footer;
