<?php
// project-root/public/staff/subjects/index.php
declare(strict_types=1);

// Bootstrap
$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (!is_file($init)) {
  echo "<h1>FATAL: initialize.php missing</h1>";
  echo "<p>Expected at: {$init}</p>";
  exit;
}
require_once $init;

// Auth guard (back-compat)
if (function_exists('require_staff')) {
  require_staff();
} elseif (function_exists('require_login')) {
  require_login();
}

global $db;

// -----------------------------------------------------
// Local helpers (only if missing)
// -----------------------------------------------------
if (!function_exists('h')) {
  function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }
}

if (!function_exists('db_column_exists')) {
  function db_column_exists(string $table, string $column): bool {
    static $cache = [];
    $k = strtolower("$table.$column");
    if (array_key_exists($k, $cache)) {
      return $cache[$k];
    }

    try {
      global $db;
      $sql = "SELECT 1
                FROM information_schema.COLUMNS
               WHERE TABLE_SCHEMA = DATABASE()
                 AND TABLE_NAME   = :t
                 AND COLUMN_NAME  = :c
               LIMIT 1";
      $st = $db->prepare($sql);
      $st->execute([':t' => $table, ':c' => $column]);
      $cache[$k] = (bool)$st->fetchColumn();
    } catch (Throwable $e) {
      $cache[$k] = false;
    }

    return $cache[$k];
  }
}

if (!function_exists('mk_slugify')) {
  function mk_slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
    $text = trim((string)$text, '-');
    return $text !== '' ? $text : 'subject';
  }
}

// -----------------------------------------------------
// Action routing
// -----------------------------------------------------
$action      = $_GET['action'] ?? 'list'; // 'list' | 'new' (others reserved for later)
$subjectSlug = $_GET['slug']   ?? null;   // not used yet

// Flags for schema
$has_menu_name = db_column_exists('subjects', 'menu_name');
$has_name      = db_column_exists('subjects', 'name');
$has_nav_order = db_column_exists('subjects', 'nav_order');
$has_visible   = db_column_exists('subjects', 'visible');
$has_is_public = db_column_exists('subjects', 'is_public');

// Which column to use for subject title?
$subject_title_column = 'name';
if ($has_menu_name) {
  $subject_title_column = 'menu_name';
} elseif (!$has_name && $has_menu_name) {
  $subject_title_column = 'menu_name';
}

// -----------------------------------------------------
// Handle "New Subject" POST
// -----------------------------------------------------
$new_errors = [];
$new_values = [
  'title'     => '',
  'slug'      => '',
  'nav_order' => '',
  'is_public' => 1,
];

if ($action === 'new' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $new_values['title']     = trim((string)($_POST['title'] ?? ''));
  $new_values['slug']      = trim((string)($_POST['slug'] ?? ''));
  $new_values['nav_order'] = trim((string)($_POST['nav_order'] ?? ''));
  $new_values['is_public'] = isset($_POST['is_public']) ? 1 : 0;

  // Validation
  if ($new_values['title'] === '') {
    $new_errors['title'] = 'Subject name/title is required.';
  }

  if ($new_values['slug'] === '') {
    $new_values['slug'] = mk_slugify($new_values['title']);
  }
  if ($new_values['slug'] === '') {
    $new_errors['slug'] = 'Unable to generate a slug.';
  }

  if ($new_values['nav_order'] !== '' && !ctype_digit($new_values['nav_order'])) {
    $new_errors['nav_order'] = 'Nav order must be a positive integer or empty.';
  }

  // Unique slug check
  if (empty($new_errors)) {
    try {
      $sql = "SELECT COUNT(*) FROM subjects WHERE slug = :slug";
      $st  = $db->prepare($sql);
      $st->execute([':slug' => $new_values['slug']]);
      $exists = (int)$st->fetchColumn() > 0;
      if ($exists) {
        $new_errors['slug'] = 'Slug already exists. Choose another.';
      }
    } catch (Throwable $e) {
      // let DB constraints catch it later
    }
  }

  // Insert if OK
  if (empty($new_errors)) {
    try {
      $columns = ['slug'];
      $params  = [
        ':slug' => $new_values['slug'],
      ];

      // Title column (menu_name / name)
      $columns[]       = $subject_title_column;
      $params[':name'] = $new_values['title'];

      // nav_order automation
      if ($has_nav_order) {
        if ($new_values['nav_order'] === '') {
          // append to end
          $sql = "SELECT COALESCE(MAX(nav_order), 0) + 1 FROM subjects";
          $st  = $db->query($sql);
          $nextPos = (int)$st->fetchColumn();
          $new_values['nav_order'] = (string)$nextPos;
        } else {
          $new_values['nav_order'] = (string)max(1, (int)$new_values['nav_order']);
        }

        // ensure unique nav_order (global across all subjects)
        $current = (int)$new_values['nav_order'];
        while (true) {
          $sql = "SELECT 1 FROM subjects WHERE nav_order = :pos LIMIT 1";
          $st  = $db->prepare($sql);
          $st->execute([':pos' => $current]);
          $taken = (bool)$st->fetchColumn();
          if (!$taken) {
            break;
          }
          $current++;
        }
        $new_values['nav_order'] = (string)$current;

        $columns[]             = 'nav_order';
        $params[':nav_order']  = (int)$new_values['nav_order'];
      }

      // visibility flags
      if ($has_is_public) {
        $columns[]             = 'is_public';
        $params[':is_public']  = $new_values['is_public'];
      }
      if ($has_visible) {
        $columns[]             = 'visible';
        $params[':visible']    = $new_values['is_public'];
      }

      // Build INSERT
      $colList = implode(', ', $columns);
      $valList = implode(', ', array_keys($params));

      $sql = "INSERT INTO subjects ({$colList}) VALUES ({$valList})";
      $st  = $db->prepare($sql);
      $st->execute($params);

      // After insert, go back to the list view
      header('Location: ' . url_for('/staff/subjects/index.php'));
      exit;
    } catch (Throwable $e) {
      $new_errors['general'] = 'Insert failed: ' . $e->getMessage();
    }
  }
}

