<?php
declare(strict_types=1);
$init = dirname(__DIR__, 5) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;
require_once PRIVATE_PATH . '/common/platforms/platform_common.php';
$platform_slug='communities'; $platform_name='Communities';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
  csrf_check();
  $dir = platform_uploads_dir($platform_slug); $f = $_FILES['file'];
  if ($f['error'] === UPLOAD_ERR_OK) {
    $name = preg_replace('~[^a-zA-Z0-9._-]+~','-', basename($f['name']));
    $dest = $dir . DIRECTORY_SEPARATOR . $name;
    if (move_uploaded_file($f['tmp_name'], $dest)) { flash('success','File uploaded.'); header('Location: ' . url_for("/staff/platforms/{$platform_slug}/media/")); exit; }
    flash('error','Failed to move uploaded file.');
  } else { flash('error','Upload error code: ' . (int)$f['error']); }
}
$page_title="{$platform_name} • Upload"; $active_nav='staff'; $body_class="role--staff platform--{$platform_slug}";
$page_logo="/lib/images/platforms/{$platform_slug}.svg"; $stylesheets[]='/lib/css/ui.css';
$breadcrumbs=[['label'=>'Home','url'=>'/'],['label'=>'Staff','url'=>'/staff/'],['label'=>'Platforms','url'=>'/staff/platforms/'],['label'=>$platform_name,'url'=>"/staff/platforms/{$platform_slug}/"],['label'=>'Media','url'=>"/staff/platforms/{$platform_slug}/media/"],['label'=>'Upload']];
require PRIVATE_PATH . '/shared/staff_header.php';
?>
<main class="container" style="max-width:700px;padding:1.25rem 0">
  <h1>Upload — <?= h($platform_name) ?></h1>
  <?= function_exists('display_session_message') ? display_session_message() : '' ?>
  <form method="post" enctype="multipart/form-data">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <div class="field"><label>File</label><input class="input" type="file" name="file" required></div>
    <div class="actions">
      <button class="btn btn-primary" type="submit">Upload</button>
      <a class="btn" href="<?= h(url_for("/staff/platforms/{$platform_slug}/media/")) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php require PRIVATE_PATH . '/shared/staff_footer.php'; ?>
