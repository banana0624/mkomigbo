<?php
declare(strict_types=1);

/**
 * project-root/public/staff/subjects/pgs/edit.php
 * Editor for a single `pages` row.
 *
 * Columns used: id, subject_id, title, slug, body, nav_order
 * Optional: is_public for pages, page_files attachments.
 */

/* 1) Bootstrap */
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

/* 3) DB handle */
global $db;
if (!isset($db) || !($db instanceof PDO)) {
  if (function_exists('db')) {
    $db = db();
  } else {
    http_response_code(500);
    exit('Database connection not available.');
  }
}

/* 4) Helpers */
if (!function_exists('h')) {
  function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }
}

/**
 * Load page row by id.
 */
function mk_page_load_by_id(int $id): ?array {
  if (function_exists('find_page_by_id')) {
    $row = find_page_by_id($id);
    return is_array($row) ? $row : null;
  }

  global $db;
  $sql = "SELECT * FROM pages WHERE id = :id LIMIT 1";
  $stmt = $db->prepare($sql);
  $stmt->execute([':id' => $id]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  return $row ?: null;
}

/**
 * Update page row (only columns we know exist).
 * On error, throws; caller will catch and show message.
 */
function mk_page_update(array $page): void {
  global $db;

  $sql = "UPDATE pages
             SET subject_id = :subject_id,
                 title      = :title,
                 slug       = :slug,
                 body       = :body,
                 nav_order  = :nav_order
           WHERE id = :id";

  $stmt = $db->prepare($sql);
  $stmt->execute([
    ':subject_id' => (int)($page['subject_id'] ?? 0),
    ':title'      => (string)($page['title'] ?? ''),
    ':slug'       => (string)($page['slug'] ?? ''),
    ':body'       => (string)($page['body'] ?? ''),
    ':nav_order'  => (int)($page['nav_order'] ?? 0),
    ':id'         => (int)($page['id'] ?? 0),
  ]);
}

/**
 * Load all subjects (for dropdown).
 */
function mk_load_all_subjects(): array {
  if (function_exists('find_all_subjects')) {
    $rows = find_all_subjects();
    return is_array($rows) ? $rows : [];
  }

  global $db;
  try {
    // Prefer nav_order if it exists
    $sql = "SELECT * FROM subjects ORDER BY nav_order, id";
    $stmt = $db->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($rows)) {
      return $rows;
    }
  } catch (Throwable $e) {
    // ignore and fall back
  }

  $sql = "SELECT * FROM subjects ORDER BY id ASC";
  $stmt = $db->query($sql);
  return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Subject label for dropdown.
 */
function mk_subject_label(array $subject): string {
  return (string)(
    $subject['name']
    ?? $subject['menu_name']
    ?? $subject['title']
    ?? $subject['slug']
    ?? ('Subject #' . ($subject['id'] ?? '?'))
  );
}

/* Attachments (optional) */
function mk_page_attachments(int $page_id): array {
  if (!function_exists('page_files_for_page')) {
    return [];
  }
  try {
    return page_files_for_page($page_id, true);
  } catch (Throwable $e) {
    return [];
  }
}

/* 5) Get & validate ID */
$page_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($page_id <= 0) {
  http_response_code(400);
  echo "<h1>Missing or invalid page id.</h1>";
  exit;
}

/* 6) Load page + subjects + attachments */
$page = mk_page_load_by_id($page_id);
if (!$page) {
  http_response_code(404);
  echo "<h1>Page not found.</h1>";
  exit;
}

$subjects    = mk_load_all_subjects();
$attachments = mk_page_attachments($page_id);

$errors = [];
$notice = '';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

/* 7) Handle POST */
if ($method === 'POST') {
  // 7.1 merge posted values
  $page['subject_id'] = isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : (int)$page['subject_id'];
  $page['title']      = trim((string)($_POST['title'] ?? $page['title'] ?? ''));
  $page['slug']       = trim((string)($_POST['slug'] ?? $page['slug'] ?? ''));
  $page['nav_order']  = (int)($_POST['nav_order'] ?? $page['nav_order'] ?? 0);
  $page['body']       = (string)($_POST['body'] ?? $page['body'] ?? '');

  // 7.2 basic validation
  if ($page['subject_id'] <= 0) { $errors[] = 'Please choose a subject.'; }
  if ($page['title']      === '') { $errors[] = 'Title is required.'; }
  if ($page['slug']       === '') { $errors[] = 'Slug is required.'; }

  // 7.3 attachments: delete selected
  if (empty($errors)
      && !empty($_POST['delete_file_ids'])
      && is_array($_POST['delete_file_ids'])
      && function_exists('page_files_delete_by_ids')) {

    $ids = array_map('intval', $_POST['delete_file_ids']);
    $ids = array_values(array_filter($ids, fn($v) => $v > 0));

    if (!empty($ids)) {
      try {
        page_files_delete_by_ids($ids);
      } catch (Throwable $e) {
        $errors[] = 'Attachment delete failed: ' . $e->getMessage();
      }
    }
  }

  // 7.4 attachments: add new
  if (empty($errors)
      && isset($_FILES['attachment'])
      && ($_FILES['attachment']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE
      && function_exists('page_files_insert_from_upload')) {

    $file_title  = trim((string)($_POST['attachment_title'] ?? ''));
    $is_public_f = !empty($_POST['attachment_is_public']) ? 1 : 0;

    try {
      page_files_insert_from_upload(
        (int)$page['id'],
        $_FILES['attachment'],
        $file_title,
        $is_public_f
      );
    } catch (Throwable $e) {
      $errors[] = 'Attachment upload failed: ' . $e->getMessage();
    }
  }

  // 7.5 update page
  if (empty($errors)) {
    try {
      mk_page_update($page);
      $notice = 'Page updated successfully.';
      // reload attachments in case new ones were added/removed
      $attachments = mk_page_attachments($page_id);
    } catch (Throwable $e) {
      $errors[] = 'Database update failed: ' . $e->getMessage();
    }
  }
}

/* 8) Metadata for header */
$page_title = 'Edit page: ' . h($page['title'] ?? ('#' . $page_id));
$body_class = 'staff-body staff-pages-body';
$active_nav = 'staff-pages';

/**
 * Build URL for "+ New Page" action, prefilling the same subject if possible.
 */
$subject_id_for_new = (int)($page['subject_id'] ?? 0);
if ($subject_id_for_new > 0) {
  $new_page_path = '/staff/subjects/pgs/new.php?subject_id=' . rawurlencode((string)$subject_id_for_new);
} else {
  $new_page_path = '/staff/subjects/pgs/new.php';
}
$new_page_url = url_for($new_page_path);

include PRIVATE_PATH . '/shared/header.php';
?>

<main class="mk-container staff-pages-layout">

  <header class="staff-page-header">
    <h1 class="staff-page-title">Edit page</h1>
    <p class="staff-page-subtitle">
      Update the content and settings for this page.
    </p>
  </header>

  <?php if (!empty($notice)): ?>
    <div class="mk-alert mk-alert-success">
      <?= h($notice) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
    <div class="mk-alert mk-alert-danger">
      <h2>There were problems saving this page:</h2>
      <ul>
        <?php foreach ($errors as $error): ?>
          <li><?= h($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form id="page-form"
        class="mk-form"
        action=""
        method="post"
        enctype="multipart/form-data">

    <!-- SUBJECT -->
    <div class="form-group">
      <label for="subject_id">Subject</label>
      <select name="subject_id" id="subject_id" required>
        <option value="">-- Choose subject --</option>
        <?php foreach ($subjects as $s): ?>
          <?php $sid = (int)($s['id'] ?? 0); ?>
          <option value="<?= h((string)$sid) ?>"
            <?= $sid === (int)$page['subject_id'] ? 'selected' : '' ?>>
            <?= h(mk_subject_label($s)) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <p class="help-text">Which subject this page belongs to.</p>
    </div>

    <!-- TITLE -->
    <div class="form-group">
      <label for="title">Title</label>
      <input type="text"
             name="title"
             id="title"
             value="<?= h((string)($page['title'] ?? '')) ?>"
             required>
      <p class="help-text">Public heading of the article.</p>
    </div>

    <!-- SLUG -->
    <div class="form-group">
      <label for="slug">Slug</label>
      <input type="text"
             name="slug"
             id="slug"
             value="<?= h((string)($page['slug'] ?? '')) ?>"
             required>
      <p class="help-text">
        Appears in the URL. Example:
        <code>/subjects/history/<strong>history-overview</strong>/</code>
      </p>
    </div>

    <!-- NAV ORDER -->
    <div class="form-group">
      <label for="nav_order">Order</label>
      <input type="number"
             name="nav_order"
             id="nav_order"
             value="<?= h((string)($page['nav_order'] ?? '0')) ?>">
      <p class="help-text">Lower numbers appear earlier in the sidebar list.</p>
    </div>

    <!-- BODY -->
    <div class="form-group">
      <label for="body">Body</label>
      <textarea name="body"
                id="body"
                rows="18"><?= h((string)($page['body'] ?? '')) ?></textarea>
      <p class="help-text">
        HTML is allowed (&lt;p&gt;, &lt;h2&gt;, &lt;img&gt;, etc.).
      </p>
    </div>

    <!-- ATTACHMENTS -->
    <section class="page-attachments">
      <h2 class="page-attachments-heading">Attachments</h2>

      <?php if (!empty($attachments)): ?>
        <div class="existing-attachments">
          <h3>Existing files</h3>
          <ul class="attachments-list">
            <?php foreach ($attachments as $file): ?>
              <li class="attachments-item">
                <label>
                  <input type="checkbox"
                         name="delete_file_ids[]"
                         value="<?= h((string)($file['id'] ?? '')) ?>">
                  Delete
                </label>
                <span class="attachment-main">
                  <?= h($file['title'] ?? ($file['filename'] ?? 'File')) ?>
                </span>
                <?php if (!empty($file['is_public'])): ?>
                  <span class="badge badge-soft">Public</span>
                <?php else: ?>
                  <span class="badge badge-muted">Staff-only</span>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ul>
          <p class="help-text">Tick and save to remove selected files.</p>
        </div>
      <?php else: ?>
        <p class="help-text">No attachments yet.</p>
      <?php endif; ?>

      <div class="form-group">
        <label for="attachment">Add new attachment</label>
        <input type="file"
               name="attachment"
               id="attachment">
      </div>

      <div class="form-group">
        <label for="attachment_title">Attachment title</label>
        <input type="text"
               name="attachment_title"
               id="attachment_title">
      </div>

      <div class="form-group checkbox-group">
        <label>
          <input type="checkbox"
                 name="attachment_is_public"
                 value="1"
                 checked>
          File is publicly visible
        </label>
      </div>
    </section>

    <!-- ACTIONS -->
    <div class="form-actions form-actions-sticky">
      <button type="submit"
              name="submit"
              class="btn btn-primary">
        <span class="btn-icon" aria-hidden="true">💾</span>
        <span>Save Changes</span>
      </button>

      <a href="<?= h(url_for('/staff/subjects/pgs/index.php')) ?>"

         class="btn btn-secondary">
        Cancel
      </a>

      <!-- + New Page link -->
      <a href="<?= h($new_page_url) ?>"
         class="btn btn-secondary">
        <span class="btn-icon" aria-hidden="true">➕</span>
        <span>New Page</span>
      </a>

      <?php
        // Public page link, if we can build it
        $subject_slug = '';
        if (!empty($page['subject_id']) && !empty($subjects)) {
          foreach ($subjects as $s) {
            if ((int)$s['id'] === (int)$page['subject_id']) {
              $subject_slug = (string)($s['slug'] ?? '');
              break;
            }
          }
        }
        if ($subject_slug !== '' && !empty($page['slug'])) {
          $public_url = url_for(
            '/subjects/' . rawurlencode($subject_slug) . '/' . rawurlencode((string)$page['slug']) . '/'
          );
        } else {
          $public_url = '';
        }
      ?>

      <?php if ($public_url !== ''): ?>
        <a href="<?= h($public_url) ?>"

           class="btn btn-ghost"
           target="_blank"
           rel="noopener">
          View public page →
        </a>
      <?php endif; ?>
    </div>

  </form>

</main>

<?php include PRIVATE_PATH . '/shared/footer.php'; ?>