// -----------------------------------------------------
// Fetch subjects for list view (only really needed for list)
// -----------------------------------------------------
$subjects = [];
if ($action === 'list') {
  if (function_exists('find_all_subjects')) {
    $rows = find_all_subjects();
    if ($rows instanceof Traversable) {
      $subjects = iterator_to_array($rows);
    } else {
      $subjects = is_array($rows) ? $rows : [];
    }
  } else {
    // Fallback: simple SQL (tolerant to missing cols)
    $cols = ['id', 'slug'];
    if ($has_name)      { $cols[] = 'name'; }
    if ($has_menu_name) { $cols[] = 'menu_name'; }
    if ($has_nav_order) { $cols[] = 'nav_order'; }
    if ($has_is_public) { $cols[] = 'is_public'; }
    if ($has_visible)   { $cols[] = 'visible'; }

    $orderBy = $has_nav_order ? 'COALESCE(nav_order, id), id' : 'id';

    $sql = "SELECT " . implode(', ', $cols) . "
              FROM subjects
          ORDER BY {$orderBy}";
    $st = $db->query($sql);
    $subjects = $st->fetchAll(PDO::FETCH_ASSOC);
  }
}

// -----------------------------------------------------
// Page chrome
// -----------------------------------------------------
$page_title = 'Subjects (Staff)';
$active_nav = 'staff';
$body_class = 'role--staff role--subjects';

$stylesheets = $stylesheets ?? [];
if (!in_array('/lib/css/ui.css', $stylesheets, true)) {
  $stylesheets[] = '/lib/css/ui.css';
}

// Header
if (defined('SHARED_PATH') && is_file(SHARED_PATH . '/header.php')) {
  include SHARED_PATH . '/header.php';
} else {
  ?><!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <title><?= h($page_title) ?></title>
    <link rel="stylesheet" href="<?= h(url_for('/lib/css/ui.css')) ?>">
  </head>
  <body>
  <?php
}

