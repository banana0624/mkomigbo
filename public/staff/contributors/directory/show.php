<?php
// project-root/public/staff/contributors/directory/show.php
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

// Preferred: centralized middleware guard
if (is_file(PRIVATE_PATH . '/middleware/guard.php')) {
  if (!defined('REQUIRE_LOGIN')) {
    define('REQUIRE_LOGIN', true);
  }
  if (!defined('REQUIRE_PERMS')) {
    // Readers & writers of contributors directory
    define('REQUIRE_PERMS', [
      'contributors.read',
      'contributors.write',
    ]);
  }
  require PRIVATE_PATH . '/middleware/guard.php';
} else {
  // Fallback: direct helpers
  if (function_exists('require_login')) {
    require_login();
  }

  if (function_exists('require_any_permission')) {
    require_any_permission([
      'contributors.read',
      'contributors.write',
    ]);
  } elseif (function_exists('require_permission')) {
    // Minimal: allow writers
    require_permission('contributors.write');
  }
}

// ---------------------------------------------------------------------------
// Domain logic
// ---------------------------------------------------------------------------
require_once PRIVATE_PATH . '/common/contributors/contributors_common.php';

$id  = (string)($_GET['id'] ?? '');
$row = ($id !== '' && function_exists('contrib_find')) ? contrib_find($id) : null;

if (!$row) {
  http_response_code(404);
  exit('Contributor not found');
}

// Derive a nice page title
$displayName = $row['name'] ?? ($row['handle'] ?? ('Contributor #' . (string)$id));

// ---------------------------------------------------------------------------
// Page chrome
// ---------------------------------------------------------------------------
$page_title  = 'Contributor: ' . (string)$displayName;
$active_nav  = 'contributors';
$body_class  = 'role--staff role--contrib directory-show';
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
  ['label' => $displayName],
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

      <h1><?= h($displayName) ?></h1>

      <?= function_exists('display_session_message') ? display_session_message() : '' ?>

      <dl class="mk-dl">
        <div class="mk-dl__row">
          <dt>ID</dt>
          <dd><?= h((string)($row['id'] ?? $id)) ?></dd>
        </div>

        <div class="mk-dl__row">
          <dt>Name</dt>
          <dd><?= h((string)($row['name'] ?? '')) ?></dd>
        </div>

        <div class="mk-dl__row">
          <dt>Email</dt>
          <dd>
            <?php if (!empty($row['email'])): ?>
              <a href="mailto:<?= h($row['email']) ?>"><?= h($row['email']) ?></a>
            <?php else: ?>
              <span class="muted"><em>Not set</em></span>
            <?php endif; ?>
          </dd>
        </div>

        <div class="mk-dl__row">
          <dt>Handle</dt>
          <dd>
            <?php if (!empty($row['handle'])): ?>
              <?= h($row['handle']) ?>
            <?php else: ?>
              <span class="muted"><em>Not set</em></span>
            <?php endif; ?>
          </dd>
        </div>

        <?php if (!empty($row['created_at'])): ?>
          <div class="mk-dl__row">
            <dt>Created</dt>
            <dd><?= h((string)$row['created_at']) ?></dd>
          </div>
        <?php endif; ?>

        <?php if (!empty($row['updated_at'])): ?>
          <div class="mk-dl__row">
            <dt>Updated</dt>
            <dd><?= h((string)$row['updated_at']) ?></dd>
          </div>
        <?php endif; ?>
      </dl>

      <div class="mk-form__actions">
        <a class="mk-btn-primary"
           href="<?= h(url_for('/staff/contributors/directory/edit.php?id=' . urlencode((string)($row['id'] ?? $id)))) ?>">
          Edit
        </a>
        <a class="mk-btn-secondary mk-btn--danger"
           href="<?= h(url_for('/staff/contributors/directory/delete.php?id=' . urlencode((string)($row['id'] ?? $id)))) ?>">
          Delete
        </a>
        <a class="mk-btn-secondary"
           href="<?= h(url_for('/staff/contributors/directory/')) ?>">
          Back to Directory
        </a>
      </div>
    </div>
  </section>
</main>
<?php
if (defined('SHARED_PATH') && is_file(SHARED_PATH . '/footer.php')) {
  require SHARED_PATH . '/footer.php';
} elseif (defined('PRIVATE_PATH') && is_file(PRIVATE_PATH . '/shared/footer.php')) {
  require PRIVATE_PATH . '/shared/footer.php';
}
