<?php
// project-root/public/staff/platforms/items/new.php
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

$platform_id = isset($_GET['platform_id']) ? (int)$_GET['platform_id'] : (int)($_POST['platform_id'] ?? 0);
if ($platform_id <= 0) { header('Location: ' . url_for('/staff/platforms/')); exit; }
$platform = find_platform_by_id($platform_id);
if (!$platform) { header('Location: ' . url_for('/staff/platforms/')); exit; }

$page_title = 'New Item';
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
  ['label'=>'New Item'],
];
$back_href = '/staff/platforms/?pid='. (int)$platform_id;
$back_text = 'Back to Items';
require PRIVATE_PATH . '/common/ui/staff_platforms_nav.php';
/* ======================= */

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (function_exists('csrf_check')) csrf_check();
  $res = create_item($platform_id, [
    'menu_name' => trim((string)post('menu_name','')),
    'slug'      => (string)post('slug',''),
    'body_html' => (string)post('body_html',''),
    'visible'   => as_int(post('visible','1')),
    'position'  => as_int(post('position','1')),
  ]);
  if (!empty($res['ok'])) {
    header('Location: ' . url_for('/staff/platforms/?pid='.$platform_id)); exit;
  }
  $errors = $res['errors'] ?? ['_form' => 'Create failed'];
}
?>
<main class="container" style="max-width:760px;padding:1rem 0;">
  <h1>New Item for “<?= h($platform['name']) ?>”</h1>
  <?php if ($errors): ?>
    <div class="alert error"><ul><?php foreach($errors as $f=>$m){echo '<li>'.h($f.': '.(is_array($m)?json_encode($m):$m)).'</li>'; } ?></ul></div>
  <?php endif; ?>
  <form method="post">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <input type="hidden" name="platform_id" value="<?= (int)$platform_id ?>">
    <label>Menu Name <input name="menu_name" required></label>
    <label>Slug <input name="slug" placeholder="auto-from-name if blank"></label>
    <label>Visible
      <select name="visible"><option value="1">Yes</option><option value="0">No</option></select>
    </label>
    <label>Position <input name="position" type="number" value="1"></label>
    <label>Body (HTML) <textarea name="body_html" rows="6"></textarea></label>
    <div class="actions">
      <button class="btn btn-primary">Create</button>
      <a class="btn" href="<?= h(url_for('/staff/platforms/?pid='.$platform_id)) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php if (is_file($footer)) require $footer;
