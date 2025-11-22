<?php
// project-root/public/staff/platforms/items/edit.php
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
function post(string $k, ?string $d=null): ?string { return isset($_POST[$k]) ? (string)$_POST[$k] : $d; }
function as_int($v): int { return (int)$v; }

$item_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($item_id<=0) { header('Location: ' . url_for('/staff/platforms/')); exit; }

// Prefer a direct function if available
if (function_exists('find_item_by_id')) {
  $item = find_item_by_id($item_id);
} else {
  // Fallback: safest is to bail to list page (keeps this file query-agnostic)
  $item = null;
}
if (!$item) { header('Location: ' . url_for('/staff/platforms/')); exit; }

$platform_id = (int)($item['platform_id'] ?? (int)($_GET['platform_id'] ?? 0));
$platform = $platform_id ? find_platform_by_id($platform_id) : null;

$page_title = 'Edit Item';
$active_nav = 'staff';
$body_class = 'role--staff';
$stylesheets = $stylesheets ?? []; $stylesheets[] = '/lib/css/ui.css';

$staffHeader = PRIVATE_PATH . '/shared/staff_header.php';
$baseHeader  = PRIVATE_PATH . '/shared/header.php';
$footer      = PRIVATE_PATH . '/shared/footer.php';
if (is_file($staffHeader)) require $staffHeader; else require $baseHeader;

/* ====== NAV BLOCK ====== */
$breadcrumbs = [
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Platforms','url'=>'/staff/platforms/'],
  ['label'=>'Edit Item'],
];
$back_href = '/staff/platforms/?pid='. (int)$platform_id;
$back_text = 'Back to Items';
require PRIVATE_PATH . '/common/ui/staff_platforms_nav.php';
/* ======================= */

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (function_exists('csrf_check')) csrf_check();
  $res = update_item($item_id, [
    'menu_name' => trim((string)post('menu_name','')),
    'slug'      => (string)post('slug',''),
    'body_html' => (string)post('body_html',''),
    'visible'   => (post('visible','') === '' ? null : (int)post('visible','1')),
    'position'  => (post('position','') === '' ? null : (int)post('position','1')),
  ]);
  if (!empty($res['ok'])) {
    header('Location: ' . url_for('/staff/platforms/?pid='.$platform_id)); exit;
  }
  $errors = $res['errors'] ?? ['_form'=>'Update failed'];
  if (function_exists('find_item_by_id')) $item = find_item_by_id($item_id);
}
?>
<main class="container" style="max-width:760px;padding:1rem 0;">
  <h1>Edit Item #<?= (int)$item_id ?><?= $platform ? ' — '.h($platform['name']) : '' ?></h1>
  <?php if ($errors): ?>
    <div class="alert error"><ul><?php foreach($errors as $f=>$m){echo '<li>'.h($f.': '.(is_array($m)?json_encode($m):$m)).'</li>'; } ?></ul></div>
  <?php endif; ?>
  <form method="post">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <label>Menu Name <input name="menu_name" value="<?= h((string)($item['menu_name'] ?? '')) ?>" required></label>
    <label>Slug <input name="slug" value="<?= h((string)($item['slug'] ?? '')) ?>"></label>
    <label>Visible
      <select name="visible">
        <?php $vis = (int)($item['visible'] ?? 1); ?>
        <option value="1"<?= $vis===1?' selected':''; ?>>Yes</option>
        <option value="0"<?= $vis===0?' selected':''; ?>>No</option>
      </select>
    </label>
    <label>Position <input name="position" type="number" value="<?= (int)($item['position'] ?? 1) ?>"></label>
    <label>Body (HTML) <textarea name="body_html" rows="6"><?= h((string)($item['body_html'] ?? '')) ?></textarea></label>
    <div class="actions">
      <button class="btn btn-primary">Save</button>
      <a class="btn" href="<?= h(url_for('/staff/platforms/?pid='.$platform_id)) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php if (is_file($footer)) require $footer;
