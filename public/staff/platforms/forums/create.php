<?php
declare(strict_types=1);

/**
 * project-root/public/staff/platforms/forums/create.php
 *
 * Staff-only form to create a new forum thread + first post.
 * Uses forum_create_thread_with_post() from private/forum_functions.php.
 *
 * This replaces the older "platform_items" JSON-style create logic and
 * treats "Forums" as the staff interface to the real DB-backed forum.
 */

$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) {
  echo "<h1>FATAL: initialize.php missing</h1>";
  echo "<p>Expected at: {$init}</p>";
  exit;
}
require_once $init;

// Forum helpers
if (!function_exists('forum_find_categories')) {
  $forumFns = dirname(__DIR__, 4) . '/private/forum_functions.php';
  if (is_file($forumFns)) {
    require_once $forumFns;
  }
}

// -------------------------------------------------------------------------
// Auth guard: staff/admin only
// -------------------------------------------------------------------------
if (function_exists('require_staff')) {
  require_staff();
} elseif (function_exists('require_login')) {
  require_login();
}

// -------------------------------------------------------------------------
// Platform meta (kept from old file for consistency with staff platforms)
// -------------------------------------------------------------------------
$platform_slug = 'forums';
$platform_name = 'Forums';

// Body class + logo for header.php
$body_class = trim('role--staff platform--' . $platform_slug);
$page_logo  = "/lib/images/platforms/{$platform_slug}.svg";

// -------------------------------------------------------------------------
// Form state
// -------------------------------------------------------------------------
$errors = [];
$values = [
  'category_id' => '',
  'title'       => '',
  'slug'        => '',
  'body'        => '',
  'subject_id'  => '',
  'page_id'     => '',
];

// -------------------------------------------------------------------------
// POST: handle submission
// -------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // CSRF (support both old + new helpers if they exist)
  if (function_exists('csrf_check')) {
    csrf_check();
  } elseif (function_exists('csrf_token_is_valid')) {
    if (!csrf_token_is_valid()) {
      $errors['_csrf'] = 'Security token is invalid. Please try again.';
    }
  }

  $values['category_id'] = (string)($_POST['category_id'] ?? '');
  $values['title']       = trim((string)($_POST['title'] ?? ''));
  $values['slug']        = trim((string)($_POST['slug'] ?? ''));
  $values['body']        = trim((string)($_POST['body'] ?? ''));
  $values['subject_id']  = (string)($_POST['subject_id'] ?? '');
  $values['page_id']     = (string)($_POST['page_id'] ?? '');

  $category_id = (int)$values['category_id'];

  // Who is creating this thread? (staff/admin)
  $starter_admin_id = null;
  $starter_contributor_id = null;
  $starter_display_name = '';

  if (!empty($_SESSION['admin_id'])) {
    $starter_admin_id = (int)$_SESSION['admin_id'];
  }

  if (!empty($_SESSION['admin_username'])) {
    $starter_display_name = (string)$_SESSION['admin_username'];
  } elseif (!empty($_SESSION['username'])) {
    $starter_display_name = (string)$_SESSION['username'];
  } else {
    $starter_display_name = 'Staff';
  }

  $subject_id = $values['subject_id'] !== '' ? (int)$values['subject_id'] : null;
  $page_id    = $values['page_id']    !== '' ? (int)$values['page_id']    : null;

  if (empty($errors['_csrf'] ?? null)) {
    $result = forum_create_thread_with_post([
      'category_id'            => $category_id,
      'title'                  => $values['title'],
      'slug'                   => $values['slug'],
      'body'                   => $values['body'],
      'subject_id'             => $subject_id,
      'page_id'                => $page_id,
      'starter_contributor_id' => $starter_contributor_id,
      'starter_admin_id'       => $starter_admin_id,
      'starter_display_name'   => $starter_display_name,
    ]);

    if (!empty($result['ok'])) {
      $slug = (string)$result['thread_slug'];
      $threadUrl = url_for('/platforms/forum/thread.php?slug=' . urlencode($slug));

      if (function_exists('flash')) {
        flash('success', 'Forum thread created.');
      }

      header('Location: ' . $threadUrl);
      exit;
    } else {
      $errors = array_merge($errors, $result['errors'] ?? ['_db' => 'Unable to create thread.']);
      if (function_exists('flash') && !empty($errors['_db'] ?? null)) {
        flash('error', $errors['_db']);
      }
    }
  }
} else {
  // GET: allow pre-fill subject/page linkage from query string
  if (isset($_GET['subject_id'])) {
    $values['subject_id'] = (string)(int)$_GET['subject_id'];
  }
  if (isset($_GET['page_id'])) {
    $values['page_id'] = (string)(int)$_GET['page_id'];
  }
}

// -------------------------------------------------------------------------
// Load categories for <select>
// -------------------------------------------------------------------------
$categories = forum_find_categories(true);

// Page context for shared header.php
$page_title = "{$platform_name} • New Thread";
$active_nav = 'staff';

$breadcrumbs = [
  ['label' => 'Home',                 'url' => '/'],
  ['label' => 'Staff',                'url' => '/staff/'],
  ['label' => 'Platforms',            'url' => '/staff/platforms/'],
  ['label' => $platform_name,         'url' => "/staff/platforms/{$platform_slug}/"],
  ['label' => 'New thread'],
];

