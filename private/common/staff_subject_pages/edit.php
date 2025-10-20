<?php
// project-root/private/common/staff_subject_pages/edit.php
declare(strict_types=1);
/**
 * Requires: $subject_slug, $subject_name
 */
$init = dirname(__DIR__, 2) . '/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

/** ---- Permission gate (tolerant if wrapper already defined) ---- */
$__need_guard = (!defined('REQUIRE_LOGIN') || !defined('REQUIRE_PERMS'));
if (!defined('REQUIRE_LOGIN')) {
  define('REQUIRE_LOGIN', true);
}
if (!defined('REQUIRE_PERMS')) {
  define('REQUIRE_PERMS', ['pages.edit']);
}
if ($__need_guard) {
  require PRIVATE_PATH . '/middleware/guard.php';
}

if (empty($subject_slug)) { die('edit.php: $subject_slug required'); }
if (empty($subject_name)) { $subject_name = ucfirst(str_replace('-', ' ', $subject_slug)); }

// DRY logo
require_once __DIR__ . '/_prelude.php';

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$row = $id ? page_find($id, $subject_slug) : null;
if (!$row) { http_response_code(404); die('Page not found'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();

  $title   = sanitize_text($_POST['title'] ?? '');
  $slug_in = sanitize_text($_POST['slug'] ?? '');
  $body    = (string)($_POST['body'] ?? '');
  $slug    = $slug_in !== '' ? slugify($slug_in) : slugify($title);

  $data = [
    'title'        => $title,
    'slug'         => $slug,
    'body'         => $body,
    'is_published' => isset($_POST['is_published']) ? 1 : 0,
  ];

  if (page_update($id, $subject_slug, $data)) {
    flash('success', 'Page updated.');
    header('Location: ' . url_for("/staff/subjects/{$subject_slug}/pages/")); exit;
  }
  flash('error', 'Update failed. Title & Slug are required.');
}

$page_title     = "Edit Page • {$subject_name}";
$active_nav     = 'staff';
$body_class     = "role--staff subject--{$subject_slug}";
$stylesheets[]  = '/lib/css/ui.css';
$breadcrumbs    = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Subjects','url'=>'/staff/subjects/'],
  ['label'=>$subject_name,'url'=>"/staff/subjects/{$subject_slug}/"],
  ['label'=>'Pages','url'=>"/staff/subjects/{$subject_slug}/pages/"],
  ['label'=>'Edit'],
];

require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="max-width:780px;padding:1.25rem 0">
  <h1>Edit Page — <?= h($subject_name) ?></h1>
  <?= function_exists('display_session_message') ? display_session_message() : '' ?>
  <form method="post">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
    <div class="field"><label>Title</label>
      <input class="input" type="text" name="title" value="<?= h($row['title'] ?? '') ?>" required>
    </div>
    <div class="field"><label>Slug</label>
      <input class="input" type="text" name="slug" value="<?= h($row['slug'] ?? '') ?>" required>
    </div>
    <div class="field"><label>Body</label>
      <textarea class="input" name="body" rows="10"><?= h($row['body'] ?? '') ?></textarea>
    </div>
    <div class="field">
      <label><input type="checkbox" name="is_published" value="1" <?= !empty($row['is_published']) ? 'checked' : '' ?>> Published</label>
    </div>
    <div class="actions">
      <button class="btn btn-primary" type="submit">Save</button>
      <a class="btn" href="<?= h(url_for("/staff/subjects/{$subject_slug}/pages/")) ?>">Cancel</a>
    </div>
  </form>

  <?php /* URL-based media inserter */ ?>
  <section style="margin:1rem 0;border-top:1px solid #eee;padding-top:.75rem">
    <div class="muted" style="margin-bottom:.5rem">Insert media (by URL):</div>
    <div class="actions" style="display:flex;gap:.5rem;flex-wrap:wrap">
      <button class="btn btn-sm" type="button" onclick="insImg()">Image</button>
      <button class="btn btn-sm" type="button" onclick="insVideo()">Video</button>
      <button class="btn btn-sm" type="button" onclick="insAudio()">Audio</button>
      <button class="btn btn-sm" type="button" onclick="insLink()">Link</button>
    </div>
  </section>

  <script>
  (function(){
    const ta = document.querySelector('textarea[name="body"]');
    function insertAtCaret(openTag, closeTag, placeholder='') {
      if (!ta) return;
      ta.focus();
      const start = ta.selectionStart ?? ta.value.length;
      const end   = ta.selectionEnd ?? ta.value.length;
      const sel   = ta.value.substring(start, end) || placeholder;
      const before = ta.value.substring(0, start);
      const after  = ta.value.substring(end);
      ta.value = before + openTag + sel + closeTag + after;
      const caret = (before + openTag + sel + closeTag).length;
      ta.setSelectionRange(caret, caret);
    }
    window.insImg = function(){ const url = prompt('Image URL (http/https):'); if (!url) return; insertAtCaret('<img src="'+url+'" alt="','" />','alt text'); };
    window.insVideo = function(){ const url = prompt('Video URL (mp4/webm/ogg):'); if (!url) return; insertAtCaret('<video controls src="','"></video>'); };
    window.insAudio = function(){ const url = prompt('Audio URL (mp3/ogg/wav):'); if (!url) return; insertAtCaret('<audio controls src="','"></audio>'); };
    window.insLink = function(){ const url = prompt('Link URL:'); if (!url) return; insertAtCaret('<a href="'+url+'" target="_blank" rel="noopener">','</a>','link text'); };
  })();
  </script>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
