<?php
// project-root/public/staff/subjects/pgs/upload.php
declare(strict_types=1);

// Self-check: resolve and load init (depth = 4)
$init = dirname(__DIR__, 4) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

$page_title    = 'Upload resource';
$stylesheets[] = '/lib/css/ui.css';

require PRIVATE_PATH . '/shared/staff_header.php';

function __ensure_dir(string $dir): bool { return is_dir($dir) || @mkdir($dir, 0755, true); }

$notice = null; $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $subject_id = (int)($_POST['subject_id'] ?? 0);
  $title      = trim((string)($_POST['title'] ?? ''));

  if ($subject_id <= 0) {
    $error = 'Subject ID is required.';
  } elseif (!isset($_FILES['file']) || ($_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    $error = 'No file uploaded.';
  } else {
    $file = $_FILES['file'];
    $max = (int)($_ENV['FILE_MAX_BYTES'] ?? (10 * 1024 * 1024)); // 10MB
    if (($file['size'] ?? 0) > $max) {
      $error = 'File too large. Max ' . number_format($max / 1024 / 1024, 1) . ' MB.';
    } else {
      $baseDir = rtrim(PUBLIC_PATH, DIRECTORY_SEPARATOR) . '/lib/uploads/files';
      if (!__ensure_dir($baseDir)) {
        $error = 'Failed to prepare upload directory.';
      } else {
        $name = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', (string)($file['name'] ?? 'file'));
        $name = trim($name, '._') ?: ('file_' . date('Ymd_His'));
        $dest = $baseDir . DIRECTORY_SEPARATOR . date('Ymd_His') . '_' . $name;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
          $error = 'Failed to move uploaded file.';
        } else {
          @chmod($dest, 0644);
          $webPath = str_replace(rtrim(PUBLIC_PATH, DIRECTORY_SEPARATOR), '', $dest);
          $webPath = str_replace(DIRECTORY_SEPARATOR, '/', $webPath);

          if (function_exists('create_file_record')) {
            $args = [
              'subject_id' => $subject_id,
              'filename'   => basename($dest),
              'filepath'   => $webPath,
              'title'      => $title,
            ];
            $rid = create_file_record($args);
            $notice = ($rid === false)
              ? ('File stored at ' . h($webPath) . ' (DB record not created).')
              : ('Uploaded and recorded (ID ' . (int)$rid . '), path: ' . h($webPath));
          } else {
            $notice = 'File uploaded to ' . h($webPath) . ' (no DB insert; helper missing).';
          }
        }
      }
    }
  }
}

// Subjects for dropdown
$subjects = [];
if (function_exists('subjects_load_complete'))      $subjects = subjects_load_complete(null);
elseif (function_exists('subject_registry_all')) { $subjects = subject_registry_all(); if (!array_is_list($subjects)) $subjects = array_values($subjects); }

?>
<div class="container" style="padding:1rem 0">
  <h1>Upload resource</h1>
  <?php if ($error): ?><div class="alert danger"><?= h($error) ?></div><?php endif; ?>
  <?php if ($notice): ?><div class="alert success"><?= $notice ?></div><?php endif; ?>

  <form class="form" method="post" enctype="multipart/form-data">
    <div class="form-grid">
      <div class="field"><label for="subject_id" class="req">Subject</label></div>
      <div>
        <select class="select" name="subject_id" id="subject_id" required>
          <option value="">— choose subject —</option>
          <?php foreach ($subjects as $s): $sid=(int)($s['id']??0); ?>
            <option value="<?= $sid ?>"><?= h($s['name']??('Subject '.$sid)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field"><label for="title">Title (optional)</label></div>
      <div><input class="input" type="text" name="title" id="title" placeholder="File title"></div>

      <div class="field"><label for="file" class="req">File</label></div>
      <div><input class="input" type="file" name="file" id="file" required></div>
    </div>

    <div style="margin-top:1rem">
      <button class="btn btn-primary" type="submit">Upload</button>
      <a class="btn" href="<?= h(url_for('/staff/subjects/pgs/')) ?>">Back</a>
    </div>
  </form>
</div>
<?php require PRIVATE_PATH . '/shared/staff_footer.php'; ?>
