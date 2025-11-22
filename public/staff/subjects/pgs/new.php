<?php
// project-root/public/staff/subjects/pgs/new.php
declare(strict_types=1);

$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) {
  echo "<h1>FATAL: initialize.php missing</h1>";
  echo "<p>Expected at: {$init}</p>";
  exit;
}
require_once $init;

global $db;

// Auth guard
if (function_exists('require_staff')) {
  require_staff();
} elseif (function_exists('require_login')) {
  require_login();
}

// Simple escaper (fallback)
if (!function_exists('h')) {
  function h(string $v): string {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
  }
}

// Small local slugify if helpers not available
if (!function_exists('mk_slugify')) {
  function mk_slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
    $text = trim((string)$text, '-');
    return $text !== '' ? $text : 'page';
  }
}

// Detect body/content column if present
$body_column = null;
if (function_exists('pf__column_exists')) {
  foreach (['body_html', 'body', 'content_html', 'content'] as $col) {
    if (pf__column_exists('pages', $col)) {
      $body_column = $col;
      break;
    }
  }
}

// Detect which column to use for the title/menu label
$title_column = 'title'; // default
if (function_exists('pf__column_exists')) {
  if (pf__column_exists('pages', 'menu_name')) {
    $title_column = 'menu_name';
  } elseif (!pf__column_exists('pages', 'title') && pf__column_exists('pages', 'name')) {
    $title_column = 'name';
  }
}

// Detect if nav_order column exists (for automation)
$has_nav_order = false;
if (function_exists('pf__column_exists')) {
  $has_nav_order = pf__column_exists('pages', 'nav_order');
}

// Load subjects for dropdown
$subjects = [];
try {
  $sql = "SELECT id, name, slug
            FROM subjects
           ORDER BY nav_order, id";
  $st = $db->query($sql);
  $subjects = $st->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  $subjects = [];
}

$subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;

// Defaults
$values = [
  'subject_id' => $subject_id,
  'title'      => '',
  'slug'       => '',
  'visible'    => 1,
  'nav_order'  => '',
  'body'       => '',
];
$errors = [];

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $values['subject_id'] = isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : 0;
  $values['title']      = trim((string)($_POST['title'] ?? ''));
  $values['slug']       = trim((string)($_POST['slug'] ?? ''));
  $values['visible']    = isset($_POST['visible']) ? 1 : 0;
  $values['nav_order']  = trim((string)($_POST['nav_order'] ?? ''));
  if ($body_column !== null) {
    $values['body'] = (string)($_POST['body'] ?? '');
  }

  // Validation
  if ($values['subject_id'] <= 0) {
    $errors['subject_id'] = 'Please select a subject.';
  }
  if ($values['title'] === '') {
    $errors['title'] = 'Title is required.';
  }
  if ($values['slug'] === '') {
    $values['slug'] = mk_slugify($values['title']);
  }
  if ($values['slug'] === '') {
    $errors['slug'] = 'Unable to generate a slug.';
  }

  // Nav order numeric or empty
  if ($values['nav_order'] !== '' && !ctype_digit($values['nav_order'])) {
    $errors['nav_order'] = 'Nav order must be a positive integer or empty.';
  }

  // Unique slug per subject
  if (empty($errors)) {
    try {
      $sql = "SELECT COUNT(*) FROM pages
              WHERE subject_id = :sid
                AND slug       = :slug";
      $st = $db->prepare($sql);
      $st->execute([
        ':sid'  => $values['subject_id'],
        ':slug' => $values['slug'],
      ]);
      $exists = (int)$st->fetchColumn() > 0;
      if ($exists) {
        $errors['slug'] = 'Slug already exists for this subject.';
      }
    } catch (Throwable $e) {
      // if DB fails, we just let it go; unique constraints will catch it
    }
  }

  if (empty($errors)) {
    try {
      // NOTE: title_column may be menu_name/title/name in the actual table
      $columns = ['subject_id', $title_column, 'slug', 'visible'];
      $params  = [
        ':subject_id' => $values['subject_id'],
        ':title'      => $values['title'],
        ':slug'       => $values['slug'],
        ':visible'    => $values['visible'],
      ];

      // === Automated nav_order handling (per subject) ===
      if ($has_nav_order) {
        // Decide the base position
        if ($values['nav_order'] === '') {
          // If left empty, place at the end: MAX(nav_order) + 1 for this subject
          $sql = "SELECT COALESCE(MAX(nav_order), 0) + 1
                    FROM pages
                   WHERE subject_id = :sid";
          $st = $db->prepare($sql);
          $st->execute([':sid' => $values['subject_id']]);
          $nextPos = (int)$st->fetchColumn();
          $values['nav_order'] = (string)$nextPos;
        } else {
          // Use user-provided nav_order, but ensure it's at least 1
          $values['nav_order'] = (string)max(1, (int)$values['nav_order']);
        }

        // Now ensure no duplicate nav_order for this subject:
        // If position is taken, bump upwards until a free slot is found.
        $current = (int)$values['nav_order'];
        while (true) {
          $sql = "SELECT 1
                    FROM pages
                   WHERE subject_id = :sid
                     AND nav_order  = :pos
                   LIMIT 1";
          $st = $db->prepare($sql);
          $st->execute([
            ':sid' => $values['subject_id'],
            ':pos' => $current,
          ]);
          $taken = (bool)$st->fetchColumn();
          if (!$taken) {
            break;
          }
          $current++;
        }
        $values['nav_order'] = (string)$current;

        // Include nav_order in insert
        $columns[]            = 'nav_order';
        $params[':nav_order'] = (int)$values['nav_order'];
      }

      // Body/content column if present
      if ($body_column !== null) {
        $columns[]       = $body_column;
        $params[':body'] = $values['body'];
      }

      $sql = "INSERT INTO pages (" . implode(', ', $columns) . ")
              VALUES (" . implode(', ', array_keys($params)) . ")";
      $st = $db->prepare($sql);
      $st->execute($params);

      $new_id = (int)$db->lastInsertId();

      // Handle attachments if helper + files are present
      if (function_exists('page_files_handle_uploads') && !empty($_FILES['attachments'])) {
        try {
          page_files_handle_uploads($new_id, $_FILES['attachments']);
        } catch (Throwable $e) {
          if (defined('APP_ENV') && APP_ENV === 'development') {
            error_log('page_files_handle_uploads (new) error: ' . $e->getMessage());
          }
        }
      }

      // Redirect back to Subject Pages console filtered to subject
      $redir = url_for('/staff/subjects/pgs/') . '?subject_id=' . $values['subject_id'];
      header('Location: ' . $redir);
      exit;
    } catch (Throwable $e) {
      $errors['general'] = 'Insert failed: ' . $e->getMessage();
    }
  }
}