// Extra CSS specific to this form
$extra_head = <<<'HTML'
<style>
  .staff-forum-form {
    max-width: 720px;
    margin-bottom: 2rem;
    padding-top: 1.25rem;
  }
  .staff-forum-form h1 {
    font-size: 1.6rem;
    margin-bottom: .6rem;
  }
  .staff-forum-form .help-text {
    font-size: .86rem;
    color: #555;
    margin-bottom: 1rem;
  }
  .staff-form-group {
    margin-bottom: .9rem;
  }
  .staff-form-group label {
    display: block;
    font-weight: 600;
    font-size: .9rem;
    margin-bottom: .25rem;
  }
  .staff-form-group select,
  .staff-form-group input[type="text"],
  .staff-form-group textarea {
    width: 100%;
    padding: .4rem .45rem;
    border-radius: .4rem;
    border: 1px solid #ccc;
    font-size: .9rem;
    font-family: inherit;
  }
  .staff-form-group textarea {
    min-height: 160px;
    resize: vertical;
  }
  .staff-form-help-inline {
    font-size: .8rem;
    color: #777;
    margin-top: .15rem;
  }
  .staff-error {
    color: #b91c1c;
    font-size: .8rem;
    margin-top: .15rem;
  }
  .staff-form-actions {
    margin-top: 1rem;
    display: flex;
    gap: .6rem;
    flex-wrap: wrap;
  }
  .staff-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: .38rem .9rem;
    border-radius: .6rem;
    border: 1px solid #111;
    background: #111;
    color: #fff;
    font-size: .9rem;
    text-decoration: none;
    cursor: pointer;
  }
  .staff-btn.secondary {
    background: transparent;
    color: #111;
    border-color: #ccc;
  }
</style>
HTML;

require_once dirname(__DIR__, 4) . '/private/shared/header.php';
?>

<main class="container staff-forum-form">
  <h1>Create — <?= h($platform_name) ?></h1>
  <p class="help-text">
    Create a new discussion topic in the Community Forum. The first post
    will become the opening message in the thread.
  </p>

  <?php if (!empty($errors['_csrf'] ?? null)): ?>
    <div class="staff-error"><?= h($errors['_csrf']) ?></div>
  <?php endif; ?>

  <form action="" method="post">
    <?php
      // Support either csrf_field() or csrf_token_tag() depending on what exists
      if (function_exists('csrf_field')) {
        echo csrf_field();
      } elseif (function_exists('csrf_token_tag')) {
        echo csrf_token_tag();
      }
    ?>

    <div class="staff-form-group">
      <label for="category_id">Category</label>
      <select name="category_id" id="category_id" required>
        <option value="">– Choose a category –</option>
        <?php foreach ($categories as $cat): ?>
          <?php
            $cid = (int)$cat['id'];
            $selected = ($values['category_id'] !== '' && (int)$values['category_id'] === $cid);
          ?>
          <option value="<?= h((string)$cid) ?>" <?= $selected ? 'selected' : '' ?>>
            <?= h((string)$cat['title']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <?php if (!empty($errors['category_id'])): ?>
        <div class="staff-error"><?= h($errors['category_id']) ?></div>
      <?php endif; ?>
    </div>

    <div class="staff-form-group">
      <label for="title">Title</label>
      <input type="text"
             name="title"
             id="title"
             required
             value="<?= h($values['title']) ?>">
      <?php if (!empty($errors['title'])): ?>
        <div class="staff-error"><?= h($errors['title']) ?></div>
      <?php endif; ?>
    </div>

    <div class="staff-form-group">
      <label for="slug">Slug (optional)</label>
      <input type="text"
             name="slug"
             id="slug"
             value="<?= h($values['slug']) ?>"
             placeholder="leave blank to auto-generate (e.g. my-first-thread)">
      <div class="staff-form-help-inline">
        Lowercase letters, numbers and dashes only. If left blank, a
        slug will be generated from the title.
      </div>
      <?php if (!empty($errors['slug'])): ?>
        <div class="staff-error"><?= h($errors['slug']) ?></div>
      <?php endif; ?>
    </div>

    <div class="staff-form-group">
      <label for="body">Opening message</label>
      <textarea name="body"
                id="body"
                required><?= h($values['body']) ?></textarea>
      <?php if (!empty($errors['body'])): ?>
        <div class="staff-error"><?= h($errors['body']) ?></div>
      <?php endif; ?>
    </div>

    <div class="staff-form-group">
      <label>Optional: link to subject/page</label>
      <div style="display:flex;gap:.6rem;flex-wrap:wrap;">
        <div style="flex:1 1 130px;min-width:130px;">
          <input type="text"
                 name="subject_id"
                 id="subject_id"
                 value="<?= h($values['subject_id']) ?>"
                 placeholder="Subject ID">
          <div class="staff-form-help-inline">
            If this discussion relates to a specific subject, put its ID here.
          </div>
        </div>
        <div style="flex:1 1 130px;min-width:130px;">
          <input type="text"
                 name="page_id"
                 id="page_id"
                 value="<?= h($values['page_id']) ?>"
                 placeholder="Page ID">
          <div class="staff-form-help-inline">
            If it relates to a specific page/article, put its ID here.
          </div>
        </div>
      </div>
    </div>

    <?php if (!empty($errors['_db'] ?? null)): ?>
      <div class="staff-error"><?= h($errors['_db']) ?></div>
    <?php endif; ?>

    <div class="staff-form-actions">
      <button type="submit" class="staff-btn">Create thread</button>
      <a class="staff-btn secondary" href="<?= h(url_for('/staff/platforms/forums/')) ?>">Cancel</a>
    </div>
  </form>
</main>

<?php require_once dirname(__DIR__, 4) . '/private/shared/footer.php'; ?>