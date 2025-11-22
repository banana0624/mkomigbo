<?php
// project-root/public/staff/contributors/credits/create.php
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

// Preferred path: centralized middleware guard (if present)
if (is_file(PRIVATE_PATH . '/middleware/guard.php')) {
  if (!defined('REQUIRE_LOGIN')) {
    define('REQUIRE_LOGIN', true);
  }
  if (!defined('REQUIRE_PERMS')) {
    // Fine-grained permission for creating credits
    define('REQUIRE_PERMS', ['contributors.credits.create', 'contributors.write']);
  }
  require PRIVATE_PATH . '/middleware/guard.php';
} else {
  // Fallback: unified helpers from auth.php
  if (function_exists('require_login')) {
    require_login();
  }
  if (function_exists('require_permission')) {
    require_permission('contributors.write');
  } elseif (function_exists('require_any_permission')) {
    require_any_permission(['contributors.write', 'contributors.credits.create']);
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

  $subject = trim((string)($_POST['subject'] ?? ''));
  $points  = (int)($_POST['points'] ?? 0);
  $note    = trim((string)($_POST['note'] ?? ''));

  if ($subject !== '') {
    if (function_exists('credit_add')) {
      $ok = credit_add(compact('subject', 'points', 'note'));
      if (function_exists('flash')) {
        flash(
          $ok ? 'success' : 'error',
          $ok ? 'Credit added.' : 'Unable to add credit.'
        );
      }
      header('Location: ' . url_for('/staff/contributors/credits/'));
      exit;
    }
  }

  if (function_exists('flash')) {
    flash('error', 'Subject is required.');
  }
}

// ---------------------------------------------------------------------------
// Page chrome
// ---------------------------------------------------------------------------
$page_title  = 'New Credit';
$active_nav  = 'contributors';
$body_class  = 'role--staff role--contrib credits-new';
$page_logo   = '/lib/images/icons/messages.svg';

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
  ['label' => 'Credits',      'url' => '/staff/contributors/credits/'],
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
        <a href="<?= h(url_for('/staff/contributors/credits/')) ?>">&larr; Back to Credits</a>
      </p>

      <h1>New Credit</h1>
      <p class="mk-form__desc">
        Record a new credit / points entry against a subject or contributor.
      </p>

      <?= function_exists('display_session_message') ? display_session_message() : '' ?>

      <form method="post" action="">
        <?= function_exists('csrf_field') ? csrf_field() : '' ?>

        <div class="mk-field">
          <label for="subject">Subject (name or ID)</label>
          <input
            class="mk-input"
            type="text"
            id="subject"
            name="subject"
            value="<?= h((string)($_POST['subject'] ?? '')) ?>"
            required
          >
        </div>

        <div class="mk-field">
          <label for="points">Points</label>
          <input
            class="mk-input"
            type="number"
            step="1"
            id="points"
            name="points"
            value="<?= (int)($_POST['points'] ?? 0) ?>"
          >
          <small>Optional. Use positive or negative values as needed.</small>
        </div>

        <div class="mk-field">
          <label for="note">Note</label>
          <textarea
            class="mk-input"
            id="note"
            name="note"
            rows="5"
          ><?= h((string)($_POST['note'] ?? '')) ?></textarea>
          <small>Optional explanation for this credit.</small>
        </div>

        <div class="mk-form__actions">
          <button class="mk-btn-primary" type="submit">Save</button>
          <a class="mk-btn-secondary"
             href="<?= h(url_for('/staff/contributors/credits/')) ?>">Cancel</a>
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
