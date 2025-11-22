<?php
// project-root/public/staff/contributors/reviews/edit.php
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
if (is_file(PRIVATE_PATH . '/middleware/guard.php')) {
  if (!defined('REQUIRE_LOGIN')) {
    define('REQUIRE_LOGIN', true);
  }
  if (!defined('REQUIRE_PERMS')) {
    define('REQUIRE_PERMS', [
      'contributors.reviews.edit',
      'contributors.write',
    ]);
  }
  require PRIVATE_PATH . '/middleware/guard.php';
} else {
  if (function_exists('require_login')) {
    require_login();
  }

  if (function_exists('require_any_permission')) {
    require_any_permission([
      'contributors.reviews.edit',
      'contributors.write',
    ]);
  } elseif (function_exists('require_permission')) {
    require_permission('contributors.write');
  }
}

// ---------------------------------------------------------------------------
// Domain logic
// ---------------------------------------------------------------------------
require_once PRIVATE_PATH . '/common/contributors/contributors_common.php';

$id  = (string)($_GET['id'] ?? $_POST['id'] ?? '');
$row = ($id !== '' && function_exists('review_find')) ? review_find($id) : null;

if (!$row) {
  http_response_code(404);
  exit('Review not found');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (function_exists('csrf_check')) {
    csrf_check();
  }

  $subject = trim((string)($_POST['subject'] ?? ''));
  $rating  = (int)($_POST['rating'] ?? 0);
  $rating  = max(0, min(5, $rating));
  $comment = trim((string)($_POST['comment'] ?? ''));

  if ($subject !== '') {
    $ok = function_exists('review_update')
      ? review_update($id, compact('subject', 'rating', 'comment'))
      : false;

    if (function_exists('flash')) {
      flash($ok ? 'success' : 'error', $ok ? 'Review updated.' : 'Update failed.');
    }

    header('Location: ' . url_for('/staff/contributors/reviews/'));
    exit;
  }

  if (function_exists('flash')) {
    flash('error', 'Subject is required.');
  }

  // Keep edited values in $row for re-render
  $row['subject'] = $subject;
  $row['rating']  = $rating;
  $row['comment'] = $comment;
}

// ---------------------------------------------------------------------------
// Page chrome
// ---------------------------------------------------------------------------
$page_title  = 'Edit Review';
$active_nav  = 'contributors';
$body_class  = 'role--staff role--contrib reviews-edit';
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
        <a href="<?= h(url_for('/staff/contributors/reviews/')) ?>">&larr; Back to Reviews</a>
      </p>

      <h1>Edit Review</h1>
      <p class="mk-form__desc">
        Adjust the subject, rating, or comment for this review.
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
          <label for="rating">Rating (0–5)</label>
          <input
            class="mk-input"
            type="number"
            id="rating"
            name="rating"
            min="0"
            max="5"
            value="<?= (int)($row['rating'] ?? 0) ?>"
          >
        </div>

        <div class="mk-field">
          <label for="comment">Comment</label>
          <textarea
            class="mk-input"
            id="comment"
            name="comment"
            rows="5"
          ><?= h((string)($row['comment'] ?? '')) ?></textarea>
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
