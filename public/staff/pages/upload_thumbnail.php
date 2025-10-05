<?php
// project-root/public/staff/pages/upload_thumbnail.php
require_once(dirname(__DIR__, 3) . '/private/assets/initialize.php');
require_login();

$errors = [];
$notice = '';
$pages  = find_all_pages(); // for dropdown

$prefill_id = isset($_GET['page_id']) ? (int)$_GET['page_id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $page_id = (int)($_POST['page_id'] ?? 0);
  $page    = $page_id ? find_page_by_id($page_id) : null;

  if (!$page) {
    $errors[] = 'Invalid page selected.';
  } elseif (!isset($_FILES['thumbnail'])) {
    $errors[] = 'No file selected.';
  } else {
    $slug   = $page['slug'] ?? ('page-' . $page_id);
    $result = process_image_upload($_FILES['thumbnail'], UPLOADS_PAGES_PATH, $slug);

    if (!$result['ok']) {
      $errors[] = $result['error'];
    } else {
      $filename = $result['filename'];
      if (update_page_thumbnail($page_id, $filename)) {
        $notice = 'Thumbnail uploaded successfully.';
        $prefill_id = $page_id; // keep selection
      } else {
        $errors[] = 'Database update failed.';
      }
    }
  }
}

$page_title = 'Upload Page Thumbnail';
include_once(dirname(__DIR__, 3) . '/private/shared/staff_header.php');
?>

<div class="admin-upload-wrap" style="max-width:700px;margin:20px auto;">
  <h2>Upload Page Thumbnail</h2>

  <p style="margin:0 0 12px;">
    <a href="<?php echo url_for('/staff/pages/thumbnails.php'); ?>">← Back to Thumbnails</a>
  </p>

  <?php if (!empty($notice)) { ?>
    <div style="background:#e6ffed;border:1px solid #b7eb8f;padding:10px;border-radius:8px;margin-bottom:10px;">
      <?php echo h($notice); ?>
    </div>
  <?php } ?>

  <?php if (!empty($errors)) { ?>
    <div style="background:#fff1f0;border:1px solid #ffa39e;padding:10px;border-radius:8px;margin-bottom:10px;">
      <ul style="margin:0;padding-left:18px;">
        <?php foreach($errors as $e) { echo '<li>' . h($e) . '</li>'; } ?>
      </ul>
    </div>
  <?php } ?>

  <form method="post" enctype="multipart/form-data">
    <div style="margin:10px 0;">
      <label for="page_id">Select Page</label><br>
      <select id="page_id" name="page_id" required>
        <option value="">-- Choose a page --</option>
        <?php foreach($pages as $p) { 
          $sel = ($prefill_id && (int)$p['id'] === $prefill_id) ? 'selected' : '';
        ?>
          <option value="<?php echo (int)$p['id']; ?>" <?php echo $sel; ?>>
            <?php echo '(' . (int)$p['id'] . ') ' . h($p['title']); ?>
          </option>
        <?php } ?>
      </select>
    </div>

    <div style="margin:10px 0;">
      <label for="thumbnail">Thumbnail (JPG/PNG/GIF/AVIF, ≤ 5MB)</label><br>
      <input id="thumbnail" name="thumbnail" type="file" accept=".jpg,.jpeg,.png,.gif,.avif" required>
    </div>

    <button type="submit">Upload</button>
  </form>
</div>

<?php include_once(dirname(__DIR__, 3) . '/private/shared/staff_footer.php'); ?>
