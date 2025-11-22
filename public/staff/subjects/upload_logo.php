<?php
// project-root/public/staff/subjects/upload_logo.php
declare(strict_types=1);

// Bootstrap
$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (!is_file($init)) {
  echo "<h1>FATAL: initialize.php missing</h1>";
  echo "<p>Expected at: {$init}</p>";
  exit;
}
require_once $init;

// Auth guard
if (function_exists('require_staff')) {
  require_staff();
} elseif (function_exists('require_login')) {
  require_login();
}

// Helpers we rely on
if (!function_exists('h')) {
  function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
  }
}

// Make sure subject + image helpers are loaded
if (!function_exists('find_subject_by_id') || !function_exists('subject_logo_url')) {
  if (defined('PRIVATE_PATH')) {
    $sf = PRIVATE_PATH . '/functions/subject_functions.php';
    if (is_file($sf)) {
      require_once $sf;
    }
  }
}
if (!function_exists('validate_image_upload') || !function_exists('image_detect_extension_from_mime')) {
  if (defined('PRIVATE_PATH')) {
    $imgf = PRIVATE_PATH . '/functions/image_functions.php';
    if (is_file($imgf)) {
      require_once $imgf;
    }
  }
}

// Resolve subject
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  throw new RuntimeException('Missing or invalid subject id.');
}
if (!function_exists('find_subject_by_id')) {
  throw new RuntimeException('find_subject_by_id() not defined.');
}
$subject = find_subject_by_id($id);
if (!$subject) {
  http_response_code(404);
  echo 'Subject not found for id=' . h((string)$id);
  exit;
}

$errors  = [];
$success = null;

// Handle POST (upload)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (empty($_FILES['logo']) || ($_FILES['logo']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
    $errors[] = 'Please choose an image file to upload.';
  } else {
    $file = $_FILES['logo'];

    // 1) Basic validation via our existing helpers
    if (function_exists('validate_image_upload')) {
      $validationErrors = validate_image_upload($file);
      if (!empty($validationErrors)) {
        $errors = array_values($validationErrors);
      }
    }

    // 2) If still OK, detect extension and move into /lib/images/subjects/<slug>.<ext>
    if (empty($errors)) {
      $slug = strtolower(trim((string)($subject['slug'] ?? '')));
      if ($slug === '') {
        $errors[] = 'Subject has no slug – cannot derive logo filename.';
      } else {
        // Determine extension from MIME
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = (string)($finfo->file($file['tmp_name']) ?: '');
        if (function_exists('image_detect_extension_from_mime')) {
          $ext = image_detect_extension_from_mime($mime);
        } else {
          // Fallback: assume png
          $ext = 'png';
        }
        if ($ext === null) {
          $errors[] = 'Unsupported image type for logo.';
        } else {
          // Build target dir + path
          $publicBase = defined('PUBLIC_PATH')
            ? rtrim(PUBLIC_PATH, DIRECTORY_SEPARATOR)
            : dirname(__DIR__, 3) . '/public';

          $targetDir = $publicBase
                     . DIRECTORY_SEPARATOR . 'lib'
                     . DIRECTORY_SEPARATOR . 'images'
                     . DIRECTORY_SEPARATOR . 'subjects';

          if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0775, true);
          }

          $targetPath = $targetDir . DIRECTORY_SEPARATOR . $slug . '.' . $ext;

          if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            $errors[] = 'Failed to move uploaded file into subjects logo folder.';
          } else {
            @chmod($targetPath, 0644);
            $success = sprintf(
              'Logo updated successfully for subject “%s”. Saved as %s.%s.',
              (string)($subject['name'] ?? 'Subject'),
              $slug,
              $ext
            );
          }
        }
      }
    }
  }

  // After POST, re-load $subject_logo_url below, so new file is picked up.
}

// Compute current subject logo URL using our helper
$subjectLogoUrl = function_exists('subject_logo_url')
  ? subject_logo_url($subject)
  : null;

$page_title = 'Upload Subject Logo';
$stylesheets = $stylesheets ?? [];
if (!in_array('/lib/css/ui.css', $stylesheets, true)) {
  $stylesheets[] = '/lib/css/ui.css';
}

// Staff header
if (defined('PRIVATE_PATH') && is_file(PRIVATE_PATH . '/shared/staff_header.php')) {
  include PRIVATE_PATH . '/shared/staff_header.php';
} elseif (defined('SHARED_PATH') && is_file(SHARED_PATH . '/staff_header.php')) {
  include SHARED_PATH . '/staff_header.php';
}
?>

<main class="mk-main mk-container mk-container--narrow">
  <header class="mk-page-header">
    <h1>Upload Logo for “<?= h((string)($subject['name'] ?? 'Subject')) ?>”</h1>
    <p class="mk-muted">
      Choose an image for this subject’s logo. It will appear on the public subject page.
    </p>
  </header>

  <?php if ($success): ?>
    <div class="mk-alert mk-alert--success">
      <?= h($success) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
    <div class="mk-alert mk-alert--error">
      <p><strong>There were problems with your upload:</strong></p>
      <ul>
        <?php foreach ($errors as $err): ?>
          <li><?= h($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <section class="mk-section">
    <h2>Current Logo</h2>
    <?php if ($subjectLogoUrl): ?>
      <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1rem;">
        <img src="<?= h($subjectLogoUrl) ?>"
             alt="<?= h((string)($subject['name'] ?? 'Subject')) ?> logo"
             style="width:80px;height:80px;border-radius:999px;object-fit:contain;
                    background:#fff;box-shadow:0 0 0 1px rgba(0,0,0,.05),0 8px 18px rgba(0,0,0,.06);">
        <code><?= h($subjectLogoUrl) ?></code>
      </div>
    <?php else: ?>
      <p class="mk-muted">
        No subject logo found yet. Upload one below.
      </p>
    <?php endif; ?>
  </section>

  <section class="mk-section">
    <h2>Upload a New Logo</h2>
    <form method="post" enctype="multipart/form-data" class="mk-form">
      <div class="mk-form-field">
        <label for="logo">Logo image (PNG, JPG, GIF, WEBP, AVIF)</label>
        <input type="file" name="logo" id="logo" accept="image/*" required>
      </div>

      <div class="mk-form-actions">
        <button type="submit" class="mk-btn mk-btn--primary">Upload logo</button>
        <a href="<?= h(url_for('/staff/subjects/')) ?>" class="mk-btn mk-btn--ghost">
          &larr; Back to Subjects
        </a>
      </div>
    </form>
  </section>

  <section class="mk-section mk-section--debug">
    <h2>Debug info (temporary)</h2>
    <pre><?php
echo "REQUEST_METHOD = " . $_SERVER['REQUEST_METHOD'] . PHP_EOL;
echo "PUBLIC_PATH    = " . (defined('PUBLIC_PATH') ? PUBLIC_PATH : '(not defined)') . PHP_EOL;
echo "slug           = " . h((string)($subject['slug'] ?? '')) . PHP_EOL;
echo "subjectLogoUrl = " . h((string)($subjectLogoUrl ?? 'NULL')) . PHP_EOL;
?></pre>
  </section>
</main>

<?php
// Staff footer
if (defined('PRIVATE_PATH') && is_file(PRIVATE_PATH . '/shared/footer.php')) {
  include PRIVATE_PATH . '/shared/footer.php';
} elseif (defined('SHARED_PATH') && is_file(SHARED_PATH . '/footer.php')) {
  include SHARED_PATH . '/footer.php';
}
