<?php
declare(strict_types=1);

/**
 * project-root/public/staff/subjects/pgs/new.php
 * Staff: Create a new page row (pages table) + optional single attachment.
 *
 * Columns used: id, subject_id, title, slug, body, nav_order
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
  function h(string $s = ''): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }
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

/* 5) Load subjects list for dropdown */
$subjects = mk_load_all_subjects();

/* 6) Initial page data */
$page = [
  'subject_id' => isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0,
  'title'      => '',
  'slug'       => '',
  'body'       => '',
  'nav_order'  => 1,
];

$errors = [];

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

/* 7) Handle POST */
if ($method === 'POST') {
  $page['subject_id'] = isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : $page['subject_id'];
  $page['title']      = trim((string)($_POST['title'] ?? ''));
  $page['slug']       = trim((string)($_POST['slug'] ?? ''));
  $page['body']       = (string)($_POST['body'] ?? '');
  $page['nav_order']  = isset($_POST['nav_order']) ? (int)$_POST['nav_order'] : 1;

  if ($page['nav_order'] <= 0) {
    $page['nav_order'] = 1;
  }

  // Basic validation
  if ($page['subject_id'] <= 0) {
    $errors[] = 'Subject is required.';
  }
  if ($page['title'] === '') {
    $errors[] = 'Title is required.';
  }
  if ($page['slug'] === '') {
    $errors[] = 'Slug is required.';
  }
  if ($page['body'] === '') {
    $errors[] = 'Body content is required.';
  }

  $insert_ok = false;
  $new_id    = 0;

  if (empty($errors)) {
    // 7a) Prefer your existing insert_page() helper if it exists
    if (function_exists('insert_page')) {
      try {
        $result = insert_page($page);
        if ($result === true) {
          $insert_ok = true;

          // Try to get ID from $page or DB
          if (!empty($page['id'])) {
            $new_id = (int)$page['id'];
          } else {
            if (isset($db) && $db instanceof PDO) {
              $new_id = (int)$db->lastInsertId();
            }
          }
        } elseif (is_array($result)) {
          // helper returned validation errors
          $errors = array_merge($errors, $result);
        }
      } catch (Throwable $e) {
        // insert_page() threw; we'll fallback to direct INSERT
      }
    }

    // 7b) Fallback: direct PDO INSERT if helper missing or did not succeed
    if (!$insert_ok) {
      try {
        $sql = "INSERT INTO pages (subject_id, title, slug, body, nav_order)
                VALUES (:subject_id, :title, :slug, :body, :nav_order)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
          ':subject_id' => $page['subject_id'],
          ':title'      => $page['title'],
          ':slug'       => $page['slug'],
          ':body'       => $page['body'],
          ':nav_order'  => $page['nav_order'],
        ]);

        $insert_ok = true;
        $new_id    = (int)$db->lastInsertId();
      } catch (Throwable $e) {
        $errors[] = 'Database insert failed: ' . $e->getMessage();
      }
    }

    // 7c) Attach optional file if we have a new ID and upload present
    if ($insert_ok && $new_id > 0) {
      if (
        isset($_FILES['attachment']) &&
        ($_FILES['attachment']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE &&
        function_exists('page_files_insert_from_upload')
      ) {
        $file      = $_FILES['attachment'];
        $fileTitle = trim((string)($_POST['attachment_title'] ?? ''));
        $isPublic  = !empty($_POST['attachment_is_public']) ? 1 : 0;

        try {
          page_files_insert_from_upload(
            $new_id,
            $file,
            $fileTitle !== '' ? $fileTitle : null,
            $isPublic
          );
        } catch (Throwable $e) {
          $errors[] = 'An error occurred while uploading the attachment.';
        }
      }

      // 7d) Redirect to edit screen if insert worked
      if (empty($errors)) {
        $url = function_exists('url_for')
          ? url_for('/staff/subjects/pgs/edit.php?id=' . $new_id)
          : '/staff/subjects/pgs/edit.php?id=' . $new_id;

        header('Location: ' . $url);
        exit;
      }
    }
  }
}

/* 8) Metadata for header */
$page_title = 'New page';
$body_class = 'staff-body staff-pages-body';
$active_nav = 'staff-pages';

include PRIVATE_PATH . '/shared/header.php';
?>

