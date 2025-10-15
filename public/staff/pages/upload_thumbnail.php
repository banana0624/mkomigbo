<?php
// project-root/public/staff/pages/upload_thumbnail.php
declare(strict_types=1);

// Self-check: resolve and load init (depth = 3)
$init = dirname(__DIR__, 3) . '/private/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

$page_title    = 'Upload Thumbnail';
$stylesheets[] = '/lib/css/ui.css';

require PRIVATE_PATH . '/shared/staff_header.php';

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
      try {
        if ($pdo instanceof PDO) {
          $chk = $pdo->prepare("SHOW COLUMNS FROM {$table} LIKE 'thumbnail'");
          $chk->execute();
          if ($chk->fetch(PDO::FETCH_ASSOC)) {
            $st = $pdo->prepare("UPDATE {$table} SET thumbnail = :url WHERE id = :id");
            $st->execute([':url' => $url, ':id' => $page_id]);
            $notice = "Thumbnail set for page #{$page_id}. URL: " . h($url);
          } else {
            $notice = "Uploaded: " . h($url) . " (no 'thumbnail' column; DB not updated)";
          }
        } else {
          $notice = "Uploaded: " . h($url);
        }
      } catch (Throwable $e) {
        $error = $e->getMessage();
      }
    }
  }
}
?>
<div class="container" style="padding:1rem 0">
  <h1>Upload Thumbnail</h1>
  <?php if ($error): ?><div class="alert danger"><?= h($error) ?></div><?php endif; ?>
  <?php if ($notice): ?><div class="alert success"><?= $notice ?></div><?php endif; ?>

  <p><a class="btn" href="<?= h(url_for('/staff/pages/thumbnails.php')) ?>">Back to Thumbnails</a></p>
</div>
<?php require PRIVATE_PATH . '/shared/staff_footer.php'; ?>