$page_title = 'New Page (Subject Pages)';
$body_class = 'role--staff role--subjects-pages';

$stylesheets = $stylesheets ?? [];
if (!in_array('/lib/css/ui.css', $stylesheets, true)) {
  $stylesheets[] = '/lib/css/ui.css';
}
if (!in_array('/lib/css/subjects.css', $stylesheets, true)) {
  $stylesheets[] = '/lib/css/subjects.css';
}

// Header
if (defined('SHARED_PATH') && is_file(SHARED_PATH . '/header.php')) {
  include SHARED_PATH . '/header.php';
} elseif (defined('PRIVATE_PATH') && is_file(PRIVATE_PATH . '/shared/staff_header.php')) {
  include PRIVATE_PATH . '/shared/staff_header.php';
}
?>
<main class="container" style="max-width:800px;padding:1.75rem 0;">
  <div class="page-header-block">
    <h1>New Page</h1>
    <p class="page-intro">
      Create a new page under a subject.
    </p>
  </div>

  <p>
    <a href="<?= h(url_for('/staff/subjects/pgs/')); ?>" class="btn">
      &larr; Back to Subject Pages
    </a>
  </p>

  <?php if (!empty($errors['general'])): ?>
    <div class="alert alert--error">
      <?= h($errors['general']); ?>
    </div>
  <?php endif; ?>

  <form id="page-form"
        action="<?= h($_SERVER['REQUEST_URI']) ?>"
        method="post"
        class="mk-form"
        enctype="multipart/form-data">

    <div class="form-group">
      <label for="subject_id">Subject *</label>
      <select name="subject_id" id="subject_id" required>
        <option value="">-- Select subject --</option>
        <?php foreach ($subjects as $s): ?>
          <option value="<?= (int)$s['id']; ?>"
            <?= $values['subject_id'] === (int)$s['id'] ? 'selected' : ''; ?>>
            <?= h($s['name']); ?> (<?= h($s['slug']); ?>)
          </option>
        <?php endforeach; ?>
      </select>
      <?php if (!empty($errors['subject_id'])): ?>
        <div class="field-error"><?= h($errors['subject_id']); ?></div>
      <?php endif; ?>
    </div>

    <div class="form-group">
      <label for="title">Menu Name / Title *</label>
      <input type="text" name="title" id="title"
             value="<?= h($values['title']); ?>" required>
      <small class="muted">
        This appears in menus and headings.
      </small>
      <?php if (!empty($errors['title'])): ?>
        <div class="field-error"><?= h($errors['title']); ?></div>
      <?php endif; ?>
    </div>

    <div class="form-group">
      <label for="slug">Slug</label>
      <input type="text" name="slug" id="slug"
             value="<?= h($values['slug']); ?>">
      <small class="muted">
        Optional. If left blank, it will be generated from the title.<br>
        Used in URLs: <code>/subjects/&lt;subject&gt;/&lt;slug&gt;/</code>
        (letters, numbers, dash and underscore only).
      </small>
      <?php if (!empty($errors['slug'])): ?>
        <div class="field-error"><?= h($errors['slug']); ?></div>
      <?php endif; ?>
    </div>

    <div class="form-group">
      <label for="nav_order">Position / Nav order</label>
      <input type="number" name="nav_order" id="nav_order" min="1" step="1"
             value="<?= h($values['nav_order']); ?>">
      <small class="muted">
        Lower numbers appear first in menus.
        Leave empty to place this page at the end for its subject.
      </small>
      <?php if (!empty($errors['nav_order'])): ?>
        <div class="field-error"><?= h($errors['nav_order']); ?></div>
      <?php endif; ?>
    </div>

    <div class="form-group">
      <label>
        <input type="checkbox" name="visible" value="1"
          <?= $values['visible'] ? 'checked' : ''; ?>>
        Visible on public site
      </label>
    </div>

    <?php if ($body_column !== null): ?>
      <div class="form-group">
        <label for="body">Content (<?= h($body_column); ?>)</label>
        <textarea name="body" id="body" rows="10"><?= h($values['body']); ?></textarea>
        <small class="muted">
          This is stored in the <code><?= h($body_column); ?></code> column.
        </small>
      </div>
    <?php endif; ?>

    <!-- === Attachments upload (new page) === -->
    <fieldset class="page-attachments-upload">
      <legend>Attachments (optional)</legend>
      <p class="field-hint">
        You can attach images or documents (JPG, JPEG, PNG, GIF, AVIF, PDF).
        You may select multiple files.
      </p>

      <div class="field">
        <label for="attachments" class="field-label">
          Upload attachments
        </label>
        <input type="file"
               id="attachments"
               name="attachments[]"
               multiple
               class="field-control">
        <p class="field-hint small">
          Maximum size: 5 MB per file.
        </p>
      </div>
    </fieldset>
    <!-- === /Attachments upload === -->

    <!-- === Actions bar (same style as edit.php) === -->
    <div class="form-actions-bar">
      <div class="form-actions-bar-inner">
        <!-- Left side: info text -->
        <div class="form-actions-bar-info">
          <span class="actions-label">Page actions</span>
          <span class="actions-hint">Save, clear the form, or cancel.</span>
        </div>

        <!-- Right side: buttons -->
        <div class="form-actions-bar-buttons">
          <!-- Save -->
          <button type="submit"
                  name="submit"
                  class="btn btn-primary"
                  title="Save this page">
            <span class="btn-icon" aria-hidden="true">💾</span>
            <span>Save Page</span>
          </button>

          <!-- Refresh / Clear -->
          <button type="button"
                  class="btn btn-secondary"
                  data-action="reset-form"
                  title="Clear the form back to its initial state">
            <span class="btn-icon" aria-hidden="true">🔄</span>
            <span>Refresh / Clear</span>
          </button>

          <!-- Cancel -->
          <a href="<?= h(url_for('/staff/subjects/pgs/')) ?>"
             class="btn btn-link"
             title="Return to the Pages list without saving">
            <span class="btn-icon" aria-hidden="true">🚪</span>
            <span>Cancel</span>
          </a>
        </div>
      </div>
    </div>
    <!-- === /Actions bar === -->
  </form>
</main>

<?php // project-root/public/staff/subjects/pgs/new.php JS helpers ?>
<script>
(function() {
  const form = document.getElementById('page-form');
  const resetBtn = document.querySelector('[data-action="reset-form"]');

  if (!form || !resetBtn) return;

  // For NEW page: resetting just means “empty everything”
  resetBtn.addEventListener('click', function () {
    form.reset();
  });
})();
</script>

<?php
if (defined('SHARED_PATH') && is_file(SHARED_PATH . '/footer.php')) {
  include SHARED_PATH . '/footer.php';
} elseif (defined('PRIVATE_PATH') && is_file(PRIVATE_PATH . '/shared/footer.php')) {
  include PRIVATE_PATH . '/shared/footer.php';
}
