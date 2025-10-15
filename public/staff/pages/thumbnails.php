<?php
// project-root/public/staff/pages/thumbnails.php
declare(strict_types=1);

// Self-check: resolve and load init (depth = 3)
$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

$page_title    = 'Page Thumbnails';
$stylesheets[] = '/lib/css/ui.css';

require PRIVATE_PATH . '/shared/staff_header.php';

function column_exists(PDO $pdo, string $table, string $column): bool {
  try { $st = $pdo->prepare("SHOW COLUMNS FROM {$table} LIKE :col"); $st->execute([':col' => $column]); return (bool)$st->fetch(PDO::FETCH_ASSOC); }
  catch (Throwable $e) { return false; }
}
$pdo   = function_exists('db') ? db() : (function_exists('db_connect') ? db_connect() : null);
$table = function_exists('page_table') ? page_table() : ($_ENV['PAGES_TABLE'] ?? 'pages');

$notice = null; $error  = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $page_id = (int)($_POST['page_id'] ?? 0);
  if ($page_id <= 0)        $error = 'Valid Page ID is required.';
  elseif (!isset($_FILES['thumb']) || ($_FILES['thumb']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK)
                            $error = 'No image uploaded.';
  else {
    $res = process_image_upload($_FILES['thumb'], [
      'dest_subdir'    => 'thumbnails',
      'basename_prefix'=> 'page-thumb',
      'auto_resize'    => true,
      'max_w'          => 1200,
      'max_h'          => 1200,
    ]);
    if (!$res['ok']) {
      $error = $res['error'] ?? 'Upload failed.';
    } else {
      $url = (string)($res['url'] ?? '');
      if ($pdo instanceof PDO && column_exists($pdo, $table, 'thumbnail')) {
        try { $st = $pdo->prepare("UPDATE {$table} SET thumbnail = :url WHERE id = :id"); $st->execute([':url'=>$url, ':id'=>$page_id]); $notice = "Thumbnail set for page #{$page_id}. URL: " . h($url); }
        catch (Throwable $e) { $notice = "Uploaded: " . h($url) . " (DB update skipped: " . h($e->getMessage()) . ")"; }
      } else {
        $notice = "Uploaded: " . h($url) . " (no 'thumbnail' column on {$table}; DB not updated)";
      }
    }
  }
}
?>
<div class="container" style="padding:1rem 0">
  <h1>Page Thumbnails</h1>
  <?php if ($error): ?><div class="alert danger"><?= h($error) ?></div><?php endif; ?>
  <?php if ($notice): ?><div class="alert success"><?= $notice ?></div><?php endif; ?>

  <form class="form" method="post" enctype="multipart/form-data">
    <div class="form-grid">
      <div class="field"><label for="page_id" class="req">Page ID</label></div>
      <div><input class="input" type="number" name="page_id" id="page_id" min="1" required></div>

      <div class="field"><label for="thumb" class="req">Thumbnail image</label></div>
      <div><input class="input" type="file" name="thumb" id="thumb" accept="image/*" required></div>
    </div>

    <div style="margin-top:1rem">
      <button class="btn btn-primary" type="submit">Upload</button>
      <a class="btn" href="<?= h(url_for('/staff/')) ?>">Back</a>
    </div>
  </form>
</div>
<?php require PRIVATE_PATH . '/shared/staff_footer.php'; ?>
