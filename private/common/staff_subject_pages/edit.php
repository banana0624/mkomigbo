<?php
// project-root/private/common/staff_subject_pages/edit.php
declare(strict_types=1);
/**
 * Requires: $subject_slug, $subject_name
 */
$init = dirname(__DIR__, 2) . '/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

if (empty($subject_slug)) { die('edit.php: $subject_slug required'); }
if (empty($subject_name)) { $subject_name = ucfirst(str_replace('-', ' ', $subject_slug)); }

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$row = $id ? page_find($id, $subject_slug) : null;
if (!$row) { http_response_code(404); die('Page not found'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();

  // --- Sanitization & slugging (as requested) ---
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

$page_title = "Edit Page • {$subject_name}";
$active_nav = 'staff';
$body_class = "role--staff subject--{$subject_slug}";
$stylesheets[] = '/lib/css/ui.css';
$breadcrumbs = [
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
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
