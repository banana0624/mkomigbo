<?php
// project-root/public/staff/contributors/reviews/create.php
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

// Preferred: centralized middleware guard, if present
if (is_file(PRIVATE_PATH . '/middleware/guard.php')) {
  if (!defined('REQUIRE_LOGIN')) {
    define('REQUIRE_LOGIN', true);
  }
  if (!defined('REQUIRE_PERMS')) {
    // Explicit review create permission plus generic write
    define('REQUIRE_PERMS', [
      'contributors.reviews.create',
      'contributors.write',
    ]);
  }
  require PRIVATE_PATH . '/middleware/guard.php';
} else {
  // Fallback: direct helpers from auth.php

  if (function_exists('require_login')) {
    require_login();
  }

  if (function_exists('require_any_permission')) {
    require_any_permission([
      'contributors.reviews.create',
      'contributors.write',
    ]);
  } elseif (function_exists('require_permission')) {
    // Minimal: writers only
    require_permission('contributors.write');
  }
}

// ---------------------------------------------------------------------------
// Domain logic
// ---------------------------------------------------------------------------
require_once PRIVATE_PATH . '/common/contributors/contributors_common.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (function_exists('csrf_check')) {
    csrf_check();
  }

  $subject = trim((string)($_POST['subject'] ?? ''));
  $rating  = (int)($_POST['rating'] ?? 0);
  // Clamp rating between 0 and 5
  $rating  = max(0, min(5, $rating));
  $comment = trim((string)($_POST['comment'] ?? ''));

  if ($subject !== '') {
    if (function_exists('review_add')) {
      review_add(compact('subject', 'rating', 'comment'));
      if (function_exists('flash')) {
        flash('success', 'Review submitted.');
      }
      header('Location: ' . url_for('/staff/contributors/reviews/'));
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
$page_title  = 'New Review';
$active_nav  = 'contributors';
$body_class  = 'role--staff role--contrib reviews-create';
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
  ['label' => 'Reviews',      'url' => '/staff/contributors/reviews/'],
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
        <a href="<?= h(url_for('/staff/contributors/reviews/')) ?>">&larr; Back to Reviews</a>
      </p>

      <h1>New Review</h1>
      <p class="mk-form__desc">
        Record an internal review with a 0–5 rating and an optional comment.
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
          <label for="rating">Rating (0–5)</label>
          <input
            class="mk-input"
            type="number"
            id="rating"
            name="rating"
            min="0"
            max="5"
            value="<?= (int)($_POST['rating'] ?? 5) ?>"
          >
        </div>

        <div class="mk-field">
          <label for="comment">Comment</label>
          <textarea
            class="mk-input"
            id="comment"
            name="comment"
            rows="5"
          ><?= h((string)($_POST['comment'] ?? '')) ?></textarea>
        </div>

        <div class="mk-form__actions">
          <button class="mk-btn-primary" type="submit">Save</button>
          <a class="mk-btn-secondary"
             href="<?= h(url_for('/staff/contributors/reviews/')) ?>">Cancel</a>
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
