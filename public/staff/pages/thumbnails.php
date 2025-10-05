<?php
// project-root/public/staff/pages/thumbnails.php

require_once(dirname(__DIR__, 3) . '/private/assets/initialize.php');
require_login(); // from auth_functions.php

$errors = [];
$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
  $page_id = (int)($_POST['page_id'] ?? 0);
  if ($page_id <= 0) {
    $errors[] = 'Invalid page id.';
  } else {
    if (delete_page_thumbnail($page_id)) {
      $notice = 'Thumbnail deleted.';
    } else {
      $errors[] = 'Delete failed (page not found or DB error).';
    }
  }
}

// Fetch all pages
$pages = find_all_pages();

$page_title = 'Page Thumbnails';
include_once(dirname(__DIR__, 3) . '/private/shared/staff_header.php');
?>

<div class="admin-upload-wrap" style="max-width:1100px;margin:20px auto;">
  <h2>Page Thumbnails</h2>

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

  <div style="overflow:auto;">
    <table style="width:100%;border-collapse:collapse;">
      <thead>
        <tr>
          <th style="text-align:left;border-bottom:1px solid #ddd;padding:8px;">ID</th>
          <th style="text-align:left;border-bottom:1px solid #ddd;padding:8px;">Title</th>
          <th style="text-align:left;border-bottom:1px solid #ddd;padding:8px;">Subject</th>
          <th style="text-align:left;border-bottom:1px solid #ddd;padding:8px;">Thumbnail</th>
          <th style="text-align:left;border-bottom:1px solid #ddd;padding:8px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($pages as $p) { 
          $subject = !empty($p['subject_id']) ? find_subject_by_id($p['subject_id']) : null;
          $thumb   = page_thumbnail_url($p);
          $hasThumb = !empty($p['thumbnail']);
          $upload_url = url_for('/staff/pages/upload_thumbnail.php?page_id=' . (int)$p['id']);
        ?>
          <tr>
            <td style="border-bottom:1px solid #eee;padding:8px;"><?php echo (int)$p['id']; ?></td>
            <td style="border-bottom:1px solid #eee;padding:8px;"><?php echo h($p['title']); ?></td>
            <td style="border-bottom:1px solid #eee;padding:8px;"><?php echo $subject ? h($subject['name']) : '—'; ?></td>
            <td style="border-bottom:1px solid #eee;padding:8px;">
              <img src="<?php echo $thumb; ?>" alt="" style="width:80px;height:60px;object-fit:cover;border-radius:6px;border:1px solid #ddd;">
              <?php if(!$hasThumb) { echo '<div style="font-size:12px;opacity:.7;">(placeholder)</div>'; } ?>
            </td>
            <td style="border-bottom:1px solid #eee;padding:8px;">
              <a href="<?php echo $upload_url; ?>">Upload/Replace</a>
              <?php if($hasThumb) { ?>
                <form method="post" style="display:inline-block;margin-left:10px;" onsubmit="return confirm('Delete this thumbnail?');">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="page_id" value="<?php echo (int)$p['id']; ?>">
                  <button type="submit" style="background:#fff;border:1px solid #d33;color:#d33;border-radius:6px;padding:4px 8px;cursor:pointer;">Delete</button>
                </form>
              <?php } ?>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>

  <p style="margin-top:14px;">
    <a href="<?php echo url_for('/staff/pages/upload_thumbnail.php'); ?>">Upload a new thumbnail</a>
  </p>
</div>

<?php include_once(dirname(__DIR__, 3) . '/private/shared/staff_footer.php'); ?>
