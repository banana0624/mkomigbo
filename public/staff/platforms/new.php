<?php
// project-root/public/staff/platforms/new.php
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
if (!function_exists('post')) {
  function post(string $k, ?string $d = null): ?string {
    return isset($_POST[$k]) ? (string)$_POST[$k] : $d;
  }
}
if (!function_exists('as_int')) {
  function as_int($v): int {
    return (int)$v;
  }
}

$page_title  = 'New Platform';
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
  ['label' => 'New'],
];

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

  $res = create_platform([
    'name'             => trim((string)post('name', '')),
    'slug'             => (string)post('slug', ''),
    'description_html' => (string)post('description_html', ''),
    'visible'          => as_int(post('visible', '1')),
    'position'         => as_int(post('position', '1')),
  ]);

  if (!empty($res['ok'])) {
    header('Location: ' . url_for('/staff/platforms/'));
    exit;
  }

  $errors = $res['errors'] ?? ['_form' => 'Create failed'];
}
?>
<main class="container" style="max-width:760px;padding:1rem 0;">
  <h1>New Platform</h1>

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
      <input name="name"
             value="<?= h((string)($_POST['name'] ?? '')) ?>"
             required>
    </label>

    <label>Slug
      <input name="slug"
             value="<?= h((string)($_POST['slug'] ?? '')) ?>"
             placeholder="auto-from-name if blank">
    </label>

    <label>Visible
      <select name="visible">
        <option value="1">Yes</option>
        <option value="0">No</option>
      </select>
    </label>

    <label>Position
      <input name="position"
             type="number"
             value="<?= h((string)($_POST['position'] ?? '1')) ?>">
    </label>

    <label>Description (HTML)
      <textarea name="description_html" rows="6"><?= h((string)($_POST['description_html'] ?? '')) ?></textarea>
    </label>

    <div class="actions" style="margin-top:.6rem;display:flex;gap:.5rem;">
      <button class="btn btn-primary">Create</button>
      <a class="btn" href="<?= h(url_for('/staff/platforms/')) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php
if (is_file($footer)) {
  require $footer;
}
