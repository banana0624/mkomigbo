<?php
// project-root/public/staff/contributors/credits/edit.php
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

// Preferred: middleware guard, if present
if (is_file(PRIVATE_PATH . '/middleware/guard.php')) {
  if (!defined('REQUIRE_LOGIN')) {
    define('REQUIRE_LOGIN', true);
  }
  if (!defined('REQUIRE_PERMS')) {
    define('REQUIRE_PERMS', ['contributors.credits.edit', 'contributors.write']);
  }
  require PRIVATE_PATH . '/middleware/guard.php';
} else {
  if (function_exists('require_login')) {
    require_login();
  }
  if (function_exists('require_permission')) {
    require_permission('contributors.write');
  } elseif (function_exists('require_any_permission')) {
    require_any_permission(['contributors.write', 'contributors.credits.edit']);
  }
}

// ---------------------------------------------------------------------------
// Domain helpers
// ---------------------------------------------------------------------------
require_once PRIVATE_PATH . '/common/contributors/contributors_common.php';

// ---------------------------------------------------------------------------
// Load record
// ---------------------------------------------------------------------------
$id  = (string)($_GET['id'] ?? $_POST['id'] ?? '');
$row = ($id !== '' && function_exists('credit_find')) ? credit_find($id) : null;

if (!$row) {
  http_response_code(404);
  exit('Credit not found');
}

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
    $ok = function_exists('credit_update')
      ? credit_update($id, compact('subject', 'points', 'note'))
      : false;

    if (function_exists('flash')) {
      flash($ok ? 'success' : 'error', $ok ? 'Credit updated.' : 'Update failed.');
    }

    header('Location: ' . url_for('/staff/contributors/credits/'));
    exit;
  }

  if (function_exists('flash')) {
    flash('error', 'Subject is required.');
  }

  // Keep modified values in $row for re-render
  $row['subject'] = $subject;
  $row['points']  = $points;
  $row['note']    = $note;
}

// ---------------------------------------------------------------------------
// Page chrome
// ---------------------------------------------------------------------------
$page_title  = 'Edit Credit';
$active_nav  = 'contributors';
$body_class  = 'role--staff role--contrib credits-edit';
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
  ['label' => 'Edit'],
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

      <h1>Edit Credit</h1>
      <p class="mk-form__desc">
        Update the subject, points and note for this credit entry.
      </p>

      <?= function_exists('display_session_message') ? display_session_message() : '' ?>

      <form method="post" action="">
        <?= function_exists('csrf_field') ? csrf_field() : '' ?>
        <input type="hidden" name="id" value="<?= h($id) ?>">

        <div class="mk-field">
          <label for="subject">Subject</label>
          <input
            class="mk-input"
            type="text"
            id="subject"
            name="subject"
            value="<?= h((string)($row['subject'] ?? '')) ?>"
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
            value="<?= (int)($row['points'] ?? 0) ?>"
          >
        </div>

        <div class="mk-field">
          <label for="note">Note</label>
          <textarea
            class="mk-input"
            id="note"
            name="note"
            rows="5"
          ><?= h((string)($row['note'] ?? '')) ?></textarea>
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
