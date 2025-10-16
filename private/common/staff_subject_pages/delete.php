<?php
// project-root/private/common/staff_subject_pages/delete.php
declare(strict_types=1);
/**
 * Requires: $subject_slug, $subject_name
 */
$init = dirname(__DIR__, 2) . '/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

if (empty($subject_slug)) { die('delete.php: $subject_slug required'); }
if (empty($subject_name)) { $subject_name = ucfirst(str_replace('-', ' ', $subject_slug)); }

$id  = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$row = $id ? page_find($id, $subject_slug) : null;
if (!$row) { http_response_code(404); die('Page not found'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  if (isset($_POST['confirm']) && $_POST['confirm'] === '1') {
    if (page_delete($id, $subject_slug)) {
      flash('success', 'Page deleted.');
    } else {
      flash('error', 'Delete failed.');
    }
    header('Location: ' . url_for("/staff/subjects/{$subject_slug}/pages/")); exit;
  }
  header('Location: ' . url_for("/staff/subjects/{$subject_slug}/pages/")); exit;
}

$page_title = "Delete Page • {$subject_name}";
$active_nav = 'staff';
$body_class = "role--staff subject--{$subject_slug}";
$stylesheets[] = '/lib/css/ui.css';
$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Subjects','url'=>'/staff/subjects/'],
  ['label'=>$subject_name,'url'=>"/staff/subjects/{$subject_slug}/"],
  ['label'=>'Pages','url'=>"/staff/subjects/{$subject_slug}/pages/"],
  ['label'=>'Delete'],
];

require PRIVATE_PATH . '/shared/header.php';
?>
<main class="container" style="max-width:680px;padding:1.25rem 0">
  <h1>Delete Page</h1>
  <p>Delete <strong><?= h($row['title'] ?? '') ?></strong>?</p>
  <form method="post">
    <?= function_exists('csrf_field') ? csrf_field() : '' ?>
    <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
    <div class="actions">
      <button class="btn btn-danger" type="submit" name="confirm" value="1">Yes, delete</button>
      <a class="btn" href="<?= h(url_for("/staff/subjects/{$subject_slug}/pages/")) ?>">Cancel</a>
    </div>
  </form>
</main>
<?php require PRIVATE_PATH . '/shared/footer.php'; ?>