// Global nav
if (defined('SHARED_PATH') && is_file(SHARED_PATH . '/nav.php')) {
  include SHARED_PATH . '/nav.php';
}
?>
<main class="container" style="max-width:960px;padding:1.75rem 0;">
  <div class="page-header-block">
    <h1>Subjects (Staff)</h1>
    <p class="page-intro">
      Manage subjects available on the public site.<br>
      This is <code>public/staff/subjects/index.php</code>, not the main Staff Dashboard.
    </p>
  </div>

  <div class="page-actions-top"
       style="margin-bottom:1rem;display:flex;gap:.75rem;flex-wrap:wrap;">
    <a href="<?= h(url_for('/staff/')) ?>"
       class="btn">
      &larr; Back to Staff Dashboard
    </a>

    <?php if ($action === 'list'): ?>
      <!-- When listing, show "New Subject" -->
      <a href="<?= h(url_for('/staff/subjects/index.php?action=new')) ?>"
         class="btn btn--primary">
        + New Subject
      </a>
    <?php else: ?>
      <!-- When on "new", show back-to-list button -->
      <a href="<?= h(url_for('/staff/subjects/index.php')) ?>"
         class="btn">
        &larr; Back to Subjects list
      </a>
    <?php endif; ?>
  </div>

  <?php if ($action === 'new'): ?>

    <!-- =========================
         New Subject form (ONLY)
         ========================= -->
    <section class="panel" style="margin-bottom:2rem;">
      <h2>New Subject</h2>
      <p class="page-intro">
        Create a new subject for use on the public subjects directory.
      </p>

      <?php if (!empty($new_errors['general'])): ?>
        <div class="alert alert--error">
          <?= h($new_errors['general']); ?>
        </div>
      <?php endif; ?>

      <form action="<?= h(url_for('/staff/subjects/index.php?action=new')) ?>"
            method="post"
            class="mk-form">

        <div class="form-group">
          <label for="title">Subject Name / Title *</label>
          <input type="text"
                 name="title"
                 id="title"
                 value="<?= h($new_values['title']); ?>"
                 required>
          <small class="muted">
            This will be shown in menus and headings.
          </small>
          <?php if (!empty($new_errors['title'])): ?>
            <div class="field-error"><?= h($new_errors['title']); ?></div>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label for="slug">Slug</label>
          <input type="text"
                 name="slug"
                 id="slug"
                 value="<?= h($new_values['slug']); ?>">
          <small class="muted">
            Optional. If left blank, it will be generated from the title.<br>
            Used in URLs: <code>/subjects/&lt;slug&gt;/</code>
          </small>
          <?php if (!empty($new_errors['slug'])): ?>
            <div class="field-error"><?= h($new_errors['slug']); ?></div>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label for="nav_order">Nav order</label>
          <input type="number"
                 name="nav_order"
                 id="nav_order"
                 min="1"
                 step="1"
                 value="<?= h($new_values['nav_order']); ?>">
          <small class="muted">
            Lower numbers appear first in menus. Leave empty to place this subject at the end.
          </small>
          <?php if (!empty($new_errors['nav_order'])): ?>
            <div class="field-error"><?= h($new_errors['nav_order']); ?></div>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label>
            <input type="checkbox"
                   name="is_public"
                   value="1"
              <?= $new_values['is_public'] ? 'checked' : ''; ?>>
            Public / visible on the site
          </label>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">
            💾 Save Subject
          </button>
          <a href="<?= h(url_for('/staff/subjects/index.php')) ?>"
             class="btn btn-link">
            Cancel
          </a>
        </div>
      </form>
    </section>

  <?php else: ?>

    <!-- =========================
         Subjects list (ONLY)
         ========================= -->
    <div class="table-wrap">
      <h2 style="margin-top:0.5rem;">Existing Subjects</h2>
      <table class="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Slug</th>
            <th>Nav Order</th>
            <th>Public?</th>
            <th>Public URL</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($subjects)): ?>
          <tr>
            <td colspan="7" class="muted">No subjects found.</td>
          </tr>
        <?php else: ?>
          <?php $i = 1; foreach ($subjects as $s): ?>
            <?php
              $id   = (int)($s['id'] ?? 0);
              $name = $s['menu_name'] ?? ($s['name'] ?? ('#' . $id));
              $slug = (string)($s['slug'] ?? '');
              $nav  = $s['nav_order'] ?? null;

              $isPublic = null;
              if (array_key_exists('is_public', $s)) {
                $isPublic = (int)$s['is_public'] === 1;
              } elseif (array_key_exists('visible', $s)) {
                $isPublic = (int)$s['visible'] === 1;
              }

              $publicUrl = $slug !== '' ? url_for('/subjects/' . $slug . '/') : null;
            ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= h($name) ?></td>
              <td class="muted">
                <code><?= h($slug) ?></code>
              </td>
              <td><?= h($nav !== null ? (string)$nav : '') ?></td>
              <td><?= $isPublic === null ? '' : ($isPublic ? 'Yes' : 'No') ?></td>
              <td>
                <?php if ($publicUrl): ?>
                  <a href="<?= h($publicUrl) ?>" target="_blank">
                    View public
                  </a>
                <?php endif; ?>
              </td>
              <td>
                <a href="<?= h(url_for('/staff/subjects/upload_logo.php?id=' . $id)) ?>">
                  Upload logo
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

  <?php endif; ?>
</main>
<?php
// Footer
if (defined('SHARED_PATH') && is_file(SHARED_PATH . '/footer.php')) {
  include SHARED_PATH . '/footer.php';
} else {
  ?></body></html><?php
}
