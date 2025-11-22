<?php
// project-root/public/staff/contributors/delete.php
declare(strict_types=1);

// 1) Bootstrap (contributors → staff → public → project-root)
$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (!is_file($init)) {
  http_response_code(500);
  echo "FATAL: initialize.php not found at {$init}";
  exit;
}
require_once $init;

// 2) Auth guard
if (function_exists('require_staff')) {
  require_staff();
} elseif (function_exists('require_login')) {
  require_login();
}

// 3) DB + helpers
$db = db(); /** @var PDO $db */

// Simple redirect helper
if (!function_exists('mk_staff_redirect')) {
  function mk_staff_redirect(string $path): never {
    $url = function_exists('url_for') ? url_for($path) : $path;
    header('Location: ' . $url, true, 303);
    exit;
  }
}

// Column existence helper (cached)
if (!function_exists('contributors_column_exists')) {
  function contributors_column_exists(string $column): bool {
    static $cache = [];
    $key = strtolower($column);
    if (array_key_exists($key, $cache)) {
      return $cache[$key];
    }
    try {
      $db = db(); /** @var PDO $db */
      $sql = "
        SELECT 1
          FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME   = 'contributors'
           AND COLUMN_NAME  = :col
         LIMIT 1
      ";
      $st = $db->prepare($sql);
      $st->execute([':col' => $column]);
      $cache[$key] = (bool)$st->fetchColumn();
    } catch (Throwable $e) {
      $cache[$key] = false;
    }
    return $cache[$key];
  }
}

$hasSlug = contributors_column_exists('slug');

// CSRF (same as new/edit)
if (!function_exists('contrib_csrf_token')) {
  function contrib_csrf_token(): string {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      @session_start();
    }
    if (empty($_SESSION['contrib_csrf'])) {
      $_SESSION['contrib_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['contrib_csrf'];
  }
}
if (!function_exists('contrib_csrf_tag')) {
  function contrib_csrf_tag(): string {
    return '<input type="hidden" name="_token" value="' . h(contrib_csrf_token()) . '">';
  }
}
if (!function_exists('contrib_csrf_verify')) {
  function contrib_csrf_verify(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
      }
      $sent = $_POST['_token'] ?? '';
      $good = isset($_SESSION['contrib_csrf']) &&
              is_string($sent) &&
              hash_equals($_SESSION['contrib_csrf'], $sent);
      if (!$good) {
        http_response_code(403);
        echo "Invalid CSRF token.";
        exit;
      }
    }
  }
}

// 4) ID param
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  http_response_code(404);
  echo "Invalid contributor id.";
  exit;
}

// 5) Load contributor
$sql = "
  SELECT id,
         COALESCE(display_name, username) AS display_name,
         username,
         " . ($hasSlug ? 'slug' : 'NULL AS slug') . "
    FROM contributors
   WHERE id = :id
   LIMIT 1
";
$st = $db->prepare($sql);
$st->execute([':id' => $id]);
$c = $st->fetch(PDO::FETCH_ASSOC);

if (!$c) {
  http_response_code(404);
  echo "Contributor not found.";
  exit;
}

$displayName = $c['display_name'] ?: $c['username'];

// 6) Handle POST (actual delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  contrib_csrf_verify();

  try {
    $stDel = $db->prepare("DELETE FROM contributors WHERE id = :id");
    if ($stDel->execute([':id' => $id])) {
      if (function_exists('flash')) {
        flash('success', 'Contributor deleted.');
      }
      mk_staff_redirect('/staff/contributors/');
    } else {
      if (function_exists('flash')) {
        flash('error', 'Delete failed. Please try again.');
      }
      mk_staff_redirect('/staff/contributors/show.php?id=' . $id);
    }
  } catch (Throwable $e) {
    if (function_exists('flash')) {
      flash('error', 'Delete failed. This contributor may be referenced elsewhere.');
    }
    mk_staff_redirect('/staff/contributors/show.php?id=' . $id);
  }
}

// 7) Page chrome (confirmation form)
$page_title  = 'Delete Contributor';
$active_nav  = 'contributors';
$stylesheets = $stylesheets ?? [];
if (!in_array('/lib/css/ui.css', $stylesheets, true)) {
  $stylesheets[] = '/lib/css/ui.css';
}
if (!in_array('/lib/css/staff_forms.css', $stylesheets, true)) {
  $stylesheets[] = '/lib/css/staff_forms.css';
}

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
        <a href="<?= h(url_for('/staff/contributors/')) ?>">&larr; Back to Contributors</a>
      </p>

      <h1>Delete Contributor</h1>
      <p class="mk-form__desc">
        This action cannot be undone. Any references to this contributor in content may break.
      </p>

      <p>
        Are you sure you want to delete
        <strong><?= h($displayName) ?></strong>?
      </p>

      <form method="post" action="">
        <?= contrib_csrf_tag() ?>
        <div class="mk-form__actions">
          <button type="submit" class="mk-btn-primary">
            Yes, delete contributor
          </button>
          <a href="<?= h(url_for('/staff/contributors/show.php?id=' . (int)$c['id'])) ?>"
             class="mk-btn-secondary">
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
