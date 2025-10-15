<?php
// project-root/private/common/staff_subject_pages/delete.php

declare(strict_types=1);
$init = dirname(__DIR__, 2) . '/assets/initialize.php';
if (!is_file($init)) { die('Init not found at: ' . $init); }
require_once $init;

if (empty($subject_slug)) { die('delete.php: $subject_slug required'); }
if (empty($subject_name)) { $subject_name = ucfirst(str_replace('-', ' ', $subject_slug)); }

$id = (int)($_GET['id'] ?? 0);
$page_title = "Delete Page #{$id} • {$subject_name}";
$active_nav = 'staff';
$body_class = "role--staff subject--{$subject_slug}";
$page_logo  = "/lib/images/subjects/{$subject_slug}.svg";
$stylesheets[] = '/lib/css/ui.css';
$stylesheets[] = '/lib/css/landing.css';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  page_delete($subject_slug, $id);
  header('Location: ' . url_for("/staff/subjects/{$subject_slug}/pages/"));
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  page_delete($subject_slug, $id);
  flash('success', 'Page deleted.');
  header('Location: ' . url_for("/staff/subjects/{$subject_slug}/pages/"));
  exit;
}

$breadcrumbs = [
  ['label'=>'Home','url'=>'/'],
  ['label'=>'Staff','url'=>'/staff/'],
  ['label'=>'Subjects','url'=>'/staff/subjects/'],
  ['label'=>$subject_name,'url'=>"/staff/subjects/{$subject_slug}/"],
  ['label'=>'Pages','url'=>"/staff/subjects/{$subject_slug}/pages/"],
  ['label'=>"Delete #{$id}"],
];

require_once PRIVATE_PATH . '/shared/header.php';
?>
<main id="main" class="container" style="max-width:700px;margin:1.25rem auto;padding:0 1rem;">
  <h1>Delete Page #<?= $id ?> — <?= h($subject_name) ?></h1>
  <p class="muted">This is a stub. Hook this into your deletion handler.</p>
  <form method="post" action="<?= h(url_for("/staff/subjects/{$subject_slug}/pages/delete.php?id={$id}")) ?>">
  <?= csrf_field() ?>
    <p>Are you sure you want to delete this page?</p>
    <button class="btn btn-danger" type="submit">Delete</button>
    <a class="btn" href="<?= h(url_for("/staff/subjects/{$subject_slug}/pages/")) ?>">Cancel</a>
  </form>
</main>
<?php require_once PRIVATE_PATH . '/shared/footer.php'; ?>

