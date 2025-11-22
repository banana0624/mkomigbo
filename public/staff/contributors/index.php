<?php
// project-root/public/staff/contributors/index.php
declare(strict_types=1);

// ---------------------------------------------
// Bootstrap
// ---------------------------------------------
$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (!is_file($init)) {
  http_response_code(500);
  exit('Init not found at: ' . $init);
}
require_once $init;

// ---------------------------------------------
// Auth / permissions
// ---------------------------------------------
if (function_exists('require_staff')) {
  require_staff();
} elseif (function_exists('require_login')) {
  require_login();
}

if (function_exists('require_any_permission')) {
  require_any_permission(['contributors.read', 'contributors.write']);
} elseif (function_exists('require_permission')) {
  require_permission('contributors.read');
}

// ---------------------------------------------
// Domain logic
// ---------------------------------------------
if (is_file(PRIVATE_PATH . '/common/contributors/contributors_common.php')) {
  require_once PRIVATE_PATH . '/common/contributors/contributors_common.php';
}

$contributors = function_exists('contrib_all') ? contrib_all() : [];

// ---------------------------------------------
// Page metadata
// ---------------------------------------------
$page_title  = 'Contributors (Staff)';
$active_nav  = 'contributors';
$body_class  = 'role--staff role--contrib page--staff-contributors';

$stylesheets = $stylesheets ?? [];
if (!in_array('/lib/css/ui.css', $stylesheets, true)) {
  $stylesheets[] = '/lib/css/ui.css';
}

// Let shared header output the CSS + chrome
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
        <h1>Contributors (Staff)</h1>
        <p class="mk-section__subtitle">
          Manage contributors used on the public directory and across platforms.
          <span class="muted small">
            This is <code>public/staff/contributors/index.php</code>.
          </span>
        </p>
      </div>
      <div class="mk-section__header-actions">
        <a class="mk-btn mk-btn--ghost" href="<?= h(url_for('/staff/')) ?>">
          ← Back to Staff Dashboard
        </a>
        <a class="mk-btn mk-btn--primary" href="<?= h(url_for('/staff/contributors/new.php')) ?>">
          + New Contributor
        </a>
      </div>
    </header>

    <?= function_exists('display_session_message') ? display_session_message() : '' ?>

    <?php if (!$contributors): ?>
      <section class="mk-card mk-card--empty">
        <h2>No contributors yet</h2>
        <p class="muted">
          You haven’t created any contributors. Use the button below to add the first one.
        </p>
        <p>
          <a class="mk-btn mk-btn--primary" href="<?= h(url_for('/staff/contributors/new.php')) ?>">
            + New Contributor
          </a>
        </p>
      </section>
    <?php else: ?>
      <section class="mk-card mk-card--table">
        <div class="mk-card__header">
          <div>
            <h2>Directory</h2>
            <p class="muted small">
              <?= count($contributors) ?> contributor<?= count($contributors) === 1 ? '' : 's' ?> in total.
            </p>
          </div>
          <div>
            <a class="mk-btn mk-btn--primary" href="<?= h(url_for('/staff/contributors/new.php')) ?>">
              + New Contributor
            </a>
          </div>
        </div>

        <div class="mk-table-wrap">
          <table class="mk-table mk-table--striped mk-table--spacious">
            <thead>
              <tr>
                <th>#</th>
                <th>Display Name</th>
                <th>Username</th>
                <th>Slug</th>
                <th>Email</th>
                <th>Public?</th>
                <th>Active?</th>
                <th>Public URL</th>
                <th class="mk-table__col-actions">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($contributors as $i => $c): ?>
                <?php
                  $id        = (string)($c['id'] ?? '');
                  $slug      = (string)($c['slug'] ?? '');
                  $publicUrl = $slug !== '' ? url_for('/contributors/' . urlencode($slug) . '/') : '';
                  $isPublic  = array_key_exists('visible', $c)
                               ? (bool)$c['visible']
                               : (isset($c['is_public']) ? (bool)$c['is_public'] : true);
                  $isActive  = isset($c['is_active']) ? (bool)$c['is_active'] : true;
                  $display   = $c['display_name'] ?? $c['username'] ?? ('#' . $id);
                ?>
                <tr>
                  <td><?= (int)($i + 1) ?></td>
                  <td><strong><?= h($display) ?></strong></td>
                  <td class="muted"><?= h($c['username'] ?? '') ?></td>
                  <td><code><?= h($slug) ?></code></td>
                  <td class="muted"><?= h($c['email'] ?? '') ?></td>
                  <td>
                    <span class="mk-badge <?= $isPublic ? 'mk-badge--success' : 'mk-badge--muted' ?>">
                      <?= $isPublic ? 'Yes' : 'No' ?>
                    </span>
                  </td>
                  <td>
                    <span class="mk-badge <?= $isActive ? 'mk-badge--success' : 'mk-badge--muted' ?>">
                      <?= $isActive ? 'Yes' : 'No' ?>
                    </span>
                  </td>
                  <td class="muted small">
                    <?php if ($publicUrl): ?>
                      <a href="<?= h($publicUrl) ?>" target="_blank" rel="noopener">
                        View public
                      </a>
                    <?php else: ?>
                      <span class="muted">—</span>
                    <?php endif; ?>
                  </td>
                  <td class="mk-table__col-actions">
                    <div class="mk-actions-inline">
                      <a class="mk-btn mk-btn--xs"
                         href="<?= h(url_for('/staff/contributors/show.php?id=' . urlencode($id))) ?>">
                        Show
                      </a>
                      <a class="mk-btn mk-btn--xs"
                         href="<?= h(url_for('/staff/contributors/edit.php?id=' . urlencode($id))) ?>">
                        Edit
                      </a>
                      <a class="mk-btn mk-btn--xs mk-btn--danger"
                         href="<?= h(url_for('/staff/contributors/delete.php?id=' . urlencode($id))) ?>">
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

    <footer class="mk-page-footer-links">
      <a class="mk-btn mk-btn--ghost" href="<?= h(url_for('/staff/')) ?>">
        ← Back to Staff Dashboard
      </a>
      <a class="mk-btn" href="<?= h(url_for('/contributors/')) ?>">
        View public contributors →
      </a>
    </footer>
  </section>
</main>
<?php
if (defined('SHARED_PATH') && is_file(SHARED_PATH . '/footer.php')) {
  require SHARED_PATH . '/footer.php';
} elseif (defined('PRIVATE_PATH') && is_file(PRIVATE_PATH . '/shared/footer.php')) {
  require PRIVATE_PATH . '/shared/footer.php';
}
