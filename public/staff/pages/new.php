<?php
// project-root/public/staff/pages/new.php
declare(strict_types=1);

// ---------------------------------------------------------------------------
// Bootstrap
// ---------------------------------------------------------------------------
$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (!is_file($init)) {
  http_response_code(500);
  exit('Initialize not found: ' . htmlspecialchars($init, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
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
      'pages.create',
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
// Helpers
// ---------------------------------------------------------------------------
if (!function_exists('mk_slugify')) {
  function mk_slugify(string $s): string {
    $s = strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9]+/i', '-', $s) ?? '';
    return trim($s, '-');
  }
}

if (!function_exists('mk_sanitize_filename')) {
  function mk_sanitize_filename(string $name): string {
    $name = trim($name);
    $ext  = '';
    $pos  = strrpos($name, '.');
    if ($pos !== false) {
      $ext  = substr($name, $pos + 1);
      $base = substr($name, 0, $pos);
    } else {
      $base = $name;
    }
    $base = preg_replace('/[^A-Za-z0-9_\- ]+/', '', $base) ?? 'file';
    $base = preg_replace('/\s+/', '_', $base) ?? 'file';
    $base = trim($base, '._- ');
    $out  = $base !== '' ? $base : 'file';
    if ($ext !== '') {
      $out .= '.' . preg_replace('/[^A-Za-z0-9]+/', '', $ext);
    }
    return $out;
  }
}

// ---------------------------------------------------------------------------
// Read subject (if passed)
// ---------------------------------------------------------------------------
$subject_id = (int)($_GET['subject_id'] ?? $_POST['subject_id'] ?? 0);
$subject    = null;

if ($subject_id > 0) {
  $st = $db->prepare("SELECT id, name, slug FROM subjects WHERE id = :id");
  $st->execute([':id' => $subject_id]);
  $subject = $st->fetch(PDO::FETCH_ASSOC) ?: null;
}

// ---------------------------------------------------------------------------
// Defaults / POST handling
// ---------------------------------------------------------------------------
$errors    = [];
$title     = '';
$slug      = '';
$summary   = '';
$body      = '';
$visible   = 1;
$nav_order = 0;
$created_notice = false;
$page_id   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (function_exists('csrf_check')) {
    csrf_check();
  }

  // 1) Validate subject
  $subject_id = (int)($_POST['subject_id'] ?? 0);
  if ($subject_id <= 0) {
    $errors[] = 'Subject is required.';
  } else {
    $st = $db->prepare("SELECT id, name, slug FROM subjects WHERE id = :id");
    $st->execute([':id' => $subject_id]);
    $subject = $st->fetch(PDO::FETCH_ASSOC) ?: null;
    if (!$subject) {
      $errors[] = 'Subject not found.';
    }
  }

  // 2) Fields
  $title     = trim((string)($_POST['title'] ?? ''));
  $slug      = trim((string)($_POST['slug'] ?? ''));
  $summary   = (string)($_POST['summary'] ?? '');
  $body      = (string)($_POST['body'] ?? '');
  $visible   = isset($_POST['visible']) ? 1 : 0;
  $nav_order = (int)($_POST['nav_order'] ?? 0);

  if ($title === '') {
    $errors[] = 'Title is required.';
  }
  if ($slug === '' && $title !== '') {
    $slug = mk_slugify($title);
  }

  // Unique slug
  if ($slug !== '') {
    $ck = $db->prepare("SELECT id FROM pages WHERE slug = :slug LIMIT 1");
    $ck->execute([':slug' => $slug]);
    if ($ck->fetch()) {
      // auto-disambiguate by suffix
      $base = $slug;
      $i = 2;
      while (true) {
        $try = $base . '-' . $i;
        $ck->execute([':slug' => $try]);
        if (!$ck->fetch()) {
          $slug = $try;
          break;
        }
        $i++;
        if ($i > 200) {
          $errors[] = 'Could not generate unique slug.';
          break;
        }
      }
    }
  }

  // 3) If ok → insert page
  if (!$errors) {
    $ins = $db->prepare("
      INSERT INTO pages
        (subject_id, title, slug, summary, body,
         visible, is_active, nav_order, created_at)
      VALUES
        (:sid, :title, :slug, :summary, :body,
         :vis,  :vis,      :nav,      NOW())
    ");
    $ins->execute([
      ':sid'    => $subject_id,
      ':title'  => $title,
      ':slug'   => $slug,
      ':summary'=> $summary,
      ':body'   => $body,
      ':vis'    => $visible,
      ':nav'    => $nav_order,
    ]);

    $page_id = (int)$db->lastInsertId();

    // 4) Handle file uploads (optional)
    $upload_root = rtrim(PUBLIC_PATH, DIRECTORY_SEPARATOR)
      . DIRECTORY_SEPARATOR . 'lib'
      . DIRECTORY_SEPARATOR . 'uploads'
      . DIRECTORY_SEPARATOR . 'pages'
      . DIRECTORY_SEPARATOR . $page_id;

    if (!is_dir($upload_root)) {
      @mkdir($upload_root, 0775, true);
    }

    $maxEach = 25 * 1024 * 1024; // 25MB per file

    if (!empty($_FILES['attachments']) && is_array($_FILES['attachments']['name'])) {
      $names = $_FILES['attachments']['name'];
      $tmps  = $_FILES['attachments']['tmp_name'];
      $errs  = $_FILES['attachments']['error'];
      $sizes = $_FILES['attachments']['size'];
      $types = $_FILES['attachments']['type'];

      for ($i = 0, $len = count($names); $i < $len; $i++) {
        if ($errs[$i] === UPLOAD_ERR_NO_FILE) {
          continue;
        }
        if ($errs[$i] !== UPLOAD_ERR_OK) {
          $errors[] = "Upload failed for '{$names[$i]}' (error {$errs[$i]}).";
          continue;
        }
        if ($sizes[$i] > $maxEach) {
          $errors[] = "File too large: '{$names[$i]}' (limit 25MB).";
          continue;
        }

        $orig  = (string)$names[$i];
        $mime  = (string)$types[$i];
        $tmp   = (string)$tmps[$i];
        $bytes = (int)$sizes[$i];

        $safe   = mk_sanitize_filename($orig);
        $stored = $safe;
        $ctr    = 2;

        while (file_exists($upload_root . DIRECTORY_SEPARATOR . $stored)) {
          $dot = strrpos($safe, '.');
          if ($dot !== false) {
            $base = substr($safe, 0, $dot);
            $ext  = substr($safe, $dot + 1);
            $stored = $base . '-' . $ctr . '.' . $ext;
          } else {
            $stored = $safe . '-' . $ctr;
          }
          $ctr++;
        }

        if (!@move_uploaded_file($tmp, $upload_root . DIRECTORY_SEPARATOR . $stored)) {
          $errors[] = "Failed to store file '{$orig}'.";
          continue;
        }

        // record in page_files
        $rel = '/lib/uploads/pages/' . $page_id . '/' . $stored;
        $pf  = $db->prepare("
          INSERT INTO page_files
            (page_id, original_name, stored_name, mime_type,
             file_size, rel_path, created_at)
          VALUES
            (:pid, :on, :sn, :mt,
             :sz,  :rp, NOW())
        ");
        $pf->execute([
          ':pid' => $page_id,
          ':on'  => $orig,
          ':sn'  => $stored,
          ':mt'  => $mime,
          ':sz'  => $bytes,
          ':rp'  => $rel,
        ]);
      }
    }

    if ($errors) {
      $created_notice = true; // page created, but some upload issues
    } else {
      if (function_exists('flash')) {
        flash('success', 'Page created.');
      }
      $target = url_for('/staff/pages/index.php?subject_id=' . $subject_id . '&flash=created');
      header('Location: ' . $target);
      exit;
    }
  }
}

// ---------------------------------------------------------------------------
// Page chrome
// ---------------------------------------------------------------------------
$page_title  = 'New Page' . ($subject ? ' — ' . (string)$subject['name'] : '');
$active_nav  = 'staff';
$body_class  = 'role--staff role--pages pages-new';
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
  ['label' => 'New'],
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
        <a class="mk-btn mk-btn--ghost" href="<?= h(url_for('/staff/')) ?>">← Staff Dashboard</a>
        &nbsp;
        <?php if ($subject): ?>
          <a class="mk-btn mk-btn--ghost"
             href="<?= h(url_for('/staff/pages/index.php?subject_id=' . (int)$subject['id'])) ?>">
            ← Back to Pages
          </a>
        <?php else: ?>
          <a class="mk-btn mk-btn--ghost"
             href="<?= h(url_for('/staff/subjects/')) ?>">
            ← Back to Subjects
          </a>
        <?php endif; ?>
      </p>

      <h1><?= h($page_title) ?></h1>

      <?php if ($created_notice && $errors): ?>
        <div class="alert alert-warning">
          <p>Page created, but some attachments reported errors:</p>
          <ul>
            <?php foreach ($errors as $e): ?>
              <li><?= h($e) ?></li>
            <?php endforeach; ?>
          </ul>
          <?php if (!empty($page_id)): ?>
            <p>
              <a class="mk-btn"
                 href="<?= h(url_for('/staff/pages/show.php?id=' . (int)$page_id)) ?>">View page</a>
              &nbsp;
              <a class="mk-btn"
                 href="<?= h(url_for('/staff/pages/edit.php?id=' . (int)$page_id)) ?>">Edit page</a>
            </p>
          <?php endif; ?>
        </div>
        <?php $errors = []; // already shown ?>
      <?php endif; ?>

      <?php if ($errors): ?>
        <div class="alert alert-danger">
          <ul>
            <?php foreach ($errors as $e): ?>
              <li><?= h($e) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post"
            enctype="multipart/form-data"
            class="form form-vertical"
            novalidate>
        <?= function_exists('csrf_field') ? csrf_field() : '' ?>

        <input type="hidden" name="subject_id" value="<?= (int)$subject_id ?>"/>

        <div class="mk-field">
          <label for="title">Title <span style="color:#c00">*</span></label>
          <input
            type="text"
            id="title"
            name="title"
            class="mk-input"
            value="<?= h($title) ?>"
            required
          />
        </div>

        <div class="mk-field">
          <label for="slug">Slug (optional)</label>
          <input
            type="text"
            id="slug"
            name="slug"
            class="mk-input"
            value="<?= h($slug) ?>"
            placeholder="leave blank to auto-generate"
          />
        </div>

        <div class="mk-field">
          <label for="summary">Summary</label>
          <textarea
            id="summary"
            name="summary"
            rows="3"
            class="mk-input"
          ><?= h($summary) ?></textarea>
        </div>

        <div class="mk-field">
          <label for="body">Body</label>
          <textarea
            id="body"
            name="body"
            rows="10"
            class="mk-input"
          ><?= h($body) ?></textarea>
        </div>

        <div class="mk-field mk-field--inline">
          <label>
            <input type="checkbox" name="visible" <?= $visible ? 'checked' : '' ?>>
            Visible (active)
          </label>
        </div>

        <div class="mk-field">
          <label for="nav_order">Navigation Order</label>
          <input
            type="number"
            id="nav_order"
            name="nav_order"
            class="mk-input"
            value="<?= (int)$nav_order ?>"
          />
        </div>

        <div class="mk-field">
          <label for="attachments">Attachments (any type, multiple allowed)</label>
          <input type="file" id="attachments" name="attachments[]" multiple />
          <p class="muted small">
            Up to ~25MB per file. Files are stored in
            <code>/lib/uploads/pages/{id}/</code>.
          </p>
        </div>

        <div class="mk-form__actions">
          <button type="submit" class="mk-btn-primary">Create Page</button>
          <?php if ($subject): ?>
            <a class="mk-btn-secondary"
               href="<?= h(url_for('/staff/pages/index.php?subject_id=' . (int)$subject['id'])) ?>">
              Cancel
            </a>
          <?php else: ?>
            <a class="mk-btn-secondary"
               href="<?= h(url_for('/staff/subjects/')) ?>">
              Cancel
            </a>
          <?php endif; ?>
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
