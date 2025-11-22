<?php
// project-root/public/staff/contributors/directory/index.php
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

// Preferred: centralized middleware guard, if your project has it
if (is_file(PRIVATE_PATH . '/middleware/guard.php')) {
  if (!defined('REQUIRE_LOGIN')) {
    define('REQUIRE_LOGIN', true);
  }
  if (!defined('REQUIRE_PERMS')) {
    // View access to the directory (plus generic contributors.read/write)
    define('REQUIRE_PERMS', [
      'contributors.directory.view',
      'contributors.read',
      'contributors.write',
    ]);
  }
  require PRIVATE_PATH . '/middleware/guard.php';
} else {
  // Fallback: auth helpers from auth.php
  if (function_exists('require_login')) {
    require_login();
  }

  // Readers can see directory; writers are implicitly allowed
  if (function_exists('require_any_permission')) {
    require_any_permission([
      'contributors.directory.view',
      'contributors.read',
      'contributors.write',
    ]);
  } elseif (function_exists('require_permission')) {
    // Minimal fallback: anyone with write can see the directory
    require_permission('contributors.write');
  }
}

// ---------------------------------------------------------------------------
// Domain logic
// ---------------------------------------------------------------------------
require_once PRIVATE_PATH . '/common/contributors/contributors_common.php'; // single source (keep)

// Data
$rows = function_exists('contrib_all') ? contrib_all() : [];

// ---------------------------------------------------------------------------
// Page chrome
// ---------------------------------------------------------------------------
$page_title  = 'Contributor Directory';
$active_nav  = 'contributors';
$body_class  = 'role--staff role--contrib directory-index';
$page_logo   = '/lib/images/icons/users.svg';

$stylesheets = $stylesheets ?? [];
if (!in_array('/lib/css/ui.css', $stylesheets, true)) {
  $stylesheets[] = '/lib/css/ui.css';
}

$breadcrumbs = [
  ['label' => 'Home',         'url' => '/'],
  ['label' => 'Staff',        'url' => '/staff/'],
  ['label' => 'Contributors', 'url' => '/staff/contributors/'],
  ['label' => 'Directory'],
];

if (defined('SHARED_PATH') && is_file(SHARED_PATH . '/staff_header.php')) {
  require SHARED_PATH . '/staff_header.php';
} elseif (defined('PRIVATE_PATH') && is_file(PRIVATE_PATH . '/shared/header.php')) {
  require PRIVATE_PATH . '/shared/header.php';
}
?>
<main class="mk-main mk-main--staff">
  <section class="mk-section">
    <header class="mk-section__header">
      <div>
        <h1>Directory</h1>
        <p class="mk-section__subtitle">
          Lightweight directory used by staff for quick contributor lookups.
          <span class="muted small">
            This is <code>public/staff/contributors/directory/index.php</code>.
          </span>
        </p>
      </div>
      <div class="mk-section__header-actions">
        <a class="mk-btn mk-btn--ghost"
           href="<?= h(url_for('/staff/contributors/')) ?>">
          ← Back to Contributors
        </a>
        <a class="mk-btn mk-btn--primary"
           href="<?= h(url_for('/staff/contributors/directory/create.php')) ?>">
          + Create Contributor
        </a>
      </div>
    </header>

    <?= function_exists('display_session_message') ? display_session_message() : '' ?>

    <?php if (!$rows): ?>
      <section class="mk-card mk-card--empty">
        <h2>No contributors yet</h2>
        <p class="muted">
          You haven’t added any entries to the internal directory.
          Use the button above to create the first one.
        </p>
      </section>
    <?php else: ?>
      <section class="mk-card mk-card--table">
        <div class="mk-card__header">
          <div>
            <h2>Directory</h2>
            <p class="muted small">
              <?= count($rows) ?> contributor<?= count($rows) === 1 ? '' : 's' ?> in this directory.
            </p>
          </div>
          <div>
            <a class="mk-btn mk-btn--primary"
               href="<?= h(url_for('/staff/contributors/directory/create.php')) ?>">
              + Create Contributor
            </a>
          </div>
        </div>

        <div class="mk-table-wrap">
          <table class="mk-table mk-table--striped mk-table--spacious">
            <thead>
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Handle</th>
                <th class="mk-table__col-actions" style="width:180px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $i => $r): ?>
                <?php
                  $id     = (string)($r['id'] ?? '');
                  $name   = (string)($r['name'] ?? '');
                  $email  = (string)($r['email'] ?? '');
                  $handle = (string)($r['handle'] ?? '');
                ?>
                <tr>
                  <td><?= (int)($i + 1) ?></td>
                  <td><strong><?= h($name) ?></strong></td>
                  <td class="muted">
                    <?php if ($email !== ''): ?>
                      <a href="mailto:<?= h($email) ?>"><?= h($email) ?></a>
                    <?php else: ?>
                      <span class="muted small">—</span>
                    <?php endif; ?>
                  </td>
                  <td class="muted"><?= h($handle) ?></td>
                  <td class="mk-table__col-actions">
                    <div class="mk-actions-inline">
                      <a class="mk-btn mk-btn--xs"
                         href="<?= h(url_for('/staff/contributors/directory/show.php?id=' . urlencode($id))) ?>">
                        View
                      </a>
                      <a class="mk-btn mk-btn--xs"
                         href="<?= h(url_for('/staff/contributors/directory/edit.php?id=' . urlencode($id))) ?>">
                        Edit
                      </a>
                      <a class="mk-btn mk-btn--xs mk-btn--danger"
                         href="<?= h(url_for('/staff/contributors/directory/delete.php?id=' . urlencode($id))) ?>">
                        Delete
                      </a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    <?php endif; ?>
  </section>
</main>
<?php
if (defined('SHARED_PATH') && is_file(SHARED_PATH . '/footer.php')) {
  require SHARED_PATH . '/footer.php';
} elseif (defined('PRIVATE_PATH') && is_file(PRIVATE_PATH . '/shared/footer.php')) {
  require PRIVATE_PATH . '/shared/footer.php';
}
