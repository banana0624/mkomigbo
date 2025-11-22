<?php
// project-root/public/staff/contributors/directory/create.php
declare(strict_types=1);

// ---------------------------------------------------------------------------
// Bootstrap
// ---------------------------------------------------------------------------
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) {
  http_response_code(500);
  exit('Init not found at: ' . $init);
}
require_once $init;

// ---------------------------------------------------------------------------
// Auth / Permissions
// ---------------------------------------------------------------------------

// Preferred: centralized middleware guard (if your project has it)
if (is_file(PRIVATE_PATH . '/middleware/guard.php')) {
  if (!defined('REQUIRE_LOGIN')) {
    define('REQUIRE_LOGIN', true);
  }
  if (!defined('REQUIRE_PERMS')) {
    // Fine-grained + generic write capability
    define('REQUIRE_PERMS', [
      'contributors.directory.create',
      'contributors.write',
    ]);
  }
  require PRIVATE_PATH . '/middleware/guard.php';
} else {
  // Fallback: direct helpers from auth.php
  if (function_exists('require_login')) {
    require_login();
  }
  if (function_exists('require_permission')) {
    require_permission('contributors.write');
  } elseif (function_exists('require_any_permission')) {
    require_any_permission([
      'contributors.write',
      'contributors.directory.create',
    ]);
  }
}

// ---------------------------------------------------------------------------
// Domain helpers
// ---------------------------------------------------------------------------
require_once PRIVATE_PATH . '/common/contributors/contributors_common.php';

// ---------------------------------------------------------------------------
// Handle POST
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (function_exists('csrf_check')) {
    csrf_check();
  }

  $name   = trim((string)($_POST['name']   ?? ''));
  $email  = trim((string)($_POST['email']  ?? ''));
  $handle = trim((string)($_POST['handle'] ?? ''));

  if ($name !== '') {
    $ok = function_exists('contrib_add')
      ? contrib_add(compact('name', 'email', 'handle'))
      : false;

    if (function_exists('flash')) {
      flash($ok ? 'success' : 'error', $ok ? 'Contributor created.' : 'Create failed.');
    }

    header('Location: ' . url_for('/staff/contributors/directory/'));
    exit;
  }

  if (function_exists('flash')) {
    flash('error', 'Name is required.');
  }
}

// ---------------------------------------------------------------------------
// Page chrome
// ---------------------------------------------------------------------------
$page_title  = 'Create Contributor';
$active_nav  = 'contributors';
$body_class  = 'role--staff role--contrib directory-create';
$page_logo   = '/lib/images/icons/users.svg';

$stylesheets = $stylesheets ?? [];
if (!in_array('/lib/css/ui.css', $stylesheets, true)) {
  $stylesheets[] = '/lib/css/ui.css';
}
if (!in_array('/lib/css/staff_forms.css', $stylesheets, true)) {
  $stylesheets[] = '/lib/css/staff_forms.css';
}

$breadcrumbs = [
  ['label' => 'Home',         'url' => '/'],
  ['label' => 'Staff',        'url' => '/staff/'],
  ['label' => 'Contributors', 'url' => '/staff/contributors/'],
  ['label' => 'Directory',    'url' => '/staff/contributors/directory/'],
  ['label' => 'Create'],
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
        <a href="<?= h(url_for('/staff/contributors/directory/')) ?>">&larr; Back to Directory</a>
      </p>

      <h1>Create Contributor</h1>
      <p class="mk-form__desc">
        Add a simple record to the internal directory. This does not affect the public contributors table.
      </p>

      <?= function_exists('display_session_message') ? display_session_message() : '' ?>

      <form method="post" action="">
        <?= function_exists('csrf_field') ? csrf_field() : '' ?>

        <div class="mk-field">
          <label for="name">Name</label>
          <input
            class="mk-input"
            type="text"
            id="name"
            name="name"
            value="<?= h((string)($_POST['name'] ?? '')) ?>"
            required
          >
        </div>

        <div class="mk-field">
          <label for="email">Email</label>
          <input
            class="mk-input"
            type="email"
            id="email"
            name="email"
            value="<?= h((string)($_POST['email'] ?? '')) ?>"
          >
        </div>

        <div class="mk-field">
          <label for="handle">Handle</label>
          <input
            class="mk-input"
            type="text"
            id="handle"
            name="handle"
            placeholder="@username"
            value="<?= h((string)($_POST['handle'] ?? '')) ?>"
          >
        </div>

        <div class="mk-form__actions">
          <button class="mk-btn-primary" type="submit">Save</button>
          <a class="mk-btn-secondary"
             href="<?= h(url_for('/staff/contributors/directory/')) ?>">Cancel</a>
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
