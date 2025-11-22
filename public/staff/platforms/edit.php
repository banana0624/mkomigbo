<?php
// project-root/public/staff/platforms/edit.php
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
if (!function_exists('as_int')) {
  function as_int($v): int {
    return (int)$v;
  }
}
if (!function_exists('post')) {
  function post(string $k, ?string $d = null): ?string {
    return isset($_POST[$k]) ? (string)$_POST[$k] : $d;
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

$page_title  = 'Edit Platform';
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
  ['label' => 'Edit' . (!empty($platform['name']) ? ' — ' . $platform['name'] : '')],
];
$back_href = '/staff/platforms/?pid=' . (int)($platform['id'] ?? 0);
$back_text = 'Back to Items';

$nav_partial = PRIVATE_PATH . '/common/ui/staff_platforms_nav.php';
if (is_file($nav_partial)) {
  require $nav_partial;
}
/* ======================= */

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (function_exists('csrf_check')) {
    csrf_check();
  }

  $res = update_platform($id, [
    'name'             => trim((string)post('name', '')),
    'slug'             => (string)post('slug', ''),
    'description_html' => (string)post('description_html', ''),
    'visible'          => (post('visible', '') === '' ? null : (int)post('visible', '1')),
    'position'         => (post('position', '') === '' ? null : (int)post('position', '1')),
  ]);

  if (!empty($res['ok'])) {
    header('Location: ' . url_for('/staff/platforms/'));
    exit;
  }

  $errors   = $res['errors'] ?? ['_form' => 'Update failed'];
  $platform = find_platform_by_id($id); // refresh
}
?>
<main class="container" style="max-width:760px;padding:1rem 0;">
  <h1>Edit Platform #<?= (int)$platform['id'] ?></h1>

  <?php if ($errors): ?>
    <div class="alert error">
      <ul>
        <?php foreach ($errors as $f => $m): ?>
          <li><?= h($f . ': ' . (is_array($m) ? json_encode($m) : $m)) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>

    <label>Name
      <input name="name" value="<?= h($platform['name']) ?>" required>
    </label>

    <label>Slug
      <input name="slug" value="<?= h($platform['slug']) ?>">
    </label>

    <label>Visible
      <select name="visible">
        <option value="1"<?= (int)$platform['visible'] === 1 ? ' selected' : ''; ?>>Yes</option>
        <option value="0"<?= (int)$platform['visible'] === 0 ? ' selected' : ''; ?>>No</option>
      </select>
    </label>

    <label>Position
      <input name="position" type="number" value="<?= (int)$platform['position'] ?>">
    </label>

    <label>Description (HTML)
      <textarea name="description_html" rows="6"><?= h($platform['description_html'] ?? '') ?></textarea>
    </label>

    <div class="actions">
      <button class="btn btn-primary">Save</button>
      <a class="btn" href="<?= h(url_for('/staff/platforms/')) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php
if (is_file($footer)) {
  require $footer;
}
