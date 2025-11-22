<?php
// project-root/public/staff/pages/edit.php
declare(strict_types=1);

// ---------------------------------------------------------------------------
// Bootstrap
// ---------------------------------------------------------------------------
$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (!is_file($init)) {
  http_response_code(500);
  exit('Init not found: ' . htmlspecialchars($init, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
}
require_once $init;

/** @var PDO $db */
global $db;

// ---------------------------------------------------------------------------
// Auth / Permissions
// ---------------------------------------------------------------------------
if (is_file(PRIVATE_PATH . '/middleware/guard.php')) {
  if (!defined('REQUIRE_LOGIN')) {
    define('REQUIRE_LOGIN', true);
  }
  if (!defined('REQUIRE_PERMS')) {
    define('REQUIRE_PERMS', [
      'pages.edit',
      'pages.write',
    ]);
  }
  require PRIVATE_PATH . '/middleware/guard.php';
} else {
  if (function_exists('require_staff')) {
    require_staff();
  } elseif (function_exists('require_login')) {
    require_login();
  }
}

// ---------------------------------------------------------------------------
// Load page
// ---------------------------------------------------------------------------
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
  http_response_code(400);
  exit('Missing page id.');
}

$st = $db->prepare("SELECT * FROM pages WHERE id = :id LIMIT 1");
$st->execute([':id' => $id]);
$page = $st->fetch(PDO::FETCH_ASSOC);
if (!$page) {
  http_response_code(404);
  exit('Page not found.');
}

// Subjects for select
$subjects = $db
  ->query("SELECT id, name FROM subjects ORDER BY (nav_order = 0), nav_order, name")
  ->fetchAll(PDO::FETCH_ASSOC);

// ---------------------------------------------------------------------------
// Handle POST
// ---------------------------------------------------------------------------
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (function_exists('csrf_check')) {
    csrf_check();
  }

  $title      = trim((string)($_POST['title'] ?? ''));
  $slug       = trim((string)($_POST['slug'] ?? ''));
  $summary    = (string)($_POST['summary'] ?? '');
  $body       = (string)($_POST['body'] ?? '');
  $visible    = isset($_POST['visible']) ? 1 : 0;
  $is_active  = isset($_POST['is_active']) ? 1 : 0;
  $nav_order  = (int)($_POST['nav_order'] ?? 0);
  $subject_id = (int)($_POST['subject_id'] ?? 0);

  if ($title === '') {
    $errors[] = 'Title is required.';
  }
  if ($slug === '') {
    $errors[] = 'Slug is required.';
  }
  if ($subject_id <= 0) {
    $errors[] = 'Subject is required.';
  }

  if (!$errors) {
    // Ensure slug uniqueness for other pages
    $chk = $db->prepare("SELECT COUNT(*) FROM pages WHERE slug = :slug AND id <> :id");
    $chk->execute([':slug' => $slug, ':id' => $id]);

    if ((int)$chk->fetchColumn() > 0) {
      $errors[] = 'Slug already in use.';
    } else {
      $up = $db->prepare("
        UPDATE pages
           SET subject_id = :sid,
               title      = :title,
               slug       = :slug,
               summary    = :summary,
               body       = :body,
               visible    = :visible,
               is_active  = :active,
               nav_order  = :nav
         WHERE id         = :id
         LIMIT 1
      ");
      $ok = $up->execute([
        ':sid'    => $subject_id,
        ':title'  => $title,
        ':slug'   => $slug,
        ':summary'=> $summary,
        ':body'   => $body,
        ':visible'=> $visible,
        ':active' => $is_active,
        ':nav'    => $nav_order,
        ':id'     => $id,
      ]);

      if ($ok) {
        if (function_exists('flash')) {
          flash('success', 'Page updated.');
        }
        header('Location: ' . url_for('/staff/pages/show.php?id=' . $id));
        exit;
      } else {
        $errors[] = 'Update failed.';
      }
    }
  }

  // Re-populate $page with posted values on error
  $page = array_merge($page, [
    'title'      => $title,
    'slug'       => $slug,
    'summary'    => $summary,
    'body'       => $body,
    'visible'    => $visible,
    'is_active'  => $is_active,
    'nav_order'  => $nav_order,
    'subject_id' => $subject_id,
  ]);
}

// ---------------------------------------------------------------------------
// Page chrome
// ---------------------------------------------------------------------------
$page_title  = 'Edit Page — ' . (string)($page['title'] ?? ('#' . $id));
$active_nav  = 'staff';
$body_class  = 'role--staff role--pages pages-edit';
$page_logo   = '/lib/images/icons/pages.svg';

$stylesheets = $stylesheets ?? [];
if (!in_array('/lib/css/ui.css', $stylesheets, true)) {
  $stylesheets[] = '/lib/css/ui.css';
}
if (!in_array('/lib/css/staff_forms.css', $stylesheets, true)) {
  $stylesheets[] = '/lib/css/staff_forms.css';
}

$breadcrumbs = [
  ['label' => 'Home',   'url' => '/'],
  ['label' => 'Staff',  'url' => '/staff/'],
  ['label' => 'Pages',  'url' => '/staff/pages/'],
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
        <a href="<?= h(url_for('/staff/pages/show.php?id=' . (int)$page['id'])) ?>">
          &larr; Cancel
        </a>
        &nbsp;
        <a href="<?= h(url_for('/staff/pages/?subject_id=' . (int)$page['subject_id'])) ?>">
          &larr; Pages
        </a>
        &nbsp;
        <a href="<?= h(url_for('/staff/subjects/')) ?>">
          &larr; Subjects
        </a>
        &nbsp;
        <a href="<?= h(url_for('/staff/')) ?>">
          &larr; Staff Dashboard
        </a>
      </p>

      <h1><?= h($page_title) ?></h1>

      <?= function_exists('display_session_message') ? display_session_message() : '' ?>

      <?php if ($errors): ?>
        <div class="alert alert-warning">
          <ul>
            <?php foreach ($errors as $e): ?>
              <li><?= h($e) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" action="">
        <?= function_exists('csrf_field') ? csrf_field() : '' ?>
        <input type="hidden" name="id" value="<?= (int)$page['id'] ?>">

        <div class="mk-field">
          <label for="subject_id">Subject</label>
          <select id="subject_id" name="subject_id" class="mk-input" required>
            <?php foreach ($subjects as $s): ?>
              <option value="<?= (int)$s['id'] ?>"
                <?= ((int)$s['id'] === (int)$page['subject_id']) ? 'selected' : '' ?>>
                #<?= (int)$s['id'] ?> — <?= h((string)$s['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mk-field">
          <label for="title">Title</label>
          <input
            class="mk-input"
            type="text"
            id="title"
            name="title"
            value="<?= h((string)$page['title']) ?>"
            required
          >
        </div>

        <div class="mk-field">
          <label for="slug">Slug</label>
          <input
            class="mk-input"
            type="text"
            id="slug"
            name="slug"
            value="<?= h((string)$page['slug']) ?>"
            required
          >
          <p class="muted small">
            Used in URLs: typically <code>/subjects/{subject}/{slug}/</code>.
          </p>
        </div>

        <div class="mk-field">
          <label for="nav_order">Nav Order</label>
          <input
            class="mk-input"
            type="number"
            id="nav_order"
            name="nav_order"
            value="<?= (int)($page['nav_order'] ?? 0) ?>"
          >
        </div>

        <div class="mk-field mk-field--inline">
          <label>
            <input
              type="checkbox"
              name="visible"
              <?= ((int)($page['visible'] ?? 1) === 1 ? 'checked' : '') ?>
            >
            Visible
          </label>
          <label>
            <input
              type="checkbox"
              name="is_active"
              <?= ((int)($page['is_active'] ?? 1) === 1 ? 'checked' : '') ?>
            >
            Active
          </label>
        </div>

        <div class="mk-field">
          <label for="summary">Summary</label>
          <textarea
            class="mk-input"
            id="summary"
            name="summary"
            rows="4"
          ><?= h((string)($page['summary'] ?? '')) ?></textarea>
        </div>

        <div class="mk-field">
          <label for="body">Body</label>
          <textarea
            class="mk-input"
            id="body"
            name="body"
            rows="12"
          ><?= h((string)($page['body'] ?? '')) ?></textarea>
        </div>

        <div class="mk-form__actions">
          <button class="mk-btn-primary" type="submit">Save Changes</button>
          <a class="mk-btn-secondary"
             href="<?= h(url_for('/staff/pages/show.php?id=' . (int)$page['id'])) ?>">
            Cancel
          </a>
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
