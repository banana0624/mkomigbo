<?php
// project-root/public/staff/subjects/pgs/edit.php
declare(strict_types=1);

/**
 * Edit a subject page (within staff/subjects/pgs)
 * - Core page fields (menu_name, title, slug, body, nav_order, is_public)
 * - Attachment list with "Delete" checkboxes
 * - Attachment upload (single file)
 */

/* 1) Bootstrap (initialize.php) */
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) {
  echo "<h1>FATAL: initialize.php missing</h1>";
  echo "<p>Expected at: {$init}</p>";
  exit;
}
require_once $init;

/* 2) Auth guard */
if (function_exists('require_staff')) {
  require_staff();
} elseif (function_exists('require_login')) {
  require_login();
}

/* 3) Helpers */
global $db;

/**
 * Small wrapper so this file works even if pf__column_exists()
 * isn't available (but in your project it should already exist in
 * private/functions/page_functions.php).
 */
if (!function_exists('pf__column_exists')) {
  function pf__column_exists(string $table, string $column): bool {
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

/* 4) Get ID */
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  if (function_exists('redirect_to') && function_exists('url_for')) {
    redirect_to(url_for('/staff/subjects/pgs/index.php'));
  }
  echo "<p>Missing or invalid page ID.</p>";
  exit;
}

$errors = [];
$page   = null;

/* 5) Load current page row */
if (function_exists('page_get_by_id')) {
  $page = page_get_by_id($id);
} else {
  // Fallback if helper is missing
  $stmt = $db->prepare("SELECT * FROM pages WHERE id = :id LIMIT 1");
  $stmt->execute([':id' => $id]);
  $page = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

if (!$page) {
  echo "<p>Page not found.</p>";
  exit;
}

/* 6) Process POST */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  /* 6a) Delete selected attachments (if any) */
  $delete_ids = $_POST['delete_file_ids'] ?? [];
  if (is_array($delete_ids) && !empty($delete_ids) && function_exists('page_files_delete_by_ids')) {
    // Normalize to unique ints
    $delete_ids = array_values(array_unique(array_map('intval', $delete_ids)));
    try {
      page_files_delete_by_ids($delete_ids);
    } catch (Throwable $e) {
      $errors[] = 'Failed to delete one or more attachments: ' . $e->getMessage();
    }
  }

  /* 6b) Handle new attachment upload (if wired) */
  if (!empty($_FILES['attachment_file']['name'] ?? '') && function_exists('page_file_handle_upload')) {
    try {
      $attTitle  = trim($_POST['attachment_title'] ?? '');
      $attPublic = !empty($_POST['attachment_is_public']);
      page_file_handle_upload((int)$page['id'], $_FILES['attachment_file'], $attTitle, $attPublic);
    } catch (Throwable $e) {
      $errors[] = 'Attachment upload failed: ' . $e->getMessage();
    }
  }

  /* 6c) Core page fields */
  $menu_name = trim($_POST['menu_name'] ?? ($page['menu_name'] ?? ''));
  $title     = trim($_POST['title']     ?? ($page['title']     ?? ''));
  $slug      = trim($_POST['slug']      ?? ($page['slug']      ?? ''));

  // Body/content (works for either `body` or `content` column)
  $body_post = trim($_POST['body'] ?? ($_POST['content'] ?? ''));
  if ($body_post === '' && isset($page['body'])) {
    $body_post = $page['body'];
  } elseif ($body_post === '' && isset($page['content'])) {
    $body_post = $page['content'];
  }

  // Visible / public flags
  $is_public = !empty($_POST['is_public']) ? 1 : 0;

  // Position / nav order
  $nav_raw   = $_POST['nav_order'] ?? $_POST['position'] ?? ($page['nav_order'] ?? $page['position'] ?? '');
  $nav_order = is_numeric($nav_raw) ? (int)$nav_raw : null;

  /* 6d) Simple validation */
  if ($menu_name === '') {
    $errors[] = 'Menu name is required.';
  }
  if ($title === '') {
    $errors[] = 'Page title is required.';
  }

  /* 6e) If no validation errors, update the DB */
  if (empty($errors)) {
    $setParts = [];
    $params   = [':id' => $id];

    if (pf__column_exists('pages', 'menu_name')) {
      $setParts[]           = 'menu_name = :menu_name';
      $params[':menu_name'] = $menu_name;
    }

    if (pf__column_exists('pages', 'title')) {
      $setParts[]       = 'title = :title';
      $params[':title'] = $title;
    }

    if (pf__column_exists('pages', 'slug')) {
      $setParts[]      = 'slug = :slug';
      $params[':slug'] = $slug;
    }

    if (pf__column_exists('pages', 'body')) {
      $setParts[]     = 'body = :body';
      $params[':body'] = $body_post;
    } elseif (pf__column_exists('pages', 'content')) {
      $setParts[]     = 'content = :body';
      $params[':body'] = $body_post;
    }

    if (pf__column_exists('pages', 'is_public')) {
      $setParts[]          = 'is_public = :is_public';
      $params[':is_public'] = $is_public;
    } elseif (pf__column_exists('pages', 'visible')) {
      $setParts[]          = 'visible = :is_public';
      $params[':is_public'] = $is_public;
    }

    if ($nav_order !== null) {
      if (pf__column_exists('pages', 'nav_order')) {
        $setParts[]           = 'nav_order = :nav_order';
        $params[':nav_order'] = $nav_order;
      } elseif (pf__column_exists('pages', 'position')) {
        $setParts[]           = 'position = :nav_order';
        $params[':nav_order'] = $nav_order;
      }
    }

    if (!empty($setParts)) {
      $sql = "UPDATE pages
                 SET " . implode(', ', $setParts) . "
               WHERE id = :id
               LIMIT 1";
      $stmt = $db->prepare($sql);
      $stmt->execute($params);
    }

    // Flash + redirect back to this edit page (so refresh won't re-POST)
    if (function_exists('session_flash')) {
      session_flash('notice', 'Page updated successfully.');
    }

    if (function_exists('redirect_to') && function_exists('url_for')) {
      redirect_to(url_for('/staff/subjects/pgs/edit.php?id=' . urlencode((string)$id)));
    } else {
      // Fallback simple reload (no HTML escaping in HTTP header)
      header('Location: ' . $_SERVER['REQUEST_URI']);
      exit;
    }
  }

  // Merge posted values back into $page so the form shows what the user tried
  $page['menu_name'] = $menu_name;
  $page['title']     = $title;
  $page['slug']      = $slug;

  if (isset($page['body'])) {
    $page['body'] = $body_post;
  } else {
    $page['content'] = $body_post;
  }

  $page['is_public'] = $is_public;
  if (isset($page['nav_order'])) {
    $page['nav_order'] = $nav_order;
  } elseif (isset($page['position'])) {
    $page['position'] = $nav_order;
  }
}

/* 7) Existing attachments for this page (for the list with delete checkboxes) */
$attachments = [];
if (function_exists('page_files_for_page')) {
  $attachments = page_files_for_page((int)$page['id'], true); // include non-public
}

/* 8) Page chrome */
$page_title = 'Edit Page';

// Use direct path instead of SHARED_PATH constant
include dirname(__DIR__, 4) . '/private/shared/staff_header.php';
?>
<main class="staff-main">
  <header class="page-header">
    <h1>Edit Page</h1>
  </header>

  <p class="page-back-link">
    <a href="<?= h(url_for('/staff/subjects/pgs/')) ?>">
      &laquo; Back to Pages list
    </a>
  </p>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <h2>There were problems with your submission:</h2>
      <ul>
        <?php foreach ($errors as $err): ?>
          <li><?= h($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if (function_exists('session_flash_output')): ?>
    <?= session_flash_output('notice'); ?>
  <?php endif; ?>

  <form id="page-form"
        action=""
        method="post"
        class="mk-form"
        enctype="multipart/form-data">

    <section class="form-section">
      <h2>Page Details</h2>

      <div class="form-row">
        <label for="menu_name">Menu name *</label>
        <input type="text"
               id="menu_name"
               name="menu_name"
               value="<?= h($page['menu_name'] ?? '') ?>">
        <p class="hint">Short label used in navigation menus.</p>
      </div>

      <div class="form-row">
        <label for="title">Title *</label>
        <input type="text"
               id="title"
               name="title"
               value="<?= h($page['title'] ?? '') ?>">
        <p class="hint">Full title shown as the main heading of the article.</p>
      </div>

      <div class="form-row">
        <label for="slug">Slug</label>
        <input type="text"
               id="slug"
               name="slug"
               value="<?= h($page['slug'] ?? '') ?>">
        <p class="hint">Used in URLs: /subjects/&lt;subject&gt;/&lt;slug&gt;/ (letters, numbers, - and _ only).</p>
      </div>

      <div class="form-row">
        <label for="body">Body</label>
        <textarea id="body"
                  name="body"
                  rows="15"><?= h($page['body'] ?? ($page['content'] ?? '')) ?></textarea>
        <p class="hint">You can paste formatted HTML here (headings, lists, tables, quotes).</p>
      </div>

      <div class="form-row form-row-inline">
        <div>
          <label for="nav_order">Position / Nav order</label>
          <input type="number"
                 id="nav_order"
                 name="nav_order"
                 min="1"
                 value="<?= h($page['nav_order'] ?? ($page['position'] ?? '')) ?>">
          <p class="hint">Lower numbers appear first in the subject's page list.</p>
        </div>

        <div class="form-checkbox">
          <label>
            <input type="checkbox"
                   name="is_public"
                   value="1"
                   <?= !empty($page['is_public'] ?? $page['visible'] ?? 0) ? 'checked' : '' ?>>
            Publicly visible?
          </label>
          <p class="hint">If unchecked, the page is hidden from the public site but visible to staff.</p>
        </div>
      </div>
    </section>

    <!-- 9) Attachments section -->
    <section class="form-section page-attachments">
      <h2>Attachments</h2>

      <?php if (!empty($attachments)): ?>
        <p class="hint">Existing files attached to this page. Tick "Delete" for any you want to remove.</p>
        <ul class="page-attachments-list">
          <?php foreach ($attachments as $f): ?>
            <?php
              $stored = $f['stored_filename'] ?? $f['filename'] ?? '';
              $fileUrl = $stored !== ''
                ? url_for('/lib/uploads/pages/' . $stored)
                : '#';

              $label = $f['title']
                ?? $f['original_filename']
                ?? $f['stored_filename']
                ?? 'Attachment';
            ?>
            <li class="page-attachment-item">
              <?php if ($stored !== ''): ?>
                <a href="<?= h($fileUrl) ?>" target="_blank" rel="noopener">
                  <?= h($label) ?>
                </a>
              <?php else: ?>
                <span><?= h($label) ?></span>
              <?php endif; ?>

              <?php if (!empty($f['filesize'])): ?>
                <span class="attachment-meta">
                  (<?= h(number_format((float)$f['filesize'] / 1024, 1)) ?> KB)
                </span>
              <?php endif; ?>

              <label class="attachment-delete">
                <input type="checkbox"
                       name="delete_file_ids[]"
                       value="<?= (int)$f['id'] ?>">
                Delete
              </label>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p class="hint">No attachments yet for this page.</p>
      <?php endif; ?>

      <div class="attachment-upload">
        <h3>Add a new attachment</h3>

        <div class="form-row">
          <label for="attachment_file">File</label>
          <input type="file"
                 id="attachment_file"
                 name="attachment_file">
          <p class="hint">Allowed types and size limits follow your image/file rules (see image_functions.php).</p>
        </div>

        <div class="form-row">
          <label for="attachment_title">Attachment title</label>
          <input type="text"
                 id="attachment_title"
                 name="attachment_title"
                 value="">
          <p class="hint">Optional. If set, shown instead of the raw filename.</p>
        </div>

        <div class="form-checkbox">
          <label>
            <input type="checkbox"
                   name="attachment_is_public"
                   value="1"
                   checked>
            Publicly visible attachment?
          </label>
        </div>
      </div>
    </section>

    <!-- 10) Actions bar -->
    <div class="form-actions form-actions-sticky">
      <!-- Save -->
      <button type="submit"
              name="submit"
              class="btn btn-primary"
              title="Save changes to this page">
        <span class="btn-icon" aria-hidden="true">💾</span>
        <span>Save Changes</span>
      </button>

      <!-- Refresh / Clear: reset to values as at page load -->
      <button type="button"
              class="btn btn-secondary"
              onclick="document.getElementById('page-form').reset();"
              title="Reset all fields to their original values">
        <span class="btn-icon" aria-hidden="true">⟳</span>
        <span>Refresh / Clear</span>
      </button>

      <!-- Cancel: back to the Pages list -->
      <a href="<?= h(url_for('/staff/subjects/pgs/')) ?>"

         class="btn btn-link"
         title="Discard changes and go back to the Pages list">
        <span class="btn-icon" aria-hidden="true">←</span>
        <span>Cancel</span>
      </a>
    </div>
  </form>
</main>

<?php
// Footer include via direct path
include dirname(__DIR__, 4) . '/private/shared/footer.php';
