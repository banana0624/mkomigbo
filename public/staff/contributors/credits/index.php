<?php
// project-root/public/staff/contributors/credits/index.php
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
    // View access to credits (plus generic contributors.read/write)
    define('REQUIRE_PERMS', [
      'contributors.credits.view',
      'contributors.read',
      'contributors.write',
    ]);
  }
  require PRIVATE_PATH . '/middleware/guard.php';
} else {
  // Fallback: basic auth helpers from auth.php

  // Ensure user is logged in
  if (function_exists('require_login')) {
    require_login();
  }

  // Permissions: readers & writers may see credits
  if (function_exists('require_any_permission')) {
    require_any_permission([
      'contributors.credits.view',
      'contributors.read',
      'contributors.write',
    ]);
  } elseif (function_exists('require_permission')) {
    // Minimal fallback: allow only writers
    require_permission('contributors.write');
  }
}

// ---------------------------------------------------------------------------
// Domain logic
// ---------------------------------------------------------------------------
require_once PRIVATE_PATH . '/common/contributors/contributors_common.php';

// Data
$rows = function_exists('credit_all') ? credit_all() : [];

// ---------------------------------------------------------------------------
// Page chrome
// ---------------------------------------------------------------------------
$page_title  = 'Contributor Credits';
$active_nav  = 'contributors';
$body_class  = 'role--staff role--contrib credits-index';
$page_logo   = '/lib/images/icons/messages.svg';

$stylesheets = $stylesheets ?? [];
if (!in_array('/lib/css/ui.css', $stylesheets, true)) {
  $stylesheets[] = '/lib/css/ui.css';
}

$breadcrumbs = [
  ['label' => 'Home',         'url' => '/'],
  ['label' => 'Staff',        'url' => '/staff/'],
  ['label' => 'Contributors', 'url' => '/staff/contributors/'],
  ['label' => 'Credits'],
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
        <h1>Credits</h1>
        <p class="mk-section__subtitle">
          Internal credit / points tracking for contributors and subjects.
          <span class="muted small">
            This is <code>public/staff/contributors/credits/index.php</code>.
          </span>
        </p>
      </div>
      <div class="mk-section__header-actions">
        <a class="mk-btn mk-btn--ghost"
           href="<?= h(url_for('/staff/contributors/')) ?>">
          ← Back to Contributors
        </a>
        <a class="mk-btn mk-btn--primary"
           href="<?= h(url_for('/staff/contributors/credits/create.php')) ?>">
          + New Credit
        </a>
      </div>
    </header>

    <?= function_exists('display_session_message') ? display_session_message() : '' ?>

    <?php if (!$rows): ?>
      <section class="mk-card mk-card--empty">
        <h2>No credits yet</h2>
        <p class="muted">
          You haven’t recorded any credits. Use the button above to add the first entry.
        </p>
      </section>
    <?php else: ?>
      <section class="mk-card mk-card--table">
        <div class="mk-card__header">
          <div>
            <h2>Credits</h2>
            <p class="muted small">
              <?= count($rows) ?> credit<?= count($rows) === 1 ? '' : 's' ?> recorded.
            </p>
          </div>
          <div>
            <a class="mk-btn mk-btn--primary"
               href="<?= h(url_for('/staff/contributors/credits/create.php')) ?>">
              + New Credit
            </a>
          </div>
        </div>

        <div class="mk-table-wrap">
          <table class="mk-table mk-table--striped mk-table--spacious">
            <thead>
              <tr>
                <th>#</th>
                <th>Subject</th>
                <th>Points</th>
                <th>Note</th>
                <th class="mk-table__col-actions" style="width:180px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $i => $r): ?>
                <?php
                  $id     = (string)($r['id'] ?? '');
                  $subject = (string)($r['subject'] ?? '');
                  $points  = (int)($r['points'] ?? 0);
                  $note    = (string)($r['note'] ?? '');
                ?>
                <tr>
                  <td><?= (int)($i + 1) ?></td>
                  <td><strong><?= h($subject) ?></strong></td>
                  <td><?= $points ?></td>
                  <td class="muted small">
                    <?= h(mb_strlen($note) > 120 ? mb_substr($note, 0, 120) . '…' : $note) ?>
                  </td>
                  <td class="mk-table__col-actions">
                    <div class="mk-actions-inline">
                      <a class="mk-btn mk-btn--xs"
                         href="<?= h(url_for('/staff/contributors/credits/show.php?id=' . urlencode($id))) ?>">
                        View
                      </a>
                      <a class="mk-btn mk-btn--xs"
                         href="<?= h(url_for('/staff/contributors/credits/edit.php?id=' . urlencode($id))) ?>">
                        Edit
                      </a>
                      <a class="mk-btn mk-btn--xs mk-btn--danger"
                         href="<?= h(url_for('/staff/contributors/credits/delete.php?id=' . urlencode($id))) ?>">
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
