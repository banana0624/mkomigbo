<?php
// project-root/private/common/staff_subject_media/upload.php
declare(strict_types=1);

$init = dirname(__DIR__, 2) . '/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

if (empty($subject_slug)) { die('media/upload.php: $subject_slug required'); }
if (empty($subject_name)) { $subject_name = ucfirst(str_replace('-', ' ', $subject_slug)); }

$uploadsRoot = PUBLIC_PATH . "/lib/uploads/{$subject_slug}";
if (!is_dir($uploadsRoot)) { @mkdir($uploadsRoot, 0775, true); }

$upload_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();

  if (!isset($_FILES['file'])) {
    $upload_error = 'No file received.';
  } else {
    $f = $_FILES['file'];
    if ($f['error'] === UPLOAD_ERR_OK) {
      $name = preg_replace('~[^a-zA-Z0-9._-]+~', '-', basename($f['name']));
      if ($name === '' || $name === '.' || $name === '..') {
        $upload_error = 'Invalid file name.';
      } else {
        $dest = $uploadsRoot . '/' . $name;
        if (move_uploaded_file($f['tmp_name'], $dest)) {
          // optional: insert into media table here
          flash('success', 'File uploaded.');
          header('Location: ' . url_for("/staff/subjects/{$subject_slug}/media/"));
          exit;
        } else {
          $upload_error = 'Failed to move uploaded file.';
        }
      }
    } else {
      $upload_error = 'Upload error code: ' . (int)$f['error'];
    }
  }
}

$page_title   = "Upload Media • {$subject_name}";
$active_nav   = 'staff';
$body_class   = "role--staff subject--{$subject_slug}";
$page_logo    = "/lib/images/subjects/{$subject_slug}.svg";
$stylesheets[] = '/lib/css/ui.css';
$stylesheets[] = '/lib/css/landing.css';

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Subjects','url'=>'/staff/subjects/'],
  ['label'=>$subject_name,'url'=>"/staff/subjects/{$subject_slug}/"],
  ['label'=>'Media','url'=>"/staff/subjects/{$subject_slug}/media/"],
  ['label'=>'Upload'],
];

require_once PRIVATE_PATH . '/shared/header.php';
?>
<main id="main" class="container" style="max-width:700px;margin:1.25rem auto;padding:0 1rem;">
  <h1>Upload — <?= h($subject_name) ?></h1>

  <?php if (!empty($upload_error)): ?>
    <div class="flash flash--error" style="padding:8px 12px;border-radius:8px;margin:10px 0;background:#fef2f2;color:#991b1b;border:1px solid #fecaca;">
      <?= h($upload_error) ?>
    </div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" action="<?= h(url_for("/staff/subjects/{$subject_slug}/media/upload.php")) ?>">
    <?= csrf_field() ?>
    <div class="field"><label>File</label><input class="input" type="file" name="file" required></div>
    <div class="actions">
      <button class="btn btn-primary" type="submit">Upload</button>
      <a class="btn" href="<?= h(url_for("/staff/subjects/{$subject_slug}/media/")) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php require_once PRIVATE_PATH . '/shared/footer.php'; ?>