<main class="mk-container staff-pages-layout">

  <header class="staff-page-header">
    <div class="staff-page-header-main">
      <h1 class="staff-page-title">New page</h1>
      <p class="staff-page-subtitle">
        Create a new page and optionally attach one file.
      </p>
    </div>

    <div class="staff-page-header-actions">
      <a href="<?= h(function_exists('url_for')
              ? url_for('/staff/subjects/pgs/index.php')
              : '/staff/subjects/pgs/index.php') ?>"
         class="btn btn-secondary"
         title="Go to the pages list to edit an existing page">
        <span class="btn-icon" aria-hidden="true">✏️</span>
        <span>Edit Pages</span>
      </a>
    </div>
  </header>

  <?php if (!empty($errors)): ?>
    <section class="mk-alert mk-alert-danger">
      <h2>There were problems with your submission:</h2>
      <ul>
        <?php foreach ($errors as $error): ?>
          <li><?= h($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </section>
  <?php endif; ?>

  <form
    id="page-form"
    class="mk-form"
    action=""
    method="post"
    enctype="multipart/form-data"
  >
    <section class="mk-card">
      <h2 class="mk-card-title">Page Details</h2>

      <div class="form-grid">
        <div class="form-group">
          <label for="subject_id">Subject</label>
          <select name="subject_id"
                  id="subject_id"
                  class="form-control"
                  required>
            <option value="">-- Select subject --</option>
            <?php foreach ($subjects as $sub): ?>
              <?php
                $sid   = (int)($sub['id'] ?? 0);
              ?>
              <option value="<?= h((string)$sid) ?>"
                <?= $sid === (int)$page['subject_id'] ? 'selected' : '' ?>>
                <?= h(mk_subject_label($sub)) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="title">Title (headline)</label>
          <input type="text"
                 name="title"
                 id="title"
                 class="form-control"
                 required
                 value="<?= h($page['title'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label for="slug">
            Slug
            <span class="help-text">Lowercase, no spaces (use hyphens)</span>
          </label>
          <input type="text"
                 name="slug"
                 id="slug"
                 class="form-control"
                 required
                 value="<?= h($page['slug'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label for="nav_order">
            Nav order
            <span class="help-text">Controls order within this subject</span>
          </label>
          <input type="number"
                 name="nav_order"
                 id="nav_order"
                 class="form-control"
                 min="1"
                 value="<?= h((string)$page['nav_order']) ?>">
        </div>
      </div>

      <div class="form-group">
        <label for="body">
          Body
          <span class="help-text">
            You can paste full HTML here (headings, paragraphs, images).
          </span>
        </label>
        <textarea
          name="body"
          id="body"
          class="form-control form-control-textarea"
          rows="18"
        ><?= h($page['body'] ?? '') ?></textarea>
      </div>
    </section>

    <section class="mk-card page-attachments">
      <h2 class="mk-card-title">Initial Attachment (optional)</h2>
      <p class="help-text">
        You can attach one file to this page now. You can always add more later from the Edit screen.
      </p>

      <div class="form-group">
        <label for="attachment">File</label>
        <input type="file"
               name="attachment"
               id="attachment"
               class="form-control">
      </div>

      <div class="form-group">
        <label for="attachment_title">Attachment title (optional)</label>
        <input type="text"
               name="attachment_title"
               id="attachment_title"
               class="form-control">
      </div>

      <div class="form-group form-group-checkbox">
        <label>
          <input type="checkbox"
                 name="attachment_is_public"
                 value="1"
                 checked>
          Publicly visible on the page
        </label>
      </div>
    </section>

    <div class="form-actions form-actions-sticky">
      <!-- Save -->
      <button type="submit"
              name="submit"
              class="btn btn-primary"
              title="Create this page">
        <span class="btn-icon" aria-hidden="true">💾</span>
        <span>Create Page</span>
      </button>

      <!-- Reset -->
      <button type="button"
              class="btn btn-secondary"
              title="Reset the form"
              onclick="document.getElementById('page-form').reset();">
        <span class="btn-icon" aria-hidden="true">🔄</span>
        <span>Refresh / Clear</span>
      </button>

      <!-- Cancel -->
      <a href="<?= h(function_exists('url_for')
              ? url_for('/staff/subjects/pgs/index.php')
              : '/staff/subjects/pgs/index.php') ?>"
         class="btn btn-link"
         title="Go back to the pages list without saving">
        <span class="btn-icon" aria-hidden="true">↩</span>
        <span>Cancel</span>
      </a>
    </div>
  </form>
</main>

<?php include PRIVATE_PATH . '/shared/footer.php'; ?>
